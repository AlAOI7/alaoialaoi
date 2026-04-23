<?php
require_once 'config.php';

try {
    // جلب جميع المسؤولين
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 قائمة المسؤولين في النظام</h3>";
    
    if (count($admins) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>
                <th>#</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>تاريخ الإضافة</th>
              </tr>";
        
        foreach ($admins as $index => $admin) {
            echo "<tr>
                    <td>" . ($index + 1) . "</td>
                    <td>{$admin['name']}</td>
                    <td>{$admin['email']}</td>
                    <td>{$admin['created_at']}</td>
                  </tr>";
        }
        echo "</table>";
        
        echo "<br><div class='alert alert-info'>";
        echo "يمكنك استخدام أي من هذه الحسابات لتسجيل الدخول";
        echo "</div>";
        
    } else {
        echo "<div class='alert alert-warning'>";
        echo "❌ لا يوجد مسؤولين في النظام. <a href='add_admin.php'>أضف مسؤولاً الآن</a>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "❌ حدث خطأ: " . $e->getMessage();
}
?>

<br>
<a href="add_admin.php" class="btn btn-primary">➕ إضافة مسؤول جديد</a>
<a href="admin_login.php" class="btn btn-success">🔐 تسجيل الدخول</a>