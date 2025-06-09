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

// Helper functions
function getSemester($date) {
    $month = date('n', strtotime($date));
    if ($month >= 9 || $month <= 1) {
        return 'Semester 1';
    } else {
        return 'Semester 2';
    }
}

// Get student participation data with roles, event levels, and calculated points
$stmt = $pdo->prepare("
    SELECT 
        vam.role,        ma.event_level,
        e.title as event_name,
        CASE 
            WHEN vam.role = 'Main Committee' THEN 
                CASE ma.event_level
                    WHEN 'International' THEN 100
                    WHEN 'National' THEN 80
                    WHEN 'State' THEN 60
                    WHEN 'District' THEN 40
                    WHEN 'UMPSA' THEN 30
                    ELSE 0
                END
            WHEN vam.role = 'Committee' THEN 
                CASE ma.event_level
                    WHEN 'International' THEN 70
                    WHEN 'National' THEN 50
                    WHEN 'State' THEN 40
                    WHEN 'District' THEN 30
                    WHEN 'UMPSA' THEN 20
                    ELSE 0
                END            WHEN vam.role = 'Participant' THEN 
                CASE ma.event_level
                    WHEN 'International' THEN 50
                    WHEN 'National' THEN 40
                    WHEN 'State' THEN 30
                    WHEN 'District' THEN 15
                    WHEN 'UMPSA' THEN 5
                    ELSE 0
                END
            ELSE vam.points_awarded        END as calculated_points    FROM view_awarded_merits vam
    JOIN merit_application ma ON vam.merit_id = ma.merit_id
    JOIN Event e ON ma.event_id = e.event_id
    WHERE vam.user_id = ?
    ORDER BY calculated_points DESC
");
$stmt->execute([$user_id]);
$student_participation = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get claims status
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count
    FROM merit_claims
    WHERE user_id = ?
    GROUP BY status
");
$stmt->execute([$user_id]);
$claims_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total merit points using proper merit calculation logic
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE 
            WHEN vam.role = 'Main Committee' THEN 
                CASE ma.event_level
                    WHEN 'International' THEN 100
                    WHEN 'National' THEN 80
                    WHEN 'State' THEN 60
                    WHEN 'District' THEN 40
                    WHEN 'UMPSA' THEN 30
                    ELSE 0
                END
            WHEN vam.role = 'Committee' THEN 
                CASE ma.event_level
                    WHEN 'International' THEN 70
                    WHEN 'National' THEN 50
                    WHEN 'State' THEN 40
                    WHEN 'District' THEN 30
                    WHEN 'UMPSA' THEN 20
                    ELSE 0
                END
            WHEN vam.role = 'Participant' THEN 
                CASE ma.event_level
                    WHEN 'International' THEN 50
                    WHEN 'National' THEN 40
                    WHEN 'State' THEN 30
                    WHEN 'District' THEN 15
                    WHEN 'UMPSA' THEN 5
                    ELSE 0
                END
            ELSE vam.points_awarded        END) as total_points    FROM view_awarded_merits vam
    JOIN merit_application ma ON vam.merit_id = ma.merit_id
    JOIN Event e ON ma.event_id = e.event_id
    WHERE vam.user_id = ?
");
$stmt->execute([$user_id]);
$total_merit = $stmt->fetch(PDO::FETCH_ASSOC);
$total_points = $total_merit['total_points'] ?? 0;

// Set page variables for shared components
$page_title = "MyPetakom - Student Dashboard";
$logout_url = "../../logout.php";
$dashboard_url = "student_dashboard.php";
$module_nav_items = [
    'profile.php' => 'Profile',
    'events.php' => 'Events',
    'attendance.php' => 'Attendance',
     'merit_management.php' => 'Merit Management'
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
    <title>MyPetakom - Student Dashboard</title>
      <script src="../../shared/js/prevent-back-button.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

</head>
<body data-login-url="../../login.php">
    <?php include_once '../../shared/components/header.php'; ?>
    
    <div class="container">
        <?php include_once '../../shared/components/sidebar.php'; ?>

        <div class="main-content">            <div class="content-header">
                <h2>Student Dashboard</h2>
                <p>Welcome back, <?= htmlspecialchars($user['name']) ?>!</p>
                <button id="downloadPDF" class="download-btn">
                    📄 Download Dashboard Report
                </button>
            </div>

            <!-- Student Profile Card -->
            <div class="dashboard-grid">
                <div class="profile-card">
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($user['name']) ?></h3>                        <div class="profile-details">
                            <p><strong>Student ID:</strong> <?= htmlspecialchars($user['student_matric_id']) ?></p>
                            <p><strong>Major:</strong> <?= htmlspecialchars($user['major']) ?></p>
                        </div>
                        <div class="merit-summary">
                            <div class="merit-stat">
                                <span class="stat-number"><?= $total_points ?></span>
                                <span class="stat-label">Total Merit Points</span>
                            </div>
                        </div>
                    </div>
                </div>                <!-- Chart 1: Claims Status -->
                <div class="chart-card">
                    <h4>Merit Claims Status</h4>
                    <canvas id="claimsChart"></canvas>
                    <?php if (empty($claims_status)): ?>
                        <div class="no-data-message">
                            <span>No claims submitted yet</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Chart 2: Student Participation Overview -->
                <div class="chart-card">
                    <h4>Event Participation & Points</h4>
                    <canvas id="participationChart"></canvas>
                    <?php if (empty($student_participation)): ?>
                        <div class="no-data-message">
                            <span>No participation data available</span>
                        </div>
                    <?php endif; ?>                </div>
            </div>
        </div>
    </div>    <script>
        // Chart.js configurations
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;

        // Chart 1: Claims Status Chart
        const claimsData = {
            labels: [<?php echo implode(',', array_map(function($item) { return '"' . ucfirst($item['status']) . '"'; }, $claims_status)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_map(function($item) { return $item['count']; }, $claims_status)); ?>],
                backgroundColor: [
                    '#FFA726', // Pending - Orange
                    '#66BB6A', // Approved - Green
                    '#EF5350', // Rejected - Red
                    '#42A5F5', // Under Review - Blue
                    '#AB47BC'  // Other - Purple
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };

        <?php if (!empty($claims_status)): ?>
        new Chart(document.getElementById('claimsChart'), {
            type: 'doughnut',
            data: claimsData,
            options: {
                plugins: {
                    legend: { 
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' claims';
                            }
                        }
                    }
                }
            }
        });
        <?php else: ?>
        // Show empty state for claims chart
        const claimsCtx = document.getElementById('claimsChart').getContext('2d');
        claimsCtx.font = '16px Arial';
        claimsCtx.fillStyle = '#666';
        claimsCtx.textAlign = 'center';
        claimsCtx.fillText('No claims data available', 200, 100);
        <?php endif; ?>

        // Chart 2: Student Participation Chart (Horizontal Bar Chart)
        const participationLabels = [
            <?php 
            foreach($student_participation as $participation) {
                echo '"' . $participation['role'] . ' - ' . $participation['event_level'] . ' (' . htmlspecialchars($participation['event_name']) . ')",';
            }
            ?>
        ];
        
        const participationPoints = [<?php echo implode(',', array_map(function($item) { return $item['calculated_points']; }, $student_participation)); ?>];

        const participationData = {
            labels: participationLabels,
            datasets: [{
                label: 'Merit Points Earned',
                data: participationPoints,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F43', '#00D2D3', '#5A67D8', '#F093FB', '#43E97B'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        };        <?php if (!empty($student_participation)): ?>
        new Chart(document.getElementById('participationChart'), {
            type: 'bar',
            data: participationData,
            options: {
                indexAxis: 'y', // Makes it horizontal
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 10,
                        bottom: 10
                    }
                },
                plugins: {
                    legend: { 
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return 'Points: ' + context.parsed.x;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Merit Points'
                        },
                        grid: {
                            display: true
                        }
                    },
                    y: {
                        title: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            font: {
                                size: 11
                            },
                            callback: function(value, index, values) {
                                const label = this.getLabelForValue(value);
                                // Truncate long labels to prevent overlap
                                if (label.length > 40) {
                                    return label.substring(0, 37) + '...';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        <?php else: ?>
        // Show empty state for participation chart
        const participationCtx = document.getElementById('participationChart').getContext('2d');
        participationCtx.font = '16px Arial';
        participationCtx.fillStyle = '#666';
        participationCtx.textAlign = 'center';        participationCtx.fillText('No participation data available', 200, 100);
        <?php endif; ?>

        // PDF Download functionality
        document.getElementById('downloadPDF').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add Title
            doc.setFontSize(20);
            doc.setFont(undefined, 'bold');
            doc.text('Student Dashboard Report', 20, 20);
            
            // Add Student Information
            doc.setFontSize(14);
            doc.setFont(undefined, 'bold');
            doc.text('Student Information:', 20, 40);
            
            doc.setFontSize(12);
            doc.setFont(undefined, 'normal');
            doc.text('Name: <?= htmlspecialchars($user['name']) ?>', 20, 50);
            doc.text('Student ID: <?= htmlspecialchars($user['student_matric_id']) ?>', 20, 60);
            doc.text('Major: <?= htmlspecialchars($user['major']) ?>', 20, 70);
            doc.text('Total Merit Points: <?= $total_points ?> points', 20, 80);
            
            // Add Claims Summary
            doc.setFontSize(14);
            doc.setFont(undefined, 'bold');
            doc.text('Merit Claims Summary:', 20, 100);
            
            doc.setFontSize(12);
            doc.setFont(undefined, 'normal');
            let yPos = 110;
            <?php if (!empty($claims_status)): ?>
                <?php foreach ($claims_status as $claim): ?>
                    doc.text('<?= ucfirst($claim['status']) ?>: <?= $claim['count'] ?> claims', 20, yPos);
                    yPos += 10;
                <?php endforeach; ?>
            <?php else: ?>
                doc.text('No claims submitted yet', 20, yPos);
                yPos += 10;
            <?php endif; ?>
            
            yPos += 10;
            
            // Add Participation Details
            doc.setFontSize(14);
            doc.setFont(undefined, 'bold');
            doc.text('Event Participation Details:', 20, yPos);
            yPos += 15;
            
            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            <?php if (!empty($student_participation)): ?>
                <?php foreach ($student_participation as $index => $participation): ?>
                    <?php if ($index < 10): // Limit to first 10 entries to fit on page ?>
                        doc.text('<?= htmlspecialchars($participation['event_name']) ?>', 20, yPos);
                        doc.text('Role: <?= htmlspecialchars($participation['role']) ?>', 25, yPos + 8);
                        doc.text('Level: <?= htmlspecialchars($participation['event_level']) ?>', 25, yPos + 16);
                        doc.text('Points: <?= $participation['calculated_points'] ?>', 25, yPos + 24);
                        yPos += 35;
                        
                        // Add new page if content exceeds page height
                        if (yPos > 250) {
                            doc.addPage();
                            yPos = 20;
                        }
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                doc.text('No participation data available', 20, yPos);
            <?php endif; ?>
            
            // Add Charts if they exist
            <?php if (!empty($claims_status)): ?>
                // Add new page for charts
                doc.addPage();
                doc.setFontSize(14);
                doc.setFont(undefined, 'bold');
                doc.text('Claims Status Chart:', 20, 20);
                
                try {
                    const claimsCanvas = document.getElementById('claimsChart');
                    if (claimsCanvas) {
                        const claimsChartDataUrl = claimsCanvas.toDataURL('image/png', 1.0);
                        doc.addImage(claimsChartDataUrl, 'PNG', 20, 30, 160, 80);
                    }
                } catch (e) {
                    doc.text('Chart could not be generated', 20, 30);
                }
            <?php endif; ?>
            
            <?php if (!empty($student_participation)): ?>
                try {
                    const participationCanvas = document.getElementById('participationChart');
                    if (participationCanvas) {
                        doc.setFontSize(14);
                        doc.setFont(undefined, 'bold');
                        doc.text('Participation Chart:', 20, 130);
                        
                        const participationChartDataUrl = participationCanvas.toDataURL('image/png', 1.0);
                        doc.addImage(participationChartDataUrl, 'PNG', 20, 140, 160, 100);
                    }
                } catch (e) {
                    doc.text('Participation chart could not be generated', 20, 140);
                }
            <?php endif; ?>
            
            // Add footer with generation date
            const now = new Date();
            const dateStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
            doc.setFontSize(8);
            doc.setFont(undefined, 'italic');
            doc.text('Report generated on: ' + dateStr, 20, 280);

            // Save the PDF
            doc.save('student_dashboard_report_<?= htmlspecialchars($user['student_matric_id']) ?>.pdf');
        });

    </script><style>
        /* Prevent horizontal scrolling */
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Ensure header stays fixed at the top */
        .header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1001 !important;
            width: 100% !important;
        }
        
        /* Adjust body to account for fixed header */
        body {
            padding-top: 80px !important;
        }
        
        /* Adjust sidebar positioning for fixed header */
        .sidebar {
            top: 80px !important;
            height: calc(100vh - 80px) !important;
        }
          /* Adjust container */
        .container {
            padding-top: 0 !important;
        }
        
        /* Download button styling */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .download-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, #218838 0%, #1abc9c 100%);
        }
        
        .download-btn:active {
            transform: translateY(0);
        }
        
          * {
            box-sizing: border-box;
        }
        
        .main-content {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 20px;
            margin-bottom: 30px;
            max-width: 100%;
        }        .profile-card {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 100%;
            overflow: hidden;
        }

        .profile-info h3 {
            margin: 0 0 20px 0;
            font-size: 28px;
            font-weight: 600;
        }        .profile-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .profile-details p {
            margin: 0;
            opacity: 0.9;
        }

        .merit-summary {
            display: flex;
            justify-content: center;
        }

        .merit-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
        }        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e1e5e9;
            max-width: 100%;
            overflow: hidden;
        }

        .chart-card h4 {
            margin: 0 0 20px 0;
            color: #2d3748;
            font-size: 18px;
            font-weight: 600;
        }        .chart-card canvas {
            max-height: 350px;
            min-height: 250px;
        }

        /* Specific styling for participation chart */
        #participationChart {
            min-height: 300px !important;
            max-height: 400px !important;
        }        .no-data-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-details {
                grid-template-columns: 1fr;
            }
            
            .merit-summary {
                justify-content: center;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .download-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</body>
</html>