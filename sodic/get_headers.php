<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: " . $method = "GET");
header("Access-Control-Allow-Origin: * ");
header("Expiers:0");

require_once("../includes/config.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] !== $method) die("Sorry , you used " . strtoupper(htmlentities($_SERVER["REQUEST_METHOD"], ENT_QUOTES, "UTF-8")) . " ,and Only " . $method . " HTTP request method is supported for this route, regards.");

$headers = getallheaders();

if (empty(($headers["Authorization"])) || ( strlen($headers["Authorization"]) < 150 )|| ($headers["Authorization"] == "Bearer ")) {
    $message = (json_encode(["code" => 403, "message" => "access denied"]));
}

return $message;