<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['coupon_code'])) {
    echo json_encode(['success' => false, 'message' => 'كود الخصم مطلوب']);
    exit;
}

$couponCode = $_POST['coupon_code'];
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;

// التحقق من صحة كود الخصم (يمكن استبداله بجلب من قاعدة البيانات)
$validCoupons = [
    'SAVE10' => ['discount' => 10, 'min_amount' => 100, 'type' => 'percentage'],
    'WELCOME25' => ['discount' => 25, 'min_amount' => 200, 'type' => 'percentage'],
    'FREESHIP' => ['discount' => 100, 'min_amount' => 300, 'type' => 'shipping']
];

if (isset($validCoupons[$couponCode])) {
    $coupon = $validCoupons[$couponCode];
    
    // إذا كان هناك منتج محدد، التحقق من سعره
    if ($productId) {
        $productQuery = "SELECT selling_price FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $productQuery);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $productResult = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($productResult);
        
        if ($product && $product['selling_price'] < $coupon['min_amount']) {
            echo json_encode([
                'success' => false, 
                'message' => 'يجب أن يكون سعر المنتج ' . $coupon['min_amount'] . ' ر.س على الأقل'
            ]);
            exit;
        }
    }
    
    // حفظ الكوبون في الجلسة
    $_SESSION['applied_coupon'] = $couponCode;
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تطبيق الخصم بنجاح!',
        'discount' => $coupon['type'] == 'percentage' ? $coupon['discount'] : 0
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'كود الخصم غير صالح']);
}
?>