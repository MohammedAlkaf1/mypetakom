<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mypetakom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['student_name'];
    $file = $_FILES['student_card'];

    // File upload logic
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $sql = "INSERT INTO membership_requests (student_name, student_card) VALUES ('$name', '$target_file')";
        if ($conn->query($sql) === TRUE) {
            echo "Membership application submitted!";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
