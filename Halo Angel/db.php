<?php
$host = "localhost";
$username = "root";
$password = "";

$database = "db_haloangel";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset("utf8mb4");