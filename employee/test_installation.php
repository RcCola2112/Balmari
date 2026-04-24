<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Employee Admin Panel - Installation Test
 * 
 * This file helps verify that the employee admin panel
 * is installed and configured correctly.
 * 
 * Access: https://yourdomain.com/employee/test_installation.php
 * 
 * NOTE: Delete this file after testing in production!
 */

// Start session
session_start();

// Get current directory
$root_dir = dirname(__FILE__);

// Test results
$tests = [];

// 1. Check PHP version
$tests['PHP Version'] = [
    'required' => '7.4+',
    'current' => phpversion(),
    'pass' => version_compare(phpversion(), '7.4.0', '>=')
];

// 2. Check database connection
$tests['Database Connection'] = [
    'required' => 'Connected',
    'current' => 'Testing...',
    'pass' => false
];

try {
    require_once '../includes/db.php';
    if ($conn && !$conn->connect_error) {
        $tests['Database Connection']['current'] = 'Connected ✓';
        $tests['Database Connection']['pass'] = true;
    } else {
        $tests['Database Connection']['current'] = 'Failed: ' . ($conn->connect_error ?? 'Unknown error');
    }
} catch (Exception $e) {
    $tests['Database Connection']['current'] = 'Error: ' . $e->getMessage();
}

// 3. Check carousel table
$tests['Carousel Table'] = [
    'required' => 'Exists',
    'current' => 'Unknown',
    'pass' => false
];

try {
    $result = $conn->query("SHOW TABLES LIKE 'carousel'");
    if ($result && $result->num_rows > 0) {
        $tests['Carousel Table']['current'] = 'Exists ✓';
        $tests['Carousel Table']['pass'] = true;
    } else {
        $tests['Carousel Table']['current'] = 'Not found';
    }
} catch (Exception $e) {
    $tests['Carousel Table']['current'] = 'Error: ' . $e->getMessage();
}

// 4. Check employees table
$tests['Employees Table'] = [
    'required' => 'Exists',
    'current' => 'Unknown',
    'pass' => false
];

try {
    $result = $conn->query("SHOW TABLES LIKE 'employees'");
    if ($result && $result->num_rows > 0) {
        $tests['Employees Table']['current'] = 'Exists ✓';
        $tests['Employees Table']['pass'] = true;
    } else {
        $tests['Employees Table']['current'] = 'Not found';
    }
} catch (Exception $e) {
    $tests['Employees Table']['current'] = 'Error: ' . $e->getMessage();
}

// 5. Check upload folder
$tests['Upload Folder'] = [
    'required' => 'Writable',
    'current' => 'Unknown',
    'pass' => false
];

$upload_dir = '../assets/uploads/carousel/';
if (is_dir($upload_dir) && is_writable($upload_dir)) {
    $tests['Upload Folder']['current'] = 'Writable ✓';
    $tests['Upload Folder']['pass'] = true;
} elseif (is_dir($upload_dir)) {
    $tests['Upload Folder']['current'] = 'Exists but not writable';
} else {
    $tests['Upload Folder']['current'] = 'Directory not found';
}

// 6. Check employee files
$required_files = [
    'login.php' => 'Login page',
    'dashboard.php' => 'Dashboard',
    'manage_carousel.php' => 'Manage carousel',
    'upload_carousel.php' => 'Upload page',
    'edit_carousel.php' => 'Edit page',
    'delete_carousel.php' => 'Delete handler',
    'logout.php' => 'Logout',
    'includes/auth.php' => 'Auth functions',
    'includes/header.php' => 'Header template'
];

$tests['Employee Files'] = [
    'required' => 'All exist',
    'current' => 'Checking...',
    'pass' => true,
    'details' => []
];

foreach ($required_files as $file => $name) {
    $path = $root_dir . '/' . $file;
    $exists = file_exists($path);
    $tests['Employee Files']['details'][$file] = [
        'name' => $name,
        'exists' => $exists,
        'readable' => file_exists($path) && is_readable($path)
    ];
    if (!$exists) {
        $tests['Employee Files']['pass'] = false;
    }
}

// Count passing tests
$pass_count = 0;
foreach ($tests as $test) {
    if (isset($test['pass']) && $test['pass']) {
        $pass_count++;
    }
}

$total_tests = count($tests);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Test - Balmari Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#040D12] text-white font-['Inter']">
    <section class="bg-[#183D3D] text-white py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-center">Installation Test</h1>
            <p class="text-[#5C8374] text-center">Verify your Balmari Admin Panel setup</p>
        </div>
    </section>
    <hr class="border-t border-[#44444E]/30 my-0">
    <div class="min-h-screen py-12 px-4 bg-[#040D12]">
        <div class="max-w-2xl mx-auto">
            
            <!-- Summary -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-500"><?php echo $pass_count; ?></div>
                        <div class="text-gray-600 text-sm">Passed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-orange-500"><?php echo $total_tests - $pass_count; ?></div>
                        <div class="text-gray-600 text-sm">Failed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-500"><?php echo $total_tests; ?></div>
                        <div class="text-gray-600 text-sm">Total</div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="bg-green-500 h-2 rounded-full transition-all" 
                        style="width: <?php echo ($pass_count / $total_tests) * 100; ?>%"
                    ></div>
                </div>
                
                <?php if ($pass_count === $total_tests): ?>
                    <div class="mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <p class="text-green-800 font-semibold">✓ All tests passed! Your setup is ready.</p>
                        <p class="text-green-700 text-sm mt-2">You can now delete this test file and start using the admin panel.</p>
                    </div>
                <?php else: ?>
                    <div class="mt-6 bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                        <p class="text-orange-800 font-semibold">⚠ Some tests failed. Please review below.</p>
                        <p class="text-orange-700 text-sm mt-2">Follow the instructions to complete setup.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Detailed Results -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-slate-900">Test Results</h2>
                </div>
                
                <?php foreach ($tests as $test_name => $test_data): ?>
                    <div class="border-b last:border-b-0">
                        <div class="p-6 flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h3 class="font-semibold text-slate-900"><?php echo $test_name; ?></h3>
                                    <?php if ($test_data['pass']): ?>
                                        <span class="ml-2 inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">✓ Pass</span>
                                    <?php else: ?>
                                        <span class="ml-2 inline-block bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">✗ Fail</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Required:</span>
                                        <span class="ml-2 font-medium text-slate-900"><?php echo $test_data['required']; ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Current:</span>
                                        <span class="ml-2 font-medium text-slate-900"><?php echo $test_data['current']; ?></span>
                                    </div>
                                </div>
                                
                                <?php if (isset($test_data['details']) && !empty($test_data['details'])): ?>
                                    <div class="mt-3 bg-gray-50 p-3 rounded text-sm">
                                        <?php foreach ($test_data['details'] as $file => $info): ?>
                                            <div class="flex items-center py-1">
                                                <?php if ($info['exists'] && $info['readable']): ?>
                                                    <span class="text-green-600 mr-2">✓</span>
                                                <?php else: ?>
                                                    <span class="text-red-600 mr-2">✗</span>
                                                <?php endif; ?>
                                                <span><?php echo $file; ?> - <?php echo $info['name']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Next Steps -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
                <h3 class="font-bold text-blue-900 mb-3">Next Steps</h3>
                <ol class="text-blue-800 text-sm space-y-2 list-decimal list-inside">
                    <?php if ($pass_count !== $total_tests): ?>
                        <li>Fix all failed tests above</li>
                        <li>Refresh this page</li>
                    <?php endif; ?>
                    <li>Delete this test file (test_installation.php)</li>
                    <li>Navigate to <strong>/employee/login.php</strong></li>
                    <li>Login with your credentials</li>
                    <li>Upload your first carousel image</li>
                    <li>Check homepage - image should appear!</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
