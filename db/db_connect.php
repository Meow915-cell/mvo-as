<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mvo_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Manila');
?>
