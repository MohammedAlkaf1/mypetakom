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
$page_title = "MyPetakom - View Users";
$logout_url = "../../logout.php";
$dashboard_url = "../../dashboard/admin_dashboard.php"; // or full path if needed

$module_nav_items = [
    '../../dashboard/admin_dashboard.php' => 'Dashboard',
    '../../modules/module1/view_users.php' => 'View Users',
    '../../modules/module1/manage_membership.php' => 'Manage Membership',
    '../../modules/module1/register_user.php' => 'Register New User',
    '../../modules/module1/profile.php' => 'Profile'
];
$current_module = 'view_users.php'; // Set active menu

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access denied. Please login as admin.";
    exit();
}

// Fetch all users from database
$sql = "SELECT user_id, name, email, role FROM user ORDER BY role, name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>View Users - MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <script src="../../shared/js/prevent-back-button.js"></script>

    <style>
        .main-content {
            margin-left: 260px;
            padding: 20px;
            background: #fff;
            min-height: 100vh;
        }

        h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f3f3f3;
        }

        .role-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
            color: white;
        }

        .role-admin {
            background-color: #dc3545;
        }

        .role-staff {
    background-color: rgb(58, 133, 11);
}


        .role-student {
            background-color: #007bff;
        }

        .no-data {
            padding: 20px;
            text-align: center;
            color: #999;
        }
        .btn-action {
    padding: 6px 12px;
    font-size: 0.8rem;
    border-radius: 20px;
    font-weight: 500;
    text-decoration: none;
    margin-right: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: 0.2s ease;
}

.btn-action.edit {
    background-color: #17a2b8;
    color: #fff;
}

.btn-action.edit:hover {
    background-color: #138496;
}

.btn-action.delete {
    background-color: #dc3545;
    color: #fff;
}

.btn-action.delete:hover {
    background-color: #c82333;
}

    </style>
</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

    <div class="main-content">
        <h2><i class="bi bi-people"></i> Registered Users</h2>
        <?php if (isset($_GET['delete'])): ?>
    <?php if ($_GET['delete'] == 'success'): ?>
        <div class="alert alert-success text-center" style="color: green; font-weight: bold; margin-bottom: 15px;">
            User deleted successfully.
        </div>
    <?php elseif ($_GET['delete'] == 'error'): ?>
        <div class="alert alert-danger text-center" style="color: red; font-weight: bold; margin-bottom: 15px;">
            Failed to delete user.
        </div>
    <?php elseif ($_GET['delete'] == 'missing'): ?>
        <div class="alert alert-warning text-center" style="color: orange; font-weight: bold; margin-bottom: 15px;">
            No user ID specified.
        </div>
    <?php endif; ?>
<?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-person"></i> Name</th>
                    <th><i class="bi bi-envelope"></i> Email</th>
                    <th><i class="bi bi-person-badge"></i> Role</th>
                    <th><i class="bi bi-gear"></i> Actions</th>
                </tr>
            </thead>
           <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['user_id']; ?></td> 
        <td><?= htmlspecialchars($row['name']); ?></td>
        <td><?= htmlspecialchars($row['email']); ?></td>
        <td>
            <?php if (strtolower($row['role']) == 'admin'): ?>
    <span class="role-badge role-admin">Admin</span>
<?php elseif (strtolower($row['role']) == 'staff'): ?>
    <span class="role-badge role-staff">Staff</span>
<?php elseif (strtolower($row['role']) == 'student'): ?>
    <span class="role-badge role-student">Student</span>
<?php else: ?>
    <span class="role-badge" style="background:rgb(6, 141, 105);">Unknown</span>
<?php endif; ?>
        </td>
        <td>
            <a href="edit_user.php?user_id=<?= $row['user_id']; ?>" class="btn-action edit">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
            <a href="delete_user.php?user_id=<?= $row['user_id']; ?>" 
   class="btn-action delete" 
   onclick="return confirm('Are you sure you want to delete this user?');">
   <i class="bi bi-trash"></i> Delete
</a>

        </td>
    </tr>
    <?php endwhile; ?>
   </tbody>

        <?php else: ?>
            <div class="no-data"><i class="bi bi-info-circle"></i> No users found.</div>
        <?php endif; ?>
    </div>
</body>
</html>
