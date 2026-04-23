<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

$supplier_id = (int)$_GET['supplier_id'];
$sql = "SELECT t.*, p.product_name 
        FROM supplier_transactions t 
        LEFT JOIN supplier_products p ON t.product_id = p.id 
        WHERE t.supplier_id = $supplier_id 
        ORDER BY t.transaction_date DESC";
        
$result = $conn->query($sql);

$transactions = [];
while($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

header('Content-Type: application/json');
echo json_encode($transactions);
?>