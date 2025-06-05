<?php

session_start();

// Prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$page_title = "MyPetakom - create event";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    '../../module1/profile.php'=>'Profile',
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>
<?php
// DB config
$host = "localhost";
$user = "root";       // or your MySQL username
$pass = "";           // or your MySQL password
$db   = "mypetakom"; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES["approval_letter"]["name"]);
    $uniqueName = time() . "_" . $fileName;
    $targetFile = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES["approval_letter"]["tmp_name"], $targetFile)) {
        // Sanitize and collect data
        $title       = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $location    = $conn->real_escape_string($_POST['location']);
        $datetime    = $_POST['datetime'];
        $status      = $_POST['status'];
        $geo = $conn->real_escape_string($_POST['geolocation']);


        // Insert into DB
        $sql = "INSERT INTO event (title, description, location, event_start_date, event_status, approval_letter, geolocation)
                VALUES ('$title', '$description', '$location', '$datetime', '$status', '$targetFile', '$geo')";

        if ($conn->query($sql) === TRUE) {
            $message = "<p style='color: green;'> Event created successfully.</p>";
        } else {
            $message = "<p style='color: red;'> Error: " . $conn->error . "</p>";
        }
    } else {
        $message = "<p style='color: red;'> Failed to upload file.</p>";
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Create Event - MyPetakom</title>
    <link rel="stylesheet" href="../Styles/create_event.css" />
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
    
</head>
<body>
    <?php include '../../../shared/components/header.php'; ?>

    <div class="container">
        <?php include '../../../shared/components/sidebar.php'; ?>


        <main class="main-content">
            <div class="event-header">
                <h2>Create Event</h2>
            </div>

            <?= $message ?>

            <form class="event-form" action="" method="POST" enctype="multipart/form-data">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" required>

                <label for="desc">Description</label>
                <textarea id="desc" name="description" rows="4" required></textarea>

                <label for="location">Location</label>
                <input type="text" id="location" name="location" required>

                <label for="datetime">Start Date & Time</label>
                <input type="datetime-local" id="datetime" name="datetime" required>

                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="">Select status</option>
                    <option value="Upcoming">Upcoming</option>
                    <option value="Postponed">Postponed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <label for="letter">Upload Approval Letter</label>
                <input type="file" id="letter" name="approval_letter" required>

                <label for="geo">Geolocation</label>
                <input type="text" id="geo" name="geolocation" required>

                <button type="submit" class="submit-btn">Submit Event</button>
            </form>
        </main>
    </div>
</body>
</html>
