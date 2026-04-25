
<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// إضافة منتج إلى المفضلة
if (isset($_GET['add_to_wishlist']) && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    // التحقق إذا كان المنتج موجودًا بالفعل في المفضلة
    $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // إضافة المنتج إلى المفضلة
        $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $product_id);
        
        if ($insert_stmt->execute()) {
            $_SESSION['success_message'] = "تمت إضافة المنتج إلى المفضلة بنجاح!";
        } else {
            $_SESSION['error_message'] = "حدث خطأ أثناء إضافة المنتج إلى المفضلة";
        }
    } else {
        $_SESSION['info_message'] = "المنتج موجود بالفعل في المفضلة";
    }
    
    header('Location: wishlist.php');
    exit();
}

// إزالة منتج من المفضلة
if (isset($_GET['remove_from_wishlist']) && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    $delete_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $product_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "تمت إزالة المنتج من المفضلة بنجاح!";
    } else {
        $_SESSION['error_message'] = "حدث خطأ أثناء إزالة المنتج من المفضلة";
    }
    
    header('Location: wishlist.php');
    exit();
}

// إزالة جميع المنتجات من المفضلة
if (isset($_POST['clear_wishlist'])) {
    $clear_sql = "DELETE FROM wishlist WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_sql);
    $clear_stmt->bind_param("i", $user_id);
    
    if ($clear_stmt->execute()) {
        $_SESSION['success_message'] = "تمت إزالة جميع المنتجات من المفضلة بنجاح!";
    } else {
        $_SESSION['error_message'] = "حدث خطأ أثناء إزالة المنتجات من المفضلة";
    }
    
    header('Location: wishlist.php');
    exit();
}

// إضافة إلى السلة من المفضلة
if (isset($_GET['add_to_cart'])) {
    // هنا يمكنك إضافة كود لإضافة المنتج إلى سلة التسوق
    // سأتركها فارغة لأن جدول السلة غير موجود في البيانات المقدمة
    $_SESSION['info_message'] = "تمت إضافة المنتج إلى سلة التسوق";
    header('Location: wishlist.php');
    exit();
}

// جلب شعار الموقع للصور الافتراضية
$site_logo_fallback = function_exists('getSettings') ? (getSettings()['site_logo'] ?? 'img/1.jpg') : 'img/1.jpg';
if (strpos($site_logo_fallback, '../') === 0) $site_logo_fallback = substr($site_logo_fallback, 3);
if (!file_exists($site_logo_fallback)) $site_logo_fallback = 'img/1.jpg';

// جلب المنتجات المفضلة للمستخدم مع تفاصيل المنتجات
$sql = "SELECT 
            w.id as wishlist_id,
            w.created_at as added_date,
            p.*,
            c.name as category_name,
            b.name as brand_name,
            pi.image_path
        FROM wishlist w
        INNER JOIN products p ON w.product_id = p.id
        LEFT JOIN (SELECT product_id, image_path FROM product_images WHERE is_main = 1 GROUP BY product_id) pi ON p.id = pi.product_id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wishlist_items = $result->fetch_all(MYSQLI_ASSOC);

// حساب إحصائيات المفضلة
$stats_sql = "SELECT 
    COUNT(*) as total_items,
    SUM(p.selling_price) as total_value,
    MIN(p.selling_price) as min_price,
    MAX(p.selling_price) as max_price
    FROM wishlist w
    INNER JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?";
    
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة المفضلة - لوحة العميل</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #7209b7;
            --danger-color: #e63946;
            --success-color: #2a9d8f;
            --warning-color: #f4a261;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            font-weight: 600;
        }
        
        .product-card {
            border: 1px solid #eaeaea;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.15);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            width: 100%;
        }
        
        .product-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .old-price {
            text-decoration: line-through;
            color: #95a5a6;
            font-size: 0.9rem;
        }
        
        .discount-badge {
            background-color: var(--danger-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
        .status-low_stock { background-color: #fff3cd; color: #856404; }
        
        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-remove {
            background-color: #ffeaea;
            color: var(--danger-color);
            border: 1px solid #ffcccc;
        }
        
        .btn-remove:hover {
            background-color: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }
        
        .btn-add-to-cart {
            background-color: #e8f4ff;
            color: var(--primary-color);
            border: 1px solid #cce5ff;
        }
        
        .btn-add-to-cart:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .stat-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        
        .wishlist-empty {
            text-align: center;
            padding: 60px 20px;
        }
        
        .wishlist-empty i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .added-date {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        
        .category-badge {
            background-color: #f0f7ff;
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .brand-badge {
            background-color: #f9f0ff;
            color: var(--secondary-color);
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .product-image {
                height: 150px;
            }
            
            .stat-card h3 {
                font-size: 1.5rem;
            }
            
            .stat-icon {
                font-size: 2rem;
            }
        }
        
        /* أنيميشن لإزالة المنتج */
        .fade-out {
            animation: fadeOut 0.5s ease forwards;
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.9); height: 0; padding: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold">
                    <i class="fas fa-heart me-2" style="color: #e63946;"></i>قائمة المفضلة
                </h2>
                <p class="text-muted">إدارة وتتبع المنتجات المفضلة لديك</p>
            </div>
        </div>
        
        <!-- عرض رسائل النجاح/الخطأ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info_message'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo $_SESSION['info_message']; unset($_SESSION['info_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- إحصائيات المفضلة -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3><?php echo $stats['total_items'] ?? 0; ?></h3>
                    <p>إجمالي المنتجات</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3><?php echo number_format($stats['total_value'] ?? 0, 2); ?> ر.س</h3>
                    <p>القيمة الإجمالية</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <h3><?php echo number_format($stats['min_price'] ?? 0, 2); ?> ر.س</h3>
                    <p>أقل سعر</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <h3><?php echo number_format($stats['max_price'] ?? 0, 2); ?> ر.س</h3>
                    <p>أعلى سعر</p>
                </div>
            </div>
        </div>
        
        <!-- أدوات التحكم -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary"><?php echo count($wishlist_items); ?> منتج</span>
                        <?php if ($stats['total_items'] > 0): ?>
                            <span class="badge bg-success ms-2"><?php echo number_format($stats['total_value'], 2); ?> ر.س</span>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <?php if (!empty($wishlist_items)): ?>
                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف جميع المنتجات من المفضلة؟');">
                                <button type="submit" name="clear_wishlist" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i>إفراغ المفضلة
                                </button>
                            </form>
                            
                            <a href="product.php" class="btn btn-primary btn-sm ms-2">
                                <i class="fas fa-plus me-1"></i>إضافة منتجات جديدة
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- قائمة المنتجات المفضلة -->
        <div class="row">
            <?php if (empty($wishlist_items)): ?>
                <div class="col-12">
                    <div class="card wishlist-empty">
                        <div class="card-body">
                            <i class="fas fa-heart-broken"></i>
                            <h4 class="text-muted mb-3">قائمة المفضلة فارغة</h4>
                            <p class="text-muted mb-4">لم تقم بإضافة أي منتجات إلى المفضلة بعد</p>
                            <a href="product.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-1"></i>تصفح المنتجات
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col-lg-4 col-md-6 mb-4" id="product-<?php echo $item['id']; ?>">
                        <div class="card product-card h-100">
                            <div class="position-relative">
                                <!-- صورة المنتج -->
                                <img src="<?php echo !empty($item['image_path']) && file_exists($item['image_path']) ? htmlspecialchars($item['image_path']) : $site_logo_fallback; ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.src='<?php echo $site_logo_fallback; ?>'">
                                
                                <!-- بطاقة الخصم -->
                                <?php if ($item['discount'] > 0): ?>
                                    <span class="discount-badge position-absolute top-0 start-0 m-3">
                                        خصم <?php echo $item['discount']; ?>%
                                    </span>
                                <?php endif; ?>
                                
                                <!-- زر الإزالة -->
                                <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-3" 
                                        onclick="removeFromWishlist(<?php echo $item['id']; ?>)"
                                        title="إزالة من المفضلة">
                                    <i class="fas fa-times"></i>
                                </button>
                                
                                <!-- حالة المنتج -->
                                <?php if ($item['status'] == 'low_stock'): ?>
                                    <span class="status-badge status-low_stock position-absolute bottom-0 start-0 m-3">
                                        كمية محدودة
                                    </span>
                                <?php elseif ($item['status'] == 'inactive'): ?>
                                    <span class="status-badge status-inactive position-absolute bottom-0 start-0 m-3">
                                        غير متوفر
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <!-- فئة وماركة المنتج -->
                                <?php if (!empty($item['category_name'])): ?>
                                    <span class="category-badge"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['brand_name'])): ?>
                                    <span class="brand-badge"><?php echo htmlspecialchars($item['brand_name']); ?></span>
                                <?php endif; ?>
                                
                                <!-- اسم المنتج -->
                                <h5 class="card-title product-title">
                                    <a href="product_details.php?id=<?php echo $item['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h5>
                                
                                <!-- وصف قصير -->
                                <?php if (!empty($item['description'])): ?>
                                    <p class="card-text text-muted small mb-3">
                                        <?php 
                                        $description = strip_tags($item['description']);
                                        echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- الأسعار -->
                                <div class="d-flex align-items-center mb-3">
                                    <span class="product-price">
                                        <?php echo number_format($item['selling_price'], 2); ?> ر.س
                                    </span>
                                    
                                    <?php if ($item['old_price'] > 0 && $item['old_price'] > $item['selling_price']): ?>
                                        <span class="old-price me-2">
                                            <?php echo number_format($item['old_price'], 2); ?> ر.س
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- معلومات المخزون -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-box me-1"></i>
                                        <?php echo $item['quantity'] > 0 ? 'متوفر (' . $item['quantity'] . ')' : 'غير متوفر'; ?>
                                    </small>
                                    
                                    <small class="added-date">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('Y/m/d', strtotime($item['added_date'])); ?>
                                    </small>
                                </div>
                                
                                <!-- أزرار الإجراءات -->
                                <div class="d-flex justify-content-between">
                                    <a href="?remove_from_wishlist=true&product_id=<?php echo $item['id']; ?>" 
                                       class="btn btn-remove action-btn" 
                                       onclick="return confirm('هل تريد إزالة هذا المنتج من المفضلة؟');">
                                        <i class="fas fa-trash me-1"></i>إزالة
                                    </a>
                                    
                                    <?php if ($item['quantity'] > 0 && $item['status'] == 'active'): ?>
                                        <a href="?add_to_cart=true&product_id=<?php echo $item['id']; ?>" 
                                           class="btn btn-add-to-cart action-btn">
                                            <i class="fas fa-shopping-cart me-1"></i>أضف للسلة
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary action-btn" disabled>
                                            <i class="fas fa-ban me-1"></i>غير متوفر
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- اقتراحات المنتجات -->
        <?php if (!empty($wishlist_items)): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>اقتراحات قد تعجبك
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // جلب فئات المنتجات المفضلة
                            $category_ids = array_filter(array_column($wishlist_items, 'category_id'));
                            $category_ids_str = implode(',', array_unique($category_ids));
                            
                            if (!empty($category_ids_str)) {
                                $suggestions_sql = "SELECT p.*, pi.image_path as image FROM products p 
                                                  LEFT JOIN (SELECT product_id, image_path FROM product_images WHERE is_main = 1 GROUP BY product_id) pi ON p.id = pi.product_id
                                                  WHERE p.category_id IN ($category_ids_str) 
                                                  AND p.id NOT IN (
                                                      SELECT product_id FROM wishlist WHERE user_id = ?
                                                  )
                                                  AND p.status = 'active' 
                                                  AND p.quantity > 0
                                                  ORDER BY RAND() LIMIT 4";
                                
                                $suggestions_stmt = $conn->prepare($suggestions_sql);
                                $suggestions_stmt->bind_param("i", $user_id);
                                $suggestions_stmt->execute();
                                $suggestions_result = $suggestions_stmt->get_result();
                                $suggestions = $suggestions_result->fetch_all(MYSQLI_ASSOC);
                                
                                if (!empty($suggestions)): ?>
                                    <div class="row">
                                        <?php foreach ($suggestions as $suggestion): ?>
                                            <div class="col-lg-3 col-md-6 mb-3">
                                                <div class="card h-100">
                                                    <div class="position-relative">
                                                        <?php if (!empty($suggestion['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($suggestion['image']); ?>" 
                                                                 class="card-img-top" 
                                                                 style="height: 150px; object-fit: cover;"
                                                                 alt="<?php echo htmlspecialchars($suggestion['name']); ?>">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                                 style="height: 150px;">
                                                                <i class="fas fa-image fa-2x text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <a href="?add_to_wishlist=true&product_id=<?php echo $suggestion['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2"
                                                           title="أضف إلى المفضلة">
                                                            <i class="far fa-heart"></i>
                                                        </a>
                                                    </div>
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?php echo htmlspecialchars($suggestion['name']); ?></h6>
                                                        <p class="card-text text-primary fw-bold mb-2">
                                                            <?php echo number_format($suggestion['selling_price'], 2); ?> ر.س
                                                        </p>
                                                        <a href="product_details.php?id=<?php echo $suggestion['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary w-100">
                                                            عرض التفاصيل
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // إزالة المنتج من المفضلة مع أنيميشن
        function removeFromWishlist(productId) {
            if (confirm('هل تريد إزالة هذا المنتج من المفضلة؟')) {
                // إضافة أنيميشن الإزالة
                const productElement = document.getElementById(`product-${productId}`);
                productElement.classList.add('fade-out');
                
                // الانتظار حتى تنتهي الأنيميشن ثم التوجيه للإزالة
                setTimeout(() => {
                    window.location.href = `?remove_from_wishlist=true&product_id=${productId}`;
                }, 500);
            }
        }
        
        // تحديث عدد المنتجات في المفضلة في الهيدر
        function updateWishlistCount() {
            // يمكنك إضافة كود لتحديث العداد في الهيدر هنا
            // مثال: document.getElementById('wishlist-count').innerText = '<?php echo count($wishlist_items); ?>';
        }
        
        // تحديث الصفحة كل 30 ثانية لعرض التحديثات
        // setTimeout(() => {
        //     window.location.reload();
        // }, 30000);
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>