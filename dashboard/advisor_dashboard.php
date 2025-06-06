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

// Include database connection
require_once __DIR__ . '/../sql/db.php';

$user_id = $_SESSION['user_id'];

// Dashboard data queries
$total_events = $conn->query("SELECT COUNT(*) AS total FROM event WHERE event_status = 'Upcoming'")->fetch_assoc()['total'];
$pending_merits = $conn->query("SELECT COUNT(*) AS total FROM merit_application WHERE status = 'Pending'")->fetch_assoc()['total'];
$upcoming_events = $conn->query("SELECT COUNT(*) AS total FROM event WHERE event_status = 'Upcoming'")->fetch_assoc()['total'];

// Page metadata
$page_title = "MyPetakom - Advisor Dashboard";
$logout_url = "../logout.php";
$dashboard_url = "advisor_dashboard.php";
$module_nav_items = [
    
    '../modules/module2/Html_files/event_advisor.php' => 'Events',
    '../modules/module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advisor Dashboard</title>
    <link rel="stylesheet" href="../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../shared/css/components.css">
    <link rel="stylesheet" href="advisor_dashboard.css">
    <script src="../shared/js/prevent-back-button.js"></script>
</head>
<body>
      <?php include_once '../shared/components/header.php'; ?>
<div class="container">
    <?php include_once '../shared/components/sidebar.php'; ?>

    <main class="main-content">
        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Events Created</h3>
                <p><?= $total_events ?></p>
                <a href="../modules/module2/Html_files/event_advisor.php">Show More →</a>
            </div>

            <div class="card">
                <h3>Pending Merit Applications</h3>
                <p><?= $pending_merits ?></p>
                <a href="../modules/module2/Html_files/event_advisor.php">Show More →</a>
            </div>

            <div class="card">
                <h3>Upcoming Events</h3>
                <p><?= $upcoming_events ?></p>
                <a href="../modules/module2/Html_files/event_advisor.php">Show More →</a>
            </div>
        </div>

        <div class="quick-actions">
            <a href="../modules/module2/Html_files/create_event.php"><button>Create New Event</button></a>
            <a href="../modules/module2/Html_files/event_advisor.php"><button>Manage Events</button></a>
        </div>
    </main>
</div>
</body>
</html>

<?php $conn->close(); ?>
