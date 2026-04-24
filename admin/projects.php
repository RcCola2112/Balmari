<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Simple selector page that links to admin-managed Completed and Progress lists
$page_title = "Projects";
require_once 'includes/header.php';
require_login();

$action = $_GET['action'] ?? 'list';
$project_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Get all projects (both ongoing and completed)
$ongoing_projects = [];
$completed_projects = [];

// Prefer filesystem-based projects (match public pages). Fallback to DB if folders missing.
$rootPath = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');
$fsCompletedDir = $rootPath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . 'completed' . DIRECTORY_SEPARATOR;
$fsProgressDir = $rootPath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . 'progress' . DIRECTORY_SEPARATOR;

$loadFromFs = function($dir) {
    $list = [];
    if (!is_dir($dir)) return $list;
    $folders = array_diff(scandir($dir, SCANDIR_SORT_DESCENDING), ['.', '..']);
    foreach ($folders as $folder) {
        $projectPath = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        $metadataFile = $projectPath . 'metadata.json';
        if (is_dir($projectPath) && file_exists($metadataFile)) {
            $m = json_decode(file_get_contents($metadataFile), true);
            if ($m) {
                $m['id'] = $folder;
                $m['title'] = $m['title'] ?? ($m['name'] ?? 'Untitled');
                $m['location'] = $m['location'] ?? '';
                $m['created_at'] = $m['created_at'] ?? date('Y-m-d H:i:s', filemtime($metadataFile));
                $list[] = $m;
            }
        }
    }
    return $list;
};

// Try filesystem first
$completed_projects = $loadFromFs($fsCompletedDir);
$ongoing_projects = $loadFromFs($fsProgressDir);

// If neither folder has data, fall back to DB tables
if (empty($completed_projects) && empty($ongoing_projects)) {
    try {
        $stmt = $pdo->query("SELECT id, title, location, created_at FROM projects_in_progress ORDER BY created_at DESC");
        $ongoing_projects = $stmt->fetchAll();
        
        $stmt = $pdo->query("SELECT id, title, location, created_at FROM completed_projects ORDER BY created_at DESC");
        $completed_projects = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Error loading projects: ' . $e->getMessage();
    }
}

// Handle project deletion (support filesystem-based projects and DB-backed projects)
if ($action === 'delete' && $project_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'progress';
    $table = ($type === 'completed') ? 'completed_projects' : 'projects_in_progress';
    $img_table = ($type === 'completed') ? 'completed_project_images' : 'progress_project_images';

    // If filesystem project exists, remove directory
    $fsDir = $rootPath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . ($type === 'completed' ? 'completed' : 'progress') . DIRECTORY_SEPARATOR . $project_id . DIRECTORY_SEPARATOR;
    $deleted = false;
    if (is_dir($fsDir)) {
        // recursive delete
        $it = new RecursiveDirectoryIterator($fsDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        if (@rmdir($fsDir)) {
            $deleted = true;
            log_activity('delete', 'project', $project_id, "Project folder deleted: $fsDir");
            $message = 'Project folder deleted successfully.';
            header('Location: projects.php');
            exit;
        } else {
            $error = 'Failed to delete project folder: ' . $fsDir;
        }
    }

    // If not filesystem or filesystem delete failed, try DB deletion
    if (!$deleted) {
        try {
            $pdo->beginTransaction();

            // Delete images first
            $stmt = $pdo->prepare("DELETE FROM $img_table WHERE project_id = ?");
            $stmt->execute([$project_id]);

            // Delete project
            $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
            $stmt->execute([$project_id]);

            $pdo->commit();

            log_activity('delete', 'project', $project_id, "Project deleted from $table");
            $message = 'Project deleted successfully.';
            header('Location: projects.php');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error deleting project: ' . $e->getMessage();
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <a href="manage_completed.php" class="block bg-[#183D3D] rounded-lg p-6 hover:border-[#5C8374] border border-transparent">
                                <h3 class="text-xl font-bold text-[#D3DAD9] mb-2"><i class="fas fa-check-circle mr-2 text-[#5C8374]"></i>Completed Work</h3>
                                <p class="text-[#5C8374]">Manage completed projects — view, edit, upload, or delete projects identical to the public Completed Works page.</p>
                            </a>
                            <a href="manage_progress.php" class="block bg-[#183D3D] rounded-lg p-6 hover:border-[#5C8374] border border-transparent">
                                <h3 class="text-xl font-bold text-[#D3DAD9] mb-2"><i class="fas fa-spinner mr-2 text-[#5C8374]"></i>Project in Progress</h3>
                                <p class="text-[#5C8374]">Manage ongoing projects — view, edit, upload, or delete projects identical to the public Works in Progress page.</p>
                            </a>
                        </div>
            


<?php require_once 'includes/footer.php'; ?>
