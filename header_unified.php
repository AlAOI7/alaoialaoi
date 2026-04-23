<?php
// header_unified.php - هيدر موحد لجميع صفحات الواجهة الأمامية
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

// تهيئة المتغيرات
$isLoggedIn = false;
$user = null;
$cart_count = 0;
$fav_count = 0;

// التحقق من تسجيل الدخول
if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
    $isLoggedIn = true;
    
    // جلب بيانات المستخدم
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        session_destroy();
        $isLoggedIn = false;
    } else {
        // تحديث آخر نشاط
        $update_stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $_SESSION['user_id']);
        $update_stmt->execute();
        
        // حساب عدد المنتجات في السلة
        $cart_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $cart_stmt->bind_param("i", $_SESSION['user_id']);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_count = $cart_result->fetch_assoc()['total'] ?? 0;
        
        // حساب عدد المفضلة - مع معالجة خطأ الجدول غير الموجود
        $fav_count = 0;
        try {
            $fav_stmt = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
            if ($fav_stmt) {
                $fav_stmt->bind_param("i", $_SESSION['user_id']);
                $fav_stmt->execute();
                $fav_result = $fav_stmt->get_result();
                $fav_count = $fav_result->fetch_assoc()['count'] ?? 0;
            }
        } catch (Exception $e) {
            // جدول favorites غير موجود - تجاهل الخطأ
            $fav_count = 0;
        }
    }
}

// جلب الفئات للقائمة
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 AND type = 'product' ORDER BY name");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Be Pretty</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            padding-top: 120px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* الهيدر الثابت */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 15px 0;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        /* الشعار */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .logo:hover {
            transform: scale(1.1);
        }
        
        .site-name {
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* شريط البحث */
        .search-container {
            flex: 1;
            max-width: 600px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 14px 50px 14px 20px;
            border: none;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            background: white;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
        }
        
        .search-btn {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-50%) scale(1.1);
        }
        
        /* الأيقونات */
        .header-icons {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .icon-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 20px;
            transition: all 0.3s;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            font-size: 11px;
            font-weight: bold;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* قائمة التنقل */
        .nav-menu {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 8px 15px;
            backdrop-filter: blur(10px);
        }
        
        .nav-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 5px;
        }
        
        .nav-menu li a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 20px;
            transition: all 0.3s;
            display: block;
            font-weight: 500;
        }
        
        .nav-menu li a:hover,
        .nav-menu li a.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* استجابة للأجهزة */
        @media (max-width: 992px) {
            .header-top {
                flex-wrap: wrap;
            }
            
            .search-container {
                order: 3;
                width: 100%;
                max-width: 100%;
            }
            
            .site-name {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 140px;
            }
            
            .logo {
                width: 50px;
                height: 50px;
            }
            
            .icon-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .nav-menu {
                overflow-x: auto;
                white-space: nowrap;
            }
        }
        
        /* Dropdown المستخدم */
        .user-dropdown {
            position: relative;
        }
        
        .user-avatar-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.25);
            padding: 0 15px;
            border-radius: 30px;
            height: 50px;
        }
        
        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid white;
            object-fit: cover;
        }
        
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 15px;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 10px;
            width: 260px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 15px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1001;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: #667eea;
        }
        
        .dropdown-item i {
            width: 20px;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <!-- الصف الأول: الشعار + البحث + الأيقونات -->
            <div class="header-top">
                <!-- الشعار -->
                <div class="logo-container">
                    <a href="home.php">
                        <img src="img/1.jpg" alt="Be Pretty" class="logo">
                    </a>
                    <span class="site-name d-none d-md-inline">Be Pretty</span>
                </div>
                
                <!-- البحث -->
                <div class="search-container">
                    <form action="search_results.php" method="GET">
                        <input type="text" name="q" class="search-input" placeholder="ابحث عن منتج، فئة أو سعر...">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- الأيقونات -->
                <div class="header-icons">
                    <?php if ($isLoggedIn && $user && ($user['user_type'] ?? 'user') == 'user'): ?>
                        <!-- للمستخدم العادي -->
                        <a href="favorites.php" class="icon-btn" title="المفضلة">
                            <i class="fas fa-heart"></i>
                            <?php if ($fav_count > 0): ?>
                                <span class="notification-badge"><?php echo $fav_count; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="cart.php" class="icon-btn" title="السلة">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="notification-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                        
                    <?php elseif ($isLoggedIn && $user && (($user['user_type'] ?? '') == 'admin' || ($user['user_type'] ?? '') == 'manager')): ?>
                        <!-- للإداريين -->
                        <a href="dashboard/admin_dashboard.php" class="icon-btn" title="لوحة التحكم">
                            <i class="fas fa-tachometer-alt"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($isLoggedIn && $user): ?>
                        <!-- قائمة المستخدم -->
                        <div class="user-dropdown">
                            <button class="icon-btn user-avatar-btn" id="user-dropdown-toggle">
                                <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'img/4.jpg'; ?>" 
                                     alt="المستخدم" class="user-avatar-small">
                                <span class="user-name d-none d-lg-inline"><?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            
                            <div class="dropdown-menu" id="user-dropdown-menu">
                                <a href="profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>الملف الشخصي</span>
                                </a>
                                
                                <?php if (($user['user_type'] ?? 'user') == 'user'): ?>
                                    <a href="order.php" class="dropdown-item">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>طلباتي</span>
                                    </a>
                                    <a href="favorites.php" class="dropdown-item">
                                        <i class="fas fa-heart"></i>
                                        <span>المفضلة</span>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>تسجيل الخروج</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- للزوار -->
                        <a href="login.php" class="icon-btn" title="تسجيل الدخول">
                            <i class="fas fa-user"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- الصف الثاني: القائمة -->
            <nav class="nav-menu">
                <ul>
                    <li><a href="home.php" <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-home"></i> الرئيسية
                    </a></li>
                    <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-shopping-bag"></i> المنتجات
                    </a></li>
                    <li><a href="categories.php" <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-th-large"></i> الفئات
                    </a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="cart.php" <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-cart-shopping"></i> السلة
                        </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <script>
        // Dropdown toggle
        const dropdownToggle = document.getElementById('user-dropdown-toggle');
        const dropdownMenu = document.getElementById('user-dropdown-menu');
        
        if (dropdownToggle && dropdownMenu) {
            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
            
            // إغلاق القائمة عند النقر خارجها
            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
        }
    </script>
