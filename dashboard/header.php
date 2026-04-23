<?php
// التحقق من الجلسة في كل صفحة تستخدم الهيدر
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من أن المستخدم مسؤول
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: http://localhost/Storthory-main7/admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - لوحة تحكم المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    </head>
<body>
<!-- الهيدر -->
<div class="header">
    <div class="header-left">
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h2>لوحة التحكم</h2>
    </div>
    <div class="header-right">
        <div class="header-icon" id="quickAccessBtn">
            <i class="fas fa-th"></i>
            <span class="notification-badge">9</span>
            <div class="dropdown-menu" id="quickAccessMenu">
                <div class="dropdown-header">
                    <h3>الوصول السريع</h3>
                </div>
                <div class="quick-access-grid">
                    <a href="orders.php" class="quick-access-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>الطلبات</span>
                    </a>
                    <a href="payments.php" class="quick-access-item">
                        <i class="fas fa-credit-card"></i>
                        <span>الدفع</span>
                    </a>
                    <a href="products.php" class="quick-access-item">
                        <i class="fas fa-box"></i>
                        <span>المنتج</span>
                    </a>
                    <a href="categories.php" class="quick-access-item">
                        <i class="fas fa-tags"></i>
                        <span>الفئات</span>
                    </a>
                    <a href="sales.php" class="quick-access-item">
                        <i class="fas fa-chart-line"></i>
                        <span>المبيعات</span>
                    </a>
                    <a href="purchases.php" class="quick-access-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>المشتريات</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="header-icon" id="notificationBtn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">5</span>
            <div class="dropdown-menu" id="notificationMenu">
                <div class="dropdown-header">
                    <h3>الإشعارات</h3>
                    <span class="mark-all-read">تحديد الكل</span>
                </div>
                <a href="order_details.php?id=7842" class="dropdown-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>طلب جديد #7842</span>
                </a>
                <a href="inventory.php" class="dropdown-item">
                    <i class="fas fa-box"></i>
                    <span>منتج على وشك النفاد</span>
                </a>
                <a href="users.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>عميل جديد مسجل</span>
                </a>
                <a href="reports.php" class="dropdown-item">
                    <i class="fas fa-chart-line"></i>
                    <span>تقرير المبيعات جاهز</span>
                </a>
                <a href="products.php" class="dropdown-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>منتج يحتاج مراجعة</span>
                </a>
            </div>
        </div>
        
        <div class="user-profile" id="userProfileBtn">
            <div class="user-avatar"><?php echo mb_substr($_SESSION['admin_name'], 0, 1, 'UTF-8'); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['admin_name']; ?></div>
                <div class="user-role">مدير النظام</div>
            </div>
            <div class="dropdown-menu" id="userProfileMenu">
                <div class="dropdown-header">
                    <h3>الملف الشخصي</h3>
                </div>
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>الملف الشخصي</span>
                </a>
                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>الإعدادات</span>
                </a>
                <a href="activity_log.php" class="dropdown-item">
                    <i class="fas fa-history"></i>
                    <span>سجل النشاط</span>
                </a>
                <div class="dropdown-item" id="darkModeToggle">
                    <i class="fas fa-moon"></i>
                    <span>الوضع الليلي</span>
                </div>
                     <a href="logout.php" style="color: var(--danger); text-decoration: none;">
                                    <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                                </a>
          
            </div>
        </div>
    </div>
</div>

<script>
// دالة عامة للهيدر
document.addEventListener('DOMContentLoaded', function() {
    // تبديل السايدبار
    const toggleSidebar = document.getElementById('toggleSidebar');
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            // حفظ الحالة في localStorage
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
        });
    }

    // تحميل حالة السايدبار من localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    // إدارة القوائم المنسدلة
    const dropdownTriggers = document.querySelectorAll('.header-icon, .user-profile');
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // إغلاق جميع القوائم المنسدلة الأخرى
            dropdownTriggers.forEach(otherTrigger => {
                if (otherTrigger !== trigger) {
                    const otherMenu = otherTrigger.querySelector('.dropdown-menu');
                    if (otherMenu) {
                        otherMenu.style.opacity = '0';
                        otherMenu.style.visibility = 'hidden';
                        otherMenu.style.transform = 'translateY(10px)';
                    }
                }
            });

            // تبديل القائمة الحالية
            const menu = this.querySelector('.dropdown-menu');
            if (menu) {
                const isVisible = menu.style.visibility === 'visible';
                menu.style.opacity = isVisible ? '0' : '1';
                menu.style.visibility = isVisible ? 'hidden' : 'visible';
                menu.style.transform = isVisible ? 'translateY(10px)' : 'translateY(0)';
            }
        });
    });

    // إغلاق القوائم عند النقر خارجها
    document.addEventListener('click', function() {
        dropdownTriggers.forEach(trigger => {
            const menu = trigger.querySelector('.dropdown-menu');
            if (menu) {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                menu.style.transform = 'translateY(10px)';
            }
        });
    });

    // تحديد كل الإشعارات كمقروءة
    const markAllRead = document.querySelector('.mark-all-read');
    if (markAllRead) {
        markAllRead.addEventListener('click', function(e) {
            e.stopPropagation();
            const badge = document.querySelector('#notificationBtn .notification-badge');
            if (badge) {
                badge.textContent = '0';
                badge.style.display = 'none';
            }
        });
    }

    // الوضع الليلي
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
    }

    // تحميل الوضع الليلي من localStorage
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
