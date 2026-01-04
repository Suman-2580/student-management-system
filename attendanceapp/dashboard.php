<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}
$facultyUsername = $_SESSION['faculty'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        h2 {
            margin: 0;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 40px 20px;
            gap: 25px;
        }
        .card {
            background: white;
            width: 220px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card a {
            display: block;
            text-decoration: none;
            color: #007BFF;
            font-size: 16px;
            margin-top: 10px;
            font-weight: bold;
        }
        .card a:hover {
            text-decoration: underline;
        }
        .logout {
            text-align: center;
            margin: 50px 0;
        }
        .logout a {
            text-decoration: none;
            color: white;
            background-color: red;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: bold;
        }
        .logout a:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <header>
        <h2>Welcome, <?php echo htmlspecialchars($facultyUsername); ?> 👋</h2>
    </header>

    <div class="container">
        <div class="card">
            <h3>Mark Attendance</h3>
            <a href="attendance.php">📋 Go</a>
        </div>
        <div class="card">
            <h3>Update Attendance</h3>
            <a href="update_attendance.php">✏ Go</a>
        </div>
        <div class="card">
            <h3>View Report</h3>
            <a href="report.php">📊 Go</a>
        </div>
        <div class="card">
            <h3>Add Student</h3>
            <a href="add_student.php">👨‍🎓 Go</a>
        </div>
        <div class="card">
            <h3>Add Faculty</h3>
            <a href="add_faculty.php">👨‍🏫 Go</a>
        </div>
        <div class="card">
            <h3>Change Password</h3>
            <a href="changepassword.php">🔑 Go</a>
        </div>
    </div>

    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
