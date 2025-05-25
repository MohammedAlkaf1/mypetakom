<?php
session_start();
require_once '../../sql/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $selectedRole = $_POST['role'];

    if (empty($email) || empty($password) || empty($selectedRole)) {
        $_SESSION['login_error'] = "Please fill in all the required fields.";
        header("Location: ../MyPetakonUpdatedSystem/index.php");
        exit();
    }

    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (
            $user['role'] === $selectedRole &&
            $password === $user['password'] &&
            $email === $user['email']
        ) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: ../../dashboard/cordinator_dashboard.php");
                    exit();
                case 'staff':
                    header("Location: ../../dashboard/advisor_dashboard.php");
                    exit();
                case 'student':
                    header("Location: ../../dashboard/student_dashboard.php");
                    exit();
                default:
                    $_SESSION['login_error'] = "Role unauthorized.";
                    header("Location: ../../index.php");
                    exit();
            }
        } else {
            $_SESSION['login_error'] = "Invalid credentials.";
            header("Location: ../../index.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "User not found.";
        header("Location: ../../index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['login_error'] = "Invalid request.";
    header("Location: /MyPetakonUpdatedSystem/index.php");
    exit();
}
?>
