<?php

session_start();

// Check if event ID is passed in the URL, if not, stop the script
if (!isset($_GET['event_id'])) {
    die("Missing event_id.");
}
$event_id = intval($_GET['event_id']);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Generating QR Code...</title>
    <!-- This script includes the QRCode.js library, which is used to generate QR codes in the browser -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>
    <!-- This canvas is hidden and will be used to generate the QR code image -->
    <canvas id="qr-canvas" style="display:none;"></canvas>

    <script>
        // Use your computer's IP here (replace this with your actual local IP if needed)
        const eventId = <?= json_encode($event_id) ?>;
        // This is the URL that will be encoded in the QR code
        const qrUrl = `http://10.65.87.199/mypetakom/modules/module2/Html_files/event_info.php?event_id=${eventId}`;

        // Generate the QR code inside the hidden canvas
        const canvas = document.getElementById('qr-canvas');
        new QRCode(canvas, {
            text: qrUrl,
            width: 200,
            height: 200
        });

        // Wait 1 second to make sure the QR code is generated, then process it
        setTimeout(() => {
            // Get the generated QR code image from the canvas
            const img = canvas.querySelector('img');
            if (!img || !img.src) {
                alert("Failed to generate QR code.");
                return;
            }

            // Send the QR code image data to save_qr.php using fetch (AJAX)
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
                    // If saved successfully, redirect to event advisor page with success message
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