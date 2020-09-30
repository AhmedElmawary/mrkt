<?php

function class_autoload($class_name)
{
	$class_name = str_replace("\\", '/', $class_name);
    require_once(__DIR__ .'/'.$class_name.'.php');
}

spl_autoload_register('class_autoload');

use \Firebase\JWT\JWT;

define('JWT_COOKIE_EXPIRE_MINUTES', JWT_EXPIRATION_TIME);
define('JWT_TOKEN_EXPIRE_MINUTES', JWT_EXPIRATION_TIME);
define('JWT_TOKEN_CHECK_MINUTES', JWT_EXPIRATION_TIME);


function get_jwt_field($field = null)
{
	$data = get_jwt_data();
	
	if(!empty($field))
	{
		if(isset($data[$field])) $data = $data[$field];
		else $data = null;
	}
	
	if(!empty($data)) return $data;
	else return null;
}

function get_jwt_data()
{
	$data = [];
	$requestHeaders = apache_request_headers();
	
	if(!isset($requestHeaders['authorization']) && !isset($requestHeaders['Authorization'])) return $data;
	
	$authorizationHeader = (isset($requestHeaders['authorization'])) ? $requestHeaders['authorization'] : $requestHeaders['Authorization'];
	if($authorizationHeader == "" || $authorizationHeader == "Bearer " ) return $data;
	
	$token = str_ireplace('bearer ', '', $authorizationHeader);
	
	try
	{
		$jwt = JWT::decode($token, JWT_SECRET_KEY, ['HS256']);
	}
	catch(Exception $e)
	{
		return $data;
	}
	
	if(!$jwt) return $data;
	else
	{
		$time = time();
		$new_response['data'] = $data = (array)$jwt->data;
		
		if(($jwt->exp - 60 * JWT_TOKEN_CHECK_MINUTES) < $time)
		{
			$token = [
				'iss' => 'localhost',
				'aud' => 'localhost',
				'iat' => $time,
				'nbf' => $time - 1000,
				'exp' => $time + 60 * JWT_TOKEN_EXPIRE_MINUTES
			];
			
			$token = array_merge($token, $new_response);
			$new_jwt = JWT::encode($token, JWT_SECRET_KEY, 'HS256');
			$expire_time = (new DateTime('+'.JWT_COOKIE_EXPIRE_MINUTES.' minutes'))->format('r');
			
			header('Set-Cookie: jwt='.$new_jwt.'; Expires='.$expire_time.'; Path=/');
		}
	}
	
	return $data;
}

function generate_jwt($data)
{
	$jwt = null;
	$time = time();
	
	$token = [
	    'iss' => 'localhost',
	    'aud' => 'localhost',
	    'iat' => $time,
	    'nbf' => $time - 1000,
	    'exp' => $time + 60 * JWT_TOKEN_EXPIRE_MINUTES
	];
	
	$data = ['data' => $data];
	$token = array_merge($token, $data);
	
	try
	{
		$jwt = JWT::encode($token, JWT_SECRET_KEY, 'HS256');
	}
	catch(Exception $e)
	{
		return $jwt;
	}
	
	return $jwt;
}