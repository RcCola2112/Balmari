<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['employee_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Balmari' : 'Balmari Employee'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #040D12;
            --bg-secondary: #183D3D;
            --accent-color: #5C8374;
            --accent-hover: #715A5A;
            --text-light: #D3DAD9;
        }
        body { background-color: var(--bg-primary); color: var(--text-light); }
        .sidebar { background-color: var(--bg-secondary); }
        .nav-link:hover, .nav-link.active { background-color: var(--accent-color); color: var(--text-light); }
    </style>
</head>
<body class="bg-[#040D12] font-['Inter'] text-[#D3DAD9]">
    <div class="flex min-h-screen">
        <!-- Sidebar (employee, same style as admin) -->
        <aside class="w-64 sidebar h-screen p-6 fixed left-0 top-0 overflow-y-auto" style="height:100vh; -webkit-overflow-scrolling:touch;">
            <div class="mb-8">
                <a href="dashboard.php" class="flex items-center gap-2">
                    <img src="../assets/images/Balmari_Name.png" alt="Balmari" class="h-8 w-auto">
                </a>
            </div>
            <nav class="space-y-2">
                <a href="dashboard.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9]"> <i class="fas fa-chart-line mr-2"></i>Dashboard</a>
                <a href="manage_carousel.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9]"> <i class="fas fa-images mr-2"></i>Carousel</a>
                <a href="manage_completed.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9]"> <i class="fas fa-check-circle mr-2"></i>Completed Work</a>
                <a href="manage_progress.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9]"> <i class="fas fa-project-diagram mr-2"></i>Project in Progress</a>
                <a href="settings.php" class="nav-link block px-4 py-2 rounded-lg text-[#D3DAD9]"> <i class="fas fa-cog mr-2"></i>Settings</a>
            </nav>

            <div class="mt-12 pt-6 border-t border-[#44444E]">
                <div class="user-box">
                    <p class="label">Logged in as:</p>
                    <p class="name"><?php echo htmlspecialchars($_SESSION['employee_name'] ?? 'Employee'); ?></p>
                </div>
                <a href="logout.php" class="logout-btn block w-full text-center py-2 rounded-lg bg-[#5C8374] text-[#183D3D]"> <i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="ml-64 flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-[#D3DAD9]"><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></h2>
                <div class="text-right text-[#5C8374]"><?php echo date('l, F j, Y'); ?></div>
            </div>



