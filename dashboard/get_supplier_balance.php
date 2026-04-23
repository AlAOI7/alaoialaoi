<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

$supplier_id = (int)$_GET['supplier_id'];
$result = $conn->query("SELECT balance, updated_at FROM suppliers WHERE id = $supplier_id");

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
} else {
    $data = ['balance' => 0, 'updated_at' => null];
}

header('Content-Type: application/json');
echo json_encode($data);
?>