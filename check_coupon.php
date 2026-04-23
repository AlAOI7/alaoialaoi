<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = $_POST;

// إذا تم تعيين العنوان كافتراضي، إلغاء العناوين الأخرى كافتراضية
if (isset($data['is_default']) && $data['is_default'] == 1) {
    $stmt = $pdo->prepare("UPDATE delivery_addresses SET is_default = 0 WHERE user_id = ?");
    $stmt->execute([$userId]);
}

// إدخال العنوان الجديد
$stmt = $pdo->prepare("
    INSERT INTO delivery_addresses 
    (user_id, title, full_name, phone, secondary_phone, country, city, region, 
     district, street, building, floor, apartment, nearest_landmark, 
     postal_code, address_type, is_default, is_active) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'home', ?, 1)
");

try {
    $success = $stmt->execute([
        $userId,
        $data['title'] ?? 'عنوان جديد',
        $data['full_name'],
        $data['primary_phone'],
        $data['secondary_phone'] ?? null,
        $data['country'],
        $data['city'],
        $data['region'] ?? null,
        $data['district'],
        $data['street'],
        $data['building'] ?? null,
        $data['floor'] ?? null,
        $data['apartment'] ?? null,
        $data['nearest_landmark'] ?? null,
        $data['postal_code'] ?? null,
        $data['is_default'] ?? 0
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'تم حفظ العنوان بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في حفظ العنوان']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>