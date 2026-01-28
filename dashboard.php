<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM sales WHERE DATE(created_at) = CURRENT_DATE");
$todayRevenue = $stmt->fetch()['revenue'];

$stmt = $pdo->query("
    SELECT COALESCE(SUM(s.total_amount) - SUM(si.quantity * p.buying_price), 0) as profit
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    JOIN products p ON si.product_id = p.id
    WHERE DATE(s.created_at) = CURRENT_DATE
");
$todayProfit = $stmt->fetch()['profit'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_deleted = FALSE");
$totalProducts = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock < 10 AND is_deleted = FALSE");
$lowStockCount = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT * FROM products WHERE stock < 10 AND is_deleted = FALSE ORDER BY stock ASC LIMIT 5");
$lowStockProducts = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT p.name, si.quantity, (si.quantity * si.unit_price) as amount
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.created_at) = CURRENT_DATE
    ORDER BY s.created_at DESC
    LIMIT 5
");
$recentSales = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
        </div>
        <div class="page-date">Today: <?php echo date('F jS, Y'); ?></div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">
                <span>Total Revenue</span>
                <span>$</span>
            </div>
            <div class="stat-value revenue">RM <?php echo number_format($todayRevenue, 2); ?></div>
            <div class="stat-sub">Today's earnings</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">
                <span>Net Profit</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div class="stat-value profit">RM <?php echo number_format($todayProfit, 2); ?></div>
            <div class="stat-sub">Today's profit</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">
                <span>Total Products</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="stat-value"><?php echo $totalProducts; ?></div>
            <div class="stat-sub">In inventory</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">
                <span>Low Stock</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="stat-value warning"><?php echo $lowStockCount; ?></div>
            <div class="stat-sub">Items need restocking</div>
        </div>
    </div>
    
    <div class="content-grid">
        <div class="card">
            <h3 class="card-title">Low Stock Alerts</h3>
            <?php if (empty($lowStockProducts)): ?>
                <p style="color: #9ca3af; text-align: center; padding: 20px;">No low stock alerts</p>
            <?php else: ?>
                <?php foreach ($lowStockProducts as $product): ?>
                    <div class="alert-item">
                        <div style="display: flex; align-items: center;">
                            <span class="alert-dot"></span>
                            <div>
                                <div class="alert-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="alert-stock">Stock: <?php echo $product['stock']; ?></div>
                            </div>
                        </div>
                        <a href="inventory.php?restock=<?php echo $product['id']; ?>" class="alert-action">Restock Needed</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3 class="card-title">Recent Sales</h3>
            <?php if (empty($recentSales)): ?>
                <p style="color: #9ca3af; text-align: center; padding: 20px;">No sales today</p>
            <?php else: ?>
                <?php foreach ($recentSales as $sale): ?>
                    <div class="sale-item">
                        <div>
                            <div class="sale-name"><?php echo htmlspecialchars($sale['name']); ?></div>
                            <div class="sale-qty">Qty: <?php echo $sale['quantity']; ?></div>
                        </div>
                        <div class="sale-amount">+RM <?php echo number_format($sale['amount'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
