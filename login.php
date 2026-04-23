
<?php
session_start();

// إذا كان المستخدم مسجل دخول بالفعل، توجيهه إلى home.php
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: home.php');
    exit();
}

// معالجة تسجيل الدخول (اختياري)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config/database.php';
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        // التحقق من بيانات المستخدم
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // إنشاء جلسة المستخدم
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                header('Location: home.php');
                exit();
            }
        }
    }
    $error = "بيانات الدخول غير صحيحة";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Be Pretty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            background-image: url('img/4.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .logo-area {
            margin-bottom: 30px;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background-color: #fff;
            border-radius: 50%;
            border: 4px solid #ff3366;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ff3366;
        }

        .store-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff3366;
            margin-bottom: 10px;
        }

        .tagline {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: right;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .input-with-icon {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f8f8;
        }

        .form-input:focus {
            outline: none;
            border-color: #ff3366;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(255, 51, 102, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.2rem;
        }

        .login-btn {
            background-color: #ff3366;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #e62e5c;
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(255, 51, 102, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #999;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e1e1e1;
        }

        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }

        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .link {
            color: #ff3366;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .link:hover {
            color: #e62e5c;
            text-decoration: underline;
        }

        .error-message {
            background-color: #ffeaea;
            color: #d63031;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: right;
            border-right: 4px solid #d63031;
        }

        .success-message {
            background-color: #eaffea;
            color: #27ae60;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: right;
            border-right: 4px solid #27ae60;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .links {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-area">
            <div class="logo-circle">
                <span class="logo-text">BP</span>
            </div>
            <h1 class="store-name">Be Pretty</h1>
            <p class="tagline">متجرك المفضل للجمال والعناية</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <div class="input-with-icon">
                    <input type="email" name="email" class="form-input" placeholder="أدخل بريدك الإلكتروني" required>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <div class="input-with-icon">
                    <input type="password" name="password" class="form-input" placeholder="أدخل كلمة المرور" required>
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>
<div class="text-center mt-3">
                <a href="home.php" class="text-muted">
                    <i class="fas fa-arrow-right"></i> العودة للمتجر
                </a>
            </div>
        <div class="links">
            <a href="register.php" class="link">إنشاء حساب جديد</a>
            <a href="forgot-password.php" class="link">نسيت كلمة المرور؟</a>
        </div>
    </div>
</body>
</html>