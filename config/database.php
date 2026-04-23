<?php
// config/database.php

// تفعيل عرض الأخطاء للتطوير (أوقفها في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'be_pretty';

// إنشاء الاتصال
$conn = new mysqli($host, $username, $password, $database);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
// جلسة المستخدم

// تأكد من وجود معرف المستخدم في الجلسة
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن هناك مستخدم مسجل الدخول، استخدم جلسة مؤقتة
    $_SESSION['user_id'] = session_id(); // أو 0 للمستخدمين الزوار
}


// أو إذا كنت تستخدم الشرط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// تعيين الترميز
if (!$conn->set_charset("utf8mb4")) {
    die("فشل تعيين الترميز: " . $conn->error);
}
mysqli_set_charset($conn, "utf8mb4");

// بدء الجلسة إذا لم تكن بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// دالة تنفيذ الاستعلامات بأمان
function executeQuery($sql, $params = [], $types = "") {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => $conn->error];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        return ['success' => false, 'error' => $stmt->error];
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return ['success' => true, 'result' => $result];
}
// دالة للحصول على معرف المستخدم الحالي
function getCurrentUserId() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    // إذا لم يكن هناك مستخدم مسجل الدخول، استخدم معرف الجلسة
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // إنشاء معرف فريد للزوار
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['temp_user_id'] = 'guest_' . session_id() . '_' . time();
    }
    
    return $_SESSION['temp_user_id'];
}

// دالة للتحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && 
           $_SESSION['user_id'] !== getCurrentUserId();
}

// إعداد المنطقة الزمنية (للمملكة العربية السعودية)
date_default_timezone_set('Asia/Riyadh');
?>
