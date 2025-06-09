
<?php

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
}
$page_title = "MyPetakom - Manage events";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>
<?php
include 'connection.php';

// Fetch all events
$sql = "SELECT * FROM event ORDER BY event_start_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyPetakom - Manage Events</title>
    <link rel="stylesheet" href="../Styles/eventAdv.css">
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
    
</head>

<body>

    <?php include '../../../shared/components/header.php'; ?>

    <div class="container">
        <?php include '../../../shared/components/sidebar.php'; ?>

        <main class="main-content">
            <div class="event-header">
                <h2>Manage Events</h2>
                <a href="create_event.php"><button class="add-event">+ Add Event</button></a>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'deleted'): ?>
                    <div id="toast-msg" class="toast-alert success">Event deleted successfully.</div>
                <?php elseif ($_GET['msg'] === 'updated'): ?>
                    <div id="toast-msg" class="toast-alert success">Event <?= htmlspecialchars($_GET['title']) ?> updated successfully.</div>
                <?php elseif ($_GET['msg'] === 'qr_success'): ?>
                    <div id="toast-msg" class="toast-alert success"> QR Code generated successfully.</div>
                <?php elseif ($_GET['msg'] === 'merit_applied'): ?>
                    <div id="toast-msg" class="toast-alert success"> Merit application submitted.</div>
                <?php elseif ($_GET['msg'] === 'merit_updated'): ?>
                    <div id="toast-msg" class="toast-alert success"> Merit application updated.</div>
                <?php elseif ($_GET['msg'] === 'merit_deleted'): ?>
                    <div id="toast-msg" class="toast-alert success"> Merit application deleted.</div>
                <?php endif; ?>
                <script>
                    setTimeout(() => {
                        const toast = document.getElementById('toast-msg');
                        if (toast) toast.remove();
                    }, 3000);
                </script>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="event-card">
                        <h3>Event Name: <?= htmlspecialchars($row['title']) ?></h3>
                        <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars($row['event_start_date']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($row['event_status']) ?></p>
                        <p><strong>Geo:</strong> <?= htmlspecialchars($row['geolocation']) ?></p>
                        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                        <p><strong>Approval Letter:</strong>
                            <a href="<?= $row['approval_letter'] ?>" target="_blank">View</a>
                        </p>

                        
                        <?php
                        $qrPath = "../qr_images/event_" . $row['event_id'] . ".png";
                        if (file_exists($qrPath)): ?>
                            <div class="qr-preview">
                                <img src="<?= $qrPath ?>" alt="QR Code" width="160">
                            </div>
                        <?php endif; ?>



                        <div class="event-actions">
                            <a href="update_event.php?event_id=<?= $row['event_id'] ?>"><button>Update</button></a>
                            <a href="delete_event.php?event_id=<?= $row['event_id'] ?>" onclick="return confirm('Are you sure?');"><button>Delete</button></a>
                            <a href="assign_committee.php?event_id=<?= $row['event_id'] ?>"><button>Assign</button></a>
                            <a href="generate_qr.php?event_id=<?= $row['event_id'] ?>"><button>Generate QR</button></a>

                            <?php
                            $eid = $row['event_id'];
                            $merit_check = $conn->query("SELECT merit_id FROM merit_application WHERE event_id = $eid");
                            if ($merit_check->num_rows > 0):
                                $mid = $merit_check->fetch_assoc()['merit_id'];
                            ?>
                                <a href="update_merit.php?merit_id=<?= $mid ?>&event_id=<?= $eid ?>"><button>Update Merit</button></a>
                                <a href="delete_merit.php?merit_id=<?= $mid ?>&event_id=<?= $eid ?>" onclick="return confirm('Delete merit application?');"><button>Delete Merit</button></a>
                            <?php else: ?>
                                <a href="apply_merit.php?event_id=<?= $row['event_id']?>"><button>Apply Merit</button></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php $conn->close(); ?>
