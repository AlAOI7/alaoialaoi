
<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$items = [];
$total_price = 0;

if (isset($_SESSION['user_id'])) {
    // للمستخدمين المسجلين
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT c.*, p.name, p.selling_price, p.old_price, p.stock,
                   pi.image_path as image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $item_total = $row['quantity'] * $row['selling_price'];
        $total_price += $item_total;
        
        $items[] = [
            'id' => $row['id'],
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'price' => number_format($row['selling_price'], 2),
            'total_price' => number_format($item_total, 2),
            'image' => $row['image'] ?: 'img/default-product.jpg',
            'stock' => $row['stock']
        ];
    }
} elseif (isset($_SESSION['guest_cart'])) {
    // للزوار
    foreach ($_SESSION['guest_cart'] as $product_id => $item) {
        $item_total = $item['quantity'] * $item['price'];
        $total_price += $item_total;
        
        $items[] = [
            'id' => $product_id,
            'product_id' => $product_id,
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'price' => number_format($item['price'], 2),
            'total_price' => number_format($item_total, 2),
            'image' => $item['image'],
            'stock' => $item['stock']
        ];
    }
}

// حساب الضريبة والمجموع
$tax_rate = 0.15;
$tax = $total_price * $tax_rate;
$grand_total = $total_price + $tax;

echo json_encode([
    'success' => true,
    'items' => $items,
    'summary' => [
        'total_price' => number_format($total_price, 2),
        'tax' => number_format($tax, 2),
        'grand_total' => number_format($grand_total, 2)
    ]
]);
?>