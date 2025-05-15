<?php
session_start();  // Ensure the session is started

$serverName = "localhost";
$userName = "root"; // default XAMPP MySQL userName
$password = ""; // default XAMPP MySQL password
$dbName = "mypetakom"; // the database we created earlier

// Create connection
$conn = new mysqli($serverName, $userName, $password, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = $_POST['userName'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Protect against SQL injection
    $userName = $conn->real_escape_string($userName);
    $role = $conn->real_escape_string($role);

    // Query to check login credentials
    $query = "SELECT * FROM users WHERE userName = '$userName' AND role = '$role'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch user details
        $storedPassword = $user['password'];

        // Check if password is hashed or not
        if (password_verify($password, $storedPassword)) {
            // Login successful for hashed password
            $_SESSION['id'] = $user['id'];
            $_SESSION['userName'] = $user['userName'];
            $_SESSION['role'] = $user['role'];

            // Redirect to the appropriate page based on the role
            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                header('Location: dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit(); // Ensure script stops here
        } elseif ($password === $storedPassword) {
            // Login successful for plaintext password
            $_SESSION['id'] = $user['id'];
            $_SESSION['userName'] = $user['userName'];
            $_SESSION['role'] = $user['role'];

            // Redirect to the appropriate page based on the role
            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                header('Location: dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit(); // Ensure script stops here
        } else {
            $error = "Invalid userName, password, or role!";
        }
    } else {
        $error = "Invalid userName, password, or role!";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Login | Mantis Bootstrap 5 Admin Template</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta Name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta Name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta Name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta Name="author" content="CodedThemes">

  <!-- [Favicon] icon -->
  <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon"> <!-- [Google Font] Family -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
<!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" >
<!-- [Feather Icons] https://feathericons.com -->
<link rel="stylesheet" href="assets/fonts/feather.css" >
<!-- [Font Awesome Icons] https://fontawesome.com/icons -->
<link rel="stylesheet" href="assets/fonts/fontawesome.css" >
<!-- [Material Icons] https://fonts.google.com/icons -->
<link rel="stylesheet" href="assets/fonts/material.css" >
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="assets/css/style.css" id="main-style-link" >
<link rel="stylesheet" href="assets/css/style-preset.css" >

</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body>
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <div class="auth-main">
    <div class="auth-wrapper v3">
      <div class="auth-form">
        <div class="auth-header">
          <a href="#"><img src="assets/images/logo-dark.svg" alt="img"></a>
        </div>
 <div class="card my-5">
    <div class="card-body">
        <!-- Form Action and Method -->
        <form action="index.php" method="POST">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h3 class="mb-0"><b>Login</b></h3>
                <a href="#" class="link-primary">Don't have an account?</a>
            </div>

            <!-- UserName Field -->
            <div class="form-group mb-3">
                <label class="form-label">UserName</label>
                <input type="text" id="userName" Name="userName" class="form-control" placeholder="UserName" required>
            </div>

            <!-- Password Field -->
            <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <input type="password" id="password" Name="password" class="form-control" placeholder="Password" required>
            </div>

            <!-- Role Selection Dropdown -->
            <div class="form-group mb-3">
                <label for="role" class="form-label">Role</label>
                <select Name="role" id="role" class="form-select" required>
                    <option value="admin" selected>Admin</option>
                    <option value="staff">Staff</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
</div>

        <div class="auth-footer row">
          <!-- <div class=""> -->
            <div class="col my-1">
              <p class="m-0">Copyright © <a href="#">Codedthemes</a></p>
            </div>
            <div class="col-auto my-1">
              <ul class="list-inline footer-link mb-0">
                <li class="list-inline-item"><a href="#">Home</a></li>
                <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                <li class="list-inline-item"><a href="#">Contact us</a></li>
              </ul>
            </div>
          <!-- </div> -->
        </div>
      </div>
    </div>
  </div>
  <!-- [ Main Content ] end -->
  <!-- Required Js -->
  <script src="assets/js/plugins/popper.min.js"></script>
  <script src="assets/js/plugins/simplebar.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/fonts/custom-font.js"></script>
  <script src="assets/js/pcoded.js"></script>
  <script src="assets/js/plugins/feather.min.js"></script>

  
  
  
  
  <script>layout_change('light');</script>
  
  
  
  
  <script>change_box_container('false');</script>
  
  
  
  <script>layout_rtl_change('false');</script>
  
  
  <script>preset_change("preset-1");</script>
  
  
  <script>font_change("Public-Sans");</script>
  
    
 
</body>
<!-- [Body] end -->

</html>