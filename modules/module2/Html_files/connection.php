<?php
$host = "localhost";
$user = "root";
$pass = ""; // Leave empty if no password for MySQL
$db = "mypetakom"; // Your actual database name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

