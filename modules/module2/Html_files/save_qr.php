<?php

include 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['event_id']) || !isset($data['image_data']) || !isset($data['qr_url'])) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

$event_id = intval($data['event_id']);
$imageData = $data['image_data'];
$qr_url = $data['qr_url'];

// Save QR image
$base64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
$decoded = base64_decode($base64);
$savePath = "../qr_images/event_{$event_id}.png";
file_put_contents($savePath, $decoded);

// Save QR info in QRCode table
$stmt = $conn->prepare("INSERT INTO QRCode (code_url) VALUES (?)");
$stmt->bind_param("s", $qr_url);
$stmt->execute();
$qrcode_id = $stmt->insert_id;
$stmt->close();

// Update event with qrcode_id
$stmt2 = $conn->prepare("UPDATE event SET qrcode_id = ? WHERE event_id = ?");
$stmt2->bind_param("ii", $qrcode_id, $event_id);
$stmt2->execute();
$stmt2->close();

echo "Saved";