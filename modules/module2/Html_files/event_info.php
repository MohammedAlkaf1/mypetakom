<?php
session_start();
require_once '../../../sql/db.php';

// Validate event ID
if (!isset($_GET['event_id'])) {
    echo "<h2>No event ID provided.</h2>";
    exit();
}


$event_id = intval($_GET['event_id']);

// Fetch event data
$event_stmt = $conn->prepare("SELECT * FROM event WHERE event_id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    echo "<h2>Event not found.</h2>";
    exit();
}

// Fetch committee members
$committee_stmt = $conn->prepare("SELECT u.name, c.role_name FROM eventcommittee ec JOIN user u ON ec.user_id = u.user_id JOIN committee_role c ON ec.cr_id = c.cr_id WHERE ec.event_id = ?");
$committee_stmt->bind_param("i", $event_id);
$committee_stmt->execute();
$committee_result = $committee_stmt->get_result();
$committees = $committee_result->fetch_all(MYSQLI_ASSOC);

// Fetch merit application
$merit_stmt = $conn->prepare("SELECT status FROM merit_application WHERE event_id = ?");
$merit_stmt->bind_param("i", $event_id);
$merit_stmt->execute();
$merit_result = $merit_stmt->get_result();
$merit_status = $merit_result->fetch_assoc()['status'] ?? 'Not Applied';

// Page metadata
$page_title = "MyPetakom - Event Info";
$logout_url = "../../logout.php";
$dashboard_url = "../module2/events.php";
$module_nav_items = [
    '../module1/profile.php' => 'Profile',
    '../module2/events.php' => 'Events',
    '../module3/attendance.php' => 'Attendance',
    '../module4/merit_management.php' => 'Merit Management',
];
$current_module = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <script src="../../shared/js/prevent-back-button.js"></script>
    <style>
        .event-box {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 2rem auto;
        }
        .event-box h2 {
            color: #004080;
        }
        .section {
            margin-top: 1.5rem;
        }
        .section ul {
            padding-left: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Header Include -->
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <!-- Sidebar Include -->
        <?php include_once '../../shared/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="event-box">
                <h2><?= htmlspecialchars($event['title']) ?></h2>
                <p><strong>Date:</strong> <?= htmlspecialchars($event['event_start_date']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($event['event_status']) ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                <p><strong>Geo-location:</strong> <?= htmlspecialchars($event['geolocation']) ?></p>

                <div class="section">
                    <h3>Committee Members</h3>
                    <?php if (!empty($committees)): ?>
                        <ul>
                            <?php foreach ($committees as $c): ?>
                                <li><strong><?= htmlspecialchars($c['role_name']) ?>:</strong> <?= htmlspecialchars($c['name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No committee members assigned.</p>
                    <?php endif; ?>
                </div>

                <div class="section">
                    <h3>Merit Application Status</h3>
                    <p><?= htmlspecialchars($merit_status) ?></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php $conn->close(); ?>
