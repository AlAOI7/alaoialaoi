<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'غير مصرح']));
}

if (!isset($_GET['blog_id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'معرف المدونة مطلوب']));
}

$blog_id = (int)$_GET['blog_id'];

// جلب المنتجات المرتبطة بالمدونة
$sql = "SELECT p.id, p.name, p.selling_price 
        FROM blog_products bp 
        JOIN products p ON bp.product_id = p.id 
        WHERE bp.blog_id = $blog_id 
        ORDER BY bp.sort_order";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
?>