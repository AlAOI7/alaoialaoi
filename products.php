<?php
session_start();
require_once 'config/database.php';
require_once 'functions.php';

// جلب جميع المنتجات
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$limit = 12;
$offset = ($page - 1) * $limit;

// بناء الاستعلام
$where_clauses = ["(p.is_active = 1 OR p.status = 'active')"];
$params = [];
$types = '';

if ($search) {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($category_id > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

$where_sql = implode(' AND ', $where_clauses);

// ترتيب
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

// جلب المنتجات
$sql = "SELECT p.*, 
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
        WHERE {$where_sql}
        ORDER BY {$order_by}
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// حساب العدد الكلي
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE {$where_sql}";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params_for_count = array_slice($params, 0, -2))) {
    $count_types = substr($types, 0, -2);
    if (!empty($count_types)) {
        $count_stmt->bind_param($count_types, ...$params_for_count);
    }
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// جلب الفئات
$categories_sql = "SELECT * FROM categories 
                   WHERE (is_active = 1 OR status = 'active') 
                   AND type = 'product' 
                   AND parent_id IS NULL 
                   ORDER BY name ASC";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat;
}
?>
<?php 
$page_title = "المنتجات";
include 'header_unified.php'; 
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
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
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-box button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            transform: translateY(-50%) scale(1.05);
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
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
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header included at top -->

    <!-- الفلتر والبحث -->
    <div class="filter-section">
        <form method="GET" action="">
            <div class="search-box">
                <input type="text" name="search" placeholder="ابحث عن منتج..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
            
            <div class="filter-row">
                <div class="filter-item">
                    <label><i class="fas fa-filter"></i> الفئة</label>
                    <select name="category" onchange="this.form.submit()">
                        <option value="0">جميع الفئات</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
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
            </div>
        </form>
    </div>

    <!-- المنتجات -->
    <div class="products-container">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>لا توجد منتجات</h3>
                <p class="text-muted">جرب البحث بكلمات أخرى أو اختر فئة مختلفة</p>
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
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>">
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
                    showNotification('تمت الإضافة إلى السلة بنجاح ✓', 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showNotification(data.message || 'حدث خطأ', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال بالخادم', 'error');
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
                    if (button) {
                        const icon = button.querySelector('i');
                        if (data.is_favorite) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            button.classList.add('active');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            button.classList.remove('active');
                        }
                    }
                    showNotification(data.message, 'success');
                } else {
                    if (data.message && data.message.includes('تسجيل')) {
                        showNotification('يجب تسجيل الدخول أولاً', 'error');
                        setTimeout(() => { window.location.href = 'login.php'; }, 1500);
                    } else {
                        showNotification(data.message || 'حدث خطأ', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال', 'error');
            });
        }
        
        function updateCartCount(count) {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                cartBadge.textContent = count;
                if (count > 0) {
                    cartBadge.style.display = 'flex';
                }
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
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
                min-width: 250px;
                text-align: center;
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
