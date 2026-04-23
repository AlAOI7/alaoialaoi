<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'معرف المنتج مطلوب']);
    exit;
}

$product_id = intval($_GET['id']);

$sql = "SELECT p.*, 
               c.name as category_name,
               pi.image_path as main_image,
               (p.old_price - p.selling_price) as discount_amount,
               CASE 
                   WHEN p.old_price > 0 THEN ROUND(((p.old_price - p.selling_price) / p.old_price) * 100)
                   ELSE 0
               END as discount_percentage
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.id = ? AND p.is_active = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'المنتج غير موجود']);
    exit;
}

$product = $result->fetch_assoc();

// تحديد حالة المخزون
if ($product['stock'] <= 0) {
    $product['stock_status'] = 'out_of_stock';
    $product['stock_message'] = 'غير متوفر';
} elseif ($product['stock'] <= 5) {
    $product['stock_status'] = 'low_stock';
    $product['stock_message'] = "بقي {$product['stock']} قطع فقط";
} else {
    $product['stock_status'] = 'in_stock';
    $product['stock_message'] = "متوفر";
}

// جلب الصور الإضافية
$images_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
$product['images'] = $images_result->fetch_all(MYSQLI_ASSOC);

// جلب الألوان
$colors_sql = "SELECT * FROM product_colors WHERE product_id = ?";
$colors_stmt = $conn->prepare($colors_sql);
$colors_stmt->bind_param("i", $product_id);
$colors_stmt->execute();
$colors_result = $colors_stmt->get_result();
$product['colors'] = $colors_result->fetch_all(MYSQLI_ASSOC);

// جلب المقاسات
$sizes_sql = "SELECT * FROM product_sizes WHERE product_id = ?";
$sizes_stmt = $conn->prepare($sizes_sql);
$sizes_stmt->bind_param("i", $product_id);
$sizes_stmt->execute();
$sizes_result = $sizes_stmt->get_result();
$product['sizes'] = $sizes_result->fetch_all(MYSQLI_ASSOC);

echo json_encode($product);
?>