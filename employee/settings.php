<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Settings";
require_once '../includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$error_message = '';
$success_message = '';
$employee = getCurrentEmployee();

// Handle profile update
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';

    if (empty($full_name)) {
        $error_message = 'Please enter your full name';
    } else {
        $result = updateEmployeeProfile($employee['id'], $full_name);
        if ($result['success']) {
            $success_message = $result['message'];
            $employee = getCurrentEmployee(); // Refresh employee data
        } else {
            $error_message = $result['message'];
        }
    }
}

// Handle password change
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'Please fill in all password fields';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'New password must be at least 6 characters';
    } elseif ($current_password === $new_password) {
        $error_message = 'New password must be different from current password';
    } else {
        $result = updateEmployeePassword($employee['id'], $current_password, $new_password);
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#FFFFFF] mb-2">
            Settings
        </h1>
        <p class="text-[#5C8374]">Manage your account and preferences</p>
    </div>

    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error_message): ?>
        <div class="bg-[#715A5A]/30 border-l-4 border-[#715A5A] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Settings Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Account Information Card -->
        <div class="bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#5C8374] text-[#D3DAD9]">
            <h2 class="text-2xl font-semibold text-[#FFFFFF] mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Account Information
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#5C8374] mb-2">Email</label>
                    <div class="bg-[#040D12] px-4 py-2 rounded-lg border border-[#44444E]/30 text-[#D3DAD9]">
                        <?php echo htmlspecialchars($employee['email']); ?>
                    </div>
                    <p class="text-xs text-[#5C8374]/70 mt-2">Email cannot be changed</p>
                </div>
            </div>
        </div>

        <!-- Update Profile Card -->
        <div class="bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#5C8374] text-[#D3DAD9]">
            <h2 class="text-2xl font-semibold text-[#FFFFFF] mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </h2>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="action" value="update_profile">
                
                <div>
                    <label for="full_name" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        value="<?php echo htmlspecialchars($employee['full_name']); ?>"
                        required
                        class="w-full px-4 py-2 rounded-lg bg-[#040D12] border border-[#44444E] text-[#D3DAD9] focus:outline-none focus:border-[#5C8374] placeholder-[#5C8374]/40"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-[#5C8374] text-[#183D3D] py-2 rounded-lg font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition"
                >
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#5C8374] text-[#D3DAD9] lg:col-span-2">
            <h2 class="text-2xl font-semibold text-[#FFFFFF] mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Change Password
            </h2>

            <form method="POST" action="" class="space-y-4 max-w-md">
                <input type="hidden" name="action" value="change_password">
                
                <div>
                    <label for="current_password" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Current Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            required
                            class="w-full px-4 py-2 rounded-lg bg-[#040D12] border border-[#44444E] text-[#D3DAD9] focus:outline-none focus:border-[#5C8374] placeholder-[#5C8374]/40"
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#5C8374] hover:text-[#D3DAD9] transition"
                            onclick="togglePasswordVisibility('current_password')"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-[#5C8374] mb-2">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            required
                            placeholder="At least 6 characters"
                            class="w-full px-4 py-2 rounded-lg bg-[#040D12] border border-[#44444E] text-[#D3DAD9] focus:outline-none focus:border-[#5C8374] placeholder-[#5C8374]/40"
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#5C8374] hover:text-[#D3DAD9] transition"
                            onclick="togglePasswordVisibility('new_password')"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-[#5C8374] mb-2">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            class="w-full px-4 py-2 rounded-lg bg-[#040D12] border border-[#44444E] text-[#D3DAD9] focus:outline-none focus:border-[#5C8374] placeholder-[#5C8374]/40"
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#5C8374] hover:text-[#D3DAD9] transition"
                            onclick="togglePasswordVisibility('confirm_password')"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-[#715A5A] text-[#FFFFFF] py-2 rounded-lg font-semibold hover:bg-[#5C8374] hover:text-[#183D3D] transition"
                >
                    Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}
</script>

</body>
</html>

