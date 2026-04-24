<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db.php';
require_once 'includes/auth.php';

requireLogin();

// Get carousel item ID from URL
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id === 0) {
    header('Location: manage_carousel.php?error=invalid');
    exit();
}

// Delete carousel item
try {
    $result = deleteCarouselItem($item_id);
    
    // Redirect back to manage page with status
    if ($result['success']) {
        header('Location: manage_carousel.php?deleted=1');
    } else {
        header('Location: manage_carousel.php?error=delete_failed');
    }
} catch (Exception $e) {
    error_log("Delete carousel error: " . $e->getMessage());
    header('Location: manage_carousel.php?error=exception');
}
exit();
?>
