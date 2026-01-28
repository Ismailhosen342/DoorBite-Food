<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Sales';
$currentPage = 'sales';

$stmt = $pdo->query("SELECT * FROM products WHERE is_deleted = FALSE ORDER BY name");
$products = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Sales</h1>
        </div>
    </div>
    
    <div class="sales-layout">
        <div>
            <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#9ca3af">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="productSearch" placeholder="Search products to add...">
            </div>
            
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card <?php echo $product['stock'] < 10 ? 'low-stock' : ''; ?>" 
                         data-id="<?php echo $product['id']; ?>"
                         data-name="<?php echo htmlspecialchars($product['name']); ?>"
                         data-price="<?php echo $product['selling_price']; ?>"
                         data-stock="<?php echo $product['stock']; ?>"
                         onclick="addToCart(this)">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-stock">Stock: <?php echo $product['stock']; ?></div>
                        <div class="product-price">RM <?php echo number_format($product['selling_price'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="cart-section">
            <h3 class="cart-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Current Sale
            </h3>
            
            <div id="cartEmpty" class="cart-empty">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <div>Cart is empty</div>
            </div>
            
            <div id="cartItems" class="cart-items" style="display: none;"></div>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal">RM 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax (0%)</span>
                    <span id="tax">RM 0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total">RM 0.00</span>
                </div>
            </div>
            
            <button class="complete-sale-btn" id="completeSaleBtn" onclick="completeSale()" disabled>Complete Sale</button>
        </div>
    </div>
</main>

<script>
let cart = [];

function addToCart(element) {
    const id = element.dataset.id;
    const name = element.dataset.name;
    const price = parseFloat(element.dataset.price);
    const stock = parseInt(element.dataset.stock);
    
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        if (existingItem.quantity < stock) {
            existingItem.quantity++;
        } else {
            alert('Not enough stock!');
            return;
        }
    } else {
        if (stock > 0) {
            cart.push({ id, name, price, quantity: 1, stock });
        } else {
            alert('Out of stock!');
            return;
        }
    }
    
    updateCart();
}

function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.quantity > item.stock) {
            item.quantity = item.stock;
            alert('Not enough stock!');
        }
    }
    updateCart();
}

function updateCart() {
    const cartEmpty = document.getElementById('cartEmpty');
    const cartItems = document.getElementById('cartItems');
    const completeSaleBtn = document.getElementById('completeSaleBtn');
    
    if (cart.length === 0) {
        cartEmpty.style.display = 'block';
        cartItems.style.display = 'none';
        completeSaleBtn.disabled = true;
    } else {
        cartEmpty.style.display = 'none';
        cartItems.style.display = 'block';
        completeSaleBtn.disabled = false;
        
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div>
                    <div class="cart-item-name">${item.name}</div>
                </div>
                <div class="cart-item-qty">
                    <button class="qty-btn" onclick="updateQuantity('${item.id}', -1)">-</button>
                    <span>${item.quantity}</span>
                    <button class="qty-btn" onclick="updateQuantity('${item.id}', 1)">+</button>
                </div>
                <div class="cart-item-price">RM ${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `).join('');
    }
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = 0;
    const total = subtotal + tax;
    
    document.getElementById('subtotal').textContent = 'RM ' + subtotal.toFixed(2);
    document.getElementById('tax').textContent = 'RM ' + tax.toFixed(2);
    document.getElementById('total').textContent = 'RM ' + total.toFixed(2);
}

function completeSale() {
    if (cart.length === 0) return;
    
    const formData = new FormData();
    formData.append('action', 'complete_sale');
    formData.append('items', JSON.stringify(cart));
    
    fetch('api/sales.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sale completed successfully!');
            cart = [];
            updateCart();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error completing sale');
        console.error(error);
    });
}

document.getElementById('productSearch').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        card.style.display = name.includes(search) ? 'block' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
