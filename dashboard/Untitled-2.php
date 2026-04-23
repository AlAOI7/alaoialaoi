<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// إنشاء المجلدات اللازمة
$folders = ['product_images', 'brand_logos', 'import_files'];
foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
}

// معالجة العمليات
$message = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        // إضافة أو تعديل منتج
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $category_id = (int)$_POST['category_id'];
        $brand_id = (int)$_POST['brand_id'];
        $base_price = floatval($_POST['base_price']);
        $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : NULL;
        $tax_rate = floatval($_POST['tax_rate']);
        $discount = floatval($_POST['discount']);
        $currency_id = (int)$_POST['currency_id'];
        $quantity = (int)$_POST['quantity'];
        $barcode = trim($_POST['barcode']);
        $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;
        $featured = isset($_POST['featured']) ? 1 : 0;
        $popular = isset($_POST['popular']) ? 1 : 0;
        $new_product = isset($_POST['new_product']) ? 1 : 0;
        $status = trim($_POST['status']);
        
        // حساب سعر البيع
        $tax_amount = $base_price * ($tax_rate / 100);
        $discount_amount = $base_price * ($discount / 100);
        $selling_price = $base_price + $tax_amount - $discount_amount;
        
        if (isset($_POST['add_product'])) {
            // إضافة منتج جديد باستخدام Prepared Statement
            $sql = "INSERT INTO products (name, description, category_id, brand_id, base_price, old_price, selling_price, 
                    tax_rate, discount, currency_id, quantity, barcode, expiry_date, featured, popular, new_product, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssiiddddddisssiii", 
                    $name, $description, $category_id, $brand_id, $base_price, $old_price, 
                    $selling_price, $tax_rate, $discount, $currency_id, $quantity, $barcode, 
                    $expiry_date, $featured, $popular, $new_product, $status
                );
                
                if ($stmt->execute()) {
                    $product_id = $stmt->insert_id;
                    $message = "<div class='message success'>تم إضافة المنتج بنجاح!</div>";
                    
                    // معالجة الصور
                    if (!empty($_FILES['product_images']['name'][0])) {
                        processProductImages($product_id, $conn);
                    }
                    
                    // معالجة الأحجام
                    if (isset($_POST['sizes'])) {
                        processProductSizes($product_id, $conn);
                    }
                    
                    // معالجة الألوان
                    if (isset($_POST['colors'])) {
                        processProductColors($product_id, $conn);
                    }
                } else {
                    $message = "<div class='message error'>خطأ في إضافة المنتج: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='message error'>خطأ في إعداد الاستعلام: " . $conn->error . "</div>";
            }
        } else {
            // تعديل منتج موجود
            $product_id = (int)$_POST['product_id'];
            
            $sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    category_id = ?, 
                    brand_id = ?, 
                    base_price = ?, 
                    old_price = ?, 
                    selling_price = ?, 
                    tax_rate = ?, 
                    discount = ?, 
                    currency_id = ?, 
                    quantity = ?, 
                    barcode = ?, 
                    expiry_date = ?, 
                    featured = ?, 
                    popular = ?, 
                    new_product = ?, 
                    status = ? 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssiiddddddisssiiii", 
                    $name, $description, $category_id, $brand_id, $base_price, $old_price, 
                    $selling_price, $tax_rate, $discount, $currency_id, $quantity, $barcode, 
                    $expiry_date, $featured, $popular, $new_product, $status, $product_id
                );
                
                if ($stmt->execute()) {
                    $message = "<div class='message success'>تم تعديل المنتج بنجاح!</div>";
                    
                    // معالجة الصور
                    if (!empty($_FILES['product_images']['name'][0])) {
                        processProductImages($product_id, $conn);
                    }
                    
                    // معالجة الأحجام
                    if (isset($_POST['sizes'])) {
                        processProductSizes($product_id, $conn);
                    }
                    
                    // معالجة الألوان
                    if (isset($_POST['colors'])) {
                        processProductColors($product_id, $conn);
                    }
                } else {
                    $message = "<div class='message error'>خطأ في تعديل المنتج: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='message error'>خطأ في إعداد الاستعلام: " . $conn->error . "</div>";
            }
        }
    } 
    elseif (isset($_POST['delete_product'])) {
        // حذف منتج
        $product_id = (int)$_POST['product_id'];
        
        // حذف الصور من السيرفر
        $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while($image = $result->fetch_assoc()) {
            if (file_exists($image['image_path'])) {
                unlink($image['image_path']);
            }
        }
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $message = "<div class='message success'>تم حذف المنتج بنجاح!</div>";
        } else {
            $message = "<div class='message error'>خطأ في حذف المنتج: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    elseif (isset($_POST['import_products'])) {
        // استيراد المنتجات من ملف Excel
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
            $file_name = 'import_files/' . time() . '_' . basename($_FILES['import_file']['name']);
            if (move_uploaded_file($_FILES['import_file']['tmp_name'], $file_name)) {
                $message = "<div class='message success'>تم رفع ملف الاستيراد بنجاح! سيتم معالجة البيانات قريباً.</div>";
            } else {
                $message = "<div class='message error'>خطأ في رفع الملف!</div>";
            }
        }
    }
    elseif (isset($_POST['export_products'])) {
        // تصدير المنتجات إلى ملف Excel
        exportProductsToExcel($conn);
    }
}

// دوال مساعدة
function processProductImages($product_id, $conn) {
    // حذف الصور القديمة أولاً
    $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    
    $main_image_set = false;
    
    foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['product_images']['error'][$key] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['product_images']['type'][$key];
            
            if (in_array($file_type, $allowed_types)) {
                $image_name = 'product_images/' . time() . '_' . $key . '_' . basename($_FILES['product_images']['name'][$key]);
                if (move_uploaded_file($tmp_name, $image_name)) {
                    $is_main = (!$main_image_set && $key == 0) ? 1 : 0;
                    if ($is_main) $main_image_set = true;
                    
                    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main, sort_order) 
                                           VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isii", $product_id, $image_name, $is_main, $key);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }
}

function processProductSizes($product_id, $conn) {
    // حذف الأحجام القديمة أولاً
    $stmt = $conn->prepare("DELETE FROM product_sizes WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    
    foreach ($_POST['sizes'] as $index => $size_data) {
        if (!empty($size_data['size'])) {
            $size = trim($size_data['size']);
            $length = !empty($size_data['length']) ? trim($size_data['length']) : '';
            $width = !empty($size_data['width']) ? trim($size_data['width']) : '';
            
            $stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, length, width) 
                                   VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $product_id, $size, $length, $width);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function processProductColors($product_id, $conn) {
    // حذف الألوان القديمة أولاً
    $stmt = $conn->prepare("DELETE FROM product_colors WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    
    foreach ($_POST['colors'] as $index => $color_data) {
        if (!empty($color_data['name']) && !empty($color_data['code'])) {
            $color_name = trim($color_data['name']);
            $color_code = trim($color_data['code']);
            
            $stmt = $conn->prepare("INSERT INTO product_colors (product_id, color_name, color_code) 
                                   VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $product_id, $color_name, $color_code);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function exportProductsToExcel($conn) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="products_export_' . date('Y-m-d') . '.xls"');
    
    $sql = "SELECT p.*, c.name as category_name, b.name as brand_name, cr.name as currency_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            LEFT JOIN currencies cr ON p.currency_id = cr.id 
            ORDER BY p.created_at DESC";
    
    $products = $conn->query($sql);
    
    echo "اسم المنتج\tالفئة\tالعلامة التجارية\tالسعر الأساسي\tسعر البيع\tالكمية\tالحالة\n";
    
    while($product = $products->fetch_assoc()) {
        echo $product['name'] . "\t" .
             $product['category_name'] . "\t" .
             $product['brand_name'] . "\t" .
             $product['base_price'] . "\t" .
             $product['selling_price'] . "\t" .
             $product['quantity'] . "\t" .
             $product['status'] . "\n";
    }
    exit;
}

// جلب البيانات للعرض
// إحصائيات المنتجات
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$sold_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity = 0")->fetch_assoc()['total'];
$featured_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE featured = 1")->fetch_assoc()['total'];
$low_stock_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE quantity < 10 AND quantity > 0")->fetch_assoc()['total'];

// جلب المنتجات للعرض في الجدول
$where_clause = "";
$sql_params = [];
if ($search) {
    $safe_search = '%' . $search . '%';
    $where_clause = " WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR b.name LIKE ?";
    $sql_params = [$safe_search, $safe_search, $safe_search, $safe_search];
}

$sql = "SELECT p.*, c.name as category_name, b.name as brand_name, cr.name as currency_name, cr.symbol as currency_symbol,
       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN currencies cr ON p.currency_id = cr.id 
    $where_clause
    ORDER BY p.created_at DESC 
    LIMIT 10";

$stmt = $conn->prepare($sql);
if ($search && !empty($sql_params)) {
    $stmt->bind_param(str_repeat('s', count($sql_params)), ...$sql_params);
}
if ($stmt) {
    if ($search && !empty($sql_params)) {
        $stmt->bind_param(str_repeat('s', count($sql_params)), ...$sql_params);
    }
    $stmt->execute();
    $products_result = $stmt->get_result();
} else {
    // إذا فشل التحضير، استخدم استعلام بسيط
    $products_result = $conn->query($sql);
}

// جلب البيانات للقوائم المنسدلة
$categories_result = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY parent_id, name");
$brands_result = $conn->query("SELECT * FROM brands WHERE status = 'active' ORDER BY name");
$currencies_result = $conn->query("SELECT * FROM currencies WHERE status = 'active' ORDER BY name");

// تنظيم الفئات في مجموعات (رئيسية وفرعية)
$categories = [];
$all_categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY parent_id, name");
while($category = $all_categories->fetch_assoc()) {
    if ($category['parent_id'] === NULL) {
        $categories[$category['id']] = [
            'id' => $category['id'],
            'name' => $category['name'],
            'subcategories' => []
        ];
    } else {
        if (isset($categories[$category['parent_id']])) {
            $categories[$category['parent_id']]['subcategories'][] = [
                'id' => $category['id'],
                'name' => $category['name']
            ];
        } else {
            // إذا لم تكن الفئة الرئيسية موجودة في المصفوفة
            $categories[$category['id']] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'subcategories' => []
            ];
        }
    }
}

// إعادة ضبط مؤشر النتائج
$brands_result->data_seek(0);
$currencies_result->data_seek(0);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المنتجات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* هيكل الصفحة */
        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            right: 0;
            top: 0;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .main-content {
            flex: 1;
            margin-right: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: margin-right 0.3s ease;
        }

        /* صفحة المحتوى */
        .page-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .page-title {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .page-title h2 {
            font-size: 2.2em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .date {
            opacity: 0.9;
            font-size: 1.1em;
        }

        /* الرسائل */
        .message {
            padding: 15px;
            margin: 20px 30px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        /* إحصائيات سريعة */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: white;
        }

        .card-1 .stat-icon { background: linear-gradient(135deg, #007bff, #0056b3); }
        .card-2 .stat-icon { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .card-3 .stat-icon { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .card-4 .stat-icon { background: linear-gradient(135deg, #dc3545, #c82333); }

        .stat-info h3 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .stat-info p {
            color: #6c757d;
            margin-bottom: 8px;
        }

        /* شريط البحث والإجراءات */
        .page-actions {
            padding: 25px 30px;
            background: white;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #007bff;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary { background: linear-gradient(135deg, #007bff, #0056b3); color: white; }
        .btn-success { background: linear-gradient(135deg, #28a745, #1e7e34); color: white; }
        .btn-warning { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* جدول المنتجات */
        .products-container {
            padding: 30px;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .products-header h3 {
            color: #2c3e50;
            font-size: 1.5em;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .products-table th, .products-table td {
            border: 1px solid #dee2e6;
            padding: 15px;
            text-align: right;
        }

        .products-table th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-weight: 600;
            color: #495057;
        }

        .product-image {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.2em;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .product-category {
            background: #e9ecef;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            color: #6c757d;
            display: inline-block;
        }

        .product-price {
            font-weight: 600;
            color: #2c3e50;
        }

        .old-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9em;
            margin-right: 8px;
        }

        .product-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
        }

        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-low-stock { background: #fff3cd; color: #856404; }

        .product-tags {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .product-tag {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: 600;
            display: inline-block;
        }

        .featured { background: #fff3cd; color: #856404; }
        .popular { background: #d1ecf1; color: #0c5460; }
        .new { background: #d4edda; color: #155724; }

        .product-actions {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .action-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
        }

        .action-btn.view:hover { background: #007bff; color: white; }
        .action-btn.edit:hover { background: #28a745; color: white; }
        .action-btn.share:hover { background: #17a2b8; color: white; }
        .action-btn.delete:hover { background: #dc3545; color: white; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
        }

        .pagination button {
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            background: white;
            color: #007bff;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .pagination button:hover, .pagination button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* النوافذ المنبثقة */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .modal-header h3 {
            font-size: 1.5em;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.2);
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            position: sticky;
            bottom: 0;
            background: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .barcode-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .barcode-container .form-control, .barcode-container .form-select {
            flex: 1;
        }

        .barcode-container button {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 15px;
            height: 46px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 46px;
        }

        .barcode-container button:hover {
            background: #5a6268;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-check label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .image-upload {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
            background: #f8f9fa;
        }

        .image-upload:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .image-upload i {
            font-size: 3em;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .image-upload p {
            color: #6c757d;
            margin: 0;
        }

        .image-upload span {
            color: #007bff;
            font-weight: 600;
        }

        .images-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .image-preview {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            transition: transform 0.3s ease;
        }

        .remove-image:hover {
            transform: scale(1.1);
        }

        .price-calculation {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .price-row:last-child {
            border-bottom: none;
        }

        .price-row.total {
            font-weight: 700;
            font-size: 1.1em;
            color: #2c3e50;
            border-top: 2px solid #dee2e6;
            margin-top: 8px;
            padding-top: 12px;
        }

        .sizes-colors-container {
            margin-bottom: 15px;
        }

        .size-color-item {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 100px 40px 40px;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #dee2e6;
            cursor: pointer;
        }

        .add-more-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
            font-size: 0.9em;
        }

        .add-more-btn:hover {
            background: #5a6268;
        }

        .remove-size-color {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .remove-size-color:hover {
            background: #c82333;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            max-width: 150px;
        }

        .quantity-control button {
            background: #e9ecef;
            border: none;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2em;
            transition: background 0.3s ease;
            color: #495057;
        }

        .quantity-control button:hover {
            background: #dee2e6;
        }

        .quantity-control input {
            flex: 1;
            text-align: center;
            border: 1px solid #e9ecef;
            border-left: none;
            border-right: none;
            height: 40px;
            font-size: 1em;
        }

        /* زر القائمة للجوال */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5em;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            align-items: center;
            justify-content: center;
        }

        /* تصميم عرض تفاصيل المنتج */
        .product-detail-modal {
            max-width: 1000px;
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .product-gallery {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-radius: 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .product-thumbnails {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .product-thumbnail {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            border: 2px solid transparent;
        }

        .product-thumbnail.active, .product-thumbnail:hover {
            opacity: 1;
            border-color: #007bff;
        }

        .product-info h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.8em;
        }

        .product-category-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 15px;
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .product-price-large {
            font-size: 1.8em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .product-price-large .old-price {
            font-size: 0.7em;
            color: #6c757d;
            text-decoration: line-through;
            margin-left: 10px;
        }

        .product-description {
            color: #6c757d;
            line-height: 1.8;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-right: 4px solid #007bff;
        }

        .product-specs {
            margin: 25px 0;
        }

        .product-specs h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 8px;
        }

        .specs-list {
            list-style: none;
        }

        .specs-list li {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .specs-list li:last-child {
            border-bottom: none;
        }

        .spec-name {
            font-weight: 600;
            color: #495057;
        }

        .spec-value {
            color: #6c757d;
        }

        .social-share {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .social-share button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity 0.3s ease;
            font-size: 0.9em;
        }

        .social-share button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .facebook { background: linear-gradient(135deg, #3b5998, #2d4373); }
        .twitter { background: linear-gradient(135deg, #1da1f2, #0c85d0); }
        .whatsapp { background: linear-gradient(135deg, #25d366, #1da851); }

        /* تعديلات للاستجابة */
        @media (max-width: 992px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
            
            .product-main-image {
                height: 300px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
                width: 100%;
                padding: 15px;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .page-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .size-color-item {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
                gap: 8px;
            }
            
            .size-color-item > * {
                grid-column: 1 / -1;
            }
            
            .size-color-item > :nth-child(5),
            .size-color-item > :nth-child(6) {
                grid-column: span 1;
                justify-self: start;
            }
            
            .products-table {
                font-size: 0.9em;
                display: block;
                overflow-x: auto;
            }
            
            .products-table th, .products-table td {
                padding: 10px 5px;
                min-width: 100px;
            }
            
            .modal {
                width: 95%;
                max-height: 85vh;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0;
            }
            
            .page-title {
                padding: 20px;
            }
            
            .page-title h2 {
                font-size: 1.8em;
            }
            
            .products-container {
                padding: 15px;
            }
            
            .modal-body {
                padding: 15px;
            }
            
            .social-share {
                flex-direction: column;
            }
            
            .modal-header {
                padding: 15px;
            }
            
            .modal-footer {
                padding: 15px;
                flex-direction: column;
            }
            
            .modal-footer .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* تحسينات عامة */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 1.1em;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #007bff;
        }

        .loading i {
            margin-right: 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .required {
            color: #dc3545;
            margin-right: 5px;
        }

        .form-hint {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }

        /* تنسيق زر القائمة الجانبية */
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            z-index: 1001;
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <?php 
    // تحقق مما إذا كان ملف header.php موجودًا
    if (file_exists('header.php')) {
        include 'header.php';
    } else {
        // بديل في حالة عدم وجود header.php
        echo '
        <header style="background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin: 0; font-size: 1.5em;">نظام إدارة المنتجات</h1>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span>مرحباً، ' . ($_SESSION['admin_name'] ?? 'المسؤول') . '</span>
                <a href="logout.php" style="color: white; text-decoration: none; background: #dc3545; padding: 8px 15px; border-radius: 5px;">تسجيل خروج</a>
            </div>
        </header>';
    }
    ?>
    
    <div class="container">
        <?php 
        // تحقق مما إذا كان ملف sidebar.php موجودًا
        if (file_exists('sidebar.php')) {
            include 'sidebar.php';
        } else {
            // بديل في حالة عدم وجود sidebar.php
            echo '
            <div class="sidebar" id="sidebar">
                <div style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <h3 style="margin: 0; color: white;">لوحة التحكم</h3>
                </div>
                <nav style="padding: 20px 0;">
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="products.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none; border-right: 3px solid #007bff; background: rgba(0,123,255,0.1);"><i class="fas fa-box" style="margin-left: 10px;"></i>المنتجات</a></li>
                        <li><a href="categories.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-list" style="margin-left: 10px;"></i>الفئات</a></li>
                        <li><a href="orders.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-shopping-cart" style="margin-left: 10px;"></i>الطلبات</a></li>
                        <li><a href="users.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-users" style="margin-left: 10px;"></i>المستخدمين</a></li>
                    </ul>
                </nav>
            </div>';
        }
        ?>
        
        <div class="main-content">
            <div class="page-content">
                <div class="page-title">
                    <h2>إدارة المنتجات</h2>
                    <div class="date"><?php echo date('l، j F Y'); ?></div>
                </div>

                <?php echo $message; ?>

                <!-- إحصائيات سريعة -->
                <div class="stats-cards">
                    <div class="stat-card card-1">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($total_products) ?></h3>
                            <p>إجمالي المنتجات</p>
                        </div>
                    </div>
                    <div class="stat-card card-2">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($sold_products) ?></h3>
                            <p>المنتجات المباعة</p>
                        </div>
                    </div>
                    <div class="stat-card card-3">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($featured_products) ?></h3>
                            <p>المنتجات المميزة</p>
                        </div>
                    </div>
                    <div class="stat-card card-4">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format($low_stock_products) ?></h3>
                            <p>منخفضة المخزون</p>
                        </div>
                    </div>
                </div>

                <!-- شريط البحث والإجراءات -->
                <div class="page-actions">
                    <div class="search-box">
                        <form method="GET" style="display: flex; align-items: center; width: 100%;">
                            <input type="text" name="search" placeholder="ابحث عن منتج، فئة، أو علامة تجارية..." value="<?= htmlspecialchars($search) ?>">
                            <i class="fas fa-search"></i>
                        </form>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-warning" id="importProductsBtn">
                            <i class="fas fa-download"></i>
                            استيراد
                        </button>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="export_products" class="btn btn-success">
                                <i class="fas fa-upload"></i>
                                تصدير
                            </button>
                        </form>
                        <button class="btn btn-primary" id="addProductBtn">
                            <i class="fas fa-plus"></i>
                            إضافة منتج
                        </button>
                    </div>
                </div>

                <!-- جدول المنتجات -->
                <div class="products-container">
                    <div class="products-header">
                        <h3>قائمة المنتجات</h3>
                        <div class="view-toggle">
                            <span>عرض <?= min(10, $products_result->num_rows ?? 0) ?> من <?= number_format($total_products) ?> منتج</span>
                        </div>
                    </div>

                    <?php if (isset($products_result) && $products_result->num_rows > 0): ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>الصورة</th>
                                    <th>المنتج</th>
                                    <th>الفئة</th>
                                    <th>السعر</th>
                                    <th>المخزون</th>
                                    <th>الحالة</th>
                                    <th>العلامات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = $products_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="product-image">
                                                <?php if ($product['main_image'] && file_exists($product['main_image'])): ?>
                                                    <img src="<?= $product['main_image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                                                <?php else: ?>
                                                    <i class="fas fa-box"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-title"><?= htmlspecialchars($product['name']) ?></div>
                                            <small class="text-muted"><?= substr(htmlspecialchars($product['description'] ?? ''), 0, 50) ?>...</small>
                                        </td>
                                        <td>
                                            <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'غير محدد') ?></span>
                                        </td>
                                        <td>
                                            <div class="product-price">
                                                <span><?= number_format($product['selling_price'], 2) ?> <?= $product['currency_symbol'] ?? '' ?></span>
                                                <?php if ($product['old_price'] && $product['old_price'] > 0): ?>
                                                    <span class="old-price"><?= number_format($product['old_price'], 2) ?> <?= $product['currency_symbol'] ?? '' ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="<?= $product['quantity'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                <?= number_format($product['quantity']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="product-status status-<?= $product['status'] ?>">
                                                <?= $product['status'] == 'active' ? 'نشط' : ($product['status'] == 'inactive' ? 'غير نشط' : 'منخفض') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="product-tags">
                                                <?php if ($product['featured']): ?>
                                                    <span class="product-tag featured">مميز</span>
                                                <?php endif; ?>
                                                <?php if ($product['popular']): ?>
                                                    <span class="product-tag popular">مطلوب</span>
                                                <?php endif; ?>
                                                <?php if ($product['new_product']): ?>
                                                    <span class="product-tag new">جديد</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="product-actions">
                                                <button class="action-btn view" 
                                                        data-id="<?= $product['id'] ?>"
                                                        data-name="<?= htmlspecialchars($product['name']) ?>"
                                                        data-category="<?= htmlspecialchars($product['category_name'] ?? '') ?>"
                                                        data-price="<?= $product['selling_price'] ?>"
                                                        data-old-price="<?= $product['old_price'] ?>"
                                                        data-currency="<?= $product['currency_symbol'] ?? '' ?>"
                                                        data-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                                                        data-quantity="<?= $product['quantity'] ?>"
                                                        data-barcode="<?= htmlspecialchars($product['barcode'] ?? '') ?>"
                                                        data-status="<?= $product['status'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit" 
                                                        data-id="<?= $product['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف المنتج؟ هذا الإجراء لا يمكن التراجع عنه.');">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <button type="submit" name="delete_product" class="action-btn delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <div class="pagination">
                            <button class="active">1</button>
                            <button>2</button>
                            <button>3</button>
                            <button>4</button>
                            <button>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-box-open" style="font-size: 3em; color: #dee2e6; margin-bottom: 15px;"></i>
                            <h3 style="color: #6c757d; margin-bottom: 10px;">لا توجد منتجات</h3>
                            <p style="color: #adb5bd;"><?= $search ? 'لم يتم العثور على منتجات تطابق بحثك' : 'لم يتم إضافة أي منتجات بعد' ?></p>
                            <button class="btn btn-primary" id="addFirstProductBtn" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i>
                                إضافة أول منتج
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- نافذة منبثقة لإضافة/تعديل منتج -->
    <div class="modal-overlay" id="productModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="productModalTitle">إضافة منتج جديد</h3>
                <button class="close-modal" id="closeProductModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <input type="hidden" name="product_id" id="productId">
                    
                    <div class="form-group">
                        <label for="productName">اسم المنتج <span class="required">*</span></label>
                        <input type="text" class="form-control" id="productName" name="name" placeholder="أدخل اسم المنتج" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productCategory">الفئة الفرعية <span class="required">*</span></label>
                            <div class="barcode-container">
                                <select class="form-select" id="productCategory" name="category_id" required>
                                    <option value="">اختر الفئة الفرعية</option>
                                    <?php foreach($categories as $category): ?>
                                        <?php if (!empty($category['subcategories'])): ?>
                                            <optgroup label="<?= htmlspecialchars($category['name']) ?>">
                                                <?php foreach($category['subcategories'] as $subcategory): ?>
                                                    <option value="<?= $subcategory['id'] ?>"><?= htmlspecialchars($subcategory['name']) ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php else: ?>
                                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="addCategoryBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="productBrand">العلامة التجارية <span class="required">*</span></label>
                            <div class="barcode-container">
                                <select class="form-select" id="productBrand" name="brand_id" required>
                                    <option value="">اختر العلامة التجارية</option>
                                    <?php 
                                    $brands_result->data_seek(0);
                                    while($brand = $brands_result->fetch_assoc()): ?>
                                        <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="button" id="addBrandBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>العلامات</label>
                        <div class="form-check">
                            <input type="checkbox" id="featuredProduct" name="featured" value="1">
                            <label for="featuredProduct">منتج مميز</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="popularProduct" name="popular" value="1">
                            <label for="popularProduct">الأكثر طلباً</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="newProduct" name="new_product" value="1" checked>
                            <label for="newProduct">منتج جديد</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productDescription">وصف المنتج</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="4" placeholder="أدخل وصف المنتج"></textarea>
                        <span class="form-hint">يمكنك كتابة وصف مفصل للمنتج هنا</span>
                    </div>
                    
                    <div class="form-group">
                        <label>صور المنتج <span class="form-hint">(يمكن رفع أكثر من صورة)</span></label>
                        <div class="image-upload" id="productImagesUpload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>اسحب وأفلت الصور هنا أو <span>انقر للاختيار</span></p>
                            <span class="form-hint">الصيغ المدعومة: JPG, PNG, GIF, WebP</span>
                        </div>
                        <input type="file" id="productImages" name="product_images[]" multiple accept="image/*" style="display: none;">
                        <div class="images-preview" id="productImagesPreview">
                            <!-- سيتم إضافة معاينات الصور هنا ديناميكياً -->
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="basePrice">السعر الأساسي (قبل الضريبة) <span class="required">*</span></label>
                            <input type="number" class="form-control" id="basePrice" name="base_price" placeholder="0.00" step="0.01" min="0" required onchange="calculatePrices()" oninput="calculatePrices()">
                        </div>
                        <div class="form-group">
                            <label for="taxRate">نسبة الضريبة</label>
                            <select class="form-select" id="taxRate" name="tax_rate" onchange="calculatePrices()">
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="10">10%</option>
                                <option value="15" selected>15%</option>
                                <option value="20">20%</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="oldPrice">السعر القديم (اختياري)</label>
                            <input type="number" class="form-control" id="oldPrice" name="old_price" placeholder="0.00" step="0.01" min="0" onchange="calculatePrices()" oninput="calculatePrices()">
                        </div>
                        <div class="form-group">
                            <label for="discount">الخصم (%)</label>
                            <input type="number" class="form-control" id="discount" name="discount" placeholder="0" step="0.01" value="0" min="0" max="100" onchange="calculatePrices()" oninput="calculatePrices()">
                        </div>
                    </div>
                    
                    <div class="price-calculation">
                        <div class="price-row">
                            <span>السعر الأساسي:</span>
                            <span id="displayBasePrice">0.00</span>
                        </div>
                        <div class="price-row">
                            <span>الضريبة (<span id="displayTaxRate">15</span>%):</span>
                            <span id="displayTaxAmount">0.00</span>
                        </div>
                        <div class="price-row">
                            <span>الخصم:</span>
                            <span id="displayDiscount">0.00</span>
                        </div>
                        <div class="price-row total">
                            <span>السعر النهائي:</span>
                            <span id="displayFinalPrice">0.00</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productCurrency">العملة <span class="required">*</span></label>
                        <select class="form-select" id="productCurrency" name="currency_id" required>
                            <option value="">اختر العملة</option>
                            <?php 
                            $currencies_result->data_seek(0);
                            while($currency = $currencies_result->fetch_assoc()): ?>
                                <option value="<?= $currency['id'] ?>"><?= htmlspecialchars($currency['name']) ?> (<?= htmlspecialchars($currency['symbol']) ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>المقاسات والألوان</label>
                        <div class="sizes-colors-container" id="sizesColorsContainer">
                            <div class="size-color-item">
                                <input type="text" class="form-control size-input" name="sizes[0][size]" placeholder="المقاس (مثال: XL)" required>
                                <input type="text" class="form-control" name="sizes[0][length]" placeholder="الطول (سم)">
                                <input type="text" class="form-control" name="sizes[0][width]" placeholder="العرض (سم)">
                                <input type="color" class="form-control" name="colors[0][code]" value="#6C63FF" onchange="updateColorName(this)">
                                <div class="color-preview" style="background-color: #6C63FF;" onclick="this.previousElementSibling.click()"></div>
                                <input type="hidden" name="colors[0][name]" value="أرجواني">
                                <button type="button" class="remove-size-color" onclick="removeSizeColor(this)" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="add-more-btn" id="addSizeColorBtn">
                            <i class="fas fa-plus"></i>
                            إضافة مقاس ولون
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="productQuantity">الكمية <span class="required">*</span></label>
                        <div class="quantity-control">
                            <button type="button" id="decreaseQuantity">-</button>
                            <input type="number" class="form-control" id="productQuantity" name="quantity" value="1" min="0" required>
                            <button type="button" id="increaseQuantity">+</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productBarcode">الباركود</label>
                        <div class="barcode-container">
                            <input type="text" class="form-control" id="productBarcode" name="barcode" placeholder="باركود المنتج">
                            <button type="button" id="generateBarcode">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiryDate">تاريخ انتهاء الصلاحية (اختياري)</label>
                        <input type="date" class="form-control" id="expiryDate" name="expiry_date" min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="productStatus">حالة المنتج <span class="required">*</span></label>
                        <select class="form-select" id="productStatus" name="status" required>
                            <option value="active" selected>نشط</option>
                            <option value="inactive">غير نشط</option>
                            <option value="low_stock">منخفض المخزون</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelProduct">إلغاء</button>
                <button class="btn btn-primary" id="saveProduct">حفظ المنتج</button>
            </div>
        </div>
    </div>

    <!-- نافذة استيراد المنتجات -->
    <div class="modal-overlay" id="importModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3>استيراد المنتجات</h3>
                <button class="close-modal" id="closeImportModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="importForm">
                    <div class="form-group">
                        <label>تحميل ملف Excel</label>
                        <div class="image-upload" id="importFileUpload">
                            <i class="fas fa-file-excel"></i>
                            <p>انقر لاختيار ملف Excel أو اسحبه هنا</p>
                            <small style="display: block; margin-top: 10px; color: #666;">
                                يجب أن يكون الملف بصيغة .xlsx أو .xls
                            </small>
                        </div>
                        <input type="file" id="importFile" name="import_file" accept=".xlsx,.xls" style="display: none;" required>
                    </div>
                    
                    <div class="form-group">
                        <label>تعليمات الاستيراد</label>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 0.9em;">
                            <p><strong>تأكد أن ملف Excel يحتوي على الأعمدة التالية:</strong></p>
                            <ul style="margin-right: 20px;">
                                <li>اسم المنتج (مطلوب)</li>
                                <li>الوصف</li>
                                <li>الفئة</li>
                                <li>العلامة التجارية</li>
                                <li>السعر الأساسي (مطلوب)</li>
                                <li>الكمية (مطلوب)</li>
                            </ul>
                            <p style="margin-top: 10px; color: #dc3545;">
                                <i class="fas fa-exclamation-triangle"></i>
                                سيتم استبدال البيانات الحالية في حالة وجود تكرار
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelImport">إلغاء</button>
                <button class="btn btn-primary" id="submitImport">بدء الاستيراد</button>
            </div>
        </div>
    </div>

    <!-- نافذة عرض المنتج -->
    <div class="modal-overlay" id="viewProductModal">
        <div class="modal product-detail-modal">
            <div class="modal-header">
                <h3>تفاصيل المنتج</h3>
                <button class="close-modal" id="closeViewProductModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="loading" id="productDetailLoading">
                    <i class="fas fa-spinner"></i> جاري تحميل بيانات المنتج...
                </div>
                <div class="product-detail" id="productDetailContent" style="display: none;">
                    <div class="product-gallery">
                        <img src="https://via.placeholder.com/500x400?text=جاري+تحميل+الصورة" alt="المنتج" class="product-main-image" id="viewMainImage">
                        <div class="product-thumbnails" id="viewThumbnails">
                            <!-- سيتم إضافة الصور المصغرة هنا -->
                        </div>
                    </div>
                    <div class="product-info">
                        <h2 id="viewProductName">-</h2>
                        <div class="product-category-badge" id="viewProductCategory">-</div>
                        <div class="product-price-large">
                            <span class="old-price" id="viewOldPrice"></span>
                            <span id="viewProductPrice">-</span>
                        </div>
                        <div class="product-description" id="viewProductDescription">
                            لا يوجد وصف
                        </div>
                        
                        <div class="product-specs">
                            <h4>المواصفات</h4>
                            <ul class="specs-list" id="viewProductSpecs">
                                <li>
                                    <span class="spec-name">الكمية:</span>
                                    <span class="spec-value" id="viewProductQuantity">-</span>
                                </li>
                                <li>
                                    <span class="spec-name">الباركود:</span>
                                    <span class="spec-value" id="viewProductBarcode">-</span>
                                </li>
                                <li>
                                    <span class="spec-name">الحالة:</span>
                                    <span class="spec-value" id="viewProductStatus">-</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="social-share">
                            <button class="facebook" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i>
                                فيسبوك
                            </button>
                            <button class="twitter" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i>
                                تويتر
                            </button>
                            <button class="whatsapp" onclick="shareOnWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                                واتساب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeViewProduct">إغلاق</button>
                <button class="btn btn-primary" id="editViewProduct">
                    <i class="fas fa-edit"></i>
                    تعديل المنتج
                </button>
            </div>
        </div>
    </div>

    <script>
        // متغيرات عامة
        let sizeColorCounter = 1;
        let uploadedImages = [];
        let currentProductId = null;

        // تهيئة الصفحة عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            calculatePrices();
            initEventListeners();
            
            // إذا كان هناك رسالة، إخفائها بعد 5 ثواني
            setTimeout(() => {
                const messages = document.querySelectorAll('.message');
                messages.forEach(msg => {
                    msg.style.transition = 'opacity 0.5s ease';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                });
            }, 5000);
        });

        // تهيئة مستمعي الأحداث
        function initEventListeners() {
            // زر إضافة منتج
            document.getElementById('addProductBtn').addEventListener('click', openAddProductModal);
            document.getElementById('addFirstProductBtn')?.addEventListener('click', openAddProductModal);
            
            // أزرار الإغلاق
            document.getElementById('closeProductModal').addEventListener('click', closeProductModal);
            document.getElementById('cancelProduct').addEventListener('click', closeProductModal);
            document.getElementById('closeImportModal').addEventListener('click', closeImportModal);
            document.getElementById('cancelImport').addEventListener('click', closeImportModal);
            document.getElementById('closeViewProductModal').addEventListener('click', closeViewProductModal);
            document.getElementById('closeViewProduct').addEventListener('click', closeViewProductModal);
            
            // حفظ المنتج
            document.getElementById('saveProduct').addEventListener('click', saveProduct);
            
            // استيراد
            document.getElementById('importProductsBtn').addEventListener('click', openImportModal);
            document.getElementById('importFileUpload').addEventListener('click', () => document.getElementById('importFile').click());
            document.getElementById('submitImport').addEventListener('click', submitImport);
            
            // رفع الصور
            document.getElementById('productImagesUpload').addEventListener('click', () => document.getElementById('productImages').click());
            document.getElementById('productImages').addEventListener('change', previewImages);
            
            // التحكم في الكمية
            document.getElementById('increaseQuantity').addEventListener('click', () => changeQuantity(1));
            document.getElementById('decreaseQuantity').addEventListener('click', () => changeQuantity(-1));
            
            // توليد الباركود
            document.getElementById('generateBarcode').addEventListener('click', generateBarcode);
            
            // إضافة مقاس ولون
            document.getElementById('addSizeColorBtn').addEventListener('click', addSizeColor);
            
            // زر القائمة الجانبية للجوال
            document.getElementById('mobileMenuBtn').addEventListener('click', toggleSidebar);
            
            // تحرير المنتج من نافذة العرض
            document.getElementById('editViewProduct').addEventListener('click', editProductFromView);
            
            // إغلاق النوافذ عند النقر خارجها
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                    }
                });
            });
            
            // عرض المنتج عند النقر على زر العرض
            document.querySelectorAll('.action-btn.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    viewProduct(productId);
                });
            });
            
            // تحرير المنتج عند النقر على زر التحرير
            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    editProduct(productId);
                });
            });
            
            // سحب وإفلات الصور
            initDragAndDrop();
        }

        // فتح نافذة إضافة منتج
        function openAddProductModal() {
            document.getElementById('productModalTitle').textContent = 'إضافة منتج جديد';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productImagesPreview').innerHTML = '';
            document.getElementById('sizesColorsContainer').innerHTML = `
                <div class="size-color-item">
                    <input type="text" class="form-control size-input" name="sizes[0][size]" placeholder="المقاس (مثال: XL)" required>
                    <input type="text" class="form-control" name="sizes[0][length]" placeholder="الطول (سم)">
                    <input type="text" class="form-control" name="sizes[0][width]" placeholder="العرض (سم)">
                    <input type="color" class="form-control" name="colors[0][code]" value="#6C63FF" onchange="updateColorName(this)">
                    <div class="color-preview" style="background-color: #6C63FF;" onclick="this.previousElementSibling.click()"></div>
                    <input type="hidden" name="colors[0][name]" value="أرجواني">
                    <button type="button" class="remove-size-color" onclick="removeSizeColor(this)" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            sizeColorCounter = 1;
            uploadedImages = [];
            calculatePrices();
            document.getElementById('productModal').style.display = 'flex';
        }

        // إغلاق نافذة المنتج
        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // فتح نافذة الاستيراد
        function openImportModal() {
            document.getElementById('importModal').style.display = 'flex';
        }

        // إغلاق نافذة الاستيراد
        function closeImportModal() {
            document.getElementById('importModal').style.display = 'none';
            document.getElementById('importForm').reset();
        }

        // إغلاق نافذة العرض
        function closeViewProductModal() {
            document.getElementById('viewProductModal').style.display = 'none';
        }

        // حفظ المنتج
        function saveProduct() {
            const form = document.getElementById('productForm');
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            // التحقق من الحقول المطلوبة
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                    
                    // إضافة رسالة خطأ
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-msg')) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-msg';
                        errorMsg.style.color = '#dc3545';
                        errorMsg.style.fontSize = '0.85em';
                        errorMsg.style.marginTop = '5px';
                        errorMsg.textContent = 'هذا الحقل مطلوب';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                } else {
                    field.style.borderColor = '#e9ecef';
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-msg')) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                alert('يرجى ملء جميع الحقول المطلوبة');
                return;
            }
            
            // التحقق من وجود مقاس واحد على الأقل
            const sizeInputs = form.querySelectorAll('.size-input');
            let hasSize = false;
            sizeInputs.forEach(input => {
                if (input.value.trim()) hasSize = true;
            });
            
            if (!hasSize) {
                alert('يرجى إدخال مقاس واحد على الأقل');
                return;
            }
            
            // تحديد نوع العملية (إضافة أو تحديث)
            const productId = document.getElementById('productId').value;
            if (productId) {
                form.action = 'products.php?edit=' + productId;
                const updateInput = document.createElement('input');
                updateInput.type = 'hidden';
                updateInput.name = 'update_product';
                updateInput.value = '1';
                form.appendChild(updateInput);
            } else {
                form.action = 'products.php';
                const addInput = document.createElement('input');
                addInput.type = 'hidden';
                addInput.name = 'add_product';
                addInput.value = '1';
                form.appendChild(addInput);
            }
            
            // إرسال النموذج
            form.submit();
        }

        // معاينة الصور
        function previewImages(input) {
            const preview = document.getElementById('productImagesPreview');
            const files = input.files;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imagePreview = document.createElement('div');
                    imagePreview.className = 'image-preview';
                    imagePreview.innerHTML = `
                        <img src="${e.target.result}" alt="معاينة الصورة">
                        <button type="button" class="remove-image" onclick="removeImage(${uploadedImages.length})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(imagePreview);
                    
                    uploadedImages.push({
                        file: file,
                        dataUrl: e.target.result,
                        name: file.name
                    });
                }
                
                reader.readAsDataURL(file);
            }
            
            // إعادة تعيين قيمة input للسماح برفع نفس الملف مرة أخرى
            input.value = '';
        }

        function removeImage(index) {
            uploadedImages.splice(index, 1);
            previewImagesFromArray();
        }

        function previewImagesFromArray() {
            const preview = document.getElementById('productImagesPreview');
            preview.innerHTML = '';
            
            uploadedImages.forEach((image, index) => {
                const imagePreview = document.createElement('div');
                imagePreview.className = 'image-preview';
                imagePreview.innerHTML = `
                    <img src="${image.dataUrl}" alt="معاينة الصورة">
                    <button type="button" class="remove-image" onclick="removeImage(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                preview.appendChild(imagePreview);
            });
        }

        // إضافة مقاس ولون جديد
        function addSizeColor() {
            const container = document.getElementById('sizesColorsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'size-color-item';
            newItem.innerHTML = `
                <input type="text" class="form-control size-input" name="sizes[${sizeColorCounter}][size]" placeholder="المقاس (مثال: XL)" required>
                <input type="text" class="form-control" name="sizes[${sizeColorCounter}][length]" placeholder="الطول (سم)">
                <input type="text" class="form-control" name="sizes[${sizeColorCounter}][width]" placeholder="العرض (سم)">
                <input type="color" class="form-control" name="colors[${sizeColorCounter}][code]" value="#6C63FF" onchange="updateColorName(this)">
                <div class="color-preview" style="background-color: #6C63FF;" onclick="this.previousElementSibling.click()"></div>
                <input type="hidden" name="colors[${sizeColorCounter}][name]" value="أرجواني">
                <button type="button" class="remove-size-color" onclick="removeSizeColor(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newItem);
            
            // إظهار أزرار الحذف إذا كان هناك أكثر من عنصر
            const items = container.querySelectorAll('.size-color-item');
            if (items.length > 1) {
                items.forEach(item => {
                    item.querySelector('.remove-size-color').style.display = 'block';
                });
            }
            
            sizeColorCounter++;
        }

        function removeSizeColor(button) {
            const item = button.closest('.size-color-item');
            item.remove();
            
            // إعادة ترقيم العناصر
            const container = document.getElementById('sizesColorsContainer');
            const items = container.querySelectorAll('.size-color-item');
            
            if (items.length === 1) {
                items[0].querySelector('.remove-size-color').style.display = 'none';
            }
            
            // يمكن إعادة ترقيم أسماء الحقول هنا إذا لزم الأمر
        }

        // تحديث اسم اللون بناءً على قيمته
        function updateColorName(input) {
            const colorNameInput = input.nextElementSibling.nextElementSibling;
            const colorPreview = input.nextElementSibling;
            const color = input.value;
            
            // تحديث معاينة اللون
            colorPreview.style.backgroundColor = color;
            
            // تحديد اسم اللون بناءً على قيمته
            const colorNames = {
                '#FF0000': 'أحمر',
                '#00FF00': 'أخضر',
                '#0000FF': 'أزرق',
                '#FFFF00': 'أصفر',
                '#FF00FF': 'وردي',
                '#00FFFF': 'سماوي',
                '#000000': 'أسود',
                '#FFFFFF': 'أبيض',
                '#808080': 'رمادي',
                '#FFA500': 'برتقالي',
                '#800080': 'بنفسجي',
                '#6C63FF': 'أرجواني'
            };
            
            colorNameInput.value = colorNames[color.toUpperCase()] || 'لون مخصص';
        }

        // التحكم في الكمية
        function changeQuantity(amount) {
            const quantityInput = document.getElementById('productQuantity');
            let currentValue = parseInt(quantityInput.value) || 0;
            currentValue += amount;
            
            if (currentValue < 0) currentValue = 0;
            quantityInput.value = currentValue;
        }

        // توليد الباركود
        function generateBarcode() {
            const barcode = 'PROD' + Date.now().toString().substr(-8) + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            document.getElementById('productBarcode').value = barcode;
        }

        // حساب الأسعار
        function calculatePrices() {
            const basePrice = parseFloat(document.getElementById('basePrice').value) || 0;
            const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            
            const taxAmount = basePrice * (taxRate / 100);
            const discountAmount = basePrice * (discount / 100);
            const finalPrice = basePrice + taxAmount - discountAmount;
            
            document.getElementById('displayBasePrice').textContent = basePrice.toFixed(2);
            document.getElementById('displayTaxRate').textContent = taxRate;
            document.getElementById('displayTaxAmount').textContent = taxAmount.toFixed(2);
            document.getElementById('displayDiscount').textContent = discountAmount.toFixed(2);
            document.getElementById('displayFinalPrice').textContent = finalPrice.toFixed(2);
        }

        // عرض المنتج
        function viewProduct(productId) {
            currentProductId = productId;
            
            // إظهار مؤشر التحميل
            document.getElementById('productDetailLoading').style.display = 'block';
            document.getElementById('productDetailContent').style.display = 'none';
            
            // فتح النافذة
            document.getElementById('viewProductModal').style.display = 'flex';
            
            // محاكاة جلب البيانات (في الواقع سيكون هذا طلب AJAX)
            setTimeout(() => {
                // في التطبيق الحقيقي، ستكون هذه بيانات حقيقية من الخادم
                const viewBtn = document.querySelector(`.action-btn.view[data-id="${productId}"]`);
                if (viewBtn) {
                    document.getElementById('viewProductName').textContent = viewBtn.getAttribute('data-name');
                    document.getElementById('viewProductCategory').textContent = viewBtn.getAttribute('data-category');
                    document.getElementById('viewProductPrice').textContent = viewBtn.getAttribute('data-price') + ' ' + viewBtn.getAttribute('data-currency');
                    document.getElementById('viewProductDescription').textContent = viewBtn.getAttribute('data-description') || 'لا يوجد وصف';
                    document.getElementById('viewProductQuantity').textContent = viewBtn.getAttribute('data-quantity');
                    document.getElementById('viewProductBarcode').textContent = viewBtn.getAttribute('data-barcode') || 'غير متوفر';
                    
                    const oldPrice = viewBtn.getAttribute('data-old-price');
                    if (oldPrice && oldPrice !== '0') {
                        document.getElementById('viewOldPrice').textContent = oldPrice + ' ' + viewBtn.getAttribute('data-currency');
                        document.getElementById('viewOldPrice').style.display = 'inline';
                    } else {
                        document.getElementById('viewOldPrice').style.display = 'none';
                    }
                    
                    const status = viewBtn.getAttribute('data-status');
                    document.getElementById('viewProductStatus').textContent = 
                        status === 'active' ? 'نشط' : 
                        status === 'inactive' ? 'غير نشط' : 
                        status === 'low_stock' ? 'منخفض المخزون' : status;
                }
                
                // إخفاء مؤشر التحميل وإظهار المحتوى
                document.getElementById('productDetailLoading').style.display = 'none';
                document.getElementById('productDetailContent').style.display = 'grid';
            }, 500);
        }

        // تحرير المنتج
        function editProduct(productId) {
            // في التطبيق الحقيقي، ستكون هذه بيانات حقيقية من الخادم
            const editBtn = document.querySelector(`.action-btn.edit[data-id="${productId}"]`);
            const viewBtn = document.querySelector(`.action-btn.view[data-id="${productId}"]`);
            
            if (viewBtn) {
                document.getElementById('productModalTitle').textContent = 'تعديل المنتج';
                document.getElementById('productId').value = productId;
                document.getElementById('productName').value = viewBtn.getAttribute('data-name');
                document.getElementById('productDescription').value = viewBtn.getAttribute('data-description') || '';
                
                // ملاحظة: في التطبيق الحقيقي، تحتاج إلى جلب جميع بيانات المنتج عبر AJAX
                
                document.getElementById('saveProduct').textContent = 'تحديث المنتج';
                calculatePrices();
                document.getElementById('productModal').style.display = 'flex';
            }
        }

        // تحرير المنتج من نافذة العرض
        function editProductFromView() {
            closeViewProductModal();
            if (currentProductId) {
                editProduct(currentProductId);
            }
        }

        // إرسال نموذج الاستيراد
        function submitImport() {
            const importFile = document.getElementById('importFile');
            if (!importFile.files.length) {
                alert('يرجى اختيار ملف للاستيراد');
                importFile.style.borderColor = '#dc3545';
                return;
            }
            
            document.getElementById('importForm').submit();
        }

        // سحب وإفلات الصور
        function initDragAndDrop() {
            const uploadArea = document.getElementById('productImagesUpload');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                uploadArea.style.borderColor = '#007bff';
                uploadArea.style.background = '#f0f8ff';
            }
            
            function unhighlight() {
                uploadArea.style.borderColor = '#dee2e6';
                uploadArea.style.background = '#f8f9fa';
            }
            
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length) {
                    const input = document.getElementById('productImages');
                    input.files = files;
                    previewImages(input);
                }
            }
        }

        // مشاركة المنتج
        function shareOnFacebook() {
            const productName = document.getElementById('viewProductName').textContent;
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('اطلع على هذا المنتج: ' + productName);
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + url + '&quote=' + text, '_blank');
        }

        function shareOnTwitter() {
            const productName = document.getElementById('viewProductName').textContent;
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('اطلع على هذا المنتج: ' + productName);
            window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + url, '_blank');
        }

        function shareOnWhatsApp() {
            const productName = document.getElementById('viewProductName').textContent;
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('اطلع على هذا المنتج: ' + productName + ' ' + url);
            window.open('https://api.whatsapp.com/send?text=' + text, '_blank');
        }

        // التحكم في القائمة الجانبية للجوال
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (sidebar) {
                sidebar.classList.toggle('active');
                if (sidebar.classList.contains('active')) {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
                    mobileMenuBtn.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
                } else {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileMenuBtn.style.background = 'linear-gradient(135deg, #007bff, #0056b3)';
                }
            }
        }

        // إغلاق القائمة الجانبية عند النقر خارجها (للجوال)
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (sidebar && sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !mobileMenuBtn.contains(e.target) && 
                window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                mobileMenuBtn.style.background = 'linear-gradient(135deg, #007bff, #0056b3)';
            }
        });

        // إغلاق القائمة عند تغيير حجم النافذة
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth > 768 && sidebar) {
                sidebar.classList.remove('active');
                if (mobileMenuBtn) {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileMenuBtn.style.background = 'linear-gradient(135deg, #007bff, #0056b3)';
                }
            }
        });
    </script>
</body>
</html>