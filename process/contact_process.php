<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../includes/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.php?error=1');
    exit();
}

// Get form data
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate required fields
if (empty($full_name) || empty($email) || empty($phone) || empty($message)) {
    header('Location: ../contact.php?error=1');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../contact.php?error=1');
    exit();
}

try {
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, phone, message) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param("ssss", $full_name, $email, $phone, $message);
    
    // Execute query
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Close statement
    $stmt->close();
    
    // Redirect to success page
    header('Location: ../contact.php?success=1');
    exit();
    
} catch (Exception $e) {
    // Log error for debugging (in production, log to file)
    error_log("Contact form error: " . $e->getMessage());
    
    // Redirect to error page
    header('Location: ../contact.php?error=1');
    exit();
}
?>
