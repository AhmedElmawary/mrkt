<?php
/**
 * @author ahmed nassef El-Mawardy
 * 
 */
header("Content-type: application/json");

require_once('includes/config.php');
    
class Api{

    private    $http_methods = [];
    private    $command;
    private    $methods = ["login_user"];

 
private function login_user($request){

    $email = addslashes(strtolower(trim($request['email'])));
	$password = hash(HASH_ALGO, trim($request['password']));

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

    }
    

    function __construct($command)
    {
 
        
    }

    





}