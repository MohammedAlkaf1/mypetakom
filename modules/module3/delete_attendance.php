<?php
session_start();
require_once '../../sql/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_POST['event_id'])) {
    exit("Error: Event ID not specified.");
}

$event_id = intval($_POST['event_id']);

$conn->begin_transaction();

try {
    // Delete the attendance records from Attendance_Slot
    $stmt = $conn->prepare("DELETE FROM Attendance_Slot WHERE attendance_id IN (SELECT attendance_id FROM Attendance WHERE event_id = ?)");
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting attendance slots.");
    }

    // Delete the attendance record from Attendance table
    $stmt = $conn->prepare("DELETE FROM Attendance WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting attendance record.");
    }


    $conn->commit();

    header("Location: attendance.php?success=attendance_deleted");
    exit();
} catch (Exception $e) {
    $conn->rollback();

    header("Location: attendance.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
