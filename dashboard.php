<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
requireLogin();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

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

$hasBuyingPrice = columnExists($pdo, 'products', 'buying_price');

// --------------------
// Today's revenue
// --------------------
$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0) AS revenue
    FROM sales
    WHERE DATE(created_at) = CURDATE()
");
$todayRevenue = (float)($stmt->fetch()['revenue'] ?? 0);

// --------------------
// Today's profit (REAL)
// profit = SUM(qty * (unit_price - buying_price))
// --------------------
if ($hasBuyingPrice) {
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(si.quantity * (si.unit_price - p.buying_price)), 0) AS profit
        FROM sale_items si
        JOIN sales s ON s.id = si.sale_id
        JOIN products p ON p.id = si.product_id
        WHERE DATE(s.created_at) = CURDATE()
    ");
    $todayProfit = (float)($stmt->fetch()['profit'] ?? 0);
} else {
    $todayProfit = 0.0; // safe fallback
}

// --------------------
// Total products
// --------------------
$stmt = $pdo->query("SELECT COUNT(*) AS count FROM products");
$totalProducts = (int)($stmt->fetch()['count'] ?? 0);

// --------------------
// Low stock count
// --------------------
$stmt = $pdo->query("SELECT COUNT(*) AS count FROM products WHERE stock < 3");
$lowStockCount = (int)($stmt->fetch()['count'] ?? 0);

// --------------------
// Low stock products list
// --------------------
$stmt = $pdo->query("
    SELECT id, name, stock
    FROM products
    WHERE stock < 3
    ORDER BY stock ASC
    LIMIT 5
");
$lowStockProducts = $stmt->fetchAll();

// --------------------
// Recent sales today
// --------------------
$stmt = $pdo->query("
    SELECT p.name, si.quantity, (si.quantity * si.unit_price) AS amount
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.created_at) = CURDATE()
    ORDER BY s.created_at DESC
    LIMIT 5
");
$recentSales = $stmt->fetchAll();

// --------------------
// Layout includes
// --------------------
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
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

            <?php if (!$hasBuyingPrice): ?>
                <div class="stat-sub" style="color:#6b7280;">
                    (buying_price missing — profit = 0)
                </div>
            <?php else: ?>
                <div class="stat-sub">Today's profit</div>
            <?php endif; ?>
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
                                <div class="alert-stock">Stock: <?php echo (int)$product['stock']; ?></div>
                            </div>
                        </div>
                        <a href="inventory.php?restock=<?php echo (int)$product['id']; ?>" class="alert-action">Restock Needed</a>
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
                            <div class="sale-qty">Qty: <?php echo (int)$sale['quantity']; ?></div>
                        </div>
                        <div class="sale-amount">+RM <?php echo number_format((float)$sale['amount'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
