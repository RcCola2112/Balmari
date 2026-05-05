<?php
// Database connection: prefer environment variables to avoid committing credentials
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'u549992181_Admin_Balmari';
$pass = getenv('DB_PASS') ?: 'Admin_01_Balmari';
$db   = getenv('DB_NAME') ?: 'u549992181_Balmari_DB';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection failed');
}

$conn->set_charset('utf8mb4');
?>
