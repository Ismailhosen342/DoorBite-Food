<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
requireLogin();

$pageTitle = 'Reports';
$currentPage = 'reports';

// --------------------
// Helpers
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
// Total Sell (Revenue)
// --------------------
$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) AS revenue FROM sales");
$totalRevenue = (float)($stmt->fetch()['revenue'] ?? 0);

// --------------------
// Total Cost
// --------------------
if ($hasBuyingPrice) {
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(si.quantity * p.buying_price), 0) AS cost
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
    ");
    $totalCost = (float)($stmt->fetch()['cost'] ?? 0);
} else {
    $totalCost = 0.0;
}

$totalProfit = $totalRevenue - $totalCost;

// --------------------
// Last 7 days PROFIT (MySQL)
// profit = SUM(quantity * (unit_price - buying_price))
// --------------------
$profitData = [];
if ($hasBuyingPrice) {
    $stmt = $pdo->query("
        SELECT DATE(s.created_at) AS date,
               COALESCE(SUM(si.quantity * (si.unit_price - p.buying_price)), 0) AS profit
        FROM sales s
        JOIN sale_items si ON s.id = si.sale_id
        JOIN products p ON si.product_id = p.id
        WHERE DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(s.created_at)
        ORDER BY date
    ");
    $profitData = $stmt->fetchAll();
}

// --------------------
// Last 7 days Items sold (MySQL)
// --------------------
$stmt = $pdo->query("
    SELECT DATE(s.created_at) AS date, COALESCE(SUM(si.quantity),0) AS items
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    WHERE DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(s.created_at)
    ORDER BY date
");
$itemsData = $stmt->fetchAll();

// Build arrays for charts (always 7 days)
$labels = [];
$profits = [];
$items = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($date));

    // profit per day
    $profit = 0;
    foreach ($profitData as $p) {
        if ($p['date'] === $date) {
            $profit = (float)$p['profit'];
            break;
        }
    }
    $profits[] = $profit;

    // items per day
    $itemCount = 0;
    foreach ($itemsData as $row) {
        if ($row['date'] === $date) {
            $itemCount = (int)$row['items'];
            break;
        }
    }
    $items[] = $itemCount;
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Reports</h1>
            <p class="page-subtitle">Sales analytics and performance</p>
        </div>
        <a href="api/export.php" class="btn btn-primary">Download CSV Report</a>
    </div>

    <div class="reports-stats">
        <div class="stat-card">
            <div class="stat-label">Total Sell</div>
            <div class="stat-value revenue">RM <?php echo number_format($totalRevenue, 2); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Cost</div>
            <div class="stat-value">RM <?php echo number_format($totalCost, 2); ?></div>
            <?php if (!$hasBuyingPrice): ?>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">
                    (buying_price not set — cost/profit may be 0)
                </div>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Profit</div>
            <div class="stat-value profit">RM <?php echo number_format($totalProfit, 2); ?></div>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h3 class="chart-title">Profit (Last 7 Days)</h3>
            <canvas id="profitChart"></canvas>
        </div>

        <div class="chart-card">
            <h3 class="chart-title">Items Sold</h3>
            <canvas id="itemsChart"></canvas>
        </div>
    </div>
</main>

<script>
const labels = <?php echo json_encode($labels); ?>;
const profits = <?php echo json_encode($profits); ?>;
const items = <?php echo json_encode($items); ?>;

new Chart(document.getElementById('profitChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Profit (RM)',
            data: profits,
            backgroundColor: '#3b82f6',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) { return 'RM ' + value; }
                }
            }
        }
    }
});

new Chart(document.getElementById('itemsChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Items Sold',
            data: items,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
