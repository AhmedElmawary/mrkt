<?php

trait DB
{
    private $db = "mrkt_db";
    private $host = "3.12.3.248";
    private $user = "mrkt_admin";
    private $password = "mrkt_password123";

    private function makeConnection()
    {
        try {
            return  new PDO("mysql:host={$this->host};dbname={$this->db};", $this->user, $this->password);
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }
    }
}
