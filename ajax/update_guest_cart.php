
<?php
session_start();

header('Content-Type: application/json');

$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
    exit;
}

if ($quantity < 1) {
    // حذف المنتج
    if (isset($_SESSION['guest_cart'][$product_id])) {
        unset($_SESSION['guest_cart'][$product_id]);
        echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
    }
    exit;
}

// تحديث الكمية
if (isset($_SESSION['guest_cart'][$product_id])) {
    $_SESSION['guest_cart'][$product_id]['quantity'] = $quantity;
    echo json_encode(['success' => true, 'message' => 'تم التحديث بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
}
?>