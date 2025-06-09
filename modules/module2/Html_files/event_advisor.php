<?php


session_start();

// These headers are set to make sure users can't use the back button to access this page after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Set up some variables for navigation and page title
$page_title = "MyPetakom - Manage events";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
// Sidebar navigation items for this module
$module_nav_items = [
    'event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
    '../../module4/approve_merit.php' => 'Approve Merit Claims',
];
$current_module = 'event_advisor.php';
?>
<?php
// Connect to the database
include 'connection.php';

// Get all events from the database, newest first
$sql = "SELECT * FROM event ORDER BY event_start_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyPetakom - Manage Events</title>
    <!-- Custom CSS for this page -->
    <link rel="stylesheet" href="../Styles/eventAdv.css">
    <!-- Shared layout and component styles for consistent look -->
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
    <!-- No Bootstrap here, but if there was, it would be linked like this:
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> -->
</head>

<body>

    <!-- This includes the header (logo, user info, etc.) -->
    <?php include '../../../shared/components/header.php'; ?>

    <div class="container">
        <!-- Sidebar for navigation between modules -->
        <?php include '../../../shared/components/sidebar.php'; ?>

        <main class="main-content">
            <div class="event-header">
                <h2>Manage Events</h2>
                <!-- Button to add a new event -->
                <a href="create_event.php"><button class="add-event">+ Add Event</button></a>
            </div>

            <!-- Show toast messages for actions like delete, update, etc. -->
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
                <!-- This script hides the toast message after 3 seconds -->
                <script>
                    // This will remove the toast message after 3 seconds so it doesn't stay on the screen
                    setTimeout(() => {
                        const toast = document.getElementById('toast-msg');
                        if (toast) toast.remove();
                    }, 3000);
                </script>
            <?php endif; ?>

            <!-- Check if there are any events to show -->
            <?php if ($result->num_rows > 0): ?>
                <!-- Loop through each event and display its details -->
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="event-card">
                        <!-- Show event details -->
                        <h3>Event Name: <?= htmlspecialchars($row['title']) ?></h3>
                        <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars($row['event_start_date']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($row['event_status']) ?></p>
                        <p><strong>Geo:</strong> <?= htmlspecialchars($row['geolocation']) ?></p>
                        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                        <p><strong>Approval Letter:</strong>
                            <!-- Link to view the approval letter file -->
                            <a href="<?= $row['approval_letter'] ?>" target="_blank">View</a>
                            </p>
                        
                        <?php
                        // Check if a QR code image exists for this event
                        $qrPath = "../qr_images/event_" . $row['event_id'] . ".png";
                        if (file_exists($qrPath)): ?>
                            <!-- If QR code exists, show it here -->
                            <div class="qr-preview">
                                <img src="<?= $qrPath ?>" alt="QR Code" width="160">
                            </div>
                        <?php endif; ?>

                        <!-- Action buttons for each event -->
                        <div class="event-actions">
                            <!-- Update event button -->
                            <a href="update_event.php?event_id=<?= $row['event_id'] ?>"><button>Update</button></a>
                            <!-- Delete event button, asks for confirmation -->
                            <a href="delete_event.php?event_id=<?= $row['event_id'] ?>" onclick="return confirm('Are you sure?');"><button>Delete</button></a>
                            <!-- Assign committee button -->
                            <a href="assign_committee.php?event_id=<?= $row['event_id'] ?>"><button>Assign</button></a>
                            <!-- Generate QR code button -->
                            <a href="generate_qr.php?event_id=<?= $row['event_id'] ?>"><button>Generate QR</button></a>

                            <?php
                            // Check if this event already has a merit application
                            $eid = $row['event_id'];
                            $merit_check = $conn->query("SELECT merit_id FROM merit_application WHERE event_id = $eid");
                            if ($merit_check->num_rows > 0):
                                // If merit exists, get its ID
                                $mid = $merit_check->fetch_assoc()['merit_id'];
                            ?>
                                <!-- Update merit application button -->
                                <a href="update_merit.php?merit_id=<?= $mid ?>&event_id=<?= $eid ?>"><button>Update Merit</button></a>
                                <!-- Delete merit application button, asks for confirmation -->
                                <a href="delete_merit.php?merit_id=<?= $mid ?>&event_id=<?= $eid ?>" onclick="return confirm('Delete merit application?');"><button>Delete Merit</button></a>
                            <?php else: ?>
                                <!-- If no merit application, show apply button -->
                                <a href="apply_merit.php?event_id=<?= $row['event_id']?>"><button>Apply Merit</button></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- If there are no events, show this message -->
                <p>No events found.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php
// Close the database connection at the end
$conn->close();
?>