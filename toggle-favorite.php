<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير كاملة']);
    exit;
}

$product_id = intval($_POST['product_id']);
$action = $_POST['action'];

// التحقق من وجود المنتج
$check_sql = "SELECT id FROM products WHERE id = ? AND is_active = 1";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
    exit;
}

// تهيئة المفضلة إذا لم تكن موجودة
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

if ($action === 'add') {
    if (!in_array($product_id, $_SESSION['favorites'])) {
        $_SESSION['favorites'][] = $product_id;
        echo json_encode(['success' => true, 'message' => 'تمت إضافة المنتج إلى المفضلة']);
    } else {
        echo json_encode(['success' => false, 'message' => 'المنتج موجود بالفعل في المفضلة']);
    }
} elseif ($action === 'remove') {
    $key = array_search($product_id, $_SESSION['favorites']);
    if ($key !== false) {
        unset($_SESSION['favorites'][$key]);
        $_SESSION['favorites'] = array_values($_SESSION['favorites']); // إعادة ترتيب المفاتيح
        echo json_encode(['success' => true, 'message' => 'تمت إزالة المنتج من المفضلة']);
    } else {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود في المفضلة']);
    }
}
?>