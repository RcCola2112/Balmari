<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
// CSRF protection for login form
$csrf_path = __DIR__ . '/csrf.php';
if (file_exists($csrf_path)) {
    require_once $csrf_path;
}

$error_message = '';
$success_message = '';

// Check for redirect error message
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Check for logout success message
if (isset($_SESSION['logout_success'])) {
    $success_message = 'You have been successfully logged out';
    unset($_SESSION['logout_success']);
}

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['admin_user_id'])) {
    header('Location: ../admin/dashboard.php');
    exit();
} elseif (isset($_SESSION['employee_id'])) {
    header('Location: ../employee/dashboard.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token if present
    if (function_exists('csrf_verify')) {
        $token = $_POST['_csrf'] ?? null;
        if (!csrf_verify($token)) {
            $error_message = 'Invalid form submission (CSRF).';
        }
    }
    // Forgot password handler
    if (isset($_POST['forgot_password'])) {
        $forgot_email = isset($_POST['forgot_email']) ? trim($_POST['forgot_email']) : '';
        if (empty($forgot_email)) {
            $error_message = 'Please enter your email address to receive a reset code.';
        } else {
            try {
                global $conn;
                if (!isset($conn) || !$conn) {
                    throw new Exception('Database connection not available');
                }

                // Check user exists
                $stmt = $conn->prepare("SELECT id, email, full_name FROM `user` WHERE email = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('s', $forgot_email);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $user = $res->fetch_assoc();
                    $stmt->close();
                } else {
                    $user = null;
                }

                // Ensure password_resets table exists
                $createSql = "CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    email VARCHAR(255) NOT NULL,
                    code VARCHAR(255) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                $conn->query($createSql);

                // Generate 6-digit code and expiry (15 minutes)
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
                $code_hash = password_hash($code, PASSWORD_DEFAULT);

                // Insert reset record
                $user_id = $user['id'] ?? null;
                if ($user_id !== null) {
                    $ins = $conn->prepare("INSERT INTO password_resets (user_id, email, code, expires_at) VALUES (?, ?, ?, ?)");
                    $ins->bind_param('isss', $user_id, $forgot_email, $code_hash, $expires);
                    $ins->execute();
                    $ins->close();
                } else {
                    $ins = $conn->prepare("INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)");
                    $ins->bind_param('sss', $forgot_email, $code_hash, $expires);
                    $ins->execute();
                    $ins->close();
                }

                // Attempt to send email (best-effort)
                $to = $forgot_email;
                $subject = 'Password reset code';
                $message = "Hello\n\nA password reset was requested for your account.\n\nYour one-time verification code is: " . $code . "\n\nThis code will expire in 15 minutes. If you did not request this, please ignore this email.\n\nThanks,\nBalmari Team";
                $headers = 'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
                           'Reply-To: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
                           'X-Mailer: PHP/' . phpversion();

                // send mail but don't reveal whether email exists
                @mail($to, $subject, $message, $headers);

                $success_message = 'If the email exists in our system, a verification code has been sent.';
            } catch (Exception $e) {
                error_log('Forgot password error: ' . $e->getMessage());
                $error_message = 'Unable to process password reset right now. Please try again later.';
            }
        }
    } else {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields';
    } else {
        $login_result = loginShared($email, $password);
        if ($login_result['success']) {
            // Redirect based on whether session was marked as admin-capable
            if (isAdminSession()) {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../employee/dashboard.php');
            }
            exit();
        }
        $error_message = $login_result['message'];
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/images/Balmari_Icon.png" type="image/png">
    <link rel="shortcut icon" href="../assets/images/Balmari_Icon.png" type="image/png">
    <link rel="apple-touch-icon" href="../assets/images/Balmari_Icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#040D12] text-[#FFFFFF]">
    <hr class="border-t border-[#44444E]/30 my-0">
    <div class="min-h-screen flex items-center justify-center bg-[#040D12]">
        <div class="bg-[#183D3D] p-8 rounded-2xl shadow-xl w-full max-w-md border border-[#5C8374]">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-['Playfair_Display'] font-semibold mb-2">
                    <span class="text-[#FFFFFF]">Balmari</span>
                </h1>
                <p class="text-[#FFFFFF]">Employee Admin Panel</p>
            </div>
            <h2 class="text-2xl font-bold mb-6 text-center font-['Playfair_Display'] text-[#FFFFFF]">Login</h2>
            <!-- Error Message -->
            <?php if ($error_message): ?>
                <div class="bg-[#715A5A]/30 border-l-4 border-[#715A5A] p-4 mb-6 rounded">
                    <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>
            <!-- Login Form -->
            <form method="POST" class="space-y-4">
                            <?php if (function_exists('csrf_input')) echo csrf_input(); ?>
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-[#FFFFFF] mb-2">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-[#44444E] rounded-lg bg-[#040D12] text-[#FFFFFF] placeholder:text-[#FFFFFF]/50 focus:ring-2 focus:ring-[#FFFFFF] focus:border-[#FFFFFF] outline-none transition"
                        placeholder="your@email.com"
                    >
                </div>
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-[#FFFFFF] mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 pr-12 border border-[#44444E] rounded-lg bg-[#040D12] text-[#FFFFFF] placeholder:text-[#FFFFFF]/50 focus:ring-2 focus:ring-[#FFFFFF] focus:border-[#FFFFFF] outline-none transition"
                            placeholder="Enter your password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility()"
                            class="absolute right-3 top-3 text-[#FFFFFF] hover:text-[#FFFFFF] transition"
                            title="Show/Hide Password"
                        >
                            <svg id="eyeIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-[#5C8374] text-[#183D3D] py-3 rounded-lg font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition duration-200 mt-6"
                >
                    Login to Dashboard
                </button>
                <!-- OR separator -->
                <div class="flex items-center my-4">
                    <hr class="flex-1 border-t border-[#44444E]/30">
                    <span class="px-3 text-sm text-[#FFFFFF]/70">or</span>
                    <hr class="flex-1 border-t border-[#44444E]/30">
                </div>
                <!-- Google Sign-in -->
                <div class="text-center">
                    <a href="google_oauth.php" class="inline-flex items-center justify-center gap-3 w-full bg-white text-[#183D3D] py-2 rounded-lg font-semibold hover:brightness-95 transition">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="w-5 h-5">
                        Sign in with Google
                    </a>
                </div>
            </form>
            <div class="mt-6 text-center">
                <a href="reset_password.php" class="text-sm text-[#5C8374] hover:underline">Forgot password?</a>
            </div>
            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="../index.php" class="text-[#FFFFFF] hover:text-[#FFFFFF] transition">
                    ← Back to Website
                </a>
            </div>
        </div>
    </div>

    <!-- Show/Hide Password Toggle Script -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                // Show password - use eye-off icon
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
                eyeIcon.parentElement.title = 'Hide Password';
            } else {
                // Hide password - use eye icon
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                eyeIcon.parentElement.title = 'Show Password';
            }
        }
    </script>
    <!-- Forgot password now handled on reset_password.php -->
</body>
</html>

