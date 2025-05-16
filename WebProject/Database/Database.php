<?php
$servername = "localhost";
$username = "root"; // or your phpMyAdmin username
$password = "";     // your phpMyAdmin password (blank by default in XAMPP)
$dbname = "mypetakomsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
