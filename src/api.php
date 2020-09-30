<?php

header('Cache-Control: max-age=34400');
set_time_limit(0);
ini_set("memory_limit", "-1");

ob_start();

if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
	$url = parse_url($_SERVER['HTTP_REFERER']);
	$url = $url['scheme'] . '://' . $url['host'];
} else if (isset($_SERVER['HTTP_ORIGIN']) && !empty($_SERVER['HTTP_ORIGIN'])) $url = $_SERVER['HTTP_ORIGIN'];
else $url = '*';

header('Access-Control-Allow-Origin: ' . $url);
header('Content-Type: application/json; charset=utf-8');

require_once('includes/config.php');
require_once('sms.php');

$functions = [
	'get_areas',
	'get_sodic_areas',
	'get_categories',
	'get_stores',
	'get_products',
	'is_user_logged_in',
	'login_user',
	'register_user',
	'update_user',
	'password_reset',
	'login_delivery_staff',
	'product_toggle_favorite',
	'get_favorite_products',
	'get_lists',
	'save_list',
	'remove_list',
	'get_orders',
	'save_order',
	'rate_order',
	'add_device',
	'remove_device',
	'change_order_state',
	'toggle_product_fetch',
	'get_payfort_data',
	'get_options',
	'dashboard_stats',
	'graph_users_count',
	'graph_orders_count',
	'graph_orders_price_sum',
	'graph_orders_payment_method',
	'graph_avg_revenue_per_store',
	'graph_revenue_per_store',
	'validate_coupon',
	'get_banner_for_area',
	'update_price',
	'edit_order_products',
	'update_shop_order',
	'get_charge',
	'get_slots',

];

$output = process_api_call($functions);

ob_clean();
echo $output;
ob_end_flush();

function check_ip($client_ip = null, $api_list_mode = API_LIST_MODE)
{
	if ($api_list_mode == API_LIST_MODE_OFF) return true;

	$status = false;
	if (!$client_ip) $client_ip = $_SERVER['REMOTE_ADDR'];
	$ip_list = explode("\n", API_IP_LIST);

	foreach ($ip_list as &$pattern) {
		$pattern = trim($pattern);
		if (empty($pattern)) continue;

		$status = preg_match('/' . $pattern . '/x', $client_ip);
		if ($status) break;
	}

	if (($status && $api_list_mode == API_LIST_MODE_WHITE) || (!$status && $api_list_mode == API_LIST_MODE_BLACK)) return true;
	else return false;
}

function process_api_call($functions)
{
	if (!check_ip()) return null;

	$output = null;
	$data = json_decode(trim(file_get_contents('php://input')), true);

	if ($data && isset($data['call'])) {
		$function = trim($data['call']);

		if (isset($data['args'])) $args = $data['args'];
		else $args = null;

		if (in_array($function, $functions) && function_exists($function)) $output = json_encode(utf8_encode_array(call_user_func($function, $args)), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
	}

	return $output;
}

function dashboard_stats($data)
{
	$data = prepare_inputs($data);

	$year = date('Y');
	$min_month = $max_month = date('m');
	$min_day = $max_day = date('d');

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $min_month = $data['month'];
	if (isset($data['day'])) $min_day = $data['day'];

	if ($min_month == '%') {
		$min_month = '01';
		$max_month = '12';
	}

	if ($min_day == '%') {
		$min_day = '01';
		$max_day = '31';
	}

	$min_date = $year . '-' . $min_month . '-' . $min_day . ' 00:00:00';
	$max_date = $year . '-' . $max_month . '-' . $max_day . ' 23:59:59';

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$user_count = $shop_count = $avg_total_price = $delivery_staff_count = $delivered_orders_count = $undelivered_orders_count = $total_revenue = $upcoming_revenue = $avg_delivery_time_confirmed = $avg_delivery_time_fetched = 0;

	$output = [];
	$con = get_db_con();

	$query = "SELECT 
	(SELECT COUNT(*) FROM shop WHERE admin_id LIKE '" . $admin_id . "') AS shop_count,
	(SELECT COUNT(*) FROM delivery_staff WHERE admin_id LIKE '" . $admin_id . "') AS delivery_staff_count,
	(SELECT COUNT(*) FROM user) AS user_count,
	
	(SELECT COUNT(*) FROM states_per_order,order_states,orders WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND order_states.id=order_state_id AND orders.id=order_id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num=6) AS delivered_orders_count,
	
	(SELECT COUNT(*) FROM states_per_order,order_states,orders WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND order_states.id=order_state_id AND orders.id=order_id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num NOT IN ('" . ORDER_STATE_COMPLETED . "','" . ORDER_STATE_CANCELED . "','" . ORDER_STATE_NOT_DELIVERED . "')) AS undelivered_orders_count,
	
	(SELECT ROUND(AVG(products_per_order.single_price*products_per_order.quantity),2) FROM products_per_order 
	INNER JOIN orders ON orders.id=products_per_order.order_id 
	INNER JOIN product ON product.id=products_per_order.product_id 
	INNER JOIN shop ON shop.id=product.shop_id 
	WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND is_paid='1' AND shop.admin_id LIKE '" . $admin_id . "') AS avg_total_price,
	
	(SELECT SUM(products_per_order.single_price*products_per_order.quantity) FROM products_per_order 
	INNER JOIN orders ON orders.id=products_per_order.order_id 
	INNER JOIN product ON product.id=products_per_order.product_id 
	INNER JOIN shop ON shop.id=product.shop_id 
	WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND is_paid='1' AND shop.admin_id LIKE '" . $admin_id . "') AS total_revenue,
	
	(SELECT SUM(total_price) FROM states_per_order,order_states,orders WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND order_states.id=order_state_id AND orders.id=order_id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num NOT IN ('" . ORDER_STATE_COMPLETED . "','" . ORDER_STATE_CANCELED . "','" . ORDER_STATE_NOT_DELIVERED . "')) AS upcoming_revenue,
	
	(SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND,(SELECT MIN(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND state_num='" . ORDER_STATE_CONFIRMED . "'),
	(SELECT MAX(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND created_at=(SELECT MAX(created_at) 
	FROM states_per_order WHERE order_id=orders.id) AND state_num=6)))) FROM states_per_order,orders WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND orders.id=order_id) AS avg_delivery_time_confirmed,
	
	(SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND,(SELECT MIN(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND state_num='" . ORDER_STATE_FETCHED . "'),
	(SELECT MAX(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND created_at=(SELECT MAX(created_at) 
	FROM states_per_order WHERE order_id=orders.id) AND state_num=6)))) FROM states_per_order,orders WHERE orders.created_at BETWEEN '" . $min_date . "' AND '" . $max_date . "' AND orders.id=order_id) AS avg_delivery_time_fetched";

	$result = db_query($con, $query);

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$output = [
			'shop_count' => $row['shop_count'],
			'user_count' => $row['user_count'],
			'delivery_staff_count' => $row['delivery_staff_count'],
			'avg_total_price' => format_money($row['avg_total_price']),
			'delivered_orders_count' => $row['delivered_orders_count'],
			'undelivered_orders_count' => $row['undelivered_orders_count'],
			'total_revenue' => format_money($row['total_revenue']),
			'upcoming_revenue' => format_money($row['upcoming_revenue']),
			'avg_delivery_time_confirmed' => gmdate('G\h i\m', $row['avg_delivery_time_confirmed']),
			'avg_delivery_time_fetched' => gmdate('G\h i\m', $row['avg_delivery_time_fetched'])
		];
	}

	close_db_con($con);
	return $output;
}

function graph_users_count($data)
{
	return [graph_users_count_users($data), graph_users_count_delivery_staff($data)];
}

function get_slots($data)
{
	$data = prepare_inputs($data);

	$con = get_db_con();
	$day = $data['day'];
	$area_id = $data['area_id'];
	$query = "SELECT *  FROM day JOIN slots on slots.day_id = day.id JOIN area on slots.area_id = area.id    WHERE day='${day}' AND area.id = ${area_id} order by slots.id desc LIMIT 1 "; 
	
		 return mysqli_fetch_assoc (mysqli_query($con, $query));
}


function graph_users_count_users($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	$con = get_db_con();
	$query = "SELECT COUNT(*) AS user_count,created_at FROM user WHERE YEAR(created_at) LIKE '" . $year . "' AND MONTH(created_at) LIKE '" . $month . "' AND DAY(created_at) LIKE '" . $day . "' GROUP BY YEAR(created_at),MONTH(created_at)";

	if ($month != '%') $query .= ",DAY(created_at)";
	if ($day != '%') $query .= ",HOUR(created_at)";

	$query .= " ORDER BY created_at";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$timestamp = strtotime($row['created_at']);

			$output[] = [
				'value' => intval($row['user_count']),
				'month' => intval(date('n', $timestamp)),
				'day' => intval(date('j', $timestamp)),
				'hour' => intval(date('G', $timestamp))
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function graph_users_count_delivery_staff($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$con = get_db_con();
	$query = "SELECT COUNT(*) AS user_count,created_at FROM delivery_staff WHERE admin_id LIKE '" . $admin_id . "' AND YEAR(created_at) LIKE '" . $year . "' AND MONTH(created_at) LIKE '" . $month . "' AND DAY(created_at) LIKE '" . $day . "' GROUP BY YEAR(created_at),MONTH(created_at)";

	if ($month != '%') $query .= ",DAY(created_at)";
	if ($day != '%') $query .= ",HOUR(created_at)";

	$query .= " ORDER BY created_at";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$timestamp = strtotime($row['created_at']);

			$output[] = [
				'value' => intval($row['user_count']),
				'month' => intval(date('n', $timestamp)),
				'day' => intval(date('j', $timestamp)),
				'hour' => intval(date('G', $timestamp))
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function graph_orders_count($data)
{
	return [graph_orders_count_completed($data), graph_orders_count_canceled($data)];
}

function graph_orders_count_completed($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	$con = get_db_con();

	$query = "SELECT COUNT(*) AS order_count,states_per_order.created_at AS timestamp FROM states_per_order,order_states,orders WHERE 
	order_states.id=order_state_id AND orders.id=order_id AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) 
	AND state_num=6 AND 
	YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' GROUP BY YEAR(states_per_order.created_at),MONTH(states_per_order.created_at)";

	if ($month != '%') $query .= ",DAY(states_per_order.created_at)";
	if ($day != '%') $query .= ",HOUR(states_per_order.created_at)";

	$query .= " ORDER BY states_per_order.created_at";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$timestamp = strtotime($row['timestamp']);

			$output[] = [
				'value' => intval($row['order_count']),
				'month' => intval(date('n', $timestamp)),
				'day' => intval(date('j', $timestamp)),
				'hour' => intval(date('G', $timestamp))
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function graph_orders_count_canceled($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	$con = get_db_con();

	$query = "SELECT COUNT(*) AS order_count,states_per_order.created_at AS timestamp FROM states_per_order,order_states,orders WHERE 
	order_states.id=order_state_id AND orders.id=order_id AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num='" . ORDER_STATE_CANCELED . "' AND 
	YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' GROUP BY YEAR(states_per_order.created_at),MONTH(states_per_order.created_at)";

	if ($month != '%') $query .= ",DAY(states_per_order.created_at)";
	if ($day != '%') $query .= ",HOUR(states_per_order.created_at)";

	$query .= " ORDER BY states_per_order.created_at";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$timestamp = strtotime($row['timestamp']);

			$output[] = [
				'value' => intval($row['order_count']),
				'month' => intval(date('n', $timestamp)),
				'day' => intval(date('j', $timestamp)),
				'hour' => intval(date('G', $timestamp))
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function graph_orders_price_sum($data)
{
	return [graph_orders_price_sum_completed($data), graph_orders_price_sum_canceled($data)];
}

function graph_orders_price_sum_completed($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	$con = get_db_con();

	$query = "SELECT SUM(total_price) AS price_sum,states_per_order.created_at AS timestamp FROM states_per_order,order_states,orders WHERE 
	order_states.id=order_state_id AND orders.id=order_id AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) 
	AND state_num=6 AND 
	YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' GROUP BY YEAR(states_per_order.created_at),MONTH(states_per_order.created_at)";

	if ($month != '%') $query .= ",DAY(states_per_order.created_at)";
	if ($day != '%') $query .= ",HOUR(states_per_order.created_at)";

	$query .= " ORDER BY states_per_order.created_at";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$timestamp = strtotime($row['timestamp']);

			$output[] = [
				'value' => intval($row['price_sum']),
				'month' => intval(date('n', $timestamp)),
				'day' => intval(date('j', $timestamp)),
				'hour' => intval(date('G', $timestamp))
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function graph_orders_price_sum_canceled($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	$con = get_db_con();

	$query = "SELECT SUM(total_price) AS price_sum,states_pe