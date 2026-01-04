<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}

// ✅ Direct access block
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("❌ Access denied. Please export from report page.");
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
$db = new Database();

$course_id = $_POST['course_id'] ?? "";
$session_id = $_POST['session_id'] ?? "";
$search = $_POST['search'] ?? "";

// Report Query
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

// ✅ Excel Export (as CSV/Excel)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "Roll No\tName\tTotal Classes\tPresent\tAbsent\tPercentage\n";

foreach ($report as $row) {
    $percentage = $row['total_classes'] > 0 
        ? round(($row['present_count'] / $row['total_classes']) * 100, 2) 
        : 0;
    echo $row['roll_no'] . "\t" .
         $row['name'] . "\t" .
         $row['total_classes'] . "\t" .
         $row['present_count'] . "\t" .
         $row['absent_count'] . "\t" .
         $percentage . "%\n";
}
exit;
