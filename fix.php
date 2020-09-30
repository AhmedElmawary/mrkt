<?php

$con = mysqli_connect("database-mrkt.ccbrwbeduh24.us-east-2.rds.amazonaws.com", "mrkt_admin", "mrkt_password123", "mrkt_db", 3306);

$orders = mysqli_fetch_all(mysqli_query($con, "SELECT order_id, project_id FROM orders_projects"), MYSQLI_ASSOC);

foreach ($orders as $row) {
   mysqli_query($con, "UPDATE orders SET project_id={$row['project_id']} WHERE id={$row['order_id']}");
}

$orders = mysqli_fetch_all(
                            mysqli_query($con, "SELECT id, area_id, project_id FROM orders WHERE area_id = ANY(SELECT id FROM area WHERE city_id=3) AND project_id IS NULL"), 
                            MYSQLI_ASSOC
                          );

foreach($orders as $order) {
    $projectName = mysqli_fetch_assoc(mysqli_query($con, "SELECT id FROM projects where area_id ={$order['area_id']} AND name='other' "));
    mysqli_query($con, "UPDATE orders SET project_id={$projectName['id']} WHERE id={$order['id']}");
}