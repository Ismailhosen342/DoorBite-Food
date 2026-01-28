<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Products';
$currentPage = 'products';

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM products WHERE is_deleted = FALSE";
$params = [];

if ($search) {
    $query .= " AND LOWER(name) LIKE LOWER(?)";
    $params[] = "%$search%";
}

$query .= " ORDER BY id";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Products</h1>
            <p class="page-subtitle">Manage your product catalog</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('add')">+ Add Product</button>
    </div>
    
    <div class="search-box" style="max-width: 400px; margin-bottom: 20px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#9ca3af">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="searchInput" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td>#<?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>RM <?php echo number_format($product['buying_price'], 2); ?></td>
                        <td>RM <?php echo number_format($product['selling_price'], 2); ?></td>
                        <td class="<?php echo $product['stock'] < 10 ? 'stock-low' : ''; ?>">
                            <?php echo $product['stock']; ?>
                        </td>
                        <td>
                            <button class="action-btn" onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($product)); ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button class="action-btn delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal-overlay" id="productModal">
    <div class="modal">
        <h3 class="modal-title" id="modalTitle">Add Product</h3>
        <form id="productForm">
            <input type="hidden" id="productId" name="id">
            <div class="form-group">
                <label for="productName">Product Name</label>
                <input type="text" id="productName" name="name" required>
            </div>
            <div class="form-group">
                <label for="buyingPrice">Buying Price (RM)</label>
                <input type="number" id="buyingPrice" name="buying_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="sellingPrice">Selling Price (RM)</label>
                <input type="number" id="sellingPrice" name="selling_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="stock">Initial Stock</label>
                <input type="number" id="stock" name="stock" value="0" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditing = false;

function openModal(mode, product = null) {
    isEditing = mode === 'edit';
    document.getElementById('modalTitle').textContent = isEditing ? 'Edit Product' : 'Add Product';
    document.getElementById('productModal').classList.add('active');
    
    if (isEditing && product) {
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('buyingPrice').value = product.buying_price;
        document.getElementById('sellingPrice').value = product.selling_price;
        document.getElementById('stock').value = product.stock;
    } else {
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
    }
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', isEditing ? 'update' : 'add');
    
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
});

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
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

document.getElementById('searchInput').addEventListener('input', function(e) {
    const search = e.target.value;
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        window.location.href = 'products.php?search=' + encodeURIComponent(search);
    }, 500);
});
</script>

<?php include 'includes/footer.php'; ?>
