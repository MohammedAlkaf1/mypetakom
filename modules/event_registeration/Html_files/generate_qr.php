<?php
require_once '../vendor/autoload.php';
require_once './connection.php';

use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer;

// 1. Get event ID
if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}
$event_id = intval($_GET['event_id']);
$codeUrl = "http://10.65.84.166/Project%20prototypes/Html_files/student_attendance.php?event_id=" . $event_id;


// 2. Set image path
$qrDir = "../qr_images/";
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true);
}
$imagePath = "{$qrDir}event_{$event_id}.svg";

// 3. Generate QR code and save
$renderer = new ImageRenderer(
    new RendererStyle(300),
    new SvgImageBackEnd()
);

$writer = new Writer($renderer);
file_put_contents($imagePath, $writer->writeString($codeUrl));

// 4. Insert into database
$stmt = $conn->prepare("INSERT INTO qrcode (code, code_status, qr_image) VALUES (?, 'active', ?)");
$stmt->bind_param("ss", $codeUrl, $imagePath);
if ($stmt->execute()) {
    // 5. Redirect back to event_advisor with success message
    header("Location: event_advisor.php?msg=qr_generated&event_id=$event_id");
    exit;
} else {
    echo "Failed to insert into database: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>



