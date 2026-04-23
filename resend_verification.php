<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// التحقق من الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// قراءة البيانات المرسلة
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

// التحقق من البريد الإلكتروني
if (empty($email) || $email !== ($_SESSION['verification_email'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit();
}

// إنشاء رمز جديد
$new_code = rand(100000, 999999);

try {
    // تحديث الرمز في قاعدة البيانات
    $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
    $stmt->execute([$new_code, $email]);
    
    // تحديث الرمز في الجلسة
    $_SESSION['verification_code'] = $new_code;
    
    echo json_encode([
        'success' => true,
        'code' => $new_code,
        'message' => 'تم إنشاء رمز جديد'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'فشل في تحديث الرمز: ' . $e->getMessage()
    ]);
}
?>