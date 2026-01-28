<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Inventory';
$currentPage = 'inventory';

$stmt = $pdo->query("
    SELECT il.*, p.name as product_name 
    FROM inventory_logs il 
    JOIN products p ON il.product_id = p.id 
    ORDER BY il.created_at DESC 
    LIMIT 50
");
$logs = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM products WHERE is_deleted = FALSE ORDER BY name");
$products = $stmt->fetchAll();

$restockId = $_GET['restock'] ?? null;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Inventory Logs</h1>
            <p class="page-subtitle">Track stock movements and adjustments</p>
        </div>
        <button class="btn btn-primary" onclick="openRestockModal()">+ Restock Item</button>
    </div>
    
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Product</th>
                    <th>Reason</th>
                    <th>Change</th>
                    <th>Old Stock</th>
                    <th>New Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #9ca3af;">No inventory logs yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['product_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($log['reason']); ?>">
                                    <?php echo ucfirst($log['reason']); ?>
                                </span>
                            </td>
                            <td class="<?php echo $log['change_amount'] > 0 ? 'change-positive' : 'change-negative'; ?>">
                                <?php echo $log['change_amount'] > 0 ? '+' : ''; ?><?php echo $log['change_amount']; ?>
                            </td>
                            <td><?php echo $log['old_stock']; ?></td>
                            <td><?php echo $log['new_stock']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal-overlay" id="restockModal">
    <div class="modal">
        <h3 class="modal-title">Restock Item</h3>
        <form id="restockForm">
            <div class="form-group">
                <label for="restockProduct">Product</label>
                <select id="restockProduct" name="product_id" required>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>" <?php echo $restockId == $product['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['name']); ?> (Current: <?php echo $product['stock']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="restockAmount">Amount to Add</label>
                <input type="number" id="restockAmount" name="amount" min="1" required>
            </div>
            <div class="form-group">
                <label for="restockReason">Reason</label>
                <select id="restockReason" name="reason">
                    <option value="restock">Restock</option>
                    <option value="adjustment">Adjustment</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRestockModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRestockModal() {
    document.getElementById('restockModal').classList.add('active');
}

function closeRestockModal() {
    document.getElementById('restockModal').classList.remove('active');
}

document.getElementById('restockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'restock');
    
    fetch('api/inventory.php', {
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
});

<?php if ($restockId): ?>
openRestockModal();
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
