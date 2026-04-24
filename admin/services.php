<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Services";
require_once 'includes/header.php';
require_login();

$error = '';
$message = '';

// Check if services table exists before attempting DB logic
$services = [];
$has_services_table = false;
try {
    $chk = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($chk && $chk->rowCount() > 0) {
        $has_services_table = true;
    }
} catch (Exception $e) {
    // ignore
}

if ($has_services_table) {
    try {
        // Detect ordering column to support older/newer schemas
        $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services'");
        $colStmt->execute();
        $svcCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('display_order', $svcCols, true)) {
            $order_col = 'display_order';
        } elseif (in_array('sort_order', $svcCols, true)) {
            $order_col = 'sort_order';
        } else {
            $order_col = 'id';
        }

        $stmt = $pdo->query("SELECT * FROM services ORDER BY " . $order_col . " ASC");
        $services = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Error loading services.';
    }
} else {
    $error = 'Services feature unavailable: the `services` table is not present in the database.';
}

// Handle add/edit service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (!$has_services_table) {
        $error = 'Cannot save: `services` table is not present in the database.';
    } else {
        if (empty($title)) {
            $error = 'Service title is required.';
        } else {
            try {
                if ($action === 'edit' && $id) {
                    // Use detected ordering column when updating
                    $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, " . $order_col . " = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $display_order, $is_active, $id]);
                    log_activity('update', 'service', $id, 'Service updated');
                    $message = 'Service updated successfully.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO services (title, description, " . $order_col . ", is_active) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $display_order, $is_active]);
                    log_activity('create', 'service', $pdo->lastInsertId(), 'New service created');
                    $message = 'Service created successfully.';
                }
                header('Refresh: 2; url=services.php');
            } catch (PDOException $e) {
                $error = 'Error saving service.';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_GET['delete']);
    if (!$has_services_table) {
        $error = 'Cannot delete: `services` table is not present in the database.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            log_activity('delete', 'service', $id, 'Service deleted');
            $message = 'Service deleted successfully.';
            header('Refresh: 1; url=services.php');
        } catch (PDOException $e) {
            $error = 'Error deleting service.';
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
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Add/Edit Form -->
                <div class="lg:col-span-1">
                    <div class="bg-[#183D3D] rounded-lg p-6">
                        <h3 class="text-lg font-bold text-[#D3DAD9] mb-4">
                            <i class="fas fa-plus-circle mr-2 text-[#715A5A]"></i>Add Service
                        </h3>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add">
                            
                            <div>
                                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Service Title</label>
                                <input type="text" name="title" required class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5C8374]" placeholder="e.g., Architectural Design">
                            </div>
                            
                            <div>
                                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Description</label>
                                <textarea name="description" rows="4" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5C8374]" placeholder="Service description..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-[#D3DAD9] text-sm font-semibold mb-2">Display Order</label>
                                <input type="number" name="display_order" value="0" class="w-full px-3 py-2 bg-[#040D12] text-[#D3DAD9] border border-[#5C8374] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5C8374]">
                            </div>
                            
                            <div>
                                <label class="flex items-center text-[#D3DAD9]">
                                    <input type="checkbox" name="is_active" checked class="mr-2">
                                    <span class="text-sm">Active</span>
                                </label>
                            </div>
                            
                            <button type="submit" class="w-full px-4 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition">
                                <i class="fas fa-check mr-2"></i>Create Service
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Services List -->
                <div class="lg:col-span-2">
                    <div class="bg-[#183D3D] rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-[#5C8374]/30">
                            <h3 class="text-lg font-bold text-[#D3DAD9]">
                                <i class="fas fa-list mr-2 text-[#5C8374]"></i>Services (<?php echo count($services); ?>)
                            </h3>
                        </div>
                        
                        <?php if (count($services) > 0): ?>
                        <div class="space-y-2">
                            <?php foreach ($services as $service): ?>
                            <div class="p-4 border-b border-[#5C8374]/20 hover:bg-[#040D12] transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[#D3DAD9]"><?php echo htmlspecialchars($service['title']); ?></h4>
                                        <p class="text-sm text-[#5C8374] mt-1"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>...</p>
                                        <div class="flex gap-2 mt-2 text-xs">
                                            <span class="px-2 py-1 bg-[#5C8374]/30 text-[#D3DAD9] rounded">Order: <?php echo htmlspecialchars($service[$order_col] ?? 0); ?></span>
                                            <span class="px-2 py-1 <?php echo $service['is_active'] ? 'bg-green-500/30 text-green-300' : 'bg-red-500/30 text-red-300'; ?> rounded">
                                                <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded text-sm hover:bg-blue-500/30 transition" onclick="editService(<?php echo $service['id']; ?>)">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <form method="POST" action="services.php?delete=<?php echo $service['id']; ?>" style="display:inline;">
                                            <button type="submit" onclick="return confirm('Delete this service?');" class="px-3 py-1 bg-red-500/20 text-red-300 rounded text-sm hover:bg-red-500/30 transition">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="p-6 text-center text-[#715A5A]">
                            <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                            <p>No services added yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

<?php require_once 'includes/footer.php'; ?>
