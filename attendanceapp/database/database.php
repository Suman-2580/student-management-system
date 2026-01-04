<?php
class Database
{
    private $servername = "localhost";  // server ka naam (XAMPP me hamesha localhost hota hai)
    private $username = "root";         // default phpMyAdmin ka username
    private $password = "";             // default phpMyAdmin ka password khali hota hai
    private $dbname = "attendance_db";  // jo database humne banaya tha
    public  $conn = null;               // isme connection store hoga

    // Jab class ka object banega tab constructor chalega
    public function __construct() {
        try {
            // MySQL ke sath connection banane ke liye PDO use kar rahe hain
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "✅ Database Connected Successfully";
        } catch(PDOException $e) {
            echo "❌ Connection failed: " . $e->getMessage();
        }
    }
}
?>
