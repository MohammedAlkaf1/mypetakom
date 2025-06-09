<?php
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['event_id']) || !isset($data['image_data'])) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

$event_id = intval($data['event_id']);
$imageData = $data['image_data'];

// Strip base64 header
$base64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
$decoded = base64_decode($base64);

// Save to qr_images/
$savePath = "../qr_images/event_{$event_id}.png";
file_put_contents($savePath, $decoded);

echo "Saved";
