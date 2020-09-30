<?php

function generate_payfort_signature($data, $excluded = [])
{
	$signiture = "";

	foreach($excluded as $key)
	{
		if(isset($data[$key])) unset($data[$key]);
	}
	
	ksort($data);
	// foreach($data as $key => &$value) $value = $key.'='.$value;
	foreach($data as $key => $value) $signiture .=  "$key=$value";

	$signiture = "mrkt1$" . $signiture . "mrkt1$";

	
	
	// $data = implode('', $data);
	// return hash(PAYFORT_SHA_ALGO, PAYFORT_SHA_PHRASE.$data.PAYFORT_SHA_PHRASE);
	return hash("sha512", $signiture);

}


function query_payfort($url, $data = null, $json = true)
{
	$options = [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_FOLLOWLOCATION => false
	];
	
	if(!empty($data))
	{
		if($json)
		{
			$data = json_encode(utf8_encode_array($data), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json; charset=utf-8'];
		}
		else $data = http_build_query($data);
		
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $data;
	}

	$ch = curl_init();
	curl_setopt_array($ch, $options);
	$response = curl_exec($ch);

	if($response && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) $response = json_decode($response, true);
	else parse_str(parse_url(curl_getinfo($ch, CURLINFO_REDIRECT_URL), PHP_URL_QUERY), $response);

	curl_close($ch);
	return $response;
}

function payfort_refund($order_id)
{
	$con = get_db_con();
	$query = "SELECT total_price,payment_method FROM orders WHERE is_paid='1' AND id='".$order_id."'";
	$result = db_query($con, $query);
	
	if(!$result || db_num_rows($result) != 1) return false;
	
	$row = db_fetch_assoc($result);
	db_free_result($result);
	
	if($row['payment_method'] != 'cash')
	{
		$data = [
			'command' => PAYFORT_REFUND_COMMAND,
			'access_code' => PAYFORT_ACCESS_CODE,
			'merchant_identifier' => PAYFORT_MERCHANT_ID,
			'merchant_reference' => encode_payfort_merchant_reference($order_id),
			'language' => PAYFORT_LANGUAGE_CODE,
			'amount' => $row['total_price'] * PAYFORT_CURRENCY_MULTIPLIER,
			'currency' => PAYFORT_CURRENCY_CODE
		];
		
		$data['signature'] = generate_payfort_signature($data);
		$data = query_payfort(PAYFORT_API_URL_REFUND, $data);
		
		if(!$data || $data['status'] != PAYFORT_SUCCESS_REFUND_CODE) return false;
	}
	
	$query = "UPDATE orders SET is_paid='0' WHERE id='".$order_id."'";
	$result = db_query($con, $query);
	
	close_db_con($con);
	return $result;
}

function encode_payfort_merchant_reference($order_id)
{
	$created_at = 0;
	$con = get_db_con();
	
	$query = "SELECT created_at FROM orders WHERE id='".$order_id."'";
	$result = db_query($con, $query);
	
	if($result && db_num_rows($result) == 1)
	{
		$row = db_fetch_assoc($result);
		db_free_result($result);
		
		$created_at = strtotime($row['created_at']);
	}
	
	close_db_con($con);
	
	$merchant_reference = [$order_id, $created_at];
	return implode(PAYFORT_GLUE, $merchant_reference);
}

function decode_payfort_merchant_reference($merchant_reference)
{
	$merchant_reference = explode(PAYFORT_GLUE, $merchant_reference);
	return $merchant_reference[0];
}

function remove_order_by_id($order_id)
{
	$con = get_db_con();
	
	$query = "DELETE FROM states_per_order WHERE order_id='".$order_id."'";
	$result = db_query($con, $query);
	
	$query = "DELETE FROM products_per_order WHERE order_id='".$order_id."'";
	$result = db_query($con, $query);
	
	$query = "DELETE FROM orders WHERE id='".$order_id."'";
	$result = db_query($con, $query);
	
	close_db_con($con);
}

function utf8_encode_array($data)
{
	if(is_string($data)) return utf8_encode(trim($data));
	else if(is_array($data))
	{
		foreach($data as &$value) $value = utf8_encode_array($value);
	}
	
	return $data;
}

function notify_delivery_staff($state_per_order_id)
{
	try {
	$con = get_db_con();
	$query = "SELECT order_id,delivery_staff_id,order_states.name AS order_state_name FROM states_per_order,order_states WHERE order_state_id=order_states.id AND states_per_order.id='".$state_per_order_id."'";
	$result = db_query($con, $query);
	$row = db_fetch_assoc($result);
	db_free_result($result);
	close_db_con($con);
	
	$body = 'Order #'.$row['order_id'].' is now in '.strtoupper($row['order_state_name']).' state.';
	
	$data = [
		'title' => 'Order update',
		'body' => $body,
		'sound' => 'default',
		'data' => json_encode(['body' => $body], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES)
	];
	
	$expo = new ExpoPushHandler;
	return $expo->send($row['delivery_staff_id'], $data);	
	} catch (Exception $e) {
		
	}
	
}
