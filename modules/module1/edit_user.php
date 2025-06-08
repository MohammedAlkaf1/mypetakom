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
$page_title = "MyPetakom - Edit User";
$logout_url = "../../logout.php";
$dashboard_url = "../../dashboard/admin_dashboard.php"; // or full path if needed

$module_nav_items = [
    '../../dashboard/admin_dashboard.php' => 'Dashboard',
    '../../modules/module1/view_users.php' => 'View Users',
    '../../modules/module1/manage_membership.php' => 'Manage Membership',
    '../../modules/module1/register_user.php' => 'Register New User',
    '../../modules/module1/profile.php' => 'Profile'
];
$current_module = 'edit_user.php'; // Set active menu

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access denied.";
    exit();
}

// Get user ID
if (!isset($_GET['user_id'])) {
    echo "User ID not provided.";
    exit();
}

$user_id = intval($_GET['user_id']);
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $stmt = $conn->prepare("UPDATE user SET name = ?, email = ?, role = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $name, $email, $role, $user_id);

    if ($stmt->execute()) {
        $message = "User updated successfully.";
    } else {
        $message = "Error updating user.";
    }
    $stmt->close();
    
}

// Fetch user data
$stmt = $conn->prepare("SELECT name, email, role FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <style>
        .main-content {
    margin-left: 260px;
    padding: 30px;
    padding-top: 100px; /* ✅ Push content below header */
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
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background-color: #0069d9;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            color: green;
            font-weight: 600;
        }
    </style>
</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>    <div class="main-content">

        <div class="edit-container">
            <h2><i class="bi bi-pencil-square"></i> Edit User</h2>

            <?php if ($message): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user['name']); ?>">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']); ?>">

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="admin" <?= strtolower($user['role']) == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="staff" <?= strtolower($user['role']) == 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="student" <?= strtolower($user['role']) == 'student' ? 'selected' : '' ?>>Student</option>
                </select>

                <button type="submit"><i class="bi bi-save"></i> Update User</button>
            </form>
        </div>
    </div>
</body>
</html>
