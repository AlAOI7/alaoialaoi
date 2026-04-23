<?php
require_once 'config.php';

// إضافة المسؤول مباشرة
$sql = "INSERT IGNORE INTO users (name, email, password, user_type, email_verified, created_at) 
        VALUES ('المسؤول الرئيسي', 'admin@storthory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW())";

try {
    $pdo->exec($sql);
    echo "تم! جرب الدخول بـ:<br>";
    echo "Email: admin@storthory.com<br>";
    echo "Password: password<br>";
    echo "<a href='admin_login.php'>الذهاب لتسجيل الدخول</a>";
} catch (Exception $e) {
    echo "المسؤول موجود بالفعل! <a href='admin_login.php'>جرب الدخول</a>";
}
?>