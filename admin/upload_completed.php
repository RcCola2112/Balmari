<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Upload Completed Work";
require_once 'includes/header.php';
require_login();

$success_message = '';
$error_message = '';

// Check for messages from redirect
if (isset($_GET['success'])) {
    $success_message = 'Project uploaded successfully!';
}
if (isset($_GET['error'])) {
    $error_message = isset($_SESSION['upload_error']) ? $_SESSION['upload_error'] : 'An error occurred during upload';
    unset($_SESSION['upload_error']);
}

?>

<section class="bg-[#183D3D] text-[#D3DAD9] py-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4 text-center">Upload Completed Work</h1>
    </div>
</section>
<hr class="border-t border-[#44444E]/30 my-0">
<div class="min-h-screen bg-[#040D12] max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-[#D3DAD9]">
    
    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($success_message); ?></p>
            <p class="text-[#5C8374]/80 text-sm mt-2">
                <a href="dashboard.php" class="underline hover:no-underline text-[#5C8374]">← Back to Dashboard</a>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Error Message -->
    <?php if ($error_message): ?>
        <div class="bg-[#715A5A]/30 border-l-4 border-[#715A5A] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Upload Form -->
    <div class="bg-[#183D3D] rounded-2xl shadow-xl p-8 border border-[#5C8374] text-[#D3DAD9]">
        <form action="upload_completed_process.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <!-- Project Title -->
            <div>
                <label for="title" class="block font-semibold mb-2 text-[#5C8374]">Project Title *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="w-full border border-[#44444E] rounded px-4 py-3 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition" 
                    placeholder="e.g., Beautiful Residential Complex"
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
                    class="w-full border border-[#44444E] rounded px-4 py-3 bg-[#040D12] text-[#D3DAD9] placeholder:text-[#5C8374]/50 focus:ring-2 focus:ring-[#5C8374] outline-none transition" 
                    placeholder="e.g., Quezon City, Metro Manila"
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
                    placeholder="Describe the project, its features, and highlights..."
                ></textarea>
            </div>
            
            <!-- Cover Image -->
            <div>
                <label for="cover_image" class="block font-semibold mb-2 text-[#5C8374]">Cover Image (Featured Photo) *</label>
                <input 
                    type="file" 
                    id="cover_image" 
                    name="cover_image" 
                    accept="image/*" 
                    class="w-full text-[#D3DAD9]" 
                    required
                >
                <p class="text-[#5C8374]/70 text-sm mt-2">This image will be displayed as the project thumbnail in the gallery</p>
            </div>
            
            <!-- Additional Images -->
            <div>
                <label for="detail_images" class="block font-semibold mb-2 text-[#5C8374]">Additional Detail Images</label>
                <input 
                    type="file" 
                    id="detail_images" 
                    name="detail_images[]" 
                    accept="image/*" 
                    multiple
                    class="w-full text-[#D3DAD9]"
                >
                <p class="text-[#5C8374]/70 text-sm mt-2">Upload additional detail images to show full project gallery</p>
            </div>
            
            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition mt-8"
            >
                Upload Project
            </button>
        </form>
    </div>
    
    <!-- Info Box -->
    <div class="mt-8 p-4 bg-[#183D3D] border border-[#5C8374] rounded">
        <p class="text-[#5C8374]/80 text-sm">
            <strong>Note:</strong> Please upload photos showing the completed project prominently. High-quality images recommended. Maximum file size is 5MB.
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
