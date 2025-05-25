<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyPetakom</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary shadow-sm px-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/MyPetakonUpdatedSystem/index.php"><img src="../shared/img/images3.png" alt=""></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="../MyPetakonUpdatedSystem/index.php">Home</a>
        </li>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fas fa-sign-in-alt"></i> Login
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php
session_start();
if (isset($_SESSION['login_error'])) {
  echo '<div class="alert alert-danger text-center m-2">' . $_SESSION['login_error'] . '</div>';
  unset($_SESSION['login_error']);
}
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="../MyPetakonUpdatedSystem/modules/login_membership/login.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="loginModalLabel" style="text-align: center;">Welcome To My Petakom System</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" required>
              <option value="">Select Role</option>
              <option value="admin">admin</option>
              <option value="staff">staff</option>
              <option value="student">student</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Login</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


</body>
</html>
