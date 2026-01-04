<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
$db = new Database();

// ---- Get logged-in faculty_id from username ----
$facultyUsername = $_SESSION['faculty'];
$facStmt = $db->conn->prepare("SELECT id FROM faculty_details WHERE user_name = :u LIMIT 1");
$facStmt->execute([':u' => $facultyUsername]);
$facRow = $facStmt->fetch(PDO::FETCH_ASSOC);
$faculty_id = $facRow ? (int)$facRow['id'] : 1;

// ---- Fetch data for form ----
$students = $db->conn->query("SELECT * FROM student_details ORDER BY roll_no")->fetchAll(PDO::FETCH_ASSOC);
$courses  = $db->conn->query("SELECT * FROM course_details ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $db->conn->query("SELECT * FROM session_details ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$message = "";
$today = date('Y-m-d');

// Defaults for select boxes
$selected_course  = $courses[0]['id']  ?? 1;
$selected_session = $sessions[0]['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_course  = (int)($_POST['course_id']  ?? $selected_course);
    $selected_session = (int)($_POST['session_id'] ?? $selected_session);

    foreach ($students as $student) {
        $status = isset($_POST['student_' . $student['id']]) ? "Present" : "Absent";

        $check = $db->conn->prepare(
            "SELECT 1 FROM attendance_details
             WHERE faculty_id=:faculty_id AND course_id=:course_id
               AND session_id=:session_id AND student_id=:student_id
               AND on_date=:on_date LIMIT 1"
        );
        $check->execute([
            ':faculty_id' => $faculty_id,
            ':course_id'  => $selected_course,
            ':session_id' => $selected_session,
            ':student_id' => $student['id'],
            ':on_date'    => $today
        ]);

        if ($check->rowCount() == 0) {
            $ins = $db->conn->prepare(
                "INSERT INTO attendance_details
                 (faculty_id, course_id, session_id, student_id, on_date, status)
                 VALUES (:faculty_id, :course_id, :session_id, :student_id, :on_date, :status)"
            );
            $ins->execute([
                ':faculty_id' => $faculty_id,
                ':course_id'  => $selected_course,
                ':session_id' => $selected_session,
                ':student_id' => $student['id'],
                ':on_date'    => $today,
                ':status'     => $status
            ]);
        } else {
            $upd = $db->conn->prepare(
                "UPDATE attendance_details
                 SET status=:status
                 WHERE faculty_id=:faculty_id AND course_id=:course_id
                   AND session_id=:session_id AND student_id=:student_id
                   AND on_date=:on_date"
            );
            $upd->execute([
                ':status'     => $status,
                ':faculty_id' => $faculty_id,
                ':course_id'  => $selected_course,
                ':session_id' => $selected_session,
                ':student_id' => $student['id'],
                ':on_date'    => $today
            ]);
        }
    }

    $message = "✅ Attendance saved for $today (Course ID: $selected_course, Session ID: $selected_session)";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
        }
        header {
            background-color: #007BFF;
            color: white;
            text-align: center;
            padding: 20px;
        }
        h2 {
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        select, input[type="checkbox"] {
            padding: 8px;
            margin: 5px 0;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        button {
            padding: 10px 25px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 15px;
            color: green;
            font-weight: bold;
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
    </style>
</head>
<body>
    <header>
        <h2>Mark Attendance</h2>
    </header>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <h3>Select Course & Session</h3>
            Course:
            <select name="course_id" required>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if ($selected_course == $c['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($c['name'] ?? 'Unknown'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            &nbsp;&nbsp;
            Session:
            <select name="session_id" required>
                <?php foreach ($sessions as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php if ($selected_session == $s['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($s['name'] ?? 'Unknown'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <table>
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Present?</th>
                </tr>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['roll_no']); ?></td>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><input type="checkbox" name="student_<?php echo $student['id']; ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit">Save Attendance</button>
        </form>

        <a class="back" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
