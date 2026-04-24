<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Edit Project";
require_once 'includes/header.php';
require_login();

// Get project id and type from query string
$project_id = isset($_GET['id']) ? $_GET['id'] : null;
$type = isset($_GET['type']) ? $_GET['type'] : 'completed';

// Validate type
if ($type !== 'completed' && $type !== 'progress') {
    $type = 'completed';
}

$project = null;
$detail_images = [];
$details_dir = '';
$error_message = '';
$success_message = '';

if ($project_id) {
    // Build project path
    $project_path = '../assets/projects/' . $type . '/' . $project_id . '/';
    $metadata_file = $project_path . 'metadata.json';
    
    // Load project metadata
    if (file_exists($metadata_file)) {
        $project = json_decode(file_get_contents($metadata_file), true);
    }

    if ($project) {
        $details_dir = $project_path . 'details/';
        if (is_dir($details_dir)) {
            $images = array_diff(scandir($details_dir), ['.', '..']);
            natsort($images);
            foreach ($images as $img) {
                $detail_images[] = $img;
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $project) {
    // Handle bulk delete of selected detail images
    if (isset($_POST['form_action']) && $_POST['form_action'] === 'delete_images' && !empty($_POST['delete_images'])) {
        $details_dir = $project_path . 'details/';
        $deleted = 0;
        foreach ($_POST['delete_images'] as $dimg) {
            $fn = basename($dimg);
            $fp = $details_dir . $fn;
            if (is_file($fp)) {
                @unlink($fp);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            $project['detail_count'] = max(0, ($project['detail_count'] ?? 0) - $deleted);
            @file_put_contents($metadata_file, json_encode($project, JSON_PRETTY_PRINT));
        }
        header('Location: edit_project.php?id=' . urlencode($project_id) . '&type=' . urlencode($type) . '&deleted_images=' . $deleted);
        exit();
    }
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $set_cover_image = isset($_POST['set_cover_image']) ? basename($_POST['set_cover_image']) : '';
    
    if (empty($title)) {
        $error_message = 'Project title is required';
    } else {
        try {
            // Update metadata
            $project['title'] = $title;
            $project['location'] = $location;
            $project['description'] = $description;
            
            // Handle new images if uploaded
            if (!empty($_FILES['new_images']['name'][0])) {
                $details_dir = $project_path . 'details/';
                if (!is_dir($details_dir)) {
                    mkdir($details_dir, 0755, true);
                }
                
                // Get current highest image number
                $existing_images = array_diff(scandir($details_dir), ['.', '..']);
                $next_number = count($existing_images) + 1;
                
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                for ($i = 0; $i < count($_FILES['new_images']['name']); $i++) {
                    if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['new_images']['name'][$i],
                            'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                            'size' => $_FILES['new_images']['size'][$i]
                        ];
                        
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        
                        if (!in_array($ext, $allowed_types)) {
                            continue;
                        }
                        if ($file['size'] > 5 * 1024 * 1024) {
                            continue;
                        }
                        
                        $new_filename = $next_number . '.' . $ext;
                        $new_path = $details_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $new_path)) {
                            $project['detail_count']++;
                            $next_number++;
                        }
                    }
                }
            }
            
            // Update cover image if provided
            if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $cover_file = $_FILES['cover_image'];
                $ext = strtolower(pathinfo($cover_file['name'], PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed_types) && $cover_file['size'] <= 5 * 1024 * 1024) {
                    // Delete old cover image
                    $old_cover = $project_path . $project['cover_image'];
                    if (file_exists($old_cover)) {
                        unlink($old_cover);
                    }
                    
                    // Save new cover image
                    $new_cover_name = 'cover.' . $ext;
                    $new_cover_path = $project_path . $new_cover_name;
                    
                    if (move_uploaded_file($cover_file['tmp_name'], $new_cover_path)) {
                        $project['cover_image'] = $new_cover_name;
                    }
                }
            }

            // Set cover image from existing detail image
            if (empty($_FILES['cover_image']['name']) && !empty($set_cover_image) && !empty($details_dir)) {
                $source_path = $details_dir . $set_cover_image;
                $ext = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (is_file($source_path) && in_array($ext, $allowed_types)) {
                    $old_cover = $project_path . $project['cover_image'];
                    if (file_exists($old_cover)) {
                        unlink($old_cover);
                    }

                    $new_cover_name = 'cover.' . $ext;
                    $new_cover_path = $project_path . $new_cover_name;

                    if (copy($source_path, $new_cover_path)) {
                        $project['cover_image'] = $new_cover_name;
                    }
                }
            }
            
            // Save updated metadata
            if (file_put_contents($metadata_file, json_encode($project, JSON_PRETTY_PRINT))) {
                $success_message = 'Project updated successfully!';
                // Reload project data
                $project = json_decode(file_get_contents($metadata_file), true);
            } else {
                $error_message = 'Failed to save project metadata';
            }
        } catch (Exception $e) {
            error_log("Edit project error: " . $e->getMessage());
            $error_message = 'An error occurred while updating the project';
        }
    }
}

?>

<hr class="border-t border-[#44444E]/30 my-0">
<div class="min-h-screen bg-[#040D12] max-w-10xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-white">
    <?php if ($project): ?>
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo $type === 'progress' ? 'manage_progress.php' : 'manage_completed.php'; ?>" class="text-[#5C8374] hover:text-[#D3DAD9] mb-4 inline-block">
                ← Back to Projects
            </a>
        </div>
        
        <!-- Error Message -->
        <?php if ($error_message): ?>
            <div class="bg-[#715A5A]/30 border-l-4 border-[#715A5A] p-4 mb-6 rounded">
                <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Success Message -->
        <?php if ($success_message): ?>
            <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
                <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Edit Form -->
        <div class="bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#44444E] text-[#D3DAD9]">
            <form id="editProjectForm" action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" id="set_cover_image" name="set_cover_image" value="">
                <input type="hidden" id="form_action" name="form_action" value="">
                
                <!-- Project Title -->
                <div>
                    <label for="title" class="block font-semibold mb-2 text-[#5C8374]">Project Title *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?php echo htmlspecialchars($project['title']); ?>"
                        class="w-full border border-[#44444E] rounded px-4 py-3 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition" 
                        required
                    >
                </div>
                
                
                
                <!-- Project Location -->
                <div>
                    <label for="location" class="block font-semibold mb-2 text-[#5C8374]">Project Location</label>
                    <input 
                        type="text" 
                        id="location" 
                        name="location" 
                        value="<?php echo htmlspecialchars($project['location'] ?? ''); ?>"
                        class="w-full border border-[#44444E] rounded px-4 py-3 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition" 
                    >
                </div>
                
                <!-- Project Description -->
                <div>
                    <label for="description" class="block font-semibold mb-2 text-[#5C8374]">Project Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="w-full border border-[#44444E] rounded px-4 py-3 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition"
                    ><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                </div>
                
                <!-- Current Cover Image -->
                <div>
                    <label class="block font-semibold mb-2 text-[#5C8374]">Current Cover Image</label>
                    <div class="bg-[#040D12] rounded p-4 border border-[#44444E]">
                        <img 
                            src="<?php echo htmlspecialchars('../assets/projects/' . $type . '/' . $project_id . '/' . $project['cover_image']); ?>" 
                            alt="Cover image"
                            class="max-w-xs rounded"
                        >
                    </div>
                </div>

                <!-- Detail Images (Set as Cover) -->
                <div>
                    <label class="block font-semibold mb-2 text-[#5C8374]">Detail Images</label>
                    <?php if (empty($detail_images)): ?>
                        <div class="bg-[#040D12] rounded p-4 border border-[#44444E]">
                            <p class="text-[#5C8374]/70 text-sm">No detail images uploaded yet.</p>
                        </div>
                    <?php else: ?>
                                        <div class="mb-3 flex items-center justify-end">
                                            <div>
                                                <button type="button" id="deleteSelectedBtn" disabled class="inline-block bg-[#715A5A] text-white px-3 py-1 rounded text-sm hover:bg-[#8b6b6b] transition opacity-50">Delete Selected</button>
                                                <button type="button" id="deleteSelectedBtnMobile" disabled class="md:hidden ml-2 bg-[#715A5A] text-white px-3 py-1 rounded text-sm hover:bg-[#8b6b6b] transition opacity-50">Delete</button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <?php foreach ($detail_images as $idx => $img): ?>
                                                <div class="relative bg-[#040D12] rounded p-2 border border-[#44444E]">
                                                    <label class="absolute top-2 left-2 z-10">
                                                        <input type="checkbox" name="delete_images[]" value="<?php echo htmlspecialchars($img); ?>" class="detail-select h-4 w-4">
                                                    </label>
                                                    <img 
                                                        src="<?php echo htmlspecialchars('../assets/projects/' . $type . '/' . $project_id . '/details/' . $img); ?>" 
                                                        alt="Detail image"
                                                        data-index="<?php echo $idx; ?>"
                                                        class="detail-thumb w-full h-28 object-cover rounded cursor-pointer"
                                                    >
                                                    <button 
                                                        type="button"
                                                        class="w-full mt-2 bg-[#5C8374] text-[#FFFFFF] px-2 py-1 rounded text-xs hover:bg-[#183D3D] hover:text-[#5C8374] transition"
                                                        onclick="setCoverImage('<?php echo htmlspecialchars($img); ?>')"
                                                    >
                                                        Set as Cover
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                    <?php endif; ?>
                    <p class="text-[#5C8374]/70 text-sm mt-2">Pick any detail image to become the cover.</p>
                </div>
                
                <!-- Replace Cover Image -->
                <div>
                    <label for="cover_image" class="block font-semibold mb-2 text-[#5C8374]">Replace Cover Image (Optional)</label>
                    <input 
                        type="file" 
                        id="cover_image" 
                        name="cover_image" 
                        accept="image/*" 
                        class="w-full text-[#D3DAD9]"
                    >
                    <p class="text-[#5C8374]/70 text-sm mt-2">Leave blank to keep the current image (Max 5MB)</p>
                </div>
                
                <!-- Add New Images -->
                <div>
                    <label for="new_images" class="block font-semibold mb-2 text-[#5C8374]">Add New Detail Images</label>
                    <input 
                        type="file" 
                        id="new_images" 
                        name="new_images[]" 
                        accept="image/*" 
                        multiple
                        class="w-full text-[#D3DAD9]"
                    >
                    <p class="text-[#5C8374]/70 text-sm mt-2">Upload additional images (Each max 5MB). Current: <?php echo $project['detail_count']; ?> images</p>
                </div>
                
                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-[#5C8374] text-[#FFFFFF] px-6 py-3 rounded font-semibold hover:bg-[#183D3D] hover:text-[#5C8374] transition mt-8"
                >
                    Save Changes
                </button>
            </form>
        </div>

        <script>
        function setCoverImage(filename) {
            document.getElementById('set_cover_image').value = filename;
            document.getElementById('editProjectForm').submit();
        }
        </script>
        <script>
        // Lightbox viewer for detail images
        (function(){
            var images = <?php echo json_encode(array_values($detail_images)); ?> || [];
            var basePath = <?php echo json_encode('../assets/projects/' . $type . '/' . $project_id . '/details/'); ?>;
            var currentIndex = 0;

            // Create lightbox HTML
            var lb = document.createElement('div');
            lb.id = 'lightbox';
            lb.className = 'hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50';
            lb.innerHTML = '\n                <div class="relative max-w-5xl max-h-[90vh] w-full px-4">\n                    <button id="lbClose" class="absolute top-2 right-2 text-white text-2xl opacity-90">&times;</button>\n                    <img id="lightboxImage" src="" alt="" class="mx-auto max-h-[80vh] object-contain rounded"/>\n                    <button id="lbPrev" class="absolute left-2 top-1/2 -translate-y-1/2 text-white text-3xl p-2">&#10094;</button>\n                    <button id="lbNext" class="absolute right-2 top-1/2 -translate-y-1/2 text-white text-3xl p-2">&#10095;</button>\n                </div>';
            document.body.appendChild(lb);

            var lbOverlay = document.getElementById('lightbox');
            var lbImg = document.getElementById('lightboxImage');
            var lbPrev = document.getElementById('lbPrev');
            var lbNext = document.getElementById('lbNext');
            var lbClose = document.getElementById('lbClose');

            function openLightbox(idx){
                if (!images || images.length === 0) return;
                currentIndex = (idx + images.length) % images.length;
                lbImg.src = basePath + images[currentIndex];
                lbOverlay.classList.remove('hidden');
            }
            function closeLightbox(){
                lbOverlay.classList.add('hidden');
                lbImg.src = '';
            }
            function showIndex(idx){
                currentIndex = (idx + images.length) % images.length;
                lbImg.src = basePath + images[currentIndex];
            }

            // Attach click handlers to thumbs
            document.querySelectorAll('.detail-thumb').forEach(function(el){
                el.addEventListener('click', function(e){
                    var idx = parseInt(el.getAttribute('data-index') || '0', 10);
                    openLightbox(idx);
                });
            });

            lbPrev.addEventListener('click', function(e){ e.stopPropagation(); showIndex(currentIndex - 1); });
            lbNext.addEventListener('click', function(e){ e.stopPropagation(); showIndex(currentIndex + 1); });
            lbClose.addEventListener('click', function(e){ e.stopPropagation(); closeLightbox(); });
            lbOverlay.addEventListener('click', function(e){ if (e.target === lbOverlay) closeLightbox(); });

            document.addEventListener('keydown', function(e){
                if (lbOverlay.classList.contains('hidden')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') showIndex(currentIndex - 1);
                if (e.key === 'ArrowRight') showIndex(currentIndex + 1);
            });
        })();
        </script>
        <script>
        // Bulk-select detail images and delete selected
        (function(){
            var selects = Array.from(document.querySelectorAll('.detail-select'));
            var deleteBtn = document.getElementById('deleteSelectedBtn');
            var deleteBtnMobile = document.getElementById('deleteSelectedBtnMobile');
            var form = document.getElementById('editProjectForm');
            var actionInput = document.getElementById('form_action');

            function updateButtons(){
                var any = selects.some(function(s){ return s.checked; });
                if (deleteBtn) {
                    deleteBtn.disabled = !any;
                    deleteBtn.style.opacity = any ? '1' : '0.5';
                }
                if (deleteBtnMobile) {
                    deleteBtnMobile.disabled = !any;
                    deleteBtnMobile.style.opacity = any ? '1' : '0.5';
                }
            }

            selects.forEach(function(s){ s.addEventListener('change', updateButtons); });

            function doDelete(){
                if (!confirm('Delete selected images? This cannot be undone.')) return;
                actionInput.value = 'delete_images';
                form.submit();
            }

            if (deleteBtn) deleteBtn.addEventListener('click', doDelete);
            if (deleteBtnMobile) deleteBtnMobile.addEventListener('click', doDelete);
        })();
        </script>
    <?php else: ?>
        <div class="text-center py-24">
            <h2 class="text-2xl text-[#5C8374] font-bold mb-4">Project Not Found</h2>
            <a href="manage_completed.php" class="text-[#5C8374] underline">← Back to Projects</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
