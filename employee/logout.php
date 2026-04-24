<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Start a new session for success message
    session_start();
    $_SESSION['logout_success'] = true;
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
}

// Redirect to central login page in the main folder
header('Location: ../includes/login.php?logout=1');
exit;
?>
