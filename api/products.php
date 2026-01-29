  <?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $name = trim($_POST['name'] ?? '');
        $buyingPrice = floatval($_POST['buying_price'] ?? 0);
        $sellingPrice = floatval($_POST['selling_price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Product name is required']);
            exit();
        }
        
        $stmt = $pdo->prepare("INSERT INTO products (name, buying_price, selling_price, stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $buyingPrice, $sellingPrice, $stock]);
        
        if ($stock > 0) {
            $productId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, reason, change_amount, old_stock, new_stock) VALUES (?, 'restock', ?, 0, ?)");
            $stmt->execute([$productId, $stock, $stock]);
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $buyingPrice = floatval($_POST['buying_price'] ?? 0);
        $sellingPrice = floatval($_POST['selling_price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $oldStock = $stmt->fetch()['stock'];
        
        $stmt = $pdo->prepare("UPDATE products SET name = ?, buying_price = ?, selling_price = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $buyingPrice, $sellingPrice, $stock, $id]);
        
        if ($stock != $oldStock) {
            $change = $stock - $oldStock;
            $reason = $change > 0 ? 'adjustment' : 'adjustment';
            $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, reason, change_amount, old_stock, new_stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $reason, $change, $oldStock, $stock]);
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        
        $stmt = $pdo->prepare("UPDATE products SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        
        $stmt = $pdo->prepare("UPDATE products SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        
        $stmt = $pdo->prepare("DELETE FROM inventory_logs WHERE product_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND is_deleted = TRUE");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>