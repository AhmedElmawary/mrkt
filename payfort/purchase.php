<?php

require_once '../includes/config.php';

if ($outputed = handle_payfort($_GET) ) {

    if (strtolower($_GET['response_message']) == "success") {  $redirect = PAYFORT_SUCCESS_URL;
    } else {
          $redirect = PAYFORT_ERROR_URL;
    }
} else {
     $redirect = PAYFORT_ERROR_URL;
}

header('Location: ' . $redirect);


function handle_payfort($data)
{
    $result = false;
    if (!isset($data['merchant_reference'])) { return $result;
    }

    $merchant_reference = $data['merchant_reference'];
    $order_id = decode_payfort_merchant_reference($merchant_reference);
    $payment_method = $data['payment_option'];

    $data = [
    'query_command' => PAYFORT_CHECK_STATUS_COMMAND,
    'access_code' => PAYFORT_ACCESS_CODE,
    'merchant_identifier' => PAYFORT_MERCHANT_ID,
    'merchant_reference' => $merchant_reference,
    'language' => PAYFORT_LANGUAGE_CODE
    ];

    $data['signature'] = generate_payfort_signature($data);

    
    $data = query_payfort(PAYFORT_API_URL_CHECK_STATUS, $data);

    if (!$data || $data['transaction_status'] != PAYFORT_SUCCESS_PAYMENT_CODE) {
        // remove_order_by_id($order_id);
        return $result;
    }

    $con = get_db_con();
    $query = "UPDATE orders SET is_paid='1',payment_method='" . $payment_method . "' WHERE id='" . $order_id . "'";
    $result = db_query($con, $query);
    close_db_con($con);

    return $result;
}
