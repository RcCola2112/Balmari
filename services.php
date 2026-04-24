<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Services";
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-[#183D3D] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4">Our Services</h1>
        <p class="text-xl">Discover what we offer for your next project</p>
    </div>
</section>

<!-- Services Grid -->
<hr class="border-t border-[#44444E]/30 my-0">
<section class="py-20 bg-[#040D12] opacity-0 translate-y-6 transition duration-700 ease-out" data-animate>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Service 1 -->
        <div class="mb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="flex items-center mb-4">
                        <svg class="w-12 h-12 mr-4 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 3l3 3m0 0l3-3m-3 3V7"/>
                        </svg>
                        <h2 class="text-3xl font-bold text-[#D3DAD9]">Architectural Design & Visualization</h2>
                    </div>
                    <p class="text-[#D3DAD9] mb-4 leading-relaxed">
                        Our expert architects create stunning designs that combine aesthetics with functionality. We provide 3D visualizations to help you see your project before construction begins.
                    </p>
                    <ul class="space-y-2 text-[#D3DAD9]">
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Conceptual design and planning
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            3D modeling and visualization
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Space optimization
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Interior and exterior design
                        </li>
                    </ul>
                </div>
                <div class="bg-[#183D3D] rounded-lg h-64 flex items-center justify-center border border-[#5C8374]/25">
                    <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 .268m-6-2H5a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2.5a2 2 0 00-1 .268"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Service 2 -->
        <div class="mb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center md:grid-cols-2-reverse">
                <div class="bg-[#183D3D] rounded-lg h-64 flex items-center justify-center border border-[#5C8374]/25">
                    <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l-2 2m0 0V4m2 2h8V4m0 2v13m0 0v2m0-2h2m-2 0h-2m4 0h2m-2 0v2m0-2h-2"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center mb-4">
                        <svg class="w-12 h-12 mr-4 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l-2 2m0 0V4m2 2h8V4m0 2v13m0 0v2m0-2h2m-2 0h-2m4 0h2m-2 0v2m0-2h-2"/>
                        </svg>
                        <h2 class="text-3xl font-bold text-[#D3DAD9]">Technical Drawings</h2>
                    </div>
                    <p class="text-[#D3DAD9] mb-4 leading-relaxed">
                        Precise technical drawings are the foundation of any construction project. Our team creates detailed plans that meet all building codes and regulations.
                    </p>
                    <ul class="space-y-2 text-[#D3DAD9]">
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Architectural floor plans
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Structural plans
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Building code compliance
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Detail drawings and specifications
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Service 3 -->
        <div class="mb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="flex items-center mb-4">
                        <svg class="w-12 h-12 mr-4 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <h2 class="text-3xl font-bold text-[#D3DAD9]">Blueprint Printing Services</h2>
                    </div>
                    <p class="text-[#D3DAD9] mb-4 leading-relaxed">
                        High-quality blueprint printing services for all your construction documentation needs. We ensure clear, accurate prints on demand.
                    </p>
                    <ul class="space-y-2 text-[#D3DAD9]">
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Fast turnaround printing
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Various print sizes available
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            High-quality output
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Competitive pricing
                        </li>
                    </ul>
                </div>
                <div class="bg-[#183D3D] rounded-lg h-64 flex items-center justify-center border border-[#5C8374]/25">
                    <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Service 4 -->
        <div class="mb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center md:grid-cols-2-reverse">
                <div class="bg-[#183D3D] rounded-lg h-64 flex items-center justify-center border border-[#5C8374]/25">
                    <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center mb-4">
                        <svg class="w-12 h-12 mr-4 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h2 class="text-3xl font-bold text-[#D3DAD9]">Construction Services</h2>
                    </div>
                    <p class="text-[#D3DAD9] mb-4 leading-relaxed">
                        From foundation to finishing touches, our experienced construction team brings your project to life with precision and professionalism.
                    </p>
                    <ul class="space-y-2 text-[#D3DAD9]">
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Residential construction
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Commercial projects
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Project management
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Quality assurance
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Service 5 -->
        <div class="mb-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="flex items-center mb-4">
                        <svg class="w-12 h-12 mr-4 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11v-5m0 0h.01M9 15h6"/>
                        </svg>
                        <h2 class="text-3xl font-bold text-[#D3DAD9]">Build & Sell Real Estate</h2>
                    </div>
                    <p class="text-[#D3DAD9] mb-4 leading-relaxed">
                        We handle complete real estate projects from design and construction to marketing and sales. Maximizing your investment potential.
                    </p>
                    <ul class="space-y-2 text-[#D3DAD9]">
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Real estate development
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Property marketing
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Investment consultation
                        </li>
                        <li class="flex items-center">
                            <span class="text-[#D3DAD9] mr-3">✓</span>
                            Portfolio growth strategies
                        </li>
                    </ul>
                </div>
                <div class="bg-[#183D3D] rounded-lg h-64 flex items-center justify-center border border-[#5C8374]/25">
                    <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-3m0 0l7-4 7 4m-7 3v10m0 0H5a1 1 0 01-1-1v-8m14 0h4a1 1 0 011 1v8a1 1 0 01-1 1h-4m-6-10h.01M9 15h6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Our Services -->
    <hr class="border-t border-[#44444E]/30 my-8">
<section class="bg-[#040D12] py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-[#D3DAD9] mb-12 text-center">Why Choose Our Services?</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 mr-2 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-[#D3DAD9]">Fast Execution</h3>
                </div>
                <p class="text-[#D3DAD9]/85">We deliver projects on schedule without compromising quality.</p>
            </div>
            <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 mr-2 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-[#D3DAD9]">Cost Effective</h3>
                </div>
                <p class="text-[#D3DAD9]/85">Competitive pricing with transparent quotations and no hidden fees.</p>
            </div>
            <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 mr-2 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-[#D3DAD9]">Expert Team</h3>
                </div>
                <p class="text-[#D3DAD9]/85">Experienced professionals with proven track records in the industry.</p>
            </div>
            <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                <div class="flex items-center mb-2">
                    <svg class="w-6 h-6 mr-2 text-[#D3DAD9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-[#D3DAD9]">Support 24/7</h3>
                </div>
                <p class="text-[#D3DAD9]/85">Always available to address your concerns and updates.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<hr class="border-t border-[#44444E]/30 my-8">
<section class="bg-[#040D12] text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold mb-4">Need More Information?</h2>
                <p class="text-xl mb-8 text-[#D3DAD9]">Contact us for a detailed consultation about our services</p>
        <a href="contact.php" class="inline-block bg-[#5C8374] text-[#D3DAD9] px-8 py-3 rounded-lg font-semibold hover:bg-[#44444E] transition">Get in Touch</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

