<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Works in Progress";
include 'includes/header.php';

// Load progress projects from filesystem
$projects_dir = 'assets/projects/progress/';
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
                $progress_projects[] = $metadata;
            }
        }
    }
}
?>
<section class="bg-[#183D3D] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4 text-center">Works in Progress</h1>
    </div>
</section>
<hr class="border-t border-[#44444E]/30 my-0">
<section class="min-h-screen bg-[#040D12] flex flex-col items-center pt-12 pb-8 px-2">
    <div class="w-full max-w-[95vw] md:max-w-[90vw] lg:max-w-[1400px] mx-auto px-2 md:px-4">
        <div class="w-full h-1 mb-8 flex justify-center">
            <span class="block w-24 h-1 rounded bg-gradient-to-r from-[#715A5A] via-[#715A5A] to-[#44444E]"></span>
        </div>
        
        <?php if (empty($progress_projects)): ?>
            <!-- Empty State -->
            <div class="text-center py-20">
                <p class="text-[#D3DAD9] text-lg">No projects in progress yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <!-- Projects Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($progress_projects as $project): ?>
                    <a href="project_details.php?id=<?php echo urlencode($project['project_id']); ?>&type=progress" class="group cursor-pointer">
                        <div class="relative overflow-hidden rounded-lg shadow-lg h-64 bg-[#44444E]">
                            <!-- Cover Image -->
                            <img 
                                src="<?php echo htmlspecialchars($project['cover_image_url']); ?>" 
                                alt="<?php echo htmlspecialchars($project['title']); ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            >
                            
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#37353E] via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                <h3 class="text-[#D3DAD9] font-['Playfair_Display'] font-semibold text-lg"><?php echo htmlspecialchars($project['title']); ?></h3>
                                
                                <?php if (!empty($project['location'])): ?>
                                    <p class="text-[#D3DAD9] text-xs mt-1"><?php echo htmlspecialchars($project['location']); ?></p>
                                <?php endif; ?>
                                <p class="text-[#D3DAD9] text-xs mt-2 hover:text-[#715A5A]">View Details →</p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>

