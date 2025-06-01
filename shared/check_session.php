<?php
session_start();
header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");

$response = array(
    'logged_in' => isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])
);

echo json_encode($response);
?>