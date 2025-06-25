<?php


session_start();

// These headers are to prevent users from using the browser back button to access this page after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Set up some variables for navigation and page title
$page_title = "MyPetakom - Apply_merit";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
// Sidebar navigation items for this module
$module_nav_items = [
    '../../module1/profile.php'=>'Profile',
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>

<?php
// Connect to the database
include 'connection.php';

// Get the event_id from the URL query string
$event_id = $_GET['event_id'];

// For now, user_id is hardcoded, but in production, use the session user_id
$user_id = 1; // Replace with $_SESSION['user_id'] in production

// Check if a merit application already exists for this event
$check = $conn->prepare("SELECT merit_id FROM merit_application WHERE event_id = ?");
$check->bind_param("i", $event_id);
$check->execute();
$check->store_result();

// If a merit application exists, redirect to the update page for that merit
if ($check->num_rows > 0) {
    $check->bind_result($existing_id);
    $check->fetch();
    header("Location: update_merit.php?merit_id=$existing_id&event_id=$event_id");
    exit();
}

// If the form is submitted (POST request), insert the new merit application into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $level = $_POST['event_level'];
    $main = $_POST['points_main_committee'];
    $comm = $_POST['points_committee'];
    $part = $_POST['points_participant'];

    // Prepare the SQL statement to insert the merit application
    $stmt = $conn->prepare("INSERT INTO merit_application (event_id, event_level, points_main_committee, points_committee, points_participant, status, applied_by) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("isiiii", $event_id, $level, $main, $comm, $part, $user_id);

    // If the insert is successful, redirect back to event advisor page with a success message
    if ($stmt->execute()) {
        header("Location: event_advisor.php?msg=merit_applied");
        exit();
    } else {
        // If there is an error, show it on the page
        $error = "Failed to apply: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Apply Merit</title>
  <!-- Shared layout and component styles for consistent look across the system -->
  <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
  <link rel="stylesheet" href="../../../shared/css/components.css">
  <!-- Custom CSS for the merit application form -->
  <link rel="stylesheet" href="../Styles/merit.css">
</head>
<body>
<?php 
// Include the shared header (logo, user info, etc.)
include '../../../shared/components/header.php'; 
?>
<div class="container">
    <!-- Sidebar for navigation between modules -->
    <?php include '../../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <div class="form-wrapper">
            <h2>Apply for Merit (Event ID: <?= $event_id ?>)</h2>
            <!-- If there is an error, show it here -->
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <!-- Merit application form -->
            <form method="POST">
                <label>Event Level:</label>
                <!-- Dropdown for selecting event level, triggers JS to set points -->
                <select name="event_level" id="event_level" onchange="setMeritPoints()" required>
                    <option value="">-- Select Level --</option>
                    <option value="International">International</option>
                    <option value="National">National</option>
                    <option value="State">State</option>
                    <option value="District">District</option>
                    <option value="UMPSA">UMPSA</option>
                </select>

                <label>Main Committee Points:</label>
                <!-- Points for main committee, auto-filled by JS, readonly -->
                <input type="number" name="points_main_committee" id="main_points" readonly required>

                <label>Committee Points:</label>
                <!-- Points for committee, auto-filled by JS, readonly -->
                <input type="number" name="points_committee" id="committee_points" readonly required>

                <label>Participant Points:</label>
                <!-- Points for participant, auto-filled by JS, readonly -->
                <input type="number" name="points_participant" id="participant_points" readonly required>

                <button type="submit">Submit Application</button>
            </form>
        </div>
    </main>
</div>

<!-- JavaScript for setting merit points based on event level selection -->
<script>
// This function sets the points for each role based on the selected event level
function setMeritPoints() {
    const level = document.getElementById("event_level").value;
    const main = document.getElementById("main_points");
    const committee = document.getElementById("committee_points");
    const participant = document.getElementById("participant_points");

    // Points for each level and role
    const scores = {
        "International": { main: 100, committee: 70, participant: 50 },
        "National":      { main: 80, committee: 50, participant: 40 },
        "State":         { main: 60, committee: 40, participant: 30 },
        "District":      { main: 40, committee: 30, participant: 15 },
        "UMPSA":         { main: 30, committee: 20, participant: 5 }
    };

    // If a valid level is selected, set the points, otherwise clear the fields
    if (scores[level]) {
        main.value = scores[level].main;
        committee.value = scores[level].committee;
        participant.value = scores[level].participant;
    } else {
        main.value = "";
        committee.value = "";
        participant.value = "";
    }
}
</script>
</body>
</html>

<?php 
// Close the database connection at the end
$conn->close(); 
?>