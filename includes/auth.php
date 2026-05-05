<?php
// Shared authentication helpers for both admin and employee areas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include login throttle helper
$throttle_path = __DIR__ . '/login_throttle.php';
if (file_exists($throttle_path)) {
    require_once $throttle_path;
}

// expects $conn (mysqli) from includes/db.php
function loginShared($email, $password) {
    global $conn;
    // Throttle key combines normalized email and client IP to reduce brute-force risk
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $throttle_key = 'e:' . md5(strtolower(trim($email))) . '|ip:' . $client_ip;
    if (function_exists('is_locked_out')) {
        $locked = is_locked_out($throttle_key);
        if ($locked !== false) {
            return ['success' => false, 'message' => 'Too many failed attempts. Try again in ' . ceil($locked/60) . ' minute(s).'];
        }
    }
    if (!isset($conn) || !$conn) {
        error_log('Database connection missing in loginShared');
        return ['success' => false, 'message' => 'Database connection error'];
    }

    try {
        // Use `user` table which holds role, full_name, email, password
        $stmt = $conn->prepare("SELECT id, email, full_name, password, COALESCE(role, '') as role FROM `user` WHERE email = ? LIMIT 1");
        if (!$stmt) return ['success' => false, 'message' => 'Database error'];
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email not found'];
        }

        $user = $res->fetch_assoc();
        $stmt->close();

        if (!password_verify($password, $user['password'])) {
            if (function_exists('record_failed_login')) {
                record_failed_login($throttle_key);
            }
            return ['success' => false, 'message' => 'Incorrect password'];
        }

        // No status column in `user` table in current schema; allow login if password matches

        // Populate canonical session values (always set)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        // Backwards-compat: set employee session keys used by employee area
        $_SESSION['employee_id'] = $user['id'];
        $_SESSION['employee_email'] = $user['email'];
        $_SESSION['employee_name'] = $user['full_name'];

        // Only set admin session keys for admin role
        $adminRoles = ['admin'];
        if (!empty($user['role']) && in_array($user['role'], $adminRoles, true)) {
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
        } else {
            // Ensure no stale admin session keys remain
            unset($_SESSION['admin_user_id'], $_SESSION['admin_email'], $_SESSION['admin_name'], $_SESSION['admin_role']);
        }

        // Note: `user` table in DB does not have last_login/status by default;
        // skip updating last_login to avoid errors.

        // Successful login: clear throttle state for this key
        if (function_exists('reset_login_attempts')) {
            reset_login_attempts($throttle_key);
        }
        return ['success' => true, 'message' => 'Login successful', 'role' => $user['role']];
    } catch (Exception $e) {
        error_log('loginShared error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred'];
    }
}

function isAnyUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminSession() {
    return isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_role']);
}

function logoutShared($redirect = '../index.php') {
    session_unset();
    session_destroy();
    header('Location: ' . $redirect);
    exit;
}

?>
