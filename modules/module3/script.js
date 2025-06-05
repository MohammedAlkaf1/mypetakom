function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius of Earth in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

const dropZone = document.getElementById('qr-drop-zone');
const feedback = document.getElementById('attendance-feedback');

// Data from PHP about events
const eventsData = <?= json_encode($events_for_js) ?>;

// Drag and drop styling
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.style.backgroundColor = '#e6f0ff';
});
dropZone.addEventListener('dragleave', e => {
    e.preventDefault();
    dropZone.style.backgroundColor = '';
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.backgroundColor = '';
    if (e.dataTransfer.files.length) {
        handleFile(e.dataTransfer.files[0]);
    } else {
        // handle dragged image from page
        const imgSrc = e.dataTransfer.getData('text/uri-list');
        if (imgSrc) {
            fetchImage(imgSrc);
        } else {
            feedback.textContent = "Drop a valid QR code image!";
        }
    }
});

// Click to select file
dropZone.addEventListener('click', () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = e => {
        if (e.target.files.length) {
            handleFile(e.target.files[0]);
        }
    };
    input.click();
});

// Handle dragging QR images from the table
document.querySelectorAll('.qrcode-img').forEach(img => {
    img.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/uri-list', img.src);
    });
});

// Read and decode image file
function handleFile(file) {
    const reader = new FileReader();
    reader.onload = function(event) {
        decodeImage(event.target.result);
    };
    reader.readAsDataURL(file);
}

// Fetch image from URL and decode
function fetchImage(url) {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, canvas.width, canvas.height);
        if (code) {
            processQRCode(code.data);
        } else {
            feedback.textContent = "QR code not detected in image.";
        }
    };
    img.onerror = () => feedback.textContent = "Failed to load image.";
    img.src = url;
}

// Decode QR code from base64 image data
function decodeImage(dataUrl) {
    const img = new Image();
    img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, canvas.width, canvas.height);
        if (code) {
            processQRCode(code.data);
        } else {
            feedback.textContent = "QR code not detected in image.";
        }
    };
    img.src = dataUrl;
}

function processQRCode(qrData) {
    try {
        const url = new URL(qrData);
        const eventId = url.searchParams.get('event_id');
        if (!eventId) throw "No event_id";

        if (!eventsData[eventId]) {
            feedback.textContent = "Event not found or no active attendance.";
            return;
        }

        if (!navigator.geolocation) {
            feedback.textContent = "Geolocation not supported.";
            return;
        }

        feedback.textContent = "Checking your location...";

        navigator.geolocation.getCurrentPosition(position => {
            const userLat = position.coords.latitude;
            const userLon = position.coords.longitude;

            const eventLat = parseFloat(eventsData[eventId].lat);
            const eventLon = parseFloat(eventsData[eventId].lon);

            const dist = getDistance(userLat, userLon, eventLat, eventLon);
            const allowedDistance = 100; // meters

            if (dist <= allowedDistance) {
                feedback.textContent = `You are within ${Math.round(dist)} meters. Recording attendance...`;

                // Send POST request to checkin.php
                fetch(qrData, {
                    method: 'POST',
                    credentials: 'include'
                })
                .then(resp => resp.text())
                .then(text => {
                    feedback.innerHTML = text;
                })
                .catch(() => {
                    feedback.textContent = "Error recording attendance.";
                });

            } else {
                feedback.textContent = `Too far from event location (${Math.round(dist)} meters). Attendance denied.`;
            }
        }, error => {
            feedback.textContent = "Error getting your location: " + error.message;
        });
    } catch {
        feedback.textContent = "Invalid QR code data.";
    }
}