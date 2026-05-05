<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Projects";
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-[#040D12] text-[#D3DAD9] py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4 text-[#FFFFFF]">Our Projects</h1>
        <p class="text-xl text-[#5C8374]">See our completed and ongoing works</p>
    </div>
</section>

<!-- Projects Grid -->

<section class="py-20 bg-[#040D12] opacity-0 translate-y-6 transition duration-700 ease-out" data-animate>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Project 1 -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition border border-[#44444E]/30">
                <div class="bg-gradient-to-br from-[#183D3D] to-[#5C8374] h-48 flex items-center justify-center">
                    <svg class="w-24 h-24 text-[#FFFFFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-[#FFFFFF] mb-2">Modern Office Complex</h3>
                    <p class="text-[#D3DAD9]/85 text-sm mb-4">
                        A state-of-the-art office building featuring modern architecture and sustainable design practices.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Commercial</span>
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Design</span>
                    </div>
                </div>
            </div>
            
            <!-- Project 2 -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition border border-[#44444E]/30">
                <div class="bg-gradient-to-br from-[#183D3D] to-[#5C8374] h-48 flex items-center justify-center">
                    <svg class="w-24 h-24 text-[#FFFFFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-3m0 0l7-4 7 4m-7 3v10m0 0H5a1 1 0 01-1-1v-8m14 0h4a1 1 0 011 1v8a1 1 0 01-1 1h-4m-6-10h.01M9 15h6"/>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-[#FFFFFF] mb-2">Luxury Residential Subdivision</h3>
                    <p class="text-[#D3DAD9]/85 text-sm mb-4">
                        A premium residential development with carefully designed homes and landscaped communities.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Residential</span>
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Real Estate</span>
                    </div>
                </div>
            </div>
            
            <!-- Project 3 -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition border border-[#44444E]/30">
                <div class="bg-gradient-to-br from-[#183D3D] to-[#5C8374] h-48 flex items-center justify-center">
                    <svg class="w-24 h-24 text-[#FFFFFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-[#FFFFFF] mb-2">Industrial Warehouse</h3>
                    <p class="text-[#D3DAD9]/85 text-sm mb-4">
                        Modern industrial facility designed for optimal storage and logistics operations.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Industrial</span>
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Construction</span>
                    </div>
                </div>
            </div>
            
            <!-- Project 4 -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition border border-[#44444E]/30">
                <div class="bg-gradient-to-br from-[#183D3D] to-[#5C8374] h-48 flex items-center justify-center">
                    <svg class="w-24 h-24 text-[#FFFFFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9a2 2 0 012-2z"/>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-[#FFFFFF] mb-2">Retail Shopping Center</h3>
                    <p class="text-[#D3DAD9]/85 text-sm mb-4">
                        Contemporary shopping mall with modern retail spaces and customer-friendly design.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Commercial</span>
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Design</span>
                    </div>
                </div>
            </div>
            
            <!-- Project 5 -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition border border-[#44444E]/30">
                <div class="bg-gradient-to-br from-[#183D3D] to-[#5C8374] h-48 flex items-center justify-center">
                    <svg class="w-24 h-24 text-[#FFFFFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-[#FFFFFF] mb-2">Medical Complex</h3>
                    <p class="text-[#D3DAD9]/85 text-sm mb-4">
                        State-of-the-art medical facility designed for patient comfort and operational efficiency.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Healthcare</span>
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Construction</span>
                    </div>
                </div>
            </div>
            
            <!-- Project 6 -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition border border-[#44444E]/30">
                <div class="bg-gradient-to-br from-[#183D3D] to-[#5C8374] h-48 flex items-center justify-center">
                    <svg class="w-24 h-24 text-[#FFFFFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17.25m20-11.002c0 5.252-4.5 10.002-10 10.002M19 20H5m0 0a9.023 9.023 0 001.946 2.560m16.108 0a9.023 9.023 0 001.946-2.56m-35.027-2.5h34.334m-34.334 0a15.13 15.13 0 01-.214-1.033m34.546 0a15.13 15.13 0 01-.214 1.033M9 9h.008v.008H9V9m11 0h.008v.008H20V9m-10 5h.008v.008H10v-.008zm11 0h.008v.008H21v-.008z"/>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-[#FFFFFF] mb-2">Educational Institution</h3>
                    <p class="text-[#D3DAD9]/85 text-sm mb-4">
                        Modern educational facility with classrooms, laboratories, and recreational spaces.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Education</span>
                        <span class="bg-[#040D12] text-[#5C8374] text-xs px-3 py-1 rounded-full border border-[#5C8374]/40">Design</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Statistics -->
<hr class="border-t border-[#44444E]/30 my-8">
<section class="bg-[#183D3D] py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-[#FFFFFF] mb-12 text-center">Project Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="bg-[#040D12] p-8 rounded-lg shadow-lg text-center border border-[#44444E]/30">
                <h3 class="text-4xl font-bold text-[#5C8374] mb-2">50+</h3>
                <p class="text-[#D3DAD9]/85 font-semibold">Projects Completed</p>
            </div>
            <div class="bg-[#040D12] p-8 rounded-lg shadow-lg text-center border border-[#44444E]/30">
                <h3 class="text-4xl font-bold text-[#5C8374] mb-2">₱500M+</h3>
                <p class="text-[#D3DAD9]/85 font-semibold">Total Project Value</p>
            </div>
            <div class="bg-[#040D12] p-8 rounded-lg shadow-lg text-center border border-[#44444E]/30">
                <h3 class="text-4xl font-bold text-[#5C8374] mb-2">100%</h3>
                <p class="text-[#D3DAD9]/85 font-semibold">On-Time Delivery</p>
            </div>
            <div class="bg-[#040D12] p-8 rounded-lg shadow-lg text-center border border-[#44444E]/30">
                <h3 class="text-4xl font-bold text-[#5C8374] mb-2">25+</h3>
                <p class="text-[#D3DAD9]/85 font-semibold">Team Members</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="bg-[#040D12] text-[#D3DAD9] py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold mb-4 text-[#FFFFFF]">Have a Project in Mind?</h2>
        <p class="text-xl mb-8 text-[#D3DAD9]/90">Let's discuss how we can bring your vision to life</p>
        <a href="contact.php" class="inline-block bg-[#5C8374] text-[#183D3D] px-8 py-3 rounded-lg font-semibold hover:bg-[#93B1A6] transition">Start Your Project</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

