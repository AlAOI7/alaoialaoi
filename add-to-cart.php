<?php
// add-to-cart.php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    
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
    $product = $result->fetch_assoc();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'selling_price' => $product['selling_price'],
                'old_price' => $product['old_price'],
                'discount' => $product['discount'],
                'quantity' => $quantity,
                'stock' => $product['stock'],
                'image' => $product['main_image']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'cart_count' => count($_SESSION['cart']),
            'message' => 'تمت إضافة المنتج إلى السلة'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'المنتج غير موجود'
        ]);
    }
}
?>