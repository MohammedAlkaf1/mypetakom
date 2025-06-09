<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../sql/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT u.*, s.major, s.student_matric_id FROM user u 
                      LEFT JOIN student s ON u.user_id = s.user_id 
                      WHERE u.user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Helper function to determine semester
function getSemester($date) {
    $month = date('n', strtotime($date));
    if ($month >= 9 || $month <= 1) {
        return 'Semester 1';
    } else {
        return 'Semester 2';
    }
}

// Helper function to get academic year
function getAcademicYear($date) {
    $year = date('Y', strtotime($date));
    $month = date('n', strtotime($date));
    if ($month >= 9) {
        return $year . '/' . ($year + 1);
    } else {
        return ($year - 1) . '/' . $year;
    }
}

// Handle file upload
function handleFileUpload($file) {
    $upload_dir = '../../uploads/merit_claims/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Only PDF files are allowed');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File size must be less than 5MB');
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    } else {
        throw new Exception('Failed to upload file'); 
    }
}

$page_title = "MyPetakom - Merit Management";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management',
    'apply_membership.php' => 'Apply Membership'
];
$current_module = 'merit_management.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
      <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <title>MyPetakom - Merit Management</title>

    <script src="../../shared/js/prevent-back-button.js"></script>
    
    <!-- QRCode.js library from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>
    
    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

        <div class="main-content">
            <?php if ($action == 'dashboard'): ?>
                <div class="content-header">
                    <h2>Merit Management Dashboard</h2>
                    <p>Manage your merit points and applications</p>
                </div>

                <div class="dashboard-buttons">
                    <a href="?action=view_merits" class="dashboard-btn">View Awarded Merits</a>
                    <a href="?action=check_status" class="dashboard-btn">Check Claim Status</a>
                    <a href="?action=apply_missing" class="dashboard-btn">Apply for Missing Merit</a>
                </div>

            <?php elseif ($action == 'view_merits'): ?>
    <div class="content-header">
        <h2>Your Awarded Merits</h2>
        <p>View all merits you have been awarded for events and activities</p>
    </div>

    <div class="table-container">
        <?php
        $stmt = $pdo->prepare("
            SELECT vam.*, ma.event_level, e.title as event_title, e.event_start_date, e.location
            FROM view_awarded_merits vam
            JOIN merit_application ma ON vam.merit_id = ma.merit_id
            JOIN event e ON ma.event_id = e.event_id
            WHERE vam.user_id = ?
            ORDER BY e.event_start_date DESC
        ");
        $stmt->execute([$user_id]);
        $merits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($merits) > 0):
            $total_points = 0;
            $semester_points = ['Semester 1' => 0, 'Semester 2' => 0];
            $current_academic_year = '';
        ?>
            <!-- QR Code Section -->
            <?php            // Calculate current academic year
            $current_year = date('Y');
            $current_month = date('n');
            
            if ($current_month >= 9) {
                $academic_year = $current_year . '/' . ($current_year + 1);
            } else {
                $academic_year = ($current_year - 1) . '/' . $current_year;
            }

            // Calculate total points first (before creating QR code)
            $total_calculated_points = 0;
            foreach ($merits as $merit) {
                $total_calculated_points += $merit['points_awarded'];
                $semester = getSemester($merit['event_start_date']);
                $semester_points[$semester] += $merit['points_awarded'];
            }

            // Create QR code data URL (using same method as attendance system)
            $verification_url = 'http://192.168.0.6/mypetakom/modules/module4/verify_student.php?id=' . $user_id;
                ?>                <div class="merit-qr-section">
                    <div class="qr-container">
                        <div class="qr-image">
                            <div id="qrcode-merit-<?= $user_id ?>" class="qrcode-container"></div>
                        </div>
                        <div class="qr-info">
                            <h4>🎓 Merit QR Code</h4>
                            <p><strong><?= htmlspecialchars($user['name']) ?></strong></p>
                            <p><span style="color:#666;">Student ID:</span> <?= htmlspecialchars($user['student_matric_id']) ?></p>
                            <p><span style="color:#666;">Academic Year:</span> <?= $academic_year ?></p>
                            <p><span style="color:#666;">Total Points:</span> <strong style="color:#007bff;"><?= $total_calculated_points ?> points</strong></p>
                            <small style="color:#666;">📱 Scan to verify merit information</small>
                            <div style="margin-top:15px;">
                                <a href="<?= $verification_url ?>" target="_blank" 
                                   style="background:#007bff; color:white; padding:8px 15px; border-radius:5px; text-decoration:none; font-size:12px;">
                                   🔗 Test Link Directly
                                </a>
                                <button onclick="copyToClipboard('<?= htmlspecialchars($verification_url) ?>')" 
                                        style="background:#28a745; color:white; padding:8px 15px; border:none; border-radius:5px; font-size:12px; margin-left:10px; cursor:pointer;">
                                   📋 Copy URL
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <h3>Merit History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Event Level</th>
                        <th>Role</th>
                        <th>Points Awarded</th>
                        <th>Date</th>
                        <th>Semester</th>
                        <th>Academic Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_points = 0; // Reset total points for display
                    foreach ($merits as $merit): 
                        $total_points += $merit['points_awarded'];
                        $semester = getSemester($merit['event_start_date']);
                        $academic_year_merit = getAcademicYear($merit['event_start_date']);
                        if (empty($current_academic_year)) $current_academic_year = $academic_year_merit;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($merit['event_title']) ?></td>
                            <td><?= htmlspecialchars($merit['event_level']) ?></td>
                            <td><?= htmlspecialchars($merit['role']) ?></td>
                            <td><?= $merit['points_awarded'] ?></td>
                            <td><?= date('M d, Y', strtotime($merit['event_start_date'])) ?></td>
                            <td><?= $semester ?></td>
                            <td><?= $academic_year_merit ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="merit-summary">
                <div class="summary-card">
                    <h4>Academic Year <?= $current_academic_year ?> Summary</h4>
                    <p><strong>Semester 1:</strong> <?= $semester_points['Semester 1'] ?> points</p>
                    <p><strong>Semester 2:</strong> <?= $semester_points['Semester 2'] ?> points</p>
                    <p><strong>Total Points:</strong> <?= $total_points ?> points</p>
                </div>
            </div>
        <?php else: ?>
            <div class="no-data">
                No merit points awarded yet. Participate in events to earn merit points!
            </div>
        <?php endif; ?>
    </div>

    <a href="?action=dashboard" class="back-btn">Back to Merit Dashboard</a>

            <?php elseif ($action == 'check_status'): ?>
                <div class="content-header">
                    <h2>Merit Claim Status</h2>
                    <p>Check the status of your merit claim applications</p>
                </div>

                <div class="table-container">
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT mc.*, e.title as event_title, e.event_start_date
                        FROM merit_claims mc
                        JOIN event e ON mc.event_id = e.event_id
                        WHERE mc.user_id = ?
                        ORDER BY mc.created_at DESC
                    ");
                    $stmt->execute([$user_id]);
                    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <h3>Merit Claim Applications</h3>
                    <?php if (count($claims) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Role Claimed</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Official Letter</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($claims as $claim): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($claim['event_title']) ?></td>
                                        <td><?= htmlspecialchars($claim['role_claimed']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $claim['status'] ?>">
                                                <?= ucfirst($claim['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $claim['created_at'] ? date('M d, Y H:i', strtotime($claim['created_at'])) : '-' ?></td>
                                        <td>
                                            <?php if ($claim['official_letter_path']): ?>
                                                <a href="<?= $claim['official_letter_path'] ?>" target="_blank" class="file-link">View PDF</a>
                                            <?php else: ?>
                                                No file uploaded
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($claim['status'] == 'pending'): ?>
                                                <a href="?action=edit_claim&claim_id=<?= $claim['claim_id'] ?>" class="edit-btn">Edit</a>
                                                <a href="?action=delete_claim&claim_id=<?= $claim['claim_id'] ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this claim?')" 
                                                   class="delete-btn">Delete</a>
                                            <?php else: ?>
                                                <span class="disabled-text">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            No merit claim applications found.
                        </div>
                    <?php endif; ?>
                </div>

                <a href="?action=dashboard" class="back-btn">Back to Merit Dashboard</a>

            <?php elseif ($action == 'apply_missing'): ?>
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    try {
                        $event_id = $_POST['event_id'];
                        $role_claimed = $_POST['role_claimed'];
                        $justification = $_POST['justification'];
                        
                        // Get event date for semester calculation
                        $stmt = $pdo->prepare("SELECT event_start_date FROM event WHERE event_id = ?");
                        $stmt->execute([$event_id]);
                        $event = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $semester = getSemester($event['event_start_date']);
                        $academic_year = getAcademicYear($event['event_start_date']);
                        
                        $official_letter_path = null;
                        if (isset($_FILES['official_letter']) && $_FILES['official_letter']['error'] === UPLOAD_ERR_OK) {
                            $official_letter_path = handleFileUpload($_FILES['official_letter']);
                        }
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO merit_claims (user_id, event_id, role_claimed, justification, status, semester, academic_year, official_letter_path, created_at)
                            VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$user_id, $event_id, $role_claimed, $justification, $semester, $academic_year, $official_letter_path]);
                        $success_message = "Your merit claim application has been submitted successfully. It will be reviewed by the Event Advisor.";
                    } catch (Exception $e) {
                        $error_message = "Error submitting application: " . $e->getMessage();
                    }
                }
                ?>

                <div class="content-header">
                    <h2>Apply for Missing Merit</h2>
                    <p>Claim merit points for events where you participated but haven't received points</p>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="event_id">Select Event:</label>
                            <select name="event_id" id="event_id" required>
                                <option value="">Choose an event...</option>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT DISTINCT e.event_id, e.title, e.event_start_date, e.location
                                    FROM event e
                                    LEFT JOIN attendance a ON e.event_id = a.event_id
                                    LEFT JOIN attendance_slot ats ON a.attendance_id = ats.attendance_id AND ats.user_id = ?
                                    LEFT JOIN eventcommittee ec ON e.event_id = ec.event_id AND ec.user_id = ?
                                    WHERE (ats.status = 'present' OR ec.user_id IS NOT NULL)
                                    AND e.event_id NOT IN (
                                        SELECT DISTINCT ma.event_id 
                                        FROM merit_application ma 
                                        JOIN view_awarded_merits vam ON ma.merit_id = vam.merit_id 
                                        WHERE vam.user_id = ?
                                    )
                                    AND e.event_id NOT IN (
                                        SELECT event_id FROM merit_claims WHERE user_id = ? AND status = 'pending'
                                    )
                                    ORDER BY e.event_start_date DESC
                                ");
                                $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
                                $available_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($available_events as $event):
                                ?>
                                    <option value="<?= $event['event_id'] ?>">
                                        <?= htmlspecialchars($event['title']) ?> - <?= date('M d, Y', strtotime($event['event_start_date'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="role_claimed">Role in Event:</label>
                            <select name="role_claimed" id="role_claimed" required>
                                <option value="">Select your role...</option>
                                <option value="Main Committee">Main Committee</option>
                                <option value="Committee">Committee</option>
                                <option value="Participant">Participant</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="official_letter">Official Participation Letter (PDF only):</label>
                            <input type="file" name="official_letter" id="official_letter" accept=".pdf" required>
                            <small>Upload your official participation letter (PDF format, max 5MB)</small>
                        </div>

                        <div class="form-group">
                            <label for="justification">Justification for Merit Claim:</label>
                            <textarea name="justification" id="justification" 
                                    placeholder="Please provide details about your participation and why you believe you deserve merit points for this event..."
                                    required></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Submit Merit Claim</button>
                    </form>

                    <?php if (empty($available_events)): ?>
                        <div class="no-data" style="margin-top: 20px;">
                            No events available for merit claims. You may have already received merit points for all your attended events or have pending claims.
                        </div>
                    <?php endif; ?>
                </div>

                <a href="?action=dashboard" class="back-btn">Back to Merit Dashboard</a>

            <?php elseif ($action == 'edit_claim'): ?>
                <?php
                $claim_id = $_GET['claim_id'] ?? 0;
                
                // Get claim details
                $stmt = $pdo->prepare("
                    SELECT mc.*, e.title as event_title 
                    FROM merit_claims mc
                    JOIN event e ON mc.event_id = e.event_id
                    WHERE mc.claim_id = ? AND mc.user_id = ? AND mc.status = 'pending'
                ");
                $stmt->execute([$claim_id, $user_id]);
                $claim = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$claim) {
                    header("Location: ?action=check_status");
                    exit();
                }
                
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    try {
                        $role_claimed = $_POST['role_claimed'];
                        $justification = $_POST['justification'];
                        
                        $official_letter_path = $claim['official_letter_path'];
                        if (isset($_FILES['official_letter']) && $_FILES['official_letter']['error'] === UPLOAD_ERR_OK) {
                            // Delete old file if exists
                            if ($official_letter_path && file_exists($official_letter_path)) {
                                unlink($official_letter_path);
                            }
                            $official_letter_path = handleFileUpload($_FILES['official_letter']);
                        }
                        
                        $stmt = $pdo->prepare("
                            UPDATE merit_claims 
                            SET role_claimed = ?, justification = ?, official_letter_path = ?, updated_at = NOW()
                            WHERE claim_id = ? AND user_id = ?
                        ");
                        $stmt->execute([$role_claimed, $justification, $official_letter_path, $claim_id, $user_id]);
                        $success_message = "Your merit claim has been updated successfully.";
                    } catch (Exception $e) {
                        $error_message = "Error updating claim: " . $e->getMessage();
                    }
                }
                ?>

                <div class="content-header">
                    <h2>Edit Merit Claim</h2>
                    <p>Edit your merit claim for: <?= htmlspecialchars($claim['event_title']) ?></p>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Event:</label>
                            <input type="text" value="<?= htmlspecialchars($claim['event_title']) ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="role_claimed">Role in Event:</label>
                            <select name="role_claimed" id="role_claimed" required>
                                <option value="Main Committee" <?= $claim['role_claimed'] == 'Main Committee' ? 'selected' : '' ?>>Main Committee</option>
                                <option value="Committee" <?= $claim['role_claimed'] == 'Committee' ? 'selected' : '' ?>>Committee</option>
                                <option value="Participant" <?= $claim['role_claimed'] == 'Participant' ? 'selected' : '' ?>>Participant</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="official_letter">Official Participation Letter (PDF only):</label>
                            <input type="file" name="official_letter" id="official_letter" accept=".pdf">
                            <small>
                                <?php if ($claim['official_letter_path']): ?>
                                    Current file: <a href="<?= $claim['official_letter_path'] ?>" target="_blank">View Current PDF</a><br>
                                <?php endif; ?>
                                Upload a new file to replace the current one (PDF format, max 5MB)
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="justification">Justification for Merit Claim:</label>
                            <textarea name="justification" id="justification" required><?= htmlspecialchars($claim['justification']) ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Update Merit Claim</button>
                    </form>
                </div>

                <a href="?action=check_status" class="back-btn">Back to Status Check</a>

            <?php elseif ($action == 'delete_claim'): ?>
                <?php
                $claim_id = $_GET['claim_id'] ?? 0;
                
                // Verify claim belongs to user and is pending
                $stmt = $pdo->prepare("
                    SELECT official_letter_path 
                    FROM merit_claims 
                    WHERE claim_id = ? AND user_id = ? AND status = 'pending'
                ");
                $stmt->execute([$claim_id, $user_id]);
                $claim = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($claim) {
                    // Delete file if exists
                    if ($claim['official_letter_path'] && file_exists($claim['official_letter_path'])) {
                        unlink($claim['official_letter_path']);
                    }
                    
                    // Delete claim
                    $stmt = $pdo->prepare("DELETE FROM merit_claims WHERE claim_id = ? AND user_id = ?");
                    $stmt->execute([$claim_id, $user_id]);
                }
                
                header("Location: ?action=check_status");
                exit();
                ?>

            <?php endif; ?>
        </div>
    </div>

    <style>
    .merit-summary {
        margin-top: 20px;
    }
    
    .summary-card {
        background-color: #e7f3ff;
        border-radius: 5px;
        padding: 20px;
        border-left: 4px solid #007bff;
    }
    
    .summary-card h4 {
        margin-top: 0;
        color: #333;
    }
    
    .summary-card p {
        margin: 10px 0;
    }
    
    .file-link {
        color: #007bff;
        text-decoration: none;
        padding: 5px 10px;
        background-color: #f8f9fa;
        border-radius: 3px;
        border: 1px solid #dee2e6;
        display: inline-block;
    }
    
    .file-link:hover {
        background-color: #e9ecef;
    }
    
    .edit-btn, .delete-btn {
        padding: 5px 10px;
        margin: 2px;
        border-radius: 3px;
        text-decoration: none;
        font-size: 12px;
        display: inline-block;
    }
    
    .edit-btn {
        background-color: #28a745;
        color: white;
    }
    
    .delete-btn {
        background-color: #dc3545;
        color: white;
    }
    
    .disabled-text {
        color: #6c757d;
        font-style: italic;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }    .merit-qr-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #dee2e6;
    }

    .qr-container {
        display: flex;
        align-items: center;
        gap: 20px;
        max-width: 400px;
    }

    .qr-image .qrcode-container {
        border: 2px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        background: white;
        display: inline-block;
    }

    .qr-info h4 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .qr-info p {
        margin: 5px 0;
        color: #666;
        font-size: 14px;
    }    @media (max-width: 768px) {
        .qr-container {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>

    <script>
        // Function to copy text to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('URL copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                var textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('URL copied to clipboard!');
            });
        }

        // Generate QR code when page loads
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($action == 'view_merits' && count($merits) > 0): ?>
                var qrCodeDiv = document.getElementById('qrcode-merit-<?= $user_id ?>');
                if (qrCodeDiv) {
                    // Clear any existing content
                    qrCodeDiv.innerHTML = '';
                    
                    // Generate QR code using QRCode.js
                    var qrcode = new QRCode(qrCodeDiv, {
                        text: '<?= $verification_url ?>',
                        width: 128,
                        height: 128,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>