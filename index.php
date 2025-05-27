<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome To The Petakom System</title>
    <link rel="stylesheet" href="../MyPetakonUpdatedSystem/shared_assests/CSS/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../MyPetakonUpdatedSystem/shared_assests/JS/main.js"></script>

</head>
<body>
    <div><?php include('../mypetakom/reuseablePhpFiles/header.php'); ?> </div>
    
 <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>

  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="../mypetakom/shared/img/images3.png" 
     class="d-block w-100" 
     style="height: 400px; object-fit: contain;" 
     alt="First slide">
    </div>
         <div class="carousel-item">
           <img src="../mypetakom/shared/img/images2.jpg" 
     class="d-block w-100" 
     style="height: 400px; object-fit: cover;" 
     alt="First slide">
 
    </div>
    <div class="carousel-item">
           <img src="../mypetakom/shared/img/images1.png" 
     class="d-block w-100" 
     style="height: 400px; object-fit: cover" 
     alt="First slide">
    </div>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(0deg);"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(0deg);"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
<!-- About Petakom System -->
<div class="container mt-5">
  <div class="card">
    <div class="card-body">
      <h4 class="card-title text-center mb-3">About Petakom System</h4>
      <p class="card-text text-center" style="text-align:justify">
        MyPetakom is a web-based system developed for the Faculty of Computing, UMPSA.
        It helps manage Petakom membership, events, attendance, and merit tracking.
        The system supports multiple user roles: students, Event Advisors, and administrators.
        Students can register for events and claim merits online.
        Event Advisors can create events and manage attendance with QR codes.
        Coordinators can review data, approve merits, and view reports.
        MyPetakom improves transparency and reduces manual paperwork.
        The system also provides real-time dashboards and statistics.
        It is designed to be user-friendly, secure, and mobile responsive.
        This platform supports better co-curricular management for all FK students.
      </p>
    </div>
  </div>
</div>
  <div><?php include('../mypetakom/reuseablePhpFiles/footer.php'); ?> </div>
</body>
</html>