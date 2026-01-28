<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Settings';
$currentPage = 'settings';

$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $stmt->fetch();
if (!$settings) {
    $pdo->query("INSERT INTO settings (filter_type) VALUES ('daily')");
    $settings = ['filter_type' => 'daily'];
}

$stmt = $pdo->query("SELECT * FROM products WHERE is_deleted = TRUE ORDER BY deleted_at DESC");
$trashedProducts = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
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
                <option value="daily" <?php echo $settings['filter_type'] === 'daily' ? 'selected' : ''; ?>>Daily (Default)</option>
                <option value="weekly" <?php echo $settings['filter_type'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                <option value="monthly" <?php echo $settings['filter_type'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
            </select>
        </div>
    </div>
    
    <div class="settings-section trash-section">
        <h3 class="settings-title">Trash (Recently Deleted)</h3>
        <p class="settings-desc">Deleted products will stay here until you empty the trash.</p>
        
        <?php if (empty($trashedProducts)): ?>
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
                            <td><?php echo date('M d, Y H:i', strtotime($product['deleted_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="restoreProduct(<?php echo $product['id']; ?>)">Restore</button>
                                <button class="btn btn-sm btn-danger" onclick="permanentDelete(<?php echo $product['id']; ?>)">Delete Forever</button>
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
    
    fetch('api/settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error updating settings');
        }
    });
}

function restoreProduct(id) {
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('id', id);
    
    fetch('api/products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function permanentDelete(id) {
    if (confirm('Are you sure you want to permanently delete this product? This cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'permanent_delete');
        formData.append('id', id);
        
        fetch('api/products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
