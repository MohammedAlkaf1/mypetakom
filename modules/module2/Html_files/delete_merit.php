<?php
include 'connection.php';

$merit_id = $_GET['merit_id'];
$event_id = $_GET['event_id'];

$stmt = $conn->prepare("DELETE FROM merit_application WHERE merit_id = ?");
$stmt->bind_param("i", $merit_id);

if ($stmt->execute()) {
    header("Location: event_advisor.php?msg=merit_deleted");
    exit();
} else {
    echo "Error deleting merit application: " . $conn->error;
}

$stmt->close();
$conn->close();
