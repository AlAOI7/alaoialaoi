<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !isset($_POST['rating'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$productId = intval($_POST['product_id']);
$rating = intval($_POST['rating']);
$comment = $_POST['comment'] ?? '';
$userId = getCurrentUserId();

// التحقق من وجود المنتج
$productQuery = "SELECT id FROM products WHERE id = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $productQuery);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$productResult = mysqli_stmt_get_result($stmt);

if (!mysqli_num_rows($productResult)) {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
    exit;
}

// التحقق مما إذا كان المستخدم قد أضاف تقييمًا مسبقًا
$checkQuery = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?";
$stmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($stmt, 'si', $userId, $productId);
mysqli_stmt_execute($stmt);
$checkResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($checkResult) > 0) {
    echo json_encode(['success' => false, 'message' => 'لقد أضفت تقييمًا مسبقًا لهذا المنتج']);
    exit;
}

// إضافة التقييم
$insertQuery = "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($stmt, 'siis', $userId, $productId, $rating, $comment);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'تم إضافة تقييمك بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة التقييم']);
}
?>