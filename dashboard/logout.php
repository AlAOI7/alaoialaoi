<?php
session_start();
require_once 'config.php';
session_destroy();
if (isset($_SESSION['user_id'])) {
    // تسجيل نشاط تسجيل الخروج
    logUserActivity($_SESSION['user_id'], 'logout', 'تسجيل خروج من النظام', 'success');
    
    // تدمير الجلسة
    session_destroy();
}

header('Location: ../admin_login.php');
exit();
?>

