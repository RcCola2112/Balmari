<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// project_details.php
$page_title = "Project Details";
include 'includes/header.php';

// Get project id and type from query string
$project_id = isset($_GET['id']) ? $_GET['id'] : null;
$type = isset($_GET['type']) ? $_GET['type'] : 'completed';

// Validate type
if ($type !== 'completed' && $type !== 'progress') {
    $type = 'completed';
}

$project = null;
$detail_images = [];

if ($project_id) {
    // Build project path
    $project_path = 'assets/projects/' . $type . '/' . $project_id . '/';
    $metadata_file = $project_path . 'metadata.json';
    
    // Load project metadata
    if (file_exists($metadata_file)) {
        $project = json_decode(file_get_contents($metadata_file), true);
        
        if ($project) {
            // Always add cover image first
            $detail_images[] = $project_path . $project['cover_image'];
            
            // Load detail images from details/ subdirectory
            $details_dir = $project_path . 'details/';
            if (is_dir($details_dir)) {
                $images = array_diff(scandir($details_dir), ['.', '..']);
                foreach ($images as $img) {
                    $detail_images[] = $details_dir . $img;
                }
            }
        }
    }
}
?>
<section class="bg-[#183D3D] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4 text-center">Project Details</h1>
    </div>
</section>
<hr class="border-t border-[#44444E]/30 my-0">
<section class="min-h-screen bg-[#040D12] flex flex-col items-center pt-12 pb-8 px-2">
    <div class="w-full max-w-[95vw] md:max-w-[90vw] lg:max-w-[1100px] mx-auto px-2 md:px-4">
        <?php if ($project): ?>
            <div class="mb-8 text-left">
                
                <h1 class="text-3xl md:text-5xl font-['Playfair_Display'] font-semibold mb-2 tracking-wide text-white" style="letter-spacing:0.08em;">
                    <?php echo htmlspecialchars($project['title']); ?>
                </h1>
                <h2 class="text-lg md:text-2xl font-semibold text-[#5C8374] mb-2"><?php echo htmlspecialchars($project['location']); ?></h2>
                <p class="text-white max-w-2xl mb-8"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($detail_images as $idx => $img): ?>
                    <div class="bg-[#22272B] rounded-lg shadow p-2 flex items-center justify-center">
                        <img 
                            src="<?php echo htmlspecialchars($img); ?>" 
                            alt="<?php echo htmlspecialchars($project['title']); ?>" 
                            class="w-full h-auto object-contain rounded-lg cursor-pointer transition hover:opacity-80 border-2 border-[#44444E]" 
                            onclick="openModal(<?php echo $idx; ?>)" 
                        />
                    </div>
                <?php endforeach; ?>
                <?php if (empty($detail_images)): ?>
                    <div class="text-center py-12 col-span-full">
                        <p class="text-white">No images available for this project.</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Fullscreen Modal with navigation -->
            <div id="imgModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 hidden">
                <button aria-label="Close" class="absolute top-6 right-8 text-4xl text-white cursor-pointer select-none z-50" onclick="closeModal()">&times;</button>

                <button id="prevBtn" aria-label="Previous image" class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-white text-3xl z-50 p-2 bg-black/30 rounded-full hover:bg-black/50" onclick="prevImage()">◀</button>

                <div class="max-w-[95vw] max-h-[90vh] p-4">
                    <img id="modalImg" src="" alt="Project Image" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl border-4 border-[#715A5A] object-contain" />
                </div>

                <button id="nextBtn" aria-label="Next image" class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 text-white text-3xl z-50 p-2 bg-black/30 rounded-full hover:bg-black/50" onclick="nextImage()">▶</button>
            </div>

            <script>
            const detailImages = <?php echo json_encode($detail_images, JSON_UNESCAPED_SLASHES); ?>;
            let currentIndex = 0;

            function openModal(idx) {
                if (!Array.isArray(detailImages) || detailImages.length === 0) return;
                currentIndex = parseInt(idx, 10) || 0;
                document.getElementById('modalImg').src = detailImages[currentIndex];
                document.getElementById('imgModal').classList.remove('hidden');
                updateNavVisibility();
            }

            function closeModal() {
                document.getElementById('imgModal').classList.add('hidden');
                document.getElementById('modalImg').src = '';
            }

            function prevImage() {
                if (!detailImages || detailImages.length === 0) return;
                currentIndex = (currentIndex - 1 + detailImages.length) % detailImages.length;
                document.getElementById('modalImg').src = detailImages[currentIndex];
            }

            function nextImage() {
                if (!detailImages || detailImages.length === 0) return;
                currentIndex = (currentIndex + 1) % detailImages.length;
                document.getElementById('modalImg').src = detailImages[currentIndex];
            }

            function updateNavVisibility() {
                // Always show nav if more than one image
                const show = detailImages.length > 1;
                document.getElementById('prevBtn').style.display = show ? 'block' : 'none';
                document.getElementById('nextBtn').style.display = show ? 'block' : 'none';
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (document.getElementById('imgModal').classList.contains('hidden')) return;
                if (e.key === 'Escape') return closeModal();
                if (e.key === 'ArrowLeft') return prevImage();
                if (e.key === 'ArrowRight') return nextImage();
            });

            // Close modal when clicking outside image
            document.getElementById('imgModal').addEventListener('click', function(e) {
                // if clicked backdrop (not the image or nav buttons)
                if (e.target === this) closeModal();
            });

            // Touch swipe support for mobile
            (function() {
                let startX = 0;
                let endX = 0;
                const modal = document.getElementById('imgModal');
                modal.addEventListener('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                }, {passive:true});
                modal.addEventListener('touchend', function(e) {
                    endX = e.changedTouches[0].clientX;
                    const diff = endX - startX;
                    if (Math.abs(diff) > 40) {
                        if (diff > 0) prevImage(); else nextImage();
                    }
                });
            })();
            </script>
        <?php else: ?>
            <div class="text-center py-24">
                <h2 class="text-2xl text-white font-bold mb-4">Project Not Found</h2>
                <a href="<?php echo $type === 'progress' ? 'progress.php' : 'completed.php'; ?>" class="text-white underline">&lt; BACK TO PORTFOLIO</a>
            </div>
        <?php endif; ?>
        <div class="mt-8">
            <a href="<?php echo $type === 'progress' ? 'progress.php' : 'completed.php'; ?>" class="text-white underline">&lt; BACK TO PORTFOLIO</a>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>

