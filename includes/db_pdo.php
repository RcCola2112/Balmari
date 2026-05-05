<?php
// Database connection (PDO): prefer environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'u549992181_Balmari_DB';
$user = getenv('DB_USER') ?: 'u549992181_Admin_Balmari';
$pass = getenv('DB_PASS') ?: 'Admin_01_Balmari';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Also provide $pdo alias for modules expecting that variable
    $pdo = $conn;
} catch(PDOException $e) {
    error_log('PDO DB connection failed: ' . $e->getMessage());
    die('Database connection failed');
}
?>
