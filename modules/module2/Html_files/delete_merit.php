<?php

include 'connection.php';

// Get merit_id and event_id from the URL
$merit_id = $_GET['merit_id'];
$event_id = $_GET['event_id'];

// First, delete all awarded merits linked to this merit_id
$stmt1 = $conn->prepare("DELETE FROM view_awarded_merits WHERE merit_id = ?");
$stmt1->bind_param("i", $merit_id);
$stmt1->execute();
$stmt1->close();

// Now, delete the merit application itself
$stmt2 = $conn->prepare("DELETE FROM merit_application WHERE merit_id = ?");
$stmt2->bind_param("i", $merit_id);

if ($stmt2->execute()) {
    header("Location: event_advisor.php?msg=merit_deleted");
    exit();
} else {
    echo "Error deleting merit application: " . $conn->error;
}

$stmt2->close();
$conn->close();