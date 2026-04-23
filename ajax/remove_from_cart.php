
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['cart_item_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف العنصر مطلوب']);
    exit;
}

$cartItemId = intval($_POST['cart_item_id']);
$userId = getCurrentUserId();

$query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'is', $cartItemId, $userId);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'تم حذف المنتج من السلة']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الحذف']);
}
?>