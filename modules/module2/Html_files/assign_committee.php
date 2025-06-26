<?php


session_start();

// These headers are to prevent users from using the browser back button to access this page after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Set up some variables for navigation and page title
$page_title = "MyPetakom - Assign committees";
$logout_url = "../../../logout.php";
$dashboard_url = "../../../dashboard/advisor_dashboard.php";
// Sidebar navigation items for this module
$module_nav_items = [
    '../../module1/profile.php'=>'Profile',
    './event_advisor.php' => 'Events',
    '../../module3/attendance.php' => 'Attendance Activity',
];
$current_module = '';
?>

<?php
// Database connection setup
$host = "localhost";
$user = "root";
$pass = "";
$db = "mypetakom";

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the event_id from the URL query string
$event_id = $_GET['event_id'];

// Handle form submission for assigning committees
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_ids = $_POST['user_ids'];
    $cr_ids = $_POST['cr_ids'];

    // Loop through each selected user and role, and insert into eventcommittee table
    foreach ($user_ids as $index => $user_id) {
        $cr_id = $cr_ids[$index];
        $sql = "INSERT INTO eventcommittee (event_id, user_id, cr_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $event_id, $user_id, $cr_id);
        $stmt->execute();
    }
    // Show success message after assigning
    $success = " Committees assigned successfully.";
}

// Get all students for the dropdown
$students = $conn->query("SELECT user_id, name FROM user WHERE role='student'");
// Get all committee roles for the dropdown
$roles = $conn->query("SELECT cr_id, cr_desc FROM committee_role");
// Get already assigned committees for this event
$committees = $conn->query("SELECT ec.committee_id, u.name, cr.cr_desc FROM eventcommittee ec 
                            JOIN user u ON ec.user_id = u.user_id 
                            JOIN committee_role cr ON ec.cr_id = cr.cr_id 
                            WHERE ec.event_id = $event_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Committee</title>
    <!-- Custom CSS for committee assignment page -->
    <link rel="stylesheet" href="../Styles/committee.css">
    <!-- Shared layout and component styles for consistent look -->
    <link rel="stylesheet" href="../../../shared/css/shared-layout.css">
    <link rel="stylesheet" href="../../../shared/css/components.css">
</head>
<body>
<?php 
// Include the shared header (logo, user info, etc.)
include '../../../shared/components/header.php'; 
?>
<div class="container" id="container">
    <!-- Sidebar for navigation between modules -->
    <?php include '../../../shared/components/sidebar.php'; ?>
    <main class="main-content">
        <div class="form-wrapper">
            <h2>Assign Committees for Event ID: <?= $event_id ?></h2>
            <!-- Show success message if committees were assigned -->
            <?php if (isset($success)): ?>
                <p class="message success"><?= $success ?></p>
            <?php endif; ?>
            <!-- Form to assign committee members and their roles -->
            <form method="POST">
                <div id="assignments">
                    <div class="assignment-row">
                        <!-- Dropdown to select student -->
                        <select name="user_ids[]" required>
                            <option value="">-- Select Student --</option>
                            <?php mysqli_data_seek($students, 0); while($s = $students->fetch_assoc()): ?>
                                <option value="<?= $s['user_id'] ?>"><?= $s['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <!-- Dropdown to select committee role -->
                        <select name="cr_ids[]" required>
                            <option value="">-- Select Role --</option>
                            <?php mysqli_data_seek($roles, 0); while($r = $roles->fetch_assoc()): ?>
                                <option value="<?= $r['cr_id'] ?>"><?= $r['cr_desc'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <!-- Button to add another row for more assignments -->
                <button type="button" onclick="addRow()">+ Add Another</button>
                <!-- Submit button to assign committees -->
                <button type="submit">Assign Committees</button>
            </form>
        </div>

        <div class="table-wrapper">
            <h3>Already Assigned Committees</h3>
            <!-- Table showing all assigned committees for this event -->
            <table>
                <thead>
                    <tr><th>Name</th><th>Role</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($c = $committees->fetch_assoc()): ?>
                        <tr>
                            <td><?= $c['name'] ?></td>
                            <td><?= $c['cr_desc'] ?></td>
                            <td>
                                <!-- Edit and delete actions for committee assignments -->
                                <a href="update_committee.php?id=<?= $c['committee_id'] ?>&event_id=<?= $event_id ?>">Edit</a>
                                <a href="delete_committee.php?id=<?= $c['committee_id'] ?>&event_id=<?= $event_id ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- JavaScript to add more assignment rows dynamically -->
<script>
function addRow() {
    // Clone the first assignment row and add it to the form
    const row = document.createElement('div');
    row.classList.add('assignment-row');
    row.innerHTML = document.querySelector('.assignment-row').innerHTML;
    document.getElementById('assignments').appendChild(row);
}
</script>
</body>
</html>

<?php 
// Close the database connection at the end
$conn->close(); 
?>