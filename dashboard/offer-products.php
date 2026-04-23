<?php
// admin/offer-products.php
session_start();
require_once '../config/database.php';

// التحقق من وجود معرف العرض
if (!isset($_GET['id'])) {
    header('Location: offers.php');
    exit();
}

$offer_id = intval($_GET['id']);

// جلب بيانات العرض
$sql = "SELECT id, title, start_date, end_date, is_active FROM offers WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $offer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    header('Location: offers.php');
    exit();
}

// البحث والتصفية
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// جلب الفئات
$categories_sql = "SELECT id, name FROM categories WHERE type = 'product' AND status = 'active' ORDER BY name";
$categories_result = mysqli_query($conn, $categories_sql);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

// بناء استعلام جلب المنتجات
$products_sql = "SELECT p.id, p.name, p.selling_price, p.status, p.stock,
                        c.name as category_name,
                        pi.image_path as image,
                        op.product_id as is_linked
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 LEFT JOIN offer_products op ON p.id = op.product_id AND op.offer_id = ?
                 WHERE p.is_active = 1";

$params = [$offer_id];
$types = "i";

if (!empty($search)) {
    $products_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($category_id > 0) {
    $products_sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if ($status !== 'all') {
    $products_sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

$products_sql .= " ORDER BY p.created_at DESC";

$products_stmt = mysqli_prepare($conn, $products_sql);
if ($types) {
    mysqli_stmt_bind_param($products_stmt, $types, ...$params);
}
mysqli_stmt_execute($products_stmt);
$products_result = mysqli_stmt_get_result($products_stmt);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// جلب المنتجات المرتبطة بالفعل بالعرض
$linked_sql = "SELECT op.product_id, op.created_at 
               FROM offer_products op 
               WHERE op.offer_id = ?";
$linked_stmt = mysqli_prepare($conn, $linked_sql);
mysqli_stmt_bind_param($linked_stmt, "i", $offer_id);
mysqli_stmt_execute($linked_stmt);
$linked_result = mysqli_stmt_get_result($linked_stmt);
$linked_products = array_column(mysqli_fetch_all($linked_result, MYSQLI_ASSOC), 'product_id');

// معالجة إضافة/إزالة المنتجات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['product_id'])) {
        $action = $_POST['action'];
        $product_id = intval($_POST['product_id']);
        
        if ($action === 'add') {
            // إضافة المنتج للعرض
            $add_sql = "INSERT INTO offer_products (offer_id, product_id) VALUES (?, ?)";
            $add_stmt = mysqli_prepare($conn, $add_sql);
            mysqli_stmt_bind_param($add_stmt, "ii", $offer_id, $product_id);
            mysqli_stmt_execute($add_stmt);
            
            echo json_encode(['success' => true, 'message' => 'تمت إضافة المنتج للعرض']);
            exit;
            
        } elseif ($action === 'remove') {
            // إزالة المنتج من العرض
            $remove_sql = "DELETE FROM offer_products WHERE offer_id = ? AND product_id = ?";
            $remove_stmt = mysqli_prepare($conn, $remove_sql);
            mysqli_stmt_bind_param($remove_stmt, "ii", $offer_id, $product_id);
            mysqli_stmt_execute($remove_stmt);
            
            echo json_encode(['success' => true, 'message' => 'تمت إزالة المنتج من العرض']);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة منتجات العرض: <?php echo htmlspecialchars($offer['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .offer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 25px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .offer-info h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
        }
        
        .offer-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .meta-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .products-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            min-height: 70vh;
        }
        
        @media (max-width: 992px) {
            .products-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* العمود الأيمن: المنتجات المرتبطة */
        .linked-products-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .section-title {
            color: #2d3748;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .linked-count {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
        
        .linked-products-list {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .linked-products-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .linked-products-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .linked-products-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        
        .linked-product-item {
            background: #f8f9ff;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }
        
        .linked-product-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.15);
        }
        
        .product-details {
            flex: 1;
        }
        
        .product-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
            font-size: 0.95rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            color: #667eea;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .remove-btn {
            background: #ff4757;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #ff6b81;
            transform: scale(1.1);
        }
        
        /* العمود الأيسر: جميع المنتجات */
        .all-products-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        /* أدوات البحث والتصفية */
        .filters-section {
            background: #f8f9ff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding-right: 45px;
        }
        
        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
        }
        
        .filter-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .filter-badge {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 8px 15px;
            font-size: 0.85rem;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-badge:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .filter-badge.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .clear-filters {
            color: #ff4757;
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* قائمة المنتجات */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 15px;
            position: relative;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.15);
        }
        
        .product-card.linked {
            border: 2px solid #667eea;
            background: #f8f9ff;
        }
        
        .linked-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #2ecc71;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .product-header {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid #e9ecef;
            flex-shrink: 0;
        }
        
        .product-image-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 1rem;
            line-height: 1.4;
        }
        
        .product-category {
            font-size: 0.8rem;
            color: #718096;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .product-price-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        
        .product-price {
            color: #667eea;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .stock-status {
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .low-stock {
            background: #fff3cd;
            color: #856404;
        }
        
        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .add-btn, .remove-btn-small {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .add-btn {
            background: #667eea;
            color: white;
        }
        
        .add-btn:hover:not(:disabled) {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .add-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }
        
        .remove-btn-small {
            background: #ff4757;
            color: white;
        }
        
        .remove-btn-small:hover {
            background: #ff6b81;
            transform: translateY(-2px);
        }
        
        /* حالة فارغة */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #cbd5e0;
        }
        
        /* رسائل التأكيد */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            z-index: 9999;
            transform: translateX(400px);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid #667eea;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            border-left-color: #2ecc71;
        }
        
        .toast.error {
            border-left-color: #ff4757;
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #a0aec0;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- رأس الصفحة -->
        <div class="offer-header">
            <div class="header-content">
                <div class="offer-info">
                    <h1>
                        <i class="fas fa-boxes me-2"></i>
                        إدارة منتجات العرض: <?php echo htmlspecialchars($offer['title']); ?>
                    </h1>
                    <div class="offer-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('Y-m-d', strtotime($offer['start_date'])); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-times"></i>
                            <?php echo date('Y-m-d', strtotime($offer['end_date'])); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-boxes"></i>
                            <?php echo count($linked_products); ?> منتج
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <span class="status-badge <?php echo $offer['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $offer['is_active'] ? 'نشط' : 'غير نشط'; ?>
                    </span>
                    <a href="edit-offer.php?id=<?php echo $offer_id; ?>" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-edit me-2"></i>
                        تعديل العرض
                    </a>
                </div>
            </div>
        </div>
        
        <!-- محتوى الصفحة -->
        <div class="products-container">
            <!-- العمود الأيمن: المنتجات المرتبطة -->
            <div class="linked-products-section">
                <div class="section-title">
                    <span>المنتجات المرتبطة</span>
                    <span class="linked-count"><?php echo count($linked_products); ?></span>
                </div>
                
                <div class="linked-products-list" id="linkedProductsList">
                    <?php if (!empty($linked_products)): ?>
                        <?php 
                        // جلب تفاصيل المنتجات المرتبطة
                        $linked_details_sql = "SELECT p.id, p.name, p.selling_price 
                                              FROM products p 
                                              WHERE p.id IN (" . implode(',', $linked_products) . ")";
                        $linked_details_result = mysqli_query($conn, $linked_details_sql);
                        while ($product = mysqli_fetch_assoc($linked_details_result)):
                        ?>
                            <div class="linked-product-item" data-product-id="<?php echo $product['id']; ?>">
                                <div class="product-details">
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-price"><?php echo number_format($product['selling_price'], 2); ?> ر.س</div>
                                </div>
                                <button class="remove-btn" onclick="removeProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>لا توجد منتجات مرتبطة</p>
                            <small class="text-muted">يمكنك إضافة منتجات من القائمة</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- العمود الأيسر: جميع المنتجات -->
            <div class="all-products-section">
                <!-- أدوات البحث والتصفية -->
                <div class="filters-section">
                    <form method="GET" action="" class="row g-3">
                        <input type="hidden" name="id" value="<?php echo $offer_id; ?>">
                        
                        <div class="col-md-6">
                            <div class="search-box">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="ابحث عن منتج..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="category_id" class="form-control">
                                <option value="0">جميع الفئات</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>جميع الحالات</option>
                                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>نشط</option>
                                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                                <option value="low_stock" <?php echo $status == 'low_stock' ? 'selected' : ''; ?>>مخزون منخفض</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="filter-badges">
                                    <a href="?id=<?php echo $offer_id; ?>" class="filter-badge <?php echo empty($search) && $category_id == 0 && $status == 'all' ? 'active' : ''; ?>">
                                        <i class="fas fa-filter"></i>
                                        الكل
                                    </a>
                                    <a href="?id=<?php echo $offer_id; ?>&search=&category_id=0&status=active" class="filter-badge <?php echo $status == 'active' ? 'active' : ''; ?>">
                                        <i class="fas fa-check-circle"></i>
                                        نشط فقط
                                    </a>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- قائمة المنتجات -->
                <div class="products-grid" id="allProductsGrid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php $is_linked = in_array($product['id'], $linked_products); ?>
                            <div class="product-card <?php echo $is_linked ? 'linked' : ''; ?>" 
                                 data-product-id="<?php echo $product['id']; ?>">
                                
                                <?php if ($is_linked): ?>
                                    <div class="linked-indicator">
                                        <i class="fas fa-link me-1"></i> مرتبط
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-header">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>
                                    
                                    <div class="product-image-placeholder" 
                                         style="<?php echo empty($product['image']) ? 'display:flex' : 'display:none'; ?>">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    
                                    <div class="product-info">
                                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <?php if (!empty($product['category_name'])): ?>
                                            <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                        <?php endif; ?>
                                        
                                        <div class="product-price-stock">
                                            <div class="product-price">
                                                <?php echo number_format($product['selling_price'], 2); ?> ر.س
                                            </div>
                                            <div class="stock-status <?php 
                                                echo $product['stock'] > 10 ? 'in-stock' : 
                                                     ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock');
                                            ?>">
                                                <?php 
                                                echo $product['stock'] > 10 ? 'متوفر' : 
                                                     ($product['stock'] > 0 ? 'مخزون منخفض' : 'غير متوفر');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <?php if ($is_linked): ?>
                                        <button class="remove-btn-small" onclick="removeProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                            إزالة
                                        </button>
                                    <?php else: ?>
                                        <button class="add-btn" onclick="addProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-plus"></i>
                                            إضافة للعرض
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h4>لم يتم العثور على منتجات</h4>
                            <p class="text-muted">جرب تغيير معايير البحث</p>
                            <a href="?id=<?php echo $offer_id; ?>" class="btn btn-outline-primary mt-3">
                                <i class="fas fa-redo me-2"></i>
                                عرض جميع المنتجات
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- العودة -->
                <div class="text-center mt-5">
                    <a href="offers.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        العودة لقائمة العروض
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // دالة لإضافة منتج للعرض
        function addProduct(productId) {
            const productCard = $(`.product-card[data-product-id="${productId}"]`);
            
            // عرض مؤشر التحميل
            const button = productCard.find('.add-btn');
            const originalHtml = button.html();
            button.html('<i class="fas fa-spinner fa-spin"></i>');
            button.prop('disabled', true);
            
            // إرسال طلب AJAX
            $.ajax({
                url: 'offer-products.php?id=<?php echo $offer_id; ?>',
                method: 'POST',
                data: {
                    action: 'add',
                    product_id: productId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showToast('success', result.message);
                            
                            // تحديث واجهة المستخدم
                            productCard.addClass('linked');
                            productCard.find('.add-btn').replaceWith(`
                                <button class="remove-btn-small" onclick="removeProduct(${productId})">
                                    <i class="fas fa-times"></i>
                                    إزالة
                                </button>
                            `);
                            
                            // إضافة مؤشر مرتبط
                            if (!productCard.find('.linked-indicator').length) {
                                productCard.prepend(`
                                    <div class="linked-indicator">
                                        <i class="fas fa-link me-1"></i> مرتبط
                                    </div>
                                `);
                            }
                            
                            // إضافة للمنتجات المرتبطة في الجانب
                            updateLinkedProductsList('add', productId);
                            
                        } else {
                            showToast('error', result.message || 'حدث خطأ');
                            button.html(originalHtml);
                            button.prop('disabled', false);
                        }
                    } catch (e) {
                        showToast('error', 'حدث خطأ غير متوقع');
                        button.html(originalHtml);
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    showToast('error', 'حدث خطأ في الاتصال بالخادم');
                    button.html(originalHtml);
                    button.prop('disabled', false);
                }
            });
        }
        
        // دالة لإزالة منتج من العرض
        function removeProduct(productId) {
            // إرسال طلب AJAX
            $.ajax({
                url: 'offer-products.php?id=<?php echo $offer_id; ?>',
                method: 'POST',
                data: {
                    action: 'remove',
                    product_id: productId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showToast('success', result.message);
                            
                            // تحديث بطاقة المنتج
                            const productCard = $(`.product-card[data-product-id="${productId}"]`);
                            productCard.removeClass('linked');
                            productCard.find('.linked-indicator').remove();
                            productCard.find('.remove-btn-small, .remove-btn').replaceWith(`
                                <button class="add-btn" onclick="addProduct(${productId})">
                                    <i class="fas fa-plus"></i>
                                    إضافة للعرض
                                </button>
                            `);
                            
                            // إزالة من القائمة المرتبطة
                            updateLinkedProductsList('remove', productId);
                            
                        } else {
                            showToast('error', result.message || 'حدث خطأ');
                        }
                    } catch (e) {
                        showToast('error', 'حدث خطأ غير متوقع');
                    }
                },
                error: function() {
                    showToast('error', 'حدث خطأ في الاتصال بالخادم');
                }
            });
        }
        
        // دالة لتحديث قائمة المنتجات المرتبطة
        function updateLinkedProductsList(action, productId) {
            const productCard = $(`.product-card[data-product-id="${productId}"]`);
            const productName = productCard.find('.product-title').text();
            const productPrice = productCard.find('.product-price').text();
            
            if (action === 'add') {
                // إضافة للقائمة المرتبطة
                const linkedItem = `
                    <div class="linked-product-item" data-product-id="${productId}">
                        <div class="product-details">
                            <div class="product-name">${productName}</div>
                            <div class="product-price">${productPrice}</div>
                        </div>
                        <button class="remove-btn" onclick="removeProduct(${productId})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                $('#linkedProductsList').append(linkedItem);
                
                // تحديث العداد
                const count = parseInt($('.linked-count').text());
                $('.linked-count').text(count + 1);
                
            } else if (action === 'remove') {
                // إزالة من القائمة المرتبطة
                $(`.linked-product-item[data-product-id="${productId}"]`).remove();
                
                // تحديث العداد
                const count = parseInt($('.linked-count').text());
                $('.linked-count').text(count - 1);
            }
            
            // إذا كانت القائمة فارغة، عرض رسالة
            if ($('#linkedProductsList .linked-product-item').length === 0) {
                $('#linkedProductsList').html(`
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>لا توجد منتجات مرتبطة</p>
                        <small class="text-muted">يمكنك إضافة منتجات من القائمة</small>
                    </div>
                `);
            }
        }
        
        // دالة لعرض رسائل التأكيد
        function showToast(type, message) {
            // إزالة أي Toast سابق
            $('.toast').remove();
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
            const toast = $(`
                <div class="toast ${type}">
                    <div class="toast-content">
                        <i class="fas ${icon}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="toast-close">&times;</button>
                </div>
            `);
            
            $('body').append(toast);
            
            // إظهار Toast
            setTimeout(() => {
                toast.addClass('show');
            }, 100);
            
            // إخفاء تلقائي بعد 3 ثواني
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
            
            // إغلاق يدوي
            toast.find('.toast-close').click(function() {
                toast.removeClass('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            });
        }
        
        // البحث المباشر أثناء الكتابة
        $(document).ready(function() {
            let searchTimeout;
            $('#search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    $(this).closest('form').submit();
                }, 500);
            });
            
            // إضافة تأثيرات للبطاقات
            $('.product-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
    </script>
</body>
</html>