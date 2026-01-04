<?php
session_start();
if (!isset($_SESSION['faculty'])) {
    header("Location: login.php");
    exit();
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
$db = new Database();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty = $_SESSION['faculty']; // current logged in faculty
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check old password
    $sql = "SELECT * FROM faculty_details WHERE user_name=:username AND password=:password";
    $stmt = $db->conn->prepare($sql);
    $stmt->execute([
        ':username' => $faculty,
        ':password' => $old_password
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $message = "❌ Old password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ New passwords do not match!";
    } else {
        // Update password
        $sql = "UPDATE faculty_details SET password=:password WHERE user_name=:username";
        $stmt = $db->conn->prepare($sql);
        $stmt->execute([
            ':password' => $new_password,
            ':username' => $faculty
        ]);
        $message = "✅ Password changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
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
            max-width: 500px;
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
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
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
        <h2>Change Password</h2>
    </header>

    <div class="container">
        <?php if($message): ?>
            <div class="<?php echo strpos($message,'❌')!==false ? 'error':'message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Old Password:</label>
            <input type="password" name="old_password" required>

            <label>New Password:</label>
            <input type="password" name="new_password" required>

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Update Password</button>
        </form>

        <a class="back" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
