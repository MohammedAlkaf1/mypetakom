<?php
include 'connection.php';

$committee_id = $_GET['id'];
$event_id = $_GET['event_id'];

$sql = "DELETE FROM eventcommittee WHERE committee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $committee_id);

if ($stmt->execute()) {
    header("Location: assign_committee.php?event_id=$event_id&msg=deleted");
    exit();
} else {
    echo "Error deleting committee assignment: " . $conn->error;
}

$stmt->close();
$conn->close();
