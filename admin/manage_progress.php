<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Project in Progress";
require_once 'includes/header.php';

// Load progress projects from filesystem
$projects_dir = __DIR__ . '/../assets/projects/progress/';
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
                $metadata['cover_image_url'] = '../assets/projects/progress/' . $folder . '/' . ($metadata['cover_image'] ?? '');
                $metadata['project_path'] = $project_path;
                $progress_projects[] = $metadata;
            }
        }
    }
}

// Handle delete action (POST)
if (isset($_POST['delete_project']) && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];
    $project_path = $projects_dir . $project_id . '/';
    
    $deleteDirectory = function($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . $file;
            if (is_dir($path)) {
                $deleteDirectory($path . '/');
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    };
    
    if (is_dir($project_path)) {
        $deleteDirectory($project_path);
        header('Location: manage_progress.php?deleted=1');
        exit();
    }
}

?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#FFFFFF]">Project in Progress</h1>
        <a href="upload_progress.php" class="bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition">+ Add New Project</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium">Project deleted successfully</p>
        </div>
    <?php endif; ?>

    <?php if (empty($progress_projects)): ?>
        <div class="text-center py-20">
            <p class="text-[#D3DAD9] text-lg mb-6">No projects in progress yet</p>
            <a href="upload_progress.php" class="inline-block bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition">Upload Your First Project</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($progress_projects as $project): ?>
                <div class="bg-[#183D3D] rounded-lg shadow-lg overflow-hidden border border-[#44444E] hover:border-[#5C8374] transition">
                    <div class="relative h-48 bg-[#183D3D] overflow-hidden">
                        <img src="<?php echo htmlspecialchars($project['cover_image_url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                        <div class="absolute top-0 right-0 bg-[#5C8374] text-[#183D3D] px-3 py-1 text-sm font-semibold m-2 rounded"><?php echo htmlspecialchars($project['detail_count'] ?? '0'); ?> images</div>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-[#FFFFFF] mb-2"><?php echo htmlspecialchars($project['title']); ?></h3>
                        <?php if (!empty($project['location'])): ?>
                            <p class="text-[#5C8374] text-sm mb-3">📍 <?php echo htmlspecialchars($project['location']); ?></p>
                        <?php endif; ?>
                        <p class="text-[#5C8374]/80 text-xs mb-4">Created: <?php echo date('M d, Y', strtotime($project['created_at'] ?? date('Y-m-d H:i:s'))); ?></p>
                        <div class="flex gap-2">
                            <a href="../project_details.php?id=<?php echo urlencode($project['project_id']); ?>&type=progress" class="flex-1 bg-[#5C8374] text-[#183D3D] px-3 py-2 rounded text-sm font-medium hover:bg-[#44444E] hover:text-[#FFFFFF] transition text-center">View</a>
                            <a href="edit_project.php?id=<?php echo urlencode($project['project_id']); ?>&type=progress" class="flex-1 bg-[#44444E] text-[#FFFFFF] px-3 py-2 rounded text-sm font-medium hover:bg-[#5C8374] hover:text-[#183D3D] transition text-center">Edit</a>
                            <form method="POST" class="flex-1">
                                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['project_id']); ?>">
                                <button type="submit" name="delete_project" class="w-full bg-[#715A5A]/50 text-[#FFFFFF] px-3 py-2 rounded text-sm font-medium hover:bg-[#715A5A] hover:text-[#FFFFFF] transition" onclick="return confirm('Delete this project? This cannot be undone.');">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
