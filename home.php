<?php
        require_once 'config/database.php';
        require_once 'functions.php';


        // تحديد حالة المستخدم (هل هو مسجل دخول أم لا)
        $isLoggedIn = false;
        $user = null;

        // التحقق إذا كان المستخدم مسجل دخول
        // if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        //     $isLoggedIn = true;
            
        //     require_once 'config/database.php';
            
        //     // جلب بيانات المستخدم من قاعدة البيانات
        //     $user_id = $_SESSION['user_id'];
        //     $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        //     $stmt->bind_param("i", $user_id);
        //     $stmt->execute();
        //     $result = $stmt->get_result();
        //     $user = $result->fetch_assoc();
            
        //     // تحديث آخر نشاط فقط إذا كان المستخدم موجوداً
        //     if ($user) {
        //         $update_stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        //         $update_stmt->bind_param("i", $user_id);
        //         $update_stmt->execute();
        //     }
        // }
        $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
        // جلب الفئات المميزة
        $featured_categories_query = "SELECT * FROM categories WHERE status = 'active' AND is_active = 1 LIMIT 4";
        $featured_categories_result = mysqli_query($conn, $featured_categories_query);

        // جلب جميع الفئات
        $all_categories_query = "SELECT * FROM categories WHERE status = 'active' AND is_active = 1";
        $all_categories_result = mysqli_query($conn, $all_categories_query);

        // جلب المنتجات المميزة
        $featured_products_query = "SELECT p.*, c.name as category_name 
                                FROM products p 
                                JOIN categories c ON p.category_id = c.id 
                                WHERE p.featured = 1 AND p.status = 'active' 
                                ORDER BY p.created_at DESC LIMIT 8";
        $featured_products_result = mysqli_query($conn, $featured_products_query);

        // جلب جميع المنتجات
        // $all_products_query = "SELECT p.*, c.name as category_name 
        //                     FROM products p 
        //                     JOIN categories c ON p.category_id = c.id 
        //                     WHERE p.status = 'active' 
        //                     ORDER BY p.created_at DESC LIMIT 20";
        // $all_products_result = mysqli_query($conn, $all_products_query);
  
// استعلام لجلب البراندات النشطة مع عدد المنتجات
$brands_query = "SELECT 
                    b.*,
                    (SELECT COUNT(*) FROM products p 
                     WHERE p.brand_id = b.id AND p.status = 'active') as products_count
                 FROM brands b
                 WHERE b.status = 'active'
                 ORDER BY b.products_count DESC, b.name ASC";

$brands_result = mysqli_query($conn, $brands_query);

?>


    <?php include 'header.php'; ?>
      <!-- Main Content -->
<main class="container py-4">
    <!-- Welcome Section -->
    <?php if ($isLoggedIn && $user): ?>
        <div class="welcome-card">
            <div class="welcome-content">
                <h2>مرحباً <?php echo htmlspecialchars($user['name'] ?? $user['username']); ?>! 👋</h2>
                <p class="welcome-text">
                    <?php if (($user['user_type'] ?? 'user') == 'user'): ?>
                        نسعد برؤيتك مرة أخرى. تابع طلباتك واستكشف العروض الجديدة.
                    <?php elseif (($user['user_type'] ?? '') == 'admin'): ?>
                        مرحباً بك في لوحة التحكم. يمكنك إدارة جميع جوانب الموقع من هنا.
                    <?php elseif (($user['user_type'] ?? '') == 'manager'): ?>
                        مرحباً بك كمدير. يمكنك إدارة المنتجات والطلابات من هنا.
                    <?php endif; ?>
                </p>
            </div>
            <div class="welcome-actions">
                <?php if (($user['user_type'] ?? 'user') == 'user'): ?>
                    <a href="products.php" class="btn btn-primary">تصفح المنتجات</a>
                <?php else: ?>
                    <a href="admin/dashboard.php" class="btn btn-primary">الذهاب للوحة التحكم</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="welcome-card guest-welcome">
            <div class="welcome-content">
                <h2>مرحباً بك في متجرنا! 🛍️</h2>
                <p class="welcome-text">
                    اكتشف أفضل المنتجات والعروض المميزة. سجل دخولك للاستفادة من جميع المزايا.
                </p>
            </div>
            <div class="welcome-actions">
                <a href="products.php" class="btn btn-primary">تصفح المنتجات</a>
                <a href="register.php" class="btn btn-outline-primary mr-2">إنشاء حساب جديد</a>
                 <a href="login.php" class="btn btn-outline-primary mr-2">    تسجيل دخول </a>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <?php if ($isLoggedIn && $user): ?>
        <div class="stats-cards">
            <?php if (($user['user_type'] ?? 'user') == 'user'): ?>
                <!-- إحصائيات المستخدم العادي -->
                <?php
                try {
                    // عدد الطلبات
                    $ordersStmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                    $ordersStmt->execute([$_SESSION['user_id']]);
                    $ordersCount = $ordersStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                    // عدد المنتجات المفضلة
                    $favStmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
                    $favStmt->execute([$_SESSION['user_id']]);
                    $favCount = $favStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                    // عدد المنتجات في السلة
                    $cartStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                    $cartStmt->execute([$_SESSION['user_id']]);
                    $cartCount = $cartStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                    
                } catch (PDOException $e) {
                    $ordersCount = $favCount = $cartCount = 0;
                }
                ?>
                
                <!-- <div class="stat-card">
                    <div class="stat-icon order-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $ordersCount; ?></h3>
                        <p>الطلبات</p>
                    </div>
                    <a href="order.php" class="stat-link">عرض الطلبات</a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon favorite-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $favCount; ?></h3>
                        <p>المفضلة</p>
                    </div>
                    <a href="favorites.php" class="stat-link">عرض المفضلة</a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $cartCount; ?></h3>
                        <p>سلة التسوق</p>
                    </div>
                    <a href="cart.php" class="stat-link">عرض السلة</a>
                </div> -->
                
                <?php if(isset($user['points'])): ?>
                <div class="stat-card">
                    <div class="stat-icon points-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $user['points']; ?></h3>
                        <p>نقاط المكافآت</p>
                    </div>
                    <a href="rewards.php" class="stat-link">استخدام النقاط</a>
                </div>
                <?php endif; ?>
                
            <?php elseif (($user['user_type'] ?? '') == 'admin'): ?>
                <!-- إحصائيات المدير -->
                <?php
                try {
                    // إجمالي المستخدمين
                    $usersStmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $usersCount = $usersStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                    // إجمالي الطلبات
                    $ordersStmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
                    $ordersCount = $ordersStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                    // إجمالي المنتجات
                    $productsStmt = $pdo->query("SELECT COUNT(*) as count FROM products");
                    $productsCount = $productsStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                    // إجمالي المبيعات
                    $salesStmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'");
                    $salesTotal = $salesStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                    
                } catch (PDOException $e) {
                    $usersCount = $ordersCount = $productsCount = $salesTotal = 0;
                }
                ?>
                
                <div class="stat-card admin-stat">
                    <div class="stat-icon users-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($usersCount); ?></h3>
                        <p>المستخدمين</p>
                    </div>
                    <a href="admin/users.php" class="stat-link">إدارة المستخدمين</a>
                </div>
                
                <div class="stat-card admin-stat">
                    <div class="stat-icon sales-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($ordersCount); ?></h3>
                        <p>الطلبات</p>
                    </div>
                    <a href="admin/orders.php" class="stat-link">عرض الطلبات</a>
                </div>
                
                <div class="stat-card admin-stat">
                    <div class="stat-icon products-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($productsCount); ?></h3>
                        <p>المنتجات</p>
                    </div>
                    <a href="admin/products.php" class="stat-link">إدارة المنتجات</a>
                </div>
                
                <div class="stat-card admin-stat">
                    <div class="stat-icon revenue-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($salesTotal, 2); ?> ر.س</h3>
                        <p>إجمالي المبيعات</p>
                    </div>
                    <a href="admin/reports.php" class="stat-link">التقارير</a>
                </div>
                
            <?php elseif (($user['user_type'] ?? '') == 'manager'): ?>
                <!-- إحصائيات المدير -->
                <?php
                try {
                    // الطلبات الجديدة
                    $newOrdersStmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
                    $newOrdersCount = $newOrdersStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                    // المنتجات المنخفضة المخزون
                    $lowStockStmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE quantity < 10");
                    $lowStockCount = $lowStockStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    
                } catch (PDOException $e) {
                    $newOrdersCount = $lowStockCount = 0;
                }
                ?>
                
                <div class="stat-card manager-stat">
                    <div class="stat-icon pending-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $newOrdersCount; ?></h3>
                        <p>طلبات جديدة</p>
                    </div>
                    <a href="admin/orders.php?status=pending" class="stat-link">معالجة الطلبات</a>
                </div>
                
                <div class="stat-card manager-stat">
                    <div class="stat-icon lowstock-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $lowStockCount; ?></h3>
                        <p>منتجات قليلة المخزون</p>
                    </div>
                    <a href="admin/products.php?filter=low_stock" class="stat-link">تجديد المخزون</a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
       
    <?php endif; ?>
    
    <!-- Quick Actions -->
       <?php if ($isLoggedIn && $user): ?>
        <!-- <h3 class="mb-3">إجراءات سريعة</h3>
        <div class="quick-actions">
            <?php  if (($user['user_type'] ?? 'user') == 'user'): ?>
                // إجراءات سريعة للمستخدم العادي 
                <a href="products.php?filter=new" class="action-btn">
                    <i class="fas fa-star action-icon"></i>
                    <span>عروض جديدة</span>
                </a>
                
                <a href="order.php?status=processing" class="action-btn">
                    <i class="fas fa-truck action-icon"></i>
                    <span>تتبع الطلبات</span>
                </a>
                
                <a href="favorites.php" class="action-btn">
                    <i class="fas fa-heart action-icon"></i>
                    <span>قائمة المفضلة</span>
                </a>
                
                <a href="addresses.php" class="action-btn">
                    <i class="fas fa-map-marker-alt action-icon"></i>
                    <span>عناويني</span>
                </a>
                
                <a href="profile.php" class="action-btn">
                    <i class="fas fa-user-edit action-icon"></i>
                    <span>تعديل الملف الشخصي</span>
                </a>
                
                <a href="support.php" class="action-btn">
                    <i class="fas fa-headset action-icon"></i>
                    <span>الدعم الفني</span>
                </a>
                
            <?php elseif (($user['user_type'] ?? '') == 'admin' || ($user['user_type'] ?? '') == 'manager'): ?>
                إجراءات سريعة للإداريين 
                <a href="admin/dashboard.php" class="action-btn admin-action">
                    <i class="fas fa-tachometer-alt action-icon"></i>
                    <span>لوحة التحكم</span>
                </a>
                
                <a href="admin/orders.php" class="action-btn admin-action">
                    <i class="fas fa-shopping-cart action-icon"></i>
                    <span>إدارة الطلبات</span>
                </a>
                
                <a href="admin/products.php" class="action-btn admin-action">
                    <i class="fas fa-box action-icon"></i>
                    <span>إدارة المنتجات</span>
                </a>
                
                <a href="admin/reports.php" class="action-btn admin-action">
                    <i class="fas fa-chart-bar action-icon"></i>
                    <span>التقارير والإحصائيات</span>
                </a>
                
                <?php if (($user['user_type'] ?? '') == 'admin'): ?>
                    <a href="admin/users.php" class="action-btn admin-action">
                        <i class="fas fa-users action-icon"></i>
                        <span>إدارة المستخدمين</span>
                    </a>
                    
                    <a href="admin/settings.php" class="action-btn admin-action">
                        <i class="fas fa-cogs action-icon"></i>
                        <span>إعدادات الموقع</span>
                    </a>
                <?php endif; ?>
                
                <?php if (($user['user_type'] ?? '') == 'manager'): ?>
                    <a href="admin/inventory.php" class="action-btn admin-action">
                        <i class="fas fa-warehouse action-icon"></i>
                        <span>المخزون</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div> -->
    <?php else: ?>
     
    <?php endif; ?>
</main>


    <div class="main-content container py-4">
        <main class="container py-4" style="margin-top: 60px; margin-bottom: 70px;">
     <?php


        // جلب العروض النشطة من قاعدة البيانات
        $sql = "SELECT o.*, 
               COUNT(op.product_id) as products_count
        FROM offers o
        LEFT JOIN offer_products op ON o.id = op.offer_id
        WHERE o.is_active = 1 
          AND CURDATE() BETWEEN DATE(o.start_date) AND DATE(o.end_date)
        GROUP BY o.id
        ORDER BY o.display_order ASC
        LIMIT 5";

            $result = $conn->query($sql);
            $offers = [];
            $active_indicators = 0;

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // معالجة الصورة
                    if (empty($row['image']) || !file_exists($row['image'])) {
                        $row['image'] = 'img/default-offer.jpg';
                    }
                    
                    // معالجة نص الزر
                    if (empty($row['button_text'])) {
                        $row['button_text'] = 'اكتشف العروض';
                    }
                    
                    $offers[] = $row;
                    $active_indicators = $result->num_rows;
                }
            }

        // إذا لم توجد عروض، نعرض رسالة بدلاً من الكاروسيل
        $has_offers = !empty($offers);
    ?>
<?php if ($has_offers): ?>
<section class="offers-section mb-4">
    <h2 class="section-title">عروض اليوم</h2>
    <div id="offersCarousel" class="carousel slide" data-bs-ride="carousel">
        
        <?php if ($active_indicators > 1): ?>
        <div class="carousel-indicators">
            <?php for($i = 0; $i < $active_indicators; $i++): ?>
                <button type="button" 
                        data-bs-target="#offersCarousel" 
                        data-bs-slide-to="<?php echo $i; ?>" 
                        class="<?php echo $i === 0 ? 'active' : ''; ?>" 
                        aria-label="عرض <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <div class="carousel-inner rounded-3 shadow-sm">
            <?php foreach ($offers as $index => $offer): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo $offer['image']; ?>" 
                         class="d-block w-100" 
                         alt="<?php echo htmlspecialchars($offer['title']); ?>"
                         style="height: 400px; object-fit: cover;"
                         onerror="this.src='img/default-offer.jpg'">
                    
                    <div class="carousel-caption">
                        <h5><?php echo htmlspecialchars($offer['title']); ?></h5>
                        <p class="d-none d-sm-block"><?php echo htmlspecialchars($offer['description']); ?></p>
                        
                        <?php if ($offer['products_count'] > 0): ?>
                            <small class="d-block mb-2 text-white">
                                <i class="fas fa-box me-1"></i> <?php echo $offer['products_count']; ?> منتج
                            </small>
                        <?php endif; ?>
                        
                        <a href="offer-details.php?id=<?php echo $offer['id']; ?>" 
                           class="btn btn-light rounded-pill mt-2">
                            <?php echo htmlspecialchars($offer['button_text']); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($active_indicators > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#offersCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">السابق</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#offersCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">التالي</span>
        </button>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>

<div class="alert alert-info text-center mb-4">
    <i class="fas fa-info-circle me-2"></i> لا توجد عروض نشطة حالياً
</div>
<?php endif; ?>
<!-- الفئات المميزه -->

<?php include 'featured-categories.php'; ?>
<?php include 'categories-section.php'; ?>

 <!-- المنتجات المميزة -->
        <section class="products-section mb-5">
            <h2 class="section-title">المنتجات المميزة</h2>
            <div class="products-grid" id="featured-products-grid">
                <?php while($product = mysqli_fetch_assoc($featured_products_result)): ?>
                    <?php echo generateProductCard($product); ?>
                <?php endwhile; ?>
            </div>
        </section>

    
<!-- عروض خاصه  -->
        <!-- <section class="special-offers-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title">عروض حصرية</h2>
                <a href="#" class="btn btn-outline-danger btn-sm">عرض الكل</a>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="special-offer-card bg-gradient-danger text-white rounded-3 p-4 position-relative">
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3">خصم 30%</span>
                        <h4>عرض نهاية الموسم</h4>
                        <p>وفر على مجموعة العناية الكاملة بالبشرة</p>
                        <div class="d-flex align-items-center">
                            <span class="me-2 text-decoration-line-through opacity-75">300 ر.س</span>
                            <span class="h5 mb-0">210 ر.س</span>
                        </div>
                        <button class="btn btn-light mt-3">اطلبي الآن</button>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="special-offer-card bg-primary text-white rounded-3 p-4">
                        <h4>شحن مجاني</h4>
                        <p>لطلبات فوق 200 ريال</p>
                        <small>استخدم الكود: FREESHIP</small>
                    </div>
                </div>
            </div>
        </section> -->
 
     <style>
        /* تصميم القسم الرئيسي */
        .brands-section {
            padding: 50px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .brands-section h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }

        .brands-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 50%;
            transform: translateX(50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #ff3366, #ff6699);
            border-radius: 2px;
        }

        /* تصميم حاوية البراندات المتحركة */
        .brands-slider-container {
            position: relative;
            overflow: hidden;
            padding: 20px 0;
        }

        .brands-slider-track {
            display: flex;
            gap: 25px;
            animation: slide 30s linear infinite;
            padding: 10px;
        }

        @keyframes slide {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-250px * 4)); }
        }

        .brands-slider-track:hover {
            animation-play-state: paused;
        }

        /* تصميم بطاقة البراند */
        .brand-item {
            background: white;
            border-radius: 20px;
            padding: 25px 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 51, 102, 0.1);
            min-width: 220px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .brand-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #ff3366, #ff6699);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .brand-item:hover::before {
            opacity: 1;
        }

        .brand-item:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 20px 40px rgba(255, 51, 102, 0.15);
            border-color: #ff3366;
        }

        /* الدائرة للشعار */
        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff3366 0%, #ff6699 100%);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(255, 51, 102, 0.3);
            transition: all 0.3s;
        }

        .brand-item:hover .logo-circle {
            transform: rotate(360deg);
            box-shadow: 0 15px 35px rgba(255, 51, 102, 0.4);
        }

        .logo-circle::after {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }

        .brand-item:hover .brand-logo {
            transform: scale(1.1);
        }

        /* اسم البراند */
        .brand-item h5 {
            color: #333;
            font-weight: 700;
            margin: 15px 0 8px;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .brand-item:hover h5 {
            color: #ff3366;
        }

        /* الدولة */
        .brand-item .country {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .brand-item .country i {
            color: #ff3366;
            font-size: 0.8rem;
        }

        /* عدد المنتجات */
        .brand-item .product-count {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.2);
        }

        .brand-item .product-count i {
            font-size: 0.8rem;
        }

        /* الشاشة المنبثقة */
        .brand-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .brand-modal {
            position: fixed;
            top: 50%;
            right: 50%;
            transform: translate(50%, -50%);
            width: 90%;
            max-width: 500px;
            background: white;
            border-radius: 25px;
            padding: 40px;
            z-index: 10000;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            animation: modalSlide 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translate(50%, -40%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(50%, -50%) scale(1);
            }
        }

        .modal-close {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #ff3366;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s;
            z-index: 1;
        }

        .modal-close:hover {
            background: #e62e5c;
            transform: rotate(90deg);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .modal-logo-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff3366, #ff6699);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(255, 51, 102, 0.3);
        }

        .modal-logo-circle::after {
            content: '';
            position: absolute;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: white;
        }

        .modal-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            position: relative;
            z-index: 1;
        }

        .modal-title {
            font-size: 2rem;
            color: #333;
            margin: 15px 0 10px;
            font-weight: 800;
        }

        .modal-country {
            color: #666;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 5px;
        }

        .modal-country i {
            color: #ff3366;
        }

        .modal-description {
            color: #777;
            text-align: center;
            line-height: 1.6;
            margin: 20px 0;
            padding: 0 20px;
        }

        .modal-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 30px 0;
            text-align: center;
        }

        .stat-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .stat-item:hover {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            color: white;
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }


        .btn-view-all {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            color: white;
        }

        .btn-view-all:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 51, 102, 0.3);
        }

        .btn-visit-site {
            background: white;
            color: #333;
            border: 2px solid #ddd;
        }

        .btn-visit-site:hover {
            border-color: #ff3366;
            color: #ff3366;
        }

        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .brands-slider-track {
                animation: slide 20s linear infinite;
            }
            
            .brand-item {
                min-width: 180px;
            }
            
            .logo-circle {
                width: 80px;
                height: 80px;
            }
            
            .logo-circle::after {
                width: 70px;
                height: 70px;
            }
            
            .brand-logo {
                width: 50px;
                height: 50px;
            }
            
            .brand-modal {
                width: 95%;
                padding: 30px 20px;
            }
            
            .modal-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .brands-section h2 {
                font-size: 2rem;
            }
            
            .brand-item {
                min-width: 150px;
                padding: 20px 10px;
            }
            
            .modal-stats {
                grid-template-columns: 1fr;
            }
            
            .modal-actions {
                flex-direction: column;
            }
        }
    </style>

        <!-- عرض البراندات -->
    <div class="brands-section">
        <h2>علاماتنا التجارية</h2>
        
        <?php if(mysqli_num_rows($brands_result) > 0): ?>
            <div class="brands-slider-container">
                <div class="brands-slider-track" id="brandsSlider">
                    <?php 
                    // إعادة تعيين المؤشر إلى البداية
                    mysqli_data_seek($brands_result, 0);
                    while($brand = mysqli_fetch_assoc($brands_result)): 
                        $logo = !empty($brand['logo']) ? $brand['logo'] : 'img/1.jpg';
                    ?>
                        <div class="brand-item" onclick="openBrandModal(<?php echo htmlspecialchars(json_encode($brand)); ?>)">
                            <div class="logo-circle">
                                <img src="<?php echo $logo; ?>" 
                                     alt="<?php echo htmlspecialchars($brand['name']); ?>"
                                     class="brand-logo"
                                     onerror="this.src='img/1.jpg'">
                            </div>
                            
                            <h5><?php echo htmlspecialchars($brand['name']); ?></h5>
                            
                           
                            
                            <div class="product-count">
                                <i class="fas fa-box"></i>
                                <span><?php echo $brand['products_count']; ?> منتج</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> لا توجد علامات تجارية حالياً
            </div>
        <?php endif; ?>
    </div>

    <!-- الشاشة المنبثقة -->
    <div class="brand-modal-overlay" id="brandModalOverlay"></div>
    
    <div class="brand-modal" id="brandModal" style="display: none;">
        <button class="modal-close" onclick="closeBrandModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-header">
            <div class="modal-logo-circle">
                <img id="modalBrandLogo" src="" alt="" class="modal-logo">
            </div>
            
            <h2 class="modal-title" id="modalBrandName"></h2>
            
            <div class="modal-country" id="modalBrandCountry">
                <i class="fas fa-map-marker-alt"></i>
                <span id="modalCountryText"></span>
            </div>
            
            <p class="modal-description" id="modalBrandDescription"></p>
        </div>
        
         <!-- <div class="modal-stats">
            <div class="stat-item">
                <div class="stat-number" id="modalProductsCount">0</div>
                <div class="stat-label">المنتجات</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="modalRating">4.8</div>
                <div class="stat-label">التقييم</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="modalSince">2023</div>
                <div class="stat-label">سنة التأسيس</div>
            </div>
        </div>  -->
        
        <h4>أحدث المنتجات</h4>
        <div class="products-grid" id="modalProductsGrid">
            <!-- سيتم تعبئتها بالمنتجات -->
        </div>
        
        <div class="modal-actions">
            <button class="btn-modal btn-view-all" id="modalViewProductsBtn">
                <i class="fas fa-eye"></i>
                عرض جميع المنتجات
            </button>
            <button class="btn-modal btn-visit-site" id="modalVisitSiteBtn">
                <i class="fas fa-external-link-alt"></i>
                زيارة الموقع
            </button>
        </div>
    </div>

    <script>
        // فتح شاشة البراند المنبثقة
        function openBrandModal(brandData) {
            // تعبئة البيانات
            document.getElementById('modalBrandName').textContent = brandData.name;
            document.getElementById('modalBrandLogo').src = brandData.logo || 'img/1.jpg';
            document.getElementById('modalBrandLogo').alt = brandData.name;
            
            if (brandData.country) {
                document.getElementById('modalCountryText').textContent = brandData.country;
                document.getElementById('modalBrandCountry').style.display = 'flex';
            } else {
                document.getElementById('modalBrandCountry').style.display = 'none';
            }
            
            document.getElementById('modalBrandDescription').textContent = 
                brandData.description || 'علامة تجارية متميزة تقدم منتجات عالية الجودة.';
            
            document.getElementById('modalProductsCount').textContent = brandData.products_count;
            
            // إعداد المنتجات (بيانات وهمية للعرض)
            loadBrandProducts(brandData.id);
            
            // إعداد الأزرار
            document.getElementById('modalViewProductsBtn').onclick = function() {
                window.location.href = `products.php?brand_id=${brandData.id}`;
            };
            
            if (brandData.website) {
                document.getElementById('modalVisitSiteBtn').onclick = function() {
                    window.open(brandData.website, '_blank');
                };
                document.getElementById('modalVisitSiteBtn').style.display = 'flex';
            } else {
                document.getElementById('modalVisitSiteBtn').style.display = 'none';
            }
            
            // إظهار الشاشة المنبثقة
            document.getElementById('brandModalOverlay').style.display = 'block';
            document.getElementById('brandModal').style.display = 'block';
            
            // منع التمرير
            document.body.style.overflow = 'hidden';
        }

        // إغلاق الشاشة المنبثقة
        function closeBrandModal() {
            document.getElementById('brandModalOverlay').style.display = 'none';
            document.getElementById('brandModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // إغلاق بالنقر خارج النافذة
        document.getElementById('brandModalOverlay').onclick = closeBrandModal;

        // منع إغلاق النافذة عند النقر داخلها
        document.getElementById('brandModal').onclick = function(e) {
            e.stopPropagation();
        };

        // تحميل منتجات البراند (وهمي للعرض)
        function loadBrandProducts(brandId) {
            const productsGrid = document.getElementById('modalProductsGrid');
            productsGrid.innerHTML = `
                <div class="col-12 text-center py-3">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">جاري تحميل المنتجات...</p>
                </div>
            `;
            
            // هنا يمكنك استدعاء API حقيقي
            setTimeout(() => {
                // بيانات وهمية للعرض
                const mockProducts = [
                    { id: 1, name: 'منتج رقم 1', price: '120 ر.س', image: 'img/1.jpg' },
                    { id: 2, name: 'منتج رقم 2', price: '85 ر.س', image: 'img/1.jpg' },
                    { id: 3, name: 'منتج رقم 3', price: '200 ر.س', image: 'img/1.jpg' },
                    { id: 4, name: 'منتج رقم 4', price: '150 ر.س', image: 'img/1.jpg' },
                ];
                
                let productsHtml = '';
                mockProducts.forEach(product => {
                    productsHtml += `
                        <div class="product-item" onclick="viewProduct(${product.id})">
                            <img src="${product.image}" alt="${product.name}" class="product-img">
                            <div class="product-name">${product.name}</div>
                            <div class="product-price">${product.price}</div>
                        </div>
                    `;
                });
                
                productsGrid.innerHTML = productsHtml;
            }, 1000);
        }

        // دالة عرض المنتج
        function viewProduct(productId) {
            alert('عرض تفاصيل المنتج رقم: ' + productId);
            // يمكنك توجيه المستخدم لصفحة المنتج
        }

        // إيقاف وتشغيل الحركة عند التمرير
        const sliderTrack = document.getElementById('brandsSlider');
        let isPaused = false;

        sliderTrack.addEventListener('mouseenter', () => {
            if (!isPaused) {
                sliderTrack.style.animationPlayState = 'paused';
            }
        });

        sliderTrack.addEventListener('mouseleave', () => {
            if (!isPaused) {
                sliderTrack.style.animationPlayState = 'running';
            }
        });

        // للهواتف: إيقاف الحركة عند اللمس
        sliderTrack.addEventListener('touchstart', () => {
            sliderTrack.style.animationPlayState = 'paused';
            isPaused = true;
        });

        sliderTrack.addEventListener('touchend', () => {
            setTimeout(() => {
                sliderTrack.style.animationPlayState = 'running';
                isPaused = false;
            }, 3000);
        });
    </script>


    </div>
    <!-- Modals -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">
                        <i class="fas fa-shopping-cart me-2"></i>سلة التسوق
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cartModalBody">
                    <!-- سيتم تحميل المحتوى عبر AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="mt-3">جاري تحميل سلة التسوق...</p>
                    </div>
                </div>
                <div class="modal-footer d-block text-center" id="cartModalFooter" style="display: none;">
                    <div class="cart-summary mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>المجموع:</span>
                            <strong id="cart-total-price">0 ر.س</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>الضريبة:</span>
                            <span id="cart-tax">0 ر.س</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-top pt-2">
                            <span class="fw-bold">الإجمالي:</span>
                            <strong class="text-danger fs-5" id="cart-grand-total">0 ر.س</strong>
                        </div>
                    </div>
                    <a href="cart.php" class="btn btn-danger rounded-pill w-75 mb-2">
                        <i class="fas fa-shopping-bag me-2"></i>اذهب إلى السلة
                    </a>
                    <button class="btn btn-outline-danger rounded-pill w-75" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-right me-2"></i>متابعة التسوق
                    </button>
                </div>
            </div>
        </div>
    </div>

            <!-- Modal للمفضلة -->
        <div class="modal fade" id="favoritesModal" tabindex="-1" aria-labelledby="favoritesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="favoritesModalLabel">
                            <i class="fas fa-heart me-2"></i>قائمة المفضلة
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="favoritesModalBody">
                        <!-- سيتم تحميل المحتوى عبر AJAX -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-3">جاري تحميل قائمة المفضلة...</p>
                        </div>
                    </div>
                    <div class="modal-footer d-block text-center" id="favoritesModalFooter" style="display: none;">
                        <a href="wishlist.php" class="btn btn-danger rounded-pill w-75 mb-2">
                            <i class="fas fa-heart me-2"></i>عرض كل المفضلة
                        </a>
                        <button class="btn btn-outline-danger rounded-pill w-75" data-bs-dismiss="modal">
                            <i class="fas fa-arrow-right me-2"></i>متابعة التسوق
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">الإشعارات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action notification-item unread">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">طلبك قيد التجهيز</h6>
                                <small>منذ 5 دقائق</small>
                            </div>
                            <p class="mb-1">تم تأكيد طلبك #1234 وسيتم شحنه قريباً</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action notification-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">عرض خاص</h6>
                                <small>منذ ساعة</small>
                            </div>
                            <p class="mb-1">خصم 20% على منتجات العناية بالبشرة</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action notification-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">شحن مجاني</h6>
                                <small>منذ يوم</small>
                            </div>
                            <p class="mb-1">احصلي على شحن مجاني لطلبات فوق 200 ريال</p>
                        </a>
                    </div>
                </div>
                <div class="modal-footer d-block text-center">
                    <a href="notifications.php" class="btn btn-outline-danger rounded-pill w-75">عرض كل الإشعارات</a>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة منبثقة لعرض المنتج -->
    <!-- نافذة منبثقة لعرض المنتج -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="productDetailModalLabel">تفاصيل المنتج</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <!-- صور المنتج -->
                    <div class="col-lg-6">
                        <div class="product-images-container">
                            <div class="main-image mb-3">
                                <img src="img/1.jpg" alt="منتج" class="img-fluid rounded shadow-sm product-detail-img" 
                                     id="product-detail-img" style="max-height: 400px; object-fit: cover; width: 100%;">
                            </div>
                            <div class="thumbnail-images d-flex gap-2" id="product-thumbnails">
                                <!-- سيتم ملؤها بالصور الإضافية -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- تفاصيل المنتج -->
                    <div class="col-lg-6">
                        <h2 class="fw-bold mb-3" id="product-detail-name">اسم المنتج</h2>
                        
                        <div class="product-meta mb-4">
                            <span class="badge bg-primary me-2" id="product-detail-category">الفئة</span>
                            <span class="badge bg-success" id="product-stock-badge">متوفر</span>
                            <span class="badge bg-warning" id="new-badge" style="display: none;">جديد</span>
                            <span class="badge bg-danger" id="featured-badge" style="display: none;">مميز</span>
                        </div>
                        
                        <div class="price-section mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <h3 class="text-danger fw-bold mb-0" id="product-detail-price">150 ر.س</h3>
                                <h5 class="text-muted text-decoration-line-through mb-0" 
                                    id="product-detail-old-price" style="display: none;">180 ر.س</h5>
                            </div>
                        </div>
                        
                        <!-- التقييم -->
                        <div class="rating-section mb-4">
                            <div class="rating mb-2" id="product-detail-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <a href="#reviews" class="text-decoration-none" id="reviews-link">
                                <small class="text-muted">(0 تقييم)</small>
                            </a>
                        </div>
                        
                        <!-- الوصف -->
                        <div class="description-section mb-4">
                            <h5 class="fw-bold mb-2">وصف المنتج</h5>
                            <p class="text-muted" id="product-detail-description">
                                وصف المنتج يظهر هنا. هذا المنتج رائع ومميز ويحتوي على مكونات طبيعية.
                            </p>
                        </div>
                        
                        <!-- الألوان والمقاسات -->
                        <div class="variants-section mb-4">
                            <div id="product-colors"></div>
                            <div id="product-sizes"></div>
                        </div>
                        
                        <!-- المخزون -->
                        <div class="stock-section mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <strong>الكمية المتاحة:</strong>
                                <span class="badge bg-success" id="product-detail-stock">15</span>
                                <span>قطعة</span>
                            </div>
                        </div>
                        
                        <!-- تحكم الكمية -->
                        <div class="quantity-controls mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <label class="fw-bold">الكمية:</label>
                                <div class="input-group" style="width: 150px;">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">-</button>
                                    <input type="text" class="form-control text-center quantity-input" 
                                           value="1" readonly id="product-detail-quantity">
                                    <button class="btn btn-outline-secondary quantity-btn" type="button">+</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- أزرار الإجراءات -->
                        <div class="action-buttons d-grid gap-2 d-md-flex">
                            <button class="btn btn-danger flex-fill py-3" id="add-to-cart-detail">
                                <i class="fas fa-shopping-cart me-2"></i>
                                <span class="btn-text">أضف إلى السلة</span>
                            </button>
                            <button class="btn btn-outline-danger flex-fill py-3" id="add-to-favorites-detail">
                                <i class="far fa-heart me-2"></i>
                                <span class="btn-text">أضف إلى المفضلة</span>
                            </button>
                        </div>
                        
                        <!-- معلومات إضافية -->
                        <div class="additional-info mt-4 pt-4 border-top">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">رمز المنتج:</small>
                                    <strong id="product-barcode">غير متوفر</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">تاريخ الصلاحية:</small>
                                    <strong id="product-expiry">غير محدد</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="shareProduct()">
                    <i class="fas fa-share-alt me-2"></i>مشاركة
                </button>
            </div>
        </div>
    </div>
</div>
    <style>
        /* Welcome Card */
                .welcome-card {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 15px;
                    padding: 10px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .guest-welcome {
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                }

                .welcome-content h2 {
                    font-size: 28px;
                    margin-bottom: 10px;
                }

                .welcome-text {
                    font-size: 16px;
                    opacity: 0.9;
                    max-width: 600px;
                }

                .welcome-actions .btn {
                    margin-left: 10px;
                }

                /* Stats Cards */
                .stats-cards {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                    gap: 20px;
                    margin: 30px 0;
                }

                .stat-card {
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    transition: transform 0.3s ease;
                    position: relative;
                    overflow: hidden;
                }

                .stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
                }

                .guest-stat {
                    background: #f8f9fa;
                    text-align: center;
                }

                .admin-stat {
                    border-top: 4px solid #4CAF50;
                }

                .manager-stat {
                    border-top: 4px solid #FF9800;
                }

                .stat-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    margin-bottom: 15px;
                }

                .order-icon { background: #e3f2fd; color: #2196F3; }
                .favorite-icon { background: #fce4ec; color: #E91E63; }
                .cart-icon { background: #e8f5e9; color: #4CAF50; }
                .points-icon { background: #fff3e0; color: #FF9800; }
                .users-icon { background: #e8eaf6; color: #3F51B5; }
                .sales-icon { background: #e8f5e8; color: #4CAF50; }
                .products-icon { background: #e3f2fd; color: #2196F3; }
                .revenue-icon { background: #f3e5f5; color: #9C27B0; }
                .pending-icon { background: #fff3cd; color: #856404; }
                .lowstock-icon { background: #f8d7da; color: #721c24; }

                .stat-info h3 {
                    font-size: 32px;
                    margin: 0;
                    color: #333;
                }

                .stat-info p {
                    color: #666;
                    margin-top: 5px;
                }

                .stat-link {
                    display: inline-block;
                    margin-top: 15px;
                    color: #2196F3;
                    text-decoration: none;
                    font-weight: 500;
                }

                .stat-link:hover {
                    text-decoration: underline;
                }

                /* Quick Actions */
                .quick-actions {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                    gap: 15px;
                    margin-top: 20px;
                }

                .action-btn {
                    background: white;
                    border: 1px solid #e0e0e0;
                    border-radius: 10px;
                    padding: 20px;
                    text-decoration: none;
                    color: #333;
                    transition: all 0.3s ease;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }

                .action-btn:hover {
                    background: #f5f5f5;
                    border-color: #2196F3;
                    transform: translateY(-3px);
                    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);
                }

                .admin-action {
                    border-color: #4CAF50;
                }

                .admin-action:hover {
                    border-color: #388E3C;
                    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);
                }

                .guest-action {
                    border-color: #ddd;
                }

                .guest-action:hover {
                    border-color: #f5576c;
                    box-shadow: 0 4px 12px rgba(245, 87, 108, 0.1);
                }

                .action-icon {
                    font-size: 28px;
                    margin-bottom: 10px;
                    color: #2196F3;
                }

                .admin-action .action-icon {
                    color: #4CAF50;
                }

                .guest-action .action-icon {
                    color: #f5576c;
                }

                .action-btn span {
                    font-weight: 500;
                    font-size: 14px;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .welcome-card {
                        flex-direction: column;
                        text-align: center;
                        padding: 20px;
                    }
                    
                    .welcome-actions {
                        margin-top: 20px;
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                    }
                    
                    .welcome-actions .btn {
                        margin: 5px 0;
                    }
                    
                    .stats-cards {
                        grid-template-columns: repeat(2, 1fr);
                    }
                    
                    .quick-actions {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }

                @media (max-width: 480px) {
                    .stats-cards,
                    .quick-actions {
                        grid-template-columns: 1fr;
                    }
                }
    </style>
  

     <?php include 'footer.php'; ?>
   
</body>
</html>
  