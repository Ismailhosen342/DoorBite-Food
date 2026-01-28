<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'complete_sale') {
    $items = json_decode($_POST['items'] ?? '[]', true);
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No items in cart']);
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += floatval($item['price']) * intval($item['quantity']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO sales (total_amount) VALUES (?) RETURNING id");
        $stmt->execute([$totalAmount]);
        $saleId = $stmt->fetch()['id'];
        
        foreach ($items as $item) {
            $productId = intval($item['id']);
            $quantity = intval($item['quantity']);
            $unitPrice = floatval($item['price']);
            
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product || $product['stock'] < $quantity) {
                throw new Exception('Not enough stock for product');
            }
            
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$saleId, $productId, $quantity, $unitPrice]);
            
            $oldStock = $product['stock'];
            $newStock = $oldStock - $quantity;
            
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->execute([$newStock, $productId]);
            
            $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, reason, change_amount, old_stock, new_stock) VALUES (?, 'sale', ?, ?, ?)");
            $stmt->execute([$productId, -$quantity, $oldStock, $newStock]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'sale_id' => $saleId]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
