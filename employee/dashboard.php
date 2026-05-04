<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Dashboard";
require_once '../includes/db.php';
require_once 'includes/auth.php';

requireLogin();

// Get statistics
$carousel_count = 0;
$db_error = false;

try {
    $carousel_dir = realpath(__DIR__ . '/../assets/images/carousel');
    if ($carousel_dir && is_dir($carousel_dir)) {
        $carousel_files = glob($carousel_dir . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
        $carousel_count = is_array($carousel_files) ? count($carousel_files) : 0;
    }

    if ($carousel_count === 0 && isset($conn) && $conn) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM carousel");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $carousel_count = (int)($row['count'] ?? 0);
            $stmt->close();
        }
    }

    // Only treat it as a warning if we still could not obtain a count.
    if ($carousel_count === 0) {
        $db_error = true;
    }
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $db_error = true;
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if ($db_error): ?>
    <!-- Database Error Warning -->
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <span class="text-2xl">⚠️</span>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Database Connection Issue</h3>
                <p class="mt-1 text-sm text-yellow-700">Some statistics may not be available. Basic functionality is still accessible.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Welcome Section -->
    <div class="mb-12 bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#5C8374] text-[#D3DAD9]">
        <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#FFFFFF] mb-2">
            Welcome back, <?php echo htmlspecialchars($_SESSION['employee_name'] ?? 'Employee'); ?>!
        </h1>
        <p class="text-[#5C8374]">Manage your Balmari website content from here</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <!-- Carousel Items -->
        <div class="bg-[#183D3D] rounded-lg shadow p-6 border-l-4 border-[#5C8374]">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-[#D3DAD9] text-sm">Carousel Items</p>
                    <h3 class="text-3xl font-bold text-[#5C8374] mt-2"><?php echo $carousel_count; ?></h3>
                </div>
                <div class="text-4xl text-orange-500 opacity-20">
                    📸
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-[#183D3D] rounded-lg shadow p-6 border-l-4 border-[#715A5A]">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-[#D3DAD9] text-sm">Quick Actions</p>
                    <h3 class="text-3xl font-bold text-[#715A5A] mt-2">4</h3>
                    <p class="text-xs text-[#D3DAD9]/80 mt-1">Available tasks</p>
                </div>
                <div class="text-4xl text-blue-500 opacity-20">
                    ⚡
                </div>
            </div>
        </div>
        
        <!-- Account -->
        <div class="bg-[#183D3D] rounded-lg shadow p-6 border-l-4 border-[#5C8374]">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-[#D3DAD9] text-sm">Your Account</p>
                    <h3 class="text-lg font-bold text-[#5C8374] mt-2"><?php echo htmlspecialchars($_SESSION['employee_email']); ?></h3>
                    <p class="text-xs text-[#D3DAD9]/80 mt-1">Logged in</p>
                </div>
                <div class="text-4xl text-green-500 opacity-20">
                    👤
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#5C8374] text-[#D3DAD9]">
        <h2 class="text-2xl font-bold text-[#5C8374] mb-6">Quick Actions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Upload New Carousel -->
            <a href="manage_carousel.php" class="p-6 bg-gradient-to-br from-[#183D3D] to-[#5C8374]/30 rounded-lg hover:shadow-lg transition cursor-pointer border border-[#5C8374] text-[#D3DAD9]">
                <div class="text-3xl mb-3">📤</div>
                <h3 class="font-bold text-[#FFFFFF] mb-1">Upload Image</h3>
                <p class="text-sm text-[#5C8374]">Add new carousel image</p>
            </a>
            
            <!-- View All Carousel -->
            <a href="manage_carousel.php" class="p-6 bg-gradient-to-br from-[#183D3D] to-[#5C8374]/30 rounded-lg hover:shadow-lg transition cursor-pointer border border-[#5C8374] text-[#D3DAD9]">
                <div class="text-3xl mb-3">📋</div>
                <h3 class="font-bold text-[#FFFFFF] mb-1">View All</h3>
                <p class="text-sm text-[#5C8374]">See all carousel items</p>
            </a>
            
            <!-- Documentation -->
            <a href="#" class="p-6 bg-gradient-to-br from-[#183D3D] to-[#5C8374]/30 rounded-lg hover:shadow-lg transition cursor-pointer border border-[#715A5A] text-[#D3DAD9]">
                <div class="text-3xl mb-3">📖</div>
                <h3 class="font-bold text-[#FFFFFF] mb-1">Help</h3>
                <p class="text-sm text-[#5C8374]">View documentation</p>
            </a>
            
            <!-- Logout -->
            <a href="logout.php" class="p-6 bg-gradient-to-br from-[#183D3D] to-[#715A5A]/30 rounded-lg hover:shadow-lg transition cursor-pointer border border-[#715A5A] text-[#D3DAD9]">
                <div class="text-3xl mb-3">🚪</div>
                <h3 class="font-bold text-[#FFFFFF] mb-1">Logout</h3>
                <p class="text-sm text-[#5C8374]">Exit admin panel</p>
            </a>
        </div>
    </div>
    
    <!-- Info Box -->
    <div class="mt-8 bg-[#183D3D] border-l-4 border-[#5C8374] p-6 rounded">
        <h3 class="font-bold text-[#5C8374] mb-2">About This Panel</h3>
        <p class="text-[#D3DAD9] text-sm mb-3">
            This admin panel allows you to manage the carousel on your website homepage. Upload, edit, and delete carousel images directly from here. Changes appear immediately on the live website.
        </p>
        <p class="text-[#715A5A] text-xs">
            Need help? Contact your website administrator.
        </p>
    </div>
</div>

<body class="bg-[#040D12] text-[#D3DAD9]">
    <div class="max-w-4xl mx-auto mt-12">
        <h1 class="text-3xl font-bold mb-6 font-['Playfair_Display'] text-[#5C8374]">Welcome to the Employee Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

