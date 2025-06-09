<?php

// Get the raw POST data sent as JSON and decode it into an array
$data = json_decode(file_get_contents('php://input'), true);

// Check if both event_id and image_data are provided in the request
if (!isset($data['event_id']) || !isset($data['image_data'])) {
    http_response_code(400); // Set HTTP response code to 400 (Bad Request)
    echo "Invalid input";
    exit;
}

// Get the event ID and image data from the decoded JSON
$event_id = intval($data['event_id']);
$imageData = $data['image_data'];

// Remove the base64 header from the image data string
$base64 = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
// Decode the base64 string to binary image data
$decoded = base64_decode($base64);

// Set the path to save the QR image (filename includes the event ID)
$savePath = "../qr_images/event_{$event_id}.png";
// Save the decoded image data to the file
file_put_contents($savePath, $decoded);

// Respond with "Saved" if everything went well
echo "Saved";