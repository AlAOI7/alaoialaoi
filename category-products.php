<?php
require_once 'config/database.php';
require_once 'functions.php';
session_start();

// التحقق من وجود معرف الفئة
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = intval($_GET['id']);

// جلب معلومات الفئة
$category_query = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
$category_stmt = mysqli_prepare($conn, $category_query);
mysqli_stmt_bind_param($category_stmt, "i", $category_id);
mysqli_stmt_execute($category_stmt);
$category_result = mysqli_stmt_get_result($category_stmt);
$category = mysqli_fetch_assoc($category_stmt);

if (!$category) {
    header('Location: index.php');
    exit;
}

// جلب جميع منتجات الفئة
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   WHERE p.category_id = ? 
                   AND p.status = 'active' 
                   ORDER BY p.created_at DESC";

$products_stmt = mysqli_prepare($conn, $products_query);
mysqli_stmt_bind_param($products_stmt, "i", $category_id);
mysqli_stmt_execute($products_stmt);
$products_result = mysqli_stmt_get_result($products_stmt);

// حساب عدد المنتجات
$count_query = "SELECT COUNT(*) as total FROM products 
                WHERE category_id = ? AND status = 'active'";
$count_stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($count_stmt, "i", $category_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_data = mysqli_fetch_assoc($count_result);
$total_products = $count_data['total'];

include 'header.php';
?>

<div class="main-content">
    <div class="container">
        <!-- مسار التنقل -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="categories.php">الفئات</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
            </ol>
        </nav>
        
        <!-- عنوان الصفحة -->
        <div class="page-header mb-4">
            <h1 class="page-title"><?php echo htmlspecialchars($category['name']); ?></h1>
            <p class="text-muted"><?php echo $total_products; ?> منتج متوفر</p>
        </div>
        
        <?php if (mysqli_num_rows($products_result) > 0): ?>
            <div class="products-grid">
                <?php while($product = mysqli_fetch_assoc($products_result)): 
                    // التحقق مما إذا كان المنتج في المفضلة
                    $is_favorite = false;
                    if (isset($_SESSION['user_id'])) {
                        $favorite_check = mysqli_query($conn, 
                            "SELECT COUNT(*) as count FROM wishlist 
                             WHERE user_id = {$_SESSION['user_id']} 
                             AND product_id = {$product['id']}");
                        $favorite_data = mysqli_fetch_assoc($favorite_check);
                        $is_favorite = $favorite_data['count'] > 0;
                    }
                ?>
                    <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                        <div class="product-img-container">
                            <img src="<?php echo $product['image'] ?? 'img/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-img"
                                 onclick="showProductDetails(<?php echo $product['id']; ?>)">
                            <?php if($product['featured']): ?>
                                <span class="featured-badge">مميز</span>
                            <?php endif; ?>
                            <?php if($product['new_product']): ?>
                                <span class="new-badge">جديد</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <div class="price-rating-container">
                                <div class="product-price">
                                    <span class="current-price"><?php echo $product['selling_price']; ?> ر.س</span>
                                    <?php if($product['old_price'] && $product['old_price'] > $product['selling_price']): ?>
                                        <small class="text-muted text-decoration-line-through"><?php echo $product['old_price']; ?> ر.س</small>
                                    <?php endif; ?>
                                </div>
                                <div class="rating">
                                    <?php 
                                    $rating = $product['rating'] ?? 0;
                                    for($i = 1; $i <= 5; $i++):
                                        echo $i <= $rating 
                                            ? '<i class="fas fa-star"></i>' 
                                            : '<i class="far fa-star"></i>';
                                    endfor;
                                    ?>
                                </div>
                            </div>
                            <?php if($product['description']): ?>
                                <p class="product-description"><?php echo substr(htmlspecialchars($product['description']), 0, 60); ?>...</p>
                            <?php endif; ?>
                        </div>
                        <span class="stock-badge"><?php echo $product['stock']; ?> متبقي</span>
                        <div class="product-actions">
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, 1)" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="btn-text">أضف للسلة</span>
                            </button>
                            <button class="favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" 
                                    onclick="toggleFavorite(<?php echo $product['id']; ?>, this)" 
                                    data-product-id="<?php echo $product['id']; ?>">
                                <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <button class="quick-view-btn" onclick="showProductDetails(<?php echo $product['id']; ?>)" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-eye"></i>
                                <span class="btn-text">عرض سريع</span>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x mb-3" style="color: #ddd;"></i>
                <h3>لا توجد منتجات في هذه الفئة</h3>
                <p class="text-muted mb-4">سيتم إضافة منتجات قريباً</p>
                <a href="categories.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>عرض جميع الفئات
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>