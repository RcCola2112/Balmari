<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db.php'; // provides $conn (mysqli)

$error = '';
$success = '';
// Informational message for code send
$info = '';

// Handle request to send verification code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_code') {
    $req_email = trim($_POST['email'] ?? '');
    if (empty($req_email)) {
        $info = 'Please provide an email address.';
    } else {
        try {
            if (!isset($conn) || !$conn) throw new Exception('Database connection missing');

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

            // Generate code and store hashed
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
            $code_hash = password_hash($code, PASSWORD_DEFAULT);

            // Try to find user id (may be null)
            $user_id = null;
            $s = $conn->prepare("SELECT id FROM `user` WHERE email = ? LIMIT 1");
            if ($s) {
                $s->bind_param('s', $req_email);
                $s->execute();
                $res = $s->get_result();
                if ($res->num_rows > 0) {
                    $u = $res->fetch_assoc();
                    $user_id = $u['id'];
                }
                $s->close();
            }

            if ($user_id !== null) {
                $ins = $conn->prepare("INSERT INTO password_resets (user_id, email, code, expires_at) VALUES (?, ?, ?, ?)");
                $ins->bind_param('isss', $user_id, $req_email, $code_hash, $expires);
            } else {
                $ins = $conn->prepare("INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)");
                $ins->bind_param('sss', $req_email, $code_hash, $expires);
            }
            $ins->execute();
            $ins->close();

            // Send email (best-effort)
            $to = $req_email;
            $subject = 'Your Balmari password reset code';
            $message = "Hello,\n\nUse this code to reset your password: " . $code . "\nIt expires in 15 minutes.\n\nIf you did not request this, ignore this message.";
            $headers = 'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
                       'Reply-To: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            @mail($to, $subject, $message, $headers);

            // Generic info message to avoid enumeration
            $info = 'If the email exists, a verification code was sent.';
        } catch (Exception $e) {
            error_log('Request code error: ' . $e->getMessage());
            $info = 'Unable to send code right now.';
        }
    }
}

// Handle reset submission: expects email, code, new_password, confirm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($code) || empty($new_password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            if (!isset($conn) || !$conn) throw new Exception('Database connection missing');

            // Find the most recent unused reset request for this email
            $stmt = $conn->prepare("SELECT id, user_id, code, expires_at, used FROM password_resets WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
            if (!$stmt) throw new Exception('Failed to prepare statement');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                $stmt->close();
                $error = 'Invalid code or email.';
            } else {
                $row = $res->fetch_assoc();
                $stmt->close();

                // Check expiry
                $now = new DateTime();
                $expires = new DateTime($row['expires_at']);
                if ($now > $expires) {
                    $error = 'The verification code has expired. Please request a new one.';
                } else {
                    // Verify code against hashed code in DB
                    if (!password_verify($code, $row['code'])) {
                        $error = 'Invalid verification code.';
                    } else {
                        // Update user's password
                        $hash = password_hash($new_password, PASSWORD_BCRYPT);
                        $updated = false;
                        if (!empty($row['user_id'])) {
                            $uStmt = $conn->prepare("UPDATE `user` SET password = ? WHERE id = ?");
                            if ($uStmt) {
                                $uStmt->bind_param('si', $hash, $row['user_id']);
                                $uStmt->execute();
                                $updated = $uStmt->affected_rows >= 0;
                                $uStmt->close();
                            }
                        } else {
                            $uStmt = $conn->prepare("UPDATE `user` SET password = ? WHERE email = ?");
                            if ($uStmt) {
                                $uStmt->bind_param('ss', $hash, $email);
                                $uStmt->execute();
                                $updated = $uStmt->affected_rows >= 0;
                                $uStmt->close();
                            }
                        }

                        if ($updated) {
                            // Mark this reset row used
                            $mStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                            if ($mStmt) {
                                $mStmt->bind_param('i', $row['id']);
                                $mStmt->execute();
                                $mStmt->close();
                            }

                            // Optionally mark other pending reset rows for this email used
                            $conn->query("UPDATE password_resets SET used = 1 WHERE email = '" . $conn->real_escape_string($email) . "' AND used = 0");

                            $success = 'Password updated successfully. You may now log in.';
                            // redirect to login page in same folder
                            header('Location: login.php?reset=1');
                            exit;
                        } else {
                            $error = 'Failed to update password.';
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Reset password error: ' . $e->getMessage());
            $error = 'Unable to reset password right now.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset Password - Balmari</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#040D12;color:#fff}</style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full p-8 bg-[#183D3D] rounded-2xl border border-[#5C8374]">
        <h1 class="text-2xl font-semibold mb-4">Reset Password</h1>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-[#715A5A]/30 rounded"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-[#5C8374]/30 rounded"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($info): ?>
            <div class="mb-4 p-3 bg-[#5C8374]/20 rounded"><?php echo htmlspecialchars($info); ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E]" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
                <div class="mt-2">
                    <button type="submit" name="action" value="request_code" class="mt-1 px-3 py-1 bg-[#3B6E65] rounded text-sm">Send Verification Code</button>
                </div>
            </div>
        </form>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
            <div>
                <label class="block text-sm mb-1">Verification Code</label>
                <input type="text" name="code" class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E]" placeholder="6-digit code">
            </div>
            <div>
                <label class="block text-sm mb-1">New Password</label>
                <div class="relative">
                    <input id="new_password" type="password" name="new_password" class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E] pr-10">
                    <button type="button" id="toggle_new" aria-label="Show password" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300"> 
                        <!-- eye icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm mb-1">Confirm Password</label>
                <div class="relative">
                    <input id="confirm_password" type="password" name="confirm_password" class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E] pr-10">
                    <button type="button" id="toggle_confirm" aria-label="Show password" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300">
                        <!-- eye icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            <button type="submit" name="action" value="reset" class="w-full py-2 bg-[#5C8374] rounded font-semibold">Set New Password</button>
        </form>

        <div class="mt-4 text-sm">
            <a href="login.php" class="text-[#5C8374]">← Back to Login</a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            function makeToggle(inputId, btnId){
                var input = document.getElementById(inputId);
                var btn = document.getElementById(btnId);
                if (!input || !btn) return;
                var eye = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                var eyeOff = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.75 21.75 0 0 1 5-4.94"></path><path d="M3 3l18 18"></path><path d="M9.53 9.53A3.5 3.5 0 0 0 14.47 14.47"></path></svg>';
                btn.addEventListener('click', function(){
                    if (input.type === 'password'){
                        input.type = 'text';
                        btn.innerHTML = eyeOff;
                        btn.setAttribute('aria-label', 'Hide password');
                    } else {
                        input.type = 'password';
                        btn.innerHTML = eye;
                        btn.setAttribute('aria-label', 'Show password');
                    }
                });
            }
            makeToggle('new_password','toggle_new');
            makeToggle('confirm_password','toggle_confirm');
        });
    </script>
</body>
</html>
