<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Edit Carousel Image";
require_once '../includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$error_message = '';
$success_message = '';
$carousel_item = null;
$db_error = false;

// Get carousel item ID from URL
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($item_id === 0) {
    header('Location: manage_carousel.php');
    exit();
}

// Fetch carousel item
try {
    $carousel_item = getCarouselItem($item_id);
    
    if (!$carousel_item) {
        $error_message = 'Carousel item not found';
    }
} catch (Exception $e) {
    error_log("Edit carousel fetch error: " . $e->getMessage());
    $db_error = true;
    $error_message = 'Unable to load carousel item. Please try again later.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
    
    if (empty($title)) {
        $error_message = 'Title is required';
    } else {
        try {
            $result = updateCarouselItem($item_id, $title, $subtitle);
            
            if ($result['success']) {
                $success_message = $result['message'];
                // Refresh carousel item
                $carousel_item = getCarouselItem($item_id);
            } else {
                $error_message = $result['message'];
            }
        } catch (Exception $e) {
            error_log("Edit carousel update error: " . $e->getMessage());
            $error_message = 'An error occurred while updating. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="bg-[#183D3D] text-white py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4 text-center">Edit Carousel Image</h1>
    </div>
</section>
<hr class="border-t border-[#44444E]/30 my-0">
<div class="min-h-screen bg-[#040D12] max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-white">
    <!-- Page Header -->
    <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-slate-900 mb-8">
        Edit Carousel Image
    </h1>
    
    <!-- Error Message -->
    <?php if ($error_message): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
            <?php if ($item_id === 0): ?>
                <p class="text-red-600 text-sm mt-2">
                    <a href="manage_carousel.php" class="underline hover:no-underline">Go back to carousel list →</a>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <p class="text-green-700 font-medium"><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>
    
    <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#715A5A] mb-8">
        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <form method="POST">
                <!-- Image Preview -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-slate-900 mb-4">
                        Current Image
                    </label>
                    <div class="bg-gray-100 rounded-lg overflow-hidden">
                        <img 
                            src="../assets/uploads/carousel/<?php echo htmlspecialchars($carousel_item['image']); ?>" 
                            alt="<?php echo htmlspecialchars($carousel_item['title']); ?>"
                            class="w-full h-auto max-h-96 object-cover"
                        >
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        File: <?php echo htmlspecialchars($carousel_item['image']); ?>
                        (Uploaded: <?php echo date('M d, Y \a\t h:i A', strtotime($carousel_item['created_at'])); ?>)
                    </p>
                    <p class="text-xs text-orange-600 mt-2 font-medium">
                        Note: To change the image, delete this item and upload a new one.
                    </p>
                </div>
                
        <div class="bg-[#44444E] rounded-2xl shadow-xl p-8 mb-8 border border-[#715A5A] text-[#D3DAD9]">
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-slate-900 mb-2">
                        Carousel Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        value="<?php echo htmlspecialchars($carousel_item['title']); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="e.g., Modern Home Design"
                    >
                </div>
                
                <!-- Subtitle -->
                <div class="mb-8">
                    <label for="subtitle" class="block text-sm font-medium text-slate-900 mb-2">
                        Carousel Subtitle
                    </label>
                    <textarea 
                        id="subtitle" 
                        name="subtitle" 
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="e.g., Expert Design and Construction Services"
                    ><?php echo htmlspecialchars($carousel_item['subtitle']); ?></textarea>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex space-x-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600 transition"
                    >
                        Save Changes
                    </button>
                    <a 
                        href="manage_carousel.php" 
                        class="flex-1 bg-gray-200 text-slate-900 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Danger Zone -->
        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
            <h3 class="font-bold text-red-900 mb-4">Danger Zone</h3>
            <p class="text-red-800 text-sm mb-4">Delete this carousel item permanently. This action cannot be undone.</p>
            <a 
                href="delete_carousel.php?id=<?php echo $carousel_item['id']; ?>" 
                class="inline-block bg-red-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-600 transition"
                onclick="return confirm('Are you absolutely sure? This will permanently delete this carousel item.');"
            >
                Delete Item
            </a>
        </div>
    <?php endif; ?>
</div>

<body class="bg-[#37353E] text-[#D3DAD9]">
    <div class="max-w-2xl mx-auto mt-12">
        <h1 class="text-2xl font-bold mb-6 font-['Playfair_Display'] text-[#715A5A]">Edit Carousel Item</h1>
        <!-- Edit form here -->

