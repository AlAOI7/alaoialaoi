<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح بالوصول']);
    exit();
}

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $user = getUser($user_id);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'] ?? '',
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'role' => $user['role'],
            'status' => $user['status']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرف المستخدم مطلوب']);
}
?>