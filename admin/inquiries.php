<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Buffer output so header() redirects can be sent after included templates
if (!ob_get_level()) {
    ob_start();
}
$page_title = "Contact Inquiries";
require_once 'includes/header.php';
require_login();

$error = '';
$message = '';
$filter = $_GET['filter'] ?? 'all';

// Detect available columns on contact_messages to avoid SQL errors on older schemas
$has_is_read = false;
$has_is_spam = false;
$has_notes = false;
$has_status = false;
try {
    $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages'");
    $colStmt->execute();
    $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
    $has_is_read = in_array('is_read', $cols, true);
    $has_is_spam = in_array('is_spam', $cols, true);
    $has_notes = in_array('notes', $cols, true);
    $has_status = in_array('status', $cols, true);
} catch (Exception $e) {
    // ignore, fall back to assuming columns may not exist
}

// Get inquiries (support 'sent' filter that aggregates notes with SENT_REPLY)
$inquiries = [];
try {
    if ($filter === 'sent') {
        if ($has_notes) {
            $stmt = $pdo->query("SELECT id, full_name, email, notes, created_at FROM contact_messages WHERE notes LIKE '%SENT_REPLY START%' ORDER BY created_at DESC");
            $inquiries = $stmt->fetchAll();
        } else {
            $inquiries = [];
        }
    } else {
        $query = "SELECT * FROM contact_messages ";
        if ($filter === 'unread' && $has_is_read) {
            $query .= "WHERE is_read = 0 ";
        } elseif ($filter === 'spam' && $has_is_spam) {
            $query .= "WHERE is_spam = 1 ";
        }
        $query .= "ORDER BY created_at DESC";
        $stmt = $pdo->query($query);
        $inquiries = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = 'Error loading inquiries: ' . $e->getMessage();
}

// Get stats
$stats = [
    'total' => 0,
    'unread' => 0,
    'spam' => 0,
    'sent' => 0
];
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    $stats['total'] = $result->fetch()['count'];

    if ($has_is_read) {
        $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
        $stats['unread'] = $result->fetch()['count'];
    } else {
        $stats['unread'] = 0;
    }

    if ($has_is_spam) {
        $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_spam = 1");
        $stats['spam'] = $result->fetch()['count'];
    } else {
        $stats['spam'] = 0;
    }

    if ($has_notes) {
        $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE notes LIKE '%SENT_REPLY START%'");
        $stats['sent'] = $result->fetch()['count'];
    } else {
        $stats['sent'] = 0;
    }
} catch (PDOException $e) {}

// Handle mark as read
if (isset($_GET['mark_read']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['mark_read']);
    try {
        if ($has_is_read) {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($has_notes) {
            $note = 'Marked as read by admin on ' . date('Y-m-d H:i:s') . "\n";
            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
            $stmt->execute([$note, $id]);
        } else {
            // No is_read or notes column available; skip DB update
        }
        log_activity('update', 'inquiry', $id, 'Marked as read');
        $redirect_url = 'inquiries.php';
        $redirect_delay = 1;
    } catch (PDOException $e) {
        $error = 'Error updating inquiry: ' . $e->getMessage();
    }
}

// Handle delete
if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        log_activity('delete', 'inquiry', $id, 'Inquiry deleted');
        $message = 'Inquiry deleted.';
        $redirect_url = 'inquiries.php';
        $redirect_delay = 1;
    } catch (PDOException $e) {
        $error = 'Error deleting inquiry: ' . $e->getMessage();
    }
}

// Handle reply/send email
if (isset($_GET['reply']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['reply']);
    $reply_msg = trim($_POST['reply_message'] ?? '');
    $reply_subject = trim($_POST['reply_subject'] ?? 'Reply from Balmari');
    if ($reply_msg === '') {
        $error = 'Reply message cannot be empty.';
    } else {
        try {
            // Fetch recipient email
            $stmt = $pdo->prepare("SELECT email FROM contact_messages WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                $error = 'Inquiry not found.';
            } else {
                $to = $row['email'];

                // Load mail config and PHPMailer wrappers (check multiple locations to avoid fatal errors)
                $candidates = [];
                // 1) Environment override (recommended for production)
                $envPath = getenv('EMAIL_SETUP_PATH');
                if ($envPath) {
                    $candidates[] = rtrim($envPath, "\/\\") . DIRECTORY_SEPARATOR . 'config.php';
                }
                // 2) Safe location outside webroot (common on shared hosts)
                $candidates[] = '/home/u549992181/email_setup/config.php';
                // 3) Relative to public_html (developer convenience)
                $candidates[] = __DIR__ . '/../email_setup/config.php';

                $config_path = null;
                foreach ($candidates as $c) {
                    if (file_exists($c)) {
                        $config_path = $c;
                        break;
                    }
                }

                $mailer_dir = __DIR__ . '/../email_setup/PHPMailer';
                if (!$config_path) {
                    $error = 'Email configuration not found. Expected one of: ' . implode(', ', $candidates) . '. Upload config.php to one of these locations or set the EMAIL_SETUP_PATH environment variable.';
                } elseif (!file_exists($mailer_dir . '/PHPMailer.php')) {
                    $error = 'PHPMailer wrappers not found in email_setup/PHPMailer. Ensure PHPMailer files are present.';
                } else {
                    $config = require $config_path;
                    require_once $mailer_dir . '/Exception.php';
                    require_once $mailer_dir . '/PHPMailer.php';
                    require_once $mailer_dir . '/SMTP.php';

                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    try {
                        // Enable debug output when requested via ?mail_debug=1
                        if (isset($_GET['mail_debug']) && $_GET['mail_debug'] == '1') {
                            $mail->SMTPDebug = 2; // client + server messages
                            $mail->Debugoutput = 'html';
                        } else {
                            // Default to error_log to avoid exposing transcripts in production
                            $mail->SMTPDebug = 0;
                            $mail->Debugoutput = 'error_log';
                        }

                        $mail->isSMTP();
                        $mail->Host = $config['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $config['username'];
                        $mail->Password = $config['password'];
                        $mail->SMTPSecure = $config['encryption'];
                        $mail->Port = $config['port'];

                        $mail->setFrom($config['from_email'], $config['from_name']);
                        $mail->addAddress($to);
                        $mail->Subject = $reply_subject;
                        $mail->isHTML(true);
                        $mail->Body = nl2br(htmlspecialchars($reply_msg));
                        $mail->AltBody = $reply_msg;

                        $mail->send();

                        // Mark inquiry as read and log (handle missing column)
                        if ($has_is_read) {
                            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                            $stmt->execute([$id]);
                        } elseif ($has_notes) {
                            $note = 'Replied via admin on ' . date('Y-m-d H:i:s') . "\n";
                            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
                            $stmt->execute([$note, $id]);
                        } else {
                            // No place to mark as read in DB schema
                        }

                        // Record the sent reply in notes (structured) so admin can see sent messages
                        if ($has_notes) {
                            $sent_record = "=== SENT_REPLY START ===\n";
                            $sent_record .= "Time: " . date('Y-m-d H:i:s') . "\n";
                            $sent_record .= "Subject: " . $reply_subject . "\n";
                            $sent_record .= "Body:\n" . $reply_msg . "\n";
                            $sent_record .= "=== SENT_REPLY END ===\n";
                            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
                            $stmt->execute([$sent_record, $id]);
                        }
                        log_activity('email', 'inquiry', $id, 'Replied via admin');
                        $message = 'Reply sent.';
                            $redirect_url = 'inquiries.php';
                            $redirect_delay = 1;
                    } catch (\Exception $e) {
                        $error = 'Mailer Error: ' . $e->getMessage();
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Error sending reply: ' . $e->getMessage();
        }
    }
}

// Handle batch actions (Read / Spam / Archive / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_action'])) {
    $action = $_POST['batch_action'];
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids) || count($ids) === 0) {
        $error = 'No messages selected.';
    } else {
        $ids = array_map('intval', $ids);
        try {
            if ($action === 'delete') {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                log_activity('delete', 'inquiry', implode(',', $ids), 'Batch delete');
                $message = 'Deleted ' . count($ids) . ' messages.';
            } else {
                foreach ($ids as $iid) {
                    if ($action === 'read') {
                        if ($has_is_read) {
                            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                            $stmt->execute([$iid]);
                        } elseif ($has_notes) {
                            $note = 'Marked as read by admin on ' . date('Y-m-d H:i:s') . "\n";
                            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
                            $stmt->execute([$note, $iid]);
                        }
                    } elseif ($action === 'spam') {
                        if ($has_is_spam) {
                            $stmt = $pdo->prepare("UPDATE contact_messages SET is_spam = 1 WHERE id = ?");
                            $stmt->execute([$iid]);
                        } elseif ($has_status) {
                            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'spam' WHERE id = ?");
                            $stmt->execute([$iid]);
                        }
                    } elseif ($action === 'archive') {
                        if ($has_status) {
                            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'archived' WHERE id = ?");
                            $stmt->execute([$iid]);
                        } elseif ($has_notes) {
                            $note = 'Archived by admin on ' . date('Y-m-d H:i:s') . "\n";
                            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
                            $stmt->execute([$note, $iid]);
                        }
                    }
                }
                log_activity('update', 'inquiry', implode(',', $ids), 'Batch ' . $action);
                $message = ucfirst($action) . ' applied to ' . count($ids) . ' messages.';
            }
            $redirect_url = 'inquiries.php';
            $redirect_delay = 1;
        } catch (PDOException $e) {
            $error = 'Batch action failed: ' . $e->getMessage();
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

            <?php if (!empty($redirect_url)): ?>
            <script>
                (function(){
                    var d = <?php echo isset($redirect_delay) ? intval($redirect_delay) : 0; ?> * 1000;
                    setTimeout(function(){ window.location.href = <?php echo json_encode($redirect_url); ?>; }, d);
                })();
            </script>
            <?php endif; ?>
            
            <!-- Filter Tabs -->
            <div class="flex gap-4 mb-6">
                <a href="inquiries.php?filter=all" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'all' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">
                    <i class="fas fa-inbox mr-2"></i>All (<?php echo $stats['total']; ?>)
                </a>
                <a href="inquiries.php?filter=unread" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'unread' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">
                    <i class="fas fa-envelope mr-2"></i>Unread (<?php echo $stats['unread']; ?>)
                </a>
                <a href="inquiries.php?filter=spam" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'spam' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Spam (<?php echo $stats['spam']; ?>)
                </a>
                <a href="inquiries.php?filter=sent" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'sent' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">
                    <i class="fas fa-paper-plane mr-2"></i>Sent (<?php echo $stats['sent']; ?>)
                </a>
            </div>
            
            <!-- Batch actions (hidden until selection) -->
            <div id="batchActions" class="mb-4" style="display:none">
                <form id="batchActionToolbar" onsubmit="return false;" class="flex gap-2">
                    <button type="button" data-action="read" class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded text-sm hover:bg-blue-500/30">Read</button>
                    <button type="button" data-action="spam" class="px-3 py-1 bg-red-500/20 text-red-300 rounded text-sm hover:bg-red-500/30">Spam</button>
                    <button type="button" data-action="archive" class="px-3 py-1 bg-[#5C8374]/10 text-[#D3DAD9] rounded text-sm hover:bg-[#5C8374]/20">Archive</button>
                    <button type="button" data-action="delete" class="px-3 py-1 bg-red-700/10 text-red-300 rounded text-sm hover:bg-red-700/20">Delete</button>
                </form>
                <form id="batchForm" method="POST" action="inquiries.php" style="display:none">
                    <input type="hidden" name="batch_action" id="batch_action" value="" />
                    <div id="batch_ids_container"></div>
                </form>
            </div>

            <!-- Inquiries List -->
            <div class="bg-[#183D3D] rounded-lg overflow-hidden">
                <?php if (count($inquiries) > 0): ?>
                <div class="space-y-2">
                    <?php foreach ($inquiries as $inquiry): ?>
                    <div class="p-6 border-b border-[#5C8374]/30 hover:bg-[#040D12] transition">
                        <div class="flex justify-between items-start">
                            <div class="mr-4">
                                <input type="checkbox" class="inquiry-select-checkbox" data-id="<?php echo intval($inquiry['id']); ?>" aria-label="Select message" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="font-semibold text-[#D3DAD9]"><?php echo htmlspecialchars($inquiry['full_name']); ?></h4>
                                    <?php if (!($inquiry['is_read'] ?? 0)): ?>
                                    <span class="px-2 py-1 bg-blue-500/20 text-blue-300 text-xs rounded">New</span>
                                    <?php endif; ?>
                                    <?php if (($inquiry['is_spam'] ?? 0)): ?>
                                    <span class="px-2 py-1 bg-red-500/20 text-red-300 text-xs rounded">Spam</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-[#5C8374]">
                                    <i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($inquiry['email']); ?>
                                    <i class="fas fa-phone ml-4 mr-1"></i><?php echo htmlspecialchars($inquiry['phone']); ?>
                                </p>
                                <p class="text-sm text-[#5C8374] mt-1">
                                    <i class="fas fa-clock mr-1"></i><?php echo date('M j, Y \a\t g:ia', strtotime($inquiry['created_at'])); ?>
                                </p>
                                <p class="mt-3 text-[#D3DAD9]"><?php echo htmlspecialchars($inquiry['message'] ?? ''); ?></p>
                                <?php if (!empty($inquiry['notes'] ?? '')):
                                    $notes_raw = $inquiry['notes'];
                                    // Extract sent replies stored with SENT_REPLY markers
                                    $sent_replies = [];
                                    if (preg_match_all('/=== SENT_REPLY START ===\n(.*?)\n=== SENT_REPLY END ===/s', $notes_raw, $m)) {
                                        foreach ($m[1] as $block) {
                                            $lines = preg_split('/\r?\n/', trim($block));
                                            $entry = ['time' => '', 'subject' => '', 'body' => ''];
                                            $body_lines = [];
                                            $in_body = false;
                                            foreach ($lines as $line) {
                                                if (strpos($line, 'Time:') === 0) {
                                                    $entry['time'] = trim(substr($line, 5));
                                                } elseif (strpos($line, 'Subject:') === 0) {
                                                    $entry['subject'] = trim(substr($line, 8));
                                                } elseif (strpos($line, 'Body:') === 0) {
                                                    $in_body = true;
                                                } elseif ($in_body) {
                                                    $body_lines[] = $line;
                                                }
                                            }
                                            $entry['body'] = implode("\n", $body_lines);
                                            $sent_replies[] = $entry;
                                        }
                                    }

                                    // Remove SENT_REPLY blocks to show remaining free-form notes
                                    $remaining_notes = preg_replace('/=== SENT_REPLY START ===\n(.*?)\n=== SENT_REPLY END ===\n?/s', '', $notes_raw);
                                ?>
                                <?php if (!empty($sent_replies)): ?>
                                <div class="mt-2">
                                    <strong class="text-sm">Sent Replies</strong>
                                    <?php foreach ($sent_replies as $sr): ?>
                                    <div class="mt-2 p-3 bg-[#5C8374]/10 rounded text-sm">
                                        <div class="text-xs text-[#5C8374] mb-1"><?php echo htmlspecialchars($sr['time']); ?></div>
                                        <div class="font-semibold text-[#D3DAD9] mb-1"><?php echo htmlspecialchars($sr['subject']); ?></div>
                                        <div class="text-sm text-[#D3DAD9] whitespace-pre-line"><?php echo htmlspecialchars($sr['body']); ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (trim($remaining_notes) !== ''): ?>
                                <p class="mt-2 text-sm bg-[#5C8374]/20 p-2 rounded text-[#D3DAD9]">
                                    <strong>Note:</strong> <?php echo nl2br(htmlspecialchars(trim($remaining_notes))); ?>
                                </p>
                                <?php endif; ?>
                                <?php endif; ?>

                                <details class="mt-3">
                                    <summary class="cursor-pointer text-sm text-[#5C8374]">Reply via email</summary>
                                    <form method="POST" action="inquiries.php?reply=<?php echo $inquiry['id']; ?>" class="mt-2 flex flex-col gap-2">
                                        <input type="text" name="reply_subject" placeholder="Subject" class="px-2 py-1 rounded bg-[#183D3D] text-[#D3DAD9] border border-[#5C8374]/30" />
                                        <textarea name="reply_message" rows="4" required class="px-2 py-2 rounded bg-[#040D12] text-[#D3DAD9] border border-[#5C8374]/30"></textarea>
                                        <button type="submit" class="px-3 py-1 bg-green-500/20 text-green-300 rounded text-sm hover:bg-green-500/30 transition">Send Reply</button>
                                    </form>
                                </details>
                            </div>
                            <div class="flex flex-col gap-2 ml-4">
                                <?php if (!($inquiry['is_read'] ?? 0)): ?>
                                <form method="POST" action="inquiries.php?mark_read=<?php echo $inquiry['id']; ?>" style="display:inline;">
                                    <button type="submit" class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded text-sm hover:bg-blue-500/30 transition">
                                        <i class="fas fa-check mr-1"></i>Mark Read
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" action="inquiries.php?delete=<?php echo $inquiry['id']; ?>" style="display:inline;">
                                    <button type="submit" onclick="return confirm('Delete inquiry?');" class="px-3 py-1 bg-red-500/20 text-red-300 rounded text-sm hover:bg-red-500/30 transition">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="p-6 text-center text-[#5C8374]">
                    <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                    <p>No inquiries found.</p>
                </div>
                <?php endif; ?>
            </div>

            <script>
                (function(){
                    function qs(selector, root){ return (root||document).querySelector(selector); }
                    function qsa(selector, root){ return Array.prototype.slice.call((root||document).querySelectorAll(selector)); }

                    var checkboxes = qsa('.inquiry-select-checkbox');
                    var batchActions = qs('#batchActions');
                    var batchIdsContainer = qs('#batch_ids_container');
                    var batchForm = qs('#batchForm');
                    var batchActionInput = qs('#batch_action');

                    function updateBatchUI(){
                        var selected = qsa('.inquiry-select-checkbox:checked');
                        if (selected.length > 0) {
                            batchActions.style.display = '';
                        } else {
                            batchActions.style.display = 'none';
                        }
                        // rebuild hidden inputs
                        batchIdsContainer.innerHTML = '';
                        selected.forEach(function(cb){
                            var id = cb.getAttribute('data-id');
                            var inp = document.createElement('input');
                            inp.type = 'hidden';
                            inp.name = 'ids[]';
                            inp.value = id;
                            batchIdsContainer.appendChild(inp);
                        });
                    }

                    // attach change listeners
                    checkboxes.forEach(function(cb){ cb.addEventListener('change', updateBatchUI); });

                    // handle toolbar clicks
                    var toolbar = qs('#batchActionToolbar');
                    if (toolbar) {
                        toolbar.addEventListener('click', function(e){
                            var btn = e.target.closest('button[data-action]');
                            if (!btn) return;
                            var action = btn.getAttribute('data-action');
                            var selected = qsa('.inquiry-select-checkbox:checked');
                            if (selected.length === 0) {
                                alert('Select at least one message.');
                                return;
                            }
                            if (action === 'delete') {
                                if (!confirm('Delete selected messages? This cannot be undone.')) return;
                            }
                            batchActionInput.value = action;
                            // append current ids (updateBatchUI already built them)
                            batchForm.submit();
                        });
                    }

                    // initial UI update
                    updateBatchUI();
                })();
            </script>

<?php
// Flush output buffer (if we started one) before including footer
if (ob_get_level()) {
    @ob_end_flush();
}
require_once 'includes/footer.php'; ?>
