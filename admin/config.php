<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ============================================
// Admin Configuration & Database Connection
// ============================================

define('ADMIN_PATH', dirname(__FILE__));
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/assets/uploads');

// Database Configuration (use central Hostinger credentials)
define('DB_HOST', 'localhost');
define('DB_USER', 'u549992181_Admin_Balmari');
define('DB_PASS', 'Admin_01_Balmari');
define('DB_NAME', 'u549992181_Balmari_DB');

// Initialize database connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_destroy();
    header('Location: ../includes/login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

// Define allowed file types and max upload sizes
define('ALLOWED_UPLOAD_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
