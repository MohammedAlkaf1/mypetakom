<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mypetakom";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_GET['event_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $cr_id = $_POST['cr_id'];

    $sql = "INSERT INTO eventcommittee (event_id, user_id, cr_id)
            VALUES ('$event_id', '$user_id', '$cr_id')";

    if ($conn->query($sql) === TRUE) {
        $success = "✅ Committee assigned successfully.";
    } else {
        $error = "❌ Error: " . $conn->error;
    }
}

$students = $conn->query("SELECT user_id, name FROM user WHERE role='student'");
$roles = $conn->query("SELECT cr_id, cr_desc FROM comitee_role");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Committee</title>
    <link rel="stylesheet" href="../Styles/assign_committee.css">
</head>
<body>
    <nav>
        <div class="logo">MyPetakom</div>
        <div class="logout"><button>Logout</button></div>
    </nav>

    <div class="container" id="container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-content">
                <ul>
                    <li><a href="#">Profile</a></li>
                    <li><a href="event_advisor.php">Events</a></li>
                    <li><a href="#">Manage Attendance</a></li>
                    <li><a href="#">Merit Applications</a></li>
                </ul>
            </div>
        </aside>

        <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

        <main class="main-content">
            <div class="form-wrapper">
                <h2>Assign Committee for Event ID: <?= $event_id ?></h2>
                <?php if (isset($success)): ?>
                    <p class="message" style="color: green;"><?= $success ?></p>
                <?php elseif (isset($error)): ?>
                    <p class="message" style="color: red;"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST">
                    <label for="user_id">Select Student:</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">-- Select Student --</option>
                        
                        <?php while($s = $students->fetch_assoc()): ?>
                            <option value="<?= $s['user_id'] ?>"><?= $s['name'] ?></option>
                        <?php endwhile; ?>
                    </select>

                    

                    <label for="cr_id">Select Role:</label>
                    <select name="cr_id" id="cr_id" required>
                        <option value="">-- Select Role --</option>
                        <?php while($r = $roles->fetch_assoc()): ?>
                            <option value="<?= $r['cr_id'] ?>"><?= $r['cr_desc'] ?></option>
                        <?php endwhile; ?>
                    </select>

                    <button type="submit">Assign Committee</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const container = document.getElementById('container');
            const toggleBtn = document.querySelector('.sidebar-toggle');

            if (sidebar.classList.contains('collapsing')) {
                sidebar.classList.remove('collapsing');
                toggleBtn.style.left = '240px';
                container.classList.remove('collapsed');
            } else {
                sidebar.classList.add('collapsing');
                toggleBtn.style.left = '20px';
                container.classList.add('collapsed');
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>


