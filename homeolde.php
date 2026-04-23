<?php
require_once 'config/database.php';
require_once 'functions.php';
session_start();

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
$all_products_query = "SELECT p.*, c.name as category_name 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE p.status = 'active' 
                      ORDER BY p.created_at DESC LIMIT 20";
$all_products_result = mysqli_query($conn, $all_products_query);
?>
<?php include 'header.php'; ?>

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
   <section class="featured-categories mb-5">
    <h2 class="section-title">الفئات المميزة</h2>
    <div class="featured-categories-container" id="featured-categories-container">
        <?php while($category = mysqli_fetch_assoc($featured_categories_result)): ?>
            <a href="category-details.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                <div class="featured-category">
                    <img src="<?php echo !empty($category['image']) ? $category['image'] : 'img/default-category.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="featured-category-img">
                    <p class="text-dark fw-bold mt-2"><?php echo htmlspecialchars($category['name']); ?></p>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<section class="categories-section mb-5">
    <h2 class="section-title">جميع الفئات</h2>
    
    <!-- فلتر الفئات -->
    <div class="category-filter mb-3" id="category-filter">
        <button class="category-filter-btn active" data-category="all">الكل</button>
        <?php 
        // إعادة تعيين المؤشر لاستخدام النتيجة مرة أخرى
        mysqli_data_seek($all_categories_result, 0);
        while($category = mysqli_fetch_assoc($all_categories_result)): ?>
            <button class="category-filter-btn" data-category="<?php echo $category['id']; ?>">
                <?php echo htmlspecialchars($category['name']); ?>
            </button>
        <?php endwhile; ?>
    </div>
    
    <!-- حاوية عرض الفئات -->
    <div class="categories-container" id="categories-container">
        <?php 
        // إعادة تعيين المؤشر مرة أخرى
        mysqli_data_seek($all_categories_result, 0);
        $counter = 0;
        while($category = mysqli_fetch_assoc($all_categories_result)): 
            $counter++;
        ?>
            <div class="category-item" 
                 data-category="<?php echo $category['id']; ?>"
                 onclick="loadCategoryProducts(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')">
                <div class="category-img-container">
                    <img src="<?php echo $category['image'] ?? 'img/default-category.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="category-img">
                    <?php if($category['featured']): ?>
                        <span class="featured-badge">مميزة</span>
                    <?php endif; ?>
                </div>
                <p class="category-name"><?php echo htmlspecialchars($category['name']); ?></p>
                <small class="text-muted"><?php echo $category['product_count'] ?? 0; ?> منتج</small>
            </div>
        <?php endwhile; ?>
    </div>
    
    <!-- حاوية عرض منتجات الفئة -->
    <div id="category-products-section" class="mt-4" style="display: none;">
        <div class="category-products-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="section-title mb-0" id="category-products-title"></h3>
                    <p class="text-muted mb-0" id="category-products-count"></p>
                </div>
                <button class="btn btn-outline-danger" onclick="closeCategoryProducts()">
                    <i class="fas fa-times me-1"></i> إغلاق
                </button>
            </div>
        </div>
        <div class="products-grid mt-3" id="category-products-grid">
            <!-- سيتم تعبئتها بالمنتجات عبر AJAX -->
        </div>
    </div>
</section>
<style>
        /* أضف هذه الأنماط إلى CSS الموجود */
        .category-item {
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding-bottom: 15px;
        }

        .category-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .category-img-container {
            height: 120px;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .category-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .category-item:hover .category-img {
            transform: scale(1.1);
        }

        .category-item p {
            text-align: center;
            font-weight: 600;
            margin: 0;
            padding: 0 10px;
        }

        .category-products-header {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: var(--primary-color);
            grid-column: 1 / -1;
        }

        .loading-spinner i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* تحسين مظهر قسم المنتجات */
        #category-products-section {
            animation: fadeIn 0.5s ease;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* فلتر الفئات */
        .category-filter {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding: 10px 0;
        }

        .category-filter-btn {
            white-space: nowrap;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            background: white;
            color: var(--dark-color);
            transition: all 0.3s;
            cursor: pointer;
        }

        .category-filter-btn:hover:not(.active) {
            background-color: #f8f9fa;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .category-filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* حاوية الفئات */
        .categories-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }



</style>

<!-- <style>
        /* أنماط بطاقة المنتج */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #f0f0f0;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: #ff6b8b;
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            cursor: pointer;
        }

        .product-card:hover .product-img {
            transform: scale(1.05);
        }

        /* الشارات */
        .featured-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(45deg, #ff6b8b, #ff8fab);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .new-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .product-info {
            padding: 18px;
        }

        .product-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
            line-height: 1.4;
            cursor: pointer;
            transition: color 0.3s;
        }

        .product-title:hover {
            color: #ff6b8b;
        }

        .product-category {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }

        .price-rating-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .product-price {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .current-price {
            font-size: 18px;
            font-weight: bold;
            color: #ff6b8b;
        }

        .rating {
            color: #ffc107;
            font-size: 14px;
        }

        .product-description {
            font-size: 13px;
            color: #777;
            line-height: 1.5;
            margin-bottom: 15px;
            height: 40px;
            overflow: hidden;
        }

        /* شارة الكمية */
        .stock-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        /* أزرار الإجراءات */
        .product-actions {
            display: flex;
            gap: 8px;
            padding: 0 18px 18px;
        }

        .add-to-cart-btn, .favorite-btn, .quick-view-btn {
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
        }

        .add-to-cart-btn {
            flex: 1;
            background: linear-gradient(45deg, #ff6b8b, #ff8fab);
            color: white;
            gap: 6px;
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(45deg, #ff5585, #ff7b9e);
            transform: translateY(-2px);
        }

        .add-to-cart-btn.loading {
            opacity: 0.7;
            cursor: wait;
        }

        .add-to-cart-btn.success {
            background: #28a745;
        }

        .favorite-btn {
            width: 40px;
            background: #f8f9fa;
            color: #ccc;
            border: 1px solid #eee;
        }

        .favorite-btn.active {
            color: #ff6b8b;
            background: #fff5f7;
            border-color: #ff6b8b;
        }

        .favorite-btn:hover {
            color: #ff6b8b;
            background: #fff5f7;
        }

        .favorite-btn.heartbeat {
            animation: heartbeat 0.3s;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .quick-view-btn {
            width: 40px;
            background: #f8f9fa;
            color: #666;
            border: 1px solid #eee;
        }

        .quick-view-btn:hover {
            background: #e9ecef;
            color: #333;
        }

        .btn-text {
            display: inline-block;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .product-image-container {
                height: 180px;
            }
            
            .product-actions {
                flex-direction: column;
                gap: 6px;
            }
            
            .add-to-cart-btn, .favorite-btn, .quick-view-btn {
                width: 100%;
                padding: 12px;
            }
            
            .btn-text {
                display: inline;
            }
        }

        @media (max-width: 480px) {
            .btn-text {
                display: none;
            }
        }
</style> -->
<style>
        .cart-item-img, .favorite-item-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .cart-item, .favorite-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .cart-item:hover, .favorite-item:hover {
            background-color: #f9f9f9;
        }

        .cart-item:last-child, .favorite-item:last-child {
            border-bottom: none;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .quantity-btn:hover {
            background: #f8f9fa;
            border-color: #ff6b8b;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }

        .empty-cart, .empty-favorites {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-cart i, .empty-favorites i {
            font-size: 60px;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .product-options {
            font-size: 12px;
            color: #6c757d;
        }

        .delete-btn {
            transition: all 0.3s;
        }

        .delete-btn:hover {
            transform: scale(1.1);
            color: #dc3545 !important;
        }

        .cart-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #eee;
        }
</style>

        <!-- المنتجات المميزة -->
        <section class="products-section mb-5">
            <h2 class="section-title">المنتجات المميزة</h2>
            <div class="products-grid" id="featured-products-grid">
                <?php while($product = mysqli_fetch_assoc($featured_products_result)): ?>
                    <?php echo generateProductCard($product); ?>
                <?php endwhile; ?>
            </div>
        </section>

     

        <section class="special-offers-section">
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
        </section>
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
    <div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailModalLabel">تفاصيل المنتج</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="" alt="منتج" class="product-detail-img" id="product-detail-img">
                        </div>
                        <div class="col-md-6">
                            <h3 id="product-detail-name">اسم المنتج</h3>
                            <p class="product-category" id="product-detail-category">الفئة: مكياج</p>
                            <div class="mb-3">
                                <span class="product-detail-price" id="product-detail-price">150 ر.س</span>
                                <span class="product-detail-old-price" id="product-detail-old-price">180 ر.س</span>
                            </div>
                            <div class="rating mb-3" id="product-detail-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <p id="product-detail-description">وصف المنتج يظهر هنا. هذا المنتج رائع ومميز ويحتوي على مكونات طبيعية.</p>
                            <div class="mb-3">
                                <strong>الكمية المتاحة:</strong> <span id="product-detail-stock">15</span> قطعة
                            </div>
                            <div class="quantity-controls mb-4">
                                <label class="me-2"><strong>الكمية:</strong></label>
                                <button class="quantity-btn">-</button>
                                <input type="text" class="quantity-input" value="1" readonly id="product-detail-quantity">
                                <button class="quantity-btn">+</button>
                            </div>
                            <div class="d-grid gap-2 d-md-flex">
                                <button class="btn btn-danger flex-fill" id="add-to-cart-detail">
                                    <i class="fas fa-shopping-cart me-2"></i>أضف إلى السلة
                                </button>
                                <button class="btn btn-outline-danger flex-fill" id="add-to-favorites-detail">
                                    <i class="fas fa-heart me-2"></i>أضف إلى المفضلة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  <?php include 'footer.php'; ?>
  
    <script>
// دالة لتحميل منتجات الفئة
function loadCategoryProducts(categoryId, categoryName) {
    console.log('جاري تحميل منتجات الفئة:', categoryId, categoryName);
    
    // إظهار قسم منتجات الفئة
    const productsSection = document.getElementById('category-products-section');
    const productsGrid = document.getElementById('category-products-grid');
    const productsTitle = document.getElementById('category-products-title');
    
    if (!productsSection || !productsGrid || !productsTitle) {
        console.error('عناصر DOM غير موجودة');
        showAlert('حدث خطأ في تحميل المنتجات', 'error');
        return;
    }
    
    productsSection.style.display = 'block';
    productsTitle.textContent = 'منتجات ' + categoryName;
    
    // إظهار مؤشر التحميل
    productsGrid.innerHTML = `
        <div class="loading-spinner text-center py-5" style="grid-column: 1 / -1;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <p class="mt-3">جاري تحميل المنتجات...</p>
        </div>
    `;
    
    // تحميل المنتجات عبر AJAX
    $.ajax({
        url: 'ajax/get_category_products.php',
        method: 'GET',
        data: {
            category_id: categoryId,
            limit: 10
        },
        dataType: 'json',
        success: function(response) {
            console.log('استجابة AJAX:', response);
            
            if (response.success && response.products) {
                let productsHtml = '';
                
                if (response.products.length > 0) {
                    // بناء بطاقات المنتجات
                    response.products.forEach(function(product) {
                        // إنشاء نجوم التقييم
                        let ratingStars = '';
                        const rating = parseFloat(product.rating) || 0;
                        for (let i = 1; i <= 5; i++) {
                            if (i <= Math.round(rating)) {
                                ratingStars += '<i class="fas fa-star text-warning"></i>';
                            } else {
                                ratingStars += '<i class="far fa-star text-warning"></i>';
                            }
                        }
                        
                        // تحضير السعر القديم
                        let oldPriceHtml = '';
                        if (product.old_price && parseFloat(product.old_price) > 0 && 
                            parseFloat(product.old_price) > parseFloat(product.selling_price)) {
                            oldPriceHtml = `<small class="text-muted text-decoration-line-through ms-2">${product.old_price} ر.س</small>`;
                        }
                        
                        // التحقق من المفضلة
                        let favoriteClass = product.is_favorite ? 'active' : '';
                        let favoriteIcon = product.is_favorite ? 'fas fa-heart' : 'far fa-heart';
                        
                        // تحديد حالة المخزون
                        let stockText = product.stock > 0 ? 
                            `${product.stock} متبقي` : 
                            'نفذ من المخزون';
                        let stockClass = product.stock > 0 ? 'stock-available' : 'stock-out';
                        
                        productsHtml += `
                        <div class="product-card" data-product-id="${product.id}" data-category="${product.category_id}">
                            <div class="product-img-container position-relative">
                                <img src="${product.image}" 
                                     alt="${product.name}" 
                                     class="product-img"
                                     onclick="showProductDetails(${product.id})">
                                ${product.featured ? '<span class="featured-badge">مميز</span>' : ''}
                                ${product.new_product ? '<span class="new-badge">جديد</span>' : ''}
                            </div>
                            <div class="product-info p-3">
                                <h3 class="product-title mb-2">${product.name}</h3>
                                <p class="product-category text-muted mb-2">
                                    <i class="fas fa-tag me-1"></i>${product.category_name}
                                </p>
                                <div class="price-rating-container d-flex justify-content-between align-items-center mb-2">
                                    <div class="product-price">
                                        <span class="current-price fw-bold text-danger">${product.selling_price} ر.س</span>
                                        ${oldPriceHtml}
                                    </div>
                                    <div class="rating">${ratingStars}</div>
                                </div>
                                ${product.description ? 
                                    `<p class="product-description text-muted mb-2">
                                        ${product.description.substring(0, 60)}...
                                    </p>` : ''
                                }
                            </div>
                            <div class="stock-status p-2">
                                <span class="stock-badge ${stockClass}">
                                    <i class="fas ${product.stock > 0 ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
                                    ${stockText}
                                </span>
                            </div>
                            <div class="product-actions d-flex p-3">
                                <button class="add-to-cart-btn flex-fill" 
                                        onclick="addToCart(${product.id}, 1, this)" 
                                        data-product-id="${product.id}"
                                        ${product.stock <= 0 ? 'disabled' : ''}>
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <span class="btn-text">${product.stock > 0 ? 'أضف للسلة' : 'نفذ من المخزون'}</span>
                                </button>
                                <button class="favorite-btn ${favoriteClass}" 
                                        onclick="toggleFavorite(${product.id}, this)" 
                                        data-product-id="${product.id}">
                                    <i class="${favoriteIcon}"></i>
                                </button>
                            </div>
                        </div>`;
                    });
                    
                    // إضافة زر عرض المزيد إذا كان هناك منتجات أكثر من 10
                    if (response.total_products > response.limit) {
                        productsHtml += `
                            <div class="view-more-container text-center mt-5" style="grid-column: 1 / -1;">
                                <a href="category-products.php?id=${categoryId}" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-eye me-2"></i>
                                    عرض جميع منتجات ${categoryName} (${response.total_products} منتج)
                                </a>
                            </div>
                        `;
                    }
                } else {
                    productsHtml = `
                        <div class="no-products text-center py-5" style="grid-column: 1 / -1;">
                            <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                            <h4 class="text-muted">لا توجد منتجات في هذه الفئة</h4>
                            <p class="text-muted mb-4">سيتم إضافة منتجات قريباً</p>
                            <button class="btn btn-outline-primary" onclick="closeCategoryProducts()">
                                <i class="fas fa-arrow-right me-2"></i>العودة للفئات
                            </button>
                        </div>
                    `;
                }
                
                productsGrid.innerHTML = productsHtml;
                
                // تمرير للأسفل لرؤية المنتجات
                setTimeout(() => {
                    productsSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
                
            } else {
                productsGrid.innerHTML = `
                    <div class="alert alert-danger text-center py-4" style="grid-column: 1 / -1;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${response.message || 'حدث خطأ في تحميل المنتجات'}
                        <button class="btn btn-sm btn-outline-danger mt-2" onclick="closeCategoryProducts()">
                            إغلاق
                        </button>
                    </div>
                `;
            }
        },
        error: function(xhr, status, error) {
            console.error('خطأ AJAX:', error);
            productsGrid.innerHTML = `
                <div class="alert alert-danger text-center py-4" style="grid-column: 1 / -1;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    حدث خطأ في الاتصال بالخادم
                    <br><small>${error}</small>
                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="closeCategoryProducts()">
                        إغلاق
                    </button>
                </div>
            `;
        }
    });
}

// دالة لإغلاق قسم منتجات الفئة
function closeCategoryProducts() {
    // إخفاء قسم منتجات الفئة
    document.getElementById('category-products-section').style.display = 'none';
    
    // مسح المحتوى
    document.getElementById('category-products-grid').innerHTML = '';
    document.getElementById('category-products-title').textContent = '';
    
    // الرجوع للأعلى
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// دالة لإنشاء HTML لبطاقة المنتج (نسخة JavaScript)
function generateProductCardHTML(product) {
    // إنشاء نجوم التقييم
    let ratingStars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= product.rating) {
            ratingStars += '<i class="fas fa-star"></i>';
        } else {
            ratingStars += '<i class="far fa-star"></i>';
        }
    }
    
    // تحضير السعر القديم
    let oldPriceHtml = '';
    if (product.old_price && product.old_price > 0 && product.old_price > product.selling_price) {
        oldPriceHtml = `<small class="text-muted text-decoration-line-through">${product.old_price} ر.س</small>`;
    }
    
    // التحقق من المفضلة
    let favoriteClass = product.is_favorite ? 'active' : '';
    
    return `
    <div class="product-card" data-category="${product.category_id}">
        <div class="product-image-container">
            <img src="${product.image}" 
                 alt="${product.name}" 
                 class="product-img"
                 onclick="showProductDetails(${product.id})">
            ${product.featured ? '<span class="featured-badge">مميز</span>' : ''}
            ${product.new_product ? '<span class="new-badge">جديد</span>' : ''}
        </div>
        <div class="product-info">
            <h3 class="product-title" onclick="showProductDetails(${product.id})">${product.name}</h3>
            <p class="product-category">${product.category_name}</p>
            <div class="price-rating-container">
                <div class="product-price">
                    <span class="current-price">${product.selling_price} ر.س</span>
                    ${oldPriceHtml}
                </div>
                <div class="rating">${ratingStars}</div>
            </div>
            <p class="product-description">${product.description.substring(0, 60)}...</p>
        </div>
        <span class="stock-badge">${product.stock} متبقي</span>
        <div class="product-actions">
            <button class="add-to-cart-btn" onclick="addToCart(${product.id}, 1, this)" data-product-id="${product.id}">
                <i class="fas fa-shopping-cart"></i>
                <span class="btn-text">أضف للسلة</span>
            </button>
            <button class="favorite-btn ${favoriteClass}" onclick="toggleFavorite(${product.id}, this)" data-product-id="${product.id}">
                <i class="fas fa-heart"></i>
            </button>
            <button class="quick-view-btn" onclick="showProductDetails(${product.id})" data-product-id="${product.id}">
                <i class="fas fa-eye"></i>
                <span class="btn-text">عرض سريع</span>
            </button>
        </div>
    </div>`;
}


// دالة لإضافة منتج إلى السلة
function addToCart(productId, quantity, button) {
    if (button) {
        // إضافة تأثير مؤقت
        $(button).addClass('loading');
        $(button).prop('disabled', true);
    }
    
    $.ajax({
        url: 'ajax/add_to_cart.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                // تحديث عدد العناصر في السلة
                updateCartCount();
                
                // عرض رسالة نجاح
                showAlert('تمت إضافة المنتج إلى السلة بنجاح!', 'success');
                
                // تأثير إضافي على الزر
                if (button) {
                    $(button).removeClass('loading').addClass('success');
                    setTimeout(() => {
                        $(button).removeClass('success').prop('disabled', false);
                    }, 1000);
                }
            } else {
                showAlert(response.message || 'حدث خطأ', 'error');
                if (button) {
                    $(button).removeClass('loading').prop('disabled', false);
                }
            }
        },
        error: function() {
            showAlert('حدث خطأ أثناء إضافة المنتج إلى السلة', 'error');
            if (button) {
                $(button).removeClass('loading').prop('disabled', false);
            }
        }
    });
}

// دالة للتبديل بين المفضلة
function toggleFavorite(productId, button) {
    const isActive = $(button).hasClass('active');
    const action = isActive ? 'remove' : 'add';
    
    $.ajax({
        url: 'ajax/toggle_favorite.php',
        method: 'POST',
        data: {
            product_id: productId,
            action: action
        },
        success: function(response) {
            if (response.success) {
                $(button).toggleClass('active');
                
                // تأثير القلب
                $(button).addClass('heartbeat');
                setTimeout(() => $(button).removeClass('heartbeat'), 300);
                
                // عرض رسالة
                const message = isActive ? 'تمت إزالة المنتج من المفضلة' : 'تمت إضافة المنتج إلى المفضلة';
                showAlert(message, 'success');
                
                // تحديث عدد المفضلة
                updateFavoritesCount();
            } else {
                showAlert(response.message || 'حدث خطأ', 'error');
            }
        },
        error: function() {
            showAlert('حدث خطأ أثناء تحديث المفضلة', 'error');
        }
    });
}
// دالة لعرض تفاصيل المنتج
function showProductDetails(productId) {
    $.ajax({
        url: 'ajax/get_product_details.php',
        method: 'GET',
        data: { product_id: productId },
        success: function(response) {
            if (response.success) {
                const product = response.product;
                
                // تعبئة البيانات في النافذة المنبثقة
                $('#productDetailModal .product-image').attr('src', product.image);
                $('#productDetailModal .product-name').text(product.name);
                $('#productDetailModal .product-category').text(product.category_name);
                $('#productDetailModal .product-price').text(product.selling_price + ' ر.س');
                
                if (product.old_price) {
                    $('#productDetailModal .product-old-price').text(product.old_price + ' ر.س').show();
                } else {
                    $('#productDetailModal .product-old-price').hide();
                }
                
                // تحديث التقييم
                let ratingStars = '';
                for (let i = 1; i <= 5; i++) {
                    ratingStars += `<i class="${i <= product.rating ? 'fas' : 'far'} fa-star"></i>`;
                }
                $('#productDetailModal .product-rating').html(ratingStars);
                
                $('#productDetailModal .product-description').text(product.description);
                $('#productDetailModal .product-stock').text(product.stock);
                
                // إعداد أزرار الإضافة
                $('#add-to-cart-detail').data('product-id', productId);
                $('#add-to-favorites-detail').data('product-id', productId)
                    .toggleClass('active', response.is_favorite);
                
                // فتح النافذة المنبثقة
                $('#productDetailModal').modal('show');
            } else {
                showAlert(response.message || 'حدث خطأ في تحميل المنتج', 'error');
            }
        },
        error: function() {
            showAlert('حدث خطأ في الاتصال بالخادم', 'error');
        }
    });
}
// دالة لتحديث عدد العناصر في السلة
function updateCartCount() {
    $.ajax({
        url: 'ajax/get_cart_count.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('.cart-count, .notification-badge').text(response.count);
            }
        }
    });
}

// دالة لتحديث عدد المفضلة
function updateFavoritesCount() {
    $.ajax({
        url: 'ajax/get_favorites_count.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('.favorites-count').text(response.count);
            }
        }
    });
}

// دالة لعرض رسائل التنبيه
function showAlert(message, type = 'info') {
    // إزالة أي رسالة سابقة
    $('.custom-alert').remove();
    
    // إنشاء رسالة جديدة
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    const alertHtml = `
        <div class="custom-alert alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // إخفاء الرسالة تلقائياً بعد 3 ثواني
    setTimeout(() => {
        $('.custom-alert').alert('close');
    }, 3000);
}

// دالة لإعادة إرفاق الأحداث للمنتجات الجديدة
function attachProductEvents() {
    // الكمية في نافذة التفاصيل
    $('.quantity-btn').off('click').on('click', function() {
        const input = $(this).siblings('.quantity-input');
        let value = parseInt(input.val());
        
        if ($(this).hasClass('plus')) {
            value++;
        } else if ($(this).hasClass('minus') && value > 1) {
            value--;
        }
        
        input.val(value);
    });
}

// فلتر الفئات
$(document).ready(function() {
    $('.category-filter-btn').click(function() {
        const categoryId = $(this).data('category');
        
        // تحديث الحالة النشطة للأزرار
        $('.category-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        if (categoryId === 'all') {
            // إخفاء قسم المنتجات
            $('#category-products-section').hide();
        } else {
            // تحميل منتجات الفئة المحددة
            const categoryName = $(this).text();
            loadCategoryProducts(categoryId, categoryName);
        }
    });
    
    // تحديث الأعداد عند التحميل
    updateCartCount();
    updateFavoritesCount();
});
</script>
    <script>

        // في ملف main.js أو في <script>
$(document).ready(function() {
    // تحديث عدد العناصر في السلة والمفضلة عند التحميل
    updateCartCount();
    updateFavoritesCount();
    
    // فتح Modal السلة
    $('#cartModal').on('show.bs.modal', function() {
        loadCartModal();
    });
    
    // فتح Modal المفضلة
    $('#favoritesModal').on('show.bs.modal', function() {
        loadFavoritesModal();
    });
    
    // تحديث السلة عند إغلاق الـ Modal
    $('#cartModal, #favoritesModal').on('hidden.bs.modal', function() {
        updateCartCount();
        updateFavoritesCount();
    });
});

// دالة لتحميل محتوى Modal السلة
function loadCartModal() {
    $.ajax({
        url: 'ajax/get_cart_modal.php',
        method: 'GET',
        beforeSend: function() {
            $('#cartModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="mt-3">جاري تحميل سلة التسوق...</p>
                </div>
            `);
        },
        success: function(response) {
            if (response.success) {
                if (response.items.length > 0) {
                    let itemsHtml = '';
                    
                    response.items.forEach(function(item) {
                        itemsHtml += `
                            <div class="cart-item d-flex align-items-center" data-item-id="${item.id}">
                                <img src="${item.image}" alt="${item.name}" class="cart-item-img me-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${item.name}</h6>
                                    ${item.options ? `<p class="product-options mb-1">${item.options}</p>` : ''}
                                    <div class="quantity-controls d-flex align-items-center">
                                        <button class="quantity-btn minus" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                        <input type="text" class="quantity-input" value="${item.quantity}" readonly>
                                        <button class="quantity-btn plus" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                        <span class="ms-3 text-danger fw-bold">${item.total_price} ر.س</span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger delete-btn ms-2" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });
                    
                    $('#cartModalBody').html(`<div class="cart-items">${itemsHtml}</div>`);
                    $('#cartModalFooter').show();
                    
                    // تحديث المجاميع
                    $('#cart-total-price').text(response.summary.total_price + ' ر.س');
                    $('#cart-tax').text(response.summary.tax + ' ر.س');
                    $('#cart-grand-total').text(response.summary.grand_total + ' ر.س');
                } else {
                    $('#cartModalBody').html(`
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>سلة التسوق فارغة</h4>
                            <p class="text-muted">أضف بعض المنتجات لتظهر هنا</p>
                            <button class="btn btn-primary mt-3" data-bs-dismiss="modal">
                                <i class="fas fa-store me-2"></i>متابعة التسوق
                            </button>
                        </div>
                    `);
                    $('#cartModalFooter').hide();
                }
            } else {
                $('#cartModalBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${response.message || 'حدث خطأ في تحميل السلة'}
                    </div>
                `);
            }
        },
        error: function() {
            $('#cartModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    حدث خطأ في الاتصال بالخادم
                </div>
            `);
        }
    });
}

// دالة لتحميل محتوى Modal المفضلة
function loadFavoritesModal() {
    $.ajax({
        url: 'ajax/get_favorites_modal.php',
        method: 'GET',
        beforeSend: function() {
            $('#favoritesModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="mt-3">جاري تحميل قائمة المفضلة...</p>
                </div>
            `);
        },
        success: function(response) {
            if (response.success) {
                if (response.items.length > 0) {
                    let itemsHtml = '';
                    
                    response.items.forEach(function(item) {
                        itemsHtml += `
                            <div class="favorite-item d-flex align-items-center" data-item-id="${item.id}">
                                <img src="${item.image}" alt="${item.name}" class="favorite-item-img me-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${item.name}</h6>
                                    <p class="text-muted mb-1">${item.category}</p>
                                    <div class="d-flex align-items-center">
                                        <span class="text-danger fw-bold me-3">${item.price} ر.س</span>
                                        <span class="badge ${item.in_stock ? 'bg-success' : 'bg-danger'}">
                                            ${item.in_stock ? 'متوفر' : 'غير متوفر'}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    ${item.in_stock ? 
                                        `<button class="btn btn-sm btn-outline-danger me-2" onclick="addToCart(${item.product_id}, 1)">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>` : ''
                                    }
                                    <button class="btn btn-sm btn-outline-secondary delete-btn" onclick="removeFromWishlist(${item.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    
                    $('#favoritesModalBody').html(`<div class="favorite-items">${itemsHtml}</div>`);
                    $('#favoritesModalFooter').show();
                } else {
                    $('#favoritesModalBody').html(`
                        <div class="empty-favorites">
                            <i class="fas fa-heart"></i>
                            <h4>قائمة المفضلة فارغة</h4>
                            <p class="text-muted">أضف بعض المنتجات لتظهر هنا</p>
                            <button class="btn btn-primary mt-3" data-bs-dismiss="modal">
                                <i class="fas fa-store me-2"></i>متابعة التسوق
                            </button>
                        </div>
                    `);
                    $('#favoritesModalFooter').hide();
                }
            } else {
                $('#favoritesModalBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${response.message || 'حدث خطأ في تحميل المفضلة'}
                    </div>
                `);
            }
        },
        error: function() {
            $('#favoritesModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    حدث خطأ في الاتصال بالخادم
                </div>
            `);
        }
    });
}

// دالة لتحديث كمية المنتج في السلة
function updateCartQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) {
        removeFromCart(cartItemId);
        return;
    }
    
    $.ajax({
        url: 'ajax/update_cart_quantity.php',
        method: 'POST',
        data: {
            cart_item_id: cartItemId,
            quantity: newQuantity
        },
        success: function(response) {
            if (response.success) {
                // تحديث السلة مباشرة
                loadCartModal();
                updateCartCount();
                showAlert('تم تحديث الكمية بنجاح', 'success');
            } else {
                showAlert(response.message || 'حدث خطأ', 'error');
            }
        }
    });
}

// دالة لحذف منتج من السلة
function removeFromCart(cartItemId) {
    if (!confirm('هل تريد حذف هذا المنتج من السلة؟')) return;
    
    $.ajax({
        url: 'ajax/remove_from_cart.php',
        method: 'POST',
        data: { cart_item_id: cartItemId },
        success: function(response) {
            if (response.success) {
                loadCartModal();
                updateCartCount();
                showAlert('تم حذف المنتج من السلة', 'success');
            } else {
                showAlert(response.message || 'حدث خطأ', 'error');
            }
        }
    });
}

// دالة لحذف منتج من المفضلة
function removeFromWishlist(wishlistItemId) {
    if (!confirm('هل تريد حذف هذا المنتج من المفضلة؟')) return;
    
    $.ajax({
        url: 'ajax/remove_from_wishlist.php',
        method: 'POST',
        data: { wishlist_item_id: wishlistItemId },
        success: function(response) {
            if (response.success) {
                loadFavoritesModal();
                updateFavoritesCount();
                showAlert('تم حذف المنتج من المفضلة', 'success');
            } else {
                showAlert(response.message || 'حدث خطأ', 'error');
            }
        }
    });
}

// دالة لتحديث عدد عناصر السلة
function updateCartCount() {
    $.ajax({
        url: 'ajax/get_cart_count.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('.cart-count, #cart-badge').text(response.count);
            }
        }
    });
}

// دالة لتحديث عدد عناصر المفضلة
function updateFavoritesCount() {
    $.ajax({
        url: 'ajax/get_favorites_count.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('.favorites-count, #favorites-badge').text(response.count);
            }
        }
    });
}
        $(document).ready(function() {
            // تحديث عدد عناصر السلة
            function updateCartCount() {
                $.ajax({
                    url: 'ajax/get_cart_count.php',
                    method: 'GET',
                    success: function(response) {
                        $('#cart-badge').text(response.count);
                    }
                });
            }
            
            // تحديث عند التحميل
            updateCartCount();
            
            // فلتر المنتجات حسب الفئة
            $('.category-filter-btn').click(function() {
                const categoryId = $(this).data('category');
                
                // تحديث الحالة النشطة للأزرار
                $('.category-filter-btn').removeClass('active');
                $(this).addClass('active');
                
                // إخفاء جميع المنتجات أولاً
                $('.product-card').hide();
                
                if (categoryId === 'all') {
                    // إظهار جميع المنتجات
                    $('.product-card').show();
                } else {
                    // إظهار المنتجات الخاصة بالفئة المحددة
                    $('.product-card[data-category="' + categoryId + '"]').show();
                }
            });
            
            // إدارة الكمية
            $('.quantity-btn').click(function() {
                const input = $(this).siblings('.quantity-input');
                let value = parseInt(input.val());
                
                if ($(this).text() === '+') {
                    value++;
                } else if ($(this).text() === '-' && value > 1) {
                    value--;
                }
                
                input.val(value);
            });
            
            // إضافة منتج إلى السلة
            $(document).on('click', '.add-to-cart-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = $(this).data('product-id');
                
                $.ajax({
                    url: 'ajax/add_to_cart.php',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        quantity: 1
                    },
                    success: function(response) {
                        if (response.success) {
                            // تحديث عدد العناصر في السلة
                            $('#cart-badge').text(response.cart_count);
                            
                            // عرض رسالة نجاح
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });
            
            // عرض تفاصيل المنتج
            $(document).on('click', '.quick-view-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = $(this).data('product-id');
                
                $.ajax({
                    url: 'ajax/get_product_details.php',
                    method: 'GET',
                    data: { product_id: productId },
                    success: function(response) {
                        if (response.success) {
                            const product = response.product;
                            
                            // تعبئة البيانات في النافذة المنبثقة
                            $('#product-detail-img').attr('src', product.image || 'img/default-product.jpg');
                            $('#product-detail-name').text(product.name);
                            $('#product-detail-category').text('الفئة: ' + product.category_name);
                            $('#product-detail-price').text(product.selling_price + ' ر.س');
                            
                            if (product.old_price) {
                                $('#product-detail-old-price').text(product.old_price + ' ر.س').show();
                            } else {
                                $('#product-detail-old-price').hide();
                            }
                            
                            // إنشاء نجوم التقييم
                            let ratingStars = '';
                            const rating = product.rating || 0;
                            for (let i = 1; i <= 5; i++) {
                                ratingStars += `<i class="${i <= rating ? 'fas' : 'far'} fa-star"></i>`;
                            }
                            $('#product-detail-rating').php(ratingStars);
                            
                            $('#product-detail-description').text(product.description || 'لا يوجد وصف.');
                            $('#product-detail-stock').text(product.stock || 0);
                            $('#product-detail-quantity').val(1);
                            
                            // إعداد أزرار الإضافة
                            $('#add-to-cart-detail').data('product-id', productId);
                            $('#add-to-favorites-detail').data('product-id', productId);
                            
                            // فتح النافذة المنبثقة
                            $('#productDetailModal').modal('show');
                        }
                    }
                });
            });
            
            // إضافة منتج إلى السلة من نافذة التفاصيل
            $('#add-to-cart-detail').click(function() {
                const productId = $(this).data('product-id');
                const quantity = parseInt($('#product-detail-quantity').val());
                
                $.ajax({
                    url: 'ajax/add_to_cart.php',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        quantity: quantity
                    },
                    success: function(response) {
                        if (response.success) {
                            // تحديث عدد العناصر في السلة
                            $('#cart-badge').text(response.cart_count);
                            
                            // إغلاق النافذة المنبثقة وعرض رسالة نجاح
                            $('#productDetailModal').modal('hide');
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });
            
            // إضافة منتج إلى المفضلة
            $(document).on('click', '.favorite-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = $(this).data('product-id');
                const isActive = $(this).hasClass('active');
                
                $.ajax({
                    url: 'ajax/toggle_favorite.php',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        action: isActive ? 'remove' : 'add'
                    },
                    success: function(response) {
                        if (response.success) {
                            // تبديل حالة الزر
                            $(this).toggleClass('active');
                            alert(response.message);
                        }
                    }.bind(this)
                });
            });
        });
    </script>
</body>
</html>