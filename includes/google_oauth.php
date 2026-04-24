<?php
// Redirects user to Google's OAuth2 authorization endpoint
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/google_oauth_config.php';

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => GOOGLE_OAUTH_SCOPE,
    'access_type' => 'offline',
    'prompt' => 'select_account',
    'state' => $state
]);

header('Location: ' . GOOGLE_OAUTH_AUTH_URL . '?' . $params);
exit;

?>
