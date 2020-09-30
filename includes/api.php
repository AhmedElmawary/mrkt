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

$functions = [
	'get_areas',
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
	
	if(isset($data['year'])) $year = $data['year'];
	if(isset($data['month'])) $min_month = $data['month'];
	if(isset($data['day'])) $min_day = $data['day'];
	
	if($min_month == '%')
	{
		$min_month = '01';
		$max_month = '12';
	}
	
	if($min_day == '%')
	{
		$min_day = '01';
		$max_day = '31';
	}
	
	$min_date = $year.'-'.$min_month.'-'.$min_day.' 00:00:00';
	$max_date = $year.'-'.$max_month.'-'.$max_day.' 23:59:59';
	
	if(!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';
	
	$user_count = $shop_count = $avg_total_price = $delivery_staff_count = $delivered_orders_count = $undelivered_orders_count = $total_revenue = $upcoming_revenue = $avg_delivery_time_confirmed = $avg_delivery_time_fetched = 0;
	
	$output = [];
	$con = get_db_con();
	
	$query = "SELECT 
	(SELECT COUNT(*) FROM shop WHERE admin_id LIKE '".$admin_id."') AS shop_count,
	(SELECT COUNT(*) FROM delivery_staff WHERE admin_id LIKE '".$admin_id."') AS delivery_staff_count,
	(SELECT COUNT(*) FROM user) AS user_count,
	
	(SELECT COUNT(*) FROM states_per_order,order_states,orders WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND order_states.id=order_state_id AND orders.id=order_id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num=6) AS delivered_orders_count,
	
	(SELECT COUNT(*) FROM states_per_order,order_states,orders WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND order_states.id=order_state_id AND orders.id=order_id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num NOT IN ('".ORDER_STATE_COMPLETED."','".ORDER_STATE_CANCELED."','".ORDER_STATE_NOT_DELIVERED."')) AS undelivered_orders_count,
	
	(SELECT ROUND(AVG(products_per_order.single_price*products_per_order.quantity),2) FROM products_per_order 
	INNER JOIN orders ON orders.id=products_per_order.order_id 
	INNER JOIN product ON product.id=products_per_order.product_id 
	INNER JOIN shop ON shop.id=product.shop_id 
	WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND is_paid='1' AND shop.admin_id LIKE '".$admin_id."') AS avg_total_price,
	
	(SELECT SUM(products_per_order.single_price*products_per_order.quantity) FROM products_per_order 
	INNER JOIN orders ON orders.id=products_per_order.order_id 
	INNER JOIN product ON product.id=products_per_order.product_id 
	INNER JOIN shop ON shop.id=product.shop_id 
	WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND is_paid='1' AND shop.admin_id LIKE '".$admin_id."') AS total_revenue,
	
	(SELECT SUM(total_price) FROM states_per_order,order_states,orders WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND order_states.id=order_state_id AND orders.id=order_id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num NOT IN ('".ORDER_STATE_COMPLETED."','".ORDER_STATE_CANCELED."','".ORDER_STATE_NOT_DELIVERED."')) AS upcoming_revenue,
	
	(SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND,(SELECT MIN(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND state_num='".ORDER_STATE_CONFIRMED."'),
	(SELECT MAX(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND created_at=(SELECT MAX(created_at) 
	FROM states_per_order WHERE order_id=orders.id) AND state_num=6)))) FROM states_per_order,orders WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND orders.id=order_id) AS avg_delivery_time_confirmed,
	
	(SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND,(SELECT MIN(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND state_num='".ORDER_STATE_FETCHED."'),
	(SELECT MAX(created_at) FROM states_per_order,order_states WHERE order_id=orders.id AND order_states.id=order_state_id AND created_at=(SELECT MAX(created_at) 
	FROM states_per_order WHERE order_id=orders.id) AND state_num=6)))) FROM states_per_order,orders WHERE orders.created_at BETWEEN '".$min_date."' AND '".$max_date."' AND orders.id=order_id) AS avg_delivery_time_fetched";
	
	$result = db_query($con, $query);
	
	if($result && db_num_rows($result) == 1)
	{
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

	$query = "SELECT SUM(total_price) AS price_sum,states_per_order.created_at AS timestamp FROM states_per_order,order_states,orders WHERE 
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
function graph_orders_payment_method($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	$con = get_db_con();

	$query = "SELECT COUNT(*) AS payment_method_count,payment_method FROM states_per_order,order_states,orders WHERE 
	order_states.id=order_state_id AND orders.id=order_id AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num=6  AND 
	YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' GROUP BY payment_method ORDER BY payment_method";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		$sum = 0;

		while ($row = db_fetch_assoc($result)) {
			$sum += $value = intval($row['payment_method_count']);

			$output[] = [
				'label' => $row['payment_method'] . ' (' . $value . ')',
				'data' => $value
			];
		}

		db_free_result($result);
		foreach ($output as &$value) $value['data'] = floatval(($value['data'] * 100) / $sum);
	}

	close_db_con($con);
	return $output;
}

function graph_avg_revenue_per_store($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$con = get_db_con();

	$query = "SELECT AVG(products_per_order.single_price*products_per_order.quantity) AS total_price_avg,shop.name AS shop_name FROM states_per_order,order_states,orders,products_per_order,product,shop WHERE 
	order_states.id=order_state_id AND orders.id=states_per_order.order_id AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id)
	 AND state_num=6 AND 
	YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' AND product_id=product.id AND shop_id=shop.id AND shop.admin_id LIKE '" . $admin_id . "' AND products_per_order.order_id=orders.id 
	GROUP BY shop_id ORDER BY total_price_avg DESC,shop.name ASC";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$output[] = [
				'label' => $row['shop_name'],
				'value' => intval($row['total_price_avg'])
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function graph_revenue_per_store($data)
{
	$data = prepare_inputs($data);
	$year = $month = $day = '%';

	if (isset($data['year'])) $year = $data['year'];
	if (isset($data['month'])) $month = $data['month'];
	if (isset($data['day'])) $day = $data['day'];

	if (!$_SESSION['admin_view_all']) $admin_id = $_SESSION['admin_id'];
	else $admin_id = '%';

	$con = get_db_con();

	$query = "SELECT SUM(products_per_order.single_price*products_per_order.quantity) AS total_price_sum,shop.name AS shop_name FROM states_per_order,order_states,orders,products_per_order,product,shop WHERE 
	order_states.id=order_state_id AND orders.id=states_per_order.order_id AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) 
	AND state_num=6 AND 
	YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' AND product_id=product.id AND shop_id=shop.id AND shop.admin_id LIKE '" . $admin_id . "' AND products_per_order.order_id=orders.id 
	GROUP BY shop_id ORDER BY total_price_sum DESC,shop.name ASC";

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$output[] = [
				'label' => $row['shop_name'],
				'value' => intval($row['total_price_sum'])
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function get_areas($data)
{
	$data = prepare_inputs($data);
	$city_id = '%';

	if (isset($data['city_id'])) $city_id = $data['city_id'];

	$con = get_db_con();
	$query = "SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude,city.name AS city_name FROM area,city WHERE city_id=city.id AND city_id LIKE '" . $city_id . "' ORDER BY city.name,area.name";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$output[] = [
				'id' => intval($row['area_id']),
				'name' => $row['area_name'],
				'zip_code' => $row['zip_code'],
				'latitude' => floatval($row['latitude']),
				'longitude' => floatval($row['longitude']),
				'city_id' => intval($row['city_id']),
				'city_name' => $row['city_name'],
				'express_delivery' => boolval($row['express_delivery'])
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}






// function get_charge($data)
// {
// 	$data = prepare_inputs($data);
// 	$area_id = '%';
// 	$output_2 = [];

// 	 if (isset($data['area_id'])) $area_id = (int) intval($data['area_id']);

// 	$con = get_db_con();

// 	$query_tomorrow_area = 
// 	"SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude , tomorrow	 FROM area  WHERE area.id = ${area_id}  ORDER BY area.name";

// 	 $result_tomorrow_area = db_query($con, $query_tomorrow_area);

// 		 if ($result_tomorrow_area && db_num_rows($result_tomorrow_area)) {

// 			 while ($row = db_fetch_assoc($result_tomorrow_area)) {
// 				 $output_2 = [
// 					 'express_delivery' => boolval($row['express_delivery']) ? true : false ,
// 					 'tomorrow' => boolval($row['tomorrow'])  
// 					//  'tomorrow' => boolval($row['tomorrow']) ? (new DATETIME("tomorrow", new DateTimeZone(TIMEZONE)))->format(DATE_FORMAT) : false 
// 				 ];
// 			 }

// 	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
// 	$service_result = db_query($con, $service_query);
// 	$result_fetch = db_fetch_assoc($service_result);
// 	$charge = $result_fetch['present'] / 100;

// 	$output->service_charge = 0;

// 	if ($service_result && db_num_rows($service_result)) {
// 		$output->service_charge = $charge;
// 		db_free_result($service_result);
// 	}
// 	close_db_con($con);
// 	return $output && $output_2;
// }





function get_charge($data)
{
	$data = prepare_inputs($data);

	$area_id = '%';

	$output_2 = [];

	if (isset($data['area_id'])) $area_id = (int) intval($data['area_id']);
	$area_id = $data["area_id"];

	$con = get_db_con();

	$query_tomorrow_area =
		"SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude , tomorrow	 FROM area  WHERE area.id = ${area_id}  ORDER BY area.name";

	$result_tomorrow_area = db_query($con, $query_tomorrow_area);

	if ($result_tomorrow_area && db_num_rows($result_tomorrow_area)) {

		while ($row = db_fetch_assoc($result_tomorrow_area)) {
			$output_2 = [
				'express_delivery' => boolval($row['express_delivery']) ? true : false,
				'tomorrow' => boolval($row['tomorrow'])
				//  'tomorrow' => boolval($row['tomorrow']) ? (new DATETIME("tomorrow", new DateTimeZone(TIMEZONE)))->format(DATE_FORMAT) : false 
			];
		}
		$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
		$service_result = db_query($con, $service_query);
		$result_fetch = db_fetch_assoc($service_result);
		$charge = $result_fetch['present'] / 100;

		$output->service_charge = 0;

		if ($service_result && db_num_rows($service_result)) {
			$output->service_charge = $charge;
			db_free_result($service_result);
		}
		// return  array_merge($output,  $output_2 );
		// return   $output_2 ;
	}
	close_db_con($con);
	$output->express_delivery = $output_2["express_delivery"];
	$output->tomorrow = $output_2["tomorrow"];
	return $output;
}



# old
// function get_charge($data)
// {
// 	$data = prepare_inputs($data);
// 	$area_id = '%';

// 	if (isset($data['area_id'])) $area_id = $data['area_id'];

// 	$con = get_db_con();
// 	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
// 	$service_result = db_query($con, $service_query);
// 	$result_fetch = db_fetch_assoc($service_result);
// 	$charge = $result_fetch['present'] / 100;
// 	$output->service_charge = 0;

// 	if ($service_result && db_num_rows($service_result)) {
// 		$output->service_charge = $charge;
// 		db_free_result($service_result);
// 	}
// 	close_db_con($con);
// 	return $output;
// }


// function get_charge($data)
// {
// 	$data = prepare_inputs($data);
// 	$area_id = '%';

// 	// if (isset($data['area_id'])) $area_id = (int) intval($data['area_id']);
// 	if (isset($data['area_id'])) $area_id = (int) intval($data['area_id']);

// 	$con = get_db_con();
// 	// $query = "SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude,city.name AS city_name 
// 	// FROM area,city 
// 	// WHERE area.id = {$area_id} 
// 	// ORDER BY city.name,area.name";

// 	$query = "SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude , tomorrow
// FROM area
// WHERE area.id = ${area_id} 
// ORDER BY area.name";

// 	$result = db_query($con, $query);
// 	if ($result == false) {
// 	}
// 	$output = [];

// 	if ($result && db_num_rows($result)) {

// 		while ($row = db_fetch_assoc($result)) {

// 			$output = [
// 				'id' => intval($row['area_id']),
// 				'name' => $row['area_name'],
// 			// 'zip_code' => $row['zip_code'],
// 			// 'latitude' => floatval($row['latitude']),
// 			// 'longitude' => floatval($row['longitude']),
// 			// 'city_id' => intval($row['city_id']),
// 			// 'city_name' => $row['city_name'],
// 				'express_delivery' => boolval($row['express_delivery']) ? true : false ,
// 				'tomorrow' => boolval($row['tomorrow']) ? (new DATETIME("tomorrow", new DateTimeZone(TIMEZONE)))->format(DATE_FORMAT) : false 
// 			];
// 		}

// 		db_free_result($result);
// 	}
// 	close_db_con($con);
// 	return $output;
// }





#old
// function get_charge($data)
// {
// 	$data = prepare_inputs($data);
// 	$area_id = '%';

// 	if (isset($data['area_id'])) $area_id = $data['area_id'];

// 	$con = get_db_con();
// 	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
// 	$service_result = db_query($con, $service_query);
// 	$result_fetch = db_fetch_assoc($service_result);
// 	$charge = $result_fetch['present'] / 100;
// 	$output->service_charge = 0;

// 	if ($service_result && db_num_rows($service_result)) {
// 		$output->service_charge = $charge;
// 		db_free_result($service_result);
// 	}
// 	close_db_con($con);
// 	return $output;
// }


function get_categories($data)
{
	$data = prepare_inputs($data);
	$area_id = $shop_id = $featured = '%';

	if (isset($data['area_id'])) $area_id = $data['area_id'];
	if (isset($data['store_id'])) $shop_id = $data['store_id'];
	if (isset($data['featured'])) $featured = (int) $data['featured'];

	$con = get_db_con();
	$query = "SELECT category_id,category.name AS cat_name,category.order_num AS cat_order_num,category.featured_image AS cat_featured_image,category.is_featured AS category_featured 
	FROM category,shop,product WHERE category.id=category_id AND shop.id=shop_id AND category.is_featured LIKE '" . $featured . "' AND shop_id LIKE '" . $shop_id . "' AND area_id LIKE '" . $area_id . "' 
	GROUP BY category_id ORDER BY category.is_featured DESC,category.order_num ASC,category.name ASC";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$id = $row['category_id'];
			$featured_image = $row['cat_featured_image'];

			if (is_file(CATEGORY_UPLOAD_DIR . $id . '/' . $featured_image)) $featured_image = CMS_BASE . CATEGORY_UPLOAD_DIR . $id . THUMBS_DIR . $featured_image;
			else $featured_image = null;

			$output[] = [
				'id' => intval($id),
				'name' => $row['cat_name'],
				'order' => intval($row['cat_order_num']),
				'featured' => boolval($row['category_featured']),
				'featured_image' => $featured_image
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function get_stores($data)
{
	$data = prepare_inputs($data);
	$area_id = $shop_type_id = '%';

	if (isset($data['area_id'])) $area_id = $data['area_id'];
	if (isset($data['store_type_id'])) $shop_type_id = $data['store_type_id'];

	$con = get_db_con();
	$query = "SELECT shop.id AS shop_id,shop.name AS shop_name,area_id,shop_type_id,area.name AS area_name,city_id,city.name AS city_name,shop_type.name AS shop_type_name,zip_code,express_delivery,featured_image,
	front_image,shop.latitude AS shop_latitude,shop.longitude AS shop_longitude FROM shop,city,area,shop_type WHERE city_id=city.id AND area_id=area.id AND shop_type_id=shop_type.id AND shop.active='1' 
	AND area_id LIKE '" . $area_id . "' AND shop_type_id LIKE '" . $shop_type_id . "' ORDER BY shop.name";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$id = $row['shop_id'];
			$featured_image = $row['featured_image'];
			$front_image = $row['front_image'];

			if (is_file(SHOP_UPLOAD_DIR . $id . '/' . $featured_image)) $featured_image = CMS_BASE . SHOP_UPLOAD_DIR . $id . THUMBS_DIR . $featured_image;
			else $featured_image = null;

			if (is_file(SHOP_UPLOAD_DIR . $id . '/' . $front_image)) $front_image = CMS_BASE . SHOP_UPLOAD_DIR . $id . THUMBS_DIR . $front_image;
			else $front_image = null;

			$output[] = [
				'id' => intval($id),
				'name' => $row['shop_name'],
				'type_id' => intval($row['shop_type_id']),
				'type_name' => $row['shop_type_name'],
				'area_id' => intval($row['area_id']),
				'area_name' => $row['area_name'],
				'zip_code' => $row['zip_code'],
				'city_id' => intval($row['city_id']),
				'city_name' => $row['city_name'],
				'express_delivery' => boolval($row['express_delivery']),
				'latitude' => floatval($row['shop_latitude']),
				'longitude' => floatval($row['shop_longitude']),
				'featured_image' => $featured_image,
				'front_image' => $front_image
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function get_products($data)
{
	set_time_limit(0);
	ini_set("memory_limit", "-1");
	$data = prepare_inputs($data);

	$area_id = $shop_id = $supplier_id = $category_id = $featured = $promoted = $out_of_stock = $search_string = '%';
	$offset = $limit = 0;
	$products = '';

	if (isset($data['area_id'])) $area_id = $data['area_id'];
	if (isset($data['store_id'])) $shop_id = $data['store_id'];
	if (isset($data['supplier_id'])) $supplier_id = $data['supplier_id'];
	if (isset($data['category_id'])) $category_id = $data['category_id'];
	if (isset($data['featured'])) $featured = (int) $data['featured'];
	if (isset($data['promoted'])) $promoted = (int) $data['promoted'];
	if (isset($data['out_of_stock'])) $out_of_stock = (int) $data['out_of_stock'];
	if (isset($data['products']) && !empty($data['products'])) $products = "AND product.id IN (" . implode(',', $data['products']) . ")";
	if (isset($data['offset'])) $offset = $data['offset'];
	if (isset($data['limit'])) $limit = $data['limit'];

	if (isset($data['search_string'])) {
		$search_chars = ['_', '%', ' '];
		$replace_chars = ["\_", "\%", '%'];

		$search_string = '%' . str_replace($search_chars, $replace_chars, $data['search_string']) . '%';
	}

	$con = get_db_con();

	$query = "SELECT product.id AS product_id,product.name AS product_name,shop_id,shop.name AS shop_name,supplier_id,supplier.name AS supplier_name,category_id,category.name AS category_name,
	product.featured AS product_featured,product.promoted AS product_promoted,out_of_stock,price,price_discount,quantity,special_feature,description,product.featured_image AS product_image,shop.featured_image AS featured_image_shop,
	shop.front_image AS front_image_shop,supplier.featured_image AS featured_image_supplier,category.featured_image AS featured_image_category,product.created_at AS product_timestamp 
	FROM product,shop,supplier,category,area WHERE product.shop_id=shop.id AND area_id=area.id AND 
	product.supplier_id=supplier.id AND product.category_id=category.id AND product.shop_id LIKE '" . $shop_id . "' AND product.supplier_id LIKE '" . $supplier_id . "' AND product.category_id LIKE '" . $category_id . "' AND 
	shop.area_id LIKE '" . $area_id . "' AND product.featured LIKE '" . $featured . "' AND product.promoted LIKE '" . $promoted . "' AND product.out_of_stock LIKE '" . $out_of_stock . "' AND product.active='1' 
	AND shop.active='1' AND supplier.active='1' " . $products . " AND (supplier.name LIKE '" . $search_string . "' OR product.name LIKE '" . $search_string . "') ORDER BY product.promoted DESC,product.featured DESC,product.name ASC";

	if (!empty($limit)) $query .= " LIMIT " . $offset . "," . $limit;

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		$user_id = get_jwt_field('user_id');

		while ($row = db_fetch_assoc($result)) {
			$id = $row['product_id'];
			$featured_image = $row['product_image'];

			#old
			if (is_file(PRODUCT_UPLOAD_DIR . $id . '/' . $featured_image)) $featured_image = CMS_BASE . PRODUCT_UPLOAD_DIR . $id . THUMBS_DIR . $featured_image;
			else $featured_image = null;

			//  $featured_image = CMS_BASE . "images/Mrkt-close.png";

			if ($featured_image && SHOW_IMAGELESS_PRODUCTS) continue;

			$shop_id = $row['shop_id'];
			$featured_image_shop = $row['featured_image_shop'];
			$front_image_shop = $row['front_image_shop'];

			if (is_file(SHOP_UPLOAD_DIR . $shop_id . '/' . $featured_image_shop)) $featured_image_shop = CMS_BASE . SHOP_UPLOAD_DIR . $shop_id . THUMBS_DIR . $featured_image_shop;
			else $featured_image_shop = null;

			if (is_file(SHOP_UPLOAD_DIR . $shop_id . '/' . $front_image_shop)) $front_image_shop = CMS_BASE . SHOP_UPLOAD_DIR . $shop_id . THUMBS_DIR . $front_image_shop;
			else $front_image_shop = null;

			$supplier_id = $row['supplier_id'];
			$featured_image_supplier = $row['featured_image_supplier'];

			if (is_file(SUPPLIER_UPLOAD_DIR . $supplier_id . '/' . $featured_image_supplier)) $featured_image_supplier = CMS_BASE . SUPPLIER_UPLOAD_DIR . $supplier_id . THUMBS_DIR . $featured_image_supplier;
			else $featured_image_supplier = null;

			$category_id = $row['category_id'];
			$featured_image_category = $row['featured_image_category'];

			if (is_file(CATEGORY_UPLOAD_DIR . $category_id . '/' . $featured_image_category)) $featured_image_category = CMS_BASE . CATEGORY_UPLOAD_DIR . $category_id . THUMBS_DIR . $featured_image_category;
			else $featured_image_category = null;

			$query = "SELECT id FROM favorite_products WHERE product_id='" . $id . "' AND user_id='" . $user_id . "'";
			$result1 = db_query($con, $query);

			if ($result1 && db_num_rows($result1) == 1) $has_user_favorited = true;
			else $has_user_favorited = false;

			$output[] = [
				'id' => intval($id),
				'name' => $row['product_name'],
				'price' => floatval($row['price']),
				'price_discount' => floatval($row['price_discount']),
				'quantity' => $row['quantity'],
				'special_feature' => $row['special_feature'],
				'description' => $row['description'],
				'featured' => boolval($row['product_featured']),
				'promoted' => boolval($row['product_promoted']),
				'out_of_stock' => boolval($row['out_of_stock']),
				'store_id' => intval($shop_id),
				'store_name' => $row['shop_name'],
				'supplier_id' => intval($supplier_id),
				'supplier_name' => $row['supplier_name'],
				'category_id' => intval($category_id),
				'category_name' => $row['category_name'],
				'has_user_favorited' => $has_user_favorited,
				'featured_image_shop' => $featured_image_shop,
				'front_image_shop' => $front_image_shop,
				'featured_image_supplier' => $featured_image_supplier,
				'featured_image_category' => $featured_image_category,
				'featured_image' => $featured_image,
				'created_at' => $row['product_timestamp']
			];



			// $output[] = [
			// 	'id' => intval($id),
			// 	'name' => " ",
			// 	'price' => 0.0,
			// 	'price_discount' => 0.0,
			// 	'quantity' => " ",
			// 	'special_feature' => $row['special_feature'],
			// 	'description' => $row['description'],
			// 	'featured' => boolval($row['product_featured']),
			// 	'promoted' => boolval($row['product_promoted']),
			// 	'out_of_stock' => boolval($row['out_of_stock']),
			// 	'store_id' => intval($shop_id),
			// 	'store_name' => $row['shop_name'],
			// 	'supplier_id' => intval($supplier_id),
			// 	'supplier_name' => $row['supplier_name'],
			// 	'category_id' => intval($category_id),
			// 	'category_name' => $row['category_name'],
			// 	'has_user_favorited' => $has_user_favorited,
			// 	'featured_image_shop' => $featured_image_shop,
			// 	'front_image_shop' => $front_image_shop,
			// 	'featured_image_supplier' => $featured_image_supplier,
			// 	'featured_image_category' => $featured_image_category,
			// 	'featured_image' => $featured_image,
			// 	'created_at' => $row['product_timestamp']
			// ];
		}
		db_free_result($result);
	}
	close_db_con($con);
	return $output;
}

function product_toggle_favorite($data)
{
	$data = prepare_inputs($data);
	$product_id = $data['id'];
	$user_id = get_jwt_field('user_id');

	$output = [];

	$con = get_db_con();
	$query = "SELECT id FROM favorite_products WHERE user_id='" . $user_id . "' AND product_id='" . $product_id . "'";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$query = "DELETE FROM favorite_products WHERE id='" . $row['id'] . "'";
		$result = db_query($con, $query);
	} else {
		$query = "INSERT INTO favorite_products(user_id,product_id) VALUES('" . $user_id . "','" . $product_id . "')";
		$result = db_query($con, $query);
	}

	close_db_con($con);
	return get_favorite_products();
}

function get_favorite_products()
{
	$user_id = get_jwt_field('user_id');
	$output = [];

	$con = get_db_con();
	$query = "SELECT product_id FROM favorite_products WHERE user_id='" . $user_id . "'";
	$result = db_query($con, $query);

	if (!$result || !db_num_rows($result)) return $output;
	$output = ['products' => []];

	while ($row = db_fetch_assoc($result)) $output['products'][] = $row['product_id'];
	db_free_result($result);

	close_db_con($con);
	return get_products($output);
}

function get_lists()
{
	$user_id = get_jwt_field('user_id');
	$output = [];

	$con = get_db_con();
	$query = "SELECT * FROM shop_lists WHERE user_id='" . $user_id . "' ORDER BY created_at DESC";
	$result = db_query($con, $query);

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$id = $row['id'];
			$products = [];

			$query = "SELECT product_id,quantity FROM products_per_shop_list WHERE shop_list_id='" . $id . "'";
			$result1 = db_query($con, $query);

			if ($result1 && db_num_rows($result1)) {
				while ($row1 = db_fetch_assoc($result1)) {
					$products[] = [
						'id' => intval($row1['product_id']),
						'quantity' => intval($row1['quantity'])
					];
				}

				db_free_result($result1);
			}

			$output[] = [
				'id' => intval($id),
				'name' => $row['name'],
				'created_at' => $row['created_at'],
				'products' => $products
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function save_list($data)
{
	$data = prepare_inputs($data);
	$user_id = get_jwt_field('user_id');
	$name = $data['name'];
	$products = $data['products'];

	$con = get_db_con();

	$query = "INSERT INTO shop_lists(name,user_id) VALUES('" . $name . "','" . $user_id . "')";
	if (DB_DRIVER_PREFIX == 'pg') $query .= " RETURNING id";

	$result = db_query($con, $query);

	if (DB_DRIVER_PREFIX == 'mysqli') $shop_lists_id = db_insert_id($con);
	else if (DB_DRIVER_PREFIX == 'pg') {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$shop_lists_id = $row['id'];
	}

	foreach ($products as $product) {
		$id = $product['id'];
		$quantity = $product['quantity'];

		$query = "INSERT INTO products_per_shop_list(shop_list_id,product_id,quantity) VALUES('" . $shop_lists_id . "','" . $id . "','" . $quantity . "')";
		$result = db_query($con, $query);
	}

	if (isset($data['id'])) $output = remove_list($data);
	else $output = get_lists();

	close_db_con($con);
	return $output;
}

function remove_list($data)
{
	$data = prepare_inputs($data);
	$user_id = get_jwt_field('user_id');
	$id = $data['id'];

	$con = get_db_con();

	$query = "DELETE FROM products_per_shop_list WHERE shop_list_id='" . $id . "'";
	$result = db_query($con, $query);

	$query = "DELETE FROM shop_lists WHERE id='" . $id . "' AND user_id='" . $user_id . "'";
	$result = db_query($con, $query);

	close_db_con($con);
	return get_lists();
}


function edit_order_products($data)
{
	$result = null;
	$query = null;
	$new_price = 0;
	$order_id = 0;
	$product_id = 0;
	$single_price = 0;
	$quantity = 0;
	$product_ids = [];
	$store_id = 0;
	$transaction = [];


	if ((!empty($data["order_id"])) &&
		(!empty($data["new_price"])) &&
		(!empty($data["single_price"]))
	) {

		$con = get_db_con();
		$single_price	= htmlentities($data["single_price"], ENT_NOQUOTES, "UTF-8");
		$order_id   = htmlentities($data["order_id"], ENT_NOQUOTES, "UTF-8");
		$new_price  = htmlentities($data["new_price"], ENT_NOQUOTES, "UTF-8");
		$product_id  = htmlentities($data["product_id"], ENT_NOQUOTES, "UTF-8");

		$query = "UPDATE products_per_order SET single_price = ${new_price}  WHERE product_id = ${product_id} AND order_id = ${order_id} AND single_price = ${single_price} ";
		$result = db_query($con, $query);
		mysqli_error($con);
	}
	if (!empty($result)) $transaction = ["message" => "price updated successfully"];
	else $transaction = ["message" => "error"];


	return  $transaction;
}
function get_orders()
{
	$user_data = get_jwt_field();
	$user_id = $user_data['user_id'];

	if ($user_data['is_delivery_staff']) $user_field = 'delivery_staff_id';
	else $user_field = 'user_id';

	$con = get_db_con();
	$query = "SELECT orders.id AS order_id,is_paid,delivery_staff_id,total_price, total_price_update_on,delivery_time,payment_method,notes,rating_number,rating_message,orders.created_at AS order_timestamp,order_states.name AS order_state_name,state_num,is_express_delivery,
	city_id,city.name AS city_name,area.id AS area_id,area.name AS area_name,first_name,last_name,user.email AS user_email,user.phone AS user_phone,states_per_order.created_at AS change_state_time,(CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,(CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number FROM orders,order_states,states_per_order,user,city,area
	WHERE  (orders.created_at)  >=  date_add(current_timestamp() , INTERVAL -5 DAY) AND user.id=user_id AND city.id=area.city_id AND 
	order_states.id=order_state_id AND orders.id=order_id AND " . $user_field . "='" . $user_id . "' AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) ORDER BY orders.created_at DESC";

	//var_dump($query);exit;
	// $query = "SELECT orders.id AS order_id,is_paid,delivery_staff_id,total_price,delivery_time,payment_method,notes,rating_number,rating_message,orders.created_at AS order_timestamp,order_states.name AS order_state_name,state_num,is_express_delivery,
	// city_id,city.name AS city_name,area.id AS area_id,area.name AS area_name,first_name,last_name,user.email AS user_email,user.phone AS user_phone,states_per_order.created_at AS change_state_time,(CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,(CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number FROM orders,order_states,states_per_order,user,city,area
	// WHERE  DAY(orders.created_at) >= '30'  AND  MONTH(orders.created_at) >= '03' AND YEAR(orders.created_at) = YEAR(CURRENT_DATE()) AND user.id=user_id AND city.id=area.city_id AND 
	// order_states.id=order_state_id AND orders.id=order_id AND ".$user_field."='".$user_id."' AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id AND 
	// states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) ORDER BY orders.created_at DESC";
	// //var_dump($query);exit;

	$result = db_query($con, $query);
	$output = [];

	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$order_id = $row['order_id'];
			$delivery_staff_id = $row['delivery_staff_id'];
			$products = [];
			$price = [];
			/* Coupon ADD */
			$coupons = [];
			$cou_or_id = $row['order_id'];
			$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";
			$co_orders = db_query($con, $get_offers);
			$cou_orders = db_fetch_assoc($co_orders);
			$id_id = $cou_orders['coupon_id'];
			$get_co = "SELECT * FROM coupons WHERE id=$id_id";
			$co_get = db_query($con, $get_co);
			$coupon_get = db_fetch_assoc($co_get);
			$present = (float) $coupon_get['present'];
			$total = 0;
			//*********************************** */


			/* SERVICE ADD */
			$service_query = "SELECT * FROM charge WHERE id=1";
			$service_result = db_query($con, $service_query);
			$result_fetch = db_fetch_assoc($service_result);
			$charge = $result_fetch['present'] / 100;

			/************ Lat,Long */
			$lat_query = "SELECT latitude,longitude FROM orders WHERE id=$order_id";
			$lat_result = db_query($con, $lat_query);
			$lat_fetch = db_fetch_assoc($lat_result);
			/************************/
			/* Store Price Update */
			$price_query = "SELECT * FROM order_update WHERE order_id=$order_id";
			$price_result = db_query($con, $price_query);
			$price_fetch = mysqli_fetch_all($price_result, MYSQLI_ASSOC);
			/**********************************/
			$delivery_staff_name = $delivery_staff_email = $delivery_staff_phone = null;

			$query = "SELECT full_name,email,phone FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
			$result1 = db_query($con, $query);

			if ($result1 && db_num_rows($result1) == 1) {
				$row1 = db_fetch_assoc($result1);
				db_free_result($result1);

				$delivery_staff_name = $row1['full_name'];
				$delivery_staff_email = strtolower($row1['email']);
				$delivery_staff_phone = $row1['phone'];
			}

			if (empty($row["total_price_update_on"])) {

				$query_2 = "SELECT order_id ,store_id,price FROM order_update WHERE order_id='" . $order_id . "' order by id desc ";
				$result_2 = db_query($con, $query_2);

				$result_2_fetchted = mysqli_fetch_all($result_2, MYSQLI_ASSOC);
				// $store_name_query = "SELECT name FROM shop WHERE id =";

				foreach ($result_2_fetchted as $key => $value) {
					$store_name_query = "SELECT name,featured_image,front_image FROM shop WHERE id ={$value['store_id']}";

					$res = mysqli_query($con, $store_name_query);
					$res = mysqli_fetch_assoc($res);
					$price[] =  $price_fetch[$key]["price"] = (float) $value["price"];
					$price_fetch[$key]["store_name"] =  $res['name'];
					// $price_fetch[$key]["store_front_image"] =  $res['front_image'];
					// $price_fetch[$key]["store_back_image"] =  $res['featured_image'];
				}

				$price = (float) array_sum($price);
				/**********************************/
				if ($row['is_express_delivery'] == 1) $delivery_fee = DELIVERY_FEE_EXPRESS;
				else $delivery_fee = DELIVERY_FEE;

				$total =  (float) $price + $delivery_fee + ($price * $charge) - $present;

				$query_3 = "UPDATE orders SET total_price=${total} WHERE id=${order_id}";
				$result_3 = mysqli_query($con, $query_3);
			}

			$query = "SELECT * FROM products_per_order WHERE order_id='" . $order_id . "' ORDER BY is_fetched DESC";

			$result1 = db_query($con, $query);

			if ($result1 && db_num_rows($result1)) {
				while ($row1 = db_fetch_assoc($result1)) {
					$products[] = [
						'id' => intval($row1['product_id']),
						'quantity' => intval($row1['quantity']),
						'single_price' => floatval($row1['single_price']),
						'is_fetched' => boolval($row1['is_fetched'])
					];
				}

				if ($total !== 0) {
					$row["total_price"] = $total;
				}

				db_free_result($result1);
			}

			$output[] = [
				'id' => intval($order_id),
				'total_price' => floatval($row['total_price']),
				'delivery_time' => $row['delivery_time'],
				'payment_method' => $row['payment_method'],
				'is_paid' => boolval($row['is_paid']),
				'is_express_delivery' => boolval($row['is_express_delivery']),
				'delivery_fee'=>(float) $delivery_fee,
				'user_name' => $row['first_name'] . ' ' . $row['last_name'],
				'user_email' => strtolower($row['user_email']),
				'user_phone' => $row['user_phone'],
				'city_id' => intval($row['city_id']),
				'city_name' => $row['city_name'],
				'area_id' => intval($row['area_id']),
				'area_name' => $row['area_name'],
				'longitude' => $lat_fetch['longitude'],
				'latitude' => $lat_fetch['latitude'],
				'street' => $row['street'],
				'house_number' => $row['house_number'],
				'order_state_num' => intval($row['state_num']),
				'order_state_name' => $row['order_state_name'],
				'delivery_staff_name' => $delivery_staff_name,
				'delivery_staff_email' => $delivery_staff_email,
				'delivery_staff_phone' => $delivery_staff_phone,
				'rating_number' => intval($row['rating_number']),
				'notes' => $row['notes'],
				'rating_message' => $row['rating_message'],
				'created_at' => $row['order_timestamp'],
				'change_state_time' => $row['change_state_time'],
				'products' => $products,
				'coupon_present' => (float)$present,
				'coupon' => $coupon_get,
				'service_charge' => $charge,
				'store_price_update' => $price_fetch
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}




#new 
// function get_orders()
// {
// 	$user_data = get_jwt_field();
// 	$user_id = $user_data['user_id'];
// 	if ($user_data['is_delivery_staff']) $user_field = 'delivery_staff_id';
// 	else $user_field = 'user_id';

// 	$con = get_db_con();
// 	$query = "SELECT orders.id AS order_id,is_paid,delivery_staff_id,total_price, total_price_update_on,delivery_time,payment_method,notes,rating_number,rating_message,orders.created_at AS order_timestamp,order_states.name AS order_state_name,state_num,is_express_delivery,
// 	city_id,city.name AS city_name,area.id AS area_id,area.name AS area_name,first_name,last_name,user.email AS user_email,user.phone AS user_phone,states_per_order.created_at AS change_state_time,(CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,(CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number FROM orders,order_states,states_per_order,user,city,area
//     WHERE YEAR(orders.created_at) = YEAR(CURRENT_DATE()) AND user.id=user_id AND city.id=area.city_id AND 
// 	order_states.id=order_state_id AND orders.id=order_id AND " . $user_field . "='" . $user_id . "' AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id AND 
// 	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) ORDER BY orders.created_at DESC";
// 	//var_dump($query);exit;
// 	$result = db_query($con, $query);
// 	$output = [];

// 	if ($result && db_num_rows($result)) {
// 		while ($row = db_fetch_assoc($result)) {
// 			$order_id = $row['order_id'];
// 			$delivery_staff_id = $row['delivery_staff_id'];
// 			$area_id = $row['charge_area_id'];
// 			$products = [];
// 			/* Coupon ADD */
// 			$coupons = [];
// 			$cou_or_id = $row['order_id'];
// 			$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";
// 			$co_orders = db_query($con, $get_offers);
// 			$cou_orders = db_fetch_assoc($co_orders);
// 			$id_id = $cou_orders['coupon_id'];
// 			$get_co = "SELECT * FROM coupons WHERE id=$id_id";
// 			$co_get = db_query($con, $get_co);
// 			$coupon_get = db_fetch_assoc($co_get);
// 			$coup_id = $coupon_get['id'];
// 			$coup_present = $coupon_get['present'];
// 			$present = $coup_present;

// 			if (!isset($present) || !($present > 0)) {
// 			  $present = 0;
// 		  }
// 			/*****************************/
// 				/* SERVICE ADD */
// 			$service_query = "SELECT * FROM charge WHERE charge_area_id='" . $area_id . "' LIMIT 1";
// 			$service_result = db_query($con, $service_query);
// 			$result_fetch = db_fetch_assoc($service_result);
// 			$charge = $result_fetch['present'] / 100;
// 			/**********************************/
// 			/************ Lat,Long */
// 			$lat_query = "SELECT latitude,longitude FROM orders WHERE id=$order_id";
// 			$lat_result = db_query($con, $lat_query);
// 			$lat_fetch = db_fetch_assoc($lat_result);
// 			/************************/
// 			/* Store Price Update */
// 			$price_query = "SELECT store_id , price as total_store_price FROM order_update WHERE order_id=$order_id";
// 			$price_result = db_query($con, $price_query);
// 			$price_fetch = mysqli_fetch_all($price_result, MYSQLI_ASSOC);
// 			/**********************************/
// 			$delivery_staff_name = $delivery_staff_email = $delivery_staff_phone = null;

// 			$query = "SELECT full_name,email,phone FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
// 			$result1 = db_query($con, $query);

// 			if ($result1 && db_num_rows($result1) == 1) {
// 				$row1 = db_fetch_assoc($result1);
// 				db_free_result($result1);

// 				$delivery_staff_name = $row1['full_name'];
// 				$delivery_staff_email = strtolower($row1['email']);
// 				$delivery_staff_phone = $row1['phone'];
// 			}

// 			$query = "SELECT * FROM products_per_order WHERE order_id='" . $order_id . "' ORDER BY is_fetched DESC";
// 			$result1 = db_query($con, $query);

// 			if ($result1 && db_num_rows($result1)) {
// 				while ($row1 = db_fetch_assoc($result1)) {
// 					$products[] = [
// 						'id' => intval($row1['product_id']),
// 						'quantity' => intval($row1['quantity']),
// 						'single_price' => floatval($row1['single_price']),
// 						'is_fetched' => boolval($row1['is_fetched'])
// 					];
// 				}

// 				db_free_result($result1);
// 			}



// 		if ( boolval($row["total_price_update_on"]) == false  ){
// 				$new_total = [];
// 			foreach ($price_fetch as $key => $value ){
// 					foreach ($value as $key => $value )
// 							if($key == "total_store_price")
// 								$new_total[] = (float) $value;	
// 			}

// 				$total_price = array_sum($new_total) ;


// 				if (boolval($row['is_express_delivery'])) $delivery_fee = DELIVERY_FEE_EXPRESS;
// 				else $delivery_fee = DELIVERY_FEE;

// 				if($charge > 0 )
// 				$total_price_new = $total_price * $charge - $present + $delivery_fee ;
// 				else
// 					$total_price_new = $total_price  - $present + $delivery_fee ;

// 					$query = "UPDATE orders SET total_price=${total_price_new} WHERE id='" . $order_id . "'";
// 					$result = db_query($con, $query);

// 					$query = "SELECT total_price FROM orders  WHERE id= ${order_id} ";
// 					$result = db_query($con, $query);
// 					$row = mysqli_fetch_assoc($result);
// 			}
// 			$output[] = [
// 				'id' => intval($order_id),
// 				'total_price' => floatval( $row['total_price']),
// 				'delivery_time' => $row['delivery_time'],
// 				'payment_method' => $row['payment_method'],
// 				'is_paid' => boolval($row['is_paid']),
// 				'is_express_delivery' => boolval($row['is_express_delivery']),
// 				'user_name' => $row['first_name'] . ' ' . $row['last_name'],
// 				'user_email' => strtolower($row['user_email']),
// 				'user_phone' => $row['user_phone'],
// 				'city_id' => intval($row['city_id']),
// 				'city_name' => $row['city_name'],
// 				'area_id' => intval($row['area_id']),
// 				'area_name' => $row['area_name'],
// 				'longitude' => $lat_fetch['longitude'],
// 				'latitude' => $lat_fetch['latitude'],
// 				'street' => $row['street'],
// 				'house_number' => $row['house_number'],
// 				'order_state_num' => intval($row['state_num']),
// 				'order_state_name' => $row['order_state_name'],
// 				'delivery_staff_name' => $delivery_staff_name,
// 				'delivery_staff_email' => $delivery_staff_email,
// 				'delivery_staff_phone' => $delivery_staff_phone,
// 				'rating_number' => intval($row['rating_number']),
// 				'notes' => $row['notes'],
// 				'rating_message' => $row['rating_message'],
// 				'created_at' => $row['order_timestamp'],
// 				'change_state_time' => $row['change_state_time'],
// 				'products' => $products,
// 				'coupon' => $coupon_get,
// 				'service_charge' => $charge,
// 				'store_price_update' => $price_fetch
// 			];
// 		}

// 		db_free_result($result);
// 	}

// 	close_db_con($con);
// 	return $output;
// }



function save_order($data)
{

	$data = prepare_inputs($data);

	$notes = $area_id = $street = $house_number = $latitude = $longitude = 'NULL';
	$is_express_delivery = 0;
	$order_id = null;
	$user_id = get_jwt_field('user_id');
	$delivery_time = $data['delivery_time'];
	$payment_method = $data['payment_method'];
	if (isset($data['express_delivery'])) $is_express_delivery = intval($data['express_delivery']);
	if (isset($data['notes'])) $notes = "'" . $data['notes'] . "'";
	if (isset($data['area_id'])) $area_id = "'" . $data['area_id'] . "'";
	if (isset($data['street'])) $street = "'" . $data['street'] . "'";
	if (isset($data['house_number'])) $house_number = "'" . $data['house_number'] . "'";
	if (isset($data['latitude'])) $latitude = "'" . $data['latitude'] . "'";
	if (isset($data['longitude'])) $longitude = "'" . $data['longitude'] . "'";
	$products = $data['products'];
	$client_ip = $_SERVER['REMOTE_ADDR'];
	$coupon_title = $data['coupon_title'];

	$con = get_db_con();

	$query = "INSERT INTO orders(delivery_time,payment_method,user_id,notes,area_id,street,house_number,client_ip,is_express_delivery,latitude,longitude) 
	VALUES('" . $delivery_time . "','" . $payment_method . "','" . $user_id . "'," . $notes . "," . $area_id . "," . $street . "," . $house_number . ",'" . $client_ip . "','" . $is_express_delivery . "'," . $latitude . "," . $longitude . ")";

	if (DB_DRIVER_PREFIX == 'pg') $query .= " RETURNING id";
	$result = db_query($con, $query);

	if (DB_DRIVER_PREFIX == 'mysqli') $order_id = db_insert_id($con);
	else if (DB_DRIVER_PREFIX == 'pg') {
		$row = db_fetch_assoc($result);
		db_free_result($result);
		$order_id = $row['id'];
	}
	$query = "INSERT INTO states_per_order(order_id,order_state_id) VALUES('" . $order_id . "',(SELECT id FROM order_states WHERE state_num=(SELECT MIN(state_num) FROM order_states)))";
	$result = db_query($con, $query);

	$prices_ar = [];

	foreach ($products as $product) {

		$id = $product['id'];
		$price = "SELECT (CASE price_discount WHEN 0 THEN price ELSE price_discount END) FROM product WHERE id= ${id} ";
		$res = db_query($con, $price);
		$price = db_fetch_row($res)[0];

		$quantity = $product['quantity'];
		$query = "INSERT INTO products_per_order(order_id,product_id,single_price,quantity) 
		VALUES('" . $order_id . "','" . $id . "', $price,'" . $quantity . "')";
		$result = db_query($con, $query);

		$query = "SELECT shop_id FROM  product  where id =${id}";
		$result = db_query($con, $query);
		$store_ids[$id] = db_fetch_row($result)[0];
		$prices_ar[$store_ids[$id]][] = (float) $price * $quantity;
	}


	foreach ($prices_ar as $store_id => $value) {
		if (is_array($value)) $prices_ar[$store_id] = array_sum($value);

		$query = "INSERT INTO order_update(store_id,order_id,price) VALUES( ${store_id} , ${order_id}, {$prices_ar[$store_id]} )";
		$result = db_query($con, $query);
	}

	if ($is_express_delivery) $delivery_fee = DELIVERY_FEE_EXPRESS;
	else $delivery_fee = DELIVERY_FEE;
	/*      Coupon  Query   */
	$counpon_quary = "SELECT * FROM coupons WHERE title='$coupon_title'";
	$coupon_res = db_query($con, $counpon_quary);
	$result_coup = db_fetch_assoc($coupon_res);
	$coup_id = $result_coup['id'];
	$coup_present = $result_coup['present'];
	$present = $coup_present;
	if (!isset($present) || !($present > 0)) {
		$present = 0;
	}
	/***********************************/
	// make it for the new total price of whole order
	/* SERVICE Chrage */
	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id . " LIMIT 1";
	$service_result = db_query($con, $service_query);
	$result_fetch = db_fetch_assoc($service_result);
	$charge = $result_fetch['present'] / 100;
	/**************************/
	$query = "UPDATE orders SET total_price=((SELECT SUM(((single_price+(single_price*$charge))*quantity))-$present FROM products_per_order WHERE order_id='" . $order_id . "')+" . $delivery_fee . ") WHERE id='" . $order_id . "'";
	$result = db_query($con, $query);

	if ($result == true) {
		$use_create = "INSERT INTO coupuser (coupon_id,user_id,order_id) VALUES ('" . $coup_id . "','" . $user_id . "','" . $order_id . "')";
		db_query($con, $use_create);
	}

	close_db_con($con);


	return ['id' => encode_payfort_merchant_reference($order_id)];
}




/**
 * function update_shop_order change the total of a specific order 
 * @param String[] data store_id Order_id  new_price to be chaged 
 *  @return String[] info success or fail 
 */

function update_shop_order($data): array
{
	$result = null;
	$query = null;
	$new_price = 0;
	$order_id = 0;
	$store_id = 0;
	$transaction = [];

	if ((!empty($data["store_id"])) &&
		(!empty($data["new_price"])) &&
		(!empty($data["order_id"]))
	) {
		$con = get_db_con();
		$store_id	= htmlentities($data["store_id"], ENT_NOQUOTES, "UTF-8");
		$order_id   = htmlentities($data["order_id"], ENT_NOQUOTES, "UTF-8");
		$new_price  = htmlentities($data["new_price"], ENT_NOQUOTES, "UTF-8");

		$query = "UPDATE order_update SET price = ${new_price}  WHERE order_id = ${order_id} AND store_id = ${store_id} ";
		$result = db_query($con, $query);

		$query = "UPDATE orders SET 	total_price_update_on = false  WHERE id = ${order_id} ";
		$result = db_query($con, $query);
	}

	if (!empty($result)) $transaction = ["message" => "price updated successfully"];
	else $transaction = ["message" => "error"];

	return  $transaction;
}


// function save_order($data)
// {
// 	$data = prepare_inputs($data);


// 	$notes = $area_id = $street = $house_number = $latitude = $longitude = 'NULL';
// 	$is_express_delivery = 0;
// 	$order_id = null;
// 	$user_id = get_jwt_field('user_id');
// 	$delivery_time = $data['delivery_time'];
// 	$payment_method = $data['payment_method'];
// 	if (isset($data['express_delivery'])) $is_express_delivery = intval($data['express_delivery']);
// 	if (isset($data['notes'])) $notes = "'" . $data['notes'] . "'";
// 	if (isset($data['area_id'])) $area_id = "'" . $data['area_id'] . "'";
// 	if (isset($data['street'])) $street = "'" . $data['street'] . "'";
// 	if (isset($data['house_number'])) $house_number = "'" . $data['house_number'] . "'";
// 	if (isset($data['latitude'])) $latitude = "'" . $data['latitude']