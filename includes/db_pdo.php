<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hostinger Production Database Configuration (PDO Version - Optional)
// This is a more modern and secure alternative to MySQLi

// Use same credentials as includes/db.php / admin config
$host = "localhost";
$db   = "u549992181_Balmari_DB";
$user = "u549992181_Admin_Balmari";
$pass = "Admin_01_Balmari";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Also provide $pdo alias for modules expecting that variable
    $pdo = $conn;
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
