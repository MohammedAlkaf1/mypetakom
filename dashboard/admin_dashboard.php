<?php
session_start();
include('db.php');
include('header.php');
include('sidebar.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../mypetakom/dashboard/admin_dashboard.php");
    exit();
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mypetakom - Admin Dashboard</title>

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styles/sidebar.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .main-layout {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 40px;
            margin-left: 200px;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .menu-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .menu-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            width: calc(50% - 10px);
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .menu-card-icon {
            font-size: 36px;
            color: #025298;
            margin-bottom: 15px;
        }

        .menu-card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .menu-card-description {
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 768px) {
            .menu-card {
                width: 100%;
            }
        }
    </style>
</head>

<body>


    
</body>
</html>
