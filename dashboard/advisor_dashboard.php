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

// Include database connection
require_once __DIR__ . '/../sql/db.php';

$user_id = $_SESSION['user_id'];

// Dashboard data queries
$total_events = $conn->query("SELECT COUNT(*) AS total FROM event ")->fetch_assoc()['total'];
$pending_merits = $conn->query("SELECT COUNT(*) AS total FROM merit_application WHERE status = 'Pending'")->fetch_assoc()['total'];
$upcoming_events = $conn->query("SELECT COUNT(*) AS total FROM event WHERE event_status = 'Upcoming'")->fetch_assoc()['total'];

// Page metadata
$page_title = "MyPetakom - Advisor Dashboard";
$logout_url = "../logout.php";
$dashboard_url = "advisor_dashboard.php";
$module_nav_items = [
    
    '../modules/module2/Html_files/event_advisor.php' => 'Events',
    '../modules/module3/attendance.php' => 'Attendance Activity',
    '../modules/module4/approve_merit.php' => 'Approve Merit Claims',
];
$current_module = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advisor Dashboard</title>
    <link rel="stylesheet" href="../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../shared/css/components.css">
    <link rel="stylesheet" href="advisor_dashboard.css?v=<?= time() ?>">
    <script src="../shared/js/prevent-back-button.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    

</head>
<body>
      <?php include_once '../shared/components/header.php'; ?>
<div class="container">
    <?php include_once '../shared/components/sidebar.php'; ?>

    <main class="main-content">
        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Events Created</h3>
                <p><?= $total_events ?></p>
                <a href="../modules/module2/Html_files/event_advisor.php">Show More →</a>
            </div>

            <div class="card">
                <h3>Pending Merit Applications</h3>
                <p><?= $pending_merits ?></p>
                <a href="../modules/module2/Html_files/event_advisor.php">Show More →</a>
            </div>

            <div class="card">
                <h3>Upcoming Events</h3>
                <p><?= $upcoming_events ?></p>
                <a href="../modules/module2/Html_files/event_advisor.php">Show More →</a>
            </div>
        </div>
        <?php
// Fetch data for Pie Chart 1: event_status
            $status_result = $conn->query("SELECT event_status, COUNT(*) AS total FROM event GROUP BY event_status");
            $statuses = [];
            $status_counts = [];
            while ($row = $status_result->fetch_assoc()) {
                $statuses[] = $row['event_status'];
                $status_counts[] = $row['total'];
            }

            // Fetch data for Pie Chart 2: event_level (join with event to make it valid)
            $level_result = $conn->query("SELECT event_level, COUNT(*) AS total FROM merit_application GROUP BY event_level");
            $levels = [];
            $level_counts = [];
            while ($row = $level_result->fetch_assoc()) {
                $levels[] = $row['event_level'];
                $level_counts[] = $row['total'];
            }
        ?>
       
            <div class="chart-wrapper">
                <div class="chart-box">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-box">
                    <canvas id="levelChart"></canvas>
                </div>
            </div>






        
    </main>
</div>
    <script>
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($statuses) ?>,
        datasets: [{
            data: <?= json_encode($status_counts) ?>,
            backgroundColor: ['#2ecc71', '#e67e22', '#e74c3c'] 
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Events by Status'
            }
        }
    }
});


const levelCtx = document.getElementById('levelChart').getContext('2d');
new Chart(levelCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($levels) ?>,
        datasets: [{
            data: <?= json_encode($level_counts) ?>,
            backgroundColor: ['#1abc9c', '#9b59b6', '#f1c40f', '#2ecc71', '#e84393']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Events by Level'
            }
        }
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
