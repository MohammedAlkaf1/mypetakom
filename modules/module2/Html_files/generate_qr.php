<?php
session_start();

// Check if event ID is passed
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
        // ✅ Use your computer's IP here (replace this with your actual local IP)
        const eventId = <?= json_encode($event_id) ?>;
        const qrUrl = `http://10.65.87.199/mypetakom/modules/module2/Html_files/event_info.php?event_id=${eventId}`;

        // Generate QR inside hidden canvas
        const canvas = document.getElementById('qr-canvas');
        new QRCode(canvas, {
            text: qrUrl,
            width: 200,
            height: 200
        });

        setTimeout(() => {
            const img = canvas.querySelector('img');
            if (!img || !img.src) {
                alert("Failed to generate QR code.");
                return;
            }

            // Send to save_qr.php
            fetch('save_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        event_id: eventId,
                        image_data: img.src
                    })
                })
                .then(res => res.text())
                .then(result => {
                    if (result === "Saved") {
                        window.location.href = "event_advisor.php?msg=qr_success";
                    } else {
                        alert("Error saving QR: " + result);
                    }
                })
                .catch(err => alert("Fetch error: " + err));
        }, 1000);
    </script>
</body>

</html>