<?php 
require_once('includes/config.php');
require_once('includes/utils.php');
$con = mysqli_connect("localhost","root","","markt");
    $query="SELECT * FROM category";
    $result=mysqli_query($con, $query);
    while($response=mysqli_fetch_assoc($result)){
         $row[]=$response;
    }
    // =db_fetch_assoc($result);
    close_db_con($con);
    return json_encode($row);
   
?>