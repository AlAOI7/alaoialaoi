<?php
session_start();
require_once 'config/database.php';
require_once 'functions.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, products, categories

// تهيئة النتائج
$products = [];
$categories_results = [];
$total_results = 0;

if (!empty($search_query)) {
    $search_param = "%{$search_query}%";
    
    //  1. البحث في المنتجات
    if ($filter == 'all' || $filter == 'products') {
        $products_sql = "SELECT p.*, 
                                pi.image_path as main_image,
                                c.name as category_name,
                                CASE 
                                    WHEN p.old_price > 0 THEN ROUND(((p.old_price - p.selling_price) / p.old_price) * 100)
                                    ELSE 0
                                END as discount_percentage,
                                COALESCE(p.stock, p.quantity) as stock
                         FROM products p
                         LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                         LEFT JOIN categories c ON p.category_id = c.id
                         WHERE (p.is_active = 1 OR p.status = 'active')
                           AND (p.name LIKE ? OR p.description LIKE ? OR p.selling_price LIKE ?)
                         ORDER BY p.name ASC
                         LIMIT 50";
        
        $stmt = $conn->prepare($products_sql);
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    // 2. البحث في الفئات
    if ($filter == 'all' || $filter == 'categories') {
        $categories_sql = "SELECT c.*, 
                                  (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND (p.is_active = 1 OR p.status = 'active')) as product_count
                           FROM categories c
                           WHERE (c.is_active = 1 OR c.status = 'active')
                             AND c.name LIKE ?
                           ORDER BY c.name ASC
                           LIMIT 20";
        
        $stmt2 = $conn->prepare($categories_sql);
        $stmt2->bind_param("s", $search_param);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        while ($row = $result2->fetch_assoc()) {
            $categories_results[] = $row;
        }
    }
    
    $total_results = count($products) + count($categories_results);
}

$page_title = "نتائج البحث: " . htmlspecialchars($search_query);
require_once 'header_unified.php';
?>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .search-header {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin: 30px auto;
        max-width: 1200px;
        margin-left: 20px;
        margin-right: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .search-header h2 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .search-stats {
        color: #666;
        font-size: 1.1rem;
    }
    
    .filter-tabs {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .filter-tab {
        padding: 10px 25px;
        border-radius: 25px;
        border: 2px solid #eee;
        background: white;
        color: #666;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .filter-tab.active,
    .filter-tab:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-color: transparent;
    }
    
    .results-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 40px;
    }
    
    .section-title {
        color: white;
        font-size: 1.8rem;
        margin: 30px 0 20px;
        font-weight: 700;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .category-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s;
        text-decoration: none;
        color: #333;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .category-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
    }
    
    .category-info h3 {
        margin: 0;
        font-size: 1.2rem;
        color: #333;
    }
    
    .category-count {
        color: #999;
        font-size: 0.9rem;
    }
    
    .no-results {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .no-results i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        }
        
        .categories-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-tabs {
            flex-wrap: wrap;
        }
    }
</style>

<div class="search-header">
    <h2>نتائج البحث</h2>
    <p class="search-stats">
        البحث عن: <strong><?php echo htmlspecialchars($search_query); ?></strong>
        <br>
        <span class="text-muted">تم العثور على <?php echo $total_results; ?> نتيجة</span>
    </p>
    
    <div class="filter-tabs">
        <a href="?q=<?php echo urlencode($search_query); ?>&filter=all" 
           class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
            <i class="fas fa-th"></i> الكل (<?php echo $total_results; ?>)
        </a>
        <a href="?q=<?php echo urlencode($search_query); ?>&filter=products" 
           class="filter-tab <?php echo $filter == 'products' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> منتجات (<?php echo count($products); ?>)
        </a>
        <a href="?q=<?php echo urlencode($search_query); ?>&filter=categories" 
           class="filter-tab <?php echo $filter == 'categories' ? 'active' : ''; ?>">
            <i class="fas fa-folder"></i> فئات (<?php echo count($categories_results); ?>)
        </a>
    </div>
</div>

<div class="results-container">
    <?php if (empty($products) && empty($categories_results)): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h3>لم يتم العثور على نتائج</h3>
            <p class="text-muted">جرب كلمات بحث مختلفة</p>
        </div>
    <?php else: ?>
        <!-- الفئات -->
        <?php if (!empty($categories_results) && ($filter == 'all' || $filter == 'categories')): ?>
            <h2 class="section-title">الفئات (<?php echo count($categories_results); ?>)</h2>
            <div class="categories-grid">
                <?php foreach ($categories_results as $category): ?>
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                        <div class="category-icon">
                            <?php if (!empty($category['icon'])): ?>
                                <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                            <?php else: ?>
                                <i class="fas fa-folder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="category-count"><?php echo $category['product_count']; ?> منتج</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- المنتجات -->
        <?php if (!empty($products) && ($filter == 'all' || $filter == 'products')): ?>
            <h2 class="section-title">المنتجات (<?php echo count($products); ?>)</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <?php echo generateProductCard($product); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // دوال JavaScript للمنتجات
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
                location.reload(); // لتحديث العداد
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
