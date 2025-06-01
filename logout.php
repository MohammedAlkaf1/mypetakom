<?php
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Set logout flag in sessionStorage
        sessionStorage.setItem('user_logged_out', 'true');
        
        // Redirect to login after setting the flag
        window.location.replace('index.php'); 
    </script>
</body>
</html>