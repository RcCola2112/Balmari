<?php
// Google OAuth configuration. Replace the placeholders with values
// from Google Cloud Console or set corresponding environment variables.

if (session_status() === PHP_SESSION_NONE) session_start();

// Allow overrides from environment for easier deployment
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '411570532551-0uvm7pmb6qt1ee8nbeqmbgc3fkqi7rdd.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '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');

// Build a sensible redirect URI if not provided via env
$default_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: ($default_scheme . '://' . $host . '/includes/google_callback.php'));

// Scopes we request
define('GOOGLE_OAUTH_SCOPE', 'openid email profile');

// Token endpoint and endpoints
define('GOOGLE_OAUTH_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_OAUTH_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_OAUTH_USERINFO', 'https://www.googleapis.com/oauth2/v3/userinfo');

?>
