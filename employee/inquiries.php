<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!ob_get_level()) { ob_start(); }
$page_title = "Contact Inquiries";
require_once '../includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$error = '';
$message = '';
$filter = $_GET['filter'] ?? 'all';

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
} catch (Exception $e) {}

$inquiries = [];
try {
    if ($filter === 'sent') {
        if ($has_notes) {
            $stmt = $pdo->query("SELECT id, full_name, email, phone, message, notes, created_at FROM contact_messages WHERE notes LIKE '%SENT_REPLY START%' ORDER BY created_at DESC");
            $inquiries = $stmt->fetchAll();
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

$stats = ['total' => 0, 'unread' => 0, 'spam' => 0, 'sent' => 0];
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    $stats['total'] = $result->fetch()['count'];
    if ($has_is_read) { $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0"); $stats['unread'] = $result->fetch()['count']; }
    if ($has_is_spam) { $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_spam = 1"); $stats['spam'] = $result->fetch()['count']; }
    if ($has_notes) { $result = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE notes LIKE '%SENT_REPLY START%'"); $stats['sent'] = $result->fetch()['count']; }
} catch (PDOException $e) {}

if (isset($_GET['mark_read']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['mark_read']);
    try {
        if ($has_is_read) {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($has_notes) {
            $note = 'Marked as read by employee on ' . date('Y-m-d H:i:s') . "\n";
            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
            $stmt->execute([$note, $id]);
        }
        $message = 'Marked as read.';
    } catch (PDOException $e) {
        $error = 'Error updating inquiry: ' . $e->getMessage();
    }
}

if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Inquiry deleted.';
    } catch (PDOException $e) {
        $error = 'Error deleting inquiry: ' . $e->getMessage();
    }
}

if (isset($_GET['reply']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['reply']);
    $reply_msg = trim($_POST['reply_message'] ?? '');
    $reply_subject = trim($_POST['reply_subject'] ?? 'Reply from Balmari');
    if ($reply_msg === '') {
        $error = 'Reply message cannot be empty.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT email FROM contact_messages WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                $error = 'Inquiry not found.';
            } else {
                $to = $row['email'];
                $candidates = [];
                $envPath = getenv('EMAIL_SETUP_PATH');
                if ($envPath) { $candidates[] = rtrim($envPath, "\/\\") . DIRECTORY_SEPARATOR . 'config.php'; }
                $candidates[] = '/home/u549992181/email_setup/config.php';
                $candidates[] = __DIR__ . '/../email_setup/config.php';

                $config_path = null;
                foreach ($candidates as $c) { if (file_exists($c)) { $config_path = $c; break; } }
                $mailer_dir = __DIR__ . '/../email_setup/PHPMailer';
                if (!$config_path) {
                    $error = 'Email configuration not found.';
                } elseif (!file_exists($mailer_dir . '/PHPMailer.php')) {
                    $error = 'PHPMailer wrappers not found in email_setup/PHPMailer.';
                } else {
                    $config = require $config_path;
                    require_once $mailer_dir . '/Exception.php';
                    require_once $mailer_dir . '/PHPMailer.php';
                    require_once $mailer_dir . '/SMTP.php';

                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    try {
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

                        if ($has_is_read) {
                            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                            $stmt->execute([$id]);
                        } elseif ($has_notes) {
                            $note = 'Replied via employee on ' . date('Y-m-d H:i:s') . "\n";
                            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
                            $stmt->execute([$note, $id]);
                        }

                        if ($has_notes) {
                            $sent_record = "=== SENT_REPLY START ===\n";
                            $sent_record .= "Time: " . date('Y-m-d H:i:s') . "\n";
                            $sent_record .= "Subject: " . $reply_subject . "\n";
                            $sent_record .= "Body:\n" . $reply_msg . "\n";
                            $sent_record .= "=== SENT_REPLY END ===\n";
                            $stmt = $pdo->prepare("UPDATE contact_messages SET notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ?");
                            $stmt->execute([$sent_record, $id]);
                        }

                        $message = 'Reply sent.';
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

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if ($message): ?><div class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-300"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-300"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-[#183D3D] p-4 rounded-lg"><p class="text-[#5C8374] text-sm">Total Logs</p><p class="text-2xl font-bold text-[#D3DAD9]"><?php echo count($inquiries); ?></p></div>
        <div class="bg-[#183D3D] p-4 rounded-lg"><p class="text-[#5C8374] text-sm">Unread</p><p class="text-2xl font-bold text-[#D3DAD9]"><?php echo $stats['unread']; ?></p></div>
        <div class="bg-[#183D3D] p-4 rounded-lg"><p class="text-[#5C8374] text-sm">Sent</p><p class="text-2xl font-bold text-[#D3DAD9]"><?php echo $stats['sent']; ?></p></div>
    </div>

    <div class="flex gap-4 mb-6 flex-wrap">
        <a href="inquiries.php?filter=all" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'all' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">All (<?php echo $stats['total']; ?>)</a>
        <a href="inquiries.php?filter=unread" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'unread' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">Unread (<?php echo $stats['unread']; ?>)</a>
        <a href="inquiries.php?filter=spam" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'spam' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">Spam (<?php echo $stats['spam']; ?>)</a>
        <a href="inquiries.php?filter=sent" class="px-4 py-2 rounded-lg transition <?php echo $filter === 'sent' ? 'bg-[#5C8374] text-[#D3DAD9]' : 'bg-[#183D3D] text-[#5C8374] hover:bg-[#715A5A]'; ?>">Sent (<?php echo $stats['sent']; ?>)</a>
    </div>

    <div class="bg-[#183D3D] rounded-lg overflow-hidden">
        <?php if (count($inquiries) > 0): ?>
        <div class="space-y-2">
            <?php foreach ($inquiries as $inquiry): ?>
            <div class="p-6 border-b border-[#5C8374]/30 hover:bg-[#040D12] transition">
                <div class="flex justify-between items-start">
                    <div class="mr-4"><input type="checkbox" disabled class="inquiry-select-checkbox opacity-50" /></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2"><h4 class="font-semibold text-[#D3DAD9]"><?php echo htmlspecialchars($inquiry['full_name']); ?></h4></div>
                        <p class="text-sm text-[#5C8374]"><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($inquiry['email']); ?> <i class="fas fa-phone ml-4 mr-1"></i><?php echo htmlspecialchars($inquiry['phone'] ?? ''); ?></p>
                        <p class="text-sm text-[#5C8374] mt-1"><i class="fas fa-clock mr-1"></i><?php echo date('M j, Y \a\t g:ia', strtotime($inquiry['created_at'])); ?></p>
                        <p class="mt-3 text-[#D3DAD9]"><?php echo htmlspecialchars($inquiry['message'] ?? ''); ?></p>
                        <?php if (!empty($inquiry['notes'] ?? '')): $notes_raw = $inquiry['notes']; $sent_replies = []; if (preg_match_all('/=== SENT_REPLY START ===\n(.*?)\n=== SENT_REPLY END ===/s', $notes_raw, $m)) { foreach ($m[1] as $block) { $lines = preg_split('/\r?\n/', trim($block)); $entry = ['time' => '', 'subject' => '', 'body' => '']; $body_lines = []; $in_body = false; foreach ($lines as $line) { if (strpos($line, 'Time:') === 0) { $entry['time'] = trim(substr($line, 5)); } elseif (strpos($line, 'Subject:') === 0) { $entry['subject'] = trim(substr($line, 8)); } elseif (strpos($line, 'Body:') === 0) { $in_body = true; } elseif ($in_body) { $body_lines[] = $line; } } $entry['body'] = implode("\n", $body_lines); $sent_replies[] = $entry; } } $remaining_notes = preg_replace('/=== SENT_REPLY START ===\n(.*?)\n=== SENT_REPLY END ===\n?/s', '', $notes_raw); ?>
                        <?php if (!empty($sent_replies)): ?><div class="mt-2"><strong class="text-sm">Sent Replies</strong><?php foreach ($sent_replies as $sr): ?><div class="mt-2 p-3 bg-[#5C8374]/10 rounded text-sm"><div class="text-xs text-[#5C8374] mb-1"><?php echo htmlspecialchars($sr['time']); ?></div><div class="font-semibold text-[#D3DAD9] mb-1"><?php echo htmlspecialchars($sr['subject']); ?></div><div class="text-sm text-[#D3DAD9] whitespace-pre-line"><?php echo htmlspecialchars($sr['body']); ?></div></div><?php endforeach; ?></div><?php endif; ?>
                        <?php if (trim($remaining_notes) !== ''): ?><p class="mt-2 text-sm bg-[#5C8374]/20 p-2 rounded text-[#D3DAD9]"><strong>Note:</strong> <?php echo nl2br(htmlspecialchars(trim($remaining_notes))); ?></p><?php endif; ?>
                        <?php endif; ?>
                        <details class="mt-3"><summary class="cursor-pointer text-sm text-[#5C8374]">Reply via email</summary><form method="POST" action="inquiries.php?reply=<?php echo $inquiry['id']; ?>" class="mt-2 flex flex-col gap-2"><input type="text" name="reply_subject" placeholder="Subject" class="px-2 py-1 rounded bg-[#183D3D] text-[#D3DAD9] border border-[#5C8374]/30" /><textarea name="reply_message" rows="4" required class="px-2 py-2 rounded bg-[#040D12] text-[#D3DAD9] border border-[#5C8374]/30"></textarea><button type="submit" class="px-3 py-1 bg-green-500/20 text-green-300 rounded text-sm hover:bg-green-500/30 transition">Send Reply</button></form></details>
                    </div>
                    <div class="flex flex-col gap-2 ml-4">
                        <?php if (!($inquiry['is_read'] ?? 0)): ?><form method="POST" action="inquiries.php?mark_read=<?php echo $inquiry['id']; ?>"><button type="submit" class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded text-sm hover:bg-blue-500/30 transition">Mark Read</button></form><?php endif; ?>
                        <form method="POST" action="inquiries.php?delete=<?php echo $inquiry['id']; ?>"><button type="submit" onclick="return confirm('Delete inquiry?');" class="px-3 py-1 bg-red-500/20 text-red-300 rounded text-sm hover:bg-red-500/30 transition">Delete</button></form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?><div class="p-6 text-center text-[#5C8374]"><i class="fas fa-inbox text-3xl mb-2 opacity-50"></i><p>No inquiries found.</p></div><?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>