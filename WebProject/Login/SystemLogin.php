<?php
session_start();
include('../Database/Database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user found
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password == $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'Student':
                    header("Location: ../Student/StudentDashboard.php");
                    break;
                case 'Coordinator':
                    header("Location: ../Coordinator/CoordinatorDashboard.php");
                    break;
                case 'Event Advisor':
                    header("Location: ../EventAdvisor/EventAdvisorDashboard.php");
                    break;
                default:
                    echo "Role not recognized.";
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Please submit the form correctly.";
}
?>
