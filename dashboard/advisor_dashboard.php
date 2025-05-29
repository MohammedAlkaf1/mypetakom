<?php
include 'Html_files/connection.php';

// Total Events (excluding Cancelled or Postponed)
$total_events = $conn->query("SELECT COUNT(*) AS total FROM event WHERE event_status = 'Upcoming'")->fetch_assoc()['total'];

// Pending Merit Applications (not yet approved or rejected)
$pending_merits = $conn->query("SELECT COUNT(*) AS total FROM merit_application WHERE status = 'Pending'")->fetch_assoc()['total'];

// Upcoming Events
$upcoming_events = $conn->query("SELECT COUNT(*) AS total FROM event WHERE event_status = 'Upcoming'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Advisor Dashboard</title>
  <link rel="stylesheet" href="../modules/module1/Styles/navbar.css">
  <link rel="stylesheet" href="../modules/module1/Styles/sidebar.css">
  <link rel="stylesheet" href="./advisor_dashboard.css">
</head>
<body>
<?php include 'Html_files/header.php'; ?>
<div class="container">
  <?php include 'Html_files/sidebar.php'; ?>
  <main class="main-content">
    <h2>Welcome, Advisor</h2>

    <div class="dashboard-cards">
      <div class="card">
        <h3>Total Events Created</h3>
        <p><?= $total_events ?></p>
        <a href="event_advisor.php">Show More →</a>
      </div>

      <div class="card">
        <h3>Pending Merit Applications</h3>
        <p><?= $pending_merits ?></p>
        <a href="event_advisor.php">Show More →</a>
      </div>

      <div class="card">
        <h3>Upcoming Events</h3>
        <p><?= $upcoming_events ?></p>
        <a href="event_advisor.php">Show More →</a>
      </div>
    </div>

    <div class="quick-actions">
      <a href="module1/create_event.php"><button>Create New Event</button></a>
      <a href="module1/event_advisor.php"><button>Manage Events</button></a>
    </div>
  </main>
</div>
</body>
</html>

<?php $conn->close(); ?>
