<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Project in Progress";
require_once '../includes/db.php';
require_once 'includes/auth.php';

requireLogin();

// Load progress projects from filesystem
$projects_dir = '../assets/projects/progress/';
$progress_projects = [];

if (is_dir($projects_dir)) {
    $project_folders = array_diff(scandir($projects_dir, SCANDIR_SORT_DESCENDING), ['.', '..']);
    
    foreach ($project_folders as $folder) {
        $project_path = $projects_dir . $folder . '/';
        $metadata_file = $project_path . 'metadata.json';
        
        if (file_exists($metadata_file)) {
            $metadata = json_decode(file_get_contents($metadata_file), true);
            if ($metadata) {
                $metadata['project_id'] = $folder;
                $metadata['cover_image_url'] = $project_path . $metadata['cover_image'];
                $metadata['project_path'] = $project_path;
                $progress_projects[] = $metadata;
            }
        }
    }
}

// Handle delete action
if (isset($_POST['delete_project']) && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];
    $project_path = $projects_dir . $project_id . '/';
    
    // Recursive delete directory
    function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . $file;
            if (is_dir($path)) {
                deleteDirectory($path . '/');
                rmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    if (is_dir($project_path)) {
        deleteDirectory($project_path);
        header('Location: manage_progress.php?deleted=1');
        exit();
    }
}

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Page Header with Add Button -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#FFFFFF]">
            Project in Progress
        </h1>
        <button onclick="openAddModal()" class="bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition">
            + Add New Project
        </button>
    </div>
    
    <!-- Success Message -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium">Project deleted successfully</p>
        </div>
    <?php endif; ?>
    
    <!-- Projects List -->
    <?php if (empty($progress_projects)): ?>
        <div class="text-center py-20">
            <p class="text-[#D3DAD9] text-lg mb-6">No projects in progress yet</p>
            <a href="upload_progress.php" class="inline-block bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition">
                Upload Your First Project
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($progress_projects as $project): ?>
                <div class="bg-[#183D3D] rounded-lg shadow-lg overflow-hidden border border-[#44444E] hover:border-[#5C8374] transition">
                    <!-- Project Image -->
                    <div class="relative h-48 bg-[#183D3D] overflow-hidden">
                        <img 
                            src="<?php echo htmlspecialchars($project['cover_image_url']); ?>" 
                            alt="<?php echo htmlspecialchars($project['title']); ?>"
                            class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                        >
                        <div class="absolute top-0 right-0 bg-[#5C8374] text-[#183D3D] px-3 py-1 text-sm font-semibold m-2 rounded">
                            <?php echo htmlspecialchars($project['detail_count']); ?> images
                        </div>
                    </div>
                    
                    <!-- Project Info -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#FFFFFF] mb-2">
                            <?php echo htmlspecialchars($project['title']); ?>
                        </h3>
                        
                        <?php if (!empty($project['location'])): ?>
                            <p class="text-[#5C8374] text-sm mb-3">
                                📍 <?php echo htmlspecialchars($project['location']); ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-[#5C8374]/80 text-xs mb-4">
                            Created: <?php echo date('M d, Y', strtotime($project['created_at'])); ?>
                        </p>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <a href="view_project.php?id=<?php echo urlencode($project['project_id']); ?>&type=progress" class="flex-1 bg-[#5C8374] text-[#183D3D] px-3 py-2 rounded text-sm font-medium hover:bg-[#44444E] hover:text-[#FFFFFF] transition text-center">
                                View
                            </a>
                            <a href="edit_project.php?id=<?php echo urlencode($project['project_id']); ?>&type=progress" class="flex-1 bg-[#44444E] text-[#FFFFFF] px-3 py-2 rounded text-sm font-medium hover:bg-[#5C8374] hover:text-[#183D3D] transition text-center">
                                Edit
                            </a>
                            <form method="POST" class="flex-1">
                                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['project_id']); ?>">
                                <button type="submit" name="delete_project" class="w-full bg-[#715A5A]/50 text-[#FFFFFF] px-3 py-2 rounded text-sm font-medium hover:bg-[#715A5A] hover:text-[#FFFFFF] transition" onclick="return confirm('Delete this project? This cannot be undone.');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Project Modal -->
<div id="addModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-[#183D3D] rounded-xl shadow-2xl border border-[#44444E] max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
            <div class="sticky top-0 flex justify-between items-center p-6 border-b border-[#44444E] bg-[#183D3D]">
            <h2 class="text-2xl font-['Playfair_Display'] font-semibold text-[#FFFFFF]">Add New Project</h2>
            <button onclick="closeAddModal()" class="text-3xl text-[#FFFFFF] hover:text-[#5C8374] transition">&times;</button>
        </div>
        
        <!-- Modal Content -->
        <div class="p-6 text-[#D3DAD9]">
            <form action="upload_progress_process.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                
                <!-- Project Title -->
                <div>
                    <label for="modal_title" class="block font-semibold mb-2 text-[#5C8374] text-sm">Project Title *</label>
                    <input 
                        type="text" 
                        id="modal_title" 
                        name="title" 
                        class="w-full border border-[#44444E] rounded px-4 py-2 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition text-sm" 
                        placeholder="e.g., New Commercial Building"
                        required
                    >
                </div>
                
                
                
                <!-- Project Location -->
                <div>
                    <label for="modal_location" class="block font-semibold mb-2 text-[#5C8374] text-sm">Project Location</label>
                    <input 
                        type="text" 
                        id="modal_location" 
                        name="location" 
                        class="w-full border border-[#44444E] rounded px-4 py-2 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition text-sm" 
                        placeholder="e.g., Makati City, Metro Manila"
                    >
                </div>
                
                <!-- Project Description -->
                <div>
                    <label for="modal_description" class="block font-semibold mb-2 text-[#5C8374] text-sm">Project Description</label>
                    <textarea 
                        id="modal_description" 
                        name="description" 
                        rows="3"
                        class="w-full border border-[#44444E] rounded px-4 py-2 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition text-sm" 
                        placeholder="Describe the project, its current progress, and upcoming phases..."
                    ></textarea>
                </div>
                
                <!-- Cover Image -->
                <div>
                    <label for="modal_cover_image" class="block font-semibold mb-2 text-[#5C8374] text-sm">Cover Image *</label>
                    <input 
                        type="file" 
                        id="modal_cover_image" 
                        name="cover_image" 
                        accept="image/*" 
                        class="w-full text-[#D3DAD9] text-sm" 
                        required
                    >
                    <p class="text-[#5C8374]/70 text-xs mt-1">Featured photo (Max 5MB)</p>
                </div>
                
                <!-- Additional Images -->
                <div>
                    <label for="modal_detail_images" class="block font-semibold mb-2 text-[#5C8374] text-sm">Additional Images</label>
                    <input 
                        type="file" 
                        id="modal_detail_images" 
                        name="detail_images[]" 
                        accept="image/*" 
                        multiple
                        class="w-full text-[#D3DAD9] text-sm"
                    >
                    <p class="text-[#5C8374]/70 text-xs mt-1">Progress stage images (Each max 5MB)</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        onclick="closeAddModal()"
                        class="flex-1 border border-[#44444E] text-[#5C8374] px-4 py-2 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 bg-[#5C8374] text-[#183D3D] px-4 py-2 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition"
                    >
                        Upload Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}
// Close modal when clicking outside
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});
// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddModal();
});
</script>

<?php include 'includes/footer.php'; ?>

