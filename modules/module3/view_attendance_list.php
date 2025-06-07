<?php
session_start();
require_once '../../sql/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$page_title = "MyPetakom - Attendance";
$logout_url = "../../logout.php";
$dashboard_url = "../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    '../../modules/module2/Html_files/event_advisor.php' => 'Events',
    '../module3/attendance.php' => 'Attendance Activity',
];
$current_module = 'attendance.php';
$page_title = "View Event Attendance";

// Get event_id from GET parameter
if (!isset($_GET['event_id'])) {
    exit("Error: Event ID not specified.");
}

$event_id = intval($_GET['event_id']);

// Fetch event details
$stmt = $conn->prepare("SELECT title FROM Event WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title);
$stmt->fetch();
$stmt->close();

if (!$event_title) {
    exit("Error: Event not found.");
}

// Fetch attendance records for this event
$query = "
    SELECT us.name, us.email, aslot.status, a.check_in_time
    FROM Attendance_Slot aslot
    JOIN User us ON aslot.user_id = us.user_id
    JOIN Attendance a ON aslot.attendance_id = a.attendance_id
    WHERE a.event_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../shared/css/shared-layout.css" />
    <link rel="stylesheet" href="../../shared/css/components.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="../../shared/js/prevent-back-button.js"></script>
</head>
<body>
<?php include_once '../../shared/components/header.php'; ?>
<div class="container">
    <?php include_once '../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <h2 class="mb-4">Attendance List for Event: <?php echo htmlspecialchars($event_title); ?></h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Check-in Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows === 0) { ?>
                        <tr>
                            <td colspan="4" class="text-center">No attendance records found for this event.</td>
                        </tr>
                    <?php } else {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['check_in_time']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
<?php
$stmt->close();
?>
