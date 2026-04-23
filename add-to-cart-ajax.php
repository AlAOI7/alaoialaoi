<?php
// add-to-cart-ajax.php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'بيانات غير صالحة'
        ]);
        exit;
    }
    
    // الحصول على بيانات المنتج
    $sql = "SELECT p.*, 
                   pi.image_path as main_image
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            WHERE p.id = ? AND p.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'المنتج غير موجود'
        ]);
        exit;
    }
    
    $product = $result->fetch_assoc();
    
    // التحقق من المخزون
    if ($quantity > $product['quantity']) {
        echo json_encode([
            'success' => false,
            'message' => 'الكمية المطلوبة غير متوفرة في المخزون'
        ]);
        exit;
    }
    
    // تهيئة السلة إذا لم تكن موجودة
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // إضافة أو تحديث المنتج في السلة
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'selling_price' => floatval($product['selling_price']),
            'old_price' => $product['old_price'] ? floatval($product['old_price']) : null,
            'discount' => floatval($product['discount']),
            'quantity' => $quantity,
            'stock' => intval($product['quantity']),
            'image' => $product['main_image'] ?: 'img/default-product.jpg'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'cart_count' => count($_SESSION['cart']),
        'message' => 'تمت إضافة المنتج إلى السلة بنجاح'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'طريقة الطلب غير صالحة'
    ]);
}
?>