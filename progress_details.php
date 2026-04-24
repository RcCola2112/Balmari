<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// progress_details.php
$page_title = "Project In Progress Details";
include 'includes/header.php';

// Get project id from query string
$projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Example: Replace with real data source (CSV, DB, etc.)
$projects = [
    1 => [
        'title' => 'Luxury Pool Villa',
        'type' => 'Resort',
        'location' => 'Makati City',
        'description' => 'This luxury pool villa is currently under construction, blending modern design with tropical elements. The project aims to create a relaxing retreat with seamless indoor-outdoor living spaces.',
        'images' => [
            'assets/images/633871533_880323638229977_385927937879935407_n.jpg',
            'assets/images/633871533_880323638229977_385927937879935407_n.jpg',
            'assets/images/633871533_880323638229977_385927937879935407_n.jpg',
        ]
    ],
    // Add more projects as needed
];

$project = $projects[$projectId] ?? null;
?>
<section class="min-h-screen bg-[#37353E] flex flex-col items-center pt-12 pb-8 px-2">
    <div class="w-full max-w-[95vw] md:max-w-[90vw] lg:max-w-[1100px] mx-auto px-2 md:px-4">
        <?php if ($project): ?>
            <div class="mb-8 text-left">
                
                <h1 class="text-3xl md:text-5xl font-['Playfair_Display'] font-semibold mb-2 tracking-wide text-[#715A5A]" style="letter-spacing:0.08em;">
                    <?php echo htmlspecialchars($project['title']); ?>
                </h1>
                <h2 class="text-lg md:text-2xl font-semibold text-[#715A5A] mb-2"><?php echo htmlspecialchars($project['location']); ?></h2>
                <p class="text-[#D3DAD9] max-w-2xl mb-8"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($project['images'] as $img): ?>
                    <div class="bg-white rounded-lg shadow p-2 flex items-center justify-center">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="w-full h-auto object-contain rounded-lg cursor-pointer transition hover:opacity-80" onclick="openModal('<?php echo htmlspecialchars($img); ?>')" />
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Fullscreen Modal -->
            <div id="imgModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 hidden">
                <span class="absolute top-6 right-8 text-4xl text-[#D3DAD9] cursor-pointer select-none" onclick="closeModal()">&times;</span>
                <img id="modalImg" src="" alt="Project Image" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl border-4 border-[#715A5A]" />
            </div>
            <script>
            function openModal(src) {
                document.getElementById('modalImg').src = src;
                document.getElementById('imgModal').classList.remove('hidden');
            }
            function closeModal() {
                document.getElementById('imgModal').classList.add('hidden');
                document.getElementById('modalImg').src = '';
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeModal();
            });
            document.getElementById('imgModal').addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
            </script>
        <?php else: ?>
            <div class="text-center py-24">
                <h2 class="text-2xl text-[#715A5A] font-bold mb-4">Project Not Found</h2>
                <a href="progress.php" class="text-[#715A5A] underline">&lt; Back to Portfolio</a>
            </div>
        <?php endif; ?>
        <div class="mt-8">
            <a href="progress.php" class="text-[#715A5A] underline">&lt; BACK TO PORTFOLIO</a>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>

