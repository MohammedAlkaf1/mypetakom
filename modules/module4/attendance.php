<?php
session_start();

// Prevent browser cache for back button issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check login session (optional for page access)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../sql/db.php';

$user_id = $_SESSION['user_id'];

$page_title = "MyPetakom - Attendance";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management',
    'apply_membership.php' => 'Apply Membership'
];
$current_module = 'attendance.php';

// Fetch events and their active attendance info
$query = "
    SELECT e.event_id, e.title, e.event_start_date, e.location, e.geolocation,
           a.attendance_id, a.attendance_status
    FROM Event e
    LEFT JOIN Attendance a ON e.event_id = a.event_id AND a.attendance_status = 'Active'
    ORDER BY e.event_start_date DESC
";
$events = $conn->query($query);

$rows = [];
while ($row = $events->fetch_assoc()) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="stylesheet" href="/mypetakom/shared/css/shared-layout.css" />
<link rel="stylesheet" href="/mypetakom/shared/css/components.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="../../shared/js/prevent-back-button.js"></script>

<!-- QRCode.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<!-- jsQR for decoding -->
<script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>

<style>
    .qrcode-container {
        max-width: 100px;
        margin: auto;
    }
    #qr-drop-zone {
        border: 2px dashed #007bff;
        padding: 20px;
        width: 300px;
        margin: 20px auto;
        text-align: center;
        color: #007bff;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
    }
    #qr-drop-zone:hover {
        background-color: #e6f0ff;
    }
    #attendance-feedback {
        text-align: center;
        margin-top: 10px;
        font-weight: bold;
    }
    /* Modal overlay */
    #loginModal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0; top: 0;
        width: 100%; height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
</style>
</head>
<body data-login-url="../../login.php">
<?php include_once '../../shared/components/header.php'; ?>
<div class="container">
    <?php include_once '../../shared/components/sidebar.php'; ?>

    <main class="main-content">
        <h1>Attendance</h1>
        <h2>Your Events</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)) : ?>
                        <tr>
                            <td colspan="5" class="text-center">No events found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['event_start_date']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['attendance_status'] ?? 'Inactive') ?></td>
                                <td>
                                    <?php if (!empty($row['attendance_status']) && strtolower($row['attendance_status']) === 'active'): ?>
                                        <div id="qrcode-<?= $row['event_id'] ?>" class="qrcode-container"></div>
                                    <?php else: ?>
                                        No active attendance
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h3>Scan Attendance</h3>
        <div id="qr-drop-zone" tabindex="0" aria-label="QR code drop zone">
            Drag and drop QR code image here or click to select
        </div>
        <div id="attendance-feedback"></div>
    </main>
</div>

<!-- Login Modal -->
<div id="loginModal" class="modal" tabindex="-1">
  <div class="modal-dialog" style="max-width: 400px; margin: 10% auto;">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title">Verify Your Identity</h5>
        <button type="button" class="btn-close" onclick="closeLoginModal()" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="loginForm">
          <div class="mb-3">
            <label for="loginEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="loginEmail" required />
          </div>
          <div class="mb-3">
            <label for="loginPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="loginPassword" required />
          </div>
          <div id="loginError" style="color:red;"></div>
          <button type="submit" class="btn btn-primary">Verify</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const rows = <?= json_encode($rows) ?>;

    // Generate QR codes for active events
    rows.forEach(event => {
        if (event.attendance_status && event.attendance_status.toLowerCase() === 'active') {
            const qrUrl = `http://192.168.8.129/mypetakom/modules/module3/checkin.php?event_id=${event.event_id}`;
            new QRCode(document.getElementById('qrcode-' + event.event_id), {
                text: qrUrl,
                width: 100,
                height: 100
            });
        }
    });
});

function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) ** 2 +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

const dropZone = document.getElementById('qr-drop-zone');
const feedback = document.getElementById('attendance-feedback');

let pendingCheckin = null;

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
        const imgSrc = e.dataTransfer.getData('text/uri-list');
        if (imgSrc) {
            fetchImage(imgSrc);
        } else {
            feedback.textContent = "Drop a valid QR code image!";
        }
    }
});

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

document.querySelectorAll('.qrcode-container').forEach(div => {
    div.setAttribute('draggable', 'true');
    div.addEventListener('dragstart', e => {
        const qrImg = div.querySelector('img');
        if (qrImg) {
            e.dataTransfer.setData('text/uri-list', qrImg.src);
        }
    });
});

function handleFile(file) {
    const reader = new FileReader();
    reader.onload = function(event) {
        decodeImage(event.target.result);
    };
    reader.readAsDataURL(file);
}

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

function openLoginModal() {
    document.getElementById('loginError').textContent = '';
    document.getElementById('loginForm').reset();
    document.getElementById('loginModal').style.display = 'block';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

function processQRCode(qrData) {
    try {
        const url = new URL(qrData);
        const eventId = url.searchParams.get('event_id');
        if (!eventId) throw "No event_id";

        const event = <?= json_encode($rows) ?>.find(e => e.event_id == eventId);
        if (!event) {
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

            let eventLat = null, eventLon = null;
            if (event.geolocation) {
                const parts = event.geolocation.split(',');
                if (parts.length === 2) {
                    eventLat = parseFloat(parts[0].trim());
                    eventLon = parseFloat(parts[1].trim());
                }
            }
            if (eventLat === null || eventLon === null) {
                feedback.textContent = "Event geolocation data invalid.";
                return;
            }

            const dist = getDistance(userLat, userLon, eventLat, eventLon);
            const allowedDistance = 100;

            if (dist <= allowedDistance) {
                feedback.textContent = `You are within ${Math.round(dist)} meters. Please verify your identity.`;
                pendingCheckin = {
                    eventId: eventId,
                    userLat: userLat,
                    userLon: userLon
                };
                openLoginModal();
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

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    fetch('../../modules/module3/verify_login.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetch(`../../modules/module3/checkin.php?event_id=${pendingCheckin.eventId}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `latitude=${encodeURIComponent(pendingCheckin.userLat)}&longitude=${encodeURIComponent(pendingCheckin.userLon)}&student_id=${encodeURIComponent(data.student_id)}`,
                credentials: 'include'
            })
            .then(resp => resp.text())
            .then(text => {
                feedback.innerHTML = text;
                closeLoginModal();
                pendingCheckin = null;
            })
            .catch(() => {
                feedback.textContent = "Error recording attendance.";
                closeLoginModal();
            });
        } else {
            document.getElementById('loginError').textContent = "Invalid email or password.";
        }
    })
    .catch(() => {
        document.getElementById('loginError').textContent = "Login request failed.";
    });
});
</script>
</body>
</html>
