<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'restock') {
    $productId = intval($_POST['product_id'] ?? 0);
    $amount = intval($_POST['amount'] ?? 0);
    $reason = $_POST['reason'] ?? 'restock';
    
    if ($productId <= 0 || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or amount']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND is_deleted = FALSE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            throw new Exception('Product not found');
        }
        
        $oldStock = $product['stock'];
        $newStock = $oldStock + $amount;
        
        $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$newStock, $productId]);
        
        $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, reason, change_amount, old_stock, new_stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$productId, $reason, $amount, $oldStock, $newStock]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
