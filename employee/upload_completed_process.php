<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/auth.php';

// Ensure user is logged in
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload_completed.php');
    exit();
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$type = '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$error_msg = '';

// Validate required inputs
if (empty($title)) {
    $error_msg = 'Project title is required';
} elseif (empty($_FILES) || empty($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = 'Please select a cover image';
} else {
    try {
        // Create projects directory structure
        $base_upload_dir = '../assets/projects/completed/';
        if (!is_dir($base_upload_dir)) {
            mkdir($base_upload_dir, 0755, true);
        }
        
        // Generate unique project ID
        $project_id = time() . '_' . uniqid();
        $project_dir = $base_upload_dir . $project_id . '/';
        $details_dir = $project_dir . 'details/';
        
        // Create project directories
        if (!mkdir($project_dir, 0755, true) || !mkdir($details_dir, 0755, true)) {
            $error_msg = 'Failed to create project directories';
        } else {
            // Handle cover image
            $cover_file = $_FILES['cover_image'];
            $ext = strtolower(pathinfo($cover_file['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($ext, $allowed_types)) {
                $error_msg = 'Invalid cover image type. Only JPG, PNG, GIF, and WebP allowed';
            } else {
                $cover_filename = 'cover.' . $ext;
                $cover_path = $project_dir . $cover_filename;
                
                if (!move_uploaded_file($cover_file['tmp_name'], $cover_path)) {
                    $error_msg = 'Failed to upload cover image';
                } else {
                    // Handle detail images
                    $detail_count = 0;
                    $detail_errors = [];
                    
                    if (!empty($_FILES['detail_images']['name'][0])) {
                        for ($i = 0; $i < count($_FILES['detail_images']['name']); $i++) {
                            $file_error = $_FILES['detail_images']['error'][$i];
                            $file_name = $_FILES['detail_images']['name'][$i];
                            
                            // Check for upload errors
                            if ($file_error !== UPLOAD_ERR_OK) {
                                $error_messages = [
                                    UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
                                    UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                                ];
                                $detail_errors[] = $file_name . ': ' . ($error_messages[$file_error] ?? 'Unknown error');
                                continue;
                            }
                            
                            $detail_file = [
                                'name' => $file_name,
                                'tmp_name' => $_FILES['detail_images']['tmp_name'][$i],
                                'size' => $_FILES['detail_images']['size'][$i],
                                'type' => $_FILES['detail_images']['type'][$i]
                            ];
                            
                            $detail_ext = strtolower(pathinfo($detail_file['name'], PATHINFO_EXTENSION));
                            if (!in_array($detail_ext, $allowed_types)) {
                                $detail_errors[] = $detail_file['name'] . ': Invalid file type (must be JPG, PNG, GIF, or WebP)';
                                continue;
                            }
                            
                            $detail_filename = ($detail_count + 1) . '.' . $detail_ext;
                            $detail_path = $details_dir . $detail_filename;
                            
                            if (move_uploaded_file($detail_file['tmp_name'], $detail_path)) {
                                $detail_count++;
                            } else {
                                $detail_errors[] = $detail_file['name'] . ': Failed to save file';
                                error_log("Failed to move uploaded file to: " . $detail_path);
                            }
                        }
                    }
                    
                    // Create metadata.json (type removed)
                    $metadata = [
                        'id' => $project_id,
                        'title' => $title,
                        'location' => $location,
                        'description' => $description,
                        'cover_image' => $cover_filename,
                        'detail_count' => $detail_count,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $metadata_path = $project_dir . 'metadata.json';
                    if (file_put_contents($metadata_path, json_encode($metadata, JSON_PRETTY_PRINT))) {
                        $success_msg = 'Project uploaded successfully with ' . $detail_count . ' detail image(s)!';
                        if (!empty($detail_errors)) {
                            $success_msg .= ' (' . count($detail_errors) . ' image(s) skipped due to errors)';
                        }
                        $_SESSION['upload_success'] = $success_msg;
                        header('Location: upload_completed.php?success=1');
                        exit();
                    } else {
                        $error_msg = 'Failed to save project metadata';
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Upload completed error: " . $e->getMessage());
        $error_msg = 'An error occurred during upload. Please try again.';
    }
}

// If error, redirect back with error message
if (!empty($error_msg)) {
    $_SESSION['upload_error'] = $error_msg;
    header('Location: upload_completed.php?error=1');
    exit();
}
?>
