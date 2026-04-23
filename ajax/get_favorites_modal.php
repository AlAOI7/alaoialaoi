
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

$userId = getCurrentUserId();

// جلب عناصر المفضلة
$query = "SELECT w.*, p.name, p.selling_price, p.old_price, p.stock, c.name as category_name,
                 (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image
          FROM wishlist w
          JOIN products p ON w.product_id = p.id
          JOIN categories c ON p.category_id = c.id
          WHERE w.user_id = ?
          ORDER BY w.created_at DESC";
          
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        'id' => $row['id'],
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'price' => number_format($row['selling_price'], 2),
        'category' => $row['category_name'],
        'image' => $row['image'] ?: 'img/default-product.jpg',
        'in_stock' => $row['stock'] > 0
    ];
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>