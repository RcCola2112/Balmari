<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM `user` WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare('INSERT INTO `user` (role, full_name, email, password) VALUES (?, ?, ?, ?)');
                $ins->execute(['admin', $full_name, $email, $hash]);
                $success = 'Admin account created successfully. You can now log in.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Create Admin Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#040D12] text-[#D3DAD9] flex items-center justify-center">
    <div class="w-full max-w-md bg-[#183D3D] p-8 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Create Admin Account</h1>

        <?php if ($success): ?>
            <div class="mb-4 p-3 rounded bg-green-600 text-white"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 rounded bg-red-700 text-white">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label class="block mb-2">Full name
                <input name="full_name" type="text" class="w-full mt-1 p-2 rounded bg-[#040D12] border border-[#5C8374]" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </label>
            <label class="block mb-2">Email
                <input name="email" type="email" class="w-full mt-1 p-2 rounded bg-[#040D12] border border-[#5C8374]" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </label>
            <label class="block mb-2">Password
                <div class="relative">
                    <input id="password" name="password" type="password" class="w-full mt-1 p-2 rounded bg-[#040D12] border border-[#5C8374]">
                    <button type="button" aria-label="Toggle password visibility" onclick="togglePassword('password', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-[#D3DAD9] opacity-80 hover:opacity-100">
                        <!-- eye open -->
                        <svg id="pw-eye-1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <!-- eye closed (hidden by JS when shown) -->
                        <svg id="pw-eye-1-off" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.969 9.969 0 012.223-3.425M6.1 6.1A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.99 9.99 0 01-4.34 5.177M3 3l18 18"/></svg>
                    </button>
                </div>
            </label>
            <label class="block mb-4">Confirm Password
                <div class="relative">
                    <input id="confirm_password" name="confirm_password" type="password" class="w-full mt-1 p-2 rounded bg-[#040D12] border border-[#5C8374]">
                    <button type="button" aria-label="Toggle confirm password visibility" onclick="togglePassword('confirm_password', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-[#D3DAD9] opacity-80 hover:opacity-100">
                        <svg id="pw-eye-2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg id="pw-eye-2-off" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.969 9.969 0 012.223-3.425M6.1 6.1A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.99 9.99 0 01-4.34 5.177M3 3l18 18"/></svg>
                    </button>
                </div>
            </label>

            <div class="flex items-center justify-between">
                <button type="submit" class="px-4 py-2 rounded bg-[#5C8374] text-[#D3DAD9] hover:bg-[#715A5A]">Create Admin</button>
                <a href="../" class="text-sm text-[#D3DAD9] opacity-80 hover:opacity-100">Back to site</a>
            </div>
        </form>
    </div>
</body>
<script>
function togglePassword(inputId, btn) {
    var inp = document.getElementById(inputId);
    if (!inp) return;
    var isPwd = inp.type === 'password';
    inp.type = isPwd ? 'text' : 'password';
    // toggle svg visibility inside button
    var svgs = btn.querySelectorAll('svg');
    svgs.forEach(function(s){ s.classList.toggle('hidden'); });
}
</script>
</html>
</html>
