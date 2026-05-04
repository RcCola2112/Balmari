<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "View Project";
require_once '../includes/db.php';
require_once 'includes/auth.php';

requireLogin();

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
    $project_path = '../assets/projects/' . $type . '/' . $project_id . '/';
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
                sort($images); // Sort numerically
                foreach ($images as $img) {
                    $detail_images[] = $details_dir . $img;
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if ($project): ?>
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo $type === 'progress' ? 'manage_progress.php' : 'manage_completed.php'; ?>" class="text-[#5C8374] hover:text-[#93B1A6] mb-4 inline-block transition">
                ← Back to Projects
            </a>
            <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#D3DAD9] mb-2">
                <?php echo htmlspecialchars($project['title']); ?>
            </h1>
            <div class="flex gap-4 items-center text-sm text-[#93B1A6] flex-wrap">
                <?php if (!empty($project['location'])): ?>
                    <span>📍 <?php echo htmlspecialchars($project['location']); ?></span>
                <?php endif; ?>
                <span>Created: <?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
            </div>
        </div>
        
        <!-- Project Description -->
        <?php if (!empty($project['description'])): ?>
            <div class="bg-[#183D3D] rounded-2xl p-6 mb-8 border border-[#5C8374]/30 shadow-lg">
                <h2 class="text-2xl font-semibold text-[#93B1A6] mb-4">Description</h2>
                <p class="text-[#D3DAD9] leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Images Gallery -->
        <div class="bg-[#183D3D] rounded-2xl p-6 border border-[#5C8374]/30 shadow-lg">
            <h2 class="text-2xl font-semibold text-[#93B1A6] mb-6">Project Images (<?php echo count($detail_images); ?>)</h2>
            
            <?php if (empty($detail_images)): ?>
                <p class="text-[#93B1A6]">No images available for this project</p>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($detail_images as $idx => $img): ?>
                        <div class="relative overflow-hidden rounded-lg bg-[#040D12] cursor-pointer group border border-[#5C8374]/20" onclick="openModal('<?php echo htmlspecialchars($img); ?>')">
                            <img 
                                src="<?php echo htmlspecialchars($img); ?>" 
                                alt="Project image <?php echo $idx + 1; ?>"
                                class="w-full h-40 object-cover group-hover:scale-110 transition-transform duration-300"
                            >
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/35 transition-colors duration-300 flex items-center justify-center">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity">View</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Image Modal -->
            <div id="imgModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 hidden backdrop-blur-sm">
            <span class="absolute top-6 right-8 text-4xl text-[#D3DAD9] cursor-pointer select-none hover:text-[#93B1A6] transition" onclick="closeModal()">&times;</span>
            <img id="modalImg" src="" alt="Project Image" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl border-4 border-[#5C8374]" />
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
            <h2 class="text-2xl text-[#93B1A6] font-bold mb-4">Project Not Found</h2>
            <a href="manage_completed.php" class="text-[#5C8374] underline hover:text-[#93B1A6] transition">← Back to Projects</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

