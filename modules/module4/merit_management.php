<?php
session_start();

// Add these lines to prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Include database connection
require_once '../../sql/db.php';

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $pdo->prepare("SELECT u.*, s.major, s.student_matric_id FROM user u 
                      LEFT JOIN student s ON u.user_id = s.user_id 
                      WHERE u.user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Set page variables for shared components
$page_title = "MyPetakom - Merit Management";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
    'merit_management.php' => 'Merit Management'
];
$current_module = 'merit_management.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ADD THESE: Enhanced meta tags for cache prevention -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <title>MyPetakom - Merit Management</title>

    <script src="../../shared/js/prevent-back-button.js"></script>

</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>
    
    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

        <!-- Main Content -->
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
                    // Get awarded merits for the user - Fixed table names and column names
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
                    ?>
                        <h3>Merit History</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Event Level</th>
                                    <th>Role</th>
                                    <th>Points Awarded</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_points = 0;
                                foreach ($merits as $merit): 
                                    $total_points += $merit['points_awarded'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($merit['event_title']) ?></td>
                                        <td><?= htmlspecialchars($merit['event_level']) ?></td>
                                        <td><?= htmlspecialchars($merit['role']) ?></td>
                                        <td><?= $merit['points_awarded'] ?></td>
                                        <td><?= date('M d, Y', strtotime($merit['event_start_date'])) ?></td>
                                        <td><?= htmlspecialchars($merit['location']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin-top: 20px; padding: 15px; background-color: #e7f3ff; border-radius: 5px;">
                            <strong>Total Merit Points: <?= $total_points ?></strong>
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
                    // Check merit claims - Fixed to use actual merit_claims table
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
                                    <th>Updated Date</th>
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
                                        <td><?= $claim['updated_at'] ? date('M d, Y H:i', strtotime($claim['updated_at'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            No merit claim applications found.
                        </div>
                    <?php endif; ?>

                    <h3 style="margin-top: 30px;">Membership Status</h3>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT m.*, u.name as approved_by_name
                        FROM membership m
                        LEFT JOIN user u ON m.approved_by = u.user_id
                        WHERE m.user_id = ?
                    ");
                    $stmt->execute([$user_id]);
                    $membership = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <?php if ($membership): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Application Type</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Petakom Membership</td>
                                    <td>
                                        <span class="status-badge status-<?= $membership['status'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $membership['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $membership['approved_by_name'] ? htmlspecialchars($membership['approved_by_name']) : '-' ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            No membership application found. Please apply for membership first.
                        </div>
                    <?php endif; ?>

                    <h3 style="margin-top: 30px;">Your Event Participation Status</h3>
                    <?php
                    // Show events where user participated - Fixed query
                    $stmt = $pdo->prepare("
                        SELECT DISTINCT e.title, e.event_start_date, e.location, 
                               cr.cr_desc, ec.committee_id,
                               ats.status as attendance_status, a.check_in_time
                        FROM event e
                        LEFT JOIN eventcommittee ec ON e.event_id = ec.event_id AND ec.user_id = ?
                        LEFT JOIN committee_role cr ON ec.cr_id = cr.cr_id
                        LEFT JOIN attendance a ON e.event_id = a.event_id
                        LEFT JOIN attendance_slot ats ON a.attendance_id = ats.attendance_id AND ats.user_id = ?
                        WHERE ec.user_id = ? OR ats.user_id = ?
                        ORDER BY e.event_start_date DESC
                    ");
                    $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
                    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($participations) > 0):
                    ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Role</th>
                                    <th>Attendance</th>
                                    <th>Check-in Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participations as $participation): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($participation['title']) ?></td>
                                        <td><?= date('M d, Y', strtotime($participation['event_start_date'])) ?></td>
                                        <td><?= $participation['cr_desc'] ? htmlspecialchars($participation['cr_desc']) : 'Participant' ?></td>
                                        <td>
                                            <?php if ($participation['attendance_status']): ?>
                                                <span class="status-badge status-<?= $participation['attendance_status'] == 'present' ? 'approved' : 'rejected' ?>">
                                                    <?= ucfirst($participation['attendance_status']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-pending">Not Recorded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $participation['check_in_time'] ? date('H:i', strtotime($participation['check_in_time'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            No event participation records found.
                        </div>
                    <?php endif; ?>
                </div>

                <a href="?action=dashboard" class="back-btn">Back to Merit Dashboard</a>

            <?php elseif ($action == 'apply_missing'): ?>
                <?php
                // Handle form submission - Fixed to insert into merit_claims table
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $event_id = $_POST['event_id'];
                    $role_claimed = $_POST['role_claimed'];
                    $justification = $_POST['justification'];
                    
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO merit_claims (user_id, event_id, role_claimed, justification, status, created_at)
                            VALUES (?, ?, ?, ?, 'pending', NOW())
                        ");
                        $stmt->execute([$user_id, $event_id, $role_claimed, $justification]);
                        $success_message = "Your merit claim application has been submitted successfully. It will be reviewed by the Event Advisor.";
                    } catch (PDOException $e) {
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
                    <form method="POST">
                        <div class="form-group">
                            <label for="event_id">Select Event:</label>
                            <select name="event_id" id="event_id" required>
                                <option value="">Choose an event...</option>
                                <?php
                                // Get events where user attended but doesn't have merit points yet - Fixed query
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

            <?php endif; ?>
        </div>
    </div>
</body>
</html>