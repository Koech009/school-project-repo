<?php
$hostname = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "online-crime";

// Create a connection
$conn = new mysqli($hostname, $dbUser, $dbPassword, $dbName);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
