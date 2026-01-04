<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path."/attendanceapp/database/database.php";

$dbo = new Database();

// Create student_details table
$c="CREATE TABLE student_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(20) UNIQUE,
    name VARCHAR(50)
)";
$dbo->conn->exec($c);

// Create faculty_details table
$c="CREATE TABLE faculty_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(20) UNIQUE,
    name VARCHAR(100),
    password VARCHAR(50)
)";
$dbo->conn->exec($c);

// Create session_details table
$c="CREATE TABLE session_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT,
    term VARCHAR(50),
    UNIQUE(year,term)
)";
$dbo->conn->exec($c);

// Create course_details table
$c="CREATE TABLE course_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE,
    title VARCHAR(50),
    credit INT
)";
$dbo->conn->exec($c);

// Create attendance_details table
$c="CREATE TABLE attendance_details (
    faculty_id INT,
    course_id INT,
    session_id INT,
    student_id INT,
    on_date DATE,
    status VARCHAR(10),
    PRIMARY KEY(faculty_id,course_id,session_id,student_id,on_date)
)";
$dbo->conn->exec($c);

echo "✅ All tables created successfully!";
?>
