<?php

session_start();
require_once '../../../sql/db.php';

// Check if event_id is provided in the URL, if not, show error and stop
if (!isset($_GET['event_id'])) {
    echo "<h2>No event ID provided.</h2>";
    exit();
}

$event_id = intval($_GET['event_id']);

// Fetch event details from the database
$event_stmt = $conn->prepare("SELECT * FROM event WHERE event_id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    echo "<h2>Event not found.</h2>";
    exit();
}

// Fetch committee members for this event
$committee_stmt = $conn->prepare("SELECT u.name, c.cr_desc AS role_name FROM eventcommittee ec JOIN user u ON ec.user_id = u.user_id JOIN committee_role c ON ec.cr_id = c.cr_id WHERE ec.event_id = ?");
$committee_stmt->bind_param("i", $event_id);
$committee_stmt->execute();
$committee_result = $committee_stmt->get_result();
$committees = $committee_result->fetch_all(MYSQLI_ASSOC);

// Fetch merit application status for this event
$merit_stmt = $conn->prepare("SELECT status FROM merit_application WHERE event_id = ?");
$merit_stmt->bind_param("i", $event_id);
$merit_stmt->execute();
$merit_result = $merit_stmt->get_result();
$merit_status = $merit_result->fetch_assoc()['status'] ?? 'Not Applied';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyPetakom - Event Info</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 4 CSS for layout and styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome for icons (used in the download button) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- jsPDF & html2canvas for generating PDF from HTML content -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- Bootstrap card to display event information -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><?= htmlspecialchars($event['title']) ?></h4>
        </div>
        <div class="card-body" id="eventContent">
            <p><strong>Date:</strong> <?= htmlspecialchars($event['event_start_date']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($event['event_status']) ?></p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($event['description'])) ?></p>
            <p><strong>Geo-location:</strong> <?= htmlspecialchars($event['geolocation']) ?></p>

            <hr>
            <h5>Committee Members</h5>
            <?php if (!empty($committees)): ?>
                <ul>
                    <?php foreach ($committees as $c): ?>
                        <li><strong><?= htmlspecialchars($c['role_name']) ?>:</strong> <?= htmlspecialchars($c['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No committee members assigned.</p>
            <?php endif; ?>

            <hr>
            <h5>Merit Application Status</h5>
            <p><?= htmlspecialchars($merit_status) ?></p>
        </div>

        <div class="card-footer text-right">
            <!-- Button to download the event info as PDF -->
            <button id="downloadPDF" class="btn btn-outline-primary">
                <i class="fa fa-download"></i> Download Report as PDF
            </button>
        </div>
    </div>
</div>

<script>
    // This script lets the user download the event info as a PDF.
    // It uses html2canvas to capture the event content as an image,
    // then jsPDF to put that image into a PDF and trigger the download.
    document.getElementById('downloadPDF').addEventListener('click', async function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Set the title in the PDF
        doc.setFontSize(16);
        doc.text("Event Information Report", 10, 10);

        // Get the content block and convert it to an image
        const content = document.getElementById('eventContent');
        const canvas = await html2canvas(content);
        const imgData = canvas.toDataURL('image/png');

        doc.addImage(imgData, 'PNG', 10, 20, 190, 0);
        doc.save("event_info_report.pdf");
    });
</script>

<!-- Bootstrap 4 JS + jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>