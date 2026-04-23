<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

$supplier_id = (int)$_GET['supplier_id'];
$result = $conn->query("SELECT * FROM supplier_products WHERE supplier_id = $supplier_id AND status = 'active'");

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);
?>