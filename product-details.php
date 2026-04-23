<?php
// product-details.php
require_once 'config/database.php';
require_once 'functions.php';
session_start();

// التحقق من معرف المنتج
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}


$productId = intval($_GET['id']);
$userId = getCurrentUserId();

// جلب تفاصيل المنتج
$productQuery = "SELECT p.*, c.name as category_name, b.name as brand_name,
                 (SELECT image_path FROM product_images 
                  WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          LEFT JOIN brands b ON p.brand_id = b.id
          WHERE p.id = ? AND p.status = 'active'";
          
$stmt = mysqli_prepare($conn, $productQuery);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$productResult = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($productResult);

// إذا لم يوجد المنتج
if (!$product) {
    header('Location: index.php');
    exit;
}

// جلب جميع صور المنتج
$imagesQuery = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC";
$stmt = mysqli_prepare($conn, $imagesQuery);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$imagesResult = mysqli_stmt_get_result($stmt);
$productImages = [];
while ($image = mysqli_fetch_assoc($imagesResult)) {
    $productImages[] = $image['image_path'];
}

// إذا لم يكن هناك صور، استخدم الصورة الافتراضية
if (empty($productImages)) {
    $productImages[] = 'img/default-product.jpg';
}

// جلب الألوان المتاحة
$colorsQuery = "SELECT color_name, color_code FROM product_colors WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $colorsQuery);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$colorsResult = mysqli_stmt_get_result($stmt);
$colors = [];
while ($color = mysqli_fetch_assoc($colorsResult)) {
    $colors[] = $color;
}

// جلب الأحجام المتاحة
$sizesQuery = "SELECT size FROM product_sizes WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $sizesQuery);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$sizesResult = mysqli_stmt_get_result($stmt);
$sizes = [];
while ($size = mysqli_fetch_assoc($sizesResult)) {
    $sizes[] = $size;
}

// جلب عدد المنتجات في السلة للمستخدم الحالي
$cartCountQuery = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $cartCountQuery);
mysqli_stmt_bind_param($stmt, 's', $userId);
mysqli_stmt_execute($stmt);
$cartResult = mysqli_stmt_get_result($stmt);
$cartRow = mysqli_fetch_assoc($cartResult);
$cartCount = $cartRow['count'] ?? 0;

// التحقق مما إذا كان المنتج في المفضلة
$isFavorite = false;
$wishlistQuery = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
$stmt = mysqli_prepare($conn, $wishlistQuery);
mysqli_stmt_bind_param($stmt, 'si', $userId, $productId);
mysqli_stmt_execute($stmt);
$wishlistResult = mysqli_stmt_get_result($stmt);
$isFavorite = mysqli_num_rows($wishlistResult) > 0;

// جلب المنتجات ذات الصلة (من نفس الفئة)
$relatedQuery = "SELECT p.*, c.name as category_name,
                        (SELECT image_path FROM product_images 
                         WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
                 FROM products p 
                 JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = ? 
                 AND p.id != ? 
                 AND p.status = 'active'
                 ORDER BY RAND() 
                 LIMIT 4";
$stmt = mysqli_prepare($conn, $relatedQuery);
mysqli_stmt_bind_param($stmt, 'ii', $product['category_id'], $productId);
mysqli_stmt_execute($stmt);
$relatedResult = mysqli_stmt_get_result($stmt);

// جلب تقييمات المنتج
$reviewsQuery = "SELECT r.*, u.username, u.profile_image 
                 FROM reviews r 
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE r.product_id = ? 
                 ORDER BY r.created_at DESC 
                 LIMIT 10";
$stmt = mysqli_prepare($conn, $reviewsQuery);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$reviewsResult = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | Be Pretty</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* أنماط CSS الحالية تبقى كما هي */
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff3366;
            --accent-color: #ff3366;
            --dark-color: #2c2c54;
            --light-color: #f7f7f7;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding-bottom: 70px;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #a56cc1);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }
        
        .icon-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .icon-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
            color: white;
        }
        
        .header-icons {
            display: flex;
            gap: 10px;
        }
        
        .main-content {
            margin-top: 80px;
        }
        
        /* أنماط العرض على الهاتف */
        .product-gallery-mobile {
            display: none;
        }
        
        @media (max-width: 768px) {
            .product-gallery-desktop {
                display: none;
            }
            .product-gallery-mobile {
                display: block;
            }
        }
        
        /* أنماط إضافية للصور المصغرة */
        .thumbnail-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .thumbnail-img:hover,
        .thumbnail-img.active {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }
        
        /* أزرار الألوان */
        .color-option {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin: 0 5px 5px 0;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .color-option:hover,
        .color-option.active {
            border-color: #333;
            transform: scale(1.1);
        }
        
        /* أزرار الأحجام */
        .size-option {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            margin: 0 5px 5px 0;
            cursor: pointer;
            background: white;
            transition: all 0.3s;
        }
        
        .size-option:hover,
        .size-option.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* باقي الأنماط تبقى كما هي */
        .product-detail-img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .product-detail-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .product-detail-old-price {
            font-size: 1.2rem;
            color: #777;
            text-decoration: line-through;
            margin-right: 10px;
        }
        
        .rating {
            color: #ffc107;
            font-size: 1rem;
        }
        
        .detail-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: none;
            margin: 0 10px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .add-to-cart-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
            cursor: pointer;
        }
        
        .add-to-cart-btn:hover {
            background: #e02e5a;
            transform: translateY(-2px);
        }
        
        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #777;
            font-size: 0.8rem;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item i {
            font-size: 1.2rem;
            margin-bottom: 3px;
        }
        
        .bottom-tab-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: 5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .comment-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .comment-item:last-child {
            border-bottom: none;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .related-product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }
        
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            color: inherit;
        }
        
        .related-product-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .related-product-info {
            padding: 12px;
        }
        
        .stock-badge {
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .out-of-stock-badge {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .category-badge {
            background: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }
        
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            border: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .share-btn.facebook {
            background: #3b5998;
        }
        
        .share-btn.twitter {
            background: #1da1f2;
        }
        
        .share-btn.whatsapp {
            background: #25d366;
        }
        
        .share-btn.instagram {
            background: #e4405f;
        }
        
        .share-btn:hover {
            transform: scale(1.1);
        }
        
        .coupon-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .coupon-input {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 1rem;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .coupon-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
        }
        
        .discount-badge {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-right: 10px;
        }
        
        @media (min-width: 768px) {
            .product-detail-img {
                max-height: 500px;
            }
            
            .add-to-cart-btn {
                width: auto;
            }
        }
        
        @media (min-width: 992px) {
            .product-detail-img {
                max-height: 600px;
            }
        }
        
        /* تأثيرات للزر المفضل */
        .icon-btn.favorite-btn.active {
            background: #ff4757;
            color: white;
        }
        
        /* أنماط الـ tabs */
        .product-tabs {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .product-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .product-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background: none;
        }
        
        .product-tabs .nav-link:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-top">
            <a href="javascript:history.back()" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
            <h5 class="mb-0 fw-bold">تفاصيل المنتج</h5>
            <div class="header-icons">
                <a href="wishlist.php" class="icon-btn favorite-btn <?= $isFavorite ? 'active' : '' ?>" id="header-favorite-btn">
                    <i class="fas fa-heart"></i>
                </a>
                <a href="#" class="icon-btn" id="header-cart-btn" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if($cartCount > 0): ?>
                        <span class="notification-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <div class="main-content container py-4">
        <div class="row">
            <!-- معرض صور المنتج للشاشات الكبيرة -->
            <div class="col-md-6 mb-4 product-gallery-desktop">
                <div class="mb-3">
                    <img id="product-main-image" src="<?= htmlspecialchars($productImages[0]) ?>" 
                         class="product-detail-img" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <?php if(count($productImages) > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach($productImages as $index => $image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" 
                             class="thumbnail-img <?= $index == 0 ? 'active' : '' ?>" 
                             alt="صورة <?= $index + 1 ?>"
                             onclick="changeMainImage('<?= htmlspecialchars($image) ?>', this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- معرض صور المنتج للهواتف -->
            <div class="col-md-6 mb-4 product-gallery-mobile">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach($productImages as $index => $image): ?>
                            <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($image) ?>" 
                                     class="d-block w-100 product-detail-img" 
                                     alt="صورة <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if(count($productImages) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">السابق</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">التالي</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- تفاصيل المنتج -->
            <div class="col-md-6 mb-4">
                <div class="detail-card">
                    <!-- الكمية المتبقية والفئة -->
                    <div class="d-flex justify-content-between mb-3">
                        <?php if($product['stock'] > 0): ?>
                            <span class="stock-badge" id="product-stock-badge">
                                <?= $product['stock'] > 10 ? 'متوفر' : 'بقي ' . $product['stock'] . ' قطعة فقط' ?>
                            </span>
                        <?php else: ?>
                            <span class="out-of-stock-badge">غير متوفر</span>
                        <?php endif; ?>
                        <span class="category-badge" id="product-category-badge"><?= htmlspecialchars($product['category_name']) ?></span>
                    </div>
                    
                    <h1 id="product-name" class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <!-- الأسعار -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="product-detail-price" id="product-price">
                            <?= number_format($product['selling_price'], 2) ?> ر.س
                        </span>
                        <?php if($product['old_price'] && $product['old_price'] > $product['selling_price']): ?>
                            <span class="product-detail-old-price" id="product-old-price">
                                <?= number_format($product['old_price'], 2) ?> ر.س
                            </span>
                            <?php 
                                $discountPercent = round((($product['old_price'] - $product['selling_price']) / $product['old_price']) * 100);
                            ?>
                            <span class="discount-badge">وفر <?= $discountPercent ?>%</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- التقييم -->
                    <div class="rating mb-3">
                        <?= getRatingStars($product['rating'] ?? 0) ?>
                        <span class="text-muted ms-2">(<?= number_format($product['rating'] ?? 0, 1) ?>)</span>
                    </div>
                    
                    <!-- الوصف -->
                    <p id="product-description" class="text-muted mb-4"><?= htmlspecialchars($product['description']) ?></p>
                    
                    <!-- الألوان المتاحة -->
                    <?php if(!empty($colors)): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">الألوان المتاحة:</h6>
                        <div class="d-flex flex-wrap">
                            <?php foreach($colors as $color): ?>
                                <div class="color-option" 
                                     style="background-color: <?= htmlspecialchars($color['color_code']) ?>"
                                     title="<?= htmlspecialchars($color['color_name']) ?>"
                                     onclick="selectColor('<?= htmlspecialchars($color['color_code']) ?>', this)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- الأحجام المتاحة -->
                    <?php if(!empty($sizes)): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">الأحجام المتاحة:</h6>
                        <div class="d-flex flex-wrap">
                            <?php foreach($sizes as $size): ?>
                                <button class="size-option" onclick="selectSize('<?= htmlspecialchars($size['size']) ?>', this)">
                                    <?= htmlspecialchars($size['size']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- أزرار المشاركة -->
                    <div class="share-buttons">
                        <span class="me-2 fw-bold">مشاركة:</span>
                        <button class="share-btn facebook" onclick="shareProduct('facebook')">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="share-btn twitter" onclick="shareProduct('twitter')">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="share-btn whatsapp" onclick="shareProduct('whatsapp')">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                        <button class="share-btn instagram" onclick="shareProduct('instagram')">
                            <i class="fab fa-instagram"></i>
                        </button>
                    </div>
                    
                    <!-- الكمية -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">الكمية:</h6>
                        <div class="quantity-controls">
                            <button class="quantity-btn minus">-</button>
                            <input type="text" class="quantity-input" value="1" readonly id="product-quantity">
                            <button class="quantity-btn plus">+</button>
                        </div>
                    </div>
                    
                    <!-- كوبون الخصم -->
                    <div class="coupon-section">
                        <h6 class="fw-bold mb-3">كوبون الخصم</h6>
                        <input type="text" class="coupon-input" id="coupon-code" placeholder="أدخل كود الخصم">
                        <button class="coupon-btn" id="apply-coupon">تطبيق الخصم</button>
                        <div id="coupon-message" class="mt-2"></div>
                    </div>
                    
                    <!-- أزرار الإجراءات -->
                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-danger add-to-cart-btn flex-fill" 
                                id="add-to-cart-main"
                                onclick="addToCart(<?= $productId ?>, 1, this)"
                                <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-shopping-cart me-2"></i>
                            <?= $product['stock'] > 0 ? 'أضف إلى السلة' : 'غير متوفر' ?>
                        </button>
                        <button class="btn btn-outline-danger flex-fill" 
                                id="add-to-favorite-main"
                                onclick="toggleFavorite(<?= $productId ?>, this)"
                                <?= $isFavorite ? 'style="display:none;"' : '' ?>>
                            <i class="fas fa-heart me-2"></i>إضافة إلى المفضلة
                        </button>
                        <button class="btn btn-outline-danger flex-fill" 
                                id="remove-from-favorite-main"
                                onclick="toggleFavorite(<?= $productId ?>, this)"
                                <?= !$isFavorite ? 'style="display:none;"' : '' ?>>
                            <i class="fas fa-heart me-2"></i>إزالة من المفضلة
                        </button>
                    </div>
                </div>
                
                <!-- معلومات إضافية -->
                <div class="detail-card">
                    <ul class="nav nav-tabs product-tabs" id="productInfoTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#details">معلومات المنتج</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#specifications">المواصفات</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="productInfoTabsContent">
                        <div class="tab-pane fade show active" id="details">
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <strong>الفئة:</strong> <span id="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
                                </div>
                                <?php if($product['brand_name']): ?>
                                <div class="col-6 mb-2">
                                    <strong>العلامة التجارية:</strong> <span id="product-brand"><?= htmlspecialchars($product['brand_name']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($product['barcode']): ?>
                                <div class="col-6 mb-2">
                                    <strong>الباركود:</strong> <span id="product-barcode"><?= htmlspecialchars($product['barcode']) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="col-12 mb-2">
                                    <strong>الوصف التفصيلي:</strong> 
                                    <p class="mt-2" id="product-full-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="specifications">
                            <div class="row">
                                <?php if($product['quantity']): ?>
                                <div class="col-6 mb-2">
                                    <strong>الكمية:</strong> <span><?= $product['quantity'] ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($product['tax_rate']): ?>
                                <div class="col-6 mb-2">
                                    <strong>معدل الضريبة:</strong> <span><?= $product['tax_rate'] ?>%</span>
                                </div>
                                <?php endif; ?>
                                <?php if($product['expiry_date']): ?>
                                <div class="col-6 mb-2">
                                    <strong>تاريخ الانتهاء:</strong> <span><?= date('Y/m/d', strtotime($product['expiry_date'])) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="col-6 mb-2">
                                    <strong>تاريخ الإضافة:</strong> <span><?= date('Y/m/d', strtotime($product['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- التقييمات والتعليقات -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">التقييمات والتعليقات</h5>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                    <i class="fas fa-plus me-1"></i>إضافة تقييم
                </button>
            </div>
            
            <div id="comments-container" class="comments-list">
                <?php if(mysqli_num_rows($reviewsResult) > 0): ?>
                    <?php while($review = mysqli_fetch_assoc($reviewsResult)): ?>
                        <div class="comment-item d-flex align-items-start">
                            <img src="<?= $review['profile_image'] ? htmlspecialchars($review['profile_image']) : 'https://via.placeholder.com/45x45?text=ص' ?>" 
                                 class="user-avatar me-3" 
                                 alt="<?= htmlspecialchars($review['username'] ?? 'مستخدم') ?>">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($review['username'] ?? 'مستخدم') ?></h6>
                                    <div class="rating">
                                        <?= getRatingStars($review['rating']) ?>
                                    </div>
                                </div>
                                <p class="small mb-2 text-muted"><?= date('Y/m/d', strtotime($review['created_at'])) ?></p>
                                <p class="mb-0"><?= htmlspecialchars($review['comment']) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد تقييمات بعد. كن أول من يقيم هذا المنتج!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- منتجات قد تعجبك -->
        <?php if(mysqli_num_rows($relatedResult) > 0): ?>
        <div class="detail-card">
            <h5 class="fw-bold mb-3">منتجات قد تعجبك</h5>
            <div class="row g-3">
                <?php while($relatedProduct = mysqli_fetch_assoc($relatedResult)): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <a href="product-details.php?id=<?= $relatedProduct['id'] ?>" class="related-product-card">
                            <img src="<?= htmlspecialchars($relatedProduct['main_image'] ?? 'img/default-product.jpg') ?>" 
                                 class="related-product-img" 
                                 alt="<?= htmlspecialchars($relatedProduct['name']) ?>">
                            <div class="related-product-info">
                                <?php if($relatedProduct['stock'] > 0): ?>
                                    <span class="stock-badge"><?= $relatedProduct['stock'] > 10 ? 'متوفر' : 'بقي ' . $relatedProduct['stock'] . ' قطعة' ?></span>
                                <?php else: ?>
                                    <span class="out-of-stock-badge">غير متوفر</span>
                                <?php endif; ?>
                                <h6 class="fw-bold"><?= htmlspecialchars($relatedProduct['name']) ?></h6>
                                <p class="text-danger fw-bold mb-1">
                                    <?= number_format($relatedProduct['selling_price'], 2) ?> ر.س
                                    <?php if($relatedProduct['old_price'] && $relatedProduct['old_price'] > $relatedProduct['selling_price']): ?>
                                        <small class="text-muted text-decoration-line-through">
                                            <?= number_format($relatedProduct['old_price'], 2) ?> ر.س
                                        </small>
                                    <?php endif; ?>
                                </p>
                                <div class="rating small">
                                    <?= getRatingStars($relatedProduct['rating'] ?? 0) ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- نافذة إضافة تقييم -->
    <div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReviewModalLabel">إضافة تقييم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">التقييم</label>
                        <div class="rating" id="review-rating-select">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                            <span class="ms-2" id="rating-text">0/5</span>
                        </div>
                        <input type="hidden" id="selected-rating" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="review-comment" class="form-label">تعليقك</label>
                        <textarea class="form-control" id="review-comment" rows="4" placeholder="اكتب تعليقك هنا..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="submitReview(<?= $productId ?>)">إضافة التقييم</button>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة منبثقة للسلة -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">سلة التسوق</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cart-modal-content">
                    <!-- سيتم تحميل السلة عبر AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="mt-2">جاري تحميل السلة...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="cart.php" class="btn btn-primary">عرض السلة الكاملة</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">متابعة التسوق</button>
                </div>
            </div>
        </div>
    </div>

    <nav class="bottom-tab-bar">
        <a href="home.php" class="tab-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="categories.php" class="tab-item">
            <i class="fas fa-th-large"></i>
            <span>الفئات</span>
        </a>
        <a href="#" class="tab-item" data-bs-toggle="modal" data-bs-target="#cartModal">
            <i class="fas fa-shopping-cart"></i>
            <span>السلة</span>
            <?php if($cartCount > 0): ?>
                <span class="notification-badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        <a href="order.php" class="tab-item">
            <i class="fas fa-list-alt"></i>
            <span>الطلبات</span>
        </a>
        <a href="profile.php" class="tab-item">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // إدارة أزرار زيادة ونقصان الكمية
            $('.quantity-btn').click(function() {
                const input = $(this).siblings('.quantity-input');
                let value = parseInt(input.val());
                const max = <?= $product['stock'] ?>;
                
                if ($(this).hasClass('plus')) {
                    if (value < max) {
                        value++;
                    } else {
                        alert('لا يمكن تجاوز الكمية المتاحة في المخزون');
                    }
                } else if ($(this).hasClass('minus') && value > 1) {
                    value--;
                }
                
                input.val(value);
            });
            
            // تغيير الصورة الرئيسية
            window.changeMainImage = function(imageSrc, element) {
                $('#product-main-image').attr('src', imageSrc);
                $('.thumbnail-img').removeClass('active');
                $(element).addClass('active');
            };
            
            // تحديد اللون
            window.selectColor = function(colorCode, element) {
                $('.color-option').removeClass('active');
                $(element).addClass('active');
                // يمكنك إضافة منطق لتخزين اللون المحدد
            };
            
            // تحديد الحجم
            window.selectSize = function(size, element) {
                $('.size-option').removeClass('active');
                $(element).addClass('active');
                // يمكنك إضافة منطق لتخزين الحجم المحدد
            };
            
            // تقييم المنتج
            $('#review-rating-select i').hover(function() {
                const rating = $(this).data('rating');
                $('#rating-text').text(rating + '/5');
                
                // تحديث النجوم عند التمرير
                $('#review-rating-select i').each(function(index) {
                    if (index < rating) {
                        $(this).removeClass('far').addClass('fas');
                    } else {
                        $(this).removeClass('fas').addClass('far');
                    }
                });
            }).click(function() {
                const rating = $(this).data('rating');
                $('#selected-rating').val(rating);
            });
            
            // تحميل محتوى السلة عند فتح الـ Modal
            $('#cartModal').on('show.bs.modal', function() {
                loadCartModalContent();
            });
        });
        
        // دالة لإضافة منتج إلى السلة
        function addToCart(productId, quantity, button) {
            if (button) {
                $(button).prop('disabled', true);
                $(button).html('<i class="fas fa-spinner fa-spin me-2"></i>جاري الإضافة...');
            }
            
            const selectedQuantity = $('#product-quantity').val();
            quantity = quantity || selectedQuantity;
            
            $.ajax({
                url: 'ajax/add_to_cart.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        // تحديث عدد عناصر السلة
                        updateCartCount();
                        
                        // عرض رسالة نجاح
                        showAlert('تمت إضافة المنتج إلى السلة بنجاح!', 'success');
                        
                        // إعادة ضبط الزر
                        if (button) {
                            setTimeout(() => {
                                $(button).html('<i class="fas fa-shopping-cart me-2"></i>أضف إلى السلة');
                                $(button).prop('disabled', false);
                            }, 1000);
                        }
                    } else {
                        showAlert(response.message || 'حدث خطأ', 'error');
                        if (button) {
                            $(button).html('<i class="fas fa-shopping-cart me-2"></i>أضف إلى السلة');
                            $(button).prop('disabled', false);
                        }
                    }
                },
                error: function() {
                    showAlert('حدث خطأ أثناء إضافة المنتج إلى السلة', 'error');
                    if (button) {
                        $(button).html('<i class="fas fa-shopping-cart me-2"></i>أضف إلى السلة');
                        $(button).prop('disabled', false);
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
                        // تبديل حالة الأزرار
                        $('#add-to-favorite-main, #remove-from-favorite-main, #header-favorite-btn').toggleClass('active', !isActive);
                        $('#add-to-favorite-main, #remove-from-favorite-main').toggle();
                        
                        // تأثير القلب
                        $(button).addClass('heartbeat');
                        setTimeout(() => $(button).removeClass('heartbeat'), 300);
                        
                        // عرض رسالة
                        const message = isActive ? 'تمت إزالة المنتج من المفضلة' : 'تمت إضافة المنتج إلى المفضلة';
                        showAlert(message, 'success');
                    } else {
                        showAlert(response.message || 'حدث خطأ', 'error');
                    }
                },
                error: function() {
                    showAlert('حدث خطأ أثناء تحديث المفضلة', 'error');
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
                        $('.notification-badge').text(response.count);
                    }
                }
            });
        }
        
        // دالة لتحميل محتوى سلة التسوق في الـ Modal
        function loadCartModalContent() {
            $.ajax({
                url: 'ajax/get_cart_modal.php',
                method: 'GET',
                beforeSend: function() {
                    $('#cart-modal-content').html(`
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-2">جاري تحميل السلة...</p>
                        </div>
                    `);
                },
                success: function(response) {
                    if (response.success) {
                        if (response.items.length > 0) {
                            let itemsHtml = '';
                            let totalPrice = 0;
                            
                            response.items.forEach(function(item) {
                                const itemTotal = parseFloat(item.price) * item.quantity;
                                totalPrice += itemTotal;
                                
                                itemsHtml += `
                                    <div class="cart-item d-flex align-items-center mb-3">
                                        <img src="${item.image}" alt="${item.name}" 
                                             class="cart-item-img me-3" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${item.name}</h6>
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn minus" 
                                                        onclick="updateCartItemQuantity(${item.id}, ${item.quantity - 1})"
                                                        ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                                <span class="mx-2">${item.quantity}</span>
                                                <button class="btn btn-sm btn-outline-secondary quantity-btn plus" 
                                                        onclick="updateCartItemQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                                <span class="ms-3 text-danger fw-bold">${itemTotal.toFixed(2)} ر.س</span>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="removeCartItem(${item.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            });
                            
                            itemsHtml += `
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>المجموع:</strong>
                                        <strong class="text-danger">${totalPrice.toFixed(2)} ر.س</strong>
                                    </div>
                                </div>
                            `;
                            
                            $('#cart-modal-content').html(itemsHtml);
                        } else {
                            $('#cart-modal-content').html(`
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">سلة التسوق فارغة</p>
                                </div>
                            `);
                        }
                    } else {
                        $('#cart-modal-content').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${response.message || 'حدث خطأ في تحميل السلة'}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#cart-modal-content').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            حدث خطأ في الاتصال بالخادم
                        </div>
                    `);
                }
            });
        }
        
        // دالة لتحديث كمية عنصر في السلة
        function updateCartItemQuantity(cartItemId, newQuantity) {
            if (newQuantity < 1) {
                removeCartItem(cartItemId);
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
                        loadCartModalContent();
                        updateCartCount();
                    } else {
                        showAlert(response.message || 'حدث خطأ', 'error');
                    }
                }
            });
        }
        
        // دالة لحذف عنصر من السلة
        function removeCartItem(cartItemId) {
            if (!confirm('هل تريد حذف هذا المنتج من السلة؟')) return;
            
            $.ajax({
                url: 'ajax/remove_from_cart.php',
                method: 'POST',
                data: { cart_item_id: cartItemId },
                success: function(response) {
                    if (response.success) {
                        loadCartModalContent();
                        updateCartCount();
                        showAlert('تم حذف المنتج من السلة', 'success');
                    } else {
                        showAlert(response.message || 'حدث خطأ', 'error');
                    }
                }
            });
        }
        
        // دالة لإضافة تقييم
        function submitReview(productId) {
            const rating = $('#selected-rating').val();
            const comment = $('#review-comment').val().trim();
            
            if (rating == 0) {
                showAlert('يرجى اختيار تقييم', 'error');
                return;
            }
            
            if (!comment) {
                showAlert('يرجى كتابة تعليق', 'error');
                return;
            }
            
            $.ajax({
                url: 'ajax/add_review.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    rating: rating,
                    comment: comment
                },
                success: function(response) {
                    if (response.success) {
                        $('#addReviewModal').modal('hide');
                        showAlert('تم إضافة تقييمك بنجاح', 'success');
                        location.reload(); // إعادة تحميل الصفحة لعرض التقييم الجديد
                    } else {
                        showAlert(response.message || 'حدث خطأ', 'error');
                    }
                },
                error: function() {
                    showAlert('حدث خطأ في الاتصال بالخادم', 'error');
                }
            });
        }
        
        // دالة لتطبيق كوبون الخصم
        $('#apply-coupon').click(function() {
            const couponCode = $('#coupon-code').val().trim();
            const couponMessage = $('#coupon-message');
            
            if (!couponCode) {
                couponMessage.text('يرجى إدخال كود الخصم');
                couponMessage.css('color', 'red');
                return;
            }
            
            $.ajax({
                url: 'ajax/apply_coupon.php',
                method: 'POST',
                data: {
                    coupon_code: couponCode,
                    product_id: <?= $productId ?>
                },
                success: function(response) {
                    if (response.success) {
                        couponMessage.text(response.message);
                        couponMessage.css('color', 'green');
                        
                        // تحديث السعر إذا كان هناك خصم
                        if (response.discount) {
                            const currentPrice = parseFloat(<?= $product['selling_price'] ?>);
                            const discountedPrice = currentPrice - (currentPrice * response.discount / 100);
                            $('#product-price').text(discountedPrice.toFixed(2) + ' ر.س');
                        }
                    } else {
                        couponMessage.text(response.message);
                        couponMessage.css('color', 'red');
                    }
                },
                error: function() {
                    couponMessage.text('حدث خطأ في الاتصال بالخادم');
                    couponMessage.css('color', 'red');
                }
            });
        });
        
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
        
        // دالة لمشاركة المنتج
        window.shareProduct = function(platform) {
            const productName = '<?= htmlspecialchars($product['name']) ?>';
            const productUrl = window.location.href;
            const shareText = `اكتشف هذا المنتج الرائع: ${productName}`;
            
            const shareUrls = {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}`,
                twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(productUrl)}`,
                whatsapp: `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + ' ' + productUrl)}`,
                instagram: `https://www.instagram.com/`
            };
            
            if (platform === 'instagram') {
                alert('يمكنك مشاركة المنتج على انستجرام يدويًا بنسخ الرابط');
            } else {
                window.open(shareUrls[platform], '_blank', 'width=600,height=400');
            }
        };
    </script>
</body>
</html>