<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "About (Editable)";
require_once 'includes/header.php';
require_login();

$error = '';
$message = '';

$public_about_path = realpath(__DIR__ . '/../about.php');

function extract_about_block($content) {
    if (preg_match('/<!-- ABOUT_INFO_START -->(.*?)<!-- ABOUT_INFO_END -->/is', $content, $m)) {
        return $m[1];
    }
    return null;
}

function extract_marker($content, $name) {
    $pat = '/<!-- ' . preg_quote($name, '/') . ' -->(.*?)<!-- ' . preg_quote($name, '/') . '_END -->/is';
    if (preg_match($pat, $content, $m)) return $m[1];
    // also support START/END symmetric names (e.g. ABOUT_MISSION_START ... ABOUT_MISSION_END)
    $pat2 = '/<!-- ' . preg_quote($name . '_START', '/') . ' -->(.*?)<!-- ' . preg_quote($name . '_END', '/') . ' -->/is';
    if (preg_match($pat2, $content, $m)) return $m[1];
    return null;
}

function replace_marker($content, $name, $newInner) {
    $start = '<!-- ' . $name . '_START -->';
    $end = '<!-- ' . $name . '_END -->';
    if (strpos($content, $start) !== false && strpos($content, $end) !== false) {
        $pattern = '/<!-- ' . preg_quote($name . '_START', '/') . ' -->(.*?)<!-- ' . preg_quote($name . '_END', '/') . ' -->/is';
        return preg_replace($pattern, $start . $newInner . $end, $content, 1);
    }
    return $content;
}

function html_to_plain($html) {
    // convert paragraph boundaries to double newlines, br to newline, then strip tags
    $h = preg_replace('#</p>\s*#i', "\n\n", $html);
    $h = preg_replace('#<br\s*/?>#i', "\n", $h);
    $h = strip_tags($h);
    return html_entity_decode(trim($h));
}

function text_to_paragraphs($text, $pclass='text-white/85') {
    // Split on any sequence of 2+ newline sequences (handles LF, CRLF, or CR)
    $parts = preg_split('/(?:\r\n|\r|\n){2,}/', trim($text));
    $out = '';
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $out .= "<p class=\"{$pclass}\">" . htmlspecialchars($p) . "</p>\n";
    }
    return $out;
}

// Load current pieces from markers
$our_story = $mission = $vision = $core1 = $core2 = $core3 = $core4 = '';
$founder_title = $founder_name = $founder_bio = $founder_photo = '';
if ($public_about_path && file_exists($public_about_path)) {
    $fileContent = file_get_contents($public_about_path);
    $fp_html = extract_marker($fileContent, 'ABOUT_FOUNDER_PHOTO');
    if ($fp_html !== null) $founder_photo = html_to_plain($fp_html);
    $ft_html = extract_marker($fileContent, 'ABOUT_FOUNDER_TITLE');
    if ($ft_html !== null) $founder_title = html_to_plain($ft_html);
    $fn_html = extract_marker($fileContent, 'ABOUT_FOUNDER_NAME');
    if ($fn_html !== null) $founder_name = html_to_plain($fn_html);
    $fb_html = extract_marker($fileContent, 'ABOUT_FOUNDER_BIO');
    if ($fb_html !== null) $founder_bio = html_to_plain($fb_html);
    $our_html = extract_marker($fileContent, 'ABOUT_OUR_STORY');
    if ($our_html !== null) $our_story = html_to_plain($our_html);
    $m_html = extract_marker($fileContent, 'ABOUT_MISSION');
    if ($m_html !== null) $mission = html_to_plain($m_html);
    $v_html = extract_marker($fileContent, 'ABOUT_VISION');
    if ($v_html !== null) $vision = html_to_plain($v_html);
    $c1 = extract_marker($fileContent, 'ABOUT_CORE_1'); if ($c1 !== null) $core1 = html_to_plain($c1);
    $c2 = extract_marker($fileContent, 'ABOUT_CORE_2'); if ($c2 !== null) $core2 = html_to_plain($c2);
    $c3 = extract_marker($fileContent, 'ABOUT_CORE_3'); if ($c3 !== null) $core3 = html_to_plain($c3);
    $c4 = extract_marker($fileContent, 'ABOUT_CORE_4'); if ($c4 !== null) $core4 = html_to_plain($c4);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_about'])) {
    $founder_photo_submitted = trim($_POST['founder_photo'] ?? '');

    // Handle founder photo upload - save into members_pictures/founder and remove previous founder files
    if (isset($_FILES['founder_photo_upload']) && $_FILES['founder_photo_upload']['error'] === UPLOAD_ERR_OK) {
        $base_dir = realpath(__DIR__ . '/../assets/images/members_pictures');
        if (!$base_dir) {
            $error = 'Upload base directory does not exist: assets/images/members_pictures';
        } else {
            $founder_dir = $base_dir . DIRECTORY_SEPARATOR . 'founder';
            if (!is_dir($founder_dir)) {
                @mkdir($founder_dir, 0755, true);
            }

            $file = $_FILES['founder_photo_upload'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                $error = 'Invalid file type. Only JPEG, PNG, WebP, and GIF images are allowed.';
            } else if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                $error = 'File is too large. Maximum file size is 5MB.';
            } else {
                // remove previous founder files
                $existing = glob($founder_dir . DIRECTORY_SEPARATOR . 'founder.*');
                if (!empty($existing)) {
                    foreach ($existing as $ex) {
                        @unlink($ex);
                    }
                }

                // Canonical filename: founder.jpg
                $filename = 'founder.jpg';
                $upload_path = $founder_dir . DIRECTORY_SEPARATOR . $filename;

                // Attempt to convert supported image types to JPEG (flattening alpha to white)
                $tmp = $file['tmp_name'];
                $mime = @mime_content_type($tmp);
                $src = false;
                if ($mime === 'image/jpeg' || $mime === 'image/pjpeg') {
                    if (function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($tmp);
                } elseif ($mime === 'image/png') {
                    if (function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($tmp);
                } elseif ($mime === 'image/webp') {
                    if (function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($tmp);
                } elseif ($mime === 'image/gif') {
                    if (function_exists('imagecreatefromgif')) $src = @imagecreatefromgif($tmp);
                }

                if ($src !== false) {
                    $w = imagesx($src);
                    $h = imagesy($src);
                    $dst = imagecreatetruecolor($w, $h);
                    $white = imagecolorallocate($dst, 255, 255, 255);
                    imagefill($dst, 0, 0, $white);
                    // copy and flatten (handles PNG/GIF transparency)
                    imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
                    // save as JPEG
                    if (function_exists('imagejpeg') && @imagejpeg($dst, $upload_path, 85)) {
                        $founder_photo_submitted = 'assets/images/members_pictures/founder/' . $filename;
                    } else {
                        $error = 'Failed to convert and save uploaded image as JPEG.';
                    }
                    imagedestroy($src);
                    imagedestroy($dst);
                } else {
                    // Fallback: move uploaded file and name it founder.jpg (may not change format)
                    if (@move_uploaded_file($tmp, $upload_path)) {
                        $founder_photo_submitted = 'assets/images/members_pictures/founder/' . $filename;
                    } else {
                        $error = 'Failed to upload file. Permission denied.';
                    }
                }
            }
        }
    }
    // If admin uploaded via hosting panel or placed files into founder folder, auto-detect current founder file
    if (empty($founder_photo_submitted)) {
        $founder_dir = realpath(__DIR__ . '/../assets/images/members_pictures/founder');
        if ($founder_dir && is_dir($founder_dir) && is_readable($founder_dir)) {
            $files = glob($founder_dir . DIRECTORY_SEPARATOR . 'founder.*');
            if (!empty($files)) {
                // There should be at most one; choose the newest by mtime just in case
                usort($files, function($a, $b){ return filemtime($b) - filemtime($a); });
                $latest = basename($files[0]);
                $founder_photo_submitted = 'assets/images/members_pictures/founder/' . $latest;
            }
        }
    }
    $founder_title_submitted = trim($_POST['founder_title'] ?? '');
    $founder_name_submitted = trim($_POST['founder_name'] ?? '');
    $founder_bio_submitted = trim($_POST['founder_bio'] ?? '');
    $our_story_submitted = trim($_POST['our_story'] ?? '');
    $mission_submitted = trim($_POST['mission'] ?? '');
    $vision_submitted = trim($_POST['vision'] ?? '');
    $core1_submitted = trim($_POST['core1'] ?? '');
    $core2_submitted = trim($_POST['core2'] ?? '');
    $core3_submitted = trim($_POST['core3'] ?? '');
    $core4_submitted = trim($_POST['core4'] ?? '');

    if (!$public_about_path || !is_writable($public_about_path)) {
        $error = 'Cannot write to about.php. File missing or not writable.';
    } else {
        $fileContent = file_get_contents($public_about_path);
        // replace individual markers with generated HTML (preserve surrounding markup)
        $newContent = $fileContent;
        $newContent = replace_marker($newContent, 'ABOUT_FOUNDER_PHOTO', htmlspecialchars($founder_photo_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_FOUNDER_TITLE', htmlspecialchars($founder_title_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_FOUNDER_NAME', htmlspecialchars($founder_name_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_FOUNDER_BIO', text_to_paragraphs($founder_bio_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_OUR_STORY', text_to_paragraphs($our_story_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_MISSION', text_to_paragraphs($mission_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_VISION', text_to_paragraphs($vision_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_CORE_1', text_to_paragraphs($core1_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_CORE_2', text_to_paragraphs($core2_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_CORE_3', text_to_paragraphs($core3_submitted));
        $newContent = replace_marker($newContent, 'ABOUT_CORE_4', text_to_paragraphs($core4_submitted));

        $written = @file_put_contents($public_about_path, $newContent);
        if ($written === false) {
            $error = 'Failed to write updates to about.php (permission denied).';
        } else {
            log_activity('update', 'about_page', 0, 'Updated about.php editable block');
            $message = 'About page updated successfully.';
            // reload values
            $fileContent = file_get_contents($public_about_path);
            $fp_html = extract_marker($fileContent, 'ABOUT_FOUNDER_PHOTO'); if ($fp_html !== null) $founder_photo = html_to_plain($fp_html);
            $ft_html = extract_marker($fileContent, 'ABOUT_FOUNDER_TITLE'); if ($ft_html !== null) $founder_title = html_to_plain($ft_html);
            $fn_html = extract_marker($fileContent, 'ABOUT_FOUNDER_NAME'); if ($fn_html !== null) $founder_name = html_to_plain($fn_html);
            $fb_html = extract_marker($fileContent, 'ABOUT_FOUNDER_BIO'); if ($fb_html !== null) $founder_bio = html_to_plain($fb_html);
            $our_html = extract_marker($fileContent, 'ABOUT_OUR_STORY'); if ($our_html !== null) $our_story = html_to_plain($our_html);
            $m_html = extract_marker($fileContent, 'ABOUT_MISSION'); if ($m_html !== null) $mission = html_to_plain($m_html);
            $v_html = extract_marker($fileContent, 'ABOUT_VISION'); if ($v_html !== null) $vision = html_to_plain($v_html);
            $c1 = extract_marker($fileContent, 'ABOUT_CORE_1'); if ($c1 !== null) $core1 = html_to_plain($c1);
            $c2 = extract_marker($fileContent, 'ABOUT_CORE_2'); if ($c2 !== null) $core2 = html_to_plain($c2);
            $c3 = extract_marker($fileContent, 'ABOUT_CORE_3'); if ($c3 !== null) $core3 = html_to_plain($c3);
            $c4 = extract_marker($fileContent, 'ABOUT_CORE_4'); if ($c4 !== null) $core4 = html_to_plain($c4);
        }
    }
}
?>

<?php if ($message): ?>
<div class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-300">
    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-300">
    <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="bg-[#183D3D] rounded-lg p-6">
    <h3 class="text-lg font-bold text-[#D3DAD9] mb-4">
        <i class="fas fa-edit mr-2 text-[#715A5A]"></i>Edit About Page
    </h3>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="save_about" value="1">

        <div class="mb-6">
            <h4 class="text-[#D3DAD9] text-base font-semibold mb-3">Founder Section</h4>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Upload Founder Photo</label>
                    <input type="file" name="founder_photo_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                    <p class="text-[#D3DAD9]/70 text-xs mt-1">Current: <?php echo htmlspecialchars($founder_photo); ?></p>
                </div>
                <div>
                    <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Founder Title</label>
                    <input type="text" name="founder_title" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg" value="<?php echo htmlspecialchars($founder_title); ?>">
                </div>
                <div>
                    <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Founder Name</label>
                    <input type="text" name="founder_name" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg" value="<?php echo htmlspecialchars($founder_name); ?>">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Founder Bio (paragraphs: separate with a blank line)</label>
                <textarea name="founder_bio" rows="6" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($founder_bio); ?></textarea>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Our Story (paragraphs: separate with a blank line)</label>
            <textarea name="our_story" rows="8" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($our_story); ?></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Mission</label>
                <textarea name="mission" rows="4" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($mission); ?></textarea>
            </div>
            <div>
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Vision</label>
                <textarea name="vision" rows="4" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($vision); ?></textarea>
            </div>
        </div>

        <div class="mt-4 grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Core Value: Excellence</label>
                <textarea name="core1" rows="3" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($core1); ?></textarea>
            </div>
            <div>
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Core Value: Integrity</label>
                <textarea name="core2" rows="3" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($core2); ?></textarea>
            </div>
            <div>
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Core Value: Innovation</label>
                <textarea name="core3" rows="3" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($core3); ?></textarea>
            </div>
            <div>
                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Core Value: Reliability</label>
                <textarea name="core4" rows="3" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo htmlspecialchars($core4); ?></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="px-6 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Enable Shift+Enter to insert a blank line, and Enter (without Shift) to submit the form
(function(){
    var form = document.querySelector('form');
    if (!form) return;
    function insertAtCaret(el, text) {
        var start = el.selectionStart;
        var end = el.selectionEnd;
        var val = el.value;
        el.value = val.substring(0, start) + text + val.substring(end);
        // move caret after inserted text
        var pos = start + text.length;
        el.selectionStart = el.selectionEnd = pos;
    }

    form.querySelectorAll('textarea').forEach(function(ta){
        ta.addEventListener('keydown', function(e){
            if (e.key === 'Enter') {
                if (e.shiftKey) {
                    // Insert a blank line for paragraph separation
                    e.preventDefault();
                    insertAtCaret(ta, '\n\n');
                } else {
                    // Submit the form when Enter is pressed without Shift
                    e.preventDefault();
                    // Small timeout to allow any interim input to settle
                    setTimeout(function(){ form.querySelector('button[type="submit"]')?.click(); }, 0);
                }
            }
        });
    });
})();
</script>
