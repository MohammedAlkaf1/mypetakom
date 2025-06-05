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
$page_title = "MyPetakom - update committee";
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
include 'connection.php';

$committee_id = $_GET['id'];
$event_id = $_GET['event_id'];

// Fetch current data
$sql = "SELECT * FROM eventcommittee WHERE committee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $committee_id);
$stmt->execute();
$result = $stmt->get_result();
$committee = $result->fetch_assoc();

$roles = $conn->query("SELECT cr_id, cr_desc FROM committee_role");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cr_id = $_POST['cr_id'];
    $updateSql = "UPDATE eventcommittee SET cr_id = ? WHERE committee_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ii", $cr_id, $committee_id);
    if ($updateStmt->execute()) {
        header("Location: assign_committee.php?event_id=$event_id&msg=updated");
        exit();
    } else {
        $error = "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Committee Role</title>
    <link rel="stylesheet" href="../Styles/committee.css">
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
   
</head>
<body>
<?php include '../../../shared/components/header.php'; ?>
<div class="container">
    <?php include '../../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <div class="form-wrapper">
            <h2>Update Committee Role</h2>
            <form method="POST">
                <label for="cr_id">New Role:</label>
                <select name="cr_id" id="cr_id" required>
                    <?php while ($role = $roles->fetch_assoc()): ?>
                        <option value="<?= $role['cr_id'] ?>" <?= $role['cr_id'] == $committee['cr_id'] ? 'selected' : '' ?>>
                            <?= $role['cr_desc'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Update</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

<?php $conn->close(); ?>
