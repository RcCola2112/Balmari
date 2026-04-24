<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
            $favicon_path = 'assets/images/Balmari_Icon.png';
        $favicon_version = file_exists($favicon_path) ? filemtime($favicon_path) : time();
    ?>
    <title><?php echo isset($page_title) ? $page_title . ' - Balmari' : 'Balmari: Design and Construction'; ?></title>
        <link rel="icon" href="<?php echo $favicon_path . '?v=' . $favicon_version; ?>" type="image/png">
        <link rel="shortcut icon" href="<?php echo $favicon_path . '?v=' . $favicon_version; ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo $favicon_path . '?v=' . $favicon_version; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Placoyfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#040D12] font-['Inter'] text-[#93B1A6]" style="margin:0;padding:0;">
    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-[#183D3D] backdrop-blur border-b border-[#183D3D]/60" style="width:100vw;margin:0;padding:0;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-[#183D3D]">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="index.php" class="flex items-center">
                        <img src="assets/images/Balmari_Name.png" alt="Balmari Logo" class="h-10 w-auto" style="max-height:40px;" />
                    </a>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-[#93B1A6] hover:text-[#5C8374] px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                        <a href="about.php" class="text-[#93B1A6] hover:text-[#5C8374] px-3 py-2 rounded-md text-sm font-medium transition">About</a>
                        <a href="services.php" class="text-[#93B1A6] hover:text-[#5C8374] px-3 py-2 rounded-md text-sm font-medium transition">Services</a>
                        <a href="contact.php" class="bg-[#5C8374] text-[#183D3D] px-4 py-2 rounded-md text-sm font-medium hover:bg-[#93B1A6] hover:text-[#183D3D] transition">Contact</a>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button class="inline-flex items-center justify-center p-2 rounded-md text-[#D3DAD9] hover:text-[#715A5A]" onclick="toggleMenu()">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            <hr class="border-t border-[#202025]/30 m-0">
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="hidden md:hidden bg-[#183D3D] border-t border-[#183D3D]/40">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="index.php" class="text-[#93B1A6] hover:text-[#5C8374] block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="about.php" class="text-[#93B1A6] hover:text-[#5C8374] block px-3 py-2 rounded-md text-base font-medium">About</a>
                <a href="services.php" class="text-[#93B1A6] hover:text-[#5C8374] px-3 py-2 rounded-md text-sm font-medium transition">Services</a>
                <a href="contact.php" class="bg-[#5C8374] text-[#183D3D] block px-3 py-2 rounded-md text-base font-medium hover:bg-[#93B1A6] hover:text-[#183D3D] transition">Contact</a>
            </div>
        </div>
    </nav>
    
    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
        // Detect current page and highlight active nav link
        function highlightCurrentPage() {
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const navLinks = document.querySelectorAll('a[href$=".php"]');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage || (currentPage === '' && href === 'index.php')) {
                    // Remove existing classes and add active state
                    link.classList.remove('text-white', 'hover:text-[#def391]', 'bg-[#def391]', 'text-[#005244]', 'hover:opacity-90');
                    link.classList.add('text-[#def391]', 'font-semibold');
                } else {
                    // Reset to default state
                    link.classList.remove('text-[#def391]', 'font-semibold');
                    link.classList.add('text-white', 'hover:text-[#def391]');
                }
            });
        }
        
        // Run on page load
        document.addEventListener('DOMContentLoaded', highlightCurrentPage);
    </script>

