<?php
header('Content-Type: application/json');
require_once '../../sql/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Dummy test: accept any email/password for now
if ($email && $password) {
    // Just return success for testing
    echo json_encode(['success' => true, 'student_id' => 1]);
} else {
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
}
