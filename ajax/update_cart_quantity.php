
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['cart_item_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$cartItemId = intval($_POST['cart_item_id']);
$quantity = intval($_POST['quantity']);
$userId = getCurrentUserId();

// التحقق من الكمية
if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'الكمية يجب أن تكون 1 على الأقل']);
    exit;
}

// التحقق من توفر المخزون
$checkQuery = "SELECT p.stock FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.id = ? AND c.user_id = ?";
$stmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($stmt, 'is', $cartItemId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
    exit;
}

if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة في المخزون']);
    exit;
}

// تحديث الكمية
$updateQuery = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $updateQuery);
mysqli_stmt_bind_param($stmt, 'iis', $quantity, $cartItemId, $userId);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'تم تحديث الكمية بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في تحديث الكمية']);
}
?>