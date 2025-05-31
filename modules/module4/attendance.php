<?php
session_start();

// Add these lines to prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Include database connection
require_once '../../sql/db.php';

$user_id = $_SESSION['user_id'];

$page_title = "MyPetakom - Attendance";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management',
];
$current_module = 'attendance.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/mypetakom/shared/css/shared-layout.css">
    <link rel="stylesheet" href="/mypetakom/shared/css/components.css">
    <title>Attendance</title>
    <script src="../../shared/js/prevent-back-button.js"></script>


</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Attendance</h1>
            <p>This is the attendance page.</p>
            <p>Here you can view and manage your attendance records.</p>
            <!-- Add your attendance content here -->
        </div>
    </div>
    
</body>
</html>