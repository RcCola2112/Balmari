<?php
// Google OAuth callback handler: exchange code, get user info, upsert user, create session
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php'; // provides $conn
require_once __DIR__ . '/google_oauth_config.php';

// Basic validation
if (!isset($_GET['code'])) {
    $_SESSION['login_error'] = 'Google sign-in failed (no code).';
    header('Location: login.php');
    exit;
}

// Validate state
if (!isset($_GET['state']) || ($_GET['state'] ?? '') !== ($_SESSION['google_oauth_state'] ?? '')) {
    // Not a match; possible CSRF
    error_log('Google OAuth state mismatch');
    $_SESSION['login_error'] = 'Google sign-in failed.';
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

// Exchange code for tokens
$post = http_build_query([
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
]);

$ch = curl_init(GOOGLE_OAUTH_TOKEN_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$resp = curl_exec($ch);
if ($resp === false) {
    error_log('Google token exchange curl error: ' . curl_error($ch));
    $_SESSION['login_error'] = 'Google sign-in failed (token).';
    header('Location: login.php');
    exit;
}
curl_close($ch);

$data = json_decode($resp, true);
if (empty($data['access_token'])) {
    error_log('Google token response: ' . $resp);
    $_SESSION['login_error'] = 'Google sign-in failed (token response).';
    header('Location: login.php');
    exit;
}

$access_token = $data['access_token'];

// Fetch userinfo
$ch = curl_init(GOOGLE_OAUTH_USERINFO . '?access_token=' . urlencode($access_token));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ui = curl_exec($ch);
if ($ui === false) {
    error_log('Google userinfo curl error: ' . curl_error($ch));
    $_SESSION['login_error'] = 'Google sign-in failed (userinfo).';
    header('Location: login.php');
    exit;
}
curl_close($ch);

$userinfo = json_decode($ui, true);
$email = $userinfo['email'] ?? null;
$name = $userinfo['name'] ?? ($userinfo['given_name'] ?? '');

if (empty($email)) {
    $_SESSION['login_error'] = 'Google sign-in failed (no email).';
    header('Location: login.php');
    exit;
}

try {
    // Find existing user
    $stmt = $conn->prepare("SELECT id, role FROM `user` WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $existing = $res->fetch_assoc();
        $stmt->close();
    } else {
        $existing = null;
    }

    if ($existing) {
        $user_id = $existing['id'];
        $role = $existing['role'];
        // Optionally update full_name if empty
        $u = $conn->prepare("UPDATE `user` SET full_name = ? WHERE id = ? AND (full_name = '' OR full_name IS NULL)");
        if ($u) {
            $u->bind_param('si', $name, $user_id);
            $u->execute();
            $u->close();
        }
    } else {
        // Create a new user with a random password
        $role = 'employee';
        $random_pw = bin2hex(random_bytes(16));
        $pw_hash = password_hash($random_pw, PASSWORD_BCRYPT);
        $ins = $conn->prepare("INSERT INTO `user` (role, full_name, email, password) VALUES (?, ?, ?, ?)");
        if ($ins) {
            $ins->bind_param('ssss', $role, $name, $email, $pw_hash);
            $ins->execute();
            $user_id = $ins->insert_id;
            $ins->close();
        } else {
            throw new Exception('Failed to prepare user insert');
        }
    }

    // Build session similar to loginShared
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = $role;
    $_SESSION['employee_id'] = $user_id;
    $_SESSION['employee_email'] = $email;
    $_SESSION['employee_name'] = $name;
    if (!empty($role) && strtolower($role) === 'admin') {
        $_SESSION['admin_user_id'] = $user_id;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_role'] = $role;
    }

    // Redirect to appropriate dashboard
    if (!empty($role) && strtolower($role) === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../employee/dashboard.php');
    }
    exit;

} catch (Exception $e) {
    error_log('Google callback error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'Google sign-in failed.';
    header('Location: login.php');
    exit;
}

?>
