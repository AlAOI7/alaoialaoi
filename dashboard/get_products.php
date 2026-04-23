<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'غير مصرح']));
}

// جلب جميع المنتجات النشطة
$sql = "SELECT id, name, selling_price FROM products WHERE status = 'active' ORDER BY name";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
?>