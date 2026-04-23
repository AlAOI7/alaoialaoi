<?php
require_once 'config.php';

try {
    // بيانات المسؤول
    $admin_email = 'admin@storthory.com';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // استعلام SQL لإضافة المسؤول
    $sql = "INSERT INTO users (name, email, password, user_type, email_verified, created_at) 
            VALUES ('المسؤول الرئيسي', '$admin_email', '$admin_password', 'admin', 1, NOW())";
    
    // تنفيذ الاستعلام
    $result = $pdo->exec($sql);
    
    if ($result) {
        echo "✅ تم إضافة المسؤول بنجاح!<br><br>";
        echo "📧 البريد الإلكتروني: <strong>admin@storthory.com</strong><br>";
        echo "🔑 كلمة المرور: <strong>admin123</strong><br><br>";
        echo "<a href='admin_login.php' class='btn btn-success'>تجربة الدخول</a>";
    } else {
        echo "⚠️ قد يكون المسؤول موجوداً مسبقاً";
    }
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // خطأ duplicate entry
        echo "⚠️ المسؤول موجود مسبقاً في النظام<br><br>";
        echo "📧 البريد الإلكتروني: <strong>admin@storthory.com</strong><br>";
        echo "🔑 كلمة المرور: <strong>admin123</strong><br><br>";
        echo "<a href='admin_login.php' class='btn btn-success'>تجربة الدخول</a>";
    } else {
        echo "❌ حدث خطأ: " . $e->getMessage();
    }
}
?>