
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['wishlist_item_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف العنصر مطلوب']);
    exit;
}

$wishlistItemId = intval($_POST['wishlist_item_id']);
$userId = getCurrentUserId();

$query = "DELETE FROM wishlist WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'is', $wishlistItemId, $userId);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'تم حذف المنتج من المفضلة']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الحذف']);
}
?>