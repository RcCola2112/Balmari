<?php
// Simple file-backed login throttle. Stores attempts in a JSON file in system temp dir.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function _login_throttle_store_path() {
    $dir = sys_get_temp_dir();
    return $dir . DIRECTORY_SEPARATOR . 'balmari_login_attempts.json';
}

function _login_throttle_load() {
    $path = _login_throttle_store_path();
    if (!file_exists($path)) return [];
    $json = @file_get_contents($path);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function _login_throttle_save($data) {
    $path = _login_throttle_store_path();
    @file_put_contents($path, json_encode($data));
}

function record_failed_login($key) {
    $data = _login_throttle_load();
    $now = time();
    if (!isset($data[$key])) {
        $data[$key] = ['attempts' => 1, 'first_failed' => $now, 'locked_until' => 0];
    } else {
        $data[$key]['attempts'] = ($data[$key]['attempts'] ?? 0) + 1;
    }
    // Lock if attempts exceed MAX_LOGIN_ATTEMPTS
    $max = defined('MAX_LOGIN_ATTEMPTS') ? MAX_LOGIN_ATTEMPTS : 5;
    $lock_duration = defined('LOCKOUT_DURATION') ? LOCKOUT_DURATION : 900;
    if (($data[$key]['attempts'] ?? 0) >= $max) {
        $data[$key]['locked_until'] = $now + $lock_duration;
        $data[$key]['attempts'] = 0; // reset attempts after locking
    }
    _login_throttle_save($data);
}

function reset_login_attempts($key) {
    $data = _login_throttle_load();
    if (isset($data[$key])) {
        unset($data[$key]);
        _login_throttle_save($data);
    }
}

function is_locked_out($key) {
    $data = _login_throttle_load();
    if (empty($data[$key])) return false;
    $now = time();
    $locked_until = (int)($data[$key]['locked_until'] ?? 0);
    if ($locked_until > $now) return $locked_until - $now;
    // expired lock
    if ($locked_until && $locked_until <= $now) {
        unset($data[$key]);
        _login_throttle_save($data);
    }
    return false;
}

?>