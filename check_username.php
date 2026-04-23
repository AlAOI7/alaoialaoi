<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['available' => false, 'message' => 'اسم المستخدم مطلوب']);
    exit();
}

$username = trim($_GET['username']);

try {
    // التحقق من وجود اسم المستخدم
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['available' => false, 'message' => 'اسم المستخدم غير متاح']);
    } else {
        echo json_encode(['available' => true, 'message' => 'اسم المستخدم متاح']);
    }
} catch (PDOException $e) {
    echo json_encode(['available' => false, 'message' => 'حدث خطأ في التحقق']);
}
?>