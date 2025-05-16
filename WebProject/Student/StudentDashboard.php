<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../Login/Login.html");
    exit();
}
?> 

<!-- The Above Controls the seesion and stops accessing the page if the user is not logged in as a student -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="Hamzah" content="Web Engineering Project- Student Dashboard">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Student/S_Style/StudentDash.css">
    <title>MyPetakom - Student Dashboard</title>
</head>
<body>
    <!-- Top Header -->
    <div class="top-heading-container">
        MyPetakom - Student Dashboard
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
            <h2>Hamzah Zeiad (CB23007)</h2>
            <a href="#" class="active">Dashboard</a>
            <a href="#">Profile</a>
            <a href="#">Events</a>
            <a href="#">Attendance</a>
            <a href="#">Merit Management</a>
            <a href="../Login/Login.html">Logout</a>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Welcome, Hamzah Zeiad (CB23007)</h1>
            </div>
            <div class="summary">
                <h3>Quick Summary:</h3>
                <ul>
                    <li>Registered Events: 5</li>
                    <li>Approved Merits: 65</li>
                    <li>Attendance: 4 events</li>
                </ul>
            </div>
            <div class="graphs">
                <div class="graph">Graph 1</div>
                <div class="graph">Graph 2</div>
                <div class="graph">Graph 3</div>
            </div>
        </div>
    </div>
</body>
</html>