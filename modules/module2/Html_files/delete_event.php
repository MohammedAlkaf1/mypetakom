<?php
include 'connection.php';

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

   
    $getMeritIDs = $conn->prepare("SELECT Merit_id FROM merit_application WHERE event_id = ?");
    $getMeritIDs->bind_param("i", $event_id);
    $getMeritIDs->execute();
    $result = $getMeritIDs->get_result();

    while ($row = $result->fetch_assoc()) {
        $merit_id = $row['Merit_id'];

        $deleteStudentMerit = $conn->prepare("DELETE FROM student_merit WHERE Merit_id = ?");
        $deleteStudentMerit->bind_param("i", $merit_id);
        $deleteStudentMerit->execute();
    }

   
    $deleteMeritApp = $conn->prepare("DELETE FROM merit_application WHERE event_id = ?");
    $deleteMeritApp->bind_param("i", $event_id);
    $deleteMeritApp->execute();

   
    $deleteEventCommittee = $conn->prepare("DELETE FROM eventcommittee WHERE event_id = ?");
    $deleteEventCommittee->bind_param("i", $event_id);
    $deleteEventCommittee->execute();

   
    $deleteAttendance = $conn->prepare("DELETE FROM attendance WHERE event_id = ?");
    $deleteAttendance->bind_param("i", $event_id);
    $deleteAttendance->execute();

   
    $deleteEvent = $conn->prepare("DELETE FROM event WHERE event_id = ?");
    $deleteEvent->bind_param("i", $event_id);
    $deleteEvent->execute();

    header("Location: event_advisor.php");
    exit;

} else {
    echo "No event ID provided to delete.";
}
?>

