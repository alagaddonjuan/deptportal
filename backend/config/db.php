<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'cs_dept_portal';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully!"; // Uncomment to test
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>