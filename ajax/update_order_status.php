<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? 0;

// جلب حالة الطلب الحالية
$sql = "SELECT status, delivery_status FROM orders WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if ($order) {
    echo json_encode([
        'success' => true,
        'current_status' => $order['status'],
        'delivery_status' => $order['delivery_status']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'الطلب غير موجود']);
}
?>