<?php

require_once("get_headers.php");

use  \Firebase\JWT\JWT;

ob_start();

function get_orders()
{
	
	try {
			JWT::decode( trim(strstr(apache_request_headers()["Authorization"], ' ')), JWT_SECRET_KEY,["HS256"]);
	} catch (Exception $e) {
		return
			[
				"code" => 418,
				"message" => $e->getMessage()
			];
	}

	$con = get_db_con();
	$query = "SELECT orders.id AS order_id,is_paid,delivery_staff_id,total_price, 

    total_price_update_on,delivery_time,payment_method,notes,
    
    rating_number,rating_message,orders.created_at AS order_timestamp,
    
    order_states.name AS order_state_name,state_num,is_express_delivery,
    
    city_id,city.name AS city_name,area.id AS area_id,area.name AS area_name,first_name,
    
    last_name,user.email AS user_email, user.phone AS user_phone,
    
    states_per_order.created_at AS change_state_time,
    
    (CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,
    
    (CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number 
    
FROM orders, order_states, states_per_order, user, city, area
WHERE  city.id = area.city_id
AND user_id = user.id
AND orders.id = states_per_order.order_id
AND order_state_id = order_states.id
-- AND states_per_order.created_at between '2020-01-25' AND '2020-11-30'
AND orders.area_id = area.id
AND orders.area_id = ANY(SELECT id from area where city_id=3)
AND user.area_id = area.id
AND user.area_id = ANY(SELECT id from area where city_id=3)
AND states_per_order.created_at = (SELECT MAX(created_at) FROM states_per_order where orders.id = order_id)";

	$result = db_query($con, $query);

	$orders_count_query= "SELECT count(orders.id) 
    
	FROM orders, order_states, states_per_order, user, city, area
	WHERE  city.id = area.city_id
	AND user_id = user.id
	AND orders.id = states_per_order.order_id
	AND order_state_id = order_states.id
	AND orders.area_id = area.id
	AND orders.area_id = ANY(SELECT id from area where city_id=3)
	AND user.area_id = area.id
	AND user.area_id = ANY(SELECT id from area where city_id=3)
	AND states_per_order.created_at = (SELECT MAX(created_at) FROM states_per_order where orders.id = order_id)";
	$orders_count=mysqli_fetch_row(mysqli_query($con, $orders_count_query))[0];
	
	// $output[0]= "orders_count:".$orders_count;
	$output[]= ["orders"=> $orders_count];
	if ($result && db_num_rows($result)) {
		while ($row = db_fetch_assoc($result)) {
			$order_id = $row['order_id'];
			$delivery_staff_id = $row['delivery_staff_id'];
			$products = [];
			$price = [];

			/* Coupon ADD */
			$coupons = [];
			$cou_or_id = (int)  $row['order_id'];
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
				'delivery_fee' => (float) $delivery_fee,
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
				'coupon_present' => (float) $present,
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


$output = json_encode(get_orders());

ob_end_flush();


echo ($output);
