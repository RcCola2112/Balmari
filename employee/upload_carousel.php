<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Upload Carousel Image";
require_once 'includes/auth.php';

requireLogin();

$error_message = '';
$success_message = '';
$upload_dir = '../assets/images/carousel/';

// Handle cropped image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_cropped') {
    $cropped_data = isset($_POST['cropped_image']) ? $_POST['cropped_image'] : '';
    
    if (empty($cropped_data)) {
        $error_message = 'No image data received';
    } else {
        try {
            // Ensure upload directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Decode base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $cropped_data, $type)) {
                $data = substr($cropped_data, strpos($cropped_data, ',') + 1);
                $data = base64_decode($data);
                
                if ($data === false) {
                    $error_message = 'Invalid image data';
                } else {
                    // Generate unique filename
                    $ext = 'jpg'; // Convert all to JPG for consistency
                    $new_filename = 'carousel_' . time() . '_' . uniqid() . '.' . $ext;
                    $destination = $upload_dir . $new_filename;

                    // Save file
                    if (file_put_contents($destination, $data)) {
                        $success_message = 'Carousel image uploaded and cropped successfully!';
                        $_POST = [];
                        $_FILES = [];
                    } else {
                        $error_message = 'Failed to save the cropped image';
                    }
                }
            } else {
                $error_message = 'Invalid image format';
            }
        } catch (Exception $e) {
            error_log("Upload carousel error: " . $e->getMessage());
            $error_message = 'An error occurred during upload. Please try again.';
        }
    }
}

// Handle regular upload (legacy support)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'save_cropped')) {
    if (empty($_FILES) || empty($_FILES['images']['name'][0])) {
        $error_message = 'Please select at least one image file';
    } else {
        try {
            // Ensure upload directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $success_count = 0;
            $errors = [];

            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                $file = $_FILES['images'];
                $filename = $file['name'][$i];
                $tmp_name = $file['tmp_name'][$i];
                $file_type = $file['type'][$i];
                $file_size = $file['size'][$i];
                $file_error = $file['error'][$i];

                // Validate file
                if ($file_error !== UPLOAD_ERR_OK) {
                    $errors[] = 'Error uploading ' . htmlspecialchars($filename);
                    continue;
                }

                if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $errors[] = htmlspecialchars($filename) . ' is not a valid image format (JPG, PNG, WebP only)';
                    continue;
                }

                // Generate unique filename
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $new_filename = 'carousel_' . time() . '_' . uniqid() . '.' . strtolower($ext);
                $destination = $upload_dir . $new_filename;

                // Move uploaded file
                if (move_uploaded_file($tmp_name, $destination)) {
                    $success_count++;
                } else {
                    $errors[] = 'Failed to save ' . htmlspecialchars($filename);
                }
            }

            if ($success_count > 0) {
                $success_message = $success_count . ' carousel image(s) uploaded successfully';
                $_POST = [];
                $_FILES = [];
            }

            if (!empty($errors) && $success_count === 0) {
                $error_message = $errors[0];
            }
        } catch (Exception $e) {
            error_log("Upload carousel error: " . $e->getMessage());
            $error_message = 'An error occurred during upload. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.css">

<!-- Main Content -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Page Header -->
    <h1 class="text-4xl font-['Playfair_Display'] font-semibold text-[#FFFFFF] mb-8">
        Upload New Carousel Image
    </h1>
    
    <!-- Success Message -->
    <?php if ($success_message): ?>
        <div class="bg-[#5C8374]/30 border-l-4 border-[#5C8374] p-4 mb-6 rounded">
            <p class="text-[#FFFFFF] font-medium"><?php echo htmlspecialchars($success_message); ?></p>
            <p class="text-[#5C8374]/80 text-sm mt-2">
                Your carousel images will appear on the homepage automatically.
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
        <!-- Step 1: Upload -->
        <div id="uploadStep" class="mb-8">
            <h2 class="text-xl font-semibold text-[#5C8374] mb-4">Step 1: Select Image</h2>
            <form method="POST" enctype="multipart/form-data" id="uploadForm" onsubmit="handleUploadStep(event)">
                <!-- Image Upload -->
                <div class="mb-8">
                    <label for="images" class="block text-sm font-medium text-[#5C8374] mb-4">
                        Carousel Images <span class="text-red-500">*</span>
                    </label>
                    
                    <!-- Drag and Drop Zone -->
                    <div 
                        id="dropZone" 
                        class="border-2 border-dashed border-[#5C8374] rounded-lg p-8 text-center cursor-pointer hover:border-[#5C8374] hover:bg-[#183D3D] transition"
                    >
                        <input 
                            type="file" 
                            id="images" 
                            name="images[]" 
                            accept="image/jpeg,image/png,image/webp" 
                            single
                            required
                            class="hidden"
                        >
                        <div class="text-4xl mb-2">📤</div>
                        <p class="text-[#D3DAD9] font-medium mb-1">Drag and drop your image here</p>
                        <p class="text-[#5C8374]/70 text-sm mb-4">or</p>
                        <button 
                            type="button" 
                            onclick="document.getElementById('images').click()"
                            class="bg-[#5C8374] text-[#183D3D] px-6 py-2 rounded-lg font-medium hover:bg-[#44444E] hover:text-[#FFFFFF] transition"
                        >
                            Choose Files
                        </button>
                        <p class="text-xs text-[#5C8374]/60 mt-4">
                            JPG, PNG, or WebP • Recommended: 1920x600px
                        </p>
                    </div>
                    
                    <!-- File Preview -->
                    <div id="simplePreview" class="mt-4 hidden">
                        <div class="bg-[#22272B] rounded-lg p-4">
                            <p class="text-sm font-medium text-[#5C8374] mb-2">Selected:</p>
                            <img id="simplePreviewImage" src="" alt="Preview" class="max-w-full h-auto rounded-lg max-h-96">
                            <p id="fileNameSimple" class="text-sm text-[#5C8374] mt-2"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons for Upload Step -->
                <div class="flex space-x-4">
                    <button 
                        type="button"
                        id="cropButton"
                        disabled
                        class="flex-1 bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded-lg font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Crop Image
                    </button>
                    <a 
                        href="dashboard.php" 
                        class="flex-1 bg-[#44444E] text-[#D3DAD9] py-3 rounded-lg font-semibold hover:bg-[#5C8374] hover:text-[#183D3D] transition text-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Step 2: Crop Editor -->
        <div id="cropStep" class="hidden mb-8">
            <h2 class="text-xl font-semibold text-[#5C8374] mb-4">Step 2: Crop Image</h2>
            <div style="max-height: 500px; overflow: auto;">
                <img id="cropImage" src="" alt="Image for cropping" class="max-w-full">
            </div>
            
            <!-- Crop Controls -->
            <div class="mt-4 space-y-3 text-sm text-[#D3DAD9]">
                <div class="flex space-x-2">
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" onclick="cropRotate(90)">↻ Rotate</button>
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" onclick="cropFlipH()">⇄ Flip H</button>
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" onclick="cropFlipV()">↕ Flip V</button>
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" onclick="cropReset()">↺ Reset</button>
                </div>
                <div>
                    <label class="block text-[#5C8374] mb-2">Aspect Ratio:</label>
                    <select id="aspectRatio" class="w-full bg-[#22272B] border border-[#5C8374] rounded px-3 py-2 text-[#D3DAD9]">
                        <option value="0">Free</option>
                        <option value="16/9">16:9 (Widescreen)</option>
                        <option value="4/3">4:3 (Standard)</option>
                        <option value="1/1">1:1 (Square)</option>
                        <option value="2/1">2:1 (Banner)</option>
                    </select>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex space-x-4 mt-6">
                <button 
                    type="button"
                    id="saveCropButton"
                    class="flex-1 bg-[#5C8374] text-[#183D3D] px-6 py-3 rounded-lg font-semibold hover:bg-[#44444E] hover:text-[#FFFFFF] transition"
                >
                    Save Cropped Image
                </button>
                <button 
                    type="button"
                    id="cancelCropButton"
                    class="flex-1 bg-[#44444E] text-[#D3DAD9] px-6 py-3 rounded-lg font-semibold hover:bg-[#5C8374] hover:text-[#183D3D] transition"
                >
                    Cancel
                </button>
            </div>
        </div>

        <!-- Step 3: Preview -->
        <div id="previewStep" class="hidden mb-8">
            <h2 class="text-xl font-semibold text-[#5C8374] mb-4">Step 3: Preview & Upload</h2>
            <div class="bg-[#22272B] rounded-lg p-4 mb-4">
                <p class="text-sm font-medium text-[#5C8374] mb-2">Cropped Image Preview:</p>
                <canvas id="previewCanvas" class="max-w-full h-auto rounded-lg max-h-96"></canvas>
            </div>
            
            <!-- Hidden form for submission -->
            <form method="POST" id="uploadFinalForm">
                <input type="hidden" name="action" value="save_cropped">
                <input type="hidden" name="cropped_image" id="croppedImageData">
                
                <div class="flex space-x-4">
                    <button 
                        type="submit"
                        class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition"
                    >
                        Upload Cropped Image
                    </button>
                    <button 
                        type="button"
                        onclick="backToCrop()"
                        class="flex-1 bg-[#44444E] text-[#D3DAD9] px-6 py-3 rounded-lg font-semibold hover:bg-[#5C8374] hover:text-[#183D3D] transition"
                    >
                        Back to Crop
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Requirements Box -->
    <div class="mt-8 p-6 bg-[#183D3D] rounded-lg border border-[#5C8374]">
        <h3 class="font-bold text-[#5C8374] mb-3">Image Requirements</h3>
        <ul class="text-sm text-[#D3DAD9] space-y-2">
            <li>✓ <strong>Dimensions:</strong> 1920x600px (or similar aspect ratio)</li>
            <li>✓ <strong>Format:</strong> JPG, PNG, or WebP</li>
            <li>✓ <strong>Size:</strong> Maximum 5MB</li>
            <li>✓ <strong>Quality:</strong> High-quality, professional images recommended</li>
            <li>✓ <strong>Content:</strong> Should represent your design/construction work</li>
            <li>✓ <strong>Editing:</strong> Use the crop tool to adjust image framing and composition</li>
        </ul>
    </div>

</div>

<!-- Cropper.js Library -->
<script src="https://unpkg.com/cropperjs"></script>

<script>
let cropper = null;
let selectedFile = null;

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const imageInput = document.getElementById('images');

// Prevent default behavior
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Highlight drop zone when item is dragged over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.style.borderColor = '#5C8374';
        dropZone.style.backgroundColor = 'rgba(92, 131, 116, 0.1)';
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.style.borderColor = '';
        dropZone.style.backgroundColor = '';
    });
});

// Handle dropped files
dropZone.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    const files = dt.files;
    if (files.length > 0) {
        selectedFile = files[0];
        handleFileSelect();
    }
});

// Handle file selection
imageInput.addEventListener('change', () => {
    if (imageInput.files && imageInput.files.length > 0) {
        selectedFile = imageInput.files[0];
        handleFileSelect();
    }
});

function handleFileSelect() {
    if (!selectedFile) return;

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(selectedFile.type)) {
        alert('Please select only JPG, PNG, or WebP images');
        imageInput.value = '';
        selectedFile = null;
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('simplePreviewImage').src = e.target.result;
        document.getElementById('fileNameSimple').textContent = selectedFile.name + ' (' + (selectedFile.size / 1024 / 1024).toFixed(2) + ' MB)';
        document.getElementById('simplePreview').classList.remove('hidden');
        document.getElementById('cropButton').disabled = false;
    };
    reader.readAsDataURL(selectedFile);
}

function handleUploadStep(e) {
    e.preventDefault();
    if (!selectedFile) {
        alert('Please select an image first');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = document.getElementById('cropImage');
        img.src = e.target.result;
        
        // Initialize cropper after image loads
        img.onload = function() {
            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(img, {
                aspectRatio: NaN,
                autoCropArea: 1,
                responsive: true,
                restore: true,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: true,
                viewMode: 1,
            });
        };
        
        document.getElementById('uploadStep').classList.add('hidden');
        document.getElementById('cropStep').classList.remove('hidden');
    };
    reader.readAsDataURL(selectedFile);
}

document.getElementById('cropButton').addEventListener('click', (e) => {
    handleUploadStep(new Event('submit'));
});

document.getElementById('cancelCropButton').addEventListener('click', () => {
    document.getElementById('uploadStep').classList.remove('hidden');
    document.getElementById('cropStep').classList.add('hidden');
    document.getElementById('previewStep').classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

function cropRotate(degree) {
    if (cropper) {
        cropper.rotate(degree);
    }
}

function cropFlipH() {
    if (cropper) {
        cropper.scaleX(cropper.getData().scaleX === -1 ? 1 : -1);
    }
}

function cropFlipV() {
    if (cropper) {
        cropper.scaleY(cropper.getData().scaleY === -1 ? 1 : -1);
    }
}

function cropReset() {
    if (cropper) {
        cropper.reset();
    }
}

document.getElementById('aspectRatio').addEventListener('change', (e) => {
    const ratio = e.target.value;
    if (cropper) {
        cropper.setAspectRatio(ratio === '0' ? NaN : eval(ratio));
    }
});

document.getElementById('saveCropButton').addEventListener('click', () => {
    if (!cropper) return;
    
    const canvas = cropper.getCroppedCanvas({
        maxWidth: 1920,
        maxHeight: 1080,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Show preview
    const previewCanvas = document.getElementById('previewCanvas');
    previewCanvas.width = canvas.width;
    previewCanvas.height = canvas.height;
    previewCanvas.getContext('2d').drawImage(canvas, 0, 0);
    
    // Store cropped data
    document.getElementById('croppedImageData').value = canvas.toDataURL();
    
    document.getElementById('cropStep').classList.add('hidden');
    document.getElementById('previewStep').classList.remove('hidden');
});

function backToCrop() {
    document.getElementById('previewStep').classList.add('hidden');
    document.getElementById('cropStep').classList.remove('hidden');
}

</script>

