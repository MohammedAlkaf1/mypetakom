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
    'attendance.php' => 'Attendance Activity',
    '../module4/approve_merit.php' => 'Approve Merit Claims'
];
$current_module = 'attendance.php';

// Toggle attendance status on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_id'])) {
    $aid = $_POST['attendance_id'];
    $status = $_POST['current_status'] === 'Active' ? 'Deactive' : 'Active';
    $conn->query("UPDATE Attendance SET attendance_status = '$status' WHERE attendance_id = $aid");
    header("Location: attendance.php");
    exit();
}

// Fetch all events with their attendance info 
$query = "
    SELECT e.*, a.attendance_id, a.attendance_status
    FROM Event e
    LEFT JOIN Attendance a ON e.event_id = a.event_id
    ORDER BY e.event_id DESC
";
$events = $conn->query($query);
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

    <!-- QRCode.js library from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
      /* Small fix: center QR code inside the cell */
      .qrcode-container {
        display: inline-block;
        max-width: 100px;
      }
    </style>
</head>
<body data-login-url="../../login.php">
<?php include_once '../../shared/components/header.php'; ?>
<div class="container">
    <?php include_once '../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <h2 class="mb-4">Manage Event Attendance</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>QR Code</th>
                        <th>View Attendees</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $qr_codes = []; // store event_id => URL map here for JS to generate QR codes
                    while ($row = $events->fetch_assoc()) {
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_start_date']); ?></td>
                            <td>
                                <?php
                                if (!empty($row['attendance_status'])) {
                                    echo htmlspecialchars($row['attendance_status']);
                                } else {
                                    echo 'Not Created';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($row['attendance_id'])) { ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="attendance_id" value="<?php echo $row['attendance_id']; ?>" />
                                        <input type="hidden" name="current_status" value="<?php echo $row['attendance_status']; ?>" />
                                        <?php if ($row['attendance_status'] === 'Active') { ?>
                                            <button type="submit" class="btn btn-sm btn-danger">Turn Off</button>
                                        <?php } else { ?>
                                            <button type="submit" class="btn btn-sm btn-success">Turn On</button>
                                        <?php } ?>
                                    </form>
                                <?php } else { ?>
                                    <form method="POST" action="create_attendance.php" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>" />
                                        <button type="submit" class="btn btn-sm btn-primary">Create Attendance</button>
                                    </form>
                                <?php } ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($row['attendance_status']) && strtolower($row['attendance_status']) === 'active') {
                                    $qr_url = "http://10.65.87.199/mypetakom/modules/module3/checkin.php?event_id=" . $row['event_id'];
                                    // Save for JS generation later
                                    $qr_codes[$row['event_id']] = $qr_url;
                                    ?>
                                    <div class="qrcode-container" id="qrcode-<?php echo $row['event_id']; ?>"></div>
                                    <?php
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($row['attendance_id'])) { ?>
                                    <form method="GET" action="view_attendance_list.php" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>" />
                                        <button type="submit" class="btn btn-sm btn-secondary">View</button>
                                    </form>
                                    <!-- Delete Button -->
                                     <form method="POST" action="delete_attendance.php" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>" />
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete the attendance for this event?')">Delete</button>
                                    </form>
                                <?php } else {
                                    echo "-";
                                } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ($events->num_rows === 0) { ?>
                        <tr>
                            <td colspan="6">No events found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
// Wait until DOM loads
document.addEventListener("DOMContentLoaded", function() {
    // Data passed from PHP
    const qrData = <?php echo json_encode($qr_codes); ?>;

    // For each event, generate QR code inside its container div
    for (const [eventId, url] of Object.entries(qrData)) {
        new QRCode(document.getElementById('qrcode-' + eventId), {
            text: url,
            width: 100,
            height: 100
        });
    }
});
</script>

</body>
</html>
