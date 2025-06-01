<?php
include 'connection.php';

$event_id = $_GET['event_id'];
$user_id = 1; // Replace with session user_id in production

// Check if merit application already exists for this event
$check = $conn->prepare("SELECT merit_id FROM merit_application WHERE event_id = ?");
$check->bind_param("i", $event_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->bind_result($existing_id);
    $check->fetch();
    header("Location: update_merit.php?merit_id=$existing_id&event_id=$event_id");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $level = $_POST['event_level'];
    $main = $_POST['points_main_committee'];
    $comm = $_POST['points_committee'];
    $part = $_POST['points_participant'];

    $stmt = $conn->prepare("INSERT INTO merit_application (event_id, event_level, points_main_committee, points_committee, points_participant, status, applied_by) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("isiiii", $event_id, $level, $main, $comm, $part, $user_id);

    if ($stmt->execute()) {
        header("Location: event_advisor.php?msg=merit_applied");
        exit();
    } else {
        $error = "Failed to apply: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Apply Merit</title>
  <link rel="stylesheet" href="../Styles/navbar.css">
  <link rel="stylesheet" href="../Styles/sidebar.css">
  <link rel="stylesheet" href="../Styles/merit.css">
</head>
<body>
<?php include './navbar.php'; ?>
<div class="container">
    <?php include './sidebar.php'; ?>
    <main class="main-content">
        <div class="form-wrapper">
            <h2>Apply for Merit (Event ID: <?= $event_id ?>)</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST">
                <label>Event Level:</label>
                <select name="event_level" id="event_level" onchange="setMeritPoints()" required>
                    <option value="">-- Select Level --</option>
                    <option value="International">International</option>
                    <option value="National">National</option>
                    <option value="State">State</option>
                    <option value="District">District</option>
                    <option value="UMPSA">UMPSA</option>
                </select>

                <label>Main Committee Points:</label>
                <input type="number" name="points_main_committee" id="main_points" readonly required>

                <label>Committee Points:</label>
                <input type="number" name="points_committee" id="committee_points" readonly required>

                <label>Participant Points:</label>
                <input type="number" name="points_participant" id="participant_points" readonly required>

                <button type="submit">Submit Application</button>
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