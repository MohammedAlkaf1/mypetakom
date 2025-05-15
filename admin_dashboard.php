<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mypetakom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM membership_requests";
$result = $conn->query($sql);
echo "<h2>Membership Applications</h2>";
echo "<table><tr><th>Name</th><th>Card</th><th>Action</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr><td>" . $row['student_name'] . "</td>";
    echo "<td><a href='" . $row['student_card'] . "'>View Card</a></td>";
    echo "<td><a href='approve.php?id=" . $row['id'] . "'>Approve</a> | <a href='reject.php?id=" . $row['id'] . "'>Reject</a></td></tr>";
}
echo "</table>";
?>
