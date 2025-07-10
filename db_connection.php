<?php
$host = "localhost"; 
$username = "root";
$password = "";
$database = "her_match_up";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
