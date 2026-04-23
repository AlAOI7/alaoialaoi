<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND email_verified = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['email'] = $user['email'];
            
            if ($user['user_type'] == 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: user_dashboard.php');
            }
            exit();
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
        }
    } catch (PDOException $e) {
        $error = "حدث خطأ في النظام: " . $e->getMessage();
    }
}
// إذا كان المستخدم مسجل الدخول بالفعل، توجيهه إلى الصفحة الرئيسية
// if (isset($_SESSION['user_id'])) {
//     header('Location: home.php');
//     exit;
// }

// $error = '';

// // معالجة تسجيل الدخول
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $login = trim($_POST['login']); // يمكن أن يكون username أو email
//     $password = $_POST['password'];
    
//     try {
//         // البحث عن المستخدم باستخدام username أو email
//         $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND email_verified = 1 AND status = 'active'");
//         $stmt->execute([$login, $login]);
//         $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
//         if ($user && password_verify($password, $user['password'])) {
//             // تخزين بيانات المستخدم في الجلسة
//             $_SESSION['user_id'] = $user['id'];
//             $_SESSION['user_name'] = $user['name'];
//             $_SESSION['username'] = $user['username'];
//             $_SESSION['email'] = $user['email'];
//             $_SESSION['user_type'] = $user['user_type'];
//             $_SESSION['profile_image'] = $user['profile_image'];
            
//             // تحديث آخر نشاط
//             $updateStmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
//             $updateStmt->execute([$user['id']]);
            
//             // توجيه حسب نوع المستخدم
//             if ($user['user_type'] == 'admin') {
//                 header('Location: admin_dashboard.php');
//             } else {
//                 header('Location: home.php');
//             }
//             exit();
//         } else {
//             $error = "اسم المستخدم/البريد الإلكتروني أو كلمة المرور غير صحيحة";
//         }
//     } catch (PDOException $e) {
//         $error = "حدث خطأ في النظام: " . $e->getMessage();
//     }
// }
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.95);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .card {
            border: none;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dc3545;
            padding: 5px;
        }
        .store-name {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #dc3545, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 1rem;
        }
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
        .social-login-btn {
            border-radius: 50px;
            padding: 12px 25px;
            border: 2px solid #dc3545;
            transition: all 0.3s ease;
        }
        .social-login-btn:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: #6c757d;
        }
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .divider::before {
            margin-left: 1rem;
        }
        .divider::after {
            margin-right: 1rem;
        }
        .sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 999;
        }
        .sidebar.active {
            right: 0;
        }
        .sidebar-header {
            padding: 2rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            margin-bottom: 1rem;
        }
        .sidebar-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-links li a {
            display: block;
            padding: 1rem 2rem;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        .sidebar-links li a:hover {
            background: #f8f9fa;
            padding-right: 2.5rem;
        }
        .sidebar-links li a i {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">

    <header class="main-header">
        <button id="menu-toggle" class="btn btn-outline-danger">
            <i class="fas fa-bars"></i>
        </button>
        <img src="https://via.placeholder.com/100x40/fff?text=Be+Pretty" alt="Be Pretty Logo" class="logo">
        <div></div>
    </header>

    <aside id="sidebar-menu" class="sidebar">
        <div class="sidebar-header">
            <button id="close-menu" class="btn btn-light btn-sm position-absolute" style="top: 1rem; left: 1rem;">
                <i class="fas fa-times"></i>
            </button>
            <img src="https://via.placeholder.com/80" alt="User Profile" class="profile-img">
            <h3>اسم المستخدم</h3>
        </div>
        <ul class="sidebar-links">
            <li><a href="home.php"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="#"><i class="fas fa-shopping-cart"></i> سلة التسوق</a></li>
            <li><a href="#"><i class="fas fa-heart"></i> المفضلة</a></li>
            <li><a href="#"><i class="fas fa-bell"></i> الإشعارات</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> الإعدادات</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
        </ul>
    </aside>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-lg p-4">
                    
                    <div class="logo-container">
                        <img src="https://via.placeholder.com/100" alt="Be Pretty Logo" class="logo-img">
                        <h1 class="store-name">Be Pretty</h1>
                    </div>
                    
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-danger">تسجيل الدخول</h2>
                        <p class="text-muted">مرحباً بعودتك! قم بتسجيل الدخول للمتابعة.</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>
                            </div>
                        </div>
                            <!-- <div class="mb-3">
                            <label for="login" class="form-label fw-bold">اسم المستخدم أو البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="login" 
                                       name="login" 
                                       placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" 
                                       required
                                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>">
                            </div>
                            <small class="text-muted">يمكنك استخدام اسم المستخدم أو البريد الإلكتروني</small>
                        </div> -->
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2">
                                <i class="fas fa-sign-in-alt"></i> دخول
                            </button>
                        </div>
                    </form>
                    
                    <div class="divider">أو</div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <a href="google_login.php" class="btn btn-outline-danger social-login-btn">
                            <i class="fab fa-google"></i> تسجيل الدخول باستخدام جوجل
                        </a>
                        <button class="btn btn-outline-danger social-login-btn">
                            <i class="fab fa-facebook-f"></i> تسجيل الدخول باستخدام فيسبوك
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <a href="forgot_password.php" class="text-danger small text-decoration-none">
                            <i class="fas fa-key"></i> نسيت كلمة المرور؟
                        </a>
                        <hr class="my-3">
                        <p class="mb-0 small text-muted">
                            لا تمتلك حساباً؟ 
                            <a href="register.php" class="text-danger fw-bold text-decoration-none">إنشاء حساب جديد</a>
                        </p>
                        <p class="mb-0 small text-muted mt-2">
                            <a href="admin_login.php" class="text-danger fw-bold text-decoration-none">
                                <i class="fas fa-user-shield"></i> تسجيل دخول المسؤولين
                            </a>
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar functionality
            $('#menu-toggle').click(function() {
                $('#sidebar-menu').addClass('active');
            });

            $('#close-menu').click(function() {
                $('#sidebar-menu').removeClass('active');
            });
            
            $(document).mouseup(function(e) {
                const sidebar = $('#sidebar-menu');
                if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0) {
                    sidebar.removeClass('active');
                }
            });
        });
    </script>
</body>
</html>