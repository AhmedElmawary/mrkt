<?php
ob_start();
require_once('../includes/config.php');
header('Content-Type: application/json; charset=utf-8');

$output = json_encode(handle_payfort($_GET), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

ob_clean();
echo $output;
ob_end_flush();


function handle_payfort($data)
{
	$output = [];
	$con = get_db_con();


	if (!isset($data['token_name'])) {
		if (isset($data['merchant_reference'])) remove_order_by_id(decode_payfort_merchant_reference($data['merchant_reference']));
		return $output;
	}

	$merchant_reference = $data['merchant_reference'];
	$order_id = decode_payfort_merchant_reference($merchant_reference);

	$query = "SELECT first_name,last_name,email,total_price,client_ip FROM user,orders WHERE user_id=user.id AND orders.id='" . $order_id . "'";
	$result = db_query($con, $query);

	if (!$result || db_num_rows($result) != 1) {
		remove_order_by_id($order_id);
		return $output;
	}

	$row = db_fetch_assoc($result);
	db_free_result($result);
	close_db_con($con);

	$data = [
		'command' => PAYFORT_PURCHASE_COMMAND,
		'access_code' => PAYFORT_ACCESS_CODE,
		'merchant_identifier' => PAYFORT_MERCHANT_ID,
		'merchant_reference' => $merchant_reference,
		'language' => PAYFORT_LANGUAGE_CODE,
		'amount' => $row['total_price'] * PAYFORT_CURRENCY_MULTIPLIER,
		'currency' => PAYFORT_CURRENCY_CODE,
		'customer_name' => strtolower($row['first_name']) . '_' . "mrkt",
		'customer_email' => strtolower($row['email']),
		'customer_ip' => $row['client_ip'],
		'token_name' => $data['token_name'],
		'return_url' => PAYFORT_RETURN_URL_PURCHASE
	];
	
	$data['signature'] = generate_payfort_signature($data);



	$data = query_payfort(PAYFORT_API_URL_PURCHASE, $data);

	if (isset($data['3ds_url']) && !empty($data['3ds_url'])) $url = $data['3ds_url'];
	else {
		remove_order_by_id($order_id);
		$url = null;
	}

	return ['url' => $url];
}
