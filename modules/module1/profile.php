<?php
session_start();
include '../../sql/db.php';
include '../../header.php';
include '../../dashboard/sidebar_admin.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Access denied.";
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE user SET name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
        // Update session values if changed
        $_SESSION['name'] = $name;
    } else {
        $message = "Error updating profile.";
    }
    $stmt->close();
}

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            margin-top: 80px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .edit-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background-color: #0069d9;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            color: green;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="edit-container">
            <h2><i class="bi bi-person-lines-fill"></i> Edit My Profile</h2>

            <?php if ($message): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user['name']); ?>">

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['email']); ?>">

                <button type="submit"><i class="bi bi-save"></i> Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
