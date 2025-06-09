<?php
session_start();
require_once 'sql/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $selectedRole = $_POST['role'];

    if (empty($email) || empty($password) || empty($selectedRole)) {
        $_SESSION['login_error'] = "Please fill in all required fields.";
        header("Location: index.php");
        exit();
    }

    // Get user by email
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (
            $user['role'] === $selectedRole &&
            $password === $user['password']
        ) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: dashboard/admin_dashboard.php");
                    exit();

                case 'staff':
                    header("Location: dashboard/advisor_dashboard.php");
                    exit();

                case 'student':
    // Check if student has a membership record
   $membership_sql = "SELECT status FROM membership WHERE user_id = ?";
   $membership_stmt = $conn->prepare($membership_sql);
   $membership_stmt->bind_param("i", $user['user_id']);

    $membership_stmt->execute();
    $membership_result = $membership_stmt->get_result();

    if ($membership_result->num_rows > 0) {
        $membership = $membership_result->fetch_assoc();

            if (strcasecmp($membership['status'], 'approved') === 0) {
        // ✅ Redirect to student dashboard
        header("Location: modules/module4/student_dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Your membership is pending approval.";
        header("Location: modules/module4/apply_membership.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = "No membership found. Please apply.";
    header("Location: modules/module4/apply_membership.php");
    exit();
}

                default:
                    $_SESSION['login_error'] = "Unauthorized role.";
                    header("Location: index.php");
                    exit();
            }

        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "User not found.";
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['login_error'] = "Invalid request method.";
    header("Location: index.php");
    exit();
}
?>
