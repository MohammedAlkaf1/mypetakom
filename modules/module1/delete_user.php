<?php
session_start();
include '../../sql/db.php';                      
include '../../header.php';                     
include '../../dashboard/sidebar_admin.php'; 

// Only admin can delete users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: modules/module1/view_users.php?delete=missing");
    exit();
}

$user_id = intval($_GET['user_id']);

// Step 1: Delete from dependent tables that reference user_id
$tables_by_user = [
    'View_Awarded_Merits',
    'Merit_Claims',
    'Attendance_Slot',
    'EventCommittee',
    'Membership',
    'Staff'
];

foreach ($tables_by_user as $table) {
    // Delete rows where user_id matches
    $stmt = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Step 2: Delete from Merit_Application using applied_by column
$stmt = $conn->prepare("DELETE FROM Merit_Application WHERE applied_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();


// Step 3: Delete from Event where the user was recorded as added_by
$stmt = $conn->prepare("DELETE FROM Event WHERE added_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Step 4: Delete from Student table using user_id as primary key
$stmt = $conn->prepare("DELETE FROM Student WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Step 5: Finally, delete the user from the User table
$stmt = $conn->prepare("DELETE FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $stmt->close();
    header("Location: /mypetakom/modules/module1/view_users.php?delete=success");
    exit();
} else {
    $stmt->close();
    header("Location: /mypetakom/modules/module1/view_users.php?delete=error");
    exit();
}

?>
