<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Manage Carousel";
require_once 'includes/auth.php';

requireLogin();

$upload_dir = '../assets/images/carousel/';
$error_message = '';
$success_message = '';

// Handle delete selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_selected') {
    $selected_files = json_decode($_POST['selected_files'] ?? '[]', true);
    if (!is_array($selected_files) || empty($selected_files)) {
        $error_message = 'Please select at least one image to delete';
    } else {
        $deleted_count = 0;
        $base_dir = realpath($upload_dir);

        foreach ($selected_files as $file_name) {
            $safe_name = basename($file_name);
            $file_path = realpath($upload_dir . $safe_name);

            if ($base_dir && $file_path && strpos($file_path, $base_dir) === 0 && is_file($file_path)) {
                if (unlink($file_path)) {
                    $deleted_count++;
                }
            }
        }

        if ($deleted_count > 0) {
            $success_message = $deleted_count . ' carousel image(s) deleted successfully';
        } else {
            $error_message = 'No images were deleted. Please try again.';
        }
    }
}

$carousel_files = glob($upload_dir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
if (!$carousel_files) {
    $carousel_files = [];
}
rsort($carousel_files);

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="mb-6 bg-green-900/30 border-l-4 border-green-500 p-4 rounded">
            <p class="text-green-300 font-medium"><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error_message): ?>
        <div class="mb-6 bg-red-900/30 border-l-4 border-red-500 p-4 rounded">
            <p class="text-red-300 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-white">
                Manage Carousel
            </h1>
            <p class="text-[#5C8374] mt-2">Preview, select, and delete homepage carousel images</p>
        </div>
        <a href="upload_carousel.php" class="bg-[#5C8374] text-[#040D12] px-6 py-3 rounded-lg font-semibold hover:bg-[#44444E] hover:text-white transition">
            + Upload New Image
        </a>
    </div>

    <!-- Carousel Library -->
    <div class="bg-[#22272B] rounded-2xl shadow-xl p-8 border border-[#44444E] text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-white">Carousel Library</h2>
                <p class="text-sm text-[#5C8374]/80">Hold Ctrl (Cmd on Mac) and click images to select.</p>
            </div>
            <form method="POST" onsubmit="return confirmDeleteSelected();" class="flex items-center gap-3">
                <input type="hidden" name="action" value="delete_selected">
                <input type="hidden" name="selected_files" id="selectedFiles" value="[]">
                <span id="selectedCount" class="text-sm text-[#5C8374]/80">0 selected</span>
                <button
                    type="submit"
                    id="deleteSelectedBtn"
                    disabled
                    class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Delete Selected
                </button>
            </form>
        </div>

        <?php if (empty($carousel_files)): ?>
            <div class="text-center py-10">
                <div class="text-5xl mb-4">🖼️</div>
                <p class="text-[#5C8374]">No carousel images yet.</p>
            </div>
        <?php else: ?>
            <div id="carouselGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($carousel_files as $idx => $file_path): ?>
                    <?php
                        $file_name = basename($file_path);
                        $web_path = '../assets/images/carousel/' . $file_name;
                        $file_size = is_file($file_path) ? filesize($file_path) : 0;
                    ?>
                    <button
                        type="button"
                        class="carousel-item group relative rounded-xl overflow-hidden border border-[#44444E]/40 text-left"
                        data-filename="<?php echo htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8'); ?>"
                        data-src="<?php echo htmlspecialchars($web_path, ENT_QUOTES, 'UTF-8'); ?>"
                        data-size="<?php echo (int) $file_size; ?>"
                        data-index="<?php echo (int) $idx; ?>"
                        title="Click to preview • Ctrl/Cmd+click to select"
                    >
                        <img
                            src="<?php echo htmlspecialchars($web_path, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Carousel image"
                            class="w-full h-32 sm:h-36 object-cover"
                        >
                        <div class="absolute inset-0 bg-[#183D3D]/60 opacity-0 group-hover:opacity-100 transition"></div>
                        <div class="absolute top-2 right-2 hidden selected-indicator bg-[#5C8374] text-[#040D12] text-xs font-semibold px-2 py-1 rounded">
                            Selected
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div id="modalBackdrop" class="absolute inset-0 bg-black/70"></div>
        <div class="relative z-10 max-w-5xl w-[90vw]">
            <button
                id="modalClose"
                type="button"
                class="absolute -top-10 right-0 text-white bg-[#22272B] px-3 py-2 rounded-lg hover:bg-[#44444E] transition"
            >
                Close
            </button>
            <div class="bg-[#183D3D] rounded-2xl border border-[#44444E]/40 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 bg-[#22272B]">
                    <div>
                        <p id="modalFilename" class="text-white font-semibold"></p>
                        <p id="modalFilesize" class="text-xs text-[#5C8374]/80"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            id="modalPrev"
                            type="button"
                            class="bg-[#22272B] text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#5C8374] hover:text-[#040D12] transition"
                        >
                            Previous
                        </button>
                        <button
                            id="modalNext"
                            type="button"
                            class="bg-[#22272B] text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#5C8374] hover:text-[#040D12] transition"
                        >
                            Next
                        </button>
                    </div>
                </div>
                <img id="modalImage" src="" alt="Carousel preview" class="w-full h-auto max-h-[80vh] object-contain bg-black">
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mt-8 bg-[#22272B] border-l-4 border-[#5C8374] p-6 rounded">
        <h3 class="font-bold text-[#5C8374] mb-2">Carousel Tips</h3>
        <ul class="text-sm text-white space-y-1 list-disc list-inside">
            <li>Upload high-quality images (recommended size: 1920x600px)</li>
            <li>Supported formats: JPG, PNG, WebP</li>
            <li>Maximum file size: 5MB</li>
            <li>Images are displayed in order of upload date (newest first)</li>
            <li>Use Ctrl/Cmd+click to select multiple images</li>
        </ul>
    </div>
</div>

<script>
const carouselItems = document.querySelectorAll('.carousel-item');
const imageModal = document.getElementById('imageModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalClose = document.getElementById('modalClose');
const modalImage = document.getElementById('modalImage');
const modalFilename = document.getElementById('modalFilename');
const modalFilesize = document.getElementById('modalFilesize');
const modalPrev = document.getElementById('modalPrev');
const modalNext = document.getElementById('modalNext');
const selectedFilesInput = document.getElementById('selectedFiles');
const selectedCount = document.getElementById('selectedCount');
const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
const selectedFiles = new Set();
let currentIndex = -1;

function updateSelectionUI() {
    selectedCount.textContent = selectedFiles.size + ' selected';
    selectedFilesInput.value = JSON.stringify(Array.from(selectedFiles));
    deleteSelectedBtn.disabled = selectedFiles.size === 0;
}

function formatBytes(bytes) {
    if (!bytes || bytes < 1) {
        return '0 B';
    }
    const units = ['B', 'KB', 'MB', 'GB'];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const size = bytes / Math.pow(1024, index);
    return size.toFixed(size >= 10 || index === 0 ? 0 : 1) + ' ' + units[index];
}

function openModalByIndex(index) {
    if (carouselItems.length === 0) {
        return;
    }
    const total = carouselItems.length;
    const normalizedIndex = (index + total) % total;
    const item = carouselItems[normalizedIndex];
    const src = item.getAttribute('data-src');
    const filename = item.getAttribute('data-filename') || '';
    const size = parseInt(item.getAttribute('data-size') || '0', 10);

    currentIndex = normalizedIndex;
    if (src) {
        modalImage.src = src;
        modalFilename.textContent = filename;
        modalFilesize.textContent = formatBytes(size);
        imageModal.classList.remove('hidden');
        imageModal.classList.add('flex');
    }
}

carouselItems.forEach((item) => {
    item.addEventListener('click', (e) => {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const filename = item.getAttribute('data-filename');
            const indicator = item.querySelector('.selected-indicator');

            if (selectedFiles.has(filename)) {
                selectedFiles.delete(filename);
                item.classList.remove('ring-4', 'ring-[#715A5A]');
                if (indicator) {
                    indicator.classList.add('hidden');
                }
            } else {
                selectedFiles.add(filename);
                item.classList.add('ring-4', 'ring-[#715A5A]');
                if (indicator) {
                    indicator.classList.remove('hidden');
                }
            }

            updateSelectionUI();
            return;
        }

        const index = parseInt(item.getAttribute('data-index') || '0', 10);
        openModalByIndex(index);
    });
});

function closeModal() {
    imageModal.classList.add('hidden');
    imageModal.classList.remove('flex');
    modalImage.src = '';
    modalFilename.textContent = '';
    modalFilesize.textContent = '';
    currentIndex = -1;
}

modalBackdrop.addEventListener('click', closeModal);
modalClose.addEventListener('click', closeModal);
modalPrev.addEventListener('click', () => {
    if (currentIndex !== -1) {
        openModalByIndex(currentIndex - 1);
    }
});
modalNext.addEventListener('click', () => {
    if (currentIndex !== -1) {
        openModalByIndex(currentIndex + 1);
    }
});

document.addEventListener('keydown', (e) => {
    if (imageModal.classList.contains('hidden')) {
        return;
    }
    if (e.key === 'Escape') {
        closeModal();
    }
    if (e.key === 'ArrowLeft') {
        openModalByIndex(currentIndex - 1);
    }
    if (e.key === 'ArrowRight') {
        openModalByIndex(currentIndex + 1);
    }
});

function confirmDeleteSelected() {
    if (selectedFiles.size === 0) {
        return false;
    }
    return confirm('Delete the selected carousel images? This cannot be undone.');
}
</script>

</body>
</html>

