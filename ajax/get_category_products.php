
<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف الفئة غير صالح']);
    exit();
}

try {
    // استعلام بسيط للمنتجات
    $query = "SELECT * FROM products WHERE category_id = ? AND status = 'active' LIMIT 12";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // الحصول على صورة المنتج
        $image_query = "SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1";
        $image_stmt = $conn->prepare($image_query);
        $image_stmt->bind_param("i", $row['id']);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result();
        $image = $image_result->fetch_assoc();
        
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'selling_price' => $row['selling_price'],
            'old_price' => $row['old_price'],
            // تعديل الصورة الافتراضية هنا
    'image' => ($image && !empty($image['image_path'])) ? $image['image_path'] : 'img/1.jpg'];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ]);
}
?>