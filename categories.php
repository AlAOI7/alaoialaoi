<?php
session_start();
require_once 'config/database.php';

// جلب الفئات الرئيسية
$sql = "SELECT c.*, 
               COUNT(DISTINCT p.id) as product_count,
               (SELECT COUNT(*) FROM categories WHERE parent_id = c.id AND (is_active = 1 OR status = 'active')) as subcategories_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND (p.is_active = 1 OR p.status = 'active')
        WHERE c.parent_id IS NULL 
          AND (c.is_active = 1 OR c.status = 'active') 
          AND c.type = 'product'
        GROUP BY c.id
        ORDER BY c.name ASC";

$result = $conn->query($sql);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// جلب الفئات الفرعية لكل فئة
foreach ($categories as &$category) {
    $sub_sql = "SELECT c.*, COUNT(DISTINCT p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND (p.is_active = 1 OR p.status = 'active')
                WHERE c.parent_id = ? 
                AND (c.is_active = 1 OR c.status = 'active') 
                GROUP BY c.id
                ORDER BY c.name ASC";
    $sub_stmt = $conn->prepare($sub_sql);
    $sub_stmt->bind_param("i", $category['id']);
    $sub_stmt->execute();
    $sub_result = $sub_stmt->get_result();
    $category['subcategories'] = [];
    while ($sub_row = $sub_result->fetch_assoc()) {
        $category['subcategories'][] = $sub_row;
    }
}

// جلب المنتجات لكل فئة
$all_products = [];
foreach ($categories as &$category) {
    $products_sql = "SELECT p.*, 
                            pi.image_path as main_image,
                            c.name as category_name,
                            CASE 
                                WHEN p.old_price > 0 THEN ROUND(((p.old_price - p.selling_price) / p.old_price) * 100)
                                ELSE 0
                            END as discount_percentage,
                            COALESCE(p.stock, p.quantity) as stock,
                            0 as rating,
                            0 as review_count
                     FROM products p
                     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.category_id = ? 
                     AND (p.is_active = 1 OR p.status = 'active')
                     ORDER BY p.created_at DESC
                     LIMIT 12";
    
    $products_stmt = $conn->prepare($products_sql);
    $products_stmt->bind_param("i", $category['id']);
    $products_stmt->execute();
    $products_result = $products_stmt->get_result();
    $category['products'] = [];
    while ($product_row = $products_result->fetch_assoc()) {
        $category['products'][] = $product_row;
        $all_products[] = $product_row;
    }
}

// تضمين functions.php لاستخدام generateProductCard
require_once 'functions.php';

// أيقونات الفئات - يمكن تخصيصها حسب الحاجة
$category_icons = [
    'default' => 'fa-box',
    'makeup' => 'fa-palette',
    'skincare' => 'fa-spa',
    'haircare' => 'fa-scissors',
    'fragrance' => 'fa-spray-can',
    'accessories' => 'fa-star'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفئات | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
        /* صفحة العنوان */
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin: 30px auto;
            max-width: 1400px;
            margin-left: 20px;
            margin-right: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
        }
        
        .page-header h1 {
            color: #667eea;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* الحاوية الرئيسية */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* قسم الفئة */
        .category-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
        }
        
        .category-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }
        
        .category-info h2 {
            color: #333;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }
        
        .category-stats {
            display: flex;
            gap: 20px;
            font-size: 0.95rem;
            color: #666;
        }
        
        .stat-badge {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .view-all-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .view-all-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        /* شبكة الفئات الفرعية */
        .subcategories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .subcategory-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .subcategory-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 0;
        }
        
        .subcategory-card:hover::before {
            opacity: 1;
        }
        
        .subcategory-card:hover {
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .subcategory-card > * {
            position: relative;
            z-index: 1;
        }
        
        .subcategory-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .subcategory-card .name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .subcategory-card .count {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* رسالة فارغة */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        /* شريط الفئات المتحرك */
        .categories-carousel-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin: 0 20px 30px 20px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .categories-carousel {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 10px 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
        
        .categories-carousel::-webkit-scrollbar {
            height: 6px;
        }
        
        .categories-carousel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .categories-carousel::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
        }
        
        .category-circle {
            flex: 0 0 auto;
            width: 100px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            padding: 10px;
            border-radius: 15px;
        }
        
        .category-circle:hover {
            background: #f8f9fa;
            transform: translateY(-5px);
        }
        
        .category-circle.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }
        
        .circle-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: white;
            font-size: 1.8rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            overflow: hidden;
        }
        
        .category-circle-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .category-circle:hover .circle-icon {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .category-circle.active .circle-icon {
            background: linear-gradient(135deg, #764ba2, #667eea);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }
        
        .circle-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .circle-count {
            font-size: 0.75rem;
            color: #666;
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-block;
        }
        
        /* شبكة المنتجات */
        .products-section {
            margin-top: 30px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f0f0f0;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .category-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .category-title {
                flex-direction: column;
            }
            
            .subcategories-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <!-- عنوان الصفحة -->
    <div class="page-header">
        <h1><i class="fas fa-th-large me-2"></i>تصفح الفئات</h1>
        <p>اكتشف جميع منتجاتنا المنظمة حسب الفئات</p>
    </div>

    <!-- شريط الفئات المتحرك -->
    <div class="categories-carousel-container">
        <div class="categories-carousel">
            <div class="category-circle active" data-category-id="all" onclick="filterCategory('all')">
                <div class="circle-icon">
                    <i class="fas fa-th"></i>
                </div>
                <div class="circle-name">الكل</div>
            </div>
            <?php foreach ($categories as $cat): ?>
                <div class="category-circle" data-category-id="<?php echo $cat['id']; ?>" onclick="filterCategory(<?php echo $cat['id']; ?>)">
                    <div class="circle-icon">
                        <?php 
                        // إصلاح مسار الصورة - إزالة ../ إذا كانت موجودة
                        $image_path = !empty($cat['image']) ? $cat['image'] : '';
                        if ($image_path && strpos($image_path, '../') === 0) {
                            $image_path = substr($image_path, 3); // إزالة ../
                        }
                        $category_image = !empty($image_path) && file_exists($image_path) ? $image_path : 'img/1.jpg';
                        ?>
                        <img src="<?php echo $category_image; ?>" 
                             alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                             class="category-circle-img"
                             onerror="this.onerror=null; this.src='img/1.jpg';">
                    </div>
                    <div class="circle-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <div class="circle-count"><?php echo $cat['product_count']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-container">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>لا توجد فئات</h3>
                <p class="text-muted">لا توجد فئات متاحة حالياً</p>
            </div>
        <?php else: ?>
            <!-- عرض جميع المنتجات عند اختيار "الكل" -->
            <div class="products-section" id="all-products">
                <h2 class="section-title">جميع المنتجات</h2>
                <div class="products-grid">
                    <?php foreach ($all_products as $product): ?>
                        <?php echo generateProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- عرض منتجات كل فئة -->
            <?php foreach ($categories as $category): ?>
                <div class="products-section category-products" data-category-id="<?php echo $category['id']; ?>" style="display: none;">
                    <h2 class="section-title">
                        <i class="fas <?php echo $category_icons['default']; ?> me-2"></i>
                        <?php echo htmlspecialchars($category['name']); ?>
                        <span class="text-muted" style="font-size: 1rem; font-weight: normal;">
                            (<?php echo count($category['products']); ?> منتج)
                        </span>
                    </h2>
                    
                    <?php if (!empty($category['products'])): ?>
                        <div class="products-grid">
                            <?php foreach ($category['products'] as $product): ?>
                                <?php echo generateProductCard($product); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>لا توجد منتجات</h3>
                            <p class="text-muted">لا توجد منتجات في هذه الفئة حالياً</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- عرض الفئات الفرعية -->
                    <?php if (!empty($category['subcategories'])): ?>
                        <div style="margin-top: 40px;">
                            <h3 style="font-size: 1.3rem; margin-bottom: 20px; color: #666;">
                                <i class="fas fa-folder me-2"></i>الفئات الفرعية
                            </h3>
                            <div class="subcategories-grid">
                                <?php foreach ($category['subcategories'] as $subcategory): ?>
                                    <a href="category-details.php?id=<?php echo $subcategory['id']; ?>" class="subcategory-card">
                                        <i class="fas <?php echo $category_icons['default']; ?>"></i>
                                        <div class="name"><?php echo htmlspecialchars($subcategory['name']); ?></div>
                                        <div class="count"><?php echo $subcategory['product_count']; ?> منتج</div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterCategory(categoryId) {
            // تحديث الفئة النشطة
            document.querySelectorAll('.category-circle').forEach(circle => {
                circle.classList.remove('active');
            });
            
            if (categoryId === 'all') {
                document.querySelector('.category-circle[data-category-id="all"]').classList.add('active');
                // عرض جميع المنتجات
                document.getElementById('all-products').style.display = 'block';
                document.querySelectorAll('.category-products').forEach(section => {
                    section.style.display = 'none';
                });
            } else {
                document.querySelector(`.category-circle[data-category-id="${categoryId}"]`).classList.add('active');
                // إخفاء جميع المنتجات
                document.getElementById('all-products').style.display = 'none';
                document.querySelectorAll('.category-products').forEach(section => {
                    section.style.display = 'none';
                });
                // عرض منتجات الفئة المحددة فقط
                const targetSection = document.querySelector(`.category-products[data-category-id="${categoryId}"]`);
                if (targetSection) {
                    targetSection.style.display = 'block';
                    // التمرير إلى المنتجات
                    targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }
        
        // دوال JavaScript للسلة والمفضلة
        function addToCart(productId, quantity = 1) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('تمت الإضافة إلى السلة ✓', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'حدث خطأ', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال', 'error');
            });
        }
        
        function showProductDetails(productId) {
            window.location.href = `product-details.php?id=${productId}`;
        }
        
        function filterByCategory(categoryId) {
            filterCategory(categoryId);
        }
        
        function toggleFavorite(productId, button) {
            const formData = new FormData();
            formData.append('product_id', productId);
            
            fetch('ajax/toggle_favorite.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = button.querySelector('i');
                    if (data.is_favorite) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'حدث خطأ', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ', 'error');
            });
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                left: 50%;
                transform: translateX(-50%);
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px 30px;
                border-radius: 12px;
                box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                z-index: 99999;
                font-weight: 600;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>