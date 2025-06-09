<?php


session_start();

// These headers are to prevent users from using the browser back button to access this page after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Set up some variables for navigation and page title
$page_title = "MyPetakom - create event";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
// Sidebar navigation items for this module
$module_nav_items = [
    '../../module1/profile.php'=>'Profile',
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>
<?php
// Database connection setup
$host = "localhost";
$user = "root";       // or your MySQL username
$pass = "";           // or your MySQL password
$db   = "mypetakom"; 

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Message variable to show feedback to user
$message = "";

// Handle form submission for creating a new event
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Directory to upload approval letter files
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Prepare file name to avoid conflicts
    $fileName = basename($_FILES["approval_letter"]["name"]);
    $uniqueName = time() . "_" . $fileName;
    $targetFile = $uploadDir . $uniqueName;

    // Move uploaded file to the uploads directory
    if (move_uploaded_file($_FILES["approval_letter"]["tmp_name"], $targetFile)) {
        // Sanitize and collect data from form
        $title       = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $location    = $conn->real_escape_string($_POST['location']);
        $datetime    = $_POST['datetime'];
        $status      = $_POST['status'];
        $geo = $conn->real_escape_string($_POST['geolocation']);

        // Insert event details into the database
        $sql = "INSERT INTO event (title, description, location, event_start_date, event_status, approval_letter, geolocation)
                VALUES ('$title', '$description', '$location', '$datetime', '$status', '$targetFile', '$geo')";

        // Show success or error message based on query result
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
    <!-- Custom CSS for this page -->
    <link rel="stylesheet" href="../Styles/create_event.css" />
    <!-- Shared layout and component styles for consistent look -->
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
    <!-- Google Maps JavaScript API for geolocation and map display -->
    <!-- Replace YOUR_GOOGLE_MAPS_API_KEY with your actual key if deploying -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCskLDQQG9Bj1waQm0a2KOuijdshmD5IuA"></script>
</head>
<body>
    <?php 
    // Include the shared header (logo, user info, etc.)
    include '../../../shared/components/header.php'; 
    ?>

    <div class="container">
        <!-- Sidebar for navigation between modules -->
        <?php include '../../../shared/components/sidebar.php'; ?>

        <main class="main-content">
            <div class="event-header">
                <h2>Create Event</h2>
            </div>

            <!-- Show feedback message after form submission -->
            <?= $message ?>

            <!-- Event creation form -->
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
                <!-- Dropdown for event status -->
                <select id="status" name="status" required>
                    <option value="">Select status</option>
                    <option value="Upcoming">Upcoming</option>
                    <option value="Postponed">Postponed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <label for="letter">Upload Approval Letter</label>
                <!-- File input for uploading approval letter -->
                <input type="file" id="letter" name="approval_letter" required>

                <label for="geo">Geolocation</label>
                <!-- Geolocation input and button to get current location -->
                <div style="display: flex;">
                    <input type="text" id="geo" name="geolocation" required readonly>
                    <button type="button" onclick="getLocation()" style="margin-left: 5px;">📍</button>
                </div>
                <!-- Map preview for selected geolocation -->
                <div id="map" style="width: 100%; height: 250px; margin-top: 10px;"></div>

                <button type="submit" class="submit-btn">Submit Event</button>
            </form>
        </main>
    </div>
    <!-- JavaScript for geolocation and Google Maps integration -->
    <script>
    // This function gets the user's current location using the browser's geolocation API
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                document.getElementById("geo").value = `${lat},${lng}`;
                showMap(lat, lng);
            }, function(error) {
                alert("Error getting location: " + error.message);
            });
        } else {
            alert("Geolocation not supported by this browser.");
        }
    }

    // This function displays a Google Map centered at the given latitude and longitude
    function showMap(lat, lng) {
        const map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: parseFloat(lat), lng: parseFloat(lng) },
            zoom: 15,
        });
        // Add a marker to the map at the user's location
        new google.maps.Marker({
            position: { lat: parseFloat(lat), lng: parseFloat(lng) },
            map: map,
            title: "Your Location",
        });
    }
    </script>

</body>
</html>