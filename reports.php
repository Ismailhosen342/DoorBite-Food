<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Reports';
$currentPage = 'reports';

$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM sales");
$totalRevenue = $stmt->fetch()['revenue'];

$stmt = $pdo->query("
    SELECT COALESCE(SUM(si.quantity * p.buying_price), 0) as cost
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
");
$totalCost = $stmt->fetch()['cost'];

$totalProfit = $totalRevenue - $totalCost;

$stmt = $pdo->query("
    SELECT DATE(created_at) as date, SUM(total_amount) as revenue
    FROM sales
    WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
    GROUP BY DATE(created_at)
    ORDER BY date
");
$revenueData = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT DATE(s.created_at) as date, SUM(si.quantity) as items
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    WHERE s.created_at >= CURRENT_DATE - INTERVAL '7 days'
    GROUP BY DATE(s.created_at)
    ORDER BY date
");
$itemsData = $stmt->fetchAll();

$labels = [];
$revenues = [];
$items = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($date));
    
    $revenue = 0;
    foreach ($revenueData as $r) {
        if ($r['date'] === $date) {
            $revenue = floatval($r['revenue']);
            break;
        }
    }
    $revenues[] = $revenue;
    
    $itemCount = 0;
    foreach ($itemsData as $item) {
        if ($item['date'] === $date) {
            $itemCount = intval($item['items']);
            break;
        }
    }
    $items[] = $itemCount;
}

include 'includes/header.php';
include 'includes/sidebar.php';
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
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value revenue">RM <?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Cost</div>
            <div class="stat-value">RM <?php echo number_format($totalCost, 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Profit</div>
            <div class="stat-value profit">RM <?php echo number_format($totalProfit, 2); ?></div>
        </div>
    </div>
    
    <div class="chart-grid">
        <div class="chart-card">
            <h3 class="chart-title">Revenue (Last 7 Days)</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-card">
            <h3 class="chart-title">Items Sold</h3>
            <canvas id="itemsChart"></canvas>
        </div>
    </div>
</main>

<script>
const labels = <?php echo json_encode($labels); ?>;
const revenues = <?php echo json_encode($revenues); ?>;
const items = <?php echo json_encode($items); ?>;

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revenue (RM)',
            data: revenues,
            backgroundColor: '#3b82f6',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'RM ' + value;
                    }
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
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
