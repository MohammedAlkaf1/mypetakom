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
$page_title = "MyPetakom - Assign committees";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    '../../module1/profile.php'=>'Profile',
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mypetakom";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_GET['event_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_ids = $_POST['user_ids'];
    $cr_ids = $_POST['cr_ids'];

    foreach ($user_ids as $index => $user_id) {
        $cr_id = $cr_ids[$index];
        $sql = "INSERT INTO eventcommittee (event_id, user_id, cr_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $event_id, $user_id, $cr_id);
        $stmt->execute();
    }
    $success = "✅ Committees assigned successfully.";
}

$students = $conn->query("SELECT user_id, name FROM user WHERE role='student'");
$roles = $conn->query("SELECT cr_id, cr_desc FROM committee_role");
$committees = $conn->query("SELECT ec.committee_id, u.name, cr.cr_desc FROM eventcommittee ec 
                            JOIN user u ON ec.user_id = u.user_id 
                            JOIN committee_role cr ON ec.cr_id = cr.cr_id 
                            WHERE ec.event_id = $event_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Committee</title>
    <link rel="stylesheet" href="../Styles/committee.css">
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
    
</head>
<body>
<?php include '../../../shared/components/header.php'; ?>
<div class="container" id="container">
    <?php include '../../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <div class="form-wrapper">
            <h2>Assign Committees for Event ID: <?= $event_id ?></h2>
            <?php if (isset($success)): ?>
                <p class="message success"><?= $success ?></p>
            <?php endif; ?>
            <form method="POST">
                <div id="assignments">
                    <div class="assignment-row">
                        <select name="user_ids[]" required>
                            <option value="">-- Select Student --</option>
                            <?php mysqli_data_seek($students, 0); while($s = $students->fetch_assoc()): ?>
                                <option value="<?= $s['user_id'] ?>"><?= $s['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <select name="cr_ids[]" required>
                            <option value="">-- Select Role --</option>
                            <?php mysqli_data_seek($roles, 0); while($r = $roles->fetch_assoc()): ?>
                                <option value="<?= $r['cr_id'] ?>"><?= $r['cr_desc'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <button type="button" onclick="addRow()">+ Add Another</button>
                <button type="submit">Assign Committees</button>
            </form>
        </div>

        <div class="table-wrapper">
            <h3>Already Assigned Committees</h3>
            <table>
                <thead>
                    <tr><th>Name</th><th>Role</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($c = $committees->fetch_assoc()): ?>
                        <tr>
                            <td><?= $c['name'] ?></td>
                            <td><?= $c['cr_desc'] ?></td>
                            <td>
                                <a href="update_committee.php?id=<?= $c['committee_id'] ?>&event_id=<?= $event_id ?>">Edit</a>
                                <a href="delete_committee.php?id=<?= $c['committee_id'] ?>&event_id=<?= $event_id ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
function addRow() {
    const row = document.createElement('div');
    row.classList.add('assignment-row');
    row.innerHTML = document.querySelector('.assignment-row').innerHTML;
    document.getElementById('assignments').appendChild(row);
}
</script>
</body>
</html>

<?php $conn->close(); ?>
