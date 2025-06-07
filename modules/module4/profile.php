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

// Move this here BEFORE the query
$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Update the user info in the database
    $update_stmt = $conn->prepare("UPDATE User SET name = ?, email = ? WHERE user_id = ?");
    $update_stmt->bind_param("ssi", $name, $email, $user_id);

    if ($update_stmt->execute()) {
        $message = "Profile updated successfully.";
        $user['name'] = $name;
        $user['email'] = $email;
    } else {
        $message = "Failed to update profile.";
    }

    $update_stmt->close();
}

// Get user data
$stmt = $conn->prepare("SELECT name, email FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Set page variables for shared components
$page_title = "MyPetakom - Profile";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management',
    'apply_membership.php' => 'Apply Membership'
];
$current_module = 'profile.php';
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
    <title>Profile Test Page</title>

    <script src="../../shared/js/prevent-back-button.js"></script>
     <style>
        .main-content {
            margin-left: 260px;
            margin-top: 80px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .edit-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background-color: #0069d9;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            color: green;
        }
    </style>

</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

      <!-- Main Content -->
<div class="main-content">
    <h1>Profile</h1>

    <div class="edit-container">
        <h2><i class="bi bi-person-lines-fill"></i> Edit My Profile</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user['name']) ?>">

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['email']) ?>">

            <button type="submit"><i class="bi bi-save"></i> Save Changes</button>
        </form>
    </div>
   </div>
    </div>
</body>
</html>