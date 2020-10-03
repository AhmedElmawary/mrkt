<?php


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
	"get_sodic_products",
	'copy_products_pic',
	"copy_banners",
	"copy_stores_pic",
	"copy_products_image_to_thumbs",
	"attach_products_stores",
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
	"remove_empty_products",
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

function attach_products_stores()
{
	set_time_limit(0);
	

	 $con = get_db_con();

	$result = mysqli_query($con,   "SELECT id, name, area_id
						            FROM shop
						            WHERE area_id=1");

	$result_1 = mysqli_query($con, "SELECT id, name, area_id
								    FROM shop
								    WHERE area_id=9");

	$stores_1 = mysqli_fetch_all( $result_1, MYSQLI_ASSOC);
	
	$stores = mysqli_fetch_all( $result, MYSQLI_ASSOC);

	$sotres_query= [];

	foreach ($stores as $key => $value) 
			foreach ($stores_1 as $key_ => $value_) 
					if ($value_["name"] == $value["name"]) {
						$query = "UPDATE to_ SET shop_id=";
						$query .=$value_["id"];
						$query .=" WHERE shop_id=".$value["id"]." ;";
						$sotres_query["success"][ $value["id"] ."=>". $value_["id"] ] = $inner_result = mysqli_query($con, $query);
					
						if (! isset($inner_result)) {
							$sotres_query["falied"][ $value["id"] ."=>". $value_["id"] ]= mysqli_error($con);
						}	
					}
					close_db_con($con);
					echo json_encode([
										"queries"=>$sotres_query
									  ]);
					die;
}


function copy_stores_pic()
{
	set_time_limit(0);
	
	$query_1 = "SELECT *
			    FROM   shop
			    WHERE  area_id=1";

	$query_9 = "SELECT *
			    FROM   shop
			    WHERE  area_id=9";

	$con = get_db_con();

	$result_1 = mysqli_query($con, $query_1);   

	$result_9 = mysqli_query($con, $query_9);   

	$stores_1 = mysqli_fetch_all($result_1, MYSQLI_ASSOC);

	$stores_9 = mysqli_fetch_all($result_9, MYSQLI_ASSOC);

	$copied_files = $media = [];

	
	foreach ($stores_1 as $index_1) {
		foreach($stores_9 as $index_9) {
			if ($index_1["name"] == $index_9["name"]) {
				$soruce_dir = "/var/www/html/media/shops/{$index_1["id"]}/";
				$soruce_thumbs = "/var/www/html/media/shops/{$index_1["id"]}/thumbs";
				$source_featured_file="/var/www/html/media/shops/{$index_1["id"]}/{$index_1["featured_image"]}";
				$source_featured_thumbs="/var/www/html/media/shops/{$index_1["id"]}/thumbs/{$index_1["featured_image"]}";
				$source_front_image_file="/var/www/html/media/shops/{$index_1["id"]}/{$index_1["front_image"]}";
				$source_front_image_thumbs="/var/www/html/media/shops/{$index_1["id"]}/thumbs/{$index_1["front_image"]}";

				$des_dir = "/var/www/html/media/shops/{$index_9["id"]}/";
				$des_thumbs = "/var/www/html/media/shops/{$index_9["id"]}/thumbs";
				$des_featured_file="/var/www/html/media/shops/{$index_9["id"]}/{$index_9["featured_image"]}";
				$des_file_thumbs="/var/www/html/media/shops/{$index_9["id"]}/thumbs/{$index_9["featured_image"]}";
				$des_front_image_file="/var/www/html/media/shops/{$index_9["id"]}/{$index_9["front_image"]}";
				$des_front_image_thumbs="/var/www/html/media/shops/{$index_9["id"]}/thumbs/{$index_9["front_image"]}";

				if (is_dir($soruce_dir)) {
					if (! is_dir($des_dir)) {
						$copied_dires["dires"][$des_dir] =	mkdir($des_dir,0777,true) ? "Copied Successfully":"Something went wrong";
					}
					if ( is_file($source_featured_file)) {
						$copied_files["Featured"][$index_1["id"]."=>".$index_9["id"]] = copy($source_featured_file, $des_featured_file) ?
																				 "Copied Successfully": "Something went wrong";
					
						$query="INSERT INTO media VALUES (DEFAULT, ";
						$query.= "'{$index_9["featured_image"]}','', 'media/shops/' ,{$index_9["id"]} ); ";
						$media["Featured"][$index_9] = $res = mysqli_query($con, $query);   
						if (empty($res)) {
							$media["ERRORS"]["Featured"][$index_9] = mysqli_error($con);
						}else 
						$media["media"]["Featured"][$index_9] = mysqli_error($con);
					}
					if ( is_file($source_front_image_file)) {
						$copied_files["Front"][$index_1["id"]."=>".$index_9["id"]] = copy($source_front_image_file, $des_front_image_file) ?
																				 "Copied Successfully": "Something went wrong";
						$query="INSERT INTO media VALUES (DEFAULT, ";
						$query.= "'{$index_9["front_image"]}','', 'media/shops/' ,{$index_9["id"]} ); ";
						$media["Front"][$index_9] = $res = mysqli_query($con, $query);   
							if (empty($res)) {
								$media["ERRORS"]["Front"][$index_9] = mysqli_error($con);
							}else
							$media["media"]["Front"][$index_9] = mysqli_error($con);
					}
					if (is_dir($soruce_thumbs)) {
						if ( ! is_dir($des_thumbs)) {
							$copied_dires ["thumbs"][$des_thumbs] =	mkdir($des_thumbs,0777,true);
						}
						if ( is_file($source_featured_thumbs)) {
							$copied_files["Featured"]["thumbs/".$index_1["id"]."=>".$index_9["id"]] = copy($source_featured_thumbs, $des_file_thumbs) ?
																					 "Copied Successfully": "Something went wrong";
						}
						if ( is_file($source_front_image_thumbs)) {
							$copied_files["Front"]["thumbs/".$index_1["id"]."=>".$index_9["id"]] = copy($source_front_image_thumbs, $des_front_image_thumbs) ?
																					 "Copied Successfully": "Something went wrong";
						}
					}
				}
			}
		}
	}			mysqli_close($con);
				echo json_encode($copied_files);
				die;
}

function copy_products_pic()
{
	set_time_limit(0);

	$con = get_db_con();

	
	$result = mysqli_query($con,   "SELECT  product.id, product.name, area_id, product.featured_image
								    FROM    product,shop
								    WHERE   shop.id=shop_id 
								    AND     area_id=9");

	
	$result_2 = mysqli_query($con, "SELECT  product.id, product.name, area_id, product.featured_image
									FROM    product,shop
									WHERE   shop.id=shop_id 
								    AND     area_id=1");

	$products_9 = mysqli_fetch_all($result, MYSQLI_ASSOC);

	$products_1 = mysqli_fetch_all($result_2, MYSQLI_ASSOC);
	
	$copied_files = $copied_dires = [];

	foreach ($products_9 as $index_9) {
		foreach($products_1 as $index_1) {
				if ($index_9["name"] == $index_1["name"]) {
					$soruce_dir = "/var/www/html/media/products/{$index_1["id"]}/";
					$soruce_thumbs = "/var/www/html/media/products/{$index_1["id"]}/thumbs";
					$source_file="/var/www/html/media/products/{$index_1["id"]}/{$index_1["featured_image"]}";
					$source_file_thumbs="/var/www/html/media/products/{$index_1["id"]}/thumbs/{$index_1["featured_image"]}";

					$des_dir = "/var/www/html/media/products/{$index_9["id"]}/";
					$des_thumbs = "/var/www/html/media/products/{$index_9["id"]}/thumbs";
					$des_file="/var/www/html/media/products/{$index_9["id"]}/{$index_9["featured_image"]}";
					$des_file_thumbs="/var/www/html/media/products/{$index_9["id"]}/thumbs/{$index_9["featured_image"]}";

					if(is_dir($soruce_dir)) {
					   if (!is_dir($des_dir)) {
						$copied_dires [$des_dir] =	mkdir($des_dir,0777,true);
					   } 	
					   if (is_file($source_file)) {
						 $inner_result = copy($source_file, $des_file);
							if (! empty($inner_result)) {
								$copied_files["success"][$index_1["id"]."=>".$index_9["id"]] = $inner_result;
								$query="INSERT INTO media VALUES (DEFAULT, ";
								$query.= "'{$index_9["featured_image"]}','', 'media/products/' ,{$index_9["id"]} ); ";
							}
							else $copied_files["falied"][$index_1["id"]."=>".$index_9["id"]] = $inner_result;

							// $inserted_to_media [$index_9["id"]] = mysqli_query($con, $query);
							// $inserted_to_media ["errors"][$index_9["id"]] = mysqli_error($con);
							$inserted_to_media = [];
						}
						if(is_dir($soruce_thumbs)) {
							if (!is_dir($des_thumbs)) {
								$copied_dires [$des_thumbs] = mkdir($des_dir,0777,true);
						   } 	
						   if (is_file($source_file_thumbs)) {
						   $inner_result = copy($source_file_thumbs, $des_file_thumbs);
							if (! empty($inner_result))  $copied_files["thumbs"]["success"][$index_1["id"]."=>".$index_9["id"]] = $inner_result;
							else  $copied_files["thumbs"]["falied"][$index_1["id"]."=>".$index_9["id"]] = $inner_result;
						   }
						}
					}
				}
		}
	}
			echo json_encode(["files"=> $copied_files, "dires_created"=>$copied_dires, "inserted_to_media"=> $inserted_to_media]);
			die;
}


function copy_banners()
{
	$con = get_db_con();
	
	$query_1 = "SELECT title, url, path, shop.name
				FROM banner, shop  
				WHERE shop.id = TRIM(url)
				AND area_id = 1;";
	
	$result_1 = mysqli_query($con, $query_1);
	
	$query_9 = "SELECT *  
				FROM   shop  
				WHERE  area_id = 9 ;";

	$result_9 = mysqli_query($con, $query_9);
	
	$stores = mysqli_fetch_all($result_9, MYSQLI_ASSOC);

	$array_len = count($stores);

	$insert_res =  [];

		while ($record = mysqli_fetch_assoc($result_1)) 
			for ($i=0; $i < $array_len; $i++) 
				if ($record["name"] == $stores[$i]["name"]){
					$insert_query = "INSERT INTO banner VALUES (DEFAULT, '{$stores[$i]["name"]}', '{$stores[$i]["id"]}', '{$record["path"]}');";
					$result_insert = mysqli_query($con, $insert_query);
					if (! empty ($result_insert)) 
						$insert_res["SUCCESS"][$stores[$i]["name"]] = $result_insert;
					else
						$insert_res["FALIED"][$stores[$i]["name"]] = $result_insert;
				}
				echo json_encode($insert_res);
				die;
}

function get_banner_for_area($data)
{
	$con = get_db_con();

	$data = prepare_inputs($data);
	$area_id = '%';
	if (isset($data['area_id'])) $area_id = $data['area_id'];

	$query = "SELECT * FROM banner;";

	$result = db_query($con, $query);
	$response = [];
	while ($row = mysqli_fetch_array($result)) {
		$querystore = "SELECT * FROM shop where id='" . $row['url'] . "'";
		$resultstore = db_query($con, $querystore);
		$store = db_fetch_assoc($resultstore);
		if ($store["area_id"] == $area_id) {
			// $path = 'http://3.132.243.163/' . str_replace(' ', '%20',$row['path']);
			$path = 'https://mrkt.ws/' . str_replace(' ', '%20',$row['path']);
			$response[] = [
				'title' => $row['title'],
				'path' => $path,
				'store_id' => $row['url'],
				'store_name' => $store['name']
			];
		}
	}
	close_db_con($con);
	return $response;
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

function get_slots($data)
{
	$data = prepare_inputs($data);

	$con = get_db_con();
	$day = $data['day'];
	$area_id = $data['area_id'];
	$query = "SELECT *  FROM day JOIN slots on slots.day_id = day.id JOIN area on slots.area_id = area.id    WHERE day='${day}' AND area.id = ${area_id} order by slots.id desc LIMIT 1 ";

	$result =  mysqli_fetch_assoc(mysqli_query($con, $query));

	if (isset($result)) return $result;
	else
		return [
			"status" => 450,
			"message" => "no slots aviable for that day in that area",
		];
}

function get_charge($data)
{
	$data = prepare_inputs($data);

	$area_id = '%';

	$output_2 = [];
	$output = new stdClass();

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


function get_sodic_products($data)
{
	set_time_limit(0);
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

	$query = 
	"SELECT product.id AS product_id,product.name AS product_name,shop_id,shop.name AS shop_name,supplier_id,supplier.name AS supplier_name,category_id,category.name AS category_name,
	product.featured AS product_featured,product.promoted AS product_promoted,out_of_stock,price,price_discount,quantity,special_feature,description,product.featured_image AS product_image,shop.featured_image AS featured_image_shop,
	shop.front_image AS front_image_shop,supplier.featured_image AS featured_image_supplier,category.featured_image AS featured_image_category,product.created_at AS product_timestamp 
	FROM product,shop,supplier,category,area WHERE product.shop_id=shop.id AND area_id=area.id AND 
	product.supplier_id=supplier.id AND product.category_id=category.id AND product.shop_id LIKE '" . $shop_id . "' AND product.supplier_id LIKE '" . $supplier_id . "' AND product.category_id LIKE '" . $category_id . "' AND 
	shop.area_id LIKE '".$area_id."'  
	AND product.featured LIKE '" . $featured . "' 
	AND product.promoted LIKE '" . $promoted . "' AND product.out_of_stock LIKE '" . $out_of_stock . "' AND product.active='1' 
	AND shop.active='1' AND supplier.active='1' " . $products . "
	AND (supplier.name LIKE '" . $search_string . "' OR product.name LIKE '" . $search_string . "') 
	AND area.id in (SELECT id from area  where city_id=3)   
	ORDER BY product.promoted DESC,product.featured DESC,product.name ASC";

	
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
		}
		db_free_result($result);
	}
	close_db_con($con);
	return $output;
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
	order_states.id=order_state_id AND orders.id=order_id 
	AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) 
	AND state_num in (7, 6) 
	AND YEAR(states_per_order.created_at) LIKE '" . $year . "' AND MONTH(states_per_order.created_at) LIKE '" . $month . "' AND DAY(states_per_order.created_at) LIKE '" . $day . "' GROUP BY YEAR(states_per_order.created_at),MONTH(states_per_order.created_at)";

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
	order_states.id=order_state_id AND orders.id=order_id
	 AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) 
	 AND state_num='" . ORDER_STATE_CANCELED . "' AND 
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
	order_states.id=order_state_id AND orders.id=order_id 
	AND states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) AND state_num in (7, 6) AND 
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
	// $query = "SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude,city.name AS city_name FROM area,city WHERE city_id=city.id AND city_id LIKE '" . $city_id . "' AND city.id !=3 AND area.id  NOT BETWEEN (SELECT min(id) from area where name like '%Sodic%') and (SELECT MAX(id) from area where name like '%Sodic%') ORDER BY city.name,area.name";

	$query  =  "SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude,city.name AS city_name 
			FROM area
			JOIN city
			on 
			city_id=city.id
			WHERE
			city.id !=3";

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
			$is_open = is_open($row['working_hours']);
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
				"working_hours"=>$row['working_hours'],
				"is_open"=>$is_open,
				'front_image' => $front_image
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function is_open($working_hours) {
	$now = date("H");
	return ($now >$working_hours[0] && $now < $working_hours[1]);
}

function get_products($data)
{
	ini_set("display_errors", true);
	error_reporting(E_ALL);
	
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
	shop.area_id LIKE '" . $area_id . "' AND  shop.area_id NOT in(SELECT id from area where city_id=3) AND product.featured LIKE '" . $featured . "' AND product.promoted LIKE '" . $promoted . "' AND product.out_of_stock LIKE '" . $out_of_stock . "' AND product.active='1' 
	AND shop.active='1' AND supplier.active='1' " . $products . " AND (supplier.name LIKE '" . $search_string . "' OR product.name LIKE '" . $search_string . "') ORDER BY product.promoted DESC,product.featured DESC,product.name ASC";


	if (!empty($limit)) $query .= " LIMIT " . $offset . "," . $limit;

	 $user_id = get_jwt_field('user_id');
	if(isset($user_id))
		$query = is_delievery_staff($con ,$user_id) ? str_replace("AND  shop.area_id NOT in(SELECT id from area where city_id=3)", "", $query) : $query; 

		$result = mysqli_query($con, $query);

	$output = [];
	if ($result && db_num_rows($result)) {
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
		}
		db_free_result($result);
	}
	close_db_con($con);
	return $output;
}

function is_delievery_staff(mysqli $con ,int $user_id) :bool { 
	$result = (mysqli_fetch_row(mysqli_query($con, "select full_name, email, phone, active from delivery_staff WHERE id =${user_id}")) !== null)  ;
	return $result;
}



function remove_empty_products (/**String */ $data = null) {
	set_time_limit(-1);
	
	$removed_products= [];


	$inile_script =""; 

	$prefix= "/var/www/html/media/products/";
	$con = get_db_con();
	$area_id = $data["area_id"]  ?? "%";
	$query = "SELECT product.*
			  FROM   product, shop
			  WHERE  shop.id = shop_id
			  AND    area_id LIKE '${area_id}'";
	$result = mysqli_query($con, $query);
	while ($record = mysqli_fetch_assoc($result)) {
		$id = $record["id"];
		$dir = $prefix .$id.'/';
		$thumbs = $dir."thumbs/";
		$featured_image = $record["featured_image"];
		$image=$prefix.$id.'/'.$featured_image;
		$contains_image = is_dir($dir) ? is_file($image) : false ;
		if (! $contains_image ) {
		if (is_dir($thumbs)) {
			chmod($dir, 0777);
			chmod($thumbs, 0777);
		}
		if (is_dir($thumbs)){
		$removed_products[$id]["server"] = $dir_deleted = rmdir($thumbs) ?  rmdir($dir)  : "Couldn't remove ".$dir;
			$inile_script.= " rm -rf ".$thumbs.'\\';
			$inile_script.= " rm -rf ".$dir.'\\';
		}
			$removed_products[$id]["db"] = remove_product_database($id) ? $id." removed successfully" : "Couldn't remove ".$id;
		}
	}
	
	db_free_result($result);
	close_db_con($con);
	$ob =  new stdClass();
	$ob->removed = $removed_products;
	$ob->scripte= $inile_script;
	return $ob;
} 

function copy_products_image_to_thumbs(Array $data)  { 
	$obj = new stdClass();

	$area_id = $data["area_id"] ?? "%";
	$copied = [] ;
	$con = get_db_con();
	$query  = "SELECT product.id, product.featured_image  FROM product, shop WHERE shop.id = shop_id AND area_id LIKE '". $area_id ."' ";
	$result = mysqli_query($con , $query);
	$prefix = "/var/www/html/media/products/";
	$local_prefix = "/var/www/html/mrkt01/media/products/";
	$all_products = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach ($all_products as $record) {
		$dir = $prefix . $record["id"] . "/";
		$thumbs = $dir. "thumbs/";
		$image = $dir. $record["featured_image"];
		$image_thumbs = $thumbs. $record["featured_image"];
		if (is_file($image) == true) {
			$result = is_dir($thumbs) ?  is_file($image) : mkdir($thumbs, 0777, true);
			$copied[$record["id"]] = copy($image, $image_thumbs);
		}
	}


	// while ($record = mysqli_fetch_assoc($result)) {
	
	// 	$dir = $prefix . $record["id"] . "/";
	// 	$thumbs = $dir. "thumbs/";
	// 	$image = $dir. $record["featured_image"];
	// 	$image_thumbs = $thumbs. $record["featured_image"];
	// 	if (is_file($image) == true) {
	// 		$result = is_dir($thumbs) ?  is_file($image) : mkdir($thumbs, 0777, true);
	// 		$copied[$record["id"]] = copy($image, $image_thumbs);
	// 	}
	// }
	return $copied;
}

function remove_product_database (int $id) :bool{
	$con = get_db_con();
	$query = "DELETE FROM product WHERE id =${id}";
	$result = mysqli_query($con, $query);
	$returned = new stdClass();
	$returned->status = $result ;

	db_free_result($result);
	close_db_con($con);
	return $returned->status;
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

	$product_id = 0 ;
	while ($row = db_fetch_assoc($result)) $output['products'][] = $product_id= $row['product_id'];
	db_free_result($result);
	$product_in = mysqli_fetch_assoc(mysqli_query($con, "SELECT DISTINCT  area_id 
														FROM shop,
															product 
														WHERE shop.id=shop_id
														AND   product.id={$product_id}"));

	$sodic_areas = mysqli_fetch_all(mysqli_query($con, "SELECT id from area where  city_id=3"), MYSQLI_ASSOC);


	$call_sodic_function = false;
	foreach ($sodic_areas as $index)
	if ($index["id"] == $product_in["area_id"]){
	$call_sodic_function = true;
	}

	close_db_con($con);

	if ($call_sodic_function)
	return get_sodic_products($output);
	else
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


function get_orders()
{
	$user_data = get_jwt_field();
	$user_id =$user_data['user_id'];
	$is_delivery=false;
	
	if($user_data['is_delivery_staff'])
	{
		$user_field = 'delivery_staff_id';
		$is_delivery=true;

	} 
	else $user_field = 'user_id';
	
	$con = get_db_con();
	$query = "SELECT orders.id AS order_id,is_paid,delivery_staff_id,total_price, total_price_update_on,delivery_time,payment_method,notes,rating_number,rating_message,orders.created_at AS order_timestamp,order_states.name AS order_state_name,state_num,is_express_delivery,
	city_id,city.name AS city_name,area.id AS area_id,area.name AS area_name,first_name,last_name,user.email AS user_email,user.phone AS user_phone,states_per_order.created_at AS change_state_time,(CASE WHEN orders.street IS NULL THEN user.street ELSE orders.street END) AS street,(CASE WHEN orders.house_number IS NULL THEN user.house_number ELSE orders.house_number END) AS house_number FROM orders,order_states,states_per_order,user,city,area
	WHERE  (orders.created_at)  >=  date_add(current_timestamp() , INTERVAL -5 DAY) AND user.id=user_id AND city.id=area.city_id AND 
	order_states.id=order_state_id AND orders.id=order_id AND " . $user_field . "='" . $user_id . "' AND (CASE WHEN orders.area_id IS NULL THEN user.area_id ELSE orders.area_id END)=area.id AND 
	states_per_order.created_at=(SELECT MAX(created_at) FROM states_per_order WHERE order_id=orders.id) ORDER BY orders.created_at DESC";

	$result = db_query($con, $query);
	$output = [];
	
	if($result && db_num_rows($result))
	{
		while($row = db_fetch_assoc($result))
		{
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

			//return $coupon_get;
			/*****************************/

			/* SERVICE ADD */
			$service_query = "SELECT * FROM charge WHERE charge_area_id={$row['area_id']}";
			$service_result = db_query($con, $service_query);
			$result_fetch = db_fetch_assoc($service_result);
			$charge = $result_fetch['present'] / 100;
			$charge_without_per = (float) $result_fetch['present'];

			/**********************************/
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
			
			$query = "SELECT full_name,email,phone FROM delivery_staff WHERE id='".$delivery_staff_id."'";
			$result1 = db_query($con, $query);
			
			if($result1 && db_num_rows($result1) == 1)
			{
				$row1 = db_fetch_assoc($result1);
				db_free_result($result1);
				
				$delivery_staff_name = $row1['full_name'];
				$delivery_staff_email = strtolower($row1['email']);
				$delivery_staff_phone = $row1['phone'];
			}
			if (empty($row["total_price_update_on"])) {

				$query_2 = "SELECT order_id ,store_id,price FROM order_update WHERE order_id='" . $order_id . "'";

				$result_2 = db_query($con, $query_2);

				$key = 0;
				while ($result_2_fetchted = mysqli_fetch_assoc($result_2)) {
					$store_name_query = "SELECT name,featured_image,front_image FROM shop WHERE id ={$result_2_fetchted['store_id']}";

					$res = mysqli_query($con, $store_name_query);

					$res = mysqli_fetch_assoc($res);

					$price[] =  $price_fetch[$key]["price"] = (float) $result_2_fetchted["price"];


					$price_fetch[$key]["store_name"] =  $res['name'];
					$key++;
				}


				$price = (float) array_sum($price);
				/**********************************/
				if ($row['is_express_delivery'] == 1) $delivery_fee = DELIVERY_FEE_EXPRESS;
				else $delivery_fee = DELIVERY_FEE;

				$total =  (float) $price + $delivery_fee + ($price * $charge) - $present;

				$query_3 = "UPDATE orders SET total_price=${total} WHERE id=${order_id}";
				$result_3 = mysqli_query($con, $query_3);
			}
			$query = "SELECT * 
			FROM products_per_order 
			WHERE order_id='" . $order_id . "' 
			ORDER BY is_fetched DESC";
  
			$result1 = db_query($con, $query);
			if ($result1 && db_num_rows($result1)) {
				while ($row1 = db_fetch_assoc($result1)) {
					$query = "SELECT 
					featured_image, shop_id 
					FROM product
					WHERE id='" . $row1['product_id'] . "'";
					
					$result2 = db_fetch_assoc(db_query($con, $query));
					if (isset($result2)) {
						$shop_name = db_fetch_assoc(db_query($con, 
						"SELECT name 
						FROM shop 
						WHERE id =".$result2["shop_id"]))["name"];
						
						$featured_image = CMS_BASE . PRODUCT_UPLOAD_DIR . $row1['product_id'] . THUMBS_DIR . $result2['featured_image'] ?? '-';
					}

					$products[] = [
						'id' => intval($row1['product_id']),
						'quantity' => intval($row1['quantity']),
						'featured_image' => $featured_image ,
						'single_price' => floatval($row1['single_price']),
						'is_fetched' => boolval($row1['is_fetched']),
					];

				}
					$query_shops = "SELECT DISTINCT  shop_id, shop.name
					FROM products_per_order,shop, product
					WHERE products_per_order.product_id = product.id
					AND shop_id = shop.id
					AND order_id = '". $order_id."';
					";
					$result_shops = mysqli_query($con, $query_shops);
					$shops_1 = mysqli_fetch_all($result_shops, MYSQLI_ASSOC);
					
					foreach($shops_1 as $key => $shop_row) {
							$query_delivery_prodycuts = "SELECT products_per_order.quantity, product.featured_image as featured_image
							, single_price, product.name, product.id as id
							FROM products_per_order,shop, product
							WHERE products_per_order.product_id = product.id
							AND shop_id = shop.id
							AND order_id ='". $order_id."'
							AND shop.id ='".$shop_row["shop_id"]."'";
					$result_delivery_prodycuts = mysqli_query($con, $query_delivery_prodycuts);
					$data = mysqli_fetch_all($result_delivery_prodycuts, MYSQLI_ASSOC) ; 
						foreach($data as $key => $row_3) {
							$data[$key]["featured_image"] =
							 CMS_BASE . PRODUCT_UPLOAD_DIR . $data[$key]['id'] . THUMBS_DIR . $data[$key]['featured_image'] ?? '-';

						}
					$delivery_products[$key] =
					 ["shop_id"=>$shop_row["shop_id"], "shop"=>$shop_row["name"], "products"=> $data];

					}	

				if ($total !== 0) {
					$row["total_price"] = $total;
					$charge_value =   (float) str_replace(',', '.', format_money($total * $charge));
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
				'service_charge' => $charge_without_per,
				"charge_value" => $charge_value,
				'store_price_update' => $price_fetch,
				"delivery_products" => $delivery_products ?? "",
			];
		}
		
		db_free_result($result);
	}
	
	close_db_con($con);
	return $output;
}


function get_sodic_areas($data)
{

	$data = prepare_inputs($data);
	// $city_id = '%';

	// if (isset($data['city_id'])) $city_id = $data['city_id'];

	$con = get_db_con();
	$query = "SELECT area.id AS area_id,area.name AS area_name,zip_code,city_id,express_delivery,latitude,longitude,city.name AS city_name FROM  area,city WHERE city_id like 
( CASE WHEN city.id = 3 or city.name like '%SODIC%' then city.id ELSE (select id from city  where name like '%sodic%') END) 
AND city.id LIKE ( CASE WHEN city.id = 3 or city.name like '%SODIC%' then city.id ELSE (select id from city  where name like '%sodic%') END)
ORDER BY city.name,area.name
";

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


function save_order($data)
{

	$data = prepare_inputs($data);
	$con = get_db_con();

	$notes = $area_id = $street = $house_number = $latitude = $longitude = 'NULL';
	$is_express_delivery = 0;
	$project = "other";
	$order_id = null;
	$user_id = get_jwt_field('user_id');
	$delivery_time = $data['delivery_time'];
	$payment_method = $data['payment_method'];
	if (isset($data['express_delivery'])) $is_express_delivery = intval($data['express_delivery']);
	if (isset($data['project'])) $project = "'" . $data['project'] . "'";
	if (isset($data['notes'])) $notes = "'" . $data['notes'] . "'";
	if (isset($data['area_id'])) $area_id = "'" . $data['area_id'] . "'";
	if (isset($data['street'])) $street = "'" . $data['street'] . "'";
	if (isset($data['house_number'])) $house_number = "'" . $data['house_number'] . "'";
	if (isset($data['latitude'])) $latitude = "'" . $data['latitude'] . "'";
	if (isset($data['longitude'])) $longitude = "'" . $data['longitude'] . "'";
	
	
	// ask merasall how he sends it me 
	if (! is_array($data[0])) {
		unset($data[0]);
	}

	$products = $data['products'];
	$client_ip = $_SERVER['REMOTE_ADDR'];
	$coupon_title = $data['coupon_title'];

	mysqli_set_charset($con, "utf8");
	$query = "INSERT INTO orders(delivery_time,payment_method,user_id,notes,area_id,street,house_number,client_ip,is_express_delivery,latitude,longitude) 
	VALUES('" . $delivery_time . "','" . $payment_method . "','" . $user_id . "'," . $notes . "," . $area_id . "," . $street . "," . $house_number . ",'" . $client_ip . "','" . $is_express_delivery . "'," . $latitude . "," . $longitude . ")";

	if (DB_DRIVER_PREFIX == 'pg') $query .= " RETURNING id";
	$result = db_query($con, $query); //true

	if (DB_DRIVER_PREFIX == 'mysqli') $order_id = db_insert_id($con);
	else if (DB_DRIVER_PREFIX == 'pg') {
		$row = db_fetch_assoc($result);
		db_free_result($result);
		$order_id = $row['id'];
	}

	change_order_state(['id'=> $order_id, "state_num" => ORDER_STATE_CREATED ]);

	$prices_ar = [];
	foreach ($products as $product) {

		$id = $product['id'];
		$price = "SELECT (CASE price_discount WHEN 0 THEN price ELSE price_discount END) FROM product WHERE id= ${id} ";
		$res = db_query($con, $price);
		$price = db_fetch_row($res)[0];

		$quantity = $product['quantity'];
		$query = "INSERT INTO products_per_order(order_id,product_id,single_price,quantity) 
		VALUES('" . $order_id . "','" . $id . "', $price,'" . $quantity . "')";
		$result = db_query($con, $query); //true

		$query = "SELECT shop_id FROM  product  where id =${id}";
		$result = db_query($con, $query); //true
		$store_ids[$id] = db_fetch_row($result)[0];
		$prices_ar[$store_ids[$id]][] = (float) $price * $quantity;
	}


	foreach ($prices_ar as $store_id => $value) {
		if (is_array($value)) $prices_ar[$store_id] = array_sum($value);

		$query = "INSERT INTO order_update(store_id,order_id,price) VALUES( ${store_id} , ${order_id}, {$prices_ar[$store_id]} )";
		$result = db_query($con, $query); //true
	}

	if ($is_express_delivery) $delivery_fee = DELIVERY_FEE_EXPRESS;
	else $delivery_fee = DELIVERY_FEE;
	/*      Coupon  Query   */
	$counpon_quary = "SELECT * FROM coupons WHERE title='$coupon_title'";
	$coupon_res = db_query($con, $counpon_quary); //true
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
	$service_result = db_query($con, $service_query); //true
	$result_fetch = db_fetch_assoc($service_result);
	$charge = $result_fetch['present'] / 100;
	/**User info  */
	$user_assoc_info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * from user WHERE id=${user_id}"));
	/**************************/	$query = "UPDATE orders SET total_price=((SELECT SUM(((single_price+(single_price*$charge))*quantity))-$present FROM products_per_order WHERE order_id='" . $order_id . "')+" . $delivery_fee . ") WHERE id='" . $order_id . "'";
	$result = db_query($con, $query); //true

	if ($result == true) {
		$use_create = "INSERT INTO coupuser (coupon_id,user_id,order_id) VALUES ('" . $coup_id . "','" . $user_id . "','" . $order_id . "')";
		db_query($con, $use_create); //true
	}

	if (!empty($project)) {
		$query_pro = "SELECT * from projects where name REGEXP '${project}' AND area_id=${area_id} ";
		$result_pro = mysqli_query($con, $query_pro);

		if (!empty($result_pro) && (mysqli_num_rows($result_pro)) > 0) {
			$project_id = mysqli_fetch_assoc($result_pro)['id'];
		} else {
			$query = "SELECT id from projects Where name='other' and area_id=${area_id}";
			$result_pro = mysqli_query($con, $query);
			$project_id = mysqli_fetch_assoc($result_pro)['id'];
		}

		$query = "UPDATE orders SET project_id=${project_id}) where id={$order_id}";
		mysqli_query($con, $query);
	}
	close_db_con($con);

	return ['id' => encode_payfort_merchant_reference($order_id)];
}

function rate_order($data)
{
	$data = prepare_inputs($data);
	$rating_message = 'NULL';

	$user_id = get_jwt_field('user_id');
	$order_id = $data['id'];
	$rating_number = $data['rating'];
	if (isset($data['message'])) $rating_message = "'" . $data['message'] . "'";

	$con = get_db_con();
	$query = "UPDATE orders SET rating_number='" . $rating_number . "',rating_message=" . $rating_message . " WHERE id='" . $order_id . "' AND user_id='" . $user_id . "'";
	$result = db_query($con, $query);

	close_db_con($con);
	return get_orders();
}

function change_order_state($data)
{
	$data = prepare_inputs($data);
	$delivery_staff_id = 'NULL';

	$order_id = $data['id'];
	$state_num = $data['state_num'];

	$user_data = get_jwt_field();
	if ($user_data['is_delivery_staff']) $delivery_staff_id = "'" . $user_data['user_id'] . "'";

	$con = get_db_con();
	$query = "INSERT INTO states_per_order(order_id,order_state_id,delivery_staff_id) VALUES ('" . $order_id . "',(SELECT id FROM order_states WHERE state_num='" . $state_num . "')," . $delivery_staff_id . ")";
	$result = db_query($con, $query);
	
	if ($state_num == ORDER_STATE_CREATED ) {
		$query = "SELECT   user.* 
				  FROM     orders, user
				  WHERE    orders.user_id = user.id
				  AND      orders.id ='${order_id}'";

       $result = mysqli_fetch_assoc(mysqli_query($con, $query));

	  send_sms($result['phone']);
	}

	// ORDER_STATE_SENDMAIL
	if ($state_num ==  6) {

		$query = "SELECT  store_id, order_id , price from order_update  WHERE order_id='" . $order_id . "'";
		$result = db_query($con, $query);

		$fetched_order_shops = mysqli_fetch_all($result, MYSQLI_ASSOC);

		$shops = [];
		foreach ($fetched_order_shops as $shop) {
			$query = "SELECT  name from shop  WHERE id='" . $shop['store_id'] . "'";
			$result = db_query($con, $query);
			$fetched_shops = mysqli_fetch_assoc($result);
			$shops[] =     $fetched_shops["name"];
		}

		$query = "SELECT id, user_id , 
		area_id, delivery_time, total_price, total_price_update_on , is_express_delivery from orders  WHERE id='" . $order_id . "'";
		$result = db_query($con, $query);

		$fetched_order = db_fetch_assoc($result);
		$at = format_date($fetched_order['delivery_time']);
		$order_id = $fetched_order['id'];

		$total_price = $fetched_order["total_price"] . " " . CURRENCY_CODE;

		$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=${order_id}";
		$co_orders = db_query($con, $get_offers);
		$cou_orders = db_fetch_assoc($co_orders);
		$id_id = $cou_orders['coupon_id'];
		$get_co = "SELECT * FROM coupons WHERE id=$id_id";
		$co_get = db_query($con, $get_co);
		$coupon_get = db_fetch_assoc($co_get);

		$present = (int) $coupon_get['present'];
		$title = $coupon_get["title"];

		$service_query = "SELECT * FROM charge WHERE charge_area_id={$fetched_order['area_id']}";
		$service_result = db_query($con, $service_query);
		$result_fetch = db_fetch_assoc($service_result);
		$charge = $result_fetch['present'] / 100;
		$charge_without_per = (float) $result_fetch['present'];

		if (empty($fetched_order["total_price_update_on"])) {

			$query_2 = "SELECT * FROM order_update WHERE order_id='" . $order_id . "' ";
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
		$charge *= $price;
		$charge = number_format($charge, 2);
		
		$price+= $charge;
		$title = $title ?? "Not Used";

		$total_charge =  format_money((float)  ($total_price * $charge)) . " " .  CURRENCY_CODE;

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
										<span style='font-size: 15px;'>${charge} EGP</span></p>
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
										<span style='font-size: 15px;'><strong>Products Total</strong></span></p>
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
										<span style='font-size: 15px;'><strong>Coupon (${title})</strong></span></p>
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
										<span style='font-size: 15px;'> -${present} EGP</span></p>
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
	if ($result) {
		if ($state_num == ORDER_STATE_COMPLETED) {
			$query = "UPDATE orders SET is_paid='1' WHERE id='" . $order_id . "'";
			$result = db_query($con, $query);
		} else if ($state_num == ORDER_STATE_CANCELED) {
			payfort_refund($order_id);

			if (DB_DRIVER_PREFIX == 'mysqli') $state_per_order_id = db_insert_id($con);
			else if (DB_DRIVER_PREFIX == 'pg') {
				$row = db_fetch_assoc($result);
				db_free_result($result);

				$state_per_order_id = $row['id'];
			}

			notify_delivery_staff($state_per_order_id);
		}
	}

	close_db_con($con);
	return get_orders();
}

function toggle_product_fetch($data)
{
	$data = prepare_inputs($data);
	$order_id = $data['order_id'];
	$product_id = $data['product_id'];

	$con = get_db_con();
	$query = "UPDATE products_per_order SET is_fetched=(CASE is_fetched WHEN 1 THEN 0 ELSE 1 END) WHERE order_id='" . $order_id . "' AND product_id='" . $product_id . "'";
	$result = db_query($con, $query);

	close_db_con($con);
	return get_orders();
}

function get_payfort_data()
{
	$con = get_db_con();
	$query = "SELECT * FROM options";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$output = [
			'api_url_tokenization' => $row['payfort_api_url_tokenization'],
			'api_url_purchase' => $row['payfort_api_url_purchase'],
			'api_url_check_status' => $row['payfort_api_url_check_status'],
			'api_url_refund' => $row['payfort_api_url_refund'],
			'tokenization_command' => $row['payfort_tokenization_command'],
			'purchase_command' => $row['payfort_purchase_command'],
			'check_status_command' => $row['payfort_check_status_command'],
			'refund_command' => $row['payfort_refund_command'],
			'merchant_id' => $row['payfort_merchant_id'],
			'access_code' => $row['payfort_access_code'],
			'sha_algo' => $row['payfort_sha_algo'],
			'sha_phrase' => $row['payfort_sha_phrase'],
			'language' => $row['payfort_language_code'],
			'currency' => $row['payfort_currency_code'],
			'currency_multiplier' => intval($row['payfort_currency_multiplier']),
			'success_payment_code' => $row['payfort_success_payment_code'],
			'success_refund_code' => $row['payfort_success_refund_code'],
			'return_url_tokenization' => $row['payfort_return_url_tokenization'],
			'return_url_purchase' => $row['payfort_return_url_purchase'],
			'success_url' => $row['payfort_success_url'],
			'error_url' => $row['payfort_error_url']
		];
	}

	close_db_con($con);
	return $output;
}


function get_options($data)
{
	$data = prepare_inputs($data);
	$area_id = $data['area_id'];

	$con = get_db_con();
	$query = "SELECT * FROM options";
	$result = db_query($con, $query);
	$service_query = "SELECT * FROM charge WHERE charge_area_id=" . $area_id;
	$service_result = db_query($con, $service_query);
	$result_fetch = db_fetch_assoc($service_result);
	$charge = $result_fetch['present'] / 100;
	$output = [];

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$output = [
			'currency_code' => $row['currency_code'],
			'min_pass_len' => intval($row['min_pass_len']),
			'max_rate_value' => intval($row['max_rate_value']),
			'delivery_fee' => intval($row['delivery_fee']),
			'delivery_fee_express' => intval($row['delivery_fee_express']),
			'vat' => intval($row['vat']),
			'opening_time' => intval($row['opening_time']),
			'closing_time' => intval($row['closing_time']),
			'service_charge' => $charge
		];
	}

	close_db_con($con);
	return $output;
}

function is_user_logged_in()
{
	if (get_jwt_field('user_id')) $status = true;
	else $status = false;

	return ['status' => $status];
}

function login_user($data)
{

	$email = addslashes(strtolower(trim($data['email'])));
	$password = hash(HASH_ALGO, trim($data['password']));

	$con = get_db_con();
	$query = "SELECT user.id AS user_id,
	 first_name,
	 last_name,
	 email,
	 phone,
	 user.longitude AS longitude,
	 user.latitude AS latitude,
	 created_at,area_id,
	 area.name AS area_name,
	 city_id,city.name AS city_name,
	 country,street,house_number,
	 zip_code
	 ,express_delivery 
	 ,tomorrow 
		FROM user,city,area 
	 WHERE city_id=city.id AND area_id=area.id AND email='" . $email . "' AND password='" . $password . "' AND active='1'";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$id = $row['user_id'];

		$jwt = [
			'user_id' => $id,
			'is_delivery_staff' => false
		];

		$output = [
			'id' => intval($id),
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'email' => strtolower($row['email']),
			'phone' => $row['phone'],
			'created_at' => $row['created_at'],
			'area_id' => intval($row['area_id']),
			'area_name' => $row['area_name'],
			'city_id' => intval($row['city_id']),
			'city_name' => $row['city_name'],
			'country' => $row['country'],
			'street' => $row['street'],
			'longitude' => $row['longitude'],
			'latitude' => $row['latitude'],
			'house_number' => $row['house_number'],
			'zip_code' => $row['zip_code'],
			'express_delivery' => boolval($row['express_delivery']),
			'tomorrow' => boolval($row['tomorrow']),
			'jwt' => generate_jwt($jwt)
		];
	}

	close_db_con($con);
	return $output;
}



function register_user($data)
{
	$data = prepare_inputs($data);

	$first_name = $data['first_name'];
	$last_name = $data['last_name'];
	$email = strtolower($data['email']);
	$phone = $data['phone'];
	$area_id = $data['area_id'];
	$street = $data['street'];
	$house_number = $data['house_number'];
	$raw_pass = $data['password'];
	$password = hash(HASH_ALGO, $raw_pass);

	$con = get_db_con();
	$query = "INSERT INTO user(first_name,last_name,email,phone,area_id,street,house_number,password,active) VALUES('" . $first_name . "','" . $last_name . "','" . $email . "','" . $phone . "','" . $area_id . "','" . $street . "','" . $house_number . "','" . $password . "','1')";
	$result = db_query($con, $query);
	close_db_con($con);

	$data['password'] = $raw_pass;
	return login_user($data);
}



function update_user($data)
{

	$user_id = get_jwt_field('user_id');
	$data = prepare_inputs($data);

	$con = get_db_con();
	$query = buildQueryBody($data);

	$query .= " WHERE id='" . $user_id . "'";

	$result = db_query($con, $query);
	close_db_con($con);

	return ['status' => $result];
}


function buildQueryBody($data)
{
	$query = "UPDATE user SET ";
	foreach ($data as $key => $value) {
		if ($key == "password") 	$value = hash(HASH_ALGO, $data['password']);
		if (!empty($value)) $query .= "{$key} = '{$value}' ,";
		if (!empty($key) && ($key == "area_id")  && ($value == 11))   die;
	}

	return	 $query = substr($query, 0, strlen($query) - 1);
}


function password_reset($data)
{
	$data = prepare_inputs($data);
	$email = strtolower($data['email']);
	$password = substr(md5(mt_rand()), 0, MIN_PASS_LEN);
	$db_pass = hash(HASH_ALGO, $password);

	$con = get_db_con();
	$query = "UPDATE user SET password='" . $db_pass . "' WHERE email='" . $email . "'";
	$result = db_query($con, $query);
	close_db_con($con);

	if ($result) {
		$text = "
			Hi,
			
			Your new password is: <b>" . $password . "</b>
			
			Best regards,
			" . APP_NAME . "
		";

		$result = send_mail($email, '', APP_EMAIL, APP_NAME, 'Password reset', $text);
	}

	return ['status' => $result];
}

function add_device($data)
{
	$user_data = get_jwt_field();
	if (!$user_data['is_delivery_staff']) return ['status' => false];

	$user_id = $user_data['user_id'];

	$data = prepare_inputs($data);
	$device_id = $data['device_id'];

	$con = get_db_con();

	$query = "DELETE FROM delivery_staff_device WHERE device_id='" . $device_id . "' AND delivery_staff_id<>'" . $user_id . "'";
	$result = db_query($con, $query);

	$query = "SELECT id FROM delivery_staff_device WHERE device_id='" . $device_id . "' AND delivery_staff_id='" . $user_id . "'";
	$result = db_query($con, $query);

	if ($result && !db_num_rows($result)) {
		$query = "INSERT INTO delivery_staff_device(device_id,delivery_staff_id) VALUES('" . $device_id . "','" . $user_id . "')";
		$result = db_query($con, $query);

		if ($result) {
			$expo = new ExpoPushHandler;
			$expo->subscribe($user_id, $device_id);
		}
	}

	close_db_con($con);
	return ['status' => $result];
}

function remove_device($data)
{
	$user_data = get_jwt_field();
	if (!$user_data['is_delivery_staff']) return ['status' => false];

	$user_id = $user_data['user_id'];

	$data = prepare_inputs($data);
	$device_id = $data['device_id'];

	$con = get_db_con();
	$query = "DELETE FROM delivery_staff_device WHERE device_id='" . $device_id . "' AND delivery_staff_id='" . $user_id . "'";
	$result = db_query($con, $query);
	close_db_con($con);

	if ($result) {
		$expo = new ExpoPushHandler;
		$expo->unsubscribe($user_id, $device_id);
	}

	return ['status' => $result];
}

function login_delivery_staff($data)
{
	$email = addslashes(strtolower(trim($data['email'])));
	$password = hash(HASH_ALGO, trim($data['password']));

	$con = get_db_con();
	$query = "SELECT delivery_staff.id AS user_id,full_name,email,phone,created_at,area_id,area.name AS area_name,city_id,city.name AS city_name,zip_code,express_delivery FROM delivery_staff,city,area 
	WHERE city_id=city.id AND area_id=area.id AND email='" . $email . "' AND password='" . $password . "' AND active='1'";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		db_free_result($result);

		$id = $row['user_id'];

		$jwt = [
			'user_id' => $id,
			'is_delivery_staff' => true
		];

		$output = [
			'id' => intval($id),
			'full_name' => $row['full_name'],
			'email' => strtolower($row['email']),
			'phone' => $row['phone'],
			'created_at' => $row['created_at'],
			'area_id' => intval($row['area_id']),
			'area_name' => $row['area_name'],
			'city_id' => intval($row['city_id']),
			'city_name' => $row['city_name'],
			'zip_code' => $row['zip_code'],
			'express_delivery' => boolval($row['express_delivery']),
			'jwt' => generate_jwt($jwt)
		];
	}

	close_db_con($con);
	return $output;
}

function prepare_inputs($data)
{
	if (!is_array($data)) return addslashes(trim($data));

	foreach ($data as &$value) $value = prepare_inputs($value);

	return $data;
}


function get_media($base_dir, $record_id)
{
	$con = get_db_con();
	$query = "SELECT * FROM media WHERE upload_dir='" . $base_dir . "' AND record_id='" . $record_id . "' ORDER BY name";
	$result = db_query($con, $query);

	$output = [];

	if ($result && db_num_rows($result)) {
		$base_dir .= $record_id;

		while ($row = db_fetch_assoc($result)) {
			$name = $row['name'];
			if (!is_file($base_dir . '/' . $name)) continue;

			$output[] = [
				'url' => CMS_BASE . $base_dir . '/' . $name,
				'thumb' => CMS_BASE . $base_dir . THUMBS_DIR . $name,
				'title' => $row['title']
			];
		}

		db_free_result($result);
	}

	close_db_con($con);
	return $output;
}

function validate_coupon($data)
{
	$user_id = get_jwt_field('user_id');;
	$coupon_title = $data['coupon_title'];
	$counpon_quary = "SELECT * FROM coupons WHERE title='$coupon_title'";
	$con = get_db_con();
	$coupon_res = db_query($con, $counpon_quary);
	$result_coup = db_fetch_assoc($coupon_res);
	$coup_id = $result_coup['id'];
	$coup_present = $result_coup['present'];
	$coup_use = "SELECT * FROM coupuser WHERE coupon_id=$coup_id AND user_id=$user_id";
	$use_query = db_query($con, $coup_use);
	$use_result = db_fetch_assoc($use_query);
	if ($result_coup['end'] == date('Y-m-d')) {
		return ["message" => "expired_date", "success" => 0];
	} else if ($use_result != null) {
		return ["message" => "used_coupon", "success" => 0];
	} else if ($result_coup == null) {
		return ["message" => "not_exist", "success" => 0];
	} else {

		return ["data" => [
			'messages' => 'valid',
			'present' => $result_coup['present']
		], "success" => 1];
	}
}


function get_banner()
{
	$con = get_db_con();
	$query = "SELECT * FROM banner;";

	$result = db_query($con, $query);
	$response = [];
	while ($row = mysqli_fetch_array($result)) {
		$querystore = "SELECT * FROM shop where id='" . $row['url'] . "'";
		$resultstore = db_query($con, $querystore);
		$store = db_fetch_assoc($resultstore);
		$path = 'http://139.59.137.39/mrkt/' . $row['path'];
		$response[] = [
			'title' => $row['title'],
			'path' => $path,
			'store_id' => $row['url'],
			'store_name' => $store['name']
		];
	}
	return $response;

	close_db_con($con);
}
function update_price($data)
{

	$con = get_db_con();

	$total = $data['total'];
	$order_id = $data['order_id'];

	/**   Coupon  */
	$get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$order_id";
	$co_orders = db_query($con, $get_offers);
	$cou_orders = db_fetch_assoc($co_orders);
	$id_id = $cou_orders['coupon_id'];
	$get_co = "SELECT * FROM coupons WHERE id=$id_id";
	$co_get = db_query($con, $get_co);
	$coupon_get = db_fetch_assoc($co_get);
	$present = $coupon_get['present'];
	/***********************************/


	/** *****************  SELECT express_delivery  ***************** */
	$query_express = "SELECT is_express_delivery ,area_id FROM orders WHERE id =$order_id";
	$result_express = db_query($con, $query_express);
	$is_express_delivery = db_fetch_assoc($result_express);
	//return $is_express_delivery;
	/** **********************************************************/
	if ($is_express_delivery['is_express_delivery'] == 1) $delivery_fee = DELIVERY_FEE_EXPRESS;
	else $delivery_fee = DELIVERY_FEE;


	/* SERVICE Chrage */
	$service_query = " SELECT * FROM charge WHERE charge_area_id={$result_express['area_id']}";
	$service_result = db_query($con, $service_query);
	$result_fetch = db_fetch_assoc($service_result);
	$charge = $result_fetch['present'] / 100;
	/**************************/

	$total_price =  (float) $total + $delivery_fee + ($total * $charge) - $present;

	$query = "UPDATE orders SET total_price=${total_price} , total_price_update_on = true  WHERE id=${order_id}";
	$result = db_query($con, $query);

	if ($result == true) {
		$message = ['message' => 'updated successfully'];
	} else {
		$message = ['message' => 'error'];
	}
	return $message;
}
