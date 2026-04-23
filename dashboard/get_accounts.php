<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح بالوصول']);
    exit();
}

if (isset($_GET['bank_id']) && is_numeric($_GET['bank_id'])) {
    $bank_id = (int)$_GET['bank_id'];
    $accounts = [];
    
    $result = $conn->query("SELECT * FROM bank_accounts WHERE bank_id = $bank_id ORDER BY is_primary DESC, id ASC");
    
    if ($result) {
        while ($account = $result->fetch_assoc()) {
            $accounts[] = $account;
        }
        echo json_encode(['success' => true, 'accounts' => $accounts]);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب الحسابات']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرف البنك مطلوب']);
}
?>