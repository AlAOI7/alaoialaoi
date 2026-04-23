<?php
session_start();
require_once 'config/database.php';
require_once 'functions.php';

// التحقق من وجود معرف الفئة
$category_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['category_id']) ? intval($_GET['category_id']) : 0);

if ($category_id == 0) {
    header('Location: categories.php');
    exit;
}

// جلب بيانات الفئة
$sql = "SELECT c.*, 
               COUNT(DISTINCT p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND (p.is_active = 1 OR p.status = 'active')
        WHERE c.id = ? AND (c.is_active = 1 OR c.status = 'active')
        GROUP BY c.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: categories.php');
    exit;
}

$category = $result->fetch_assoc();

// جلب المنتجات في هذه الفئة
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$limit = 12;
$offset = ($page - 1) * $limit;

// بناء الترتيب
$order_by = "p.created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "p.selling_price ASC";
        break;
    case 'price_high':
        $order_by = "p.selling_price DESC";
        break;
    case 'name':
        $order_by = "p.name ASC";
        break;
    case 'popular':
        $order_by = "p.views DESC";
        break;
}

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
                 ORDER BY $order_by
                 LIMIT ? OFFSET ?";

$products_stmt = $conn->prepare($products_sql);
$products_stmt->bind_param("iii", $category_id, $limit, $offset);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// حساب عدد الصفحات
$count_sql = "SELECT COUNT(*) as total FROM products 
              WHERE category_id = ? 
              AND (is_active = 1 OR status = 'active')";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $category_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
        /* قسم معلومات الفئة */
        .category-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 30px auto;
            max-width: 1400px;
            margin-left: 20px;
            margin-right: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
        }
        
        .category-header h1 {
            color: #667eea;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .category-header .description {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .category-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px 25px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-item i {
            font-size: 1.5rem;
            color: #667eea;
        }
        
        .stat-item .number {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
        }
        
        /* قسم الفلترة والترتيب */
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin: 30px auto;
            max-width: 1400px;
            margin-left: 20px;
            margin-right: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-item label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .filter-item select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-item select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        /* قسم المنتجات */
        .products-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }
        
        .pagination {
            display: flex;
            gap: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .page-item a, .page-item span {
            padding: 10px 18px;
            background: white;
            border-radius: 10px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .page-item.active a {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .page-item:not(.active) a:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
        }
        
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
        
        @media (max-width: 768px) {
            .category-header h1 {
                font-size: 1.8rem;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- معلومات الفئة -->
    <div class="category-header">
        <h1><i class="fas fa-folder-open me-2"></i><?php echo htmlspecialchars($category['name']); ?></h1>
        
        <?php if (!empty($category['description'])): ?>
            <p class="description"><?php echo htmlspecialchars($category['description']); ?></p>
        <?php endif; ?>
        
        <div class="category-stats">
            <div class="stat-item">
                <i class="fas fa-box"></i>
                <div>
                    <div class="number"><?php echo $category['product_count']; ?></div>
                    <small class="text-muted">منتج</small>
                </div>
            </div>
        </div>
    </div>

    <!-- الفلترة والترتيب -->
    <div class="filter-section">
        <form method="GET" action="">
            <input type="hidden" name="id" value="<?php echo $category_id; ?>">
            <div class="filter-row">
                <div class="filter-item">
                    <label><i class="fas fa-sort"></i> الترتيب</label>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>الأحدث</option>
                        <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>الأكثر مشاهدة</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>السعر من الأقل</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>السعر من الأعلى</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>حسب الاسم</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <a href="categories.php" class="btn btn-outline-primary w-100" style="padding: 12px;">
                        <i class="fas fa-arrow-right"></i> رجوع للفئات
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- المنتجات -->
    <div class="products-container">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>لا توجد منتجات</h3>
                <p class="text-muted">لا توجد منتجات في هذه الفئة حالياً</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <?php echo generateProductCard($product); ?>
                <?php endforeach; ?>
            </div>

            <!-- الترقيم -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>&sort=<?php echo $sort; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // دوال JavaScript
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
                    location.reload();
                } else {
                    showNotification(data.message || 'حدث خطأ', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال', 'error');
            });
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