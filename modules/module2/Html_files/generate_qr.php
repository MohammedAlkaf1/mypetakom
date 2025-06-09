<?php
session_start();
if (!isset($_GET['event_id'])) {
    die("Missing event_id.");
}
$event_id = intval($_GET['event_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generating QR Code...</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
<canvas id="qr-canvas" style="display:none;"></canvas>

<script>
const eventId = <?= json_encode($event_id) ?>;
const qrUrl = `http://localhost/mypetakom/modules/module2/event_info.php?event_id=${eventId}`;

// Generate QR on hidden canvas
const canvas = document.getElementById('qr-canvas');
const qr = new QRCode(canvas, {
    text: qrUrl,
    width: 200,
    height: 200
});

setTimeout(() => {
    const imgData = canvas.querySelector('img')?.src;

    if (!imgData) {
        alert("Failed to generate QR");
        return;
    }

    // Send to PHP to save
    fetch('save_qr.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ event_id: eventId, image_data: imgData })
    })
    .then(res => res.text())
    .then(() => window.location.href = "event_advisor.php?msg=qr_success");
}, 1000);
</script>
</body>
</html>
