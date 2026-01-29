<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
requireLogin();

$pageTitle = 'Settings';
$currentPage = 'settings';

// --------------------
// Load settings
// --------------------
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id ASC LIMIT 1");
$settings = $stmt->fetch();

if (!$settings) {
    $pdo->exec("INSERT INTO settings (filter_type) VALUES ('daily')");
    $stmt = $pdo->query("SELECT * FROM settings ORDER BY id ASC LIMIT 1");
    $settings = $stmt->fetch();
}

// --------------------
// Helper: check column exists
// --------------------
function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

$hasIsDeleted  = columnExists($pdo, 'products', 'is_deleted');
$hasDeletedAt  = columnExists($pdo, 'products', 'deleted_at');

// --------------------
// Trash products (safe)
// --------------------
$trashedProducts = [];

if ($hasIsDeleted) {
    if ($hasDeletedAt) {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_deleted = 1 ORDER BY deleted_at DESC");
    } else {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_deleted = 1 ORDER BY id DESC");
    }
    $trashedProducts = $stmt->fetchAll();
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-subtitle">Configure application preferences</p>
        </div>
    </div>

    <div class="settings-section">
        <h3 class="settings-title">Dashboard Filter</h3>
        <p class="settings-desc">Choose how data is aggregated on the dashboard.</p>

        <div class="form-group" style="max-width: 300px;">
            <label>Filter Type</label>
            <select id="filterType" onchange="updateSettings()">
                <option value="daily"   <?php echo ($settings['filter_type'] === 'daily') ? 'selected' : ''; ?>>Daily (Default)</option>
                <option value="weekly"  <?php echo ($settings['filter_type'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                <option value="monthly" <?php echo ($settings['filter_type'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
            </select>
        </div>
    </div>

    <div class="settings-section trash-section">
        <h3 class="settings-title">Trash (Recently Deleted)</h3>
        <p class="settings-desc">Deleted products will stay here until you empty the trash.</p>

        <?php if (!$hasIsDeleted): ?>
            <div class="trash-empty">
                <div style="color:#6b7280;">
                    Trash is disabled because <b>products.is_deleted</b> column is missing.
                    <br>Run this SQL to enable trash:
                    <pre style="margin-top:10px;background:#111827;color:#e5e7eb;padding:10px;border-radius:10px;overflow:auto;">
ALTER TABLE products ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE products ADD COLUMN deleted_at DATETIME NULL;
                    </pre>
                </div>
            </div>

        <?php elseif (empty($trashedProducts)): ?>
            <div class="trash-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <div>Trash is empty</div>
            </div>

        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trashedProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>
                                <?php
                                if ($hasDeletedAt && !empty($product['deleted_at'])) {
                                    echo date('M d, Y H:i', strtotime($product['deleted_at']));
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="restoreProduct(<?php echo (int)$product['id']; ?>)">Restore</button>
                                <button class="btn btn-sm btn-danger" onclick="permanentDelete(<?php echo (int)$product['id']; ?>)">Delete Forever</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<script>
function updateSettings() {
    const filterType = document.getElementById('filterType').value;

    const formData = new FormData();
    formData.append('action', 'update_filter');
    formData.append('filter_type', filterType);

    fetch('api/settings.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (!data.success) alert('Error updating settings');
    })
    .catch(() => alert('Error updating settings'));
}

function restoreProduct(id) {
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('id', id);

    fetch('api/products.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error: ' + (data.message || 'Failed'));
    })
    .catch(() => alert('Error restoring product'));
}

function permanentDelete(id) {
    if (!confirm('Are you sure you want to permanently delete this product? This cannot be undone.')) return;

    const formData = new FormData();
    formData.append('action', 'permanent_delete');
    formData.append('id', id);

    fetch('api/products.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error: ' + (data.message || 'Failed'));
    })
    .catch(() => alert('Error deleting product'));
}
</script>

<?php require_once 'includes/footer.php'; ?>
