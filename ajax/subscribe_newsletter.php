<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صالح']);
        exit;
    }
    
    // إنشاء الجدول إذا لم يكن موجوداً
    $createTableQuery = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTableQuery);
    
    // التحقق من وجود البريد
    $checkStmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'هذا البريد مسجل مسبقاً']);
    } else {
        $insertStmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $insertStmt->bind_param("s", $email);
        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'تم الاشتراك بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الاشتراك']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
}
?>
