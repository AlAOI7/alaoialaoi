<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $address_id = $_GET['id'];
    
    $sql = "SELECT * FROM user_addresses WHERE id = ? AND user_id = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $address = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $address]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'العنوان غير موجود']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'معرف غير صحيح']);
}
?>