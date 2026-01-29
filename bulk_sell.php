<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Bulk Sell';
$currentPage = 'bulk_sell';

$stmt = $pdo->query("SELECT id, name, stock, selling_price FROM products WHERE is_deleted = 0 ORDER BY name");
$products = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Bulk Sell</h1>
            <p class="page-subtitle">Enter current stock (counted). System will calculate sold automatically.</p>
        </div>
    </div>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>System Stock</th>
                    <th>Current Stock (Input)</th>
                    <th>Sold (Auto)</th>
                    <th>Selling Price</th>
                    <th>Total (Auto)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr
                        data-id="<?php echo (int)$p['id']; ?>"
                        data-price="<?php echo (float)$p['selling_price']; ?>"
                        data-stock="<?php echo (int)$p['stock']; ?>"
                    >
                        <td><?php echo htmlspecialchars($p['name']); ?></td>

                        <td class="sys-stock"><?php echo (int)$p['stock']; ?></td>

                        <td style="max-width:160px;">
                            <input
                                type="number"
                                min="0"
                                value="<?php echo (int)$p['stock']; ?>"
                                class="current-stock-input"
                                style="width:130px;"
                                oninput="recalcRow(this)"
                            >
                        </td>

                        <td class="sold">0</td>

                        <td>RM <?php echo number_format((float)$p['selling_price'], 2); ?></td>

                        <td class="row-total">RM 0.00</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px; padding:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-weight:700;">Grand Total</div>
            <div style="font-size:20px;font-weight:800;" id="grandTotal">RM 0.00</div>
        </div>
        <div style="color:#6b7280;margin-top:6px;font-size:13px;">
            Only rows where Sold &gt; 0 will be saved.
        </div>
    </div>

    <!-- ✅ SAVE BUTTON AT END -->
    <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
        <button class="btn btn-primary" onclick="submitBulkSell()">
            Save Bulk Sale
        </button>
    </div>
</main>

<script>
function recalcRow(input){
    const tr = input.closest('tr');
    const sysStock = parseInt(tr.dataset.stock, 10);
    const price = parseFloat(tr.dataset.price);

    let currentStock = parseInt(input.value || "0", 10);
    if (currentStock < 0) currentStock = 0;

    // If current stock is bigger than system stock, treat as adjustment (no negative sales)
    if (currentStock > sysStock) {
        currentStock = sysStock;
        input.value = sysStock;
    }

    // Sold = sysStock - currentStock
    const sold = sysStock - currentStock;

    tr.querySelector('.sold').textContent = sold;

    const total = sold * price;
    tr.querySelector('.row-total').textContent = "RM " + total.toFixed(2);

    recalcGrandTotal();
}

function recalcGrandTotal(){
    let sum = 0;
    document.querySelectorAll('tr[data-id]').forEach(tr=>{
        const sysStock = parseInt(tr.dataset.stock,10);
        const price = parseFloat(tr.dataset.price);
        const currentStock = parseInt(tr.querySelector('.current-stock-input').value || "0",10);

        const sold = sysStock - currentStock;
        if (sold > 0) sum += (sold * price);
    });

    document.getElementById('grandTotal').textContent = "RM " + sum.toFixed(2);
}

function submitBulkSell(){
    const items = [];

    document.querySelectorAll('tr[data-id]').forEach(tr=>{
        const productId = parseInt(tr.dataset.id, 10);
        const sysStock = parseInt(tr.dataset.stock,10);
        const price = parseFloat(tr.dataset.price);
        const currentStock = parseInt(tr.querySelector('.current-stock-input').value || "0",10);

        const sold = sysStock - currentStock;

        if (sold > 0) {
            items.push({
                id: productId,
                quantity: sold,
                price: price,
                current_stock: currentStock
            });
        }
    });

    if (items.length === 0) {
        alert("No sales detected (Sold is 0 for all items).");
        return;
    }

    const formData = new FormData();
    formData.append("action", "bulk_sell_by_current_stock");
    formData.append("items", JSON.stringify(items));

    fetch("api/bulk_sell.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert("Bulk sale saved successfully!");
            location.reload();
        } else {
            alert("Error: " + (data.message || "Unknown error"));
        }
    })
    .catch(err => {
        console.error(err);
        alert("Request failed");
    });
}

// initial calculate for all rows
document.querySelectorAll('.current-stock-input').forEach(i => recalcRow(i));
</script>

<?php include 'includes/footer.php'; ?>
