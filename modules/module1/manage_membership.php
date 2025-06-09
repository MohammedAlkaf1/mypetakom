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
$page_title = "MyPetakom - Manage Membership";
$logout_url = "../../logout.php";
$dashboard_url = "../../dashboard/admin_dashboard.php"; // or full path if needed

$module_nav_items = [
    '../../dashboard/admin_dashboard.php' => 'Dashboard',
    '../../modules/module1/view_users.php' => 'View Users',
    '../../modules/module1/manage_membership.php' => 'Manage Membership',
    '../../modules/module1/register_user.php' => 'Register New User',
    '../../modules/module1/profile.php' => 'Profile'
];
$current_module = 'manage_membership.php'; // Set active menu
// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access denied. Please login as admin.";
    exit();
}

// Handle approve/reject actions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve']) && isset($_POST['membership_id'])) {
        $membershipId = intval($_POST['membership_id']);
        $approvedBy = $_SESSION['user_id'];

        // Step 1: Get user_id from membership table
        $stmt = $conn->prepare("SELECT user_id FROM membership WHERE membership_id = ?");
        $stmt->bind_param("i", $membershipId);
        $stmt->execute();
        $result = $stmt->get_result();
        $membership = $result->fetch_assoc();
        $userId = $membership['user_id'] ?? null;
        $stmt->close();

        if ($userId) {
            // Step 2: Update membership status
            $update = $conn->prepare("UPDATE membership SET status = 'approved', approved_by = ? WHERE membership_id = ?");
            $update->bind_param("ii", $approvedBy, $membershipId);
            if ($update->execute()) {

                // Step 3: Check if student already exists
                $checkStudent = $conn->prepare("SELECT * FROM student WHERE user_id = ?");
                $checkStudent->bind_param("i", $userId);
                $checkStudent->execute();
                $checkResult = $checkStudent->get_result();
                $checkStudent->close();

                if ($checkResult->num_rows === 0) {
                    // Get real data from hidden inputs in the form
                    $major = htmlspecialchars(trim($_POST['major']));
                    $matric_id = htmlspecialchars(trim($_POST['matric_id']));

                    $insertStudent = $conn->prepare("INSERT INTO student (user_id, major, student_matric_id) VALUES (?, ?, ?)");
                    $insertStudent->bind_param("iss", $userId, $major, $matric_id);
                    $insertStudent->execute();
                    $insertStudent->close();
                }

                $_SESSION['success_message'] = 'Membership approved and student added.';
            } else {
                $_SESSION['error_message'] = 'Failed to approve membership.';
            }

            $update->close();
        } else {
            $_SESSION['error_message'] = 'Invalid membership ID.';
        }

    } elseif (isset($_POST['reject']) && isset($_POST['membership_id'])) {
        $membershipId = intval($_POST['membership_id']);

        $stmt = $conn->prepare("UPDATE membership SET status = 'not_approved' WHERE membership_id = ?");
        $stmt->bind_param("i", $membershipId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Membership rejected successfully!';
        } else {
            $_SESSION['error_message'] = 'Error rejecting membership.';
        }

        $stmt->close();
    }

    // Prevent form resubmission
    header("Location: manage_membership.php");
    exit();
}


// Get filter parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build SQL query based on filter
$sql_condition = "";
if ($status_filter != 'all') {
    $sql_condition = "WHERE m.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Get membership applications
$sql = "SELECT m.membership_id, m.status, m.student_matric_card, m.approved_by,
               u.name, u.email, s.student_matric_id, s.major,
               approver.name as approver_name
        FROM membership m 
        JOIN user u ON m.user_id = u.user_id 
        LEFT JOIN student s ON u.user_id = s.user_id
        LEFT JOIN user approver ON m.approved_by = approver.user_id
        $sql_condition
        ORDER BY 
            CASE m.status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'not_approved' THEN 3 
            END, 
            m.membership_id DESC";

$result = $conn->query($sql);

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'not_approved' THEN 1 ELSE 0 END) as rejected
    FROM membership";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Membership - MyPetakom</title>
    <link rel="stylesheet" href="../../dashboard/admin_dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../shared/css/components.css">
    <style>
        /* Additional styling specific to manage membership page */
        /* SIDEBAR OVERLAP FIX */
.main-content {
    margin-left: 260px;
    padding: 20px;
    min-height: 100vh;
    width: calc(100% - 260px);
    background-color: #fff;
    overflow-x: auto;
    box-sizing: border-box;
    transition: margin-left 0.3s ease;
}

        .main-content > *, .stats-container, .section, div[style*="overflow-x: auto"] {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        body {
            overflow-x: hidden !important;
        }

        /* PAGE TITLE */
        .main-content .section h1 {
            color: #2c3e50 !important;
            font-size: 2rem !important;
            font-weight: 600 !important;
            margin-bottom: 8px !important;
            display: flex;
            align-items: center;
        }

        .main-content .section h1 i {
            margin-right: 12px !important;
            color: #3498db !important;
        }

        .main-content .section p {
            color: #7f8c8d !important;
            font-size: 1rem !important;
            margin: 0 !important;
        }
        /* Success/Error Messages */
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: none;
            border-left: 5px solid #28a745;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.2);
            animation: slideDown 0.3s ease-out;
            padding: 15px 20px;
            margin-bottom: 20px;
            color: #155724;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: none;
            border-left: 5px solid #dc3545;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.2);
            animation: slideDown 0.3s ease-out;
            padding: 15px 20px;
            margin-bottom: 20px;
            color: #721c24;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Page Header */
        .page-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .page-header i {
            margin-right: 12px;
            color: #007bff;
            font-size: 1.8rem;
        }

        .page-header h1 {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }

        /* Approve and Reject Buttons */
.btn-approve, .btn-reject {
    padding: 6px 14px;
    font-size: 0.8rem;
    font-weight: 500;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Approve Button Style */
.btn-approve {
    background-color: #28a745;
    color: white;
    box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
}

.btn-approve:hover {
    background-color: #218838;
    transform: translateY(-1px);
}

/* Reject Button Style */
.btn-reject {
    background-color: #dc3545;
    color: white;
    box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
}

.btn-reject:hover {
    background-color: #c82333;
    transform: translateY(-1px);
}



        /* Filter Buttons */
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.9rem;
            border: 2px solid #dee2e6;
            background: #f8f9fa;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .filter-btn:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-color: #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
            color: #343a40;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
        }

        .status-approved {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .status-rejected {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }



        /* Action Forms */
        .action-form {
            display: inline-block;
            margin: 0 3px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-subtitle {
                margin-left: 0;
                margin-top: 5px;
            }
            
            .filter-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .filter-btn {
                justify-content: center;
                padding: 12px 20px;
            }
        }

        .stat-box {
    flex: 1;
    min-width: 160px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    text-align: left;
}

.stat-box.blue    { border-left: 5px solid #007bff; }
.stat-box.orange  { border-left: 5px solid #ffc107; }
.stat-box.green   { border-left: 5px solid #28a745; }
.stat-box.red     { border-left: 5px solid #dc3545; }

.stat-box h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-box p {
    margin-top: 5px;
    font-size: 0.9rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 6px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 8px rgba(0,0,0,0.03);
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    padding: 12px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
    color: #333;
}

tbody tr:hover {
    background-color: #f5f5f5;
    transition: background-color 0.2s ease;
}


    </style>
</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>

    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Page Title -->
        <div class="section">
            <div class="page-header">
                <i class="bi bi-person-check"></i>
                <h1>Manage Membership</h1>
            </div>
            
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert-success">
                <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
       

        <!-- Filter Section -->
        <div class="section">
            <h2><i class="bi bi-funnel"></i> Filter Applications</h2>
            <div class="filter-buttons">
                <a href="manage_membership.php?status=all" 
                   class="filter-btn <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
                    All Applications
                </a>
                <a href="manage_membership.php?status=pending" 
                   class="filter-btn <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                    Pending
                </a>
                <a href="manage_membership.php?status=approved" 
                   class="filter-btn <?php echo $status_filter == 'approved' ? 'active' : ''; ?>">
                    Approved
                </a>
                <a href="manage_membership.php?status=not_approved" 
                   class="filter-btn <?php echo $status_filter == 'not_approved' ? 'active' : ''; ?>">
                    Rejected
                </a>
            </div>
        </div>
        
        <!-- Membership Applications Table -->
        <div class="section">
            <h2><i class="bi bi-table"></i> Membership Applications</h2>
            <?php if ($result->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> ID</th>
                                <th><i class="bi bi-person"></i> Name</th>
                                <th><i class="bi bi-envelope"></i> Email</th>
                                <th><i class="bi bi-card-text"></i> Matric ID</th>
                                <th><i class="bi bi-book"></i> Major</th>
                                <th><i class="bi bi-flag"></i> Status</th>
                                <th><i class="bi bi-person-check"></i> Approved By</th>
                                <th><i class="bi bi-gear"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['membership_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_matric_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['major'] ?? 'N/A'); ?></td>
                                
                                    <?php if (!empty($row['student_matric_card'])): ?>
                                        <a href="../../uploads/<?php echo htmlspecialchars($row['student_matric_card']); ?>" 
                                           target="_blank" class="btn-view">
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <span class="status-badge status-pending">
                                            <i class="bi bi-hourglass-split"></i> Pending
                                        </span>
                                    <?php elseif ($row['status'] == 'approved'): ?>
                                        <span class="status-badge status-approved">
                                            <i class="bi bi-check-circle"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-rejected">
                                            <i class="bi bi-x-circle"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['approver_name'] ? htmlspecialchars($row['approver_name']) : '-'; ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="membership_id" value="<?php echo $row['membership_id']; ?>">
                                            <button type="submit" name="approve" class="btn btn-approve" 
                                                    onclick="return confirm('Approve membership for <?php echo htmlspecialchars($row['name']); ?>?')">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="membership_id" value="<?php echo $row['membership_id']; ?>">
                                            <button type="submit" name="reject" class="btn btn-reject" 
                                                    onclick="return confirm('Reject membership for <?php echo htmlspecialchars($row['name']); ?>?')">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-style: italic;"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No membership applications found
                    <?php if ($status_filter != 'all'): ?>
                        <br><small>Try changing the filter to see other applications</small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set active menu item
            if (typeof setActiveMenuItem === 'function') {
                setActiveMenuItem('membership');
            }
            
            // Add smooth hover effects to table rows
            const tableRows = document.querySelectorAll('table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                    this.style.transition = 'transform 0.2s ease';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>