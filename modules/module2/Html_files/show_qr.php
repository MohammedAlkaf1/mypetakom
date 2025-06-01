<?php
$event_id = $_GET['event_id'] ?? null;
if (!$event_id) die("Event ID is required.");

$imagePath = "../qr_images/event_$event_id.png";
?>

<!DOCTYPE html>
<html>
<head>
    <title>QR Code for Event #<?= $event_id ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f4f8ff;
        }
        .qr-box {
            display: inline-block;
            padding: 20px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .qr-box img {
            width: 300px;
        }
        h2 {
            color: #004080;
        }
    </style>
</head>
<body>
    <div class="qr-box">
        <h2>QR Code for Event #<?= $event_id ?></h2>
        <?php if (file_exists($imagePath)) : ?>
            <img src="<?= $imagePath ?>" alt="QR Code">
        <?php else : ?>
            <p style="color:red;">QR image not found.</p>
        <?php endif; ?>
        <p><strong>Scan this to mark attendance</strong></p>
    </div>
</body>
</html>
