<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ============================================
// Admin Authentication Helper Functions
// ============================================

/**
 * Check if user is logged in
 * Redirect to login if not
 */
if (!function_exists('require_login')) {
function require_login() {
    if (!isset($_SESSION['admin_user_id'])) {
        header('Location: login.php');
        exit;
    }
}
}

/**
 * Check if user has specific role
 */
if (!function_exists('has_role')) {
function has_role($required_role) {
    if (!isset($_SESSION['admin_role'])) {
        return false;
    }
    
    $user_role = $_SESSION['admin_role'];
    
    // admin has access to everything
    if ($user_role === 'admin') {
        return true;
    }
    
    // Check role hierarchy
    $role_hierarchy = [
        'admin' => 2,
        'employee' => 1
    ];
    
    return isset($role_hierarchy[$user_role]) && $role_hierarchy[$user_role] >= ($role_hierarchy[$required_role] ?? 0);
}
}

/**
 * Check if user is super admin
 */
if (!function_exists('is_super_admin')) {
function is_super_admin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}
}

/**
 * Log activity
 */
if (!function_exists('log_activity')) {
function log_activity($action, $entity_type = null, $entity_id = null, $description = null, $old_value = null, $new_value = null) {
    global $pdo;
    
    if (!isset($_SESSION['admin_user_id'])) {
        return false;
    }
    
        try {
            // Inspect activity_logs columns once
            $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs'");
            $colStmt->execute();
            $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);

            $has_employee_id = in_array('employee_id', $cols, true);
            $has_action = in_array('action', $cols, true);
            $has_description = in_array('description', $cols, true);
            $has_old = in_array('old_value', $cols, true) || in_array('old', $cols, true);
            $has_new = in_array('new_value', $cols, true) || in_array('new', $cols, true);
            $has_user_id = in_array('user_id', $cols, true);
            $has_event_type = in_array('event_type', $cols, true);
            $has_message = in_array('message', $cols, true);
            $has_ip = in_array('ip', $cols, true);
            $has_user_agent = in_array('user_agent', $cols, true);
            $has_meta = in_array('meta', $cols, true);
            $has_employee_name = in_array('employee_name', $cols, true) || in_array('user_name', $cols, true) || in_array('full_name', $cols, true);

            // Try to get the current admin user's display name
            $employee = null;
            $employee_name = null;
            if (function_exists('admin_get_current_user')) {
                $employee = admin_get_current_user();
                $employee_name = $employee['full_name'] ?? $employee['name'] ?? null;
            }

            // Preferred insertion mapping for older schema
            if ($has_employee_id && $has_action) {
                // If an employee_name-like column exists, include it in the insert
                if ($has_employee_name) {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (employee_id, employee_name, action, entity_type, entity_id, description, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    return $stmt->execute([
                        $_SESSION['admin_user_id'],
                        $employee_name,
                        $action,
                        $entity_type,
                        $entity_id,
                        $description,
                        $old_value,
                        $new_value
                    ]);
                }
                $stmt = $pdo->prepare("INSERT INTO activity_logs (employee_id, action, entity_type, entity_id, description, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $_SESSION['admin_user_id'],
                    $action,
                    $entity_type,
                    $entity_id,
                    $description,
                    $old_value,
                    $new_value
                ]);
            }

            // Fallback to newer schema mapping
            $fields = [];
            $placeholders = [];
            $values = [];

            if ($has_event_type) {
                $fields[] = 'event_type'; $placeholders[] = '?'; $values[] = $action;
            }
            if ($has_user_id) {
                $fields[] = 'user_id'; $placeholders[] = '?'; $values[] = $_SESSION['admin_user_id'];
            }
            if ($has_employee_name && $employee_name !== null) {
                // prioritize storing display name on newer schemas
                $fields[] = (in_array('employee_name', $cols, true) ? 'employee_name' : (in_array('user_name', $cols, true) ? 'user_name' : 'full_name'));
                $placeholders[] = '?';
                $values[] = $employee_name;
            }
            if ($has_message) {
                $fields[] = 'message'; $placeholders[] = '?'; $values[] = $description ?? '';
            }
            if ($has_ip) {
                $fields[] = 'ip'; $placeholders[] = '?'; $values[] = $_SERVER['REMOTE_ADDR'] ?? null;
            }
            if ($has_user_agent) {
                $fields[] = 'user_agent'; $placeholders[] = '?'; $values[] = $_SERVER['HTTP_USER_AGENT'] ?? null;
            }
            if ($has_event_type && $entity_type) {
                $fields[] = 'entity_type'; $placeholders[] = '?'; $values[] = $entity_type;
            } elseif ($has_event_type && !$entity_type) {
                // ensure column order alignment
                $fields[] = 'entity_type'; $placeholders[] = '?'; $values[] = null;
            }
            if (in_array('entity_id', $cols, true) && $entity_id !== null) {
                $fields[] = 'entity_id'; $placeholders[] = '?'; $values[] = (string)$entity_id;
            }
            // old/new values into meta JSON when available
            if ($has_meta) {
                $meta = [];
                if ($old_value !== null) $meta['old'] = $old_value;
                if ($new_value !== null) $meta['new'] = $new_value;
                if ($entity_id !== null) $meta['entity_id'] = $entity_id;
                if (!empty($meta)) {
                    $fields[] = 'meta'; $placeholders[] = '?'; $values[] = json_encode($meta);
                }
            } elseif ($has_old || $has_new) {
                if ($has_old) { $fields[] = 'old_value'; $placeholders[] = '?'; $values[] = $old_value; }
                if ($has_new) { $fields[] = 'new_value'; $placeholders[] = '?'; $values[] = $new_value; }
            }

            if (empty($fields)) {
                // nothing to insert into known schema
                return false;
            }

            $sql = "INSERT INTO activity_logs (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($values);

        } catch (PDOException $e) {
            error_log('Activity log failed: ' . $e->getMessage());
            return false;
        }
}
}

/**
 * Get current user info
 */
if (!function_exists('get_current_user')) {
function get_current_user() {
    global $pdo;
    
    if (!isset($_SESSION['admin_user_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT id, full_name, email, role FROM `user` WHERE id = ?");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch();
}
}

// Provide a uniquely named admin getter to avoid colliding with PHP's built-in get_current_user()
if (!function_exists('admin_get_current_user')) {
function admin_get_current_user() {
    global $pdo;

    if (!isset($_SESSION['admin_user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, full_name, email, role FROM `user` WHERE id = ?");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch();
}
}

/**
 * Get dashboard stats
 */
if (!function_exists('get_dashboard_stats')) {
function get_dashboard_stats() {
    global $pdo;
    
    $stats = [
        'total_projects' => 0,
        'ongoing_projects' => 0,
        'completed_projects' => 0,
        'new_inquiries' => 0,
        'active_services' => 0,
        'total_users' => 0
    ];
    
    // Defensive: ensure we have a PDO instance
    if (empty($pdo) || !($pdo instanceof PDO)) {
        // try to require config which should create $pdo
        if (file_exists(__DIR__ . '/../config.php')) {
            try {
                require_once __DIR__ . '/../config.php';
            } catch (Exception $e) {
                error_log('Failed to load config for dashboard stats: ' . $e->getMessage());
                return $stats;
            }
        } else {
            return $stats;
        }
    }

    try {
        // helper to check table existence
        $tableExists = function($tableName) use ($pdo) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
            $stmt->execute([$tableName]);
            return ((int)$stmt->fetchColumn()) > 0;
        };

        // Ongoing projects
        if ($tableExists('projects_in_progress')) {
            $stats['ongoing_projects'] = (int) $pdo->query("SELECT COUNT(*) FROM projects_in_progress")->fetchColumn();
        } elseif ($tableExists('projects') ) {
            // fallback: maybe all projects in a single table with a status column
            $stats['ongoing_projects'] = (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('in_progress','ongoing')")->fetchColumn();
        }

        // Completed projects
        if ($tableExists('completed_projects')) {
            $stats['completed_projects'] = (int) $pdo->query("SELECT COUNT(*) FROM completed_projects")->fetchColumn();
        } elseif ($tableExists('projects')) {
            $stats['completed_projects'] = (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('completed','done')")->fetchColumn();
        }

        // Prefer filesystem-based counts if project directories exist (match completed.php/progress.php)
        $rootPath = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/../../');
        $fsCompletedDir = $rootPath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . 'completed' . DIRECTORY_SEPARATOR;
        $fsProgressDir = $rootPath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . 'progress' . DIRECTORY_SEPARATOR;

        $countFoldersWithMetadata = function($dir) {
            if (!is_dir($dir)) return 0;
            $items = array_diff(scandir($dir, SCANDIR_SORT_DESCENDING), ['.', '..']);
            $count = 0;
            foreach ($items as $folder) {
                $projectPath = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
                $metadataFile = $projectPath . 'metadata.json';
                if (is_dir($projectPath) && file_exists($metadataFile)) {
                    $count++;
                }
            }
            return $count;
        };

        // If filesystem directories exist, use those counts to match public pages
        $fsCompletedCount = $countFoldersWithMetadata($fsCompletedDir);
        $fsProgressCount = $countFoldersWithMetadata($fsProgressDir);
        if ($fsCompletedCount > 0 || $fsProgressCount > 0) {
            $stats['completed_projects'] = $fsCompletedCount;
            $stats['ongoing_projects'] = $fsProgressCount;
            $stats['total_projects'] = $stats['completed_projects'] + $stats['ongoing_projects'];
        }

        $stats['total_projects'] = $stats['ongoing_projects'] + $stats['completed_projects'];

        // New inquiries in last 24 hours, preference for contact_messages table
        if ($tableExists('contact_messages')) {
            $stats['new_inquiries'] = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE (is_read = 0 OR is_read IS NULL) AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        } elseif ($tableExists('inquiries')) {
            $stats['new_inquiries'] = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        }

        // Active services
        if ($tableExists('services')) {
            $stats['active_services'] = (int) $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();
        }

        // Team members: prefer counting users with roles admin/employee, but fallback to total users
        if ($tableExists('user')) {
            try {
                $stats['total_users'] = (int) $pdo->query("SELECT COUNT(*) FROM `user` WHERE LOWER(TRIM(role)) IN ('admin','employee')")->fetchColumn();
            } catch (PDOException $e) {
                // fallback to any users
                $stats['total_users'] = (int) $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
            }
        } elseif ($tableExists('users')) {
            $stats['total_users'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE LOWER(TRIM(role)) IN ('admin','employee')")->fetchColumn();
        }

    } catch (PDOException $e) {
        error_log('Dashboard stats error: ' . $e->getMessage());
    }
    
    return $stats;
}
}
