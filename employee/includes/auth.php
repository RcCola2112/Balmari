<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db.php';

// Check if user is logged in
function isEmployeeLoggedIn() {
    return isset($_SESSION['employee_id']) && isset($_SESSION['employee_email']);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isEmployeeLoggedIn()) {
        // Clear any partial session data
        session_unset();
        session_destroy();
        
        // Start new session for error message
        session_start();
        $_SESSION['login_error'] = 'Please login to access this page';
        
        header('Location: login.php');
        exit();
    }
}

// Get current logged-in employee
function getCurrentEmployee() {
    if (isEmployeeLoggedIn()) {
        return [
            'id' => $_SESSION['employee_id'],
            'email' => $_SESSION['employee_email'],
            'full_name' => $_SESSION['employee_name'] ?? 'Employee'
        ];
    }
    return null;
}

// Login employee
function loginEmployee($email, $password) {
    global $conn;
    
    // Check if connection exists
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in loginEmployee");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Use `user` table for authentication
        $stmt = $conn->prepare("SELECT id, email, full_name, password, COALESCE(role, '') as role FROM `user` WHERE email = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement in loginEmployee");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Email not found'];
        }
        
        $employee = $result->fetch_assoc();
        
        if (!password_verify($password, $employee['password'])) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }
        
        // Set session variables
        $_SESSION['employee_id'] = $employee['id'];
        $_SESSION['employee_email'] = $employee['email'];
        $_SESSION['employee_name'] = $employee['full_name'];
        
        $stmt->close();
        return ['success' => true, 'message' => 'Login successful'];
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

// Logout employee
function logoutEmployee() {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Update employee profile
function updateEmployeeProfile($employee_id, $full_name) {
    global $conn;
    
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in updateEmployeeProfile");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $conn->prepare("UPDATE `user` SET full_name = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement in updateEmployeeProfile");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $full_name, $employee_id);
        
        if ($stmt->execute()) {
            $_SESSION['employee_name'] = $full_name;
            $stmt->close();
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    } catch (Exception $e) {
        error_log("Update profile error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

// Update employee password
function updateEmployeePassword($employee_id, $current_password, $new_password) {
    global $conn;
    
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in updateEmployeePassword");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // First verify current password
        $stmt = $conn->prepare("SELECT password FROM `user` WHERE id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement in updateEmployeePassword");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Employee not found'];
        }
        
        $employee = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($current_password, $employee['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Update password
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE `user` SET password = ? WHERE id = ?");
        
        if (!$update_stmt) {
            error_log("Failed to prepare update statement in updateEmployeePassword");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $update_stmt->bind_param("si", $new_password_hash, $employee_id);
        
        if ($update_stmt->execute()) {
            $update_stmt->close();
            return ['success' => true, 'message' => 'Password updated successfully'];
        } else {
            $update_stmt->close();
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    } catch (Exception $e) {
        error_log("Update password error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

// Get all carousel items
function getCarouselItems() {
    global $conn;
    
    // Check if connection exists
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in getCarouselItems");
        return [];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM carousel ORDER BY created_at DESC");
        if (!$stmt) {
            error_log("Failed to prepare statement in getCarouselItems");
            return [];
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        $stmt->close();
        return $items;
        
    } catch (Exception $e) {
        error_log("Get carousel error: " . $e->getMessage());
        return [];
    }
}

// Get single carousel item
function getCarouselItem($id) {
    global $conn;
    
    // Check if connection exists
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in getCarouselItem");
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM carousel WHERE id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement in getCarouselItem");
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $item = $result->fetch_assoc();
        $stmt->close();
        return $item;
        
    } catch (Exception $e) {
        error_log("Get carousel item error: " . $e->getMessage());
        return null;
    }
}

// Add carousel item
function addCarouselItem($title, $subtitle, $image) {
    global $conn;
    
    // Check if connection exists
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in addCarouselItem");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO carousel (title, subtitle, image) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Failed to prepare statement in addCarouselItem");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("sss", $title, $subtitle, $image);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Carousel item added successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to add carousel item'];
        }
        
    } catch (Exception $e) {
        error_log("Add carousel error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

// Update carousel item
function updateCarouselItem($id, $title, $subtitle) {
    global $conn;
    
    // Check if connection exists
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in updateCarouselItem");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        $stmt = $conn->prepare("UPDATE carousel SET title = ?, subtitle = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement in updateCarouselItem");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ssi", $title, $subtitle, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Carousel item updated successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to update carousel item'];
        }
        
    } catch (Exception $e) {
        error_log("Update carousel error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

// Delete carousel item
function deleteCarouselItem($id) {
    global $conn;
    
    // Check if connection exists
    if (!isset($conn) || !$conn) {
        error_log("Database connection not available in deleteCarouselItem");
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // First get the image filename
        $item = getCarouselItem($id);
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM carousel WHERE id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement in deleteCarouselItem");
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Delete image file if it exists
            if ($item) {
                $imagePath = '../assets/uploads/carousel/' . $item['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            return ['success' => true, 'message' => 'Carousel item deleted successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to delete carousel item'];
        }
        
    } catch (Exception $e) {
        error_log("Delete carousel error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

// Handle image upload
function uploadCarouselImage($file) {
    // Validate file
    if (empty($file) || !isset($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file selected'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error_msg = $error_messages[$file['error']] ?? 'Unknown upload error';
        error_log("File upload error: " . $error_msg);
        return ['success' => false, 'message' => $error_msg];
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP allowed'];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../assets/uploads/carousel/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            error_log("Failed to create upload directory: " . $upload_dir);
            return ['success' => false, 'message' => 'Failed to create upload directory'];
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        error_log("Upload directory is not writable: " . $upload_dir);
        return ['success' => false, 'message' => 'Upload directory is not writable'];
    }
    
    // Generate unique filename
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'carousel_' . time() . '_' . uniqid() . '.' . $file_ext;
    $destination = $upload_dir . $new_filename;
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'message' => 'Image uploaded successfully', 'filename' => $new_filename];
    } else {
        error_log("Failed to move uploaded file to: " . $destination);
        return ['success' => false, 'message' => 'Failed to upload image'];
    }
}
?>
