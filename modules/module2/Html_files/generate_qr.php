<?php
require_once './connection.php';
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// 1. Get Event ID from URL
$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    die("Event ID is required.");
}

// 2. Define URL for QR code (you can change to localhost or real domain)
$codeUrl = "http://10.65.86.209/Project_prototypes/Html_files/student_attendance.php?event_id=$event_id";


// 3. Generate QR Code
$qr = QrCode::create($codeUrl)
    ->setSize(300)
    ->setMargin(10);
$writer = new PngWriter();
$result = $writer->write($qr);

// 4. Save image locally
$qrDir = "../qr_images/";
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true);
}
$imagePath = "{$qrDir}event_{$event_id}.png";
file_put_contents($imagePath, $result->getString());

// 5. Insert or update in database
$stmt = $conn->prepare("INSERT INTO qrcode (code, code_status, qr_image) VALUES (?, 'active', ?) ON DUPLICATE KEY UPDATE qr_image = VALUES(qr_image)");
$stmt->bind_param("ss", $codeUrl, $imagePath);
$stmt->execute();
$stmt->close();
$conn->close();

// 6. Redirect to page that displays QR
header("Location: show_qr.php?event_id=$event_id");
exit;


