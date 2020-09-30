<?php

define('DB_DRIVER_PREFIX', 'mysqli'); //mysqli or pg

// define('DB_SERVER', 'mrkt-db.clcv0vgre8x3.eu-central-1.rds.amazonaws.com');
// define('DB_USER', 'mrkt_db');
// define('DB_PASS', 'mrkt_db1$');
// define('DB_NAME', 'mrkt_db');

// define('DB_SERVER', 'database-mrkt.ccbrwbeduh24.us-east-2.rds.amazonaws.com');
// define('DB_USER', 'mrkt_admin');
// define('DB_PASS', 'mrkt_password123');
// define('DB_NAME', 'mrkt_db');
define('DB_PORT', '3306');

define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'P@ssword1');
define('DB_NAME', 'mrkt_db');

define('PERSISTENT_CON', true);
define('PG_CONNECTION_STRING', "host=".DB_SERVER." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASS." options='--client_encoding=UTF8'");

$global_db_con = null;


function db_call_function($function, $args)
{
	$function = str_replace('db_', DB_DRIVER_PREFIX.'_', $function);
	
	if(function_exists($function)) return call_user_func_array($function, $args);
	else return null;
}

function get_db_con()
{
	global $global_db_con;
	
	if(!PERSISTENT_CON || !$global_db_con)
	{
		if(DB_DRIVER_PREFIX == 'mysqli') $global_db_con = db_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		else if(DB_DRIVER_PREFIX == 'pg') $global_db_con = db_connect(PG_CONNECTION_STRING);
	}
	
	return $global_db_con;
}

function close_db_con($con)
{
	if(!PERSISTENT_CON) return db_close($con);
	else return false;
}

function db_connect()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_query()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_num_rows()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_fetch_assoc()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_fetch_row()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_free_result()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_close()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_host()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_port()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_version()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_insert_id()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_get_host_info()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

function db_get_server_info()
{
	return db_call_function(__FUNCTION__, func_get_args());
}

?>