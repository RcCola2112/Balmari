<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Contact Information";
require_once 'includes/header.php';
require_login();

$error = '';
$message = '';

// Only allow editing of the four canonical contact items: Location, Email, Phone, Hours
$items = [];
$allowed = ['Location', 'Email', 'Phone', 'Hours'];

// Path to public contact page we will edit directly
$public_contact_path = realpath(__DIR__ . '/../contact.php');

// Helper: extract between markers
function extract_contact_block($content) {
    if (preg_match('/<!-- CONTACT_INFO_START -->(.*?)<!-- CONTACT_INFO_END -->/is', $content, $m)) {
        return $m[1];
    }
    return null;
}

// Helper: parse a heading block from the contact block
function parse_field_from_block($block, $heading) {
    $pattern = '/<h3[^>]*>\s*' . preg_quote($heading, '/') . '\s*<\/h3>.*?<p[^>]*>(.*?)<\/p>/is';
    if (preg_match($pattern, $block, $m)) {
        // convert common block/line tags into newlines before stripping tags
        $inner = $m[1];
        // Replace <br>, <br/> and closing divs or div boundaries with newlines
        $inner = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $inner);
        $inner = preg_replace('/<\s*\/div\s*>/i', "\n", $inner);
        $inner = preg_replace('/<\s*div[^>]*>/i', "", $inner);
        // strip remaining tags but keep the newlines
        $text = trim(strip_tags($inner));
        // Normalize multiple newlines
        $text = preg_replace('/\n{2,}/', "\n", $text);
        return html_entity_decode($text);
    }
    return '';
}

// Load current values from contact.php if available
if ($public_contact_path && file_exists($public_contact_path)) {
    $fileContent = file_get_contents($public_contact_path);
    $block = extract_contact_block($fileContent);
    if ($block !== null) {
        foreach ($allowed as $title) {
            $val = parse_field_from_block($block, $title);
            $items[$title] = ['id' => null, 'title' => $title, 'description' => $val, 'is_active' => 1];
        }
    }
}

// detect order column (best-effort, preserve compatibility)
try {
    $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_information'");
    $colStmt->execute();
    $svcCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('display_order', $svcCols, true)) {
        $order_col = 'display_order';
    } elseif (in_array('sort_order', $svcCols, true)) {
        $order_col = 'sort_order';
    } else {
        $order_col = 'id';
    }

    // Load only allowed items (case-insensitive match) and map to canonical titles
    $allowed_lc = array_map('strtolower', $allowed);
    $placeholders = implode(',', array_fill(0, count($allowed_lc), '?'));
    $stmt = $pdo->prepare("SELECT * FROM contact_information WHERE LOWER(TRIM(title)) IN (" . $placeholders . ") ORDER BY " . $order_col . " ASC");
    $stmt->execute($allowed_lc);
    $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fetched as $row) {
        $lc = strtolower(trim($row['title']));
        $idx = array_search($lc, $allowed_lc, true);
        if ($idx !== false) {
            $canon = $allowed[$idx];
            $items[$canon] = $row;
        } else {
            // unexpected title, ignore for canonical mapping
        }
    }

    // If some canonical items are still missing, try heuristic matching by keyword
    $missing = array_diff($allowed, array_keys($items));
    if (!empty($missing)) {
        $allStmt = $pdo->query("SELECT * FROM contact_information");
        $allRows = $allStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allRows as $row) {
            $titleRaw = $row['title'] ?? '';
            $descRaw = $row['description'] ?? '';
            $lcTitle = strtolower(trim($titleRaw));
            $lcDesc = strtolower($descRaw);

            foreach ($missing as $mKey => $canonTitle) {
                if (isset($items[$canonTitle])) continue;

                $matched = false;
                if ($canonTitle === 'Email') {
                    if (preg_match('/email|e-?mail|@/', $lcTitle) || preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $descRaw)) {
                        $matched = true;
                    }
                } elseif ($canonTitle === 'Phone') {
                    if (preg_match('/phone|tel|mobile|telephone/', $lcTitle) || preg_match('/(\+?[0-9][0-9()\-\s+.]{4,})/', $descRaw)) {
                        $matched = true;
                    }
                } elseif ($canonTitle === 'Hours') {
                    if (preg_match('/hour|hours|open|closed|monday|tuesday|am|pm|schedule/', $lcTitle) || preg_match('/\b(am|pm)\b/i', $descRaw)) {
                        $matched = true;
                    }
                } elseif ($canonTitle === 'Location') {
                    if (preg_match('/location|address|street|st\.|rd\.|ave\.|subdivision|balayan|batangas/', $lcTitle) || strlen(trim($descRaw)) > 20) {
                        $matched = true;
                    }
                }

                if ($matched) {
                    $items[$canonTitle] = $row;
                    // refresh missing list
                    $missing = array_diff($allowed, array_keys($items));
                }
            }
            if (empty($missing)) break;
        }
    }
} catch (PDOException $e) {
    $error = 'Error loading contact information: ' . $e->getMessage();
}

// If still missing any values, populate with sensible defaults
foreach ($allowed as $idx => $title) {
    if (empty($items[$title]['description'] ?? '')) {
        if ($title === 'Location') $items[$title]['description'] = "Balmari Design and Construction Ereville subdivision, Paz St, Balayan, Batangas\nServing the Philippines";
        if ($title === 'Email') $items[$title]['description'] = 'balmarihome@gmail.com';
        if ($title === 'Phone') $items[$title]['description'] = '0945 463 2111';
        if ($title === 'Hours') $items[$title]['description'] = "Monday - Friday: 8:00 AM - 8:00 PM\nSaturday: 8:00 AM - 5:00 PM\nSunday: Closed";
        $items[$title]['is_active'] = 1;
    }
}

// Handle saving edits to canonical items
// When admin saves, write the Contact Information block directly into contact.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contacts'])) {
    // collect submitted values
    foreach ($allowed as $title) {
        $slug = 'desc_' . strtolower(str_replace(' ', '_', $title));
        $val = trim($_POST[$slug] ?? '');
        $items[$title]['description'] = $val;
        $items[$title]['is_active'] = isset($_POST['active_' . strtolower(str_replace(' ', '_', $title))]) ? 1 : 0;
    }

    if (!$public_contact_path || !is_writable($public_contact_path)) {
        $error = 'Cannot write to contact.php. File missing or not writable.';
    } else {
        // build HTML for the inner contact item blocks only (do NOT include the surrounding column or heading)
        $html = "";
        foreach ($allowed as $title) {
            $slug = strtolower(str_replace(' ', '_', $title));
            $desc = htmlspecialchars($items[$title]['description'] ?? '');
            if ($title === 'Location') {
                $html .= "\n                <div class=\"mb-8\">\n                    <h3 class=\"font-bold text-[#5C8374] mb-2 flex items-center\">Location</h3>\n                    <p class=\"text-white/85\">" . nl2br($desc) . "</p>\n                </div>\n";
            } elseif ($title === 'Email') {
                $email_addr = '';
                if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $items[$title]['description'] ?? '', $m)) $email_addr = $m[0];
                $html .= "\n                <div class=\"mb-8\">\n                    <h3 class=\"font-bold text-[#5C8374] mb-2 flex items-center\">Email</h3>\n                    <p class=\"text-white/85\">";
                if ($email_addr) {
                    $html .= "<a href=\"mailto:" . htmlspecialchars($email_addr) . "\" class=\"text-white hover:text-[#5C8374]\">" . $desc . "</a>";
                } else {
                    $html .= nl2br($desc);
                }
                $html .= "</p>\n                </div>\n";
            } elseif ($title === 'Phone') {
                $html .= "\n                <div class=\"mb-8\">\n                    <h3 class=\"font-bold text-[#5C8374] mb-2 flex items-center\">Phone</h3>\n                    <p class=\"text-white/85\">";
                // Emit each entered phone line separately so they render stacked and each gets its own tel: link
                $lines = preg_split('/\r\n|\r|\n/', $items[$title]['description'] ?? '');
                foreach ($lines as $ln) {
                    $ln = trim($ln);
                    if ($ln === '') continue;
                    if (preg_match('/(\+?[0-9][0-9()\-\s+.]{4,})/', $ln, $m)) {
                        $tel = preg_replace('/[^+0-9]/', '', $m[0]);
                        $html .= "<div><a href=\"tel:" . htmlspecialchars($tel) . "\" class=\"text-white hover:text-[#5C8374]\">" . htmlspecialchars($ln) . "</a></div>";
                    } else {
                        $html .= "<div>" . htmlspecialchars($ln) . "</div>";
                    }
                }
                $html .= "</p>\n                </div>\n";
            } elseif ($title === 'Hours') {
                $html .= "\n                <div class=\"mb-8\">\n                    <h3 class=\"font-bold text-[#5C8374] mb-2 flex items-center\">Hours</h3>\n                    <p class=\"text-[#5C8374]/85\">" . nl2br($desc) . "</p>\n                </div>\n";
            }
        }

        // do NOT include the outer column wrapper here because contact.php already contains it.
        // When markers are missing we will insert a full wrapper once and include the inner items.
        $fileContent = file_get_contents($public_contact_path);
        if (preg_match('/<!-- CONTACT_INFO_START -->(.*?)<!-- CONTACT_INFO_END -->/is', $fileContent)) {
            $newContent = preg_replace('/<!-- CONTACT_INFO_START -->(.*?)<!-- CONTACT_INFO_END -->/is', "<!-- CONTACT_INFO_START -->" . $html . "<!-- CONTACT_INFO_END -->", $fileContent);
        } else {
            // build a full wrapper to insert when markers are absent
            $wrapper = "\n            <div class=\"md:col-span-1 bg-[#183D3D] rounded-2xl shadow-lg p-10 border border-[#5C8374]/25\">\n                <h2 class=\"text-2xl font-['Playfair_Display'] font-semibold text-white mb-8\">Contact Information</h2>\n" . $html . "\n            </div>\n";
            $newContent = preg_replace('/<!-- Contact Form -->/is', "<!-- CONTACT_INFO_START -->" . $wrapper . "<!-- CONTACT_INFO_END -->\n\n<!-- Contact Form -->", $fileContent, 1);
        }

        $written = @file_put_contents($public_contact_path, $newContent);
        if ($written === false) {
            $error = 'Failed to write updates to contact.php (permission denied).';
        } else {
            log_activity('update', 'contact_information', 0, 'Updated contact.php contact information block');
            $message = 'Contact information updated successfully.';
            $redirect_url = 'contact_information.php';
            $redirect_delay = 1;
            // reload the page values from the updated file
            $fileContent = file_get_contents($public_contact_path);
            $block = extract_contact_block($fileContent);
            if ($block !== null) {
                foreach ($allowed as $title) {
                    $items[$title]['description'] = parse_field_from_block($block, $title);
                }
            }
        }
    }
}
?>

            <?php if ($message): ?>
            <div data-flash class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-300">
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
                    <i class="fas fa-edit mr-2 text-[#715A5A]"></i>Edit Contact Information
                </h3>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="save_contacts" value="1">
                    <?php foreach ($allowed as $title):
                        $row = $items[$title] ?? ['description'=>'','is_active'=>1];
                        $slug = strtolower(str_replace(' ', '_', $title));
                        $desc = htmlspecialchars($row['description'] ?? '');
                        $active = !empty($row['is_active']) ? 'checked' : '';
                    ?>
                    <div class="bg-[#040D12] p-4 rounded-lg border border-[#5C8374]/20">
                        <label class="block text-[#D3DAD9] text-sm font-semibold mb-2"><?php echo $title; ?></label>
                        <?php if ($title === 'Location' || $title === 'Hours'): ?>
                            <textarea name="desc_<?php echo $slug; ?>" rows="4" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo $desc; ?></textarea>
                        <?php elseif ($title === 'Phone'): ?>
                            <textarea id="desc_phone" name="desc_<?php echo $slug; ?>" rows="2" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg"><?php echo $desc; ?></textarea>
                            <p class="text-[#5C8374]/70 text-xs mt-1">Press <strong>Enter</strong> to save, or <strong>Shift+Enter</strong> to add a new line/phone number.</p>
                        <?php else: ?>
                            <input type="text" name="desc_<?php echo $slug; ?>" value="<?php echo $desc; ?>" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg">
                        <?php endif; ?>
                        <label class="flex items-center mt-3 text-[#D3DAD9]"><input type="checkbox" name="active_<?php echo $slug; ?>" <?php echo $active; ?> class="mr-2"> Active</label>
                    </div>
                    <?php endforeach; ?>

                    <div>
                        <button type="submit" class="px-6 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Phone textarea: Enter submits the contact form, Shift+Enter inserts a newline
(function(){
    var phone = document.getElementById('desc_phone');
    if (!phone) return;
    var form = phone.closest('form');
    phone.addEventListener('keydown', function(e){
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (form) {
                form.querySelector('button[type="submit"]').click();
            }
        }
        // Shift+Enter default behavior in textarea already inserts newline, so no extra handling needed
    });
})();
</script>
