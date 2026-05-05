<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_get_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_input() {
    $t = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $t . '">';
}

function csrf_verify($token) {
    if (empty($_SESSION['_csrf_token'])) return false;
    if (empty($token)) return false;
    return hash_equals($_SESSION['_csrf_token'], $token);
}

?>