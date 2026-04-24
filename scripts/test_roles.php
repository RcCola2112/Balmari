<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Quick test to list users and demonstrate admin vs employee role handling
require_once __DIR__ . '/../includes/db_pdo.php'; // provides $pdo

try {
    $stmt = $pdo->query("SELECT id, full_name, email, role, created_at FROM `user` ORDER BY id LIMIT 50");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "No users found in `user` table.\n";
        exit(0);
    }

    foreach ($rows as $r) {
        $role = $r['role'] ?? '';
        $type = ($role === 'admin') ? 'ADMIN' : 'EMPLOYEE';
        echo sprintf("%3d | %-8s | %-20s | %s\n", $r['id'], $type, $r['email'], $r['full_name']);
    }
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nTest complete. Users with role 'admin' are treated as admin-capable.\n";
