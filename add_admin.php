<?php
session_start();
require_once 'config.php';

// لمنع الوصول العشوائي، يمكنك إضافة تحقق بسيط
$allowed = true; // يمكنك تغيير هذا لشرط تحقق أكثر أماناً

if ($allowed) {
    try {
        // بيانات المسؤول
        $admin_data = [
            'name' => 'المسؤول الرئيسي',
            'email' => 'admin@storthory.com', 
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'user_type' => 'admin',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // التحقق من عدم وجود المسؤول مسبقاً
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'admin'");
        $check_stmt->execute([$admin_data['email']]);
        $existing_admin = $check_stmt->fetch();
        
        if ($existing_admin) {
            $message = "⚠️ المسؤول موجود مسبقاً في النظام";
            $admin_id = $existing_admin['id'];
        } else {
            // إضافة المسؤول الجديد
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, email_verified, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $admin_data['name'],
                $admin_data['email'], 
                $admin_data['password'],
                $admin_data['user_type'],
                $admin_data['email_verified'],
                $admin_data['created_at']
            ]);
            
            $admin_id = $pdo->lastInsertId();
            $message = "✅ تم إضافة المسؤول بنجاح!";
        }
        
        // عرض بيانات الدخول
        $login_info = [
            'email' => $admin_data['email'],
            'password' => 'admin123', // كلمة المرور الأصلية للعرض فقط
            'admin_id' => $admin_id
        ];
        
    } catch (PDOException $e) {
        $error = "❌ حدث خطأ: " . $e->getMessage();
    }
} else {
    $error = "❌ غير مسموح بالوصول";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة المسؤول - Storthory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        .success-alert {
            border-right: 4px solid #28a745;
        }
        .error-alert {
            border-right: 4px solid #dc3545;
        }
        .login-info {
            background: #f8f9fa;
            border-radius: 10px;
            border-right: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="admin-card p-4 m-3">
        <div class="text-center mb-4">
            <h2><i class="fas fa-user-shield text-primary"></i> إضافة مسؤول للنظام</h2>
            <p class="text-muted">ستضاف بيانات المسؤول إلى جدول المستخدمين</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success success-alert alert-dismissible fade show">
                <h5><?php echo $message; ?></h5>
                <?php if (isset($login_info)): ?>
                    <div class="login-info p-3 mt-3">
                        <h6>بيانات الدخول:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>البريد الإلكتروني:</strong><br>
                                <code><?php echo $login_info['email']; ?></code>
                            </div>
                            <div class="col-md-6">
                                <strong>كلمة المرور:</strong><br>
                                <code><?php echo $login_info['password']; ?></code>
                            </div>
                        </div>
                        <div class="mt-2">
                            <strong>رقم المسؤول:</strong> <?php echo $login_info['admin_id']; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger error-alert alert-dismissible fade show">
                <h5><?php echo $error; ?></h5>
            </div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <h6><i class="fas fa-exclamation-triangle"></i> ملاحظات مهمة:</h6>
            <ul class="mb-0">
                <li>سيتم تشفير كلمة المرور تلقائياً</li>
                <li>المسؤول سيحصل على صلاحيات كاملة</li>
                <li>البريد الإلكتروني سيكون مفعلاً تلقائياً</li>
                <li>احفظ بيانات الدخول في مكان آمن</li>
            </ul>
        </div>

        <div class="text-center mt-4">
            <a href="admin_login.php" class="btn btn-primary me-2">
                <i class="fas fa-sign-in-alt"></i> تجربة تسجيل الدخول
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> الصفحة الرئيسية
            </a>
        </div>
    </div>
</body>
</html>