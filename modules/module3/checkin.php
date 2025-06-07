<?php
session_start();
require_once '../../sql/db.php';

// Ensure student_id is provided from session
if (!isset($_SESSION['user_id'])) {
    exit("Error: User not logged in.");
}

$user_id = $_SESSION['user_id'];  // Get user_id from session (this should be the logged-in user's ID)

// Get event_id from GET parameter
if (!isset($_GET['event_id'])) {
    exit("Error: Event ID not specified.");
}
$event_id = intval($_GET['event_id']);

// Ensure latitude and longitude are provided
if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
    exit("Error: Location data missing.");
}

$userLat = floatval($_POST['latitude']);
$userLon = floatval($_POST['longitude']);

// Fetch geolocation from Event table (latitude, longitude)
$stmt = $conn->prepare("SELECT geolocation FROM Event WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($eventGeolocation);
if (!$stmt->fetch()) {
    exit("Error: Event not found.");
}
$stmt->close();

// Split geolocation into latitude and longitude
list($eventLat, $eventLon) = explode(',', $eventGeolocation);

// Calculate distance between user and event
function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

$distance = getDistance($userLat, $userLon, $eventLat, $eventLon);

// Ensure the student is within 100 meters of the event
$maxDistance = 100; // meters
if ($distance > $maxDistance) {
    exit("Error: You are too far from the event location.");
}

// Get the active attendance_id for the event (from Attendance table)
$stmt = $conn->prepare("SELECT attendance_id FROM Attendance WHERE event_id = ? AND attendance_status = 'Active' LIMIT 1");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($attendance_id);
if (!$stmt->fetch()) {
    exit("Error: No active attendance session found for this event.");
}
$stmt->close();

// Check if student has already checked in for this attendance session (from Attendance_Slot table)
$stmt = $conn->prepare("SELECT COUNT(*) FROM Attendance_Slot WHERE user_id = ? AND attendance_id = ?");
$stmt->bind_param("ii", $user_id, $attendance_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    exit("You have already checked in.");
}

// Record the attendance slot (Insert check-in into Attendance_Slot)
$stmt = $conn->prepare("INSERT INTO Attendance_Slot (attendance_id, user_id, status) VALUES (?, ?, 'present')");
$stmt->bind_param("ii", $attendance_id, $user_id);
if (!$stmt->execute()) {
    exit("Error: Unable to record attendance.");
}
$stmt->close();

// Success message
echo "Check-in successful!<br>";
echo "Event ID: $event_id<br>";
echo "Distance: " . round($distance) . " meters.";
?>
