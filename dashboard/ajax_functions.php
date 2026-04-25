<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'get_product':
        $product_id = (int)($_GET['id'] ?? 0);
        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صحيح']);
            exit();
        }

        $sql = "SELECT p.*, 
                       c.name as category_name,
                       b.name as brand_name,
                       cu.symbol as currency_symbol,
                       cu.code as currency_code
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN currencies cu ON p.currency_id = cu.id
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
            exit();
        }

        // جلب الصور
        $imgs_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC";
        $imgs_stmt = $conn->prepare($imgs_sql);
        $imgs_stmt->bind_param('i', $product_id);
        $imgs_stmt->execute();
        $imgs_result = $imgs_stmt->get_result();
        $images = [];
        while ($img = $imgs_result->fetch_assoc()) {
            $images[] = $img;
        }

        // جلب الألوان والأحجام
        $colors_sql = "SELECT * FROM product_colors WHERE product_id = ?";
        $colors_stmt = $conn->prepare($colors_sql);
        $colors_stmt->bind_param('i', $product_id);
        $colors_stmt->execute();
        $colors = $colors_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $sizes_sql = "SELECT * FROM product_sizes WHERE product_id = ?";
        $sizes_stmt = $conn->prepare($sizes_sql);
        $sizes_stmt->bind_param('i', $product_id);
        $sizes_stmt->execute();
        $sizes = $sizes_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            'success' => true,
            'product' => $product,
            'images'  => $images,
            'colors'  => $colors,
            'sizes'   => $sizes,
        ]);
        break;

    case 'get_categories':
        $result = $conn->query("SELECT id, name FROM categories WHERE type='product' AND (is_active=1 OR status='active') ORDER BY name");
        echo json_encode(['success' => true, 'categories' => $result->fetch_all(MYSQLI_ASSOC)]);
        break;

    case 'get_brands':
        $result = $conn->query("SELECT id, name FROM brands ORDER BY name");
        echo json_encode(['success' => true, 'brands' => $result->fetch_all(MYSQLI_ASSOC)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير معروف: ' . $action]);
        break;
}