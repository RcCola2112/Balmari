<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent "headers already sent" errors
if (ob_get_level() === 0) {
    ob_start();
}
// Auto-require config and auth if not already loaded
if (!defined('ADMIN_PATH')) {
    require_once dirname(__FILE__) . '/../config.php';
}
// Safely include auth helpers: only include the file if it seems to define required functions.
$auth_path = dirname(__FILE__) . '/auth.php';
if (file_exists($auth_path)) {
    $auth_src = @file_get_contents($auth_path);
    if ($auth_src !== false) {
        $has_def = preg_match('/function\s+require_login\s*\(/', $auth_src);
        $pos_def = $has_def ? strpos($auth_src, 'function require_login') : false;
        $pos_call = strpos($auth_src, 'require_login(');

        // If there's a call to require_login before the function definition, skip including to avoid fatal.
        if ($pos_call !== false && ($pos_def === false || $pos_call < $pos_def)) {
            error_log('Auth include skipped: auth.php appears to call require_login before defining it');
        } elseif ($has_def) {
            require_once $auth_path;
        } else {
            error_log('Auth include skipped: auth.php missing require_login definition');
        }
    } else {
        error_log('Auth include skipped: unreadable auth.php');
    }
} else {
    error_log('Auth include skipped: auth.php not found');
}

// Check if user is logged in (defensive: support different auth helper names)
if (function_exists('require_login')) {
    require_login();
} elseif (function_exists('requireLogin')) {
    // older/alternate naming
    requireLogin();
} else {
    // Ensure session exists and provide a minimal fallback to avoid fatal errors
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!function_exists('require_login')) {
        function require_login() {
            if (!isset($_SESSION['admin_user_id'])) {
                header('Location: login.php');
                exit;
            }
        }
    }
    require_login();
}

// Ensure is_super_admin exists so template checks won't fatal when auth.php was skipped
if (!function_exists('is_super_admin')) {
    function is_super_admin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
    }
}

$current_user = null;
if (function_exists('admin_get_current_user')) {
    $current_user = admin_get_current_user();
} else {
    $temp = @get_current_user();
    if (is_array($temp)) {
        $current_user = $temp;
    }
}
// Provide safe defaults for $current_user to avoid undefined index notices in templates
if (!is_array($current_user)) {
    $current_user = [
        'full_name' => 'Unknown',
        'role' => 'guest'
    ];
}

// Provide a guarded get_dashboard_stats() when auth helpers weren't loaded
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
        try {
            // helper to check table existence
            $tableExists = function($t) use ($pdo) {
                try {
                    $s = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
                    $s->execute([$t]);
                    return ((int)$s->fetchColumn()) > 0;
                } catch (Exception $e) { return false; }
            };

            if ($tableExists('projects_in_progress')) {
                $r = $pdo->query("SELECT COUNT(*) as c FROM projects_in_progress");
                $stats['ongoing_projects'] = (int)($r->fetch()['c'] ?? 0);
            }
            if ($tableExists('completed_projects')) {
                $r = $pdo->query("SELECT COUNT(*) as c FROM completed_projects");
                $stats['completed_projects'] = (int)($r->fetch()['c'] ?? 0);
            }
        } catch (Exception $e) {}
        $stats['total_projects'] = $stats['ongoing_projects'] + $stats['completed_projects'];
        try {
            if ($tableExists('contact_messages')) {
                $r = $pdo->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                $stats['new_inquiries'] = (int)($r->fetch()['c'] ?? 0);
            }
            if ($tableExists('services')) {
                $r = $pdo->query("SELECT COUNT(*) as c FROM services WHERE is_active = 1");
                $stats['active_services'] = (int)($r->fetch()['c'] ?? 0);
            }
            if ($tableExists('user')) {
                $r = $pdo->query("SELECT COUNT(*) as c FROM `user`");
                $stats['total_users'] = (int)($r->fetch()['c'] ?? 0);
            } elseif ($tableExists('users')) {
                $r = $pdo->query("SELECT COUNT(*) as c FROM users");
                $stats['total_users'] = (int)($r->fetch()['c'] ?? 0);
            }
        } catch (Exception $e) {}
        return $stats;
    }
}
$page_title = $page_title ?? 'Admin Panel';
// Include CSRF helper for admin POST protection
$csrf_path = dirname(__FILE__) . '/../../includes/csrf.php';
if (file_exists($csrf_path)) {
    require_once $csrf_path;
}

// Verify CSRF for all incoming admin POST requests (simple central check)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = null;
    // Prefer token from header for AJAX
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (!empty($_POST['_csrf'])) {
        $token = $_POST['_csrf'];
    }
    if (function_exists('csrf_verify')) {
        if (!csrf_verify($token)) {
            http_response_code(403);
            echo 'CSRF verification failed.';
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $favicon_path = '../assets/images/Balmari_Icon.png';
        $favicon_version = file_exists(dirname(__FILE__) . '/../../assets/images/Balmari_Icon.png') ? filemtime(dirname(__FILE__) . '/../../assets/images/Balmari_Icon.png') : time();
    ?>
    <title><?php echo htmlspecialchars($page_title); ?> - Balmari Admin</title>
    <link rel="icon" href="<?php echo $favicon_path . '?v=' . $favicon_version; ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo $favicon_path . '?v=' . $favicon_version; ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo $favicon_path . '?v=' . $favicon_version; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #040D12; /* page background (employee) */
            --bg-secondary: #183D3D; /* nav / sidebar */
            --accent-color: #5C8374; /* primary accent used on employee pages */
            --accent-hover: #715A5A; /* hover accent used on employee pages */
            --text-light: #D3DAD9;
        }
        body {
            background-color: var(--bg-primary);
            color: var(--text-light);
        }
        .sidebar { background-color: var(--bg-secondary); }
        .nav-link { transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active {
            background-color: var(--accent-color);
            color: var(--text-light);
        }
        .btn-primary { background-color: var(--accent-color); color: var(--text-light); }
        .btn-primary:hover { background-color: var(--accent-hover); }
        /* User box and logout button styles to match employee palette */
        .user-box { background-color: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .user-box .label { color: var(--accent-color); font-size: .875rem; }
        .user-box .name { color: var(--text-light); font-weight: 600; }
        .user-box .role { color: var(--accent-color); font-size: .75rem; }
        .logout-btn { display:block; width:100%; padding: .5rem 1rem; border-radius: .5rem; text-align:center; background-color: var(--accent-color); color: var(--text-light); transition: background-color .15s ease; }
        .logout-btn:hover { background-color: var(--accent-hover); }
    </style>
    <?php if (function_exists('csrf_get_token')): ?>
    <script>
        // Inject CSRF token into POST forms automatically
        (function(){
            var token = '<?php echo htmlspecialchars(csrf_get_token(), ENT_QUOTES, "UTF-8"); ?>';
            if (!token) return;
            document.addEventListener('DOMContentLoaded', function(){
                var forms = document.querySelectorAll('form[method="POST"]');
                forms.forEach(function(f){
                    if (!f.querySelector('input[name="_csrf"]')) {
                        var i = document.createElement('input');
                        i.type = 'hidden'; i.name = '_csrf'; i.value = token;
                        f.appendChild(i);
                    }
                });
            });
        })();
    </script>
    <?php endif; ?>
    </head>
    <body class="bg-[#040D12] font-['Inter'] text-[#D3DAD9]">
    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-64 sidebar h-screen p-6 fixed left-0 top-0 overflow-y-auto" style="height:100vh; -webkit-overflow-scrolling:touch;">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-[#D3DAD9]">Balmari</h1>
                <p class="text-sm text-[#5C8374]">Admin Panel</p>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-chart-line mr-2"></i>Dashboard
                </a>
                <a href="projects.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-project-diagram mr-2"></i>Projects
                </a>
                <a href="contact_information.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-briefcase mr-2"></i>Contact Info
                </a>
                <a href="about_us.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-info-circle mr-2"></i>About Page
                </a>
                <a href="manage_carousel.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-images mr-2"></i>Carousel
                </a>
                <a href="inquiries.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-envelope mr-2"></i>Inquiries
                </a>
                
                <?php if (is_super_admin()): ?>
                <div class="my-6 border-t border-[#183D3D]"></div>
                <a href="users.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-users mr-2"></i>Users
                </a>
                <a href="activity-logs.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-history mr-2"></i>Activity Logs
                </a>
                <a href="settings.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9] hover:bg-[#5C8374]">
                    <i class="fas fa-cogs mr-2"></i>Settings
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="mt-12 pt-6 border-t border-[#44444E]">
                <div class="user-box">
                    <p class="label">Logged in as:</p>
                    <p class="name"><?php echo htmlspecialchars($current_user['full_name']); ?></p>
                    <p class="role"><?php echo ucfirst($current_user['role']); ?></p>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="ml-64 flex-1 p-8">
            <!-- Top Header Bar -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-[#D3DAD9]"><?php echo htmlspecialchars($page_title); ?></h2>
                </div>
                <div class="text-right">
                    <p class="text-[#5C8374]"><?php echo date('l, F j, Y'); ?></p>
                </div>
            </div>
