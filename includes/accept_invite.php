<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php'; // provides $conn or $pdo? admin uses $pdo; here use PDO if available

// support either $pdo (admin) or $conn (mysqli)
$pdoAvailable = isset($pdo) && $pdo instanceof PDO;
$mysqliAvailable = isset($conn) && $conn instanceof mysqli;

$error = '';
$success = '';
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

if (empty($token)) {
    $error = 'Invalid invitation token.';
}

// On POST, accept invite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'accept' && empty($error)) {
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($full_name) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            if ($pdoAvailable) {
                $stmt = $pdo->prepare("SELECT id, email, token, role, expires_at, used FROM invitations WHERE token = ? LIMIT 1");
                $stmt->execute([$token]);
                $invite = $stmt->fetch();
            } elseif ($mysqliAvailable) {
                $s = $conn->prepare("SELECT id, email, token, role, expires_at, used FROM invitations WHERE token = ? LIMIT 1");
                $s->bind_param('s', $token);
                $s->execute();
                $invite = $s->get_result()->fetch_assoc();
                $s->close();
            } else {
                throw new Exception('No DB available');
            }

            if (!$invite) {
                $error = 'Invitation not found.';
            } elseif ($invite['used']) {
                $error = 'This invitation link has already been used.';
            } else {
                $expires = new DateTime($invite['expires_at']);
                if (new DateTime() > $expires) {
                    $error = 'This invitation has expired.';
                } else {
                    // Insert user
                    $pw_hash = password_hash($password, PASSWORD_BCRYPT);
                    if ($pdoAvailable) {
                        $ins = $pdo->prepare("INSERT INTO `user` (role, full_name, email, password) VALUES (?, ?, ?, ?)");
                        $ins->execute([$invite['role'], $full_name, $invite['email'], $pw_hash]);
                        $user_id = $pdo->lastInsertId();
                        // mark invite used
                        $u = $pdo->prepare("UPDATE invitations SET used = 1 WHERE id = ?");
                        $u->execute([$invite['id']]);
                    } else {
                        $ins = $conn->prepare("INSERT INTO `user` (role, full_name, email, password) VALUES (?, ?, ?, ?)");
                        $ins->bind_param('ssss', $invite['role'], $full_name, $invite['email'], $pw_hash);
                        $ins->execute();
                        $user_id = $conn->insert_id;
                        $u = $conn->prepare("UPDATE invitations SET used = 1 WHERE id = ?");
                        $u->bind_param('i', $invite['id']);
                        $u->execute();
                    }

                    // Log in the new user (session)
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $invite['email'];
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['user_role'] = $invite['role'];
                    $_SESSION['employee_id'] = $user_id;
                    $_SESSION['employee_email'] = $invite['email'];
                    $_SESSION['employee_name'] = $full_name;
                    if (!empty($invite['role']) && strtolower($invite['role']) === 'admin') {
                        $_SESSION['admin_user_id'] = $user_id;
                        $_SESSION['admin_email'] = $invite['email'];
                        $_SESSION['admin_name'] = $full_name;
                        $_SESSION['admin_role'] = $invite['role'];
                    }

                    $success = 'Account created and you are now signed in.';
                    // redirect to proper dashboard
                    if (!empty($invite['role']) && strtolower($invite['role']) === 'admin') {
                        header('Location: ../admin/dashboard.php');
                    } else {
                        header('Location: ../employee/dashboard.php');
                    }
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log('Accept invite error: ' . $e->getMessage());
            $error = 'Unable to accept invitation right now.';
        }
    }
}

// If GET and token present, fetch invite for display
$invite_email = '';
$invite_role = '';
if (empty($error)) {
    try {
        if ($pdoAvailable) {
            $stmt = $pdo->prepare("SELECT id, email, role, expires_at, used FROM invitations WHERE token = ? LIMIT 1");
            $stmt->execute([$token]);
            $inv = $stmt->fetch();
        } elseif ($mysqliAvailable) {
            $s = $conn->prepare("SELECT id, email, role, expires_at, used FROM invitations WHERE token = ? LIMIT 1");
            $s->bind_param('s', $token);
            $s->execute();
            $inv = $s->get_result()->fetch_assoc();
            $s->close();
        } else {
            throw new Exception('No DB available');
        }

        if (!$inv) {
            $error = 'Invitation not found.';
        } elseif ($inv['used']) {
            $error = 'This invitation link has already been used.';
        } else {
            $expires = new DateTime($inv['expires_at']);
            if (new DateTime() > $expires) {
                $error = 'This invitation has expired.';
            } else {
                $invite_email = $inv['email'];
                $invite_role = $inv['role'];
            }
        }
    } catch (Exception $e) {
        error_log('Invite lookup error: ' . $e->getMessage());
        $error = 'Unable to validate invitation.';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Accept Invitation - Balmari</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{background:#040D12;color:#fff}</style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="max-w-md w-full p-8 bg-[#183D3D] rounded-2xl border border-[#5C8374]">
    <h1 class="text-2xl font-semibold mb-4">Accept Invitation</h1>
    <?php if ($error): ?>
        <div class="mb-4 p-3 bg-[#715A5A]/30 rounded"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-4 p-3 bg-[#5C8374]/30 rounded"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!$error): ?>
    <p class="mb-4">You were invited as <strong><?php echo htmlspecialchars($invite_role); ?></strong> for <strong><?php echo htmlspecialchars($invite_email); ?></strong>.</p>
    <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="accept">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div>
            <label class="block text-sm mb-1">Full name</label>
            <input type="text" name="full_name" required class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E]">
        </div>
        <div>
            <label class="block text-sm mb-1">New password</label>
            <input type="password" name="password" required class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E]">
        </div>
        <div>
            <label class="block text-sm mb-1">Confirm password</label>
            <input type="password" name="confirm" required class="w-full px-3 py-2 rounded bg-[#040D12] border border-[#44444E]">
        </div>
        <button type="submit" class="w-full py-2 bg-[#5C8374] rounded font-semibold">Create account</button>
    </form>
    <?php endif; ?>

</div>
</body>
</html>
