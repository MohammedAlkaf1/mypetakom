
<?php
/*
session_start();

// Prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}*/
$page_title = "MyPetakom - update_merit";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    '../../module1/profile.php'=>'Profile',
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?><?php
include 'connection.php';

$merit_id = $_GET['merit_id'];
$event_id = $_GET['event_id'];

// Fetch merit data
$stmt = $conn->prepare("SELECT * FROM merit_application WHERE merit_id = ?");
$stmt->bind_param("i", $merit_id);
$stmt->execute();
$result = $stmt->get_result();
$merit = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $level = $_POST['event_level'];
    $main = $_POST['points_main_committee'];
    $comm = $_POST['points_committee'];
    $part = $_POST['points_participant'];

    $update = $conn->prepare("UPDATE merit_application SET event_level=?, points_main_committee=?, points_committee=?, points_participant=? WHERE merit_id=?");
    $update->bind_param("siiii", $level, $main, $comm, $part, $merit_id);

    if ($update->execute()) {
        header("Location: event_advisor.php?msg=merit_updated");
        exit();
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Merit</title>
  <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
  <link rel="stylesheet" href="../../../shared/css/components.css">
  <link rel="stylesheet" href="../Styles/merit.css">
</head>
<body>
<?php include '../../../shared/components/header.php'; ?>
<div class="container">
    <?php include '../../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <div class="form-wrapper">
            <h2>Update Merit Application</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST">
                <label>Event Level:</label>
                <select name="event_level" id="event_level" onchange="setMeritPoints()" required>
                    <?php
                    $levels = ['International','National','State','District','UMPSA'];
                    foreach ($levels as $lvl):
                    ?>
                        <option value="<?= $lvl ?>" <?= $lvl == $merit['event_level'] ? 'selected' : '' ?>><?= $lvl ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Main Committee Points:</label>
                <input type="number" name="points_main_committee" id="main_points" value="<?= $merit['points_main_committee'] ?>" readonly required>

                <label>Committee Points:</label>
                <input type="number" name="points_committee" id="committee_points" value="<?= $merit['points_committee'] ?>" readonly required>

                <label>Participant Points:</label>
                <input type="number" name="points_participant" id="participant_points" value="<?= $merit['points_participant'] ?>" readonly required>

                <button type="submit">Update Application</button>
                <a href="delete_merit.php?merit_id=<?= $merit_id ?>&event_id=<?= $event_id ?>" onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
            </form>
        </div>
    </main>
</div>

<script>
function setMeritPoints() {
    const level = document.getElementById("event_level").value;
    const main = document.getElementById("main_points");
    const committee = document.getElementById("committee_points");
    const participant = document.getElementById("participant_points");

    const scores = {
        "International": { main: 100, committee: 70, participant: 50 },
        "National":      { main: 80, committee: 50, participant: 40 },
        "State":         { main: 60, committee: 40, participant: 30 },
        "District":      { main: 40, committee: 30, participant: 15 },
        "UMPSA":         { main: 30, committee: 20, participant: 5 }
    };

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

<?php $conn->close(); ?>
