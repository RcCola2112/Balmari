<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
// Ensure admin helper functions (log_activity, etc.) are available
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['admin_user_id'])) {
    // Log the logout activity
    log_activity('logout', 'admin', $_SESSION['admin_user_id'], 'User logged out');
}

// Destroy session
session_destroy();

// Redirect to central login page in the main folder
header('Location: ../includes/login.php?logout=1');
exit;
