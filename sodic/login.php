<?php
ini_set("display_errors", true);

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: ".$method = "POST");
header("Access-Control-Allow-Origin: * ");
header("Expiers:0");

require_once "../includes/config.php";
require_once "../vendor/autoload.php";
require_once "../includes/Firebase/JWT/JWT.php";

use  \Firebase\JWT\JWT; 



if ($_SERVER["REQUEST_METHOD"] !== $method) { die("Sorry , you used " . strtoupper(htmlentities($_SERVER["REQUEST_METHOD"], ENT_QUOTES, "UTF-8")) . " ,and Only ".$method ." HTTP request method is supported for this route, regards.");
}

ob_start();

function login_user($request)
{
    /*
    table sodic_admins  id int(3)
                     name varchar(250) ,
                     email varchar(250),
                     passowrd varchar(250),
                     token varchar(500) 
    */

    if (empty($request["email"]) || empty($request["password"])) {
        die(
            json_encode(
                [
                "status"=>403,
                "message"=>"access denied"
                ]
            )
        );
    }
     $email =  strip_tags(htmlentities(addslashes(strtolower(trim($request["email"]))),  ENT_QUOTES, "UTF-8"));
     $password = hash(HASH_ALGO, htmlentities(trim($request["password"]), ENT_QUOTES, "UTF-8"));

    $con = get_db_con();

    $query = "SELECT id,name , email  from sodic_admins WHERE email='${email}' AND password='${password}'";
  
    $result = mysqli_query($con, $query);
    $output = [];

    if ($result && db_num_rows($result) == 1) {
        $row = db_fetch_assoc($result);
        db_free_result($result);
        $now = time();
        $jwt = [
            "iss"=>"localhost",
            "aud"=>"SODIC_ADMINS",
            "iat"=>$now,
            "nbf"=> $now,
            "exp"=> strtotime("1 hour"),
             "data"=>[
                'user_email' => $row['email'],
                'user_id' => (int) $row['id'],
                'user_name' => strtoupper($row['name']),
             ]
        ];

        
        $jwt2 = [
        'user_id' => $row['id'],
        ];
        
        
        $output = [
            'name'=> $row['name'],
            'email' => strtolower($row['email']),
            'jwt' => JWT::encode($jwt, JWT_SECRET_KEY, "HS256")
            // 'jwt2'=> generate_jwt($jwt2)
        ];
    }

    close_db_con($con);

    return $output;
}

/**
 * Getting HTTP request body 
*/
// $request = file_get_contents("php://input");
/**
 * retrinving  HTTP response  
 * 
 * 
*/
$output =  json_encode(login_user($_POST));

ob_end_flush();


echo ($output);
