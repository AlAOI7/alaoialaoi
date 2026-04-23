<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح بالوصول']);
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $bank_id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM banks WHERE id = $bank_id");
    
    if ($result && $result->num_rows > 0) {
        $bank = $result->fetch_assoc();
        echo json_encode(['success' => true, 'bank' => $bank]);
    } else {
        echo json_encode(['success' => false, 'message' => 'البنك غير موجود']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرف البنك مطلوب']);
}
?>