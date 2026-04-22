<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "OrlandoAirportDB";

$conn = new mysqli($host, $user, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error){
	die("Connection Failed: " . $conn->connect_error);
}
?>