<?php

require_once 'includes/config.php';
set_time_limit(0);

$functions = [
    'login_form',
    'register_form',
    'forgot_password_form',
    'change_order_state',
    'remove_order',
    'refund_order',
    'add_shop',
    'remove_shop',
    'activate_shop',
    'deactivate_shop',
    'add_shop_type',
    'remove_shop_type',
    'add_supplier',
    'remove_supplier',
    'activate_supplier',
    'deactivate_supplier',
    'add_category',
    'remove_category',
    'add_order_state',
    'remove_order_state',
    'add_delivery_staff',
    'remove_delivery_staff',
    'activate_delivery_staff',
    'deactivate_delivery_staff',
    'change_delivery_staff_area',
    'add_city',
    'remove_city',
    'add_area',
    'remove_area',
    'add_admin',
    'remove_admin',
    'activate_admin',
    'deactivate_admin',
    'add_function',
    'remove_function',
    'add_role',
    'remove_role',
    'add_user',
    'remove_user',
    'activate_user',
    'deactivate_user',
    'add_product',
    'remove_product',
    'activate_product',
    'deactivate_product',
    'add_product_import',
    'remove_product_import',
    'add_options',
    'purge_db_tables',
    "mrkt_exporter",
    "backup_db"
];

$redirect = 'index';

foreach ($functions as $function) {
    if (array_key_exists($function, $_POST) && function_exists($function)) {
        $redirect = call_user_func($function);
        break;
    }
}

if ($redirect) {
    header('Location: ' . $redirect);
}

function login_form()
{

    $username = addslashes(trim($_POST['username']));
    $password = hash(HASH_ALGO, trim($_POST['password']));

    $con = get_db_con();
    $query = "SELECT * FROM `admin` WHERE (BINARY username='" . $username . "' OR email='" . $username . "') AND password='" . $password . "' AND active='1'";
    $result = db_query($con, $query);

    if (!$result || db_num_rows($result) != 1) {
        return 'logout';
    }

    $row = db_fetch_assoc($result);
    db_free_result($result);
    close_db_con($con);

    $data = [
        'admin_id' => $row['id'],
        'admin_name' => $row['username'],
        'admin_full' => $row['full_name'],
        'admin_email' => $row['email'],
        'admin_role_id' => $row['role_id'],
    ];

    update_session($data);
    return 'admin';
}

function register_form()
{
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'] = hash(HASH_ALGO, trim($_POST['password']));
    $role_id = $_POST['role_id'];

    $fields = $_POST['expected_fields'];
    $id = query_fields('admin', $fields);

    if (!$id) {
        return 'logout';
    }

    $data = [
        'admin_id' => $id,
        'admin_name' => $username,
        'admin_full' => $full_name,
        'admin_email' => $email,
        'admin_role_id' => $role_id,
    ];

    update_session($data);
    return 'admin';
}

function forgot_password_form()
{
    $email = addslashes(strtolower(trim($_POST['email'])));
    $password = substr(md5(mt_rand()), 0, MIN_PASS_LEN);
    $db_pass = hash(HASH_ALGO, $password);

    $con = get_db_con();
    $query = "UPDATE admin SET password='" . $db_pass . "' WHERE email='" . $email . "'";
    $result = db_query($con, $query);
    close_db_con($con);

    if (!$result) {
        return 'index';
    }

    $text = "
		Hi,

		Your new password is: <b>" . $password . "</b>
		Login URL: <a href=\"" . CMS_BASE . "\" target=\"_blank\">" . CMS_BASE . "</a>

		Best regards,
		" . APP_NAME . "
	";

    send_mail($email, '', APP_EMAIL, APP_NAME, 'Password reset', $text);
    return 'index';
}

function update_session($data)
{
    foreach ($data as $key => $value) {
        $_SESSION[$key] = $value;
    }
}


function mrkt_exporter()
{
    $exporter_value = $_POST['mrkt_exporter'];
    $con = get_db_con();
    $query = "SELECT 
	orders.id AS order_id,
    total_price,delivery_time,payment_method,is_paid,orders.created_at AS order_timestamp,
    order_states.name AS order_state_name,city.name AS city_name,area.name AS area_name , 
    CONCAT(user.first_name,' ', user.last_name) as user_name,
    coupon_id
    FROM orders
    JOIN user on user.id=orders.user_id 
    JOIN states_per_order  on orders.id=states_per_order.order_id
    JOIN area  on (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id 
    JOIN city ON city_id=city.id 
    JOIN order_states on   order_states.id = states_per_order.order_state_id 
	JOIN coupuser ON coupuser.order_id = orders.id
    AND coupuser.order_id LIKE '%'
    AND states_per_order.created_at = (SELECT max(created_at) from states_per_order where orders.id= order_id)
    AND order_states.id = 4 
    WHERE orders.created_at LIKE'".$exporter_value."%'";
    $orders = mysqli_fetch_all(mysqli_query($con, $query), MYSQLI_ASSOC);
    
    header("Content-Type: application/csv");
    header("Content-Disposition: attachment; filename = orders.".date("Y-m-d H:i:s").".csv");
   
    foreach ($orders as $key => $order) {
        $coupun_query = "SELECT title, present, coupon_id
                        FROM  coupuser, orders,coupons
                        WHERE  order_id =orders.id
                        AND   coupon_id = coupons.id
                        AND  coupon_id = {$order['coupon_id']}
                        AND   order_id = {$order['order_id']}";
        
        $coupun_result = mysqli_query($con, $coupun_query);
        $coupun = mysqli_fetch_assoc($coupun_result);
       
        $order["coupon_id"] = $coupun["coupon_id"];
        if (empty(intval($order['coupon_id']))) $order['coupon_id'] = "";
        $order['coupun']      =    $coupun["title"];
        $order['coupun_value']=    $coupun["present"] ;

        $products_query =   "SELECT name, shop_id
                            FROM product, products_per_order, orders
                            WHERE product.id = products_per_order.product_id
                            AND orders.id = order_id
                            AND order_id = {$order['order_id']}";

        $products_result = mysqli_query($con, $products_query);
        $products = mysqli_fetch_all($products_result);

        $shops_ids=[];
        $temp_products='';
        foreach ($products as $product) {
            array_push($shops_ids, $product[1]);

            $temp_products .= ' ('.utf8_encode($product[0]).")";
        }
        $shops_ids = array_unique($shops_ids);

        $shops_query =   "SELECT name
                            FROM shop
                            WHERE id in (".implode(",", $shops_ids).")
                            GROUP BY id";

        $shops_result = mysqli_query($con, $shops_query);
        $shops = mysqli_fetch_all($shops_result);
        $temp_shops='';
        foreach ($shops as $shop) {
            $temp_shops.= "(".$shop[0].') ';
        }
        $order["shops"]= $temp_shops;
        $order["products"]= $temp_products;
        $orders[$key] = $order;
    }

    $headers
        = [
            "Order ID",
            "Total Price",
            "Deliver At",
            "Payment Method",
            "Paid",
            "Order Created At",
            "Order statuts",
            "City",
            "Area",
            "User Name",
            "Coupon ID",
            "Coupon",
            "Coupon Discount",
            "Order's Shops",
            "Order's Products"
        ];
        
    $stream = fopen("php://output", "w");
    fputcsv($stream, $headers);
    foreach ($orders as $order) {
            $order["is_paid"] = $order["is_paid"] == true ? "yes" : "no";
            fputcsv($stream, $order);
    }
    fclose($stream);
}

function add_shop()
{
    $id = $_POST['id'];
    $result = query_fields('shop', $_POST['expected_fields'], $id);

    if ($result) {
        if (!$id) {
            $id = $result;
        }

        update_record_media('shop', $id, SHOP_UPLOAD_DIR, $_FILES);
    }

    return 'admin/stores';
}

function remove_shop()
{
    $values = $_POST['values'];

    foreach ($values as $id) {
        remove_dir(SHOP_UPLOAD_DIR . $id);
        remove_images(SHOP_UPLOAD_DIR, $id);
    }

    delete_records('shop', $values);
    return 'admin/stores';
}

function activate_shop()
{
    toggle_records('shop', $_POST['values'], 1);
    return 'admin/stores';
}

function deactivate_shop()
{
    toggle_records('shop', $_POST['values'], 0);
    return 'admin/stores';
}

function add_shop_type()
{
    $result = query_fields('shop_type', $_POST['expected_fields'], $_POST['id']);
    return 'admin/stores/types';
}

function remove_shop_type()
{
    delete_records('shop_type', $_POST['values']);
    return 'admin/stores/types';
}

function add_supplier()
{
    $id = $_POST['id'];
    $result = query_fields('supplier', $_POST['expected_fields'], $id);
    if ($result) {
        if (!$id) {
            $id = $result;
        }

        update_record_media('supplier', $id, SUPPLIER_UPLOAD_DIR, $_FILES);
    }

    return 'admin/suppliers';
}

function remove_supplier()
{
    $values = $_POST['values'];

    foreach ($values as $id) {
        remove_dir(SUPPLIER_UPLOAD_DIR . $id);
        remove_images(SUPPLIER_UPLOAD_DIR, $id);
    }

    delete_records('supplier', $values);
    return 'admin/suppliers';
}

function activate_supplier()
{
    toggle_records('supplier', $_POST['values'], 1);
    return 'admin/suppliers';
}

function deactivate_supplier()
{
    toggle_records('supplier', $_POST['values'], 0);
    return 'admin/suppliers';
}

function add_product()
{
    $id = $_POST['id'];
    $result = query_fields('product', $_POST['expected_fields'], $id);

    if ($result) {
        if (!$id) {
            $id = $result;
        }

        update_record_media('product', $id, PRODUCT_UPLOAD_DIR, $_FILES);
    }

    return 'admin/products';
}

function add_product_import()
{
    $import_id = query_fields('product_import_log', $_POST['expected_fields']);
    if (!$import_id) {
        return 'admin/products/imports';
    }

    $files = $_FILES['input_files'];
    $out_of_stock = $featured = $promoted = $active = 0;

    if (isset($_POST['out_of_stock'])) {
        $out_of_stock = $_POST['out_of_stock'];
    }

    if (isset($_POST['featured'])) {
        $featured = $_POST['featured'];
    }

    if (isset($_POST['promoted'])) {
        $promoted = $_POST['promoted'];
    }

    if (isset($_POST['active'])) {
        $active = $_POST['active'];
    }

    $static_fields = [
        'shop_id' => $_POST['shop_id'],
        'admin_id' => $_POST['admin_id'],
        'out_of_stock' => $out_of_stock,
        'featured' => $featured,
        'promoted' => $promoted,
        'active' => $active,
        'import_id' => $import_id,
    ];

    $zip_dest = ARCHIVE_EXTRACT_DIR . time() . '/';
    $extensions = explode(DELIMITER, PRODUCT_IMPORT_FILTER);

    process_archives($_FILES['images_archives'], $zip_dest);

    foreach ($files['error'] as $key => $error) {
        if ($error != UPLOAD_ERR_OK) {
            continue;
        }

        $ext = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));
        $func = 'parse_' . $ext;

        if (!in_array($ext, $extensions) || !function_exists($func)) {
            continue;
        }

        $result = call_user_func($func, $files['tmp_name'][$key]);
        if ($result && is_array($result)) {
            handle_product_db_import($result, $static_fields, $zip_dest . 'files/');
        }
    }

    remove_dir($zip_dest);
    // return 'admin/products/imports';
    return 'admin/products/';
}

function extract_zip_archive($file, $dest)
{
    $zip = new ZipArchive;
    if (!$zip->open($file)) {
        return false;
    }

    if (!is_dir($dest)) {
        mkdir($dest, DIR_CREATE_FLAGS, true);
    }

    $status = $zip->extractTo($dest);
    $zip->close();

    return $status;
}

function process_archives($files, $dest)
{
    $extensions = explode(DELIMITER, ARCHIVE_FILTER);
    if (!is_dir($dest . 'files/')) {
        mkdir($dest . 'files/', DIR_CREATE_FLAGS, true);
    }

    foreach ($files['error'] as $key => $error) {
        if ($error != UPLOAD_ERR_OK) {
            continue;
        }

        $filename = str_replace(' ', FILENAME_GLUE, strtolower(trim($files['name'][$key])));
        if (!in_array(pathinfo($filename, PATHINFO_EXTENSION), $extensions)) {
            continue;
        }

        move_uploaded_file($files['tmp_name'][$key], $dest . $filename);
        extract_zip_archive($dest . $filename, $dest . 'files/');
    }
}

function parse_csv($file)
{
    $output = [];
    $file = fopen($file, 'rb');
    if (!$file) {
        return $output;
    }

    while ($data = fgetcsv($file)) {
        $output[] = $data;
    }

    fclose($file);
    return $output;
}

function handle_product_db_import($data, $static_fields, $media_dir)
{
    $static_fields = prepare_inputs($static_fields);
    $mandatory_fields = [0, 1, 2, 3];

    $shop_id = $static_fields['shop_id'];
    $admin_id = $static_fields['admin_id'];
    $out_of_stock = $static_fields['out_of_stock'];
    $featured = $static_fields['featured'];
    $promoted = $static_fields['promoted'];
    $active = $static_fields['active'];
    $import_id = $static_fields['import_id'];

    $con = get_db_con();

    foreach ($data as &$record) {
        $status = true;
        $record = prepare_inputs($record);

        foreach ($mandatory_fields as $key) {
            if (empty($record[$key])) {
                $status = false;
                break;
            }
        }

        if (!$status) {
            continue;
        }

        $product_name = $record[0];
        $supplier_id = check_or_insert('supplier', $record[1]);
        $category_id = check_or_insert('category', $record[2]);
        $price = floatval($record[3]);
        $price_discount = floatval($record[4]);
        $quantity = $record[5];
        $description = $record[6];

        $query = "INSERT INTO product(name,shop_id,admin_id,supplier_id,category_id,price,price_discount,quantity,description,out_of_stock,featured,promoted,active,import_id)
		VALUES('" . $product_name . "','" . $shop_id . "','" . $admin_id . "','" . $supplier_id . "','" . $category_id . "','" . $price . "','" . $price_discount . "','" . $quantity . "','" . $description . "',
		'" . $out_of_stock . "','" . $featured . "','" . $promoted . "','" . $active . "','" . $import_id . "')";

        if (DB_DRIVER_PREFIX == 'pg') {
            $query .= " RETURNING id";
        }

        $result = db_query($con, $query);

        if ($result) {
            if (DB_DRIVER_PREFIX == 'mysqli') {
                $id = db_insert_id($con);
            } else if (DB_DRIVER_PREFIX == 'pg') {
                $row = db_fetch_assoc($result);
                db_free_result($result);
                $id = $row['id'];
            }

            handle_import_image($id, $record[7], $media_dir);
        }
    }

    close_db_con($con);
}

function handle_import_image($record_id, $image_id, $media_dir)
{
    if (empty($image_id)) {
        return false;
    }

    $image = null;
    $extensions = explode(DELIMITER, IMAGE_FILTER);

    foreach ($extensions as $ext) {
        $ext_up = strtoupper($ext);

        if (is_file($media_dir . $image_id . '.' . $ext)) {
            $image = $media_dir . $image_id . '.' . $ext;
            break;
        } else if (is_file($media_dir . $image_id . '.' . $ext_up)) {
            $image = $media_dir . $image_id . '.' . $ext_up;
            break;
        }
    }

    if (!$image || filesize($image) > MAX_UPLOAD_SIZE) {
        return false;
    }

    $media_dir = PRODUCT_UPLOAD_DIR . $record_id;
    $media_dir_thumb = $media_dir . THUMBS_DIR;

    if (!is_dir($media_dir_thumb)) {
        mkdir($media_dir_thumb, DIR_CREATE_FLAGS, true);
    }

    $new_image_name = str_replace(' ', FILENAME_GLUE, strtolower(basename($image)));

    $new_image = $media_dir . '/' . $new_image_name;
    $new_image_thumb = $media_dir_thumb . $new_image_name;

    $status = rename($image, $new_image);
    if (!$status) {
        return false;
    }

    $con = get_db_con();
    $query = "INSERT INTO media(name,upload_dir,record_id) VALUES('" . $new_image_name . "','" . PRODUCT_UPLOAD_DIR . "','" . $record_id . "')";
    $result = db_query($con, $query);
    close_db_con($con);

    make_thumb($new_image, $new_image_thumb);
    return update_featured_image('product', $record_id, $new_image_name);
}

function check_or_insert($table, $value, $field = 'name')
{
    $id = null;

    $con = get_db_con();
    $query = "SELECT id FROM " . $table . " WHERE " . $field . "='" . $value . "' LIMIT 1";
    $result = db_query($con, $query);

    if ($result && db_num_rows($result)) {
        $row = db_fetch_assoc($result);
        db_free_result($result);

        $id = $row['id'];
    } else {
        $query = "INSERT INTO " . $table . "(" . $field . ") VALUES('" . $value . "')";
        if (DB_DRIVER_PREFIX == 'pg') {
            $query .= " RETURNING id";
        }

        $result = db_query($con, $query);

        if ($result) {
            if (DB_DRIVER_PREFIX == 'mysqli') {
                $id = db_insert_id($con);
            } else if (DB_DRIVER_PREFIX == 'pg') {
                $row = db_fetch_assoc($result);
                db_free_result($result);
                $id = $row['id'];
            }
        }
    }

    close_db_con($con);
    return $id;
}

function remove_product($values = [])
{
    if (!$values) {
        $values = $_POST['values'];
    }

    foreach ($values as $id) {
        remove_dir(PRODUCT_UPLOAD_DIR . $id);
        remove_images(PRODUCT_UPLOAD_DIR, $id);
    }

    delete_records('products_per_shop_list', $values, 'product_id');
    delete_records('favorite_products', $values, 'product_id');
    delete_records('product', $values);

    return 'admin/products';
}

function remove_product_import()
{
    $values = [];
    $imports = $_POST['values'];

    $con = get_db_con();

    foreach ($imports as $id) {
        $query = "SELECT id FROM product WHERE import_id='" . $id . "'";
        $result = db_query($con, $query);

        while ($row = db_fetch_assoc($result)) {
            $values[] = $row['id'];
        }

        db_free_result($result);
    }

    close_db_con($con);

    remove_product($values);
    delete_records('product_import_log', $imports);

    return 'admin/products/imports';
}

function activate_product()
{
    toggle_records('product', $_POST['values'], 1);
    return 'admin/products';
}

function deactivate_product()
{
    toggle_records('product', $_POST['values'], 0);
    return 'admin/products';
}

function add_category()
{
    $id = $_POST['id'];
    $result = query_fields('category', $_POST['expected_fields'], $id);

    if ($result) {
        if (!$id) {
            $id = $result;
        }

        update_record_media('category', $id, CATEGORY_UPLOAD_DIR, $_FILES);
    }

    return 'admin/categories';
}

function remove_category()
{
    $values = $_POST['values'];

    foreach ($values as $id) {
        remove_dir(CATEGORY_UPLOAD_DIR . $id);
        remove_images(CATEGORY_UPLOAD_DIR, $id);
    }

    delete_records('category', $values);
    return 'admin/categories';
}

function add_order_state()
{
    $result = query_fields('order_states', $_POST['expected_fields'], $_POST['id']);
    return 'admin/orders/states';
}

function remove_order_state()
{
    delete_records('order_states', $_POST['values']);
    return 'admin/orders/states';
}

# old 
// function change_order_state()
// {
// 	$order_state_id = $_POST['order_state_id'];
// 	$delivery_staff_id = $_POST['delivery_staff_id'];

// 	if(empty($order_state_id) || empty($delivery_staff_id)) return 'admin/orders';

// 	$values = $_POST['values'];
// 	$con = get_db_con();

// 	$query = "SELECT state_num FROM order_states WHERE id='".$order_state_id."'";
// 	$result = db_query($con, $query);
// 	$row = db_fetch_assoc($result);
// 	db_free_result($result);

// 	$state_num = $row['state_num'];

// 	foreach($values as $id)
// 	{
// 		$query = "INSERT INTO states_per_order(order_id,order_state_id,delivery_staff_id) VALUES('".$id."','".$order_state_id."','".$delivery_staff_id."')";
// 		$result = db_query($con, $query);

// 		if($result && $state_num == ORDER_STATE_COMPLETED)
// 		{
// 			$query = "UPDATE orders SET is_paid='1' WHERE id='".$id."'";
// 			$result = db_query($con, $query);
// 		}
// 		else
// 		{
// 			if(DB_DRIVER_PREFIX == 'mysqli') $state_per_order_id = db_insert_id($con);
// 			else if(DB_DRIVER_PREFIX == 'pg')
// 			{
// 				$row = db_fetch_assoc($result);
// 				db_free_result($result);

// 				$state_per_order_id = $row['id'];
// 			}

// 			notify_delivery_staff($state_per_order_id);
// 		}
// 	}

// 	close_db_con($con);
// 	return 'admin/orders';
// }


function change_order_state()
{

    $order_state_id = $_POST['order_state_id'];
    $delivery_staff_id = $_POST['delivery_staff_id'];

    if (empty($order_state_id) || empty($delivery_staff_id)) {
        return 'admin/orders';
    }

    $values = $_POST['values'];
    $con = get_db_con();

    $query = "SELECT state_num FROM order_states WHERE id='" . $order_state_id . "'";
    $result = db_query($con, $query);
    $row = db_fetch_assoc($result);
    db_free_result($result);

    $state_num = $row['state_num'];


    foreach ($values as $id) {
        $query = "INSERT INTO states_per_order(order_id,order_state_id,delivery_staff_id) VALUES('" . $id . "','" . $order_state_id . "','" . $delivery_staff_id . "')";
        $result = db_query($con, $query);


        if ($state_num == ORDER_STATE_SENDMAIL) {

      
        
            // $query = "SELECT * from products_per_order  WHERE order_id='" . $id . "'";
            // $result = db_query($con, $query);
        
            // $fetched_order_products = mysqli_fetch_all($result,MYSQLI_ASSOC);
        
        
            $query = "SELECT  store_id, order_id , price from order_update  WHERE order_id='" . $id . "'";
            $result = db_query($con, $query);
        
            $fetched_order_shops = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
            $shops = [];
            foreach ($fetched_order_shops as $shop) {
                $query = "SELECT  name from shop  WHERE id='" . $shop['store_id'] . "'";
                $result = db_query($con, $query);
                $fetched_shops = mysqli_fetch_assoc($result);
                $shops[] =     $fetched_shops["name"];
            }
        
     
        
            $query = "SELECT id, user_id , area_id,created_at, delivery_time, total_price, total_price_update_on , is_express_delivery from orders  WHERE id='" . $id . "'";
            $result = db_query($con, $query);
        
            $fetched_order = db_fetch_assoc($result);
            $at = $fetched_order['delivery_time'];
            $order_id = $fetched_order['id'];
        
            $total_price = $fetched_order["total_price"] ." ".CURRENCY_CODE;
        
            $service_query = "SELECT * FROM charge WHERE charge_area_id={$fetched_order['area_id']}";
            $service_result = db_query($con, $service_query);
            $result_fetch = db_fetch_assoc($service_result);
            $charge = $result_fetch['present'] / 100;
            // $charge_without_per = (float) $result_fetch['present'] ;

            $get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=${id}";
            $co_orders = db_query($con, $get_offers);
            $cou_orders = db_fetch_assoc($co_orders);
            $id_id = $cou_orders['coupon_id'];
            $get_co = "SELECT * FROM coupons WHERE id=$id_id";
            $co_get = db_query($con, $get_co);
            $coupon_get = db_fetch_assoc($co_get);
        
            $present = (int) $coupon_get['present'];
        

            if (empty($fetched_order["total_price_update_on"])) {

                $query_2 = "SELECT * FROM order_update WHERE order_id='" . $id . "' ";
                $result_2 = db_query($con, $query_2);
                $result_2_fetchted = mysqli_fetch_all($result_2, MYSQLI_ASSOC);

                foreach ($result_2_fetchted as $key => $value) {
                    $price[] = $value["price"];
                }
                $price = (float) array_sum($price);
                /**********************************/
                if ($fetched_order['is_express_delivery'] == 1) $delivery_fee = DELIVERY_FEE_EXPRESS;
                else $delivery_fee = DELIVERY_FEE;
                $delivery_fee .= " " . CURRENCY_CODE;

                $total_price =  (float) $price + $delivery_fee + ($price * $charge) - $present . " " . CURRENCY_CODE;
            }

            $query = "SELECT email, CONCAT(first_name, ' ',last_name) as full_name from user  WHERE id= {$fetched_order['user_id']}";
            $result = db_query($con, $query);

            $fetched_user = db_fetch_assoc($result);

            $text = "<head>
            <meta content='text/html; charset=utf-8' http-equiv='Content-Type' />
            <meta content='width=device-width' name='viewport' />
            <meta content='IE=edge' http-equiv='X-UA-Compatible' />
            <style type='text/css'>
                body {
                    margin: 0;
                    padding: 0;
                }
        
                table,
                td,
                tr {
                    vertical-align: top;
                    border-collapse: collapse;
                }
        
                * {
                    line-height: inherit;
                }
        
                a[x-apple-data-detectors=true] {
                    color: inherit !important;
                    text-decoration: none !important;
                }
            </style>
            <style id='media-query' type='text/css'>
                @media (max-width: 620px) {
        
                    .block-grid,
                    .col {
                        min-width: 320px !important;
                        max-width: 100% !important;
                        display: block !important;
                    }
        
                    .block-grid {
                        width: 100% !important;
                    }
        
                    .col {
                        width: 100% !important;
                    }
        
                    .col>div {
                        margin: 0 auto;
                    }
        
                    img.fullwidth,
                    img.fullwidthOnMobile {
                        max-width: 100% !important;
                    }
        
                    .no-stack .col {
                        min-width: 0 !important;
                        display: table-cell !important;
                    }
        
                    .no-stack.two-up .col {
                        width: 50% !important;
                    }
        
                    .no-stack .col.num4 {
                        width: 33% !important;
                    }
        
                    .no-stack .col.num8 {
                        width: 66% !important;
                    }
        
                    .no-stack .col.num4 {
                        width: 33% !important;
                    }
        
                    .no-stack .col.num3 {
                        width: 25% !important;
                    }
        
                    .no-stack .col.num6 {
                        width: 50% !important;
                    }
        
                    .no-stack .col.num9 {
                        width: 75% !important;
                    }
        
                    .video-block {
                        max-width: none !important;
                    }
        
                    .mobile_hide {
                        min-height: 0px;
                        max-height: 0px;
                        max-width: 0px;
                        display: none;
                        overflow: hidden;
                        font-size: 0px;
                    }
        
                    .desktop_hide {
                        display: block !important;
                        max-height: none !important;
                    }
                }
            </style>
        </head>
        
        <body class='clean-body' style='margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #283C4B;'>
            <!--[if IE]><div class='ie-browser'><![endif]-->
            <table bgcolor='#283C4B' cellpadding='0' cellspacing='0' class='nl-container' role='presentation'
                style='table-layout: fixed; vertical-align: top; min-width: 320px; Margin: 0 auto; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #283C4B; width: 100%;'
                valign='top' width='100%'>
                <tbody>
                    <tr style='vertical-align: top;' valign='top'>
                        <td style='word-break: break-word; vertical-align: top;' valign='top'>
                            <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td align='center' style='background-color:#283C4B'><![endif]-->
                            <div style='background-color:#9ec8eb;'>
                                <div class='block-grid no-stack'
                                    style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                                    <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                                        <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color:#9ec8eb;width:600px; border-top: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                                        <div class='col num12'
                                            style='min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 594px;'>
                                            <div style='width:100% !important;'>
                                                <!--[if (!mso)&(!IE)]><!-->
                                                <div
                                                    style='border-top:3px solid #FFFFFF; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                                    <!--<![endif]-->
                                                    <div align='center' class='img-container center autowidth'
                                                        style='padding-right: 25px;padding-left: 25px;'>
                                                        <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr style='line-height:0px'><td style='padding-right: 25px;padding-left: 25px;' align='center'><![endif]-->
                                                        <div style='font-size:1px;line-height:25px'> </div><img align='center'
                                                            alt='Image' border='0' class='center autowidth'
                                                            src='https://mrkt.ws/images/white-logo.png'
                                                            style='text-decoration: none; -ms-interpolation-mode: bicubic; border: 0; height: auto; width: 100%; max-width: 256px; display: block;'
                                                            title='Image' width='256' />
                                                        <!--[if mso]></td></tr></table><![endif]-->
                                                    </div>
                                                    <!--[if (!mso)&(!IE)]><!-->
                                                </div>
                                                <!--<![endif]-->
                                            </div>
                                        </div>
                                        <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                                    </div>
                                </div>
                            </div>
                            <div style='background-color:#9ec8eb;'>
                                <div class='block-grid no-stack'
                                    style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                                    <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                                        <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color:#9ec8eb;width:600px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                                        <div class='col num12'
                                            style='min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 594px;'>
                                            <div style='width:100% !important;'>
                                                <!--[if (!mso)&(!IE)]><!-->
                                                <div
                                                    style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                                    <!--<![endif]-->
                                                    <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 20px; padding-left: 20px; padding-top: 30px; padding-bottom: 20px; font-family: Arial, sans-serif'><![endif]-->
                                                    <div
                                                        style='color:#FFFFFF;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:0;padding-right:20px;padding-bottom:;padding-left:20px;'>
                                                        <div
                                                            style='line-height: 1.2; font-size: 12px; color: #FFFFFF; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 14px;'>
                                                            <p
                                                                style='font-size: 24px; line-height: 1.2; text-align: center; word-break: break-word; mso-line-height-alt: 29px; margin: 0;'>
                                                                <span style='font-size: 24px;'>Final Receipt</span></p>
                                                        </div>
                                                    </div>
                                                    <!--[if mso]></td></tr></table><![endif]-->
                                                    <!--[if (!mso)&(!IE)]><!-->
                                                </div>
                                                <!--<![endif]-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
           </div>
        
         ";
            foreach ($shops as $i => $shop) {
                $shop_price  = $fetched_order_shops[$i]['price'] . " " . CURRENCY_CODE;
                $text .= "	
        
      
					<div style='background-color:#9ec8eb;'>
                    <div class='block-grid mixed-two-up no-stack'
                        style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                        <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                            <div class='col num8'
                                style='display: table-cell; vertical-align: top; min-width: 320px; max-width: 400px; width: 397px;'>
                                <div style='width:100% !important;'>
                                    <div
                                        style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                        <div
                                            style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:20px;'>
                                            <div
                                                style='font-size: 14px; line-height: 1.8; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 25px;'>
                                                <p
                                                    style='font-size: 15px; line-height: 1.8; word-break: break-word; mso-line-height-alt: 27px; margin: 0;'>
                                                    <span style='font-size: 15px;'><strong>${shop}</strong></span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if (!mso)&(!IE)]><!-->
                                    </div>
                                    <!--<![endif]-->
                                </div>
                            </div>
                            <div class='col num4'
                                style='display: table-cell; vertical-align: top; max-width: 320px; min-width: 200px; width: 197px;'>
                                <div style='width:100% !important;'>
                                    <div
                                        style='border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                        <div
                                            style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;'>
                                            <div
                                                style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                                <p
                                                    style='line-height: 1.8; word-break: break-word; text-align: left; font-size: 15px; mso-line-height-alt: 27px; margin: 0;'>
                                                    <span style='font-size: 15px;'>${shop_price }</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        
        ";
            }
            $text .= "
           
        
        


            <div style='background-color:#9ec8eb;'>
            <div class='block-grid mixed-two-up no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='400' style='background-color:#9ec8eb;width:400px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 0px solid transparent;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num8'
                        style='display: table-cell; vertical-align: top; min-width: 320px; max-width: 400px; width: 397px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 20px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:20px;'>
                                    <div
                                        style='font-size: 14px; line-height: 1.8; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 25px;'>
                                        <p
                                            style='font-size: 15px; line-height: 1.8; word-break: break-word; mso-line-height-alt: 27px; margin: 0;'>
                                            <span
                                                style='font-size: 15px;'><strong>Coupon(mrkt)</strong></span>
                                        </p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td><td align='center' width='200' style='background-color:#9ec8eb;width:200px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num4'
                        style='display: table-cell; vertical-align: top; max-width: 320px; min-width: 200px; width: 197px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;'>
                                    <div
                                        style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; text-align: left; font-size: 15px; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'><b>-</bF>${present} EGP</span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid mixed-two-up no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='400' style='background-color:#9ec8eb;width:400px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 0px solid transparent;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num8'
                        style='display: table-cell; vertical-align: top; min-width: 320px; max-width: 400px; width: 397px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 20px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:20px;'>
                                    <div
                                        style='font-size: 14px; line-height: 1.8; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 25px;'>
                                        <p
                                            style='font-size: 15px; line-height: 1.8; word-break: break-word; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'><strong>Service
                                                    Charge(3%)</strong></span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td><td align='center' width='200' style='background-color:#9ec8eb;width:200px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num4'
                        style='display: table-cell; vertical-align: top; max-width: 320px; min-width: 200px; width: 197px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;'>
                                    <div
                                        style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; text-align: left; font-size: 15px; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'>${charge}</span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color:#9ec8eb;width:600px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num12'
                        style='min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 594px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <table border='0' cellpadding='0' cellspacing='0' class='divider'
                                    role='presentation'
                                    style='table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;'
                                    valign='top' width='100%'>
                                    <tbody>
                                        <tr style='vertical-align: top;' valign='top'>
                                            <td class='divider_inner'
                                                style='word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;'
                                                valign='top'>
                                                <table align='center' border='0' cellpadding='0'
                                                    cellspacing='0' class='divider_content'
                                                    role='presentation'
                                                    style='table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #FFFFFF; width: 100%;'
                                                    valign='top' width='100%'>
                                                    <tbody>
                                                        <tr style='vertical-align: top;' valign='top'>
                                                            <td style='word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;'
                                                                valign='top'><span></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid mixed-two-up no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='400' style='background-color:#9ec8eb;width:400px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 0px solid transparent;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num8'
                        style='display: table-cell; vertical-align: top; min-width: 320px; max-width: 400px; width: 397px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 20px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:20px;'>
                                    <div
                                        style='font-size: 14px; line-height: 1.8; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 25px;'>
                                        <p
                                            style='font-size: 15px; line-height: 1.8; word-break: break-word; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'><strong>Products
                                                    Total</strong></span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td><td align='center' width='200' style='background-color:#9ec8eb;width:200px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num4'
                        style='display: table-cell; vertical-align: top; max-width: 320px; min-width: 200px; width: 197px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;'>
                                    <div
                                        style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; text-align: left; font-size: 15px; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'>${price} EGP</span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid mixed-two-up no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='400' style='background-color:#9ec8eb;width:400px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 0px solid transparent;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num8'
                        style='display: table-cell; vertical-align: top; min-width: 320px; max-width: 400px; width: 397px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 20px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:20px;'>
                                    <div
                                        style='font-size: 14px; line-height: 1.8; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 25px;'>
                                        <p
                                            style='font-size: 15px; line-height: 1.8; word-break: break-word; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'><strong>Delivery
                                                    Charge</strong></span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td><td align='center' width='200' style='background-color:#9ec8eb;width:200px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num4'
                        style='display: table-cell; vertical-align: top; max-width: 320px; min-width: 200px; width: 197px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;'>
                                    <div
                                        style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; text-align: left; font-size: 15px; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'>${delivery_fee}</span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color:#9ec8eb;width:600px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num12'
                        style='min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 594px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <table border='0' cellpadding='0' cellspacing='0' class='divider'
                                    role='presentation'
                                    style='table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;'
                                    valign='top' width='100%'>
                                    <tbody>
                                        <tr style='vertical-align: top;' valign='top'>
                                            <td class='divider_inner'
                                                style='word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;'
                                                valign='top'>
                                                <table align='center' border='0' cellpadding='0'
                                                    cellspacing='0' class='divider_content'
                                                    role='presentation'
                                                    style='table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #FFFFFF; width: 100%;'
                                                    valign='top' width='100%'>
                                                    <tbody>
                                                        <tr style='vertical-align: top;' valign='top'>
                                                            <td style='word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;'
                                                                valign='top'><span></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid mixed-two-up no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='400' style='background-color:#9ec8eb;width:400px; border-top: 0px solid transparent; border-left: 3px solid #FFFFFF; border-bottom: 0px solid transparent; border-right: 0px solid transparent;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num8'
                        style='display: table-cell; vertical-align: top; min-width: 320px; max-width: 400px; width: 397px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:3px solid #FFFFFF; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 20px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:20px;'>
                                    <div
                                        style='font-size: 14px; line-height: 1.8; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 25px;'>
                                        <p
                                            style='font-size: 15px; line-height: 1.8; word-break: break-word; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'><strong>Total</strong></span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td><td align='center' width='200' style='background-color:#9ec8eb;width:200px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num4'
                        style='display: table-cell; vertical-align: top; max-width: 320px; min-width: 200px; width: 197px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top: 0px; padding-bottom: 0px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px;'>
                                    <div
                                        style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; text-align: left; font-size: 15px; mso-line-height-alt: 27px; margin: 0;'>
                                            <span style='font-size: 15px;'>${total_price}</span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <div style='background-color:#9ec8eb;'>
            <div class='block-grid no-stack'
                style='Margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #9ec8eb;'>
                <div style='border-collapse: collapse;display: table;width: 100%;background-color:#9ec8eb;'>
                    <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#9ec8eb;'><tr><td align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px'><tr class='layout-full-width' style='background-color:#9ec8eb'><![endif]-->
                    <!--[if (mso)|(IE)]><td align='center' width='600' style='background-color:#9ec8eb;width:600px; border-top: 0px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-bottom: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF;' valign='top'><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;'><![endif]-->
                    <div class='col num12'
                        style='min-width: 320px; max-width: 600px; display: table-cell; vertical-align: top; width: 594px;'>
                        <div style='width:100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div
                                style='border-top:0px solid #FFFFFF; border-left:3px solid #FFFFFF; border-bottom:3px solid #FFFFFF; border-right:3px solid #FFFFFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;'>
                                <!--<![endif]-->
                                <!--[if mso]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding-right: 25px; padding-left: 25px; padding-top: 25px; padding-bottom: 25px; font-family: Arial, sans-serif'><![endif]-->
                                <div
                                    style='color:#ffffff;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:25px;padding-right:25px;padding-bottom:25px;padding-left:25px;'>
                                    <div
                                        style='line-height: 1.8; font-size: 12px; color: #ffffff; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;'>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; font-size: 14px; mso-line-height-alt: 25px; margin: 0;'>
                                            <span style='font-size: 14px;'>Order # ${order_id}</span></p>
                                        <p
                                            style='line-height: 1.8; word-break: break-word; font-size: 14px; mso-line-height-alt: 25px; margin: 0;'>
                                            <span style='font-size: 14px;'>Deliver at: ${at}</span></p>
                                    </div>
                                </div>
                                <!--[if mso]></td></tr></table><![endif]-->
                                <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                        </div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                </div>
            </div>
        </div>
        <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
    </td>
</tr>
</tbody>
</table>
<!--[if (IE)]></div><![endif]-->
</body>";

            send_mail($fetched_user['email'], $fetched_user['full_name'], APP_EMAIL, APP_NAME, "Your order state", $text, $images = [], $files = [], $inline_files = []);
        }

        if ($result && $state_num == ORDER_STATE_COMPLETED) {
            $query = "UPDATE orders SET is_paid='1' WHERE id='" . $id . "'";
            $result = db_query($con, $query);
        } else {
            if (DB_DRIVER_PREFIX == 'mysqli') {
                $state_per_order_id = db_insert_id($con);
            } else if (DB_DRIVER_PREFIX == 'pg') {
                $row = db_fetch_assoc($result);
                db_free_result($result);

                $state_per_order_id = $row['id'];
            }

            notify_delivery_staff($state_per_order_id);
        }
    }

    close_db_con($con);
    return 'admin/orders';
}

function remove_order()
{
    $values = $_POST['values'];

    delete_records('states_per_order', $values, 'order_id');
    delete_records('products_per_order', $values, 'order_id');
    delete_records('orders', $values);

    return 'admin/orders';
}

function refund_order()
{
    $values = $_POST['values'];

    foreach ($values as $id) {
        payfort_refund($id);
    }

    return 'admin/orders';
}

function add_delivery_staff()
{
    $id = $_POST['id'];
    $expected_fields = $_POST['expected_fields'];

    if ($id && empty($_POST['password'])) {
        unset($expected_fields[array_search('password', $expected_fields)]);
    } else {
        $_POST['password'] = hash(HASH_ALGO, $_POST['password']);
    }

    $result = query_fields('delivery_staff', $expected_fields, $id);
    return 'admin/delivery-staff';
}

function remove_delivery_staff()
{
    $values = $_POST['values'];
    $expo = new ExpoPushHandler;

    foreach ($values as $id) {
        $expo->unsubscribe($id);
    }

    delete_records('delivery_staff_device', $values, 'delivery_staff_id');
    delete_records('delivery_staff', $values);

    return 'admin/delivery-staff';
}

function activate_delivery_staff()
{
    toggle_records('delivery_staff', $_POST['values'], 1);
    return 'admin/delivery-staff';
}

function deactivate_delivery_staff()
{
    toggle_records('delivery_staff', $_POST['values'], 0);
    return 'admin/delivery-staff';
}

function change_delivery_staff_area()
{
    $area_id = $_POST['area_id'];
    if (empty($area_id)) {
        return 'admin/delivery-staff';
    }

    $values = $_POST['values'];
    $con = get_db_con();

    foreach ($values as $id) {
        $query = "UPDATE delivery_staff SET area_id='" . $area_id . "' WHERE id='" . $id . "'";
        $result = db_query($con, $query);
    }

    close_db_con($con);
    return 'admin/delivery-staff';
}

function add_city()
{
    $result = query_fields('city', $_POST['expected_fields'], $_POST['id']);
    return 'admin/cities';
}

function remove_city()
{
    delete_records('city', $_POST['values']);
    return 'admin/cities';
}
function add_area()
{
    $con = get_db_con();

    if (!empty($_POST["delivery_schdule_on"])) {
        $day = $_POST["delivery_date"];
        $keys_values=[];
           $query = "SELECT id, `day` FROM day WHERE `day` LIKE'%${day}%' ";
         $result = mysqli_query($con, $query);

         foreach ($_POST as $key => $value) {
        if ($key == "expected_fields") continue;
            if ($key[0] == 'F') {
                $i = str_replace("F", "", strstr($key, "=", true));
                $end = str_replace("=>", '', strstr($key, "=>"));
                for ($i; $i < $end;) {
                    if (empty($value)) $value = 0;
                    $k = "`F" . $i++ . "=>" . $i . "`";
                    $keys_values[$k]=$value;
                }
            }
        }$query_fetched = mysqli_fetch_assoc ($result);
        if (  $query_fetched != null )  {
            $id = $query_fetched["id"];
            $query = "UPDATE day SET day='${day}'";
                    foreach ($keys_values as $key => $value){
                        $query.= ', '.$key ."=". "'${value}'" ;
                    }
            $query.= " WHERE id=${id}";  
        }else{
            $values = " VALUES ('${day}'";
            $query = "INSERT INTO day (`day`";
                foreach ($keys_values as $key => $value){
                    $query.= ", ".$key ;
                    $values .=", ".$value ;
                }
             $query = $query." )". $values.")";
        }
        $result = mysqli_query($con, $query);
        $area_id = $_POST['id'];
        $id = $id ??  mysqli_insert_id($con);
        $result = mysqli_query($con, "INSERT INTO slots (area_id, day_id) VALUES (${area_id},${id})");
    
    }

    foreach ($_POST["expected_fields"] as $key => $value) {

        if ($value[0] == 'F')
            unset($_POST["expected_fields"][$key]);
        if ($value == "delivery_schdule_on")
            unset($_POST["expected_fields"][$key]);
        if ($value == "delivery_date")
            unset($_POST["expected_fields"][$key]);
    }

    close_db_con($con);


    $result = query_fields('area', $_POST['expected_fields'], $_POST['id']);
    return 'admin/cities/areas';
}

#old
// function add_area()
// {
//     $result = query_fields('area', $_POST['expected_fields'], $_POST['id']);
//     return 'admin/cities/areas';
// }

function remove_area()
{
    delete_records('area', $_POST['values']);
    return 'admin/cities/areas';
}

function add_user()
{
    $id = $_POST['id'];
    $expected_fields = $_POST['expected_fields'];
    /** prventging  extra fornt end  info from  beign insterd into a DB  */
    foreach ($expected_fields as $key => $value) if (($value  == "total_revnue") || ($value == "orders_count")) unset($expected_fields[$key]);
    if ($id && empty($_POST['password'])) {
        unset($expected_fields[array_search('password', $expected_fields)]);
    } else {
        $_POST['password'] = hash(HASH_ALGO, $_POST['password']);
    }

    $result = query_fields('user', $expected_fields, $id);
    return 'admin/users';
}

function remove_user()
{
    $values = $_POST['values'];

    delete_records('shop_lists', $values, 'user_id');
    delete_records('favorite_products', $values, 'user_id');
    delete_records('user', $values);

    return 'admin/users';
}

function activate_user()
{
    toggle_records('user', $_POST['values'], 1);
    return 'admin/users';
}

function deactivate_user()
{
    toggle_records('user', $_POST['values'], 0);
    return 'admin/users';
}

function add_function()
{
    $id = query_fields('function', $_POST['expected_fields'], $_POST['id']);
    return 'admin/functions';
}

function remove_function()
{
    delete_records('function_per_role', $_POST['values'], 'function_id');
    delete_records('function', $_POST['values']);

    return 'admin/functions';
}

function add_role()
{
    $role_id = $_POST['id'];
    $result = query_fields('role', $_POST['expected_fields'], $role_id);

    if (!$result) {
        return 'admin/roles';
    }

    if (!$role_id) {
        $role_id = $result;
    }

    $con = get_db_con();

    $functions = $_POST['functions'];
    delete_records('function_per_role', $role_id, 'role_id');

    foreach ($functions as $id) {
        $query = "INSERT INTO function_per_role(role_id,function_id) VALUES('" . $role_id . "','" . $id . "')";
        $result = db_query($con, $query);
    }

    close_db_con($con);
    return 'admin/roles';
}

function remove_role()
{
    delete_records('function_per_role', $_POST['values'], 'role_id');
    delete_records('role', $_POST['values']);

    return 'admin/roles';
}

function add_admin()
{
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = strtolower(trim($_POST['email']));
    $role_id = $_POST['role_id'];
    $expected_fields = $_POST['expected_fields'];

    if ($id && empty($_POST['password'])) {
        unset($expected_fields[array_search('password', $expected_fields)]);
    } else {
        $_POST['password'] = hash(HASH_ALGO, $_POST['password']);
    }

    $result = query_fields('admin', $expected_fields, $id);

    if ($result && $_SESSION['admin_id'] == $id) {
        $data = [
            'admin_name' => $username,
            'admin_full' => $full_name,
            'admin_email' => $email,
            'admin_role_id' => $role_id,
        ];

        update_session($data);
    }

    return 'admin/admins';
}

function remove_admin()
{
    delete_records('admin', $_POST['values']);
    return 'admin/admins';
}

function activate_admin()
{
    toggle_records('admin', $_POST['values'], 1);
    return 'admin/admins';
}

function deactivate_admin()
{
    toggle_records('admin', $_POST['values'], 0);
    return 'admin/admins';
}

function add_options()
{
    $_POST['max_upload_size'] = 1024 * 1024 * trim($_POST['max_upload_size']);
    $_POST['image_filter'] = preg_replace('/\s+/', DELIMITER, strtolower(trim($_POST['image_filter'])));
    $_POST['video_filter'] = preg_replace('/\s+/', DELIMITER, strtolower(trim($_POST['video_filter'])));
    $_POST['payfort_return_url_tokenization'] = CMS_BASE . trim($_POST['payfort_return_url_tokenization']);
    $_POST['payfort_return_url_purchase'] = CMS_BASE . trim($_POST['payfort_return_url_purchase']);
    $_POST['payfort_success_url'] = CMS_BASE . trim($_POST['payfort_success_url']);
    $_POST['payfort_error_url'] = CMS_BASE . trim($_POST['payfort_error_url']);

    $result = query_fields('options', $_POST['expected_fields'], $_POST['id']);
    return 'admin/options';
}

function purge_db_tables()
{
    $tables = prepare_inputs($_POST['tables']);
    $con = get_db_con();

    foreach ($tables as &$table) {
        $query = "TRUNCATE TABLE " . $table;
        $result = db_query($con, $query);
    }

    close_db_con($con);
    return 'admin';
}

function backup_db() :void {
    $con = get_db_con();
    $query = "mysqld  -P ".DB_PORT." 
                      -u ".DB_USER."
                      -p ".DB_PASS." 
                      --host ".DB_SERVER."
                      --databases ".DB_NAME."
                      > db.sql";
    $result = db_query($con, $query);

}

function toggle_records($table, $values, $state, $state_field = 'active', $id_field = 'id')
{
    $values = implode(',', (array) $values);

    $con = get_db_con();
    $query = "UPDATE " . $table . " SET " . $state_field . "='" . $state . "' WHERE " . $id_field . " IN (" . $values . ")";
    $result = db_query($con, $query);
    close_db_con($con);

    return $result;
}

function delete_records($table, $values, $id_field = 'id')
{
    $values = implode(',', (array) $values);

    $con = get_db_con();
    $query = "DELETE FROM " . $table . " WHERE " . $id_field . " IN (" . $values . ")";
    $result = db_query($con, $query);
    close_db_con($con);

    return $result;
}

function prepare_inputs($data)
{
    if (!is_array($data)) {
        return addslashes(trim($data));
    }

    foreach ($data as &$value) {
        $value = prepare_inputs($value);
    }

    return $data;
}

function collect_fields($fields)
{
    $result_fields = [];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $result_fields[$field] = trim($_POST[$field]);
        } else {
            $result_fields[$field] = 0;
        }
    }

    return $result_fields;
}

function query_fields($table, $fields, $update_id = 0, $update_field = 'id')
{
    if (!is_string($table) || !is_array($fields)) {
        return false;
    }

    $table = trim($table);
    if (empty($table)) {
        return false;
    }

    $result_fields = collect_fields($fields);
    if (empty($result_fields)) {
        return false;
    }

    if ($update_id) {
        if (!is_string($update_field)) {
            return false;
        }

        $update_field = trim($update_field);
        if (empty($update_field)) {
            return false;
        }

        $fields = [];
        foreach ($result_fields as $key => $field) {
            $fields[] = $key . " = '" . addslashes($field) . "'";
        }

        $fields = implode(', ', $fields);
        $query = "UPDATE " . $table . " SET " . $fields . " WHERE " . $update_field . " = '" . $update_id . "'";
    } else {
        $fields = [];
        foreach ($result_fields as $key => &$field) {
            $field = addslashes($field);
            $fields[] = $key;
        }

        $fields = implode(', ', $fields);
        $result_fields = implode("', '", $result_fields);

        $query = "INSERT INTO " . $table . "(" . $fields . ") VALUES('" . $result_fields . "')";
        if (DB_DRIVER_PREFIX == 'pg') {
            $query .= " RETURNING id";
        }
    }

    $con = get_db_con();
    $result = db_query($con, $query);

    if (!$update_id && $result) {
        if (DB_DRIVER_PREFIX == 'mysqli') {
            $result = db_insert_id($con);
        } else if (DB_DRIVER_PREFIX == 'pg') {
            $row = db_fetch_assoc($result);
            db_free_result($result);
            $result = $row['id'];
        }
    }

    close_db_con($con);
    return $result;
}

function update_featured_image($table, $id, $featured_image)
{
    if (isset($_POST['featured_image'])) {
        $featured_image = $_POST['featured_image'];
    } else if (empty($featured_image)) {
        return false;
    }

    $featured_image = str_replace(' ', FILENAME_GLUE, $featured_image);

    $con = get_db_con();
    $query = "UPDATE " . $table . " SET featured_image='" . $featured_image . "' WHERE id='" . $id . "'";
    $result = db_query($con, $query);
    close_db_con($con);

    return $result;
}

// function update_front_image($table, $id, $front_image)
// {
//     if (isset($_POST['front_image'])) $front_image = $_POST['front_image'];
//     else if (empty($front_image)) return false;

//     $front_image = str_replace(' ', FILENAME_GLUE, $front_image);

//     $con = get_db_con();
//     $query = "UPDATE " . $table . " SET front_image='" . $front_image . "' WHERE id='" . $id . "'";
//     $result = db_query($con, $query);
//     close_db_con($con);

//     return $result;
// }

// function update_record_media($table, $id, $base_dir, $files)
// {
//     $images_list = [];
//     $featured_image = null;
//     $front_image = null;

//     $con = get_db_con();
//     $query = "SELECT name FROM media WHERE record_id='" . $id . "' AND upload_dir='" . $base_dir . "' ORDER BY name";
//     $result = db_query($con, $query);

//     if ($result && db_num_rows($result)) {
//         while ($row = db_fetch_assoc($result)) $images_list[] = $row['name'];
//         db_free_result($result);
//     }

//     list($files, $titles) = regenerate_images($images_list, $base_dir . $id . '/', $files);

//     remove_images($base_dir, $id);
//     remove_dir($base_dir . $id . THUMBS_DIR);
//     if (!empty($files)) {
//         $featured_image = $files[0];
//         // $front_image = $files[1];
//         mkdir($base_dir . $id . THUMBS_DIR);
//     }

//     foreach ($files as $key => $file) {
//         if (isset($titles[$key])) $title = $titles[$key];
//         else $title = "";

//         $query = "INSERT INTO media(name,title,upload_dir,record_id) VALUES('" . $file . "','" . $title . "','" . $base_dir . "','" . $id . "')";
//         $result = db_query($con, $query);
//         make_thumb($base_dir . $id . '/' . $file, $base_dir . $id . THUMBS_DIR . $file);
//     }

//     close_db_con($con);
//     update_featured_image($table, $id, $featured_image);
//     // update_front_image($table, $id, $front_image);
// }

function update_record_media($table, $id, $base_dir, $files)
{
    //true 0777
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $images_list = [];
    $featured_image = null;

    $con = get_db_con();
    $query = "SELECT name FROM media WHERE record_id='" . $id . "' AND upload_dir='" . $base_dir . "' ORDER BY name";
    $result = db_query($con, $query);

    if ($result && db_num_rows($result)) {
        while ($row = db_fetch_assoc($result)) {
            $images_list[] = $row['name'];
        }

        db_free_result($result);
    }

    list($files, $titles) = regenerate_images($images_list, $base_dir . $id . '/', $files);
    remove_images($base_dir, $id);
    remove_dir($base_dir . $id . THUMBS_DIR);

    if (!empty($files)) {
        $featured_image = $files[0];
        mkdir($base_dir . $id . THUMBS_DIR);
    }

    // var_dump(dir($base_dir . $id));
    // var_dump(dir($base_dir . $id . THUMBS_DIR));

    foreach ($files as $key => $file) {
        if (isset($titles[$key])) {
            $title = $titles[$key];
        } else {
            $title = "";
        }

        $query = "INSERT INTO media(name,title,upload_dir,record_id) VALUES('" . $file . "','" . $title . "','" . $base_dir . "','" . $id . "')";
        $result = db_query($con, $query);

        make_thumb($base_dir . $id . '/' . $file, $base_dir . $id . THUMBS_DIR . $file);
    }

    close_db_con($con);
    update_featured_image($table, $id, $featured_image);
}

function make_thumb($src, $dest)
{

    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    // var_dump($ext);
    switch ($ext) {
        case 'jpg':
        case 'jpeg': {
                // var_dump($src);
                $src = imagecreatefromjpeg($src);
                // var_dump($src);
                break;
            }
        case 'png': {
                // var_dump($src);
                $src = imagecreatefrompng($src);

                break;
            }
        case 'bmp': {

                $src = imagecreatefrombmp($src);
                break;
            }
        case 'gif': {
                $src = imagecreatefromgif($src);
                break;
            }
        default: {
                return;
            }
    }
    // var_dump($src);
    // exit;
    if (!$src) {
        return false;
    }

    $width = imagesx($src);
    $height = imagesy($src);

    $thumb_width_landscape = CROP_W_LANDSCAPE;
    $thumb_width_portrait = CROP_W_PORTRAIT;

    if (isset($_POST['thumb_width_landscape']) && !empty($_POST['thumb_width_landscape'])) {
        $thumb_width_landscape = intval($_POST['thumb_width_landscape']);
    }

    if (isset($_POST['thumb_width_portrait']) && !empty($_POST['thumb_width_portrait'])) {
        $thumb_width_portrait = intval($_POST['thumb_width_portrait']);
    }

    if ($width > $height) {
        $crop_w = $thumb_width_landscape;
    } else {
        $crop_w = $thumb_width_portrait;
    }

    $crop_h = ($crop_w * $height) / $width;

    $virtual_image = imagecreatetruecolor($crop_w, $crop_h);
    imagefill($virtual_image, 0, 0, imagecolorallocatealpha($virtual_image, 255, 255, 255, 127));
    imagecopyresampled($virtual_image, $src, 0, 0, 0, 0, $crop_w, $crop_h, $width, $height);

    switch ($ext) {
        case 'jpg':
        case 'jpeg': {
                $status = imagejpeg($virtual_image, $dest);
                break;
            }
        case 'png': {
                $status = imagepng($virtual_image, $dest);
                break;
            }
        case 'bmp': {
                $status = imagebmp($virtual_image, $dest);
                break;
            }
        case 'gif': {
                $status = imagegif($virtual_image, $dest);
                break;
            }
    }

    return $status;
}

function regenerate_images($images_list, $base_dir, $uploaded_files)
{
    $new_images_list = $final_list = $titles = [];
    foreach ($images_list as $key => $value) {
        if (is_file($base_dir . $value) && isset($_POST['image_' . $key])) {
            $new_images_list[] = $_POST['image_' . $key];

            $new_name = addslashes(str_replace(' ', FILENAME_GLUE, trim($_POST['image_name_' . $key])));
            if (!empty($new_name)) {
                $image_ext = pathinfo($_POST['image_' . $key], PATHINFO_EXTENSION);
                $final_list[] = $new_name .= '.' . $image_ext;
                $titles[] = addslashes(trim($_POST['image_title_' . $key]));

                rename($base_dir . $value, $base_dir . $new_name);
            }
        }
    }

    $images_to_remove = array_diff($images_list, $new_images_list);
    foreach ($images_to_remove as $image) {
        if (is_file($base_dir . $image)) {
            unlink($base_dir . $image);
        }
    }

    $images = [];
    if (!empty($uploaded_files['images']['tmp_name'][0])) {
        $images = process_images($uploaded_files, $base_dir);
    }

    $images = array_merge($final_list, $images);
    return [$images, $titles];
}

function process_images($files, $base_dir)
{
    $names = [];
    $extensions = explode(DELIMITER, MEDIA_FILTER);

    if (!is_dir($base_dir)) {
        mkdir($base_dir, DIR_CREATE_FLAGS, true);
    }

    foreach ($files["images"]["error"] as $key => $error) {
        if ($error != UPLOAD_ERR_OK || $files["images"]["size"][$key] > MAX_UPLOAD_SIZE) {
            continue;
        }

        $name = str_replace(' ', FILENAME_GLUE, basename($files["images"]["name"][$key]));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, $extensions)) {
            continue;
        }

        move_uploaded_file($files["images"]["tmp_name"][$key], $base_dir . $name);

        $names[] = $name;
    }

    sort($names);
    return $names;
}

function remove_dir($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    if (substr($dir, -1) != '/') {
        $dir .= '/';
    }

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        if (is_dir($dir . $file)) {
            remove_dir($dir . $file);
        } else {
            unlink($dir . $file);
        }
    }

    rmdir($dir);
}

function remove_images($base_dir, $id)
{
    $con = get_db_con();
    $query = "DELETE FROM media WHERE upload_dir='" . $base_dir . "' AND record_id='" . $id . "'";
    $result = db_query($con, $query);
    close_db_con($con);
}
