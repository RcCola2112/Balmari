<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once 'includes/auth.php';

$error_message = '';
$success_message = '';

// If already logged in, redirect to dashboard
if (isEmployeeLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters';
    } else {
        try {
            // Check if email already exists (use `user` table)
            $check_stmt = $conn->prepare("SELECT id FROM `user` WHERE email = ? LIMIT 1");
            if (!$check_stmt) {
                $error_message = 'Database error. Please try again.';
            } else {
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error_message = 'An account with this email already exists';
                } else {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    // Insert into `user` table and set role to 'employee'
                    $insert_stmt = $conn->prepare("INSERT INTO `user` (role, full_name, email, password) VALUES ('employee', ?, ?, ?)");

                    if (!$insert_stmt) {
                        $error_message = 'Database error. Please try again.';
                    } else {
                        $insert_stmt->bind_param("sss", $full_name, $email, $password_hash);
                        if ($insert_stmt->execute()) {
                            $success_message = 'Account created successfully! You can now log in.';
                        } else {
                            $error_message = 'Failed to create account. Please try again.';
                        }
                        $insert_stmt->close();
                    }
                }

                $check_stmt->close();
            }
        } catch (Exception $e) {
            error_log("Signup error: " . $e->getMessage());
            $error_message = 'An unexpected error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Signup - Balmari Admin Panel</title>
    <link rel="icon" href="../assets/images/Balmari_Icon.png" type="image/png">
    <link rel="shortcut icon" href="../assets/images/Balmari_Icon.png" type="image/png">
    <link rel="apple-touch-icon" href="../assets/images/Balmari_Icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#040D12] text-[#D3DAD9]">
    <section class="bg-[#183D3D] text-[#D3DAD9] py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4 text-center">Employee Signup</h1>
        </div>
    </section>
    <hr class="border-t border-[#44444E]/30 my-0">
    <div class="min-h-screen flex items-center justify-center bg-[#040D12]">
        <div class="bg-[#183D3D] p-8 rounded-2xl shadow-xl w-full max-w-md border border-[#5C8374]">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-['Playfair_Display'] font-semibold mb-2">
                    <span class="text-[#5C8374]">Balmari</span>
                </h1>
                <p class="text-[#5C8374]/80">Employee Admin Panel</p>
            </div>

            <h2 class="text-2xl font-bold mb-6 text-center font-['Playfair_Display'] text-[#FFFFFF]">Create Account</h2>

            <!-- Error Message -->
            <?php if ($error_message): ?>
                <div class="bg-[#715A5A]/30 border-l-4 border-[#715A5A] p-4 mb-6 rounded">
                    <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success_message): ?>
                <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
                    <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($success_message); ?></p>
                    <p class="text-[#5C8374]/80 text-sm mt-2">
                        <a href="login.php" class="underline hover:no-underline text-[#5C8374]">Go to Login</a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Signup Form -->
            <form method="POST" class="space-y-4">
                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required
                        class="w-full px-4 py-3 border border-[#44444E] rounded-lg bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                        placeholder="Your full name"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-[#44444E] rounded-lg bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                        placeholder="your@email.com"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 pr-12 border border-[#44444E] rounded-lg bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                            placeholder="At least 6 characters"
                        >
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility('password', 'eyeIconPassword')"
                            class="absolute right-3 top-3 text-[#5C8374] hover:text-[#D3DAD9] transition"
                            title="Show/Hide Password"
                        >
                            <svg id="eyeIconPassword" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            class="w-full px-4 py-3 pr-12 border border-[#44444E] rounded-lg bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                            placeholder="Re-enter your password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility('confirm_password', 'eyeIconConfirm')"
                            class="absolute right-3 top-3 text-[#5C8374] hover:text-[#D3DAD9] transition"
                            title="Show/Hide Password"
                        >
                            <svg id="eyeIconConfirm" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    Create Account
                </button>
            </form>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="login.php" class="text-[#5C8374]/80 hover:text-[#715A5A] transition">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>

