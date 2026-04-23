<?php
// ملف للتحقق من المصادقة في جميع الصفحات
function checkAuth() {
    session_start();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
    
    // يمكنك إضافة المزيد من التحقق هنا
    return true;
}

// دالة للتحقق من نوع المستخدم
function checkUserType($allowedTypes) {
    if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Location: home.php');
        exit();
    }
}
?>