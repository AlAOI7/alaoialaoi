<?php
// ajax/smart_search.php - البحث الذكي في المنتجات والفئات
require_once '../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode(['results' => [], 'total' => 0]);
    exit;
}

$like = '%' . $conn->real_escape_string($q) . '%';

// البحث في المنتجات
$products_sql = "
    SELECT p.id, p.name, p.selling_price, p.old_price,
           COALESCE(pi.image_path, 'img/default.jpg') as image,
           c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
    WHERE p.status = 'active'
      AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
    ORDER BY
        CASE WHEN p.name LIKE ? THEN 0
             WHEN p.name LIKE ? THEN 1
             ELSE 2 END,
        p.featured DESC, p.popular DESC
    LIMIT 8
";

$stmt = $conn->prepare($products_sql);
$start_like = $q . '%';
$stmt->bind_param("sssss", $like, $like, $like, $start_like, $like);
$stmt->execute();
$products_res = $stmt->get_result();

$products = [];
while ($row = $products_res->fetch_assoc()) {
    $products[] = [
        'type'          => 'product',
        'id'            => $row['id'],
        'name'          => $row['name'],
        'price'         => number_format($row['selling_price'], 2),
        'old_price'     => $row['old_price'] ? number_format($row['old_price'], 2) : null,
        'image'         => $row['image'],
        'category'      => $row['category_name'],
        'url'           => 'product-details.php?id=' . $row['id'],
    ];
}

// البحث في الفئات
$cats_sql = "
    SELECT id, name, image
    FROM categories
    WHERE status = 'active' AND is_active = 1
      AND name LIKE ?
    LIMIT 4
";
$cat_stmt = $conn->prepare($cats_sql);
$cat_stmt->bind_param("s", $like);
$cat_stmt->execute();
$cats_res = $cat_stmt->get_result();

$categories = [];
while ($row = $cats_res->fetch_assoc()) {
    $categories[] = [
        'type'  => 'category',
        'id'    => $row['id'],
        'name'  => $row['name'],
        'image' => $row['image'] ?? '',
        'url'   => 'categories-details.php?id=' . $row['id'],
    ];
}

echo json_encode([
    'results'    => array_merge($categories, $products),
    'products'   => count($products),
    'categories' => count($categories),
    'total'      => count($products) + count($categories),
    'query'      => $q,
]);
?>
