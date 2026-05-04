<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Activity Logs";
require_once 'includes/header.php';
require_login();

// Only super admin
if (!is_super_admin()) {
    die('Access denied.');
}

// Get activity logs
// Default
$logs = [];
$error = '';

// Ensure activity_logs table exists before querying
$has_activity_logs = false;
try {
    $chk = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
    if ($chk && $chk->rowCount() > 0) {
        $has_activity_logs = true;
    }
} catch (Exception $e) {}

if ($has_activity_logs) {
    try {
        // Detect columns so this page works with either old or new activity_logs schema
        $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs'");
        $colStmt->execute();
        $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);

        $has_employee_id = in_array('employee_id', $cols, true);
        $has_user_id = in_array('user_id', $cols, true);
        $has_action = in_array('action', $cols, true);
        $has_event_type = in_array('event_type', $cols, true);
        $has_description = in_array('description', $cols, true);
        $has_message = in_array('message', $cols, true);

        // choose join column for user lookup
        $join_col = $has_employee_id ? 'employee_id' : ($has_user_id ? 'user_id' : null);
        $action_col = $has_action ? 'action' : ($has_event_type ? 'event_type' : null);
        $desc_col = $has_description ? 'description' : ($has_message ? 'message' : null);

        // Build simple query (no JOIN) and normalize user names in PHP
        $sql = 'SELECT al.* FROM activity_logs al ORDER BY al.created_at DESC LIMIT 200';
        $stmt = $pdo->query($sql);
        $logs = $stmt->fetchAll();

        // If activity rows reference a user id column, collect those and fetch full names
        $userIds = [];
        foreach ($logs as $r) {
            if (isset($r['employee_id']) && $r['employee_id']) $userIds[] = $r['employee_id'];
            if (isset($r['user_id']) && $r['user_id']) $userIds[] = $r['user_id'];
        }
        $userMap = [];
        if (!empty($userIds)) {
            $userIds = array_values(array_unique(array_map('intval', $userIds)));
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $uStmt = $pdo->prepare("SELECT id, full_name FROM `user` WHERE id IN ($placeholders)");
            $uStmt->execute($userIds);
            foreach ($uStmt->fetchAll() as $u) {
                $userMap[$u['id']] = $u['full_name'];
            }
        }

        // Normalize fields for display: action, description, full_name
        foreach ($logs as &$r) {
            if (empty($r['full_name'])) {
                $uid = $r['employee_id'] ?? ($r['user_id'] ?? null);
                $r['full_name'] = $uid ? ($userMap[$uid] ?? 'Unknown') : ($r['full_name'] ?? 'Unknown');
            }
            if (empty($r['action']) && !empty($r['event_type'])) {
                $r['action'] = $r['event_type'];
            }
            if (empty($r['description']) && !empty($r['message'])) {
                $r['description'] = $r['message'];
            }
        }
        unset($r);
    } catch (PDOException $e) {
        $error = 'Error loading activity logs.';
    }
} else {
    $error = 'Activity logs unavailable: activity_logs table is not present in the database.';
}

// Count by action/event_type
$action_stats = [];
try {
    if (!empty($has_activity_logs) && !empty($action_col)) {
        $stmt = $pdo->query("SELECT " . $action_col . " as action, COUNT(*) as count FROM activity_logs GROUP BY " . $action_col . " ORDER BY count DESC");
        foreach ($stmt->fetchAll() as $row) {
            $action_stats[$row['action']] = $row['count'];
        }
    }
} catch (PDOException $e) {}
?>

            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-[#183D3D] p-4 rounded-lg">
                    <p class="text-[#5C8374] text-sm">Total Logs</p>
                    <p class="text-2xl font-bold text-[#D3DAD9]"><?php echo count($logs); ?></p>
                </div>
                <div class="bg-[#183D3D] p-4 rounded-lg">
                    <p class="text-[#5C8374] text-sm">Unique Actions</p>
                    <p class="text-2xl font-bold text-[#D3DAD9]"><?php echo count($action_stats); ?></p>
                </div>
                <div class="bg-[#183D3D] p-4 rounded-lg">
                    <p class="text-[#5C8374] text-sm">Last 24h Activity</p>
                    <p class="text-2xl font-bold text-[#D3DAD9]">
                        <?php 
                        $count = 0;
                        foreach ($logs as $log) {
                            if (strtotime($log['created_at']) > strtotime('-24 hours')) {
                                $count++;
                            }
                        }
                        echo $count;
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Activity Table -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden">
                <div class="p-6 border-b border-[#5C8374]/30">
                    <h3 class="text-lg font-bold text-[#D3DAD9]">
                        <i class="fas fa-history mr-2 text-[#5C8374]"></i>Recent Activity
                    </h3>
                </div>
                
                <?php if (count($logs) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#5C8374]/20 border-b border-[#5C8374]/40 shadow-sm">
                                <th class="px-6 py-3 text-left text-[#D3DAD9] uppercase tracking-wide text-xs font-semibold">User</th>
                                <th class="px-6 py-3 text-left text-[#D3DAD9] uppercase tracking-wide text-xs font-semibold">Action</th>
                                <th class="px-6 py-3 text-left text-[#D3DAD9] uppercase tracking-wide text-xs font-semibold">Type</th>
                                <th class="px-6 py-3 text-left text-[#D3DAD9] uppercase tracking-wide text-xs font-semibold">Description</th>
                                <th class="px-6 py-3 text-left text-[#D3DAD9] uppercase tracking-wide text-xs font-semibold">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr class="border-t border-[#5C8374]/30 hover:bg-[#040D12]">
                                <td class="px-6 py-4 text-[#D3DAD9]">
                                    <i class="fas fa-user mr-2 text-[#5C8374]"></i>
                                    <?php echo htmlspecialchars($log['full_name'] ?? 'Unknown'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-[#5C8374]/20 text-[#D3DAD9] rounded text-xs">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[#5C8374]">
                                    <?php echo htmlspecialchars($log['entity_type'] ?? 'system'); ?>
                                </td>
                                <td class="px-6 py-4 text-[#5C8374]">
                                    <?php echo htmlspecialchars($log['description'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 text-[#5C8374] text-xs">
                                    <?php echo date('M j, Y g:ia', strtotime($log['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-6 text-center text-[#715A5A]">
                    <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                    <p>No activity logged yet.</p>
                </div>
                <?php endif; ?>
            </div>

<?php require_once 'includes/footer.php'; ?>
