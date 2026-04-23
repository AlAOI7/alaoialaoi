<?php
// upload_receipt.php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// التحقق من وجود مستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يرجى تسجيل الدخول أولاً']);
    exit;
}

// التحقق من وجود الملف
if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'يرجى اختيار ملف الإيصال']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? 0;

// التحقق من أن الطلب يخص المستخدم
$stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'الطلب غير موجود أو لا يخصك']);
    exit;
}
$stmt->close();

// معالجة الملف
$receipt_file = $_FILES['receipt'];

// التحقق من نوع الملف
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
if (!in_array($receipt_file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'نوع الملف غير مسموح به']);
    exit;
}

// التحقق من حجم الملف
if ($receipt_file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'حجم الملف كبير جداً (الحد الأقصى 5MB)']);
    exit;
}

// إنشاء اسم فريد للملف
$extension = pathinfo($receipt_file['name'], PATHINFO_EXTENSION);
$filename = 'receipt_' . $order_id . '_' . time() . '.' . $extension;
$upload_dir = 'uploads/receipts/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filepath = $upload_dir . $filename;

try {
    // رفع الملف
    if (move_uploaded_file($receipt_file['tmp_name'], $filepath)) {
        // تحديث قاعدة البيانات
        $conn->begin_transaction();
        
        // تحديث الطلب
        $sql = "UPDATE orders SET bank_receipt = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filepath, $order_id);
        $stmt->execute();
        $stmt->close();
        
        // تحديث التحويل البنكي
        $sql = "UPDATE order_bank_transfers SET receipt_image = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filepath, $order_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم رفع الإيصال بنجاح',
            'filepath' => $filepath
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في رفع الملف']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?>