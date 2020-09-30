<?php

use PHPMailer\PHPMailer\PHPMailer;

if(session_status() == PHP_SESSION_NONE) { session_start();
}

require_once 'db.php';


$con = get_db_con();
$query = "SELECT * FROM options";
$result = db_query($con, $query);
$row = db_fetch_assoc($result);
db_free_result($result);

define('APP_NAME', $row['app_name']);
define('APP_EMAIL', $row['app_email']);
define('URL_PROTOCOL', $row['url_protocol']);
define('TIMEZONE', $row['timezone']);
define('SMS', $row['sms']);
define('DATE_FORMAT', $row['date_format']);
define('TIME_FORMAT', $row['time_format']);
define('DIR_CREATE_FLAGS', octdec($row['dir_create_flags']));
define('CURRENCY_CODE', $row['currency_code']);
define('GOOGLE_API_KEY', $row['google_api_key']);
define('MIN_PASS_LEN', intval($row['min_pass_len']));
define('MAX_RATE_VALUE', intval($row['max_rate_value']));
define('DELIVERY_FEE', intval($row['delivery_fee']));
define('DELIVERY_FEE_EXPRESS', intval($row['delivery_fee_express']));
define('VAT', intval($row['vat']));
define('OPENING_TIME', intval($row['opening_time']));
define('CLOSING_TIME', intval($row['closing_time']));
define('SHOW_IMAGELESS_PRODUCTS', boolval($row['show_imageless_products']));
define('API_LIST_MODE', intval($row['api_list_mode']));
define('API_IP_LIST', $row['api_ip_list']);
define('JWT_SECRET_KEY', $row['jwt_secret_key']);
define('JWT_EXPIRATION_TIME', intval($row['jwt_expiration_time']));
define('PAYFORT_API_URL_TOKENIZATION', $row['payfort_api_url_tokenization']);
define('PAYFORT_API_URL_PURCHASE', $row['payfort_api_url_purchase']);
define('PAYFORT_API_URL_CHECK_STATUS', $row['payfort_api_url_check_status']);
define('PAYFORT_API_URL_REFUND', $row['payfort_api_url_refund']);
define('PAYFORT_TOKENIZATION_COMAND', $row['payfort_tokenization_command']);
define('PAYFORT_PURCHASE_COMMAND', $row['payfort_purchase_command']);
define('PAYFORT_CHECK_STATUS_COMMAND', $row['payfort_check_status_command']);
define('PAYFORT_REFUND_COMMAND', $row['payfort_refund_command']);
define('PAYFORT_MERCHANT_ID', $row['payfort_merchant_id']);
define('PAYFORT_ACCESS_CODE', $row['payfort_access_code']);
define('PAYFORT_LANGUAGE_CODE', $row['payfort_language_code']);
define('PAYFORT_CURRENCY_CODE', $row['payfort_currency_code']);
define('PAYFORT_CURRENCY_MULTIPLIER', pow(10, $row['payfort_currency_multiplier']));
define('PAYFORT_SUCCESS_PAYMENT_CODE', $row['payfort_success_payment_code']);
define('PAYFORT_SUCCESS_REFUND_CODE', $row['payfort_success_refund_code']);
define('PAYFORT_RETURN_URL_TOKENIZATION', $row['payfort_return_url_tokenization']);
define('PAYFORT_RETURN_URL_PURCHASE', $row['payfort_return_url_purchase']);
define('PAYFORT_SUCCESS_URL', $row['payfort_success_url']);
define('PAYFORT_ERROR_URL', $row['payfort_error_url']);
define('PAYFORT_SHA_ALGO', $row['payfort_sha_algo']);
define('PAYFORT_SHA_PHRASE', $row['payfort_sha_phrase']);
define('MAX_UPLOAD_SIZE', intval($row['max_upload_size']));
define('CROP_W_LANDSCAPE', intval($row['thumb_width_landscape']));
define('CROP_W_PORTRAIT', intval($row['thumb_width_portrait']));
define('IMAGE_FILTER', $row['image_filter']);
define('VIDEO_FILTER', $row['video_filter']);
define('REGISTRATION_ACTIVE', boolval($row['registration_active']));
define('REGISTRATION_ROLE_ID', intval($row['registration_role_id']));
define('MAINTENANCE_MODE', boolval($row['maintenance_mode']));
define('MAINTENANCE_TEXT', $row['maintenance_text']);

if(isset($_SESSION['admin_id'])) {
    $query = "SELECT view_all FROM `admin`,role WHERE role_id=role.id AND `admin`.id='".$_SESSION['admin_id']."' AND `admin`.active='1'";
    $result = db_query($con, $query);
    
    if(!$result || db_num_rows($result) != 1 || MAINTENANCE_MODE) {
        header('Location: logout');
        exit;
    }
    
    $row = db_fetch_assoc($result);
    db_free_result($result);
    
    $_SESSION['admin_view_all'] = boolval($row['view_all']);
}

close_db_con($con);

define('CMS_BASE', "http://".$_SERVER['HTTP_HOST'] .'/mrkt/');
// define('CMS_BASE', 'http://'.$_SERVER['SERVER_NAME'].'/');
define('HASH_ALGO', 'sha512');
define('DELIMITER', ';');
define('FILENAME_GLUE', '_');
define('PAYFORT_GLUE', '-');
define('DATETIME_FORMAT', DATE_FORMAT.' '.TIME_FORMAT);
define('ITEMS_PER_PAGE', 1000);
define('API_LIST_MODE_OFF', 0);
define('API_LIST_MODE_WHITE', 1);
define('API_LIST_MODE_BLACK', 2);


 define('SMTP_PROTOCOL', 'tls'); // ssl or tls
 define('SMTP_PORT', 587); // 465 or 587
 define('SMTP_HOST', 'email-smtp.us-east-2.amazonaws.com');
 define('SMTP_USER', 'AKIAVTYUUV5W4KA35COE');
 define('SMTP_PASS', 'BCN8YqUsOrWNxRoHAm1XVUeoPu5SlchKjm/Ic6ViKmf1');

  // old  orignal
// define('SMTP_PROTOCOL', 'tls'); // ssl or tls
// define('SMTP_PORT', 587); // 465 or 587
// define('SMTP_HOST', 'email-smtp.eu-west-1.amazonaws.com');
// define('SMTP_USER', 'AKIAIQS57O55XTIQAGSQ');
// define('SMTP_PASS', 'AqWovu41oVHVJV6XtIEvCcxo8ySW7omSJ1uHLG5JgU44');

define('ORDER_STATE_CREATED', 0);
define('ORDER_STATE_ASSIGNED', 1);
define('ORDER_STATE_CONFIRMED', 2);
define('ORDER_STATE_FETCHED', 3);
define('ORDER_STATE_CANCELED', 4);
define('ORDER_STATE_NOT_DELIVERED', 5);
define('ORDER_STATE_COMPLETED', 6);
define('ORDER_STATE_SENDMAIL', 7);

define('ARCHIVE_EXTRACT_DIR', 'media/tmp/');
define('PRODUCT_UPLOAD_DIR', 'media/products/');
define('SUPPLIER_UPLOAD_DIR', 'media/suppliers/');
define('SHOP_UPLOAD_DIR', 'media/shops/');
define('CATEGORY_UPLOAD_DIR', 'media/categories/');
define('THUMBS_DIR', '/thumbs/');

define('PRODUCT_IMPORT_FILTER', 'csv');
define('ARCHIVE_FILTER', 'zip');
define('MEDIA_FILTER', IMAGE_FILTER.DELIMITER.VIDEO_FILTER);

require_once 'jwt.php';
require_once 'payfort-utils.php';
require_once __DIR__.'/../vendor/autoload.php';




function format_money($value)
{
    return number_format($value, 2, ',', '.');
}

function format_date($date = 'now', $format = DATETIME_FORMAT)
{
    return (new DateTime($date))->setTimezone(new DateTimeZone(TIMEZONE))->format($format);
}

function send_mail($to_email, $to_name, $from_email, $from_name, $subject = '', $text = '', $images = [], $files = [], $inline_files = [])
{
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->AllowEmpty = true;

    $mail->addAddress($to_email, $to_name);
    $mail->setFrom($from_email, $from_name);
    $mail->addReplyTo($from_email, $from_name);
    
    $mail->Subject = $subject;
    // $mail->Body = nl2br($text);
    // $mail->AltBody = strip_tags($text);
    $mail->Body = $text;
    $mail->AltBody = $text;

    foreach($images as $image) { $mail->AddEmbeddedImage($image['path'], $image['tag'], basename($image['path']));
    }
    foreach($files as $file) { $mail->addAttachment($file['path'], basename($file['path']));
    }
    foreach($inline_files as $file) { $mail->addStringAttachment($file['data'], $file['name']);
    }

    return sendMailSecure($mail) ? true : $mail->send();
}

function sendMailSecure($mail)
{
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = SMTP_PROTOCOL;
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    
    return $mail->send();
}
