<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Footer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Footer -->
<footer style="background-color:black; color: white; padding: 20px 0; margin-top: 50px;">
  <div style="width: 90%; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px;">
    
    <!-- Connect With Us -->
    <div style="flex: 1; min-width: 250px;">
      <h3>Connect With Us</h3>
      <p>
        <a href="#" style="color: white; margin-right: 10px;"><i class="fab fa-facebook-f"></i></a>
        <a href="#" style="color: white; margin-right: 10px;"><i class="fab fa-twitter"></i></a>
        <a href="#" style="color: white; margin-right: 10px;"><i class="fab fa-instagram"></i></a>
        <a href="#" style="color: white;"><i class="fab fa-linkedin-in"></i></a>
      </p>
    </div>
<!-- Get in Touch -->
    <div style="flex: 1; min-width: 250px;">
      <h3>Get in Touch</h3>
      <form action="#" method="POST">
        <textarea name="message" rows="3" placeholder="Write your message..." 
                  style="width: 100%; padding: 10px; resize: none;" required></textarea><br><br>
        <input type="submit" value="Send" 
               style="padding: 8px 16px; background-color: green; color: white; border: none; cursor: pointer;">
      </form>
    </div>
  </div>

  <p style="text-align: center; margin-top: 20px;">&copy; <?php echo date("Y"); ?> MyPetakom System - FK UMPSA</p>
</footer>

</body>
</html>
