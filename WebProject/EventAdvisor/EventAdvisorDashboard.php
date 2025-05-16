<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Event Advisor') {
    header("Location: ../Login/Login.html");
    exit();
}
?> 

<!-- The Above Controls the seesion and stops accessing the page if the user is not logged in as a Event Advisor -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../EventAdvisor/E_Style/EventAdvisorDashboard.css">
    <title>Event Advisor Dashboard</title>
</head>
<body>
    <!-- Top Header -->
    <div class="top-heading-container">
        MyPETAKOM - Event Advisor Dashboard
    </div>
    <!-- Main Container -->
    <div class="container">
        <div class="sidebar">
            <!-- UMP Logo -->
            <div class="logo">
                <img src="../TestImages/UMP Logo.jpg" alt="UMP Logo">
            </div>
            <!-- Profile Picture -->
            <img src="../TestImages/IMG_9255.JPG" alt="Profile Picture">
            <h2>Event Advisor</h2>
            <button class="active">Dashboard</button>
            <button>Events</button>
            <button>Merit</button>
            <button>Committee</button>
            <button>Attendance</button>
            <form action="../Login/Logout.php">
            <button type="submit">Logout</button>
            </form>
        </div>
    <div class="main-content">
        <div class="header">
            <h1>Dashboard</h1>
        </div>
        <div class="dashboard-summary">
            <div class="summary-box">
                <h3>Total Events</h3>
                <p>10</p>
            </div>
            <div class="summary-box">
                <h3>Pending Approvals</h3>
                <p>5</p>
            </div>
            <div class="summary-box">
                <h3>Pending Applications</h3>
                <p>3</p>
            </div>
        </div>
        <div class="upcoming-events">
            <h2>Upcoming Events</h2>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>AI Workshop</td>
                        <td>2025-05-20</td>
                        <td>Approved</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Hackathon 2025</td>
                        <td>2025-06-15</td>
                        <td>Pending</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>