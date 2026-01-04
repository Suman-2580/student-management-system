<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path."/attendanceapp/database/database.php";

$dbo = new Database();

try {
    // Insert Faculty
    $dbo->conn->exec("INSERT INTO faculty_details (user_name, name, password) VALUES ('rcb','Rahul Dravid','123')");
    echo "Faculty inserted ✅<br>";

    // Insert Students
    $dbo->conn->exec("INSERT INTO student_details (roll_no, name) VALUES ('101','Virat Kohli')");
    $dbo->conn->exec("INSERT INTO student_details (roll_no, name) VALUES ('102','Rohit Sharma')");
    $dbo->conn->exec("INSERT INTO student_details (roll_no, name) VALUES ('103','KL Rahul')");
    echo "Students inserted ✅<br>";

    // Insert Session
    $dbo->conn->exec("INSERT INTO session_details (year, term) VALUES (2025,'Spring')");
    echo "Session inserted ✅<br>";

    // Insert Course
    $dbo->conn->exec("INSERT INTO course_details (code, title, credit) VALUES ('CSE101','Web Development',3)");
    echo "Course inserted ✅<br>";

    echo "<br><b>🎉 Sample data inserted successfully!</b>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
