<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../../sql/db.php'; // adjust path as needed

// For sidebar navigation highlighting
$page_title = "Apply Membership";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management',
    'apply_membership.php' => 'Apply Membership'
];
$current_module = 'apply_membership.php';

$message = '';

$user_id = $_SESSION['user_id'];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
$studentId = $_POST['student_id'];
$email = $_POST['email'];
$uploadDir = '../../uploads/';
$fileName = basename($_FILES['student_card']['name']);
$targetFile = $uploadDir . time() . '_' . $fileName;

// ✅ Check if this user already applied and still pending
$check = $conn->prepare("SELECT * FROM Membership WHERE user_id = ? AND status = 'pending'");
$check->bind_param("i", $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $message = "You have already submitted a membership application. Please wait for approval.";
} else {
    // ✅ Proceed to upload file and insert new record
    if (move_uploaded_file($_FILES['student_card']['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO Membership (user_id, status, student_matric_card) VALUES (?, 'pending', ?)");
        $stmt->bind_param("is", $user_id, $targetFile);

        if ($stmt->execute()) {
            $message = "Application submitted successfully!";
        } else {
            $message = "Error saving to database.";
        }
    } else {
        $message = "File upload failed.";
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Apply for Membership</title>
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <script src="../../shared/js/prevent-back-button.js"></script>
</head>
<style>
    .main-content {
        padding: 40px;
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    .form-card {
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: auto;
    }

    .form-card h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .form-card label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .form-card input[type="text"],
    .form-card input[type="email"],
    .form-card input[type="file"] {
        width: 100%;
        padding: 10px 14px;
        margin-bottom: 18px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    .form-card button {
        padding: 12px 24px;
        background-color: #4a6cf7;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .form-card button:hover {
        background-color: #3c58d6;
    }

    .form-card p {
        color: green;
        font-weight: bold;
        margin-bottom: 20px;
    }
</style>


<body>
        <?php include_once '../../shared/components/header.php'; ?>
 <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

<div class="main-content">
    <h2>Apply for Petakom Membership</h2>
    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
<div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <label>Full Name:</label><br>
            <input type="text" name="name" required style="width: 100%;"><br><br>

            <label>Student ID:</label><br>
            <input type="text" name="student_id" required style="width: 100%;"><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" required style="width: 100%;"><br><br>

            <label>Upload Student Card:</label><br>
            <input type="file" name="student_card" accept="image/*,application/pdf" required><br><br>

            <button type="submit">Submit Application</button>
        </form>
    </div>

</body>
</html>
