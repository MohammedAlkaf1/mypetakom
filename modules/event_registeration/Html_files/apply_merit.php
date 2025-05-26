<?php
include "connection.php";

$eventId = $_GET['event_id'] ?? null;
$event = null;

if ($eventId) {
    $stmt = $conn->prepare("SELECT * FROM event WHERE event_id = ?");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventId = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $date = $_POST['start_date'];

    $stmt = $conn->prepare("UPDATE event SET title = ?, description = ?, location = ?, start_date = ? WHERE event_id = ?");
    $stmt->bind_param("ssssi", $title, $description, $location, $date, $eventId);

    if ($stmt->execute()) {
        header("Location: event_advisor.php?msg=updated");
        exit();
    } else {
        $error = "Failed to update event.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Event</title>
    <link rel="stylesheet" href="../Styles/header.css">
    <link rel="stylesheet" href="../Styles/sidebar.css">
    <link rel="stylesheet" href="../Styles/merit_Application.css">
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="event-header">
            <h2>Update Event</h2>
        </div>

        <div class="form-wrapper">
            <?php if ($event): ?>
                <form method="POST">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId ?? '') ?>">

                    
                        <label>Event Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($event['title'] ?? '') ?>" required>
                
                        <label>Description</label>
                        <textarea name="description" rows="5" cols="150" style="max-width: 100%;" required><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
               

  
                        <label>Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($event['location'] ?? '') ?>" required>


                    
                        <label>Start Date</label>
                        <input class="date" type="date" name="start_date" value="<?= htmlspecialchars($event['start_date'] ?? '') ?>" required>
                    

                    <button class="submit-btn" type="submit">Update</button>
                </form>


            <?php else: ?>
                <p class="error">Event not found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>


