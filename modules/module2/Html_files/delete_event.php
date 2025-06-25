<?php


// Include the database connection file
include 'connection.php';

// Check if event_id is provided in the URL
// Check if event_id is provided in the URL
if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // 1. Delete all merit claims for this event
    $deleteMeritClaims = $conn->prepare("DELETE FROM merit_claims WHERE event_id = ?");
    $deleteMeritClaims->bind_param("i", $event_id);
    $deleteMeritClaims->execute();

    // 2. Delete all awarded merits for all merit applications of this event
    $getMeritIDs = $conn->prepare("SELECT merit_id FROM merit_application WHERE event_id = ?");
    // 1. Delete all merit claims for this event
    $deleteMeritClaims = $conn->prepare("DELETE FROM merit_claims WHERE event_id = ?");
    $deleteMeritClaims->bind_param("i", $event_id);
    $deleteMeritClaims->execute();

    // 2. Delete all awarded merits for all merit applications of this event
    $getMeritIDs = $conn->prepare("SELECT merit_id FROM merit_application WHERE event_id = ?");
    $getMeritIDs->bind_param("i", $event_id);
    $getMeritIDs->execute();
    $result = $getMeritIDs->get_result();

    while ($row = $result->fetch_assoc()) {
        $merit_id = $row['merit_id'];
        $merit_id = $row['merit_id'];

        // Delete all awarded merits linked to this merit_id
        $deleteAwarded = $conn->prepare("DELETE FROM view_awarded_merits WHERE merit_id = ?");
        $deleteAwarded->bind_param("i", $merit_id);
        $deleteAwarded->execute();
        // Delete all awarded merits linked to this merit_id
        $deleteAwarded = $conn->prepare("DELETE FROM view_awarded_merits WHERE merit_id = ?");
        $deleteAwarded->bind_param("i", $merit_id);
        $deleteAwarded->execute();
    }

    // 3. Delete all merit_application records for this event
    // 3. Delete all merit_application records for this event
    $deleteMeritApp = $conn->prepare("DELETE FROM merit_application WHERE event_id = ?");
    $deleteMeritApp->bind_param("i", $event_id);
    $deleteMeritApp->execute();

    // 4. Delete all committee assignments for this event
    // 4. Delete all committee assignments for this event
    $deleteEventCommittee = $conn->prepare("DELETE FROM eventcommittee WHERE event_id = ?");
    $deleteEventCommittee->bind_param("i", $event_id);
    $deleteEventCommittee->execute();

    // 5. Delete all attendance_slot records for this event's attendance
    // First, get all attendance_ids for this event
    $getAttendanceIDs = $conn->prepare("SELECT attendance_id FROM attendance WHERE event_id = ?");
    $getAttendanceIDs->bind_param("i", $event_id);
    $getAttendanceIDs->execute();
    $attendanceResult = $getAttendanceIDs->get_result();

    while ($attendanceRow = $attendanceResult->fetch_assoc()) {
        $attendance_id = $attendanceRow['attendance_id'];
        // Delete all attendance_slot records for this attendance_id
        $deleteAttendanceSlot = $conn->prepare("DELETE FROM attendance_slot WHERE attendance_id = ?");
        $deleteAttendanceSlot->bind_param("i", $attendance_id);
        $deleteAttendanceSlot->execute();
    }

    // 6. Delete all attendance records for this event
    $deleteAttendance = $conn->prepare("DELETE FROM attendance WHERE event_id = ?");
    $deleteAttendance->bind_param("i", $event_id);
    $deleteAttendance->execute();

    // 7. Delete all QR codes linked to this event (if you want to remove QRCode row too)
    // Optional: Only do this if you want to remove the QRCode record and it's not used elsewhere
    // $deleteQRCode = $conn->prepare("DELETE FROM qrcode WHERE qrcode_id IN (SELECT qrcode_id FROM event WHERE event_id = ?)");
    // $deleteQRCode->bind_param("i", $event_id);
    // $deleteQRCode->execute();

    // 8. Finally, delete the event itself
    $deleteEvent = $conn->prepare("DELETE FROM event WHERE event_id = ?");
    $deleteEvent->bind_param("i", $event_id);
    $deleteEvent->execute();

    // Redirect back to the event advisor page after deletion
    // Redirect back to the event advisor page after deletion
    header("Location: event_advisor.php");
    exit;

} else {
    // If no event_id is provided, show an error message
    // If no event_id is provided, show an error message
    echo "No event ID provided to delete.";
}
?>