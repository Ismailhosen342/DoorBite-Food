<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'update_filter') {
    $filterType = $_POST['filter_type'] ?? 'daily';
    
    $validTypes = ['daily', 'weekly', 'monthly'];
    if (!in_array($filterType, $validTypes)) {
        $filterType = 'daily';
    }
    
    $stmt = $pdo->prepare("UPDATE settings SET filter_type = ?, updated_at = NOW()");
    $stmt->execute([$filterType]);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
