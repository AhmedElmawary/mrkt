<?php
require_once('includes/config.php');
require_once('includes/utils.php');
require_once('includes/db.php');


if (isset($_POST['areaId'])) {
    $con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

    $query = "SELECT * FROM shop WHERE area_id=" . $_POST['areaId'];
    $result = mysqli_query($con, $query);
    $row = [];
    while ($response = mysqli_fetch_assoc($result)) {
        $row[] = $response;
    }
    close_db_con($con);

    echo json_encode($row);
    exit;
}
