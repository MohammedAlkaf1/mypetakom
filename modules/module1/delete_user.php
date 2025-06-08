<?php
session_start();
require_once '../../sql/db.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit();
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    echo "Missing user ID.";
    exit();
}

$user_id = intval($_GET['user_id']);

// Step 1: Delete related rows in other tables
$tables_by_user = [
    'View_Awarded_Merits',
    'Merit_Claims',
    'Attendance_Slot',
    'EventCommittee',
    'Membership',
    'Staff'
];

foreach ($tables_by_user as $table) {
    $stmt = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Step 2: Delete from Merit_Application (applied_by)
$stmt = $conn->prepare("DELETE FROM Merit_Application WHERE applied_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Step 3: Delete from Event (added_by)
$stmt = $conn->prepare("DELETE FROM Event WHERE added_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Step 4: Delete from Student table
$stmt = $conn->prepare("DELETE FROM Student WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Step 5: Finally delete from User table
$stmt = $conn->prepare("DELETE FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $stmt->close();
    echo "<script>
        alert('User deleted successfully.');
        window.location.href = 'view_users.php';
    </script>";
    exit();
} else {
    $stmt->close();
    echo "<script>
        alert('Failed to delete user.');
        window.location.href = 'view_users.php';
    </script>";
    exit();
}
?>
