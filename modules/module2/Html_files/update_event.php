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
$page_title = "MyPetakom - update event";
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
include "connection.php";

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $location    = $conn->real_escape_string($_POST['location']);
    $start_date  = $_POST['event_start_date'];
    $status      = $_POST['event_status'];
    $geo         = $conn->real_escape_string($_POST['geolocation']);

    $sql = "UPDATE event SET 
        title='$title',
        description='$description',
        location='$location',
        event_start_date='$start_date',
        event_status='$status',
        geolocation='$geo'
        WHERE event_id=$event_id";

    if ($conn->query($sql) === TRUE) {
        // Redirect back to event_advisor with success
        header("Location: event_advisor.php?msg=updated&title=" . urlencode($title));
        exit();
    } else {
        echo "Error updating: " . $conn->error;
    }
}

// Load existing event data
$sql = "SELECT * FROM event WHERE event_id = $event_id";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    echo "Event not found.";
    exit();
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Event</title>
    <link rel="stylesheet" href="../Styles/create_event.css">
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
    
    <style>
        .main-content h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../../../shared/components/header.php'; ?>

    <div class="container">
        <?php include '../../../shared/components/sidebar.php'; ?>

        <main class="main-content">
            <h2>Update Event</h2>

            <form method="POST" class="event-form">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" required><?= htmlspecialchars($row['description']) ?></textarea>

                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= htmlspecialchars($row['location']) ?>" required>

                <label for="start_date_event">Start Date</label>
                <input type="date" id="event_start_date" name="event_start_date" value="<?= $row['event_start_date'] ?>" required>

                <label for="event_status">Status</label>
                <select id="event_status" name="event_status" required>
                    <option value="pending" <?= $row['event_status']=='pending'?'selected':'' ?>>Pending</option>
                    <option value="approved" <?= $row['event_status']=='approved'?'selected':'' ?>>Approved</option>
                    <option value="rejected" <?= $row['event_status']=='rejected'?'selected':'' ?>>Rejected</option>
                </select>

                <label for="geolocation">Geolocation</label>
                <input type="text" id="geolocation" name="geolocation" value="<?= htmlspecialchars($row['geolocation']) ?>" required>

                <button type="submit" class="submit-btn">Save Changes</button>
            </form>
        </main>
    </div>
</body>
</html>

<?php $conn->close(); ?>


