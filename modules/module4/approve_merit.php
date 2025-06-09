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

$page_title = "MyPetakom - Manage events";
$logout_url = "../../logout.php";
$dashboard_url = "../../dashboard/advisor_dashboard.php";
$module_nav_items = [
    '../module2/Html_files/event_advisor.php' => 'Events',
    '../module3/attendance.php' => 'Attendance Activity',
    'approve_merit.php' => 'Approve Merit Claims',
];
$current_module = '';

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
    <title>MyPetakom - Claim Management</title>

    <script src="../../shared/js/prevent-back-button.js"></script>

</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>
    
    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>        <div class="main-content">
            <?php
            // Handle approve/reject actions
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_POST['action']) && isset($_POST['claim_id'])) {
                    $claim_id = $_POST['claim_id'];
                    $action_type = $_POST['action'];
                    
                    try {
                        if ($action_type == 'approve') {
                            // Get claim details for merit calculation
                            $stmt = $pdo->prepare("
                                SELECT mc.*, e.title as event_title, e.event_start_date, ma.event_level
                                FROM merit_claims mc
                                JOIN event e ON mc.event_id = e.event_id
                                LEFT JOIN merit_application ma ON mc.event_id = ma.event_id
                                WHERE mc.claim_id = ? AND mc.status = 'pending'
                            ");
                            $stmt->execute([$claim_id]);
                            $claim = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($claim) {
                                // Calculate points based on role and event level (using default if no merit_application exists)
                                $event_level = $claim['event_level'] ?? 'UMPSA'; // Default to UMPSA if not found
                                $role = $claim['role_claimed'];
                                
                                $points = 0;
                                switch ($event_level) {
                                    case 'International':
                                        $points = ($role == 'Main Committee') ? 100 : (($role == 'Committee') ? 70 : 50);
                                        break;
                                    case 'National':
                                        $points = ($role == 'Main Committee') ? 80 : (($role == 'Committee') ? 50 : 40);
                                        break;
                                    case 'State':
                                        $points = ($role == 'Main Committee') ? 60 : (($role == 'Committee') ? 40 : 30);
                                        break;
                                    case 'District':
                                        $points = ($role == 'Main Committee') ? 40 : (($role == 'Committee') ? 30 : 15);
                                        break;
                                    case 'UMPSA':
                                    default:
                                        $points = ($role == 'Main Committee') ? 30 : (($role == 'Committee') ? 20 : 5);
                                        break;
                                }
                                
                                // Start transaction
                                $pdo->beginTransaction();
                                
                                // Create merit application if it doesn't exist
                                $stmt = $pdo->prepare("SELECT merit_id FROM merit_application WHERE event_id = ?");
                                $stmt->execute([$claim['event_id']]);
                                $merit_app = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if (!$merit_app) {
                                    // Create merit application
                                    $stmt = $pdo->prepare("
                                        INSERT INTO merit_application (event_id, event_level, points_main_committee, points_committee, points_participant, status, applied_by)
                                        VALUES (?, ?, ?, ?, ?, 'Approved', ?)
                                    ");
                                    $main_points = ($event_level == 'International') ? 100 : (($event_level == 'National') ? 80 : (($event_level == 'State') ? 60 : (($event_level == 'District') ? 40 : 30)));
                                    $comm_points = ($event_level == 'International') ? 70 : (($event_level == 'National') ? 50 : (($event_level == 'State') ? 40 : (($event_level == 'District') ? 30 : 20)));
                                    $part_points = ($event_level == 'International') ? 50 : (($event_level == 'National') ? 40 : (($event_level == 'State') ? 30 : (($event_level == 'District') ? 15 : 5)));
                                    
                                    $stmt->execute([$claim['event_id'], $event_level, $main_points, $comm_points, $part_points, $user_id]);
                                    $merit_id = $pdo->lastInsertId();
                                } else {
                                    $merit_id = $merit_app['merit_id'];
                                }
                                
                                // Add to view_awarded_merits
                                $stmt = $pdo->prepare("
                                    INSERT INTO view_awarded_merits (user_id, merit_id, role, points_awarded)
                                    VALUES (?, ?, ?, ?)
                                ");
                                $stmt->execute([$claim['user_id'], $merit_id, $role, $points]);
                                
                                // Update claim status to approved
                                $stmt = $pdo->prepare("
                                    UPDATE merit_claims SET status = 'approved', updated_at = NOW()
                                    WHERE claim_id = ?
                                ");
                                $stmt->execute([$claim_id]);
                                
                                $pdo->commit();
                                $success_message = "Merit claim approved successfully and points awarded to student.";
                            } else {
                                $error_message = "Claim not found or already processed.";
                            }
                            
                        } elseif ($action_type == 'reject') {
                            // Update claim status to rejected
                            $stmt = $pdo->prepare("
                                UPDATE merit_claims SET status = 'rejected', updated_at = NOW()
                                WHERE claim_id = ? AND status = 'pending'
                            ");
                            $stmt->execute([$claim_id]);
                            
                            if ($stmt->rowCount() > 0) {
                                $success_message = "Merit claim has been rejected.";
                            } else {
                                $error_message = "Claim not found or already processed.";
                            }
                        }
                        
                    } catch (Exception $e) {
                        if (isset($pdo) && $pdo->inTransaction()) {
                            $pdo->rollback();
                        }
                        $error_message = "Error processing claim: " . $e->getMessage();
                    }
                }
            }
            
            // Fetch all pending merit claims
            $stmt = $pdo->prepare("
                SELECT mc.*, e.title as event_title, e.event_start_date, e.location, e.event_status,
                       u.name as student_name, s.student_matric_id, s.major,
                       ma.event_level
                FROM merit_claims mc
                JOIN event e ON mc.event_id = e.event_id
                JOIN user u ON mc.user_id = u.user_id
                LEFT JOIN student s ON u.user_id = s.user_id
                LEFT JOIN merit_application ma ON mc.event_id = ma.event_id
                WHERE mc.status = 'pending'
                ORDER BY mc.created_at ASC
            ");
            $stmt->execute();
            $pending_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="content-header">
                <h2>Approve Merit Claims</h2>
                <p>Review and approve merit claims submitted by students.</p>
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

            <div class="claims-container">
                <?php if (count($pending_claims) > 0): ?>
                    <h3>Pending Merit Claims (<?= count($pending_claims) ?>)</h3>
                    
                    <div class="claims-grid">
                        <?php foreach ($pending_claims as $claim): ?>
                            <div class="claim-card">
                                <div class="claim-header">
                                    <h4><?= htmlspecialchars($claim['event_title']) ?></h4>
                                    <span class="claim-date">
                                        Submitted: <?= date('M d, Y H:i', strtotime($claim['created_at'])) ?>
                                    </span>
                                </div>
                                
                                <div class="claim-details">
                                    <div class="student-info">
                                        <h5>Student Information</h5>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($claim['student_name']) ?></p>
                                        <p><strong>Matric ID:</strong> <?= htmlspecialchars($claim['student_matric_id']) ?></p>
                                        <p><strong>Major:</strong> <?= htmlspecialchars($claim['major']) ?></p>
                                    </div>
                                    
                                    <div class="event-info">
                                        <h5>Event Details</h5>
                                        <p><strong>Event Level:</strong> <?= htmlspecialchars($claim['event_level'] ?? 'UMPSA') ?></p>
                                        <p><strong>Date:</strong> <?= date('M d, Y', strtotime($claim['event_start_date'])) ?></p>
                                        <p><strong>Location:</strong> <?= htmlspecialchars($claim['location']) ?></p>
                                        <p><strong>Role Claimed:</strong> <?= htmlspecialchars($claim['role_claimed']) ?></p>
                                    </div>
                                    
                                    <div class="claim-info">
                                        <h5>Merit Points Calculation</h5>
                                        <?php
                                        $event_level = $claim['event_level'] ?? 'UMPSA';
                                        $role = $claim['role_claimed'];
                                        $calculated_points = 0;
                                        
                                        switch ($event_level) {
                                            case 'International':
                                                $calculated_points = ($role == 'Main Committee') ? 100 : (($role == 'Committee') ? 70 : 50);
                                                break;
                                            case 'National':
                                                $calculated_points = ($role == 'Main Committee') ? 80 : (($role == 'Committee') ? 50 : 40);
                                                break;
                                            case 'State':
                                                $calculated_points = ($role == 'Main Committee') ? 60 : (($role == 'Committee') ? 40 : 30);
                                                break;
                                            case 'District':
                                                $calculated_points = ($role == 'Main Committee') ? 40 : (($role == 'Committee') ? 30 : 15);
                                                break;
                                            case 'UMPSA':
                                            default:
                                                $calculated_points = ($role == 'Main Committee') ? 30 : (($role == 'Committee') ? 20 : 5);
                                                break;
                                        }
                                        ?>
                                        <p><strong>Points to be awarded:</strong> <span class="points-badge"><?= $calculated_points ?> points</span></p>
                                    </div>
                                    
                                    <div class="justification">
                                        <h5>Justification</h5>
                                        <p><?= nl2br(htmlspecialchars($claim['justification'])) ?></p>
                                    </div>
                                    
                                    <div class="official-letter">
                                        <h5>Official Letter</h5>
                                        <?php if ($claim['official_letter_path'] && file_exists($claim['official_letter_path'])): ?>
                                            <a href="<?= $claim['official_letter_path'] ?>" target="_blank" class="file-link">
                                                📄 View Official Letter (PDF)
                                            </a>
                                        <?php else: ?>
                                            <span class="no-file">No official letter uploaded</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="claim-actions">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this merit claim? This will award <?= $calculated_points ?> points to the student.')">
                                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="approve-btn">✓ Approve Claim</button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this merit claim?')">
                                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="reject-btn">✗ Reject Claim</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php else: ?>
                    <div class="no-pending-claims">
                        <div class="empty-state">
                            <h3>No Pending Claims</h3>
                            <p>There are currently no merit claims waiting for approval.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Show recent processed claims -->
            <?php
            $stmt = $pdo->prepare("
                SELECT mc.*, e.title as event_title, u.name as student_name, s.student_matric_id
                FROM merit_claims mc
                JOIN event e ON mc.event_id = e.event_id
                JOIN user u ON mc.user_id = u.user_id
                LEFT JOIN student s ON u.user_id = s.user_id
                WHERE mc.status IN ('approved', 'rejected')
                ORDER BY mc.updated_at DESC
                LIMIT 10
            ");
            $stmt->execute();
            $recent_claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (count($recent_claims) > 0): ?>
                <div class="recent-claims">
                    <h3>Recently Processed Claims</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Event</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Processed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_claims as $claim): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($claim['student_name']) ?><br>
                                            <small><?= htmlspecialchars($claim['student_matric_id']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($claim['event_title']) ?></td>
                                        <td><?= htmlspecialchars($claim['role_claimed']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $claim['status'] ?>">
                                                <?= ucfirst($claim['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $claim['updated_at'] ? date('M d, Y H:i', strtotime($claim['updated_at'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>    </div>

    <style>
    .content-header {
        margin-bottom: 30px;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-weight: 500;
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
    }

    .claims-container {
        margin-bottom: 40px;
    }

    .claims-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .claim-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e0e0e0;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .claim-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .claim-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .claim-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .claim-date {
        font-size: 12px;
        opacity: 0.9;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 8px;
        border-radius: 4px;
    }

    .claim-details {
        padding: 25px;
    }

    .claim-details > div {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .claim-details > div:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .claim-details h5 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .claim-details p {
        margin: 5px 0;
        color: #666;
        line-height: 1.5;
    }

    .student-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .event-info {
        background: #fff3cd;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }

    .claim-info {
        background: #d1ecf1;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }

    .points-badge {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
    }

    .justification {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #6c757d;
    }

    .justification p {
        font-style: italic;
        color: #555;
    }

    .official-letter {
        text-align: center;
        padding: 15px;
        background: #e8f5e8;
        border-radius: 8px;
        border-left: 4px solid #28a745;
    }

    .file-link {
        display: inline-block;
        background: #007bff;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    .file-link:hover {
        background: #0056b3;
        color: white;
    }

    .no-file {
        color: #999;
        font-style: italic;
    }

    .claim-actions {
        background: #f8f9fa;
        padding: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
        border-top: 1px solid #e0e0e0;
    }

    .approve-btn, .reject-btn {
        padding: 12px 25px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        min-width: 140px;
    }

    .approve-btn {
        background: #28a745;
        color: white;
    }

    .approve-btn:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    .reject-btn {
        background: #dc3545;
        color: white;
    }

    .reject-btn:hover {
        background: #c82333;
        transform: translateY(-1px);
    }

    .no-pending-claims {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state {
        background: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 2px dashed #e0e0e0;
    }

    .empty-state h3 {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 24px;
    }

    .empty-state p {
        margin: 0;
        color: #999;
        font-size: 16px;
    }

    .recent-claims {
        margin-top: 50px;
    }

    .recent-claims h3 {
        margin-bottom: 20px;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }

    .table-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
    }

    tr:hover {
        background: #f8f9fa;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .claims-grid {
            grid-template-columns: 1fr;
        }
        
        .claim-header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        
        .claim-actions {
            flex-direction: column;
        }
        
        .approve-btn, .reject-btn {
            width: 100%;
        }
    }

    @media (max-width: 600px) {
        .claims-container {
            padding: 0 10px;
        }
        
        .claim-card {
            margin: 0 -10px;
        }
        
        table {
            font-size: 14px;
        }
        
        th, td {
            padding: 10px 8px;
        }
    }
    </style>
</body>
</html>