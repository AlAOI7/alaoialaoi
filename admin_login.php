<?php
session_start();
require_once 'config.php';

// إذا كان المسؤول مسجل دخول بالفعل، توجيه مباشر
if (isset($_SESSION['admin_id'])) {
    
    header('Location:dashboard/admin_dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin' AND email_verified = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['user_type'] = 'admin';
            
            // التوجيه الصحيح إلى dashboard
            header('Location: dashboard/admin_dashboard.php');
            exit();
        } else {
            $error = "بيانات المسؤول غير صحيحة أو ليس لديك صلاحية الدخول";
        }
    } catch (PDOException $e) {
        $error = "حدث خطأ في النظام: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المسؤولين - Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .admin-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        .admin-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .admin-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .btn-admin {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card admin-card shadow">
                    <div class="admin-header">
                        <div class="admin-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3>منطقة المسؤولين</h3>
                        <p class="mb-0">Be Pretty Admin Panel   admin@storthory.com</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني للمسؤول</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-admin btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> دخول المسؤول
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none">
                                <i class="fas fa-arrow-right"></i> العودة لتسجيل الدخول العادي
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>