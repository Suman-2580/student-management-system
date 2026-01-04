<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
$db = new Database();

$courses = $db->conn->query("SELECT * FROM course_details")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $db->conn->query("SELECT * FROM session_details")->fetchAll(PDO::FETCH_ASSOC);

$course_id = $_POST['course_id'] ?? 1;
$session_id = $_POST['session_id'] ?? 1;
$search = $_POST['search'] ?? "";

function getDisplayName($row, $possibleKeys)
{
    foreach ($possibleKeys as $key) {
        if (isset($row[$key])) return $row[$key];
    }
    return "N/A";
}

$sql = "SELECT s.roll_no, s.name,
        COUNT(*) as total_classes,
        SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status='Absent' THEN 1 ELSE 0 END) as absent_count
        FROM student_details s
        LEFT JOIN attendance_details a 
        ON s.id = a.student_id 
        AND a.course_id = :course_id
        AND a.session_id = :session_id
        WHERE (s.roll_no LIKE :search OR s.name LIKE :search)
        GROUP BY s.id";

$stmt = $db->conn->prepare($sql);
$stmt->execute([
    ':course_id' => $course_id,
    ':session_id' => $session_id,
    ':search' => "%$search%"
]);
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
        }
        header {
            background-color: #28a745;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2 {
            margin: 0;
        }
        form label, form select, form input[type="text"], form button {
            margin-top: 5px;
            margin-right: 10px;
            font-size: 16px;
        }
        form button {
            padding: 8px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0069d9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .export-btn {
            margin-top: 15px;
            padding: 10px 25px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .export-btn:hover {
            background-color: #138496;
        }
        .back {
            display: block;
            margin-top: 30px;
            text-align: center;
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }
        .back:hover {
            text-decoration: underline;
        }
        input[type="text"] {
            padding: 6px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <header>
        <h2>Attendance Report</h2>
    </header>

    <div class="container">
        <!-- Filter Form -->
        <form method="post">
            <label>Course:</label>
            <select name="course_id">
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if ($course_id == $c['id']) echo "selected"; ?>>
                        <?php echo getDisplayName($c, ['course_name', 'name', 'c_name', 'title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Session:</label>
            <select name="session_id">
                <?php foreach ($sessions as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php if ($session_id == $s['id']) echo "selected"; ?>>
                        <?php echo getDisplayName($s, ['session_name', 'name', 's_name', 'title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Search:</label>
            <input type="text" name="search" placeholder="Roll No or Name" value="<?php echo $search; ?>">

            <button type="submit">Filter</button>
        </form>

        <!-- Report Table -->
        <table>
            <tr>
                <th>Roll No</th>
                <th>Name</th>
                <th>Total Classes</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Percentage</th>
            </tr>
            <?php foreach ($report as $row):
                $percentage = $row['total_classes'] > 0
                    ? round(($row['present_count'] / $row['total_classes']) * 100, 2)
                    : 0;
            ?>
                <tr>
                    <td><?php echo $row['roll_no']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['total_classes']; ?></td>
                    <td><?php echo $row['present_count']; ?></td>
                    <td><?php echo $row['absent_count']; ?></td>
                    <td><?php echo $percentage; ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>

        <form method="post" action="export_excel.php">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
            <input type="hidden" name="search" value="<?php echo $search; ?>">
            <button type="submit" class="export-btn">⬇ Export to Excel</button>
        </form>

        <a class="back" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
