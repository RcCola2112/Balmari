<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "About";
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-[#183D3D] text-white py-16">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4">About Balmari</h1>
        <p class="text-xl /90">Learn more about our company and mission</p>
    </div>
</section>

<!-- Main Content -->

<!-- Founder -->
<section class="bg-[#040D12] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
                <div class="flex justify-center lg:justify-end">
                    <div class="p-3 bg-[#183D3D] rounded-lg border-4 border-[#5C8374] shadow-xl">
                        <?php
                        // Prefer an admin-provided marker for the founder photo (ABOUT_FOUNDER_PHOTO),
                        // otherwise fall back to scanning the founder folder for founder.* files.
                        $founder_img_html = null;
                        $self_contents = @file_get_contents(__FILE__);
                        if ($self_contents !== false) {
                            if (preg_match('/<!--\s*ABOUT_FOUNDER_PHOTO_START\s*-->(.*?)<!--\s*ABOUT_FOUNDER_PHOTO_END\s*-->/is', $self_contents, $mm)) {
                                $marker_inner = trim($mm[1]);
                                if ($marker_inner !== '') {
                                    // If admin saved a full <img> tag use it, otherwise treat it as a path
                                    if (stripos($marker_inner, '<img') !== false) {
                                        $founder_img_html = $marker_inner;
                                    } else {
                                        $p = trim($marker_inner);
                                        $founder_img_html = '<img src="' . htmlspecialchars($p) . '" alt="Founder" class="w-full max-w-md object-cover rounded-md" />';
                                    }
                                }
                            }
                        }

                        if ($founder_img_html) {
                            echo $founder_img_html;
                        } else {
                            // fallback: prefer a file in the founder folder named founder.* (jpg/png/webp)
                            $founder_rel = 'assets/images/members_pictures/founder';
                            $founder_file = null;
                            $founder_dir = __DIR__ . '/' . $founder_rel;
                            if (is_dir($founder_dir) && is_readable($founder_dir)) {
                                $candidates = glob($founder_dir . DIRECTORY_SEPARATOR . 'founder.*');
                                if (!empty($candidates)) {
                                    usort($candidates, function($a, $b){ return filemtime($b) - filemtime($a); });
                                    $founder_file = $founder_rel . '/' . basename($candidates[0]);
                                }
                            }
                            // fallback to existing path if no file in folder
                            if (empty($founder_file)) {
                                $founder_file = 'assets/images/members_pictures/founder/founder.jpg';
                            }
                            echo '<img src="' . htmlspecialchars($founder_file) . '" alt="Ar. Allisa B. Manrique" class="w-full max-w-md object-cover rounded-md" />';
                        }
                        ?>
                    </div>
            </div>
            <div>
                <h2 class="text-4xl font-['Playfair_Display'] font-semibold text-white mb-6"><!-- ABOUT_FOUNDER_TITLE_START -->Meet Our Founder<!-- ABOUT_FOUNDER_TITLE_END --></h2>
                <h3 class="text-2xl font-['Playfair_Display'] font-semibold text-white/90"><!-- ABOUT_FOUNDER_NAME_START -->Ar. Allisa B. Manrique<!-- ABOUT_FOUNDER_NAME_END --></h3>
                <div class="mt-6 space-y-5 text-base sm:text-lg leading-relaxed text-white/85">
                    <!-- ABOUT_FOUNDER_BIO_START -->
                    <p>
                        Ar. Allisa B. Manrique founded Balmari with a commitment to thoughtful design and dependable construction. She leads the team with a focus on clear collaboration, quality craftsmanship, and a smooth client experience from concept to completion.
                    </p>
                    <!-- ABOUT_FOUNDER_BIO_END -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT_INFO_START -->
<section class="py-20 bg-[#040D12] opacity-0 translate-y-6 transition duration-700 ease-out" data-animate>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-16">
            <!-- Text Content -->
            <div>
                <h2 class="text-4xl font-['Playfair_Display'] font-semibold text-white mb-6">Our Story</h2>
                <!-- ABOUT_OUR_STORY_START -->
                <?php
                // Allow admin to control the Our Story block via the ABOUT_OUR_STORY marker.
                $our_story_inner = null;
                $about_self = @file_get_contents(__FILE__);
                if ($about_self !== false && preg_match('/<!--\s*ABOUT_OUR_STORY_START\s*-->(.*?)<!--\s*ABOUT_OUR_STORY_END\s*-->/is', $about_self, $mm)) {
                    $our_story_inner = trim($mm[1]);
                }

                if ($our_story_inner !== null && $our_story_inner !== '') {
                    // If marker contains HTML tags, output as-is. Otherwise treat as plain text and split into paragraphs.
                    if (strip_tags($our_story_inner) !== $our_story_inner) {
                        echo $our_story_inner;
                    } else {
                        $parts = preg_split('/(?:\r\n|\r|\n){2,}/', trim($our_story_inner));
                        foreach ($parts as $p) {
                            $p = trim($p);
                            if ($p === '') continue;
                            echo '<p class="text-white/85 mb-4 leading-relaxed">' . htmlspecialchars($p) . '</p>';
                        }
                    }
                } else {
                ?>
                <p class="text-white/85 mb-4 leading-relaxed">
                    Balmari Design and Construction was founded with a mission to bring professional design and construction services across the Philippines. With over 15 years of combined experience, our team has successfully completed over 50 projects ranging from residential to commercial developments.
                </p>
                <p class="text-white/85 mb-4 leading-relaxed">
                    We believe in the power of great design combined with quality construction. Every project we undertake is treated as a unique opportunity to showcase our expertise and commitment to excellence.
                </p>
                <p class="text-white/85 leading-relaxed">
                    Our tagline, "From Concept to Completion," reflects our comprehensive approach to every project. From initial design consultation to final construction delivery, we ensure every detail is handled with precision and professionalism.
                </p>
                <?php
                }
                ?>
                <!-- ABOUT_OUR_STORY_END -->
            </div>
            
            <!-- Stats Box -->
            <div class="bg-[#183D3D] text-white p-8 rounded-lg border border-[#5C8374]/25">
                <h3 class="text-2xl font-bold mb-8">Our Achievements</h3>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="text-4xl font-bold mr-4">50+</div>
                        <div>
                            <h4 class="font-bold text-lg">Projects Completed</h4>
                            <p class="text-white/90">Residential and commercial projects</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-4xl font-bold mr-4">Since 2017</div>
                        <div>
                            <h4 class="font-bold text-lg">Serving Batangas</h4>
                            <p class="text-white/90">Trusted local expertise</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-4xl font-bold mr-4">100%</div>
                        <div>
                            <h4 class="font-bold text-lg">Satisfaction Rate</h4>
                            <p class="text-white/90">Client satisfaction guaranteed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mission & Vision -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-20">
            <div class="bg-[#183D3D] p-10 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                <h3 class="text-2xl font-bold text-white mb-4">Our Mission</h3>
                <!-- ABOUT_MISSION_START -->
                <p class="text-white/85 leading-relaxed">
                    To deliver exceptional design and construction services that transform concepts into reality. We strive to exceed client expectations through innovation, quality craftsmanship, and professional service at every stage of the project.
                </p>
                <!-- ABOUT_MISSION_END -->
            </div>
            <div class="bg-[#183D3D] p-10 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                <h3 class="text-2xl font-bold text-white mb-4">Our Vision</h3>
                <!-- ABOUT_VISION_START -->
                <p class="text-white/85 leading-relaxed">
                    To be the leading design and construction company in the Philippines, recognized for our innovative solutions, sustainable practices, and commitment to client satisfaction.
                </p>
                <!-- ABOUT_VISION_END -->
            </div>
        </div>
        
        <!-- Core Values -->
        <div class="mb-20">
            <h2 class="text-4xl font-bold text-white mb-10 text-center">Our Core Values</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                    <h4 class="text-xl font-bold text-white mb-3">Excellence</h4>
                    <!-- ABOUT_CORE_1_START --><p class="text-white/85">We commit to the highest standards in design and construction quality.</p><!-- ABOUT_CORE_1_END -->
                </div>
                <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                    <h4 class="text-xl font-bold text-white mb-3">Integrity</h4>
                    <!-- ABOUT_CORE_2_START --><p class="text-white/85">Honest communication and transparent dealings with all clients and partners.</p><!-- ABOUT_CORE_2_END -->
                </div>
                <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                    <h4 class="text-xl font-bold text-white mb-3">Innovation</h4>
                    <!-- ABOUT_CORE_3_START --><p class="text-white/85">Creative solutions that bring modern design trends to your projects.</p><!-- ABOUT_CORE_3_END -->
                </div>
                <div class="bg-[#183D3D] p-8 rounded-2xl border border-[#5C8374]/25 shadow-lg">
                    <h4 class="text-xl font-bold text-white mb-3">Reliability</h4>
                    <!-- ABOUT_CORE_4_START --><p class="text-white/85">Meeting deadlines and delivering projects as promised, every time.</p><!-- ABOUT_CORE_4_END -->
                </div>
            </div>
        </div>
    
    </div>
</section>

<!-- ABOUT_INFO_END -->

<?php include 'includes/footer.php'; ?>

