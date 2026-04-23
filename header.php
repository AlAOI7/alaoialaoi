<?php
// session_start();
require_once 'config.php';

// تهيئة المتغيرات
$isLoggedIn = false;
$user = null;

// التحقق من تسجيل الدخول
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
    
    try {
        // جلب بيانات المستخدم
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // إذا لم يوجد المستخدم في قاعدة البيانات، ننهي الجلسة
        if (!$user) {
            session_destroy();
            $isLoggedIn = false;
        } else {
            // تحديث آخر نشاط فقط إذا كان المستخدم موجوداً
            $updateStmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $updateStmt->execute([$_SESSION['user_id']]);
        }
        
    } catch (PDOException $e) {
        // في حالة الخطأ، نعرض الصفحة بدون بيانات المستخدم
        error_log("خطأ في قاعدة البيانات: " . $e->getMessage());
        $isLoggedIn = false;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Be Pretty - متجر التجميل والعناية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
            
           /* أنماط الهيدر المعدلة */
                /* .main-header {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    z-index: 1000;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    padding: 15px 20px;
                } */

                .header-top {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 15px;
                }

                /* الجزء الأيسر: القائمة والشعار والبحث */
                .header-left {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    flex: 1;
                }

                /* الشعار صغير بجوار البحث */
                .logo-container {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .logo {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    object-fit: cover;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    border: 3px solid white;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                }

                .logo:hover {
                    transform: scale(1.1) rotate(5deg);
                    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
                }

                /* تأثير توسيع الشعار */
                .logo-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.85);
                    z-index: 9998;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.5s ease;
                }

                .logo-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }

                .logo-expanded {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) scale(0.1);
                    width: 80vw;
                    max-width: 600px;
                    height: 80vw;
                    max-height: 600px;
                    z-index: 9999;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 8px solid white;
                    box-shadow: 0 0 50px rgba(255, 255, 255, 0.3);
                    cursor: pointer;
                    animation: logoExpand 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
                }

                @keyframes logoExpand {
                    0% {
                        transform: translate(-50%, -50%) scale(0.1);
                        opacity: 0;
                    }
                    50% {
                        transform: translate(-50%, -50%) scale(1.1);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(-50%, -50%) scale(1);
                        opacity: 1;
                    }
                }

                @keyframes logoShrink {
                    0% {
                        transform: translate(-50%, -50%) scale(1);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(-50%, -50%) scale(0.1);
                        opacity: 0;
                    }
                }

                /* الفقاعات */
                .bubble {
                    position: absolute;
                    border-radius: 50%;
                    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.8), rgba(255,255,255,0.2));
                    animation: float 6s infinite ease-in-out;
                    pointer-events: none;
                }

                @keyframes float {
                    0%, 100% {
                        transform: translateY(0) rotate(0deg);
                    }
                    25% {
                        transform: translateY(-40px) rotate(90deg);
                    }
                    50% {
                        transform: translateY(-20px) rotate(180deg);
                    }
                    75% {
                        transform: translateY(-60px) rotate(270deg);
                    }
                }

                /* شريط البحث مع الشعار */
                .search-with-logo {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    flex: 1;
                    max-width: 800px;
                }

                .search-bar-container {
                    position: relative;
                    flex: 1;
                }

                .search-input {
                    width: 100%;
                    padding: 14px 20px 14px 50px;
                    border: none;
                    border-radius: 30px;
                    background: rgba(255, 255, 255, 0.95);
                    font-size: 16px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    transition: all 0.3s;
                    color: #333;
                }

                .search-input:focus {
                    outline: none;
                    background: white;
                    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.3);
                    transform: translateY(-2px);
                }

                .search-icon {
                    position: absolute;
                    right: 20px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #667eea;
                    font-size: 18px;
                }

                /* الأنماط القائمة للـ Dropdown */
                .user-dropdown {
                    position: relative;
                }

                .dropdown-menu {
                    position: absolute;
                    top: 100%;
                    left: auto;
                    right: -150;
                    width: 300px;
                    background: #ffffffff;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                    padding: 20px 0;
                    margin-top: 15px;
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(-20px) scale(0.95);
                    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    z-index: 1001;
                    border: 1px solid rgba(0, 0, 0, 0.05);
                    max-height: 80vh;
                    overflow-y: auto;
                }

                .dropdown-menu.show {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0) scale(1);
                }

                .dropdown-menu::-webkit-scrollbar {
                    width: 6px;
                }

                .dropdown-menu::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }

                .dropdown-menu::-webkit-scrollbar-thumb {
                    background: #667eea;
                    border-radius: 10px;
                }

                .dropdown-header {
                    display: flex;
                    align-items: center;
                    padding: 0 25px 20px;
                    border-bottom: 2px solid #f0f0f0;
                    margin-bottom: 15px;
                }

                .dropdown-avatar {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    object-fit: cover;
                    margin-left: 15px;
                    border: 3px solid #667eea;
                    padding: 2px;
                }

                .dropdown-user-info {
                    flex: 1;
                }

                .dropdown-user-info h4 {
                    margin: 0;
                    font-size: 18px;
                    color: #333;
                    font-weight: 600;
                }

                .dropdown-user-info p {
                    margin: 8px 0 0;
                    font-size: 14px;
                    color: #666;
                }

                .dropdown-item {
                    display: flex;
                    align-items: center;
                    padding: 14px 25px;
                    color: #444;
                    text-decoration: none;
                    transition: all 0.3s;
                    border-left: 4px solid transparent;
                    position: relative;
                    overflow: hidden;
                }

                .dropdown-item::before {
                    content: '';
                    position: absolute;
                    left: -100%;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
                    transition: left 0.6s;
                }

                .dropdown-item:hover::before {
                    left: 100%;
                }

                .dropdown-item:hover {
                    background: linear-gradient(to right, #f8f9ff, white);
                    border-left: 4px solid #667eea;
                    color: #667eea;
                    transform: translateX(5px);
                }

                .dropdown-item i {
                    margin-left: 12px;
                    width: 22px;
                    text-align: center;
                    font-size: 18px;
                    color: #777;
                    transition: all 0.3s;
                }

                .dropdown-item:hover i {
                    color: #667eea;
                    transform: scale(1.1);
                }

                .dropdown-divider {
                    height: 1px;
                    background: linear-gradient(to right, transparent, #f0f0f0, transparent);
                    margin: 12px 25px;
                }

                .dropdown-item.logout {
                    color: #ff4757;
                }

                .dropdown-item.logout:hover {
                    background: linear-gradient(to right, #fff5f5, white);
                    border-left: 4px solid #ff4757;
                    color: #ff4757;
                }

                /* الأزرار والأيقونات */
                .header-icons {
                    display: flex;
                    align-items: center;
                    gap: 20px;
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
                    transform: translateY(-3px) scale(1.05);
                    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
                }

                .user-avatar-btn {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    width: auto;
                    padding: 0 20px;
                    border-radius: 30px;
                    background: rgba(255, 255, 255, 0.25);
                }

                .user-avatar-btn:hover {
                    background: rgba(255, 255, 255, 0.35);
                }

                .user-avatar-small {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid white;
                }

                .user-name {
                    color: white;
                    font-weight: 600;
                    font-size: 15px;
                }

                /* شارة الإشعارات */
                .notification-badge {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    background: linear-gradient(135deg, #ff6b6b, #ff4757);
                    color: white;
                    font-size: 12px;
                    font-weight: bold;
                    min-width: 22px;
                    height: 22px;
                    border-radius: 11px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0 6px;
                    animation: pulse 2s infinite;
                    box-shadow: 0 4px 8px rgba(255, 71, 87, 0.3);
                }

                @keyframes pulse {
                    0% {
                        transform: scale(1);
                        box-shadow: 0 4px 8px rgba(255, 71, 87, 0.3);
                    }
                    50% {
                        transform: scale(1.1);
                        box-shadow: 0 6px 12px rgba(255, 71, 87, 0.5);
                    }
                    100% {
                        transform: scale(1);
                        box-shadow: 0 4px 8px rgba(255, 71, 87, 0.3);
                    }
                }

                /* التجاوب مع الشاشات */
                @media (max-width: 1024px) {
                    .header-left {
                        flex-wrap: wrap;
                    }
                    
                    .search-with-logo {
                        order: 3;
                        width: 100%;
                        margin-top: 15px;
                    }
                }

                @media (max-width: 768px) {
                    .main-header {
                        padding: 12px 15px;
                    }
                    
                    .header-top {
                        flex-wrap: wrap;
                        gap: 15px;
                    }
                    
                    .logo {
                        width: 50px;
                        height: 50px;
                    }
                    
                    .header-icons {
                        order: 2;
                        width: auto;
                    }
                    
                    .search-with-logo {
                        order: 3;
                        margin-top: 10px;
                    }
                    
                    .user-name {
                        display: none;
                    }
                    
                    .user-avatar-btn {
                        padding: 0 15px;
                    }
                    
                    .dropdown-menu {
                        position: fixed;
                        top: 80px;
                        left: 50%;
                        transform: translateX(-50%) translateY(-20px) scale(0.95);
                        width: 90%;
                        max-width: 350px;
                        right: auto;
                    }
                    
                    .dropdown-menu.show {
                        transform: translateX(-50%) translateY(0) scale(1);
                    }
                    
                    .logo-expanded {
                        width: 90vw;
                        height: 90vw;
                    }
                }

                @media (max-width: 480px) {
                    .main-header {
                        padding: 10px;
                    }
                    
                    .logo {
                        width: 45px;
                        height: 45px;
                    }
                    
                    .icon-btn {
                        width: 45px;
                        height: 45px;
                        font-size: 18px;
                    }
                    
                    .search-input {
                        padding: 12px 15px 12px 45px;
                        font-size: 14px;
                    }
                    
                    .dropdown-menu {
                        width: 95%;
                        max-height: 70vh;
                    }
                }
        .user-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }
        .info-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-label {
            color: #666;
            font-size: 0.9rem;
        }
        .info-value {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
       <br>
    <br>
       <br>
    <br>
       <br>
    <br>
    <nav>
        <?php if ($isLoggedIn && $user): ?>
            <!-- للمستخدم المسجل -->
            <!-- <div style="background-color: #e8f5e8; padding: 10px; border-radius: 5px;">
                ✅ <strong>مسجل دخول</strong>
                <p>مرحباً <?php echo htmlspecialchars($user['username']); ?></p>
                <a href="profile.php">الملف الشخصي</a> | 
                <a href="logout.php">تسجيل الخروج</a>
            </div> -->
        <?php else: ?>
            <!-- للزائر -->
            <!-- <div style="background-color: #f0f0f0; padding: 10px; border-radius: 5px;">
                
                <a href="login.php">تسجيل الدخول</a> | 
                <a href="register.php">إنشاء حساب</a>
            </div> -->
        <?php endif; ?>
    </nav>
    <header class="main-header">
        <div class="header-top">
            <!-- الجزء الأيسر: القائمة والشعار والبحث -->
            <div class="header-left">
                <button id="menu-toggle" class="icon-btn"><i class="fas fa-bars"></i></button>
                
                <!-- الشعار وشريط البحث معاً -->
                <div class="search-with-logo">
                    <div class="logo-container">
                        <img src="img/1.jpg" alt="Be Pretty Logo" class="logo" 
                            title="انقر للتكبير">
                    </div>
                    
                    <!-- <div class="search-bar-container">
                        <input type="text" placeholder="البحث عن منتج..." class="search-input">
                        <i class="fas fa-search search-icon"></i>
                    </div> -->
                </div>
            </div>
            
            <!-- الجزء الأيمن: الأيقونات والمستخدم -->
            <div class="header-icons">
                <!-- للمستخدمين العاديين فقط -->
            <?php if ($isLoggedIn && $user): ?>
            <?php if (($user['user_type'] ?? 'user') == 'user'): ?>
            <!-- أزرار خاصة بالمستخدم العادي -->
            <!-- زر الإشعارات (معلق حالياً) -->
            <!-- <button class="icon-btn" data-bs-toggle="modal" data-bs-target="#notificationModal">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notification-count">0</span>
            </button> -->
            
            <!-- زر المفضلة -->
            
                <button class="icon-btn" data-bs-toggle="modal" data-bs-target="#favoritesModal" title="المفضلة">
                    <i class="fas fa-heart"></i>
                    <span class="notification-badge" id="favorites-badge">
                        <?php 
                        // حساب عدد المنتجات المفضلة للمستخدم المسجل
                        $fav_count = 0;
                        if (isset($_SESSION['user_id'])) {
                            try {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $fav_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                            } catch (PDOException $e) {
                                $fav_count = 0;
                            }
                        }
                        echo $fav_count > 0 ? $fav_count : '';
                        ?>
                    </span>
                </button>
                    <!-- زر عربة التسوق -->
                <button class="icon-btn" data-bs-toggle="modal" data-bs-target="#cartModal" title="سلة التسوق">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="notification-badge" id="cart-badge">
                        <?php 
                        // حساب عدد المنتجات في السلة للمستخدم المسجل
                        $cart_count = 0;
                        if (isset($_SESSION['user_id'])) {
                            try {
                                $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                            } catch (PDOException $e) {
                                $cart_count = 0;
                            }
                        }
                        echo $cart_count > 0 ? $cart_count : '';
                        ?>
                    </span>
                </button>
            <?php elseif (($user['user_type'] ?? '') == 'admin' || ($user['user_type'] ?? '') == 'manager'): ?>
            <!-- أزرار خاصة بالإداريين -->
            <!-- <button class="icon-btn admin-btn" onclick="window.location.href='dashboard/admin_dashboard.php'" title="لوحة التحكم">
                <i class="fas fa-tachometer-alt"></i>
            </button>
            <button class="icon-btn admin-btn" onclick="window.location.href='dashboard/order.php'" title="الطلبات">
                <i class="fas fa-shopping-cart"></i>
            </button>
            <button class="icon-btn admin-btn" onclick="window.location.href='dashboard/products.php'" title="المنتجات">
                <i class="fas fa-box"></i>
            </button>
            
            <?php if (($user['user_type'] ?? '') == 'admin'): ?>
                <button class="icon-btn admin-btn" onclick="window.location.href='dashboard/users.php'" title="المستخدمين">
                    <i class="fas fa-users"></i>
                </button>
            <?php endif; ?> -->
        <?php endif; ?>
        
        <?php else: ?>
        <!-- أزرار للزوار (غير مسجلين) -->
        <!-- <button class="icon-btn guest-btn" data-bs-toggle="modal" data-bs-target="#guestFavoritesModal" title="المفضلة">
            <i class="far fa-heart"></i>
            <?php 
            $guest_fav_count = 0;
            if (isset($_SESSION['guest_favorites'])) {
                $guest_fav_count = count($_SESSION['guest_favorites']);
            }
            if ($guest_fav_count > 0): ?>
            <span class="notification-badge" id="guest-favorites-badge"><?php echo $guest_fav_count; ?></span>
            <?php endif; ?>
        </button>
        
        <button class="icon-btn guest-btn" data-bs-toggle="modal" data-bs-target="#guestCartModal" title="سلة التسوق">
            <i class="fas fa-shopping-cart"></i>
            <?php 
            $guest_cart_count = 0;
            if (isset($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $item) {
                    $guest_cart_count += $item['quantity'];
                }
            }
            if ($guest_cart_count > 0): ?>
            <span class="notification-badge" id="guest-cart-badge"><?php echo $guest_cart_count; ?></span>
            <?php endif; ?>
        </button> -->
       
        <!-- أو إظهار رسالة توضيحية عند التمرير -->
        <button class="icon-btn guest-btn" onclick="showGuestMessage()" title="تسجيل الدخول للاستفادة">
            <i class="fas fa-info-circle"></i>
            </button>
        <?php endif; ?>
                
                <!-- Dropdown المستخدم -->
            <?php if ($isLoggedIn && $user): ?>
        <!-- للمستخدم المسجل -->
        <div class="user-dropdown">
            <button class="icon-btn user-avatar-btn" id="user-dropdown-toggle">
                <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'img/4.jpg'; ?>" 
                    alt="صورة المستخدم" 
                    class="user-avatar-small">
                <span class="user-name"><?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <!-- قائمة Dropdown -->
            <div class="dropdown-menu" id="user-dropdown-menu">
                <div class="dropdown-header">
                    <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'img/1.jpg'; ?>" 
                        alt="صورة المستخدم" 
                        class="dropdown-avatar">
                    <div class="dropdown-user-info">
                        <h4><?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></h4>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <?php if(isset($user['user_type'])): ?>
                            <small class="user-type-badge"><?php echo htmlspecialchars($user['user_type']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>الملف الشخصي</span>
                </a>
                
                <a href="edit-profile.php" class="dropdown-item">
                    <i class="fas fa-edit"></i>
                    <span>تعديل البيانات</span>
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
                    
                    <a href="addresses.php" class="dropdown-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>عناويني</span>
                    </a>
                <?php endif; ?>
                
                <?php if (($user['user_type'] ?? '') == 'admin' || ($user['user_type'] ?? '') == 'manager'): ?>
                    <div class="dropdown-divider"></div>
                    <a href="dashboard/admin_dashboard.php" class="dropdown-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>لوحة التحكم</span>
                    </a>
                    
                    <?php if (($user['user_type'] ?? '') == 'admin'): ?>
                        <a href="dashboard/users.php" class="dropdown-item">
                            <i class="fas fa-users"></i>
                            <span>إدارة المستخدمين</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="dashboard/products.php" class="dropdown-item">
                        <i class="fas fa-box"></i>
                        <span>إدارة المنتجات</span>
                    </a>
                    
                    <a href="dashboard/orders.php" class="dropdown-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>إدارة الطلبات</span>
                    </a>
                <?php endif; ?>
                
                <div class="dropdown-divider"></div>
                

                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>الإعدادات</span>
                </a>
                
                <a href="logout.php" class="dropdown-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>تسجيل الخروج</span>
                </a>
            </div>
        </div>

        <?php else: ?>
            <!-- للزائر (غير مسجل الدخول) -->
            <!-- <div class="auth-buttons">
                <a href="login.php" class="auth-btn login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>تسجيل الدخول</span>
                </a>
                
                <a href="register.php" class="auth-btn register-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>إنشاء حساب</span>
                </a>
            </div> -->
        <?php endif; ?>
            </div>
        </div>
    </header>
    
    <aside id="sidebar-menu" class="sidebar">
        <button id="close-menu" class="close-btn"><i class="fas fa-times"></i></button>
       <div class="sidebar-header">
            <?php if ($isLoggedIn && $user): ?>
                <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'https://via.placeholder.com/80'; ?>" 
                    alt="User Profile" 
                    class="profile-img">
                
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                
                <p class="text-muted">
                    <?php echo htmlspecialchars($user['email']); ?> 
                    <br>
                    <span class="badge bg-info text-dark">
                        <?php 
                            // عرض نوع الحساب بشكل منسق
                            if ($user['user_type'] == 'admin') echo 'مدير النظام';
                            elseif ($user['user_type'] == 'manager') echo 'مشرف';
                            else echo 'عضو';
                        ?>
                    </span>
                </p>

            <?php else: ?>
                <img src="https://via.placeholder.com/80" alt="Guest" class="profile-img">
                <h3>مرحباً بك زائرنا</h3>
                <p class="text-muted">قم بتسجيل الدخول للوصول لكافة الميزات</p>
                <a href="login.php" class="btn btn-sm btn-primary">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
        <ul class="sidebar-links">
            <li><a href="home.php"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="blog.php"><i class="fas fa-blog"></i> المدونة</a></li>
                  <li><a href="login.php"><i class="fas fa-blog"></i> تسجيل الدخول</a></li>
            <li><a href="acsses.php"><i class="fas fa-bolt"></i> الوصول السريع</a></li>
            <li><a href="contact.php"><i class="fas fa-headset"></i> الاتصال </a></li>
            <li><a href="terms.php"><i class="fas fa-file-alt"></i> الشروط</a></li>
             <li><a href="customer_returns.php"><i class="fas fa-file-alt"></i> المرتجعات</a></li>
            <li><a href="about.php"><i class="fas fa-info-circle"></i> من نحن</a></li>
            <li><a href="checkout.php"><i class="fas fa-credit-card"></i> الدفع</a></li>
            <li><a href="support.php"><i class="fas fa-shipping-fast"></i> تتبع الطلب </a></li>
            <li><a href="order.php"><i class="fas fa-list-alt"></i> الطلبات</a></li>
            <li><a href="#" data-bs-toggle="modal" data-bs-target="#notificationModal"><i class="fas fa-bell"></i> الإشعارات</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> الإعدادات</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
        </ul>
    </aside>
        
        <script>


                // رسالة للزوار
                function showGuestMessage() {
                    const message = `
                        <div class="guest-message show" id="guestMessage">
                            <strong>⚠️ اهلاً بك!</strong>
                            <p>سجل دخولك للاستفادة من:</p>
                            <ul style="padding-right: 15px; margin-top: 10px;">
                                <li>حفظ المنتجات المفضلة</li>
                                <li>متابعة طلباتك</li>
                                <li>عروض خاصة</li>
                                <li>تتبع الشحن</li>
                            </ul>
                            <div style="margin-top: 15px;">
                                <a href="login.php" class="btn btn-primary btn-sm">تسجيل الدخول</a>
                                <a href="register.php" class="btn btn-outline-primary btn-sm mr-2">إنشاء حساب</a>
                            </div>
                            <button onclick="closeGuestMessage()" style="position: absolute; left: 10px; top: 10px; background: none; border: none; cursor: pointer;">×</button>
                        </div>
                    `;
                    
                    // إزالة الرسالة السابقة إذا كانت موجودة
                    const existingMsg = document.getElementById('guestMessage');
                    if (existingMsg) existingMsg.remove();
                    
                    // إضافة الرسالة الجديدة
                    document.body.insertAdjacentHTML('beforeend', message);
                    
                    // إزالة الرسالة تلقائياً بعد 10 ثواني
                    setTimeout(closeGuestMessage, 10000);
                }

                function closeGuestMessage() {
                    const message = document.getElementById('guestMessage');
                    if (message) message.remove();
                }

                // إغلاق الرسالة عند النقر خارجها
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#guestMessage') && !e.target.closest('.guest-btn')) {
                        closeGuestMessage();
                    }
                });
                $(document).ready(function() {
                    // Dropdown functionality
                
                    
                    // Logo expansion effect
                    const logo = $('.logo');
                    let isLogoExpanded = false;
                    let bubbles = [];
                    
                    logo.on('click mouseenter', function(e) {
                        if (e.type === 'mouseenter' && !isLogoExpanded) {
                            // تأثير تحريك خفيف عند المرور
                            $(this).css('transform', 'scale(1.05) rotate(3deg)');
                        }
                        
                        if (e.type === 'click') {
                            if (!isLogoExpanded) {
                                expandLogo();
                            } else {
                                collapseLogo();
                            }
                        }
                    });
                    
                    logo.on('mouseleave', function() {
                        if (!isLogoExpanded) {
                            $(this).css('transform', 'scale(1) rotate(0)');
                        }
                    });
                    
                    function expandLogo() {
                        isLogoExpanded = true;
                        
                        // إنشاء طبقة خلفية شفافة
                        const overlay = $('<div class="logo-overlay"></div>');
                        $('body').append(overlay);
                        
                        // إنشاء الشعار الموسع
                        const expandedLogo = $('<img class="logo-expanded" src="' + logo.attr('src') + '" alt="' + logo.attr('alt') + '">');
                        $('body').append(expandedLogo);
                        
                        // إظهار الطبقة الخلفية
                        setTimeout(() => {
                            overlay.addClass('active');
                        }, 10);
                        
                        // إنشاء الفقاعات
                        createBubbles();
                        
                        // إضافة حدث النقر للعودة
                        expandedLogo.click(collapseLogo);
                        overlay.click(collapseLogo);
                        
                        // منع التمرير
                        $('body').css('overflow', 'hidden');
                    }
                    
                    function collapseLogo() {
                        if (!isLogoExpanded) return;
                        
                        isLogoExpanded = false;
                        
                        // تأثير انكماش الشعار
                        $('.logo-expanded').css('animation', 'logoShrink 0.5s ease forwards');
                        $('.logo-overlay').removeClass('active');
                        
                        // إزالة الفقاعات
                        bubbles.forEach(bubble => {
                            bubble.remove();
                        });
                        bubbles = [];
                        
                        // إزالة العناصر بعد التأثير
                        setTimeout(() => {
                            $('.logo-expanded, .logo-overlay').remove();
                            $('body').css('overflow', 'auto');
                            logo.css('transform', 'scale(1) rotate(0)');
                        }, 500);
                    }
                    
                    function createBubbles() {
                        const overlay = $('.logo-overlay');
                        const colors = [
                            'rgba(102, 126, 234, 0.6)',
                            'rgba(255, 255, 255, 0.5)',
                            'rgba(118, 75, 162, 0.6)',
                            'rgba(255, 107, 107, 0.6)',
                            'rgba(255, 191, 0, 0.6)'
                        ];
                        
                        for (let i = 0; i < 25; i++) {
                            const bubble = $('<div class="bubble"></div>');
                            const size = Math.random() * 40 + 15;
                            const color = colors[Math.floor(Math.random() * colors.length)];
                            
                            bubble.css({
                                'width': size + 'px',
                                'height': size + 'px',
                                'background': color,
                                'top': Math.random() * 100 + '%',
                                'left': Math.random() * 100 + '%',
                                'animation-delay': Math.random() * 4 + 's',
                                'animation-duration': Math.random() * 4 + 4 + 's'
                            });
                            
                            overlay.append(bubble);
                            bubbles.push(bubble);
                        }
                    }
                    
                    // تحميل العدادت الحقيقية
                    loadNotificationCounts();
                    
                    function loadNotificationCounts() {
                        $.ajax({
                            url: 'get_notifications_count.php',
                            method: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    updateBadge('#notification-count', response.notifications);
                                    updateBadge('#favorites-count', response.favorites);
                                    updateBadge('#cart-count', response.cart_items);
                                    
                                    // تأثير عند تحديث العدادات
                                    if (response.notifications > 0) {
                                        animateBadge('#notification-count');
                                    }
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log('Error loading counts:', error);
                                // استخدام بيانات افتراضية في حالة الخطأ
                                updateBadge('#notification-count', 3);
                                updateBadge('#favorites-count', 2);
                                updateBadge('#cart-count', 5);
                            }
                        });
                    }
                    
                    function updateBadge(selector, count) {
                        const badge = $(selector);
                        if (count > 0) {
                            badge.text(count > 99 ? '99+' : count).show();
                        } else {
                            badge.hide();
                        }
                    }
                    
                    function animateBadge(selector) {
                        const badge = $(selector);
                        badge.css({
                            'transform': 'scale(1.3)',
                            'transition': 'transform 0.3s'
                        });
                        
                        setTimeout(() => {
                            badge.css('transform', 'scale(1)');
                        }, 300);
                    }
                    
                    // تحديث العدادات كل دقيقة
                    setInterval(loadNotificationCounts, 60000);
                    
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
                    
                    // تأثير كتابة في شريط البحث عند التركيز
                    $('.search-input').focus(function() {
                        $(this).attr('placeholder', 'اكتب ما تريد البحث عنه...');
                    }).blur(function() {
                        $(this).attr('placeholder', 'البحث عن منتج...');
                    });
                });
        </script>