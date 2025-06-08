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
require_once '../sql/db.php';

$user_id = $_SESSION['user_id'];
$page_title = "MyPetakom - Admin Dashboard";
$logout_url = "../logout.php";
$dashboard_url = "admin_dashboard.php"; // or full path if needed

$module_nav_items = [
    '../dashboard/admin_dashboard.php' => 'Dashboard',
    '../modules/module1/view_users.php' => 'View Users',
    '../modules/module1/manage_membership.php' => 'Manage Membership',
    '../modules/module1/register_user.php' => 'Register New User',
    '../modules/module1/profile.php' => 'Profile'
];
$current_module = 'admin_dashboard.php'; // Set active menu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve']) && isset($_POST['membership_id'])) {
        $membershipId = intval($_POST['membership_id']);
        $stmt = $conn->prepare("UPDATE membership SET status = 'approved' WHERE membership_id = ?");
        $stmt->bind_param("i", $membershipId);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['reject']) && isset($_POST['membership_id'])) {
        $membershipId = intval($_POST['membership_id']);
        $stmt = $conn->prepare("UPDATE membership SET status = 'rejected' WHERE membership_id = ?");
        $stmt->bind_param("i", $membershipId);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect back to prevent form resubmission
    header("Location: admin_dashboard.php");
    exit();
}


// Check if user is admin (simple check)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access denied. Please login as admin.";
    exit();
}

// Get total users
$sql1 = "SELECT COUNT(*) as total_users FROM user";
$result1 = $conn->query($sql1);
$total_users = $result1->fetch_assoc()['total_users'];

// Get total students
$sql2 = "SELECT COUNT(*) as total_students FROM user WHERE role = 'student'";
$result2 = $conn->query($sql2);
$total_students = $result2->fetch_assoc()['total_students'];

// Get total staff
$sql3 = "SELECT COUNT(*) as total_staff FROM user WHERE role = 'staff'";
$result3 = $conn->query($sql3);
$total_staff = $result3->fetch_assoc()['total_staff'];

// Get total events
$sql4 = "SELECT COUNT(*) as total_events FROM event";
$result4 = $conn->query($sql4);
$total_events = $result4->fetch_assoc()['total_events'];

// Get pending memberships
$sql5 = "SELECT COUNT(*) as pending_memberships FROM membership WHERE status = 'pending'";
$result5 = $conn->query($sql5);
$pending_memberships = $result5->fetch_assoc()['pending_memberships'];

// Get approved memberships
$sql6 = "SELECT COUNT(*) as approved_memberships FROM membership WHERE status = 'approved'";
$result6 = $conn->query($sql6);
$approved_memberships = $result6->fetch_assoc()['approved_memberships'];

// Get recent users (last 5 users)
$sql7 = "SELECT name, email, role FROM user ORDER BY user_id DESC LIMIT 5";
$result7 = $conn->query($sql7);

// Get pending membership details
$sql8 = "SELECT m.membership_id, u.name, u.email 
         FROM membership m 
         JOIN user u ON m.user_id = u.user_id 
         WHERE m.status = 'pending' 
         LIMIT 10";
$result8 = $conn->query($sql8);

// Handle approve/reject actions
if (isset($_POST['approve'])) {
    $membership_id = intval($_POST['membership_id']); // Added security
    $sql = "UPDATE membership SET status = 'approved' WHERE membership_id = $membership_id";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = 'Membership approved successfully!';
        header('Location: admin_dashboard.php');
        exit(); // Important: stops further execution
    } else {
        $_SESSION['error_message'] = 'Error approving membership. Please try again.';
        header('Location: admin_dashboard.php');
        exit();
    }
}

if (isset($_POST['reject'])) {
    $membership_id = intval($_POST['membership_id']); // Added security
    $sql = "UPDATE membership SET status = 'rejected' WHERE membership_id = $membership_id";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = 'Membership rejected successfully!';
        header('Location: admin_dashboard.php');
        exit(); // Important: stops further execution
    } else {
        $_SESSION['error_message'] = 'Error rejecting membership. Please try again.';
        header('Location: admin_dashboard.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MyPetakom</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../shared/css/components.css">

</head>
<body data-login-url="../../login.php">
    <?php include_once '../shared/components/header.php'; ?>

    <div class="container">
    <?php include_once '../shared/components/sidebar.php'; ?>

     <div class="main-content">
         
        <!-- Page Title -->
        <div class="section">
            <h1 style="color: #333; margin-bottom: 10px;">
                <i class="bi bi-speedometer2" style="margin-right: 10px;"></i>
                Admin Dashboard
            </h1>
            <p style="color: #666; margin: 0;">Welcome to MyPetakom administration panel</p>
        </div>
        
        <!-- Statistics Section -->
        <div class="stats-container">
            <div class="stat-box blue">
                <h3><?php echo $total_users; ?></h3>
                <p><i class="bi bi-people"></i> Total Users</p>
            </div>
            
            <div class="stat-box green">
                <h3><?php echo $total_students; ?></h3>
                <p><i class="bi bi-mortarboard"></i> Students</p>
            </div>
            
            <div class="stat-box orange">
                <h3><?php echo $total_staff; ?></h3>
                <p><i class="bi bi-person-badge"></i> Staff</p>
            </div>
            
            <div class="stat-box red">
                <h3><?php echo $total_events; ?></h3>
                <p><i class="bi bi-calendar-event"></i> Total Events</p>
            </div>
            
            <div class="stat-box blue">
                <h3><?php echo $pending_memberships; ?></h3>
                <p><i class="bi bi-hourglass-split"></i> Pending Memberships</p>
            </div>
            
            <div class="stat-box green">
                <h3><?php echo $approved_memberships; ?></h3>
                <p><i class="bi bi-check-circle"></i> Approved Members</p>
            </div>
        </div>
        
        <!-- Recent Users Section -->
        <div class="section">
            <h2><i class="bi bi-people" style="margin-right: 10px;"></i>Recent Users</h2>
            <?php if ($result7->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="bi bi-person"></i> Name</th>
                                <th><i class="bi bi-envelope"></i> Email</th>
                                <th><i class="bi bi-shield"></i> Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result7->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge-<?php echo $row['role']; ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No users found
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pending Memberships Section -->
        <div class="section">
            <h2><i class="bi bi-hourglass-split" style="margin-right: 10px;"></i>Pending Membership Applications</h2>
            <?php if ($result8->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="bi bi-person"></i> Name</th>
                                <th><i class="bi bi-envelope"></i> Email</th>
                                <th><i class="bi bi-gear"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result8->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="membership_id" value="<?php echo $row['membership_id']; ?>">
                                        <button type="submit" name="approve" class="btn btn-approve" 
                                                onclick="return confirm('Approve this membership for <?php echo htmlspecialchars($row['name']); ?>?')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="membership_id" value="<?php echo $row['membership_id']; ?>">
                                        <button type="submit" name="reject" class="btn btn-reject" 
                                                onclick="return confirm('Reject this membership for <?php echo htmlspecialchars($row['name']); ?>?')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-check-all" style="font-size: 2rem; margin-bottom: 10px; display: block; color: #28a745;"></i>
                    No pending applications
                </div>
            <?php endif; ?>
        </div>
        
        <!-- User Distribution Chart -->
        <div class="section">
            <h2><i class="bi bi-pie-chart" style="margin-right: 10px;"></i>User Distribution</h2>
            <div class="chart-container">
                <div class="chart-label">
                    <strong>Students:</strong> <?php echo $total_students; ?> 
                    (<?php echo $total_users > 0 ? round(($total_students / $total_users * 100), 1) : 0; ?>%)
                </div>
                <div class="chart-bar">
                    <div style="background: linear-gradient(90deg, #3498db, #2980b9); height: 100%; width: <?php echo ($total_users > 0) ? ($total_students / $total_users * 100) : 0; ?>%; border-radius: 12px; transition: width 0.8s ease;"></div>
                </div>
                
                <div class="chart-label">
                    <strong>Staff:</strong> <?php echo $total_staff; ?> 
                    (<?php echo $total_users > 0 ? round(($total_staff / $total_users * 100), 1) : 0; ?>%)
                </div>
                <div class="chart-bar">
                    <div style="background: linear-gradient(90deg, #2ecc71, #27ae60); height: 100%; width: <?php echo ($total_users > 0) ? ($total_staff / $total_users * 100) : 0; ?>%; border-radius: 12px; transition: width 0.8s ease;"></div>
                </div>
                
                <?php $admin_count = $total_users - $total_students - $total_staff; ?>
                <div class="chart-label">
                    <strong>Admin:</strong> <?php echo $admin_count; ?> 
                    (<?php echo $total_users > 0 ? round(($admin_count / $total_users * 100), 1) : 0; ?>%)
                </div>
                <div class="chart-bar">
                    <div style="background: linear-gradient(90deg, #e74c3c, #c0392b); height: 100%; width: <?php echo ($total_users > 0) ? ($admin_count / $total_users * 100) : 0; ?>%; border-radius: 12px; transition: width 0.8s ease;"></div>
                </div>
            </div>
        </div>
        
        <!-- Membership Status Chart -->
        <div class="section">
            <h2><i class="bi bi-graph-up" style="margin-right: 10px;"></i>Membership Status</h2>
            <div class="chart-container">
                <?php $total_memberships = $approved_memberships + $pending_memberships; ?>
                
                <div class="chart-label">
                    <strong>Approved:</strong> <?php echo $approved_memberships; ?>
                    (<?php echo $total_memberships > 0 ? round(($approved_memberships / $total_memberships * 100), 1) : 0; ?>%)
                </div>
                <div class="chart-bar">
                    <div style="background: linear-gradient(90deg, #2ecc71, #27ae60); height: 100%; width: <?php echo ($total_memberships > 0) ? ($approved_memberships / $total_memberships * 100) : 0; ?>%; border-radius: 12px; transition: width 0.8s ease;"></div>
                </div>
                
                <div class="chart-label">
                    <strong>Pending:</strong> <?php echo $pending_memberships; ?>
                    (<?php echo $total_memberships > 0 ? round(($pending_memberships / $total_memberships * 100), 1) : 0; ?>%)
                </div>
                <div class="chart-bar">
                    <div style="background: linear-gradient(90deg, #f39c12, #e67e22); height: 100%; width: <?php echo ($total_memberships > 0) ? ($pending_memberships / $total_memberships * 100) : 0; ?>%; border-radius: 12px; transition: width 0.8s ease;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional CSS for role badges -->
    <style>
        
        .badge-student {
            background-color: #3498db;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-staff {
            background-color: #2ecc71;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-admin {
            background-color: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>

    <script>
        // Add loading animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set active menu item
            setActiveMenuItem('dashboard');
            
            // Update header notification count
            updateNotificationCount(<?php echo $pending_memberships; ?>);
            
            // Animate chart bars
            const chartBars = document.querySelectorAll('.chart-bar > div');
            chartBars.forEach((bar, index) => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500 + (index * 200));
            });
            
            // Add hover effects to tables
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    row.addEventListener('mouseenter', function() {
                        this.style.transform = 'scale(1.01)';
                        this.style.transition = 'transform 0.2s ease';
                    });
                    row.addEventListener('mouseleave', function() {
                        this.style.transform = 'scale(1)';
                    });
                });
            });
            
            // Handle responsive layout
            handleResponsiveLayout();
        });

        // Function to handle responsive layout
        function handleResponsiveLayout() {
            const mainContent = document.querySelector('.main-content');
            
            function adjustLayout() {
                if (window.innerWidth <= 768) {
                    if (mainContent) {
                        mainContent.style.marginLeft = '0';
                    }
                } else {
                    if (mainContent) {
                        mainContent.style.marginLeft = '250px';
                    }
                }
            }
            
            adjustLayout();
            window.addEventListener('resize', adjustLayout);
        }
    </script>
</body>
</html>