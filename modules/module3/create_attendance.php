<?php
require_once '../../sql/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Check if active attendance already exists for this event
    $checkStmt = $conn->prepare("SELECT attendance_id FROM Attendance WHERE event_id = ? AND attendance_status = 'Active' LIMIT 1");
    $checkStmt->bind_param("i", $event_id);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: attendance.php?error=active_exists");
        exit();
    }
    $checkStmt->close();

    // Fetch the location of the event from the Event table
    $stmt = $conn->prepare("SELECT location FROM Event WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($location);
    $stmt->fetch();
    $stmt->close();

    if (empty($location)) {
        $location = "Unknown Location";
    }

    $check_in_time = date("Y-m-d H:i:s");

    // Insert attendance record with dynamic location
    $insertStmt = $conn->prepare("INSERT INTO Attendance (event_id, check_in_time, location, attendance_status) VALUES (?, ?, ?, 'Active')");
    $insertStmt->bind_param("iss", $event_id, $check_in_time, $location);
    if (!$insertStmt->execute()) {
        // Handle error if needed
        $insertStmt->close();
        header("Location: attendance.php?error=insert_failed");
        exit();
    }
    $insertStmt->close();
}

header("Location: attendance.php");
exit();
?>
