<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
$db = new Database();

$facultyUsername = $_SESSION['faculty'];
$facStmt = $db->conn->prepare("SELECT id FROM faculty_details WHERE user_name = :u LIMIT 1");
$facStmt->execute([':u' => $facultyUsername]);
$facRow = $facStmt->fetch(PDO::FETCH_ASSOC);
$faculty_id = $facRow ? (int)$facRow['id'] : 1;

$courses  = $db->conn->query("SELECT * FROM course_details ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $db->conn->query("SELECT * FROM session_details ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$message = "";
$selected_date  = date('Y-m-d'); 
$selected_course  = $courses[0]['id']  ?? 1;
$selected_session = $sessions[0]['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_date    = $_POST['date'] ?? $selected_date;
    $selected_course  = (int)($_POST['course_id'] ?? $selected_course);
    $selected_session = (int)($_POST['session_id'] ?? $selected_session);

    $students = $db->conn->prepare(
        "SELECT s.id, s.roll_no, s.name,
                a.status
         FROM student_details s
         LEFT JOIN attendance_details a
           ON s.id = a.student_id
           AND a.course_id = :course_id
           AND a.session_id = :session_id
           AND a.on_date = :on_date
           AND a.faculty_id = :faculty_id
         ORDER BY s.roll_no"
    );
    $students->execute([
        ':course_id'  => $selected_course,
        ':session_id' => $selected_session,
        ':on_date'    => $selected_date,
        ':faculty_id' => $faculty_id
    ]);
    $students = $students->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST['update_attendance'])) {
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
                ':on_date'    => $selected_date
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
                    ':on_date'    => $selected_date,
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
                    ':on_date'    => $selected_date
                ]);
            }
        }
        $message = "✅ Attendance updated for $selected_date (Course ID: $selected_course, Session ID: $selected_session)";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
        }
        header {
            background-color: #ffc107;
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
        label, select, input[type="date"] {
            font-size: 16px;
            margin-right: 10px;
            margin-top: 5px;
        }
        button {
            padding: 10px 25px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #138496;
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
            background-color: #17a2b8;
            color: white;
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
        <h2>Update Attendance</h2>
    </header>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Date:</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>

            <label>Course:</label>
            <select name="course_id" required>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if ($selected_course == $c['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($c['name'] ?? 'Unknown'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Session:</label>
            <select name="session_id" required>
                <?php foreach ($sessions as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php if ($selected_session == $s['id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($s['name'] ?? 'Unknown'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="load_attendance">Load Attendance</button>
        </form>

        <?php if (isset($students) && count($students) > 0): ?>
            <form method="post">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                <input type="hidden" name="session_id" value="<?php echo $selected_session; ?>">

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
                            <td>
                                <input type="checkbox" name="student_<?php echo $student['id']; ?>" 
                                <?php if (($student['status'] ?? '') === 'Present') echo "checked"; ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit" name="update_attendance">Update Attendance</button>
            </form>
        <?php endif; ?>

        <a class="back" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
