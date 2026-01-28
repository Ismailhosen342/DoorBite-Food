<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Date', 'Sale ID', 'Product', 'Quantity', 'Unit Price', 'Total']);

$stmt = $pdo->query("
    SELECT 
        s.created_at,
        s.id as sale_id,
        p.name as product_name,
        si.quantity,
        si.unit_price,
        (si.quantity * si.unit_price) as total
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    JOIN products p ON si.product_id = p.id
    ORDER BY s.created_at DESC
");

while ($row = $stmt->fetch()) {
    fputcsv($output, [
        date('Y-m-d H:i:s', strtotime($row['created_at'])),
        $row['sale_id'],
        $row['product_name'],
        $row['quantity'],
        number_format($row['unit_price'], 2),
        number_format($row['total'], 2)
    ]);
}

fclose($output);
?>
