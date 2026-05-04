<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    $page_title = "Home";
    include 'includes/header.php';

    // Load carousel images from filesystem
    $carousel_items = glob('assets/images/carousel/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    if (!$carousel_items || count($carousel_items) === 0) {
        $carousel_items = ['assets/images/633871533_880323638229977_385927937879935407_n.jpg'];
    }
    
    // Normalize paths (convert backslash to forward slash)
    $carousel_items = array_map(function ($path) {
        return str_replace('\\', '/', $path);
    }, $carousel_items);

    function loadProjectCoverImages($projects_dir, $fallback_image) {
        $covers = [];

        if (is_dir($projects_dir)) {
            $project_folders = array_diff(scandir($projects_dir, SCANDIR_SORT_DESCENDING), ['.', '..']);
            foreach ($project_folders as $folder) {
                $project_path = $projects_dir . $folder . '/';
                $metadata_file = $project_path . 'metadata.json';

                if (file_exists($metadata_file)) {
                    $metadata = json_decode(file_get_contents($metadata_file), true);
                    if ($metadata && !empty($metadata['cover_image'])) {
                        $covers[] = str_replace('\\', '/', $project_path . $metadata['cover_image']);
                    }
                }
            }
        }

        if (empty($covers)) {
            $covers[] = $fallback_image;
        }

        return $covers;
    }

    $fallback_image = 'assets/images/633871533_880323638229977_385927937879935407_n.jpg';
    $completed_cover_images = loadProjectCoverImages('assets/projects/completed/', $fallback_image);
    $progress_cover_images = loadProjectCoverImages('assets/projects/progress/', $fallback_image);

    // If project cover lists are too small to rotate, use homepage carousel images as fallback
    // This ensures the completed/progress tiles animate even when there are no project folders.
    if (count($completed_cover_images) < 2) {
        $completed_cover_images = array_values(array_unique(array_merge($completed_cover_images, $carousel_items)));
    }
    if (count($progress_cover_images) < 2) {
        $progress_cover_images = array_values(array_unique(array_merge($progress_cover_images, $carousel_items)));
    }
?>

    <!-- Hero Section -->
    <section id="heroCarousel" class="relative text-white w-screen h-screen min-h-screen overflow-hidden flex items-center justify-center">
        <div id="heroBg" class="absolute inset-0 transition-opacity duration-700 opacity-100" style="background: linear-gradient(rgba(24,61,61,0.65), rgba(24,61,61,0.65)), url('<?php echo htmlspecialchars($carousel_items[0], ENT_QUOTES, 'UTF-8'); ?>') center/cover no-repeat;"></div>
        <div class="max-w-7xl w-full px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-['Playfair_Display'] font-semibold mb-4 text-white drop-shadow-lg" style="text-shadow: 0 4px 24px #040D12, 0 1px 0 #183D3D; border-radius: 0.5rem; border: 2px solid #5C8374; display: inline-block; padding: 0.5rem 2rem; background: rgba(24,61,61,0.25);">From Concept to Completion</h1>
            <p class="text-xl md:text-2xl text-white/90 mb-8 drop-shadow-lg" style="text-shadow: 0 2px 12px #040D12; background: rgba(24,61,61,0.18); display: inline-block; border-radius: 0.5rem; padding: 0.25rem 1.5rem;">Expert Design and Construction Services — Serving Internationally</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="contact_information.php" class="bg-[#5C8374] text-white px-8 py-3 rounded-full font-semibold hover:bg-[#183D3D] hover:text-white transition shadow-lg shadow-[#040D12]/30">Contact</a>
                <a href="contact.php" class="text-white px-8 py-3 rounded-full font-semibold border-2 border-[#5C8374] hover:bg-[#5C8374] hover:text-[#040D12] transition shadow-lg shadow-[#040D12]/20">Get in Touch</a>
            </div>
        </div>
    </section>

    <script>
        (function () {
            const heroBg = document.getElementById('heroBg');
            const images = <?php echo json_encode($carousel_items, JSON_UNESCAPED_SLASHES); ?>;
            if (!heroBg || !images || images.length < 1) {
                return;
            }
            // If only one image, don't rotate
            if (images.length === 1) {
                return;
            }

            // Prepare smooth transform + opacity transitions
            heroBg.style.transition = 'transform 1.2s ease, opacity 0.35s ease';
            heroBg.style.transformOrigin = 'center center';

            let index = 0;
            const intervalMs = 5000;

            function performHeroTransition() {
                // Zoom in slightly before changing
                heroBg.style.transform = 'scale(1.06)';

                // After zoom-in completes, fade and switch image
                setTimeout(() => {
                    heroBg.classList.add('opacity-0');
                    setTimeout(() => {
                        index = (index + 1) % images.length;
                        heroBg.style.backgroundImage = `linear-gradient(rgba(10, 25, 47, 0.65), rgba(10, 25, 47, 0.65)), url('${images[index]}')`;
                        heroBg.classList.remove('opacity-0');

                        // Immediately keep slightly zoomed then ease back to normal to create zoom-out effect
                        heroBg.style.transform = 'scale(1.06)';
                        // small timeout to allow reflow then animate back to 1
                        setTimeout(() => {
                            heroBg.style.transform = 'scale(1)';
                        }, 50);
                    }, 350);
                }, 1200);
            }

            // Start periodic transitions
            setInterval(performHeroTransition, intervalMs);
        })();
    </script>


    <!-- Project Status Grid -->
    <section class="py-0 bg-[#040D12] w-screen h-screen flex-none">
        <div class="w-full h-full grid grid-cols-1 md:grid-cols-2 grid-rows-2 gap-0 h-full">
            <div class="relative w-full h-full overflow-hidden">
                <div
                    id="completedCarousel"
                    class="absolute inset-0 bg-center bg-cover transition-opacity duration-700"
                    style="background-image: linear-gradient(rgba(24, 61, 61, 0.45), rgba(24, 61, 61, 0.45)), url('<?php echo htmlspecialchars($completed_cover_images[0], ENT_QUOTES, 'UTF-8'); ?>');"
                ></div>
            </div>
                <div class="bg-[#040D12] flex items-center justify-center w-full h-full h-full px-6">
                    <a href="completed.php" class="inline-flex items-center justify-center min-w-[220px] px-8 py-4 rounded-full border-2 border-[#5C8374] bg-[#183D3D] text-white font-['Playfair_Display'] text-2xl md:text-3xl font-semibold shadow-xl shadow-[#040D12]/40 hover:bg-[#5C8374] hover:text-[#040D12] hover:scale-105 transition duration-300 text-center">
                    Completed Work
                    </a>
                </div>
                <div class="bg-[#040D12] flex items-center justify-center w-full h-full h-full px-6">
                    <a href="progress.php" class="inline-flex items-center justify-center min-w-[220px] px-8 py-4 rounded-full border-2 border-[#5C8374] bg-[#183D3D] text-white font-['Playfair_Display'] text-2xl md:text-3xl font-semibold shadow-xl shadow-[#040D12]/40 hover:bg-[#5C8374] hover:text-[#040D12] hover:scale-105 transition duration-300 text-center">
                    In Progress
                    </a>
                </div>
            <div class="relative w-full h-full overflow-hidden">
                <div
                    id="progressCarousel"
                    class="absolute inset-0 bg-center bg-cover transition-opacity duration-700"
                    style="background-image: linear-gradient(rgba(24, 61, 61, 0.45), rgba(24, 61, 61, 0.45)), url('<?php echo htmlspecialchars($progress_cover_images[0], ENT_QUOTES, 'UTF-8'); ?>');"
                ></div>
            </div>
        </div>
    </section>
    <script>
        (function () {
            function rotateBackground(el, images, intervalMs) {
                if (!el || !images || images.length < 2) {
                    return;
                }

                // Prepare transform transitions for the small carousels
                el.style.transition = 'transform 0.9s ease, opacity 0.35s ease';
                el.style.transformOrigin = 'center center';

                let idx = 0;

                function doTransition() {
                    // Zoom in a bit
                    el.style.transform = 'scale(1.04)';

                    setTimeout(() => {
                        el.classList.add('opacity-0');
                        setTimeout(() => {
                            idx = (idx + 1) % images.length;
                            el.style.backgroundImage = `linear-gradient(rgba(10, 25, 47, 0.45), rgba(10, 25, 47, 0.45)), url('${images[idx]}')`;
                            el.classList.remove('opacity-0');

                            // keep slightly zoomed and animate back to normal for zoom-out
                            el.style.transform = 'scale(1.04)';
                            setTimeout(() => { el.style.transform = 'scale(1)'; }, 50);
                        }, 300);
                    }, 800);
                }

                setInterval(doTransition, intervalMs);
            }

            const completedImages = <?php echo json_encode($completed_cover_images, JSON_UNESCAPED_SLASHES); ?>;
            const progressImages = <?php echo json_encode($progress_cover_images, JSON_UNESCAPED_SLASHES); ?>;

            rotateBackground(document.getElementById('completedCarousel'), completedImages, 4500);
            rotateBackground(document.getElementById('progressCarousel'), progressImages, 4500);
        })();
    </script>


    <!-- About Preview -->
    <section class="py-20 bg-[#040D12] transition duration-700 ease-out" data-animate>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <p class="uppercase tracking-widest text-white text-sm font-semibold">About Balmari</p>
                    <h2 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold text-white">Designing spaces that feel premium, built to last.</h2>
                    <p class="text-lg text-white/85">
                        Balmari: Design and Construction delivers end-to-end architectural planning and construction services internationally. We blend elegance, functionality, and craftsmanship to bring your vision to life.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="rounded-2xl bg-[#040D12] border border-[#5C8374]/25 p-5">
                            <p class="text-2xl text-white font-semibold">50+</p>
                            <p class="text-sm text-white/85">Projects Completed</p>
                        </div>
                        <div class="rounded-2xl bg-[#040D12] border border-[#5C8374]/25 p-5">
                            <p class="text-2xl text-white font-semibold">Since 2017</p>
                            <p class="text-sm text-white/85">Serving Internationally</p>
                        </div>
                    </div>
                    <a href="about.php" class="inline-flex items-center gap-2 text-white font-semibold hover:opacity-90 transition">
                        Learn more about us
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
                <div class="relative">
                    <img src="assets/images/members_pictures/founder/founder.jpg" alt="Balmari design and construction" class="rounded-3xl shadow-xl shadow-[#040D12]/20 border border-[#5C8374]/30 w-full max-w-md mx-auto object-cover">
                    <div class="absolute -bottom-6 -left-6 bg-[#5C8374] border border-[#5C8374] rounded-2xl shadow-lg p-4 hidden md:block">
                        <p class="text-sm text-white">In service since 2017</p>
                        <p class="font-semibold text-white">Serving Internationally</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <hr class="border-t border-[#44444E]/30 my-8">

    <!-- Services Overview -->

    <section class="py-20 bg-[#040D12] transition duration-700 ease-out" data-animate>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="uppercase tracking-widest text-white text-sm font-semibold flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 5.146a.75.75 0 011.374 0l1.518 3.33a.75.75 0 00.6.427l3.637.53a.75.75 0 01.416 1.279l-2.631 2.563a.75.75 0 00-.216.664l.621 3.623a.75.75 0 01-1.088.79l-3.257-1.712a.75.75 0 00-.698 0l-3.257 1.712a.75.75 0 01-1.088-.79l.621-3.623a.75.75 0 00-.216-.664L3.22 10.182a.75.75 0 01.416-1.279l3.637-.53a.75.75 0 00.6-.427l1.518-3.33z" />
                    </svg>
                    <span>Our Services</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold text-white mb-4">Premium solutions from design to build</h2>
                <p class="text-lg text-white/85">Comprehensive design and construction solutions tailored to your goals.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="rounded-2xl bg-[#183D3D] border border-[#5C8374]/20 p-6 hover:shadow-2xl hover:shadow-[#5C8374]/30 hover:border-[#5C8374] transition transform hover:-translate-y-2">
                    <svg class="w-8 h-8 mb-4 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 3l3 3m0 0l3-3m-3 3V7"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Architectural Design & Visualization</h3>
                    <p class="text-sm text-white/80">Concepts, 3D visuals, and refined designs that impress.</p>
                </div>
                <div class="rounded-2xl bg-[#183D3D] border border-[#5C8374]/20 p-6 hover:shadow-2xl hover:shadow-[#5C8374]/30 hover:border-[#5C8374] transition transform hover:-translate-y-2">
                    <svg class="w-8 h-8 mb-4 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l-2 2m0 0V4m2 2h8V4m0 2v13m0 0v2m0-2h2m-2 0h-2m4 0h2m-2 0v2m0-2h-2"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Technical Drawings</h3>
                    <p class="text-sm text-white/80">Precise plans and documentation for smooth execution.</p>
                </div>
                <div class="rounded-2xl bg-[#183D3D] border border-[#5C8374]/20 p-6 hover:shadow-2xl hover:shadow-[#5C8374]/30 hover:border-[#5C8374] transition transform hover:-translate-y-2">
                    <svg class="w-8 h-8 mb-4 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Blueprint Printing</h3>
                    <p class="text-sm text-white/80">Fast, high-quality printing for professional presentation.</p>
                </div>
                <div class="rounded-2xl bg-[#183D3D] border border-[#5C8374]/20 p-6 hover:shadow-2xl hover:shadow-[#5C8374]/30 hover:border-[#5C8374] transition transform hover:-translate-y-2">
                    <svg class="w-8 h-8 mb-4 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Construction Services</h3>
                    <p class="text-sm text-white/80">Skilled teams delivering durable, elegant builds.</p>
                </div>
                <div class="rounded-2xl bg-[#183D3D] border border-[#5C8374]/20 p-6 hover:shadow-2xl hover:shadow-[#5C8374]/30 hover:border-[#5C8374] transition transform hover:-translate-y-2">
                    <svg class="w-8 h-8 mb-4 text-[#5C8374]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11v-5m0 0h.01M9 15h6"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Build & Sell Real Estate</h3>
                    <p class="text-sm text-white/80">End-to-end development for premium properties.</p>
                </div>
            </div>
        </div>
    </section>
    <hr class="border-t border-[#44444E]/30 my-8">



    <!-- Call to Action -->
    <section class="py-16 bg-[#040D12] text-white transition duration-700 ease-out" data-animate>
        <div class="max-w-4xl mx-auto px-4 sm:px-8 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4">Ready to build with Balmari?</h2>
            <p class="text-lg mb-8">Let's create a space that feels refined, functional, and uniquely yours.</p>
            <a href="contact.php" class="inline-flex items-center justify-center bg-[#5C8374] text-[#040D12] px-8 py-3 rounded-full font-semibold hover:bg-[#183D3D] border-2 border-[#5C8374] transition">Contact Us Now</a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

