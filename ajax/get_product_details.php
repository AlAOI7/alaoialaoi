<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
    exit();
}

try {
    // الحصول على تفاصيل المنتج
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            c.name as category_name,
            b.name as brand_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.id = ? 
        AND p.status = 'active' 
        AND p.is_active = 1
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
        exit();
    }
    
    $product = $result->fetch_assoc();
    
    // الحصول على الصور الإضافية
    $images_stmt = $conn->prepare("
        SELECT image_path 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY is_main DESC, sort_order
    ");
    $images_stmt->bind_param("i", $product_id);
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
    
    $images = [];
    while ($row = $images_result->fetch_assoc()) {
        $images[] = $row['image_path'];
    }
    
    // الحصول على الألوان المتاحة
    $colors_stmt = $conn->prepare("
        SELECT color_name, color_code 
        FROM product_colors 
        WHERE product_id = ?
    ");
    $colors_stmt->bind_param("i", $product_id);
    $colors_stmt->execute();
    $colors_result = $colors_stmt->get_result();
    
    $colors = [];
    while ($row = $colors_result->fetch_assoc()) {
        $colors[] = $row;
    }
    
    // الحصول على المقاسات المتاحة
    $sizes_stmt = $conn->prepare("
        SELECT size 
        FROM product_sizes 
        WHERE product_id = ?
    ");
    $sizes_stmt->bind_param("i", $product_id);
    $sizes_stmt->execute();
    $sizes_result = $sizes_stmt->get_result();
    
    $sizes = [];
    while ($row = $sizes_result->fetch_assoc()) {
        $sizes[] = $row['size'];
    }
    
    // الحصول على التقييم
    // $rating_stmt = $conn->prepare("
    //     SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
    //     FROM product_reviews 
    //     WHERE product_id = ?
    // ");
    // $rating_stmt->bind_param("i", $product_id);
    // $rating_stmt->execute();
    // $rating_result = $rating_stmt->get_result();
    // $rating = $rating_result->fetch_assoc();
    
    // التحقق من المفضلة
    $is_favorite = false;
    if (isset($_SESSION['user_id'])) {
        $fav_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $fav_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
        $fav_stmt->execute();
        $fav_result = $fav_stmt->get_result();
        $is_favorite = $fav_result->num_rows > 0;
    }
    
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'base_price' => $product['base_price'],
            'old_price' => $product['old_price'],
            'selling_price' => $product['selling_price'],
            'discount' => $product['discount'],
            'stock' => max($product['stock'], $product['quantity']),
            'category_name' => $product['category_name'],
            'brand_name' => $product['brand_name'],
            'main_image' => $product['main_image'] ?: 'img/default-product.jpg',
            'images' => $images,
            'colors' => $colors,
            'sizes' => $sizes,
            'rating' => round($rating['avg_rating'] ?? 0, 1),
            'total_reviews' => $rating['total_reviews'] ?? 0,
            'featured' => $product['featured'],
            'new_product' => $product['new_product']
        ],
        'is_favorite' => $is_favorite
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ]);
}
?>