<?php
$page_title = "Users & Roles";
require_once 'includes/header.php';
require_login();

// Only admin can access this page
if (!is_super_admin()) {
    die('Access denied.');
}

$error = '';
$message = '';
$redirect_to = null;
$redirect_delay = 0; // milliseconds

// Get all users from `user` table
$users = [];
try {
    $stmt = $pdo->query("SELECT id, full_name, email, role, created_at FROM `user` ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading users: ' . $e->getMessage();
}

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'employee';

    if (empty($full_name) || empty($email)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Ensure the email domain appears to accept mail (MX or A record)
        $domain = substr(strrchr($email, "@"), 1);
        if ($domain) {
            $hasMail = false;
            // checkdnsrr may not be available on some Windows configs; fall back to gethostbynamel
            if (function_exists('checkdnsrr')) {
                $hasMail = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
            } else {
                $hosts = gethostbynamel($domain);
                $hasMail = !empty($hosts);
            }
            if (!$hasMail) {
                $error = 'Email domain does not appear to accept mail.';
            }
        } else {
            $error = 'Invalid email address.';
        }
    }

    if (empty($error)) {
        try {
            // Create invitations table if missing
            $pdo->exec("CREATE TABLE IF NOT EXISTS invitations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'employee',
                expires_at DATETIME NOT NULL,
                used TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Generate one-time token
            $token = bin2hex(random_bytes(16));
            $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

            $ins = $pdo->prepare("INSERT INTO invitations (email, token, role, expires_at) VALUES (?, ?, ?, ?)");
            $ins->execute([$email, $token, $role, $expires]);

            // Send invitation email with one-time link
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $link = $scheme . '://' . $host . '/includes/accept_invite.php?token=' . urlencode($token);
            $to = $email;
            $subject = 'You are invited to Balmari Admin/Employee panel';
            $message = "Hello\n\nYou have been invited to join Balmari as a $role.\nClick the link below to accept the invitation and set your password (one-time use):\n\n" . $link . "\n\nThis link expires in 15 minutes (on " . $expires . ").\n\nIf you did not expect this, please ignore this email.";
            $headers = 'From: no-reply@' . $host . "\r\n" . 'Reply-To: no-reply@' . $host . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            @mail($to, $subject, $message, $headers);

            log_activity('invite', 'user', null, "Invitation sent to $email with role $role");
            $message = 'Invitation sent if the email is valid.';
            $redirect_to = 'users.php';
            $redirect_delay = 2000;
        } catch (PDOException $e) {
            $error = 'Error creating invitation: ' . $e->getMessage();
        }
    }
}

// Handle update role
if (isset($_GET['update_role']) && $_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['update_role'] !== $_SESSION['admin_user_id']) {
    $user_id = intval($_GET['update_role']);
    $new_role = $_POST['role'] ?? 'employee';
    
    try {
        $stmt = $pdo->prepare("UPDATE `user` SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $user_id]);
        log_activity('update', 'user', $user_id, "Role changed to $new_role");
        $message = 'Role updated successfully.';
        $redirect_to = 'users.php';
        $redirect_delay = 1000;
    } catch (PDOException $e) {
        $error = 'Error updating role: ' . $e->getMessage();
    }
}

// Toggle status is not supported by current `user` schema (no status column)
if (isset($_GET['toggle_status']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = 'Status toggling is not supported by the current database schema.';
}
?>

            <?php if ($message): ?>
            <div data-flash class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-300">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
            </div>
            <?php if ($redirect_to): ?>
            <script>
                setTimeout(function(){ window.location.href = <?php echo json_encode($redirect_to); ?>; }, <?php echo (int)$redirect_delay; ?>);
            </script>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Add User Form -->
                <div class="bg-[#183D3D] rounded-lg p-6">
                    <h3 class="text-lg font-bold text-[#D3DAD9] mb-4">
                        <i class="fas fa-user-plus mr-2 text-[#5C8374]"></i>Add User
                    </h3>
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="action" value="add">
                        
                        <div>
                            <label class="block text-[#D3DAD9] text-sm font-semibold mb-1">Full Name</label>
                            <input type="text" name="full_name" required class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5C8374]">
                        </div>
                        
                        <div>
                            <label class="block text-[#D3DAD9] text-sm font-semibold mb-1">Email</label>
                            <input type="email" name="email" required class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-[#D3DAD9] text-sm font-semibold mb-1">Role</label>
                            <select name="role" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full px-4 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition">
                            <i class="fas fa-paper-plane mr-2"></i>Send Invitation
                        </button>
                    </form>
                </div>
                
                <!-- Users List -->
                <div class="lg:col-span-2">
                    <div class="bg-[#183D3D] rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-[#5C8374]/30">
                            <h3 class="text-lg font-bold text-[#D3DAD9]">
                                <i class="fas fa-users mr-2 text-[#5C8374]"></i>Team Members (<?php echo count($users); ?>)
                            </h3>
                        </div>
                        
                        <?php if (count($users) > 0): ?>
                        <div class="space-y-2">
                            <?php foreach ($users as $user): ?>
                            <div class="p-4 border-b border-[#5C8374]/20 hover:bg-[#040D12] transition">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[#D3DAD9]"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                        <p class="text-sm text-[#5C8374]">
                                            <i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($user['email']); ?>
                                        </p>
                                        <p class="text-xs text-[#5C8374] mt-1">
                                            Role: <span class="font-semibold"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>
                                            | Created: <?php echo $user['created_at'] ? date('M j, Y', strtotime($user['created_at'])) : 'Unknown'; ?>
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <?php if ($user['id'] !== $_SESSION['admin_user_id']): ?>
                                        <span class="px-3 py-1 bg-[#5C8374]/20 text-[#5C8374] rounded text-sm">Manage</span>
                                        <?php else: ?>
                                        <span class="px-3 py-1 bg-green-500/20 text-green-300 rounded text-sm">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="p-6 text-center text-[#715A5A]">
                            <p>No users found.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

<?php require_once 'includes/footer.php'; ?>
