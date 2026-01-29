<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'] ?? '';
if ($action !== 'bulk_sell_by_current_stock') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

$items = json_decode($_POST['items'] ?? '[]', true);
if (!is_array($items) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'No items provided']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Calculate total
    $totalAmount = 0;
    foreach ($items as $item) {
        $qty = (int)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        if ($qty > 0) $totalAmount += ($qty * $price);
    }

    if ($totalAmount <= 0) {
        throw new Exception("Total amount is zero.");
    }

    // Create one sale record
    $stmt = $pdo->prepare("INSERT INTO sales (total_amount, tax_amount, created_at) VALUES (?, 0, NOW())");
    $stmt->execute([$totalAmount]);
    $saleId = (int)$pdo->lastInsertId();

    foreach ($items as $item) {
        $productId = (int)($item['id'] ?? 0);
        $soldQty   = (int)($item['quantity'] ?? 0);
        $unitPrice = (float)($item['price'] ?? 0);
        $currentStockInput = (int)($item['current_stock'] ?? 0);

        if ($productId <= 0 || $soldQty <= 0) continue;

        // Lock row
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND is_deleted = 0 FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Product not found: ID $productId");
        }

        $oldStock = (int)$product['stock'];

        // Sold = oldStock - currentStockInput (validate)
        $expectedSold = $oldStock - $currentStockInput;
        if ($expectedSold !== $soldQty) {
            throw new Exception("Stock mismatch for product ID $productId. Refresh page and try again.");
        }

        if ($oldStock < $soldQty) {
            throw new Exception("Not enough stock for product ID $productId");
        }

        $newStock = $oldStock - $soldQty;

        // Sale item
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, created_at)
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$saleId, $productId, $soldQty, $unitPrice]);

        // Update stock to currentStockInput (same as newStock)
        $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$newStock, $productId]);

        // Inventory log
        $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, reason, change_amount, old_stock, new_stock, created_at)
                               VALUES (?, 'sale', ?, ?, ?, NOW())");
        $stmt->execute([$productId, -$soldQty, $oldStock, $newStock]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'sale_id' => $saleId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
