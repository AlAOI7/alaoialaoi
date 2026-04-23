
<?php
session_start();

header('Content-Type: application/json');

$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
    exit;
}

if (isset($_SESSION['guest_cart'][$product_id])) {
    unset($_SESSION['guest_cart'][$product_id]);
    echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
}
?>