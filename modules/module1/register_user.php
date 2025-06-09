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
$page_title = "MyPetakom - New User";
$logout_url = "../../logout.php";
$dashboard_url = "../../dashboard/admin_dashboard.php"; // or full path if needed

$module_nav_items = [
    '../../dashboard/admin_dashboard.php' => 'Dashboard',
    '../../modules/module1/view_users.php' => 'View Users',
    '../../modules/module1/manage_membership.php' => 'Manage Membership',
    '../../modules/module1/register_user.php' => 'Register New User',
    '../../modules/module1/profile.php' => 'Profile'
];
$current_module = 'register_user.php'; // Set active menu
// Restrict to admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit();
}

$message = "";

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $default_password = "123456"; // Default plain password
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT); // Securely hashed

    // Insert into user table
    $stmt = $conn->prepare("INSERT INTO user (name, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $role, $hashed_password);


    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert into student or staff table if needed
        if ($role === 'student') {
            $stmt2 = $conn->prepare("INSERT INTO student (user_id) VALUES (?)");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $stmt2->close();
        } elseif ($role === 'staff') {
            $stmt2 = $conn->prepare("INSERT INTO staff (user_id) VALUES (?)");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $stmt2->close();
        }

        $message = "User registered successfully!";
    } else {
        $message = "Error registering user.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register User - MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <style>
        .main-content {
    margin-left: 260px;
    margin-top: 80px; /* Push below the header */
    padding: 30px;
    background: #f8f9fa;
    min-height: calc(100vh - 80px);
}


        .register-container {
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
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        button {
            width: 100%;
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
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

    <div class="main-content">
        <div class="register-container">
            <h2><i class="bi bi-person-plus"></i> Register New User</h2>

            <?php if ($message): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select role</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="student">Student</option>
                </select>

                <button type="submit"><i class="bi bi-check-circle"></i> Register</button>
            </form>
        </div>
    </div>
</body>
</html>
