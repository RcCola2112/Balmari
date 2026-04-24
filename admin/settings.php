<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Settings";
require_once 'includes/header.php';
require_login();

// Admin settings: only allow current admin user to edit their own profile and password
$error = '';
$message = '';

$current = null;
if (function_exists('admin_get_current_user')) {
    $current = admin_get_current_user();
}
if (!$current) {
    $error = 'Unable to determine current user.';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    if ($full_name === '') {
        $error = 'Please enter your full name.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE `user` SET full_name = ? WHERE id = ?");
            $stmt->execute([$full_name, $current['id']]);
            log_activity('update', 'user_profile', $current['id'], 'Updated own profile');
            $message = 'Profile updated successfully.';
            $current = admin_get_current_user();
        } catch (Exception $e) {
            $error = 'Failed to update profile.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $error = 'Please fill in all password fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($current_password === $new_password) {
        $error = 'New password must be different from current password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM `user` WHERE id = ?");
            $stmt->execute([$current['id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($current_password, $row['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                $hash = password_hash($new_password, PASSWORD_BCRYPT);
                $u = $pdo->prepare("UPDATE `user` SET password = ? WHERE id = ?");
                $u->execute([$hash, $current['id']]);
                log_activity('update', 'user_password', $current['id'], 'Changed own password');
                $message = 'Password changed successfully.';
            }
        } catch (Exception $e) {
            $error = 'Failed to change password.';
        }
    }
}
?>

            <?php if ($message): ?>
            <div data-flash class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-300">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Form -->
                <form method="POST" class="space-y-6 bg-[#183D3D] rounded-lg p-6">
                    <input type="hidden" name="action" value="update_profile">
                    <h3 class="text-lg font-bold text-[#D3DAD9] mb-4">
                        <i class="fas fa-user mr-2 text-[#5C8374]"></i>My Profile
                    </h3>
                    <div>
                        <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($current['full_name'] ?? ''); ?>" class="w-full px-4 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($current['email'] ?? ''); ?>" readonly class="w-full px-4 py-2 bg-[#0B1A1A] text-[#9AA39F] border border-[#2F4D49] rounded-lg">
                    </div>
                    <button type="submit" class="w-full px-6 py-3 bg-[#5C8374] text-[#D3DAD9] font-semibold rounded-lg hover:bg-[#715A5A] transition">
                        <i class="fas fa-save mr-2"></i>Save Profile
                    </button>
                </form>

                <!-- Password Form -->
                <form method="POST" class="space-y-6 bg-[#183D3D] rounded-lg p-6">
                    <input type="hidden" name="action" value="change_password">
                    <h3 class="text-lg font-bold text-[#D3DAD9] mb-4">
                        <i class="fas fa-key mr-2 text-[#5C8374]"></i>Change Password
                    </h3>
                    <div>
                        <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Current Password</label>
                        <input type="password" name="current_password" class="w-full px-4 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">New Password</label>
                        <input type="password" name="new_password" class="w-full px-4 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="w-full px-4 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                    </div>
                    <button type="submit" class="w-full px-6 py-3 bg-[#5C8374] text-[#D3DAD9] font-semibold rounded-lg hover:bg-[#715A5A] transition">
                        <i class="fas fa-lock mr-2"></i>Change Password
                    </button>
                </form>
            </div>

<?php require_once 'includes/footer.php'; ?>
