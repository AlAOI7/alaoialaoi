<!-- السايدبار
<div class="sidebar">
    <div class="sidebar-header">
        <a href="admin_dashboard.php" class="sidebar-logo">
            <i class="fas fa-store"></i>
            <span class="menu-text">Storthory</span>
        </a>
    </div>
    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span class="menu-text">الرئيسية</span>
        </a>
        <a href="users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span class="menu-text">إدارة المستخدمين</span>
        </a>
        <a href="products.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span class="menu-text">المنتجات</span>
        </a>
        <a href="orders.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span class="menu-text">الطلبات</span>
        </a>
        <a href="categories.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span class="menu-text">الفئات</span>
        </a>
        <a href="reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span class="menu-text">التقارير</span>
        </a>
        <a href="settings.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span class="menu-text">الإعدادات</span>
        </a>
    </div>
</div> -->

<?php
// جلب اسم الموقع وشعاره من قاعدة البيانات
$_dash_settings = [];
if (isset($conn)) {
    $r = $conn->query("SELECT setting_key, setting_value FROM settings");
    if ($r) while ($rw = $r->fetch_assoc()) $_dash_settings[$rw['setting_key']] = $rw['setting_value'];
}
$_dash_site_name = $_dash_settings['site_name'] ?? 'Be Pretty';
$_dash_logo_raw  = $_dash_settings['site_logo'] ?? '';

// ضبط مسار الشعار
if (!empty($_dash_logo_raw) && strpos($_dash_logo_raw, '/') === false && strpos($_dash_logo_raw, '\\') === false) {
    $_dash_logo = '../uploads/settings/' . $_dash_logo_raw;
} else {
    $_dash_logo = $_dash_logo_raw;
}
if (strpos($_dash_logo, '../') === 0 && !file_exists($_dash_logo)) {
    // إذا تم حفظه بمسار مطلق من site_settings
    $_check = str_replace('../', '', $_dash_logo);
    if (file_exists('../' . $_check)) $_dash_logo = '../' . $_check;
}
if (empty($_dash_logo) || (!file_exists($_dash_logo) && strpos($_dash_logo, 'http') !== 0)) {
    $_dash_logo = '../img/1.jpg';
}
?>

      <!-- الشريط الجانبي -->
<div class="sidebar" id="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($_dash_logo) ?>" alt="<?= htmlspecialchars($_dash_site_name) ?>" 
             style="max-height:60px; max-width:120px; object-fit:contain; border-radius:8px; margin-bottom:8px;"
             onerror="this.style.display='none'">
        <h1 style="font-size:1.1rem; margin:0;"><?= htmlspecialchars($_dash_site_name) ?></h1>
        <p>لوحة تحكم الإدارة</p>
    </div>

    <div class="sidebar-menu">

        <!-- لوحة التحكم -->
        <a href="admin_dashboard.php" class="menu-item active">
            <i class="fas fa-tachometer-alt"></i>
            <span>لوحة التحكم</span>
        </a>

        <!-- الطلبات -->
        <div class="menu-item">
            <i class="fas fa-shopping-cart"></i>
            <span>الطلبات</span>
            <div class="submenu">
                <a href="orders.php" class="submenu-item">جميع الطلبات</a>
                <a href="orders_new.php" class="submenu-item">طلبات جديدة</a>
                <a href="orders_processing.php" class="submenu-item">طلبات معالجة</a>
            </div>
        </div>

        <!-- المنتجات -->
        <div class="menu-item">
            <i class="fas fa-box"></i>
            <span>المنتجات</span>
            <div class="submenu">
                <a href="products.php" class="submenu-item">جميع المنتجات</a>
                <a href="add_product.php" class="submenu-item">إضافة منتج</a>
                <a href="categories.php" class="submenu-item">التصنيفات</a>
            </div>
        </div>

        <!-- العملاء -->
        <div class="menu-item">
            <i class="fas fa-users"></i>
            <span>العملاء</span>
            <div class="submenu">
                <a href="customers.php" class="submenu-item">جميع العملاء</a>
                <a href="new_customers.php" class="submenu-item">عملاء جدد</a>
            </div>
        </div>
          <a href="products.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>المنتجات</span>
        </a>
         <a href="order.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>الطلبات</span>
        </a>
         <a href="moveadmin.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>حركة  مدير</span>
        </a>
          <a href="moveuser.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>حركات مستخدمين</span>
        </a>
          <a href="purchases.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>المشتريات</span>
        </a>
          <a href="chat.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>الدردشه الدعم الفني</span>
        </a>
          <a href="pos.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>  المبيعات</span>
        </a>
          <a href="adduser.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>المستخدمين</span>
        </a>
         <a href="returns.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>المرتحعات</span>
        </a>
        <a href="banck.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>البنك</span>
        </a>
        <!-- التحليلات -->
        <a href="suppliers.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>الموردين</span>
        </a>
         <!-- التحليلات -->
        <a href="currencies.php" class="menu-item">
            <i class="fas fa-chart-pie"></i>
            <span>العملات </span>
        </a>

        <!-- الفئات -->
        <a href="categories.php" class="menu-item">
            <i class="fas fa-layer-group"></i>
            <span>الفئات</span>
        </a>

        <!-- العلامات التجارية -->
        <a href="brand.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span>العلامات التجارية</span>
        </a>
        <a href="offers.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span> العروض</span>
        </a>
         <a href="blog.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span> المدونه</span>
        </a>
          <a href=" import_export.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span> المنتج استيراد وتصدير</span>
        </a>
       
         <a href="settings.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span> الاعدادات</span>
        </a>
         <a href="coupons.php" class="menu-item">
            <i class="fas fa-tag"></i>
            <span> القسيمه</span>
        </a>
        <!-- التسويق -->
        <a href="payment_methods.php" class="menu-item">
            <i class="fas fa-bullhorn"></i>
            <span>طرق الدفع</span>
        </a>

        <!-- الإعدادات -->
        <a href="settings.php" class="menu-item">
            <i class="fas fa-cog"></i>
            <span>الإعدادات</span>
        </a>
        <a href="site_settings.php" class="menu-item">
            <i class="fas fa-sliders-h"></i>
            <span>إعدادات الموقع</span>
        </a>
        <a href="currencies.php" class="menu-item">
            <i class="fas fa-money-bill-wave"></i>
            <span>العملات وأسعار الصرف</span>
        </a>

    </div>
</div>
