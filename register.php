<?php
$servername = "localhost";
$username = "root"; // default XAMPP MySQL username
$password = ""; // default XAMPP MySQL password
$dbname = "mypetakom"; // the database we created earlier

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT); // Hash password

    // Insert new user into database
    $sql = "INSERT INTO users (username, password) VALUES ('$user', '$hashed_pass')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
