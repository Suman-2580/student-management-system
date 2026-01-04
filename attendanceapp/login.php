<?php
session_start();
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path."/attendanceapp/database/database.php";
$db = new Database();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM faculty_details WHERE user_name = :username AND password = :password";
    $stmt = $db->conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['faculty'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "❌ Invalid Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faculty Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
            width: 350px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 25px;
            color: #333;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .login-container button {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .login-container .error {
            color: red;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Faculty Login</h2>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <?php if ($message): ?>
            <div class="error"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
