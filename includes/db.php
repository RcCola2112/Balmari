<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hostinger Production Database Configuration
$host = "localhost"; // usually localhost in Hostinger
$user = "u549992181_Admin_Balmari";
$pass = "Admin_01_Balmari";
$db   = "u549992181_Balmari_DB";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
