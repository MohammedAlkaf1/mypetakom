<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../../sql/db.php';
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    die("Invalid student ID provided");
}

// Get student information and merit points
$stmt = $pdo->prepare("
    SELECT u.name, u.email, s.student_matric_id, s.major,
           COALESCE(SUM(vam.points_awarded), 0) as total_points
    FROM user u 
    LEFT JOIN student s ON u.user_id = s.user_id 
    LEFT JOIN view_awarded_merits vam ON u.user_id = vam.user_id
    WHERE u.user_id = ?
    GROUP BY u.user_id
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found";
    exit();
}

$current_year = date('Y');
$current_month = date('n');
if ($current_month >= 9) {
    $academic_year = $current_year . '/' . ($current_year + 1);
} else {
    $academic_year = ($current_year - 1) . '/' . $current_year;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Merit Verification</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 500px; 
            margin: 50px auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        .header h2 {
            color: #333;
            margin: 0;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
        }
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            margin: 15px 0; 
            padding: 12px 0; 
            border-bottom: 1px solid #eee; 
        }
        .label { 
            font-weight: bold; 
            color: #555; 
        }
        .value { 
            color: #333; 
            text-align: right;
        }
        .value.highlight { 
            color: #007bff; 
            font-size: 20px; 
            font-weight: bold; 
        }
        .footer {
            text-align: center; 
            margin-top: 30px; 
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999; 
            font-size: 12px;
        }
        .verified-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🎓 Student Merit Verification</h2>
            <p>MyPetakom Merit System</p>
            <div class="verified-badge">✓ VERIFIED</div>
        </div>
        
        <div class="info-row">
            <span class="label">Student ID:</span>
            <span class="value"><?= htmlspecialchars($student['student_matric_id']) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Name:</span>
            <span class="value"><?= htmlspecialchars($student['name']) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Major:</span>
            <span class="value"><?= htmlspecialchars($student['major']) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Academic Year:</span>
            <span class="value"><?= $academic_year ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Total Merit Points:</span>
            <span class="value highlight"><?= $student['total_points'] ?> points</span>
        </div>
        
        <div class="footer">
            Verified on <?= date('F j, Y \a\t g:i A') ?><br>
            <small>This verification is authentic and issued by MyPetakom System</small>
        </div>
    </div>
</body>
</html>