<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path."/attendanceapp/database/database.php";
$db = new Database();

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_no = $_POST['roll_no'];
    $name = $_POST['name'];

    $sql = "INSERT INTO student_details (roll_no, name) VALUES (:roll_no, :name)";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute([
        ':roll_no' => $roll_no,
        ':name' => $name
    ]);

    $message = "✅ Student added successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
        }
        header {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        h2 {
            margin: 0;
            text-align: center;
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        form label, form input, form button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            font-size: 16px;
        }
        form input {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        form button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #218838;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        .back {
            display: block;
            margin-top: 20px;
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
        <h2>Add New Student</h2>
    </header>

    <div class="container">
        <?php if($message) echo "<div class='message'>$message</div>"; ?>

        <form method="post">
            <label>Roll No:</label>
            <input type="text" name="roll_no" required>

            <label>Name:</label>
            <input type="text" name="name" required>

            <button type="submit">Add Student</button>
        </form>

        <a class="back" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
