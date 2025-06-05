<?php
session_start();
require_once '../../sql/db.php';

// 1. Require student_id from POST (after credential verification)
if (!isset($_POST['student_id'])) {
    exit("Error: Student ID missing. Please verify login before checking in.");
}
$student_id = intval($_POST['student_id']);

// 2. Get event_id from GET parameter
if (!isset($_GET['event_id'])) {
    exit("Error: Event ID not specified.");
}
$event_id = intval($_GET['event_id']);

// 3. Check if latitude and longitude are provided in POST
if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
    exit("Error: Location data missing. Please allow location access.");
}

$userLat = floatval($_POST['latitude']);
$userLon = floatval($_POST['longitude']);

// 4. Fetch event location from database
$stmt = $conn->prepare("SELECT latitude, longitude FROM Event WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($eventLat, $eventLon);
if (!$stmt->fetch()) {
    $stmt->close();
    exit("Error: Event not found.");
}
$stmt->close();

// 5. Calculate distance between user location and event location
function getDistanceMeters($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

$distance = getDistanceMeters($userLat, $userLon, $eventLat, $eventLon);

// 6. Check if user is within allowed distance (100 meters)
$maxDistance = 100;
if ($distance > $maxDistance) {
    exit("Error: You are too far from the event location to check in. Distance: " . round($distance) . " meters.");
}

// 7. Find active attendance session for this event
$stmt = $conn->prepare("SELECT attendance_id FROM Attendance WHERE event_id = ? AND attendance_status = 'Active' LIMIT 1");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($attendance_id);
if (!$stmt->fetch()) {
    $stmt->close();
    exit("Error: No active attendance session found for this event.");
}
$stmt->close();

// 8. Check if student already checked in for this attendance session
$stmt = $conn->prepare("SELECT COUNT(*) FROM Attendance_Slot WHERE student_id = ? AND attendance_id = ?");
$stmt->bind_param("ii", $student_id, $attendance_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    exit("You have already checked in for this event.");
}

// 9. Record attendance slot
$check_in_time = date("Y-m-d H:i:s");
$stmt = $conn->prepare("INSERT INTO Attendance_Slot (student_id, attendance_id, check_in_time) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $student_id, $attendance_id, $check_in_time);
if (!$stmt->execute()) {
    $stmt->close();
    exit("Error: Unable to record attendance.");
}
$stmt->close();

// 10. Success message
echo "Check-in successful!<br>";
echo "Event ID: " . htmlspecialchars($event_id) . "<br>";
echo "Check-in time: " . $check_in_time . "<br>";
echo "Distance from event: " . round($distance) . " meters.";
?>
