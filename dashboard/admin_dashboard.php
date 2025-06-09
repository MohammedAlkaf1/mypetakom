<?php
session_start();
require_once '../sql/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "MyPetakom - Admin Dashboard";
$logout_url = "../logout.php";
$dashboard_url = "../dashboard/admin_dashboard.php";
$module_nav_items = [
    '../dashboard/admin_dashboard.php' => 'Dashboard',
    '../modules/module1/view_users.php' => 'View Users',
    '../modules/module1/manage_membership.php' => 'Manage Membership',
    '../modules/module1/register_user.php' => 'Register New User',
    '../modules/module1/profile.php' => 'Profile'
];
$current_module = 'admin_dashboard.php';

// Fetch data from the database
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

// Get pending memberships
$sql5 = "SELECT COUNT(*) as pending_memberships FROM membership WHERE status = 'pending'";
$result5 = $conn->query($sql5);
$pending_memberships = $result5->fetch_assoc()['pending_memberships'];

// Get event attendance data for bar chart
$sql_events = "SELECT e.event_id, e.title, COUNT(aslot.user_id) as attendance_count
               FROM Event e
               LEFT JOIN Attendance a ON e.event_id = a.event_id
               LEFT JOIN Attendance_Slot aslot ON a.attendance_id = aslot.attendance_id
               GROUP BY e.event_id";
$events_result = $conn->query($sql_events);

$events = [];
$attendance_counts = [];
while ($row = $events_result->fetch_assoc()) {
    $events[] = $row['title'];
    $attendance_counts[] = $row['attendance_count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../shared/css/shared-layout.css" />
    <link rel="stylesheet" href="../shared/css/components.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="../shared/js/prevent-back-button.js"></script>
    <!-- Chart.js for Bar Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jsPDF for PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <?php include_once '../shared/components/header.php'; ?>
    <div class="container">
        <?php include_once '../shared/components/sidebar.php'; ?>

        <!-- Main content area -->
        <div class="main-content mt-4">
            <h1>Admin Dashboard</h1>
            <p>Welcome to MyPetakom administration panel</p>

            <!-- Card Section -->
            <div class="row">
                <!-- Total Users Card -->
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-header">Total Users</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title"><?php echo $total_users; ?></h5>
                            <p class="card-text">Total registered users.</p>
                        </div>
                    </div>
                </div>

                <!-- Total Students Card -->
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-header">Total Students</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title"><?php echo $total_students; ?></h5>
                            <p class="card-text">Total registered students.</p>
                        </div>
                    </div>
                </div>

                <!-- Total Staff Card -->
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-header">Total Staff</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title"><?php echo $total_staff; ?></h5>
                            <p class="card-text">Total staff members.</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Memberships Card -->
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-danger h-100">
                        <div class="card-header">Pending Memberships</div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title"><?php echo $pending_memberships; ?></h5>
                            <p class="card-text">Pending membership applications.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart Section -->
            <h2>Event Attendance</h2>
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
            <button id="downloadPDF" class="btn btn-primary mt-3">Download as PDF</button>
        </div>
    </div>

    <script>
        // Bar Chart using Chart.js
 // Bar Chart using Chart.js
const ctx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($events); ?>,
        datasets: [{
            label: 'Number of Attendees',
            data: <?php echo json_encode($attendance_counts); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Event Attendance Count'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1, // Ensures the ticks on the Y-axis are integer values
                    precision: 0, // No decimals on Y-axis
                }
            }
        }
    }
});


        // Function to download content as PDF
        document.getElementById('downloadPDF').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add Title
            doc.setFontSize(18);
            doc.text('Admin Dashboard Report', 10, 10);

            // Add the Card Section (Text Content)
            doc.setFontSize(12);
            doc.text(`Total Users: ${<?php echo $total_users; ?>}`, 10, 30);
            doc.text(`Total Students: ${<?php echo $total_students; ?>}`, 10, 40);
            doc.text(`Total Staff: ${<?php echo $total_staff; ?>}`, 10, 50);
            doc.text(`Pending Memberships: ${<?php echo $pending_memberships; ?>}`, 10, 60);

            // Add Bar Chart as an Image
            const canvas = document.getElementById('attendanceChart');
            const chartDataUrl = canvas.toDataURL();
            doc.addImage(chartDataUrl, 'PNG', 10, 70, 180, 100);

            // Save the PDF
            doc.save('admin_dashboard_report.pdf');
        });
    </script>
</body>
</html>
