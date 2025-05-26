<?php
include 'connection.php';

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']); 

    // since the event id is used as foregn key in those sites then we have to delete them or database will refuse to delete event
    $deleteEventCommittee = $conn->prepare("DELETE FROM eventcommittee WHERE event_id = ?");
    $deleteEventCommittee->bind_param("i", $event_id);
    $deleteEventCommittee->execute();

    $deleteMerit = $conn->prepare("DELETE FROM merit WHERE event_id = ?");
    $deleteMerit->bind_param("i", $event_id);
    $deleteMerit->execute();

    $deleteAttendance = $conn->prepare("DELETE FROM attendance WHERE event_id = ?");
    $deleteAttendance->bind_param("i", $event_id);
    $deleteAttendance->execute();

    // Finally, delete the event itself
    $deleteEvent = $conn->prepare("DELETE FROM event WHERE event_id = ?");
    $deleteEvent->bind_param("i", $event_id);
    $deleteEvent->execute();

    // now we go back after deletion to event advisor page 
    header("Location: event_advisor.php");
    exit;

} else {
    echo "No event ID provided to delete.";
}
?>

