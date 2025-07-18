<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "MyPetakom - Manage events";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    'event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
    '../../module4/approve_merit.php' => 'Approve Merit Claims',
];
$current_module = 'event_advisor.php';

include 'connection.php';

// ✅ Add search logic
$search = $_GET['search'] ?? '';
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM event WHERE title LIKE ? ORDER BY event_start_date DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("s", $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM event ORDER BY event_start_date DESC");
}
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

            <!-- ✅ Search form with extra margin and clear inline CSS -->
            <form method="GET" style="margin-top: 2.5rem; margin-bottom: 2rem; display: flex; align-items: center;">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by event title"
                    value="<?= htmlspecialchars($search) ?>"
                    style="padding: 10px 12px; width: 270px; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px; margin-right: 10px; background: #fff; color: #222;"
                    autocomplete="off"
                >
                <button type="submit" style="padding: 10px 20px; font-size: 1rem; border-radius: 4px; border: none; background: #007bff; color: #fff; cursor: pointer;">Search</button>
                <?php if ($search): ?>
                    <a href="event_advisor.php" style="margin-left: 14px; color: #007bff; text-decoration: underline;">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (isset($_GET['msg'])): ?>
                <div id="toast-msg" class="toast-alert success">
                    <?php
                    switch ($_GET['msg']) {
                        case 'deleted': echo "Event deleted successfully."; break;
                        case 'updated': echo "Event " . htmlspecialchars($_GET['title']) . " updated successfully."; break;
                        case 'qr_success': echo "QR Code generated successfully."; break;
                        case 'merit_applied': echo "Merit application submitted."; break;
                        case 'merit_updated': echo "Merit application updated."; break;
                        case 'merit_deleted': echo "Merit application deleted."; break;
                    }
                    ?>
                </div>
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