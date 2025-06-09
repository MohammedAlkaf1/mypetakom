<?php
session_start();

// Add these lines to prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

// Include database connection
require_once '../../sql/db.php';

$user_id = $_SESSION['user_id'];

// Set page variables for shared components
$page_title = "MyPetakom - Events";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management',
];
$current_module = 'events.php';

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <link rel="stylesheet" href="../module2/Styles/eventAdv.css">

    <title>Evenets Test Page</title>


</head>

<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

        <!-- Main Content -->
        <!-- Main Content -->
        <div class="main-content">
            <h1>Events</h1>

            <?php
            // Fetch all events
            $sql = "SELECT * FROM event ORDER BY event_start_date DESC";
            $result = $conn->query($sql);
            ?>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="event-card">
                        <h3>Event Name: <?= htmlspecialchars($row['title']) ?></h3>
                        <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars($row['event_start_date']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($row['event_status']) ?></p>
                        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                        <p><strong>Geo:</strong> <?= htmlspecialchars($row['geolocation']) ?></p>
                        <?php if (!empty($row['approval_letter'])): ?>
                            <p><strong>Approval Letter:</strong>
                                <a href="../module2/Html_files/<?= htmlspecialchars($row['approval_letter']) ?>" target="_blank">View</a>
                            </p>
                            
                            <?php
                                $qrPath = "../module2/qr_images/event_" . $row['event_id'] . ".png";
                                if (file_exists($qrPath)): ?>
                                    <div class="qr-preview">
                                        <img src="<?= $qrPath ?>" alt="QR Code" width="160">
                                    </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
        </div>

    </div>

</body>

</html>