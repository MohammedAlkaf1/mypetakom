<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Coordinator') {
    header("Location: ../Login/Login.html");
    exit();
}
?> 

<!-- The Above Controls the seesion and stops accessing the page if the user is not logged in as a student -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Coordinator/C_Style/CoordinatorDashboard.css">
    <title>Coordinator Dashboard</title>
</head>
<body>
    <!-- Top Header -->
    <div class="top-heading-container">
        MyPETAKOM - Coordinator Dashboard
    </div>

    <div class="container">
        <div class="sidebar">
            <!-- UMP Logo -->
            <div class="logo">
                <img src="../TestImages/UMP Logo.jpg" alt="UMP Logo">
            </div>
            <!-- Profile Picture -->
            <img src="../TestImages/IMG_9255.JPG" alt="Profile Picture">
            <h2>Coordinator</h2>
            <button class="active">Dashboard</button>
            <button>Membership Application</button>
            <button>User Profile</button>
            <button>Merit</button>
            <form action="../Login/Logout.php">
            <button type="submit">Logout</button>
            </form>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Welcome, [Coordinator Name]</h1>
            </div>
            <div class="dashboard-actions">
                <div class="action-card">
                    <h2>Approve Membership</h2>
                </div>
                <div class="action-card">
                    <h2>Manage User</h2>
                </div>
                <div class="action-card">
                    <h2>Pending Application</h2>
                </div>
            </div>
            <div class="pending-membership">
                <h2>Pending Membership</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Matric ID</th>
                            <th>Date Applied</th>
                            <th>Action</th>
                            <th>View Uploaded File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Doe</td>
                            <td>CB12345</td>
                            <td>2025-05-10</td>
                            <td>
                                <button class="approve-button">Approve</button>
                                <button class="reject-button">Reject</button>
                            </td>
                            <td><a href="#">View File</a></td>
                        </tr>
                        <tr>
                            <td>Jane Smith</td>
                            <td>CB67890</td>
                            <td>2025-05-09</td>
                            <td>
                                <button class="approve-button">Approve</button>
                                <button class="reject-button">Reject</button>
                            </td>
                            <td><a href="#">View File</a></td>
                        </tr>
                    </tbody>
                </table>
                <div class="save-button">
                    <button>Save</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>