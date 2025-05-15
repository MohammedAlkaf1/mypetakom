<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html"); // Redirect if not logged in
    exit();
}
echo "Welcome, " . $_SESSION['username'];
?>
