<?php
session_start();
require_once 'config/database.php';
require_once 'functions.php';

// جلب جميع الفئات النشطة
$categories_sql = "SELECT * FROM categories WHERE status = 'active' AND type = 'product' ORDER BY name ASC";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[$row['id']] = $row;
}

// الحصول على معاملات البحث والتصفية
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'grid'; // grid أو list
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; // جديد, سعر, اسم
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

$limit = 20; // عدد المنتجات في الصفحة
$offset = ($page - 1) * $limit;

// بناء استعلام SQL لجلب المنتجات
$where_conditions = ["p.is_active = 1"];
$params = [];
$param_types = "";

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $param_types .= "i";
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "ss";
}

if ($min_price > 0) {
    $where_conditions[] = "p.selling_price >= ?";
    $params[] = $min_price;
    $param_types .= "d";
}

if ($max_price > 0) {
    $where_conditions[] = "p.selling_price <= ?";
    $params[] = $max_price;
    $param_types .= "d";
}

// إضافة حالة المنتجات النشطة فقط
$where_conditions[] = "p.status IN ('active', 'low_stock')";

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// بناء ترتيب النتائج
$order_by = "";
switch ($sort_by) {
    case 'price_low':
        $order_by = "p.selling_price ASC";
        break;
    case 'price_high':
        $order_by = "p.selling_price DESC";
        break;
    case 'name':
        $order_by = "p.name ASC";
        break;
    case 'discount':
        $order_by = "CASE WHEN p.old_price > 0 THEN ((p.old_price - p.selling_price) / p.old_price) * 100 ELSE 0 END DESC, p.created_at DESC";
        break;
    case 'newest':
    default:
        $order_by = "p.created_at DESC";
        break;
}

// استعلام لجلب عدد المنتجات الكلي
$count_sql = "SELECT COUNT(*) as total FROM products p $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// استعلام مبسط لجلب المنتجات مع الصور
$products_sql = "SELECT p.*, 
                        c.name as category_name,
                        c.id as category_id,
                        pi.image_path as main_image,
                        p.old_price
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 $where_clause
                 ORDER BY $order_by
                 LIMIT ? OFFSET ?";

// إضافة معاملات LIMIT و OFFSET
$params[] = $limit;
$params[] = $offset;
$param_types .= "ii";

$products_stmt = $conn->prepare($products_sql);
$products_stmt->bind_param($param_types, ...$params);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products = [];

while ($row = $products_result->fetch_assoc()) {
    // حساب الخصم
    $discount_percentage = 0;
    if ($row['old_price'] && $row['old_price'] > 0) {
        $discount_percentage = round((($row['old_price'] - $row['selling_price']) / $row['old_price']) * 100);
    }
    
    // تحديد حالة المخزون
    $stock = max($row['stock'], $row['quantity']);
    if ($stock <= 0) {
        $row['stock_status'] = 'out_of_stock';
        $row['stock_message'] = 'غير متوفر';
        $row['stock_class'] = 'out-of-stock';
    } elseif ($stock <= 5) {
        $row['stock_status'] = 'low_stock';
        $row['stock_message'] = "بقي {$stock} قطع فقط";
        $row['stock_class'] = 'low-stock';
    } else {
        $row['stock_status'] = 'in_stock';
        $row['stock_message'] = "متوفر";
        $row['stock_class'] = 'in-stock';
    }
    
    $row['stock'] = $stock;
    $row['rating'] = 0; // قيمة افتراضية
    $row['review_count'] = 0; // قيمة افتراضية
    $row['discount_percentage'] = $discount_percentage;
    $row['discount_amount'] = $row['old_price'] ? ($row['old_price'] - $row['selling_price']) : 0;
    
    $products[] = $row;
}

// دالة لإزالة معامل من الرابط
function removeParam($param_name) {
    $query = $_GET;
    unset($query[$param_name]);
    return 'product.php?' . http_build_query($query);
}

// دالة مساعدة لبناء روابط الصفحات
function buildPageUrl($page) {
    $url = 'product.php?page=' . $page;
    
    if (!empty($_GET['category'])) {
        $url .= '&category=' . $_GET['category'];
    }
    
    if (!empty($_GET['search'])) {
        $url .= '&search=' . urlencode($_GET['search']);
    }
    
    if (!empty($_GET['view']) && $_GET['view'] != 'grid') {
        $url .= '&view=' . $_GET['view'];
    }
    
    if (!empty($_GET['sort'])) {
        $url .= '&sort=' . $_GET['sort'];
    }
    
    if (!empty($_GET['min_price'])) {
        $url .= '&min_price=' . $_GET['min_price'];
    }
    
    if (!empty($_GET['max_price'])) {
        $url .= '&max_price=' . $_GET['max_price'];
    }
    
    return $url;
}

// دالة لإنشاء نجوم التقييم
function generateRatingStars($rating) {
    if (!$rating || $rating <= 0) {
        return '<span class="text-muted">لا توجد تقييمات</span>';
    }
    
    $stars = '';
    $rating = min(5, max(0, $rating));
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i == ceil($rating) && $rating - floor($rating) >= 0.5) {
            $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-warning"></i>';
        }
    }
    
    return $stars;
}

// ملاحظة: تم إزالة دالة generateProductCard من هنا لأنها موجودة في functions.php
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المنتجات | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff3366;
            --accent-color: #ff3366;
            --dark-color: #2c2c54;
            --light-color: #f7f7f7;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-bottom: 70px;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
        
        .logo {
            height: 40px;
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
        }
        
        .icon-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .header-icons {
            display: flex;
            gap: 10px;
        }
        
        .search-bar-container {
            position: relative;
            margin: 15px 15px 0;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: none;
            border-radius: 25px;
            background: rgba(255,255,255,0.9);
            font-size: 14px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }
        
        .main-content {
            margin-top: 180px;
        }
        
        /* شريط البحث المتقدم */
        .advanced-search-section {
            background: white;
            padding: 2px;
            position: fixed;
            top: 70px;
            width: 100%;
            z-index: 999;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .search-box-container {
            position: relative;
        }
        
        .search-box-container .search-input {
            padding-right: 45px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        
        .search-box-container .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-color);
        }
        
        .price-filter {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .price-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .filter-tag {
            background: #f0f0f0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
        }
        
        .filter-tag .remove {
            color: #999;
            text-decoration: none;
        }
        
        .filter-tag .remove:hover {
            color: #dc3545;
        }
        
        .category-section {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            position: fixed;
            top: 140px;
            width: 100%;
            z-index: 998;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .category-slider {
            display: flex;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: none;
            -ms-overflow-style: none;
            gap: 10px;
        }
        
        .category-slider::-webkit-scrollbar {
            display: none;
        }
        
        .category-item-slider {
            flex: 0 0 auto;
            padding: 8px 20px;
            border-radius: 25px;
            background: #f8f9fa;
            color: #666;
            text-decoration: none;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
            white-space: nowrap;
            font-size: 14px;
        }
        
        .category-item-slider:hover,
        .category-item-slider.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .filter-bar {
            background: white;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 215px;
        }
        
        .view-toggle {
            display: flex;
            gap: 5px;
        }
        
        .view-toggle-btn {
            background: none;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px 10px;
            color: #666;
            transition: all 0.3s;
        }
        
        .view-toggle-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .sort-select {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px 10px;
            background: white;
            color: #666;
        }
        
        /* عرض الشبكة */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 15px;
        }
        
        @media (min-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        /* عرض القائمة */
        .products-list {
            padding: 15px;
        }
        
        .product-list-item {
            display: flex;
            background: white;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .product-list-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-list-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }
        
        .product-list-info {
            flex: 1;
            padding: 15px;
        }
        
        /* بطاقة المنتج في وضع الشبكة */
        .product-card-grid {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
        }
        
        .product-card-grid:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-img-container {
            position: relative;
            overflow: hidden;
            height: 180px;
        }
        
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card-grid:hover .product-img {
            transform: scale(1.05);
        }
        
        .product-badges {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .badge-discount {
            background: #ffc107;
            color: #000;
        }
        
        .badge-new {
            background: #28a745;
            color: white;
        }
        
        .badge-stock {
            background: var(--primary-color);
            color: white;
        }
        
        .badge-out-of-stock {
            background: #6c757d;
            color: white;
        }
        
        .product-actions {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .product-card-grid:hover .product-actions {
            opacity: 1;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }
        
        .favorite-btn.active {
            color: var(--primary-color);
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-color);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .product-category {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .current-price {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .old-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
        }
        
        .product-rating {
            color: #ffc107;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .product-stock {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .stock-in {
            color: #28a745;
        }
        
        .stock-low {
            color: #ffc107;
        }
        
        .stock-out {
            color: #dc3545;
        }
        
        .add-to-cart-btn {
            width: 100%;
            padding: 8px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background: #e02e5a;
        }
        
        .add-to-cart-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }
        
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 5px;
        }
        
        .page-link {
            display: block;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .page-link:hover {
            background: #f8f9fa;
        }
        
        .page-item.active .page-link {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
        }
        
        .product-count {
            color: #666;
            font-size: 14px;
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
        
        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #777;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item i {
            font-size: 18px;
            margin-bottom: 3px;
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
        
        .modal-content {
            border-radius: 15px;
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-title {
            color: var(--dark-color);
        }
        
        .modal-body .product-detail-img {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        
        .no-products {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .no-products i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        @keyframes slideDown {
            from {
                top: -100px;
                opacity: 0;
            }
            to {
                top: 80px;
                opacity: 1;
            }
        }
        
        @keyframes slideUp {
            from {
                top: 80px;
                opacity: 1;
            }
            to {
                top: -100px;
                opacity: 0;
            }
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-top">
            <button class="icon-btn" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold">المنتجات</h5>
            <div class="header-icons">
                <a href="cart.php" class="icon-btn position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="notification-badge"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
                <button class="icon-btn" id="search-toggle">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </header>
  
    <main class="main-content">
          
        <!-- شريط البحث المتقدم -->
        <section class="advanced-search-section">
            <div class="container-fluid">
                <form method="GET" action="" class="row g-3 align-items-center">
                    <!-- البحث النصي -->
                    <div class="col-lg-12">
                        <div class="search-box-container">
                            <input type="text" 
                                   name="search" 
                                   class="form-control search-input" 
                                   placeholder="ابحث عن منتج بالاسم..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- البحث بالسعر (مخفي حالياً) -->
                    <?php if (false): ?>
                    <div class="col-lg-12">
                        <div class="price-filter">
                            <input type="number" 
                                   name="min_price" 
                                   class="form-control price-input" 
                                   placeholder="السعر الأدنى"
                                   value="<?php echo $min_price > 0 ? $min_price : ''; ?>"
                                   min="0" step="0.01">
                            <span class="text-muted">إلى</span>
                            <input type="number" 
                                   name="max_price" 
                                   class="form-control price-input" 
                                   placeholder="السعر الأقصى"
                                   value="<?php echo $max_price > 0 ? $max_price : ''; ?>"
                                   min="0" step="0.01">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- الفلاتر المختارة -->
                    <?php if ($category_id > 0 || !empty($search) || $min_price > 0 || $max_price > 0): ?>
                    <div class="col-12">
                        <div class="filter-tags">
                            <?php if ($category_id > 0 && isset($categories[$category_id])): ?>
                                <span class="filter-tag">
                                    <?php echo htmlspecialchars($categories[$category_id]['name']); ?>
                                    <a href="<?php echo removeParam('category'); ?>" class="remove ms-2">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($search)): ?>
                                <span class="filter-tag">
                                    بحث: <?php echo htmlspecialchars($search); ?>
                                    <a href="<?php echo removeParam('search'); ?>" class="remove ms-2">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($min_price > 0): ?>
                                <span class="filter-tag">
                                    من <?php echo number_format($min_price, 2); ?> ر.س
                                    <a href="<?php echo removeParam('min_price'); ?>" class="remove ms-2">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($max_price > 0): ?>
                                <span class="filter-tag">
                                    إلى <?php echo number_format($max_price, 2); ?> ر.س
                                    <a href="<?php echo removeParam('max_price'); ?>" class="remove ms-2">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($category_id > 0 || !empty($search) || $min_price > 0 || $max_price > 0): ?>
                                <a href="product.php" class="filter-tag text-danger">
                                    <i class="fas fa-redo-alt"></i> إعادة تعيين
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- الحقول المخفية -->
                    <?php if ($category_id > 0): ?>
                        <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                    <?php endif; ?>
                    <?php if ($view_mode != 'grid'): ?>
                        <input type="hidden" name="view" value="<?php echo $view_mode; ?>">
                    <?php endif; ?>
                    <?php if (!empty($sort_by) && $sort_by != 'newest'): ?>
                        <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
                    <?php endif; ?>
                </form>
            </div>
            
        </section>
        
        <!-- قسم الفئات -->
        <section class="category-section">
            
            <div class="container-fluid">
                <div class="category-slider">
                    <a href="product.php<?php echo !empty($search) ? '?search='.urlencode($search) : ''; ?>" 
                       class="category-item-slider <?php echo $category_id == 0 ? 'active' : ''; ?>">
                        الكل
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="product.php?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                           class="category-item-slider <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- شريط الفلاتر -->
        <div class="filter-bar">
            <div class="view-toggle">
                <button class="view-toggle-btn <?php echo $view_mode == 'grid' ? 'active' : ''; ?>" 
                        onclick="changeViewMode('grid')">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-toggle-btn <?php echo $view_mode == 'list' ? 'active' : ''; ?>" 
                        onclick="changeViewMode('list')">
                    <i class="fas fa-list"></i>
                </button>
            </div>
            
            <span class="product-count">
                <?php echo number_format($total_products); ?> منتج
            </span>
            
            <select class="sort-select" id="sort-select">
                <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>الأحدث</option>
                <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>السعر من الأقل للأعلى</option>
                <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>السعر من الأعلى للأقل</option>
                <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>حسب الاسم</option>
                <option value="discount" <?php echo $sort_by == 'discount' ? 'selected' : ''; ?>>أعلى خصم</option>
            </select>
        </div>
        
        <!-- عرض المنتجات -->
        <div class="container-fluid">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h4>لا توجد منتجات</h4>
                    <p>لم يتم العثور على منتجات تطابق معايير البحث</p>
                    <a href="product.php" class="btn btn-primary mt-3">عرض جميع المنتجات</a>
                </div>
            <?php else: ?>
                <?php if ($view_mode == 'grid'): ?>
                    <!-- عرض الشبكة -->
                    <div class="products-grid" id="products-container">
                        <?php foreach ($products as $product): ?>
                            <?php echo generateProductCard($product); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- عرض القائمة -->
                    <div class="products-list" id="products-container">
                        <?php foreach ($products as $product): ?>
                            <div class="product-list-item">
                                <img src="<?php echo !empty($product['main_image']) ? $product['main_image'] : 'img/default-product.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-list-img"
                                     onerror="this.src='img/default-product.jpg'">
                                
                                <div class="product-list-info">
                                    <div class="d-flex justify-content-between">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" 
                                           class="text-decoration-none">
                                            <h4 class="mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        </a>
                                        <div class="product-actions">
                                            <button class="action-btn favorite-btn" 
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    onclick="toggleFavorite(<?php echo $product['id']; ?>, this)">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="product-category mb-2">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                    </div>
                                    
                                    <div class="product-price mb-2">
                                        <span class="current-price"><?php echo number_format($product['selling_price'], 2); ?> ر.س</span>
                                        <?php if ($product['old_price'] && $product['old_price'] > $product['selling_price']): ?>
                                            <span class="old-price"><?php echo number_format($product['old_price'], 2); ?> ر.س</span>
                                            <span class="badge bg-warning text-dark ms-2">%<?php echo $product['discount_percentage']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-stock mb-3">
                                        <span class="stock-<?php echo $product['stock_status']; ?>">
                                            <i class="fas fa-box"></i> <?php echo $product['stock_message']; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showProductDetails(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-eye"></i> معاينة
                                        </button>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="addToCart(<?php echo $product['id']; ?>, 1)"
                                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                            <?php if ($product['stock'] <= 0): ?>
                                                <i class="fas fa-ban"></i> غير متوفر
                                            <?php else: ?>
                                                <i class="fas fa-cart-plus"></i> أضف إلى السلة
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- الترقيم -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="<?php echo buildPageUrl($page - 1); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo buildPageUrl($i); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="<?php echo buildPageUrl($page + 1); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- الشريط السفلي -->
    <nav class="bottom-tab-bar">
        <a href="home.php" class="tab-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="product.php" class="tab-item active">
            <i class="fas fa-store"></i>
            <span>المنتجات</span>
        </a>
        <a href="categories.php" class="tab-item">
            <i class="fas fa-th-large"></i>
            <span>الفئات</span>
        </a>
        <a href="cart.php" class="tab-item position-relative">
            <i class="fas fa-shopping-cart"></i>
            <span>السلة</span>
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="notification-badge"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </a>
        <a href="profile.php" class="tab-item">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <!-- نافذة معاينة المنتج -->
    <div class="modal fade" id="productDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="product-detail-name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="" alt="" class="product-detail-img w-100" id="product-detail-img">
                        </div>
                        <div class="col-md-6">
                            <div class="product-detail-info">
                                <div class="mb-3">
                                    <span class="badge bg-primary" id="product-detail-category"></span>
                                </div>
                                
                                <div class="product-price mb-3">
                                    <span class="current-price fs-4" id="product-detail-price"></span>
                                    <span class="old-price fs-6" id="product-detail-old-price"></span>
                                    <span class="badge bg-danger" id="product-detail-discount"></span>
                                </div>
                                
                                <div class="product-rating mb-3" id="product-detail-rating"></div>
                                
                                <div class="product-stock mb-3">
                                    <span id="product-detail-stock"></span>
                                </div>
                                
                                <div class="product-description mb-4">
                                    <h6>الوصف</h6>
                                    <p id="product-detail-description"></p>
                                </div>
                                
                                <div class="quantity-control mb-4">
                                    <label class="form-label">الكمية:</label>
                                    <div class="d-flex align-items-center">
                                        <button class="quantity-btn" onclick="changeQuantity(-1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               id="product-detail-quantity" 
                                               value="1" min="1" max="100">
                                        <button class="quantity-btn" onclick="changeQuantity(1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" id="add-to-cart-detail">
                                        <i class="fas fa-cart-plus"></i> أضف إلى السلة
                                    </button>
                                    <button class="btn btn-outline-danger" id="add-to-favorites-detail">
                                        <i class="far fa-heart"></i> أضف إلى المفضلة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // دالة إزالة معامل من الرابط
        function removeParam(param) {
            const url = new URL(window.location.href);
            url.searchParams.delete(param);
            url.searchParams.delete('page'); // العودة للصفحة الأولى
            return url.toString();
        }
        
        // البحث أثناء الكتابة (Auto Search)
        let searchTimeout;
        $('input[name="search"]').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                submitSearch();
            }, 800);
        });
        
        // إرسال البحث
        function submitSearch() {
            const form = $('form').first();
            const url = new URL(window.location.href);
            
            // تحديث معلمات البحث
            url.searchParams.set('search', $('input[name="search"]').val());
            url.searchParams.delete('page'); // العودة للصفحة الأولى
            
            window.location.href = url.toString();
        }
        
        // دالة تغيير الفرز
        $('#sort-select').change(function() {
            const sortBy = $(this).val();
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sortBy);
            url.searchParams.delete('page'); // العودة للصفحة الأولى
            window.location.href = url.toString();
        });
        
        // تغيير وضع العرض
        function changeViewMode(mode) {
            const url = new URL(window.location.href);
            url.searchParams.set('view', mode);
            window.location.href = url.toString();
        }
        
        // إضافة منتج إلى السلة
        function addToCart(productId, quantity) {
            fetch('add-to-cart-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('حدث خطأ في الاتصال بالخادم', 'error');
            });
        }
        
        // تبديل المفضلة
        function toggleFavorite(productId, btn) {
            const $btn = $(btn);
            const isActive = $btn.find('i').hasClass('fas');
            
            fetch('toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&action=' + (isActive ? 'remove' : 'add')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isActive) {
                        $btn.find('i').removeClass('fas').addClass('far');
                    } else {
                        $btn.find('i').removeClass('far').addClass('fas');
                    }
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('حدث خطأ', 'error');
            });
        }
        
        // عرض تفاصيل المنتج
        let currentProductId = null;
        
        function showProductDetails(productId) {
            currentProductId = productId;
            
            fetch('get-product-details.php?id=' + productId)
            .then(response => response.json())
            .then(product => {
                if (product) {
                    $('#product-detail-name').text(product.name);
                    $('#product-detail-category').text(product.category_name);
                    $('#product-detail-img').attr('src', product.main_image || 'img/default-product.jpg');
                    $('#product-detail-price').text(product.selling_price + ' ر.س');
                    
                    if (product.old_price && product.old_price > product.selling_price) {
                        const discount = Math.round(((product.old_price - product.selling_price) / product.old_price) * 100);
                        $('#product-detail-old-price').text(product.old_price + ' ر.س').show();
                        $('#product-detail-discount').text('%' + discount + ' خصم').show();
                    } else {
                        $('#product-detail-old-price').hide();
                        $('#product-detail-discount').hide();
                    }
                    
                    let ratingHtml = '';
                    for (let i = 1; i <= 5; i++) {
                        ratingHtml += `<i class="${i <= (product.rating || 0) ? 'fas' : 'far'} fa-star"></i>`;
                    }
                    $('#product-detail-rating').html(ratingHtml);
                    
                    $('#product-detail-description').text(product.description || 'لا يوجد وصف');
                    
                    const stockMessage = product.stock_message || 
                        (product.stock > 0 ? 'متوفر' : 'غير متوفر');
                    $('#product-detail-stock').html(`<i class="fas fa-box"></i> ${stockMessage}`);
                    
                    $('#product-detail-quantity').attr('max', product.stock || 1);
                    
                    $('#productDetailModal').modal('show');
                }
            })
            .catch(error => {
                showNotification('حدث خطأ في تحميل بيانات المنتج', 'error');
            });
        }
        
        // تغيير الكمية
        function changeQuantity(change) {
            const input = $('#product-detail-quantity');
            let value = parseInt(input.val());
            const max = parseInt(input.attr('max')) || 100;
            
            value += change;
            if (value < 1) value = 1;
            if (value > max) value = max;
            
            input.val(value);
        }
        
        // إضافة إلى السلة من النافذة المنبثقة
        $('#add-to-cart-detail').click(function() {
            const quantity = parseInt($('#product-detail-quantity').val());
            addToCart(currentProductId, quantity);
            $('#productDetailModal').modal('hide');
        });
        
        // إضافة إلى المفضلة من النافذة المنبثقة
        $('#add-to-favorites-detail').click(function() {
            toggleFavorite(currentProductId, $(this));
            $('#productDetailModal').modal('hide');
        });
        
        // تحديث عداد السلة
        function updateCartCount(count) {
            $('.notification-badge').text(count);
            if (count > 0) {
                $('.notification-badge').show();
            } else {
                $('.notification-badge').hide();
            }
        }
        
        // عرض الإشعارات
        function showNotification(message, type) {
            const notification = $(`
                <div class="alert alert-${type === 'success' ? 'success' : 'danger'} 
                             alert-dismissible fade show position-fixed" 
                     style="top: 80px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(() => {
                notification.fadeOut(300, function() { $(this).remove(); });
            }, 3000);
        }
        
        // تفعيل البحث
        $(document).ready(function() {
            // إظهار/إخفاء شريط البحث
            $('#search-toggle').click(function() {
                $('.advanced-search-section').slideToggle();
            });
            
            // البحث عند الضغط على Enter
            $('.search-input').keypress(function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    submitSearch();
                }
            });
            
            // استعادة حالة المفضلة (إذا كان المستخدم مسجل دخول)
            <?php if (isset($_SESSION['user_id'])): ?>
            $.ajax({
                url: 'get-favorites.php',
                method: 'GET',
                success: function(favorites) {
                    favorites.forEach(function(productId) {
                        $(`.favorite-btn[data-product-id="${productId}"] i`)
                            .removeClass('far')
                            .addClass('fas');
                    });
                }
            });
            <?php endif; ?>
        });
    </script>

</body>
</html>