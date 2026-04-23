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
    // التحقق من الباركود المكرر أولاً
    $check_sql = "SELECT id FROM products WHERE barcode = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $barcode);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $message = "<div class='message error'>خطأ: الباركود '$barcode' موجود بالفعل في قاعدة البيانات!</div>";
    } else {
        // إضافة منتج جديد
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
    }
    $check_stmt->close();
} else {
    // تعديل منتج موجود
    $product_id = (int)$_POST['product_id'];
    
    // التحقق من الباركود المكرر (استثناء المنتج الحالي)
    $check_sql = "SELECT id FROM products WHERE barcode = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $barcode, $product_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $message = "<div class='message error'>خطأ: الباركود '$barcode' موجود بالفعل لمنتج آخر!</div>";
    } else {
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
    $check_stmt->close();
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
    <link rel="stylesheet" href="style.css">
      <style>
        /* إضافة CSS للنوافذ المنبثقة */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
        }

        .modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* إصلاح CSS للجداول */
        .products-table {
            display: table !important;
            width: 100% !important;
        }

        .products-table th, .products-table td {
            display: table-cell !important;
        }

        /* إصلاح حالة المنتج */
        .status-active { background: #d4edda !important; color: #155724 !important; }
        .status-inactive { background: #f8d7da !important; color: #721c24 !important; }
        .status-low_stock { background: #fff3cd !important; color: #856404 !important; }
        
        /* إصلاح القائمة الجانبية */
        .sidebar {
            display: block !important;
        }
        
        /* إصلاح زر القائمة للجوال */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: flex !important;
            }
        }
    </style>
</head>
<body>
    <!-- بديل في حالة عدم وجود ملفات header و sidebar -->
 
      <header style="background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;">
        <h1 style="margin: 0; font-size: 1.5em;">نظام إدارة المنتجات</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span>مرحباً، <?= $_SESSION['admin_name'] ?? 'المسؤول' ?></span>
            <a href="logout.php" style="color: white; text-decoration: none; background: #dc3545; padding: 8px 15px; border-radius: 5px;">تسجيل خروج</a>
        </div>
    </header>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <div style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h3 style="margin: 0; color: white;">لوحة التحكم</h3>
            </div>
               <nav style="padding: 20px 0; background: #1a1a2e;">
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; justify-content: center;">
                        <!-- لوحة التحكم -->
                        <li><a href="admin_dashboard.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none; border-right: 3px solid #007bff; background: rgba(0,123,255,0.1);"><i class="fas fa-tachometer-alt" style="margin-left: 10px;"></i>لوحة التحكم</a></li>
                        
                        <!-- المنتجات -->
                        <li><a href="products.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-box" style="margin-left: 10px;"></i>المنتجات</a></li>
                        
                        <!-- الفئات -->
                        <li><a href="categories.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-list" style="margin-left: 10px;"></i>الفئات</a></li>
                        
                        <!-- الطلبات -->
                        <li><a href="orders.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-shopping-cart" style="margin-left: 10px;"></i>الطلبات</a></li>
                        
                        <!-- الطلبات الجديدة -->
                        <li><a href="orders_new.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-cart-plus" style="margin-left: 10px;"></i>طلبات جديدة</a></li>
                        
                        <!-- الطلبات المعالجة -->
                        <li><a href="orders_processing.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-cogs" style="margin-left: 10px;"></i>طلبات معالجة</a></li>
                        
                        <!-- العملاء -->
                        <li><a href="customers.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-users" style="margin-left: 10px;"></i>العملاء</a></li>
                        
                        <!-- العملاء الجدد -->
                        <li><a href="new_customers.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-user-plus" style="margin-left: 10px;"></i>عملاء جدد</a></li>
                        
                        <!-- إضافة منتج -->
                        <li><a href="add_product.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-plus-circle" style="margin-left: 10px;"></i>إضافة منتج</a></li>
                        
                        <!-- المشتريات -->
                        <li><a href="purchases.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-shopping-bag" style="margin-left: 10px;"></i>المشتريات</a></li>
                        
                        <!-- المستخدمين -->
                        <li><a href="users.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-user-cog" style="margin-left: 10px;"></i>المستخدمين</a></li>
                        
                        <!-- إضافة مستخدم -->
                        <li><a href="adduser.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-user-plus" style="margin-left: 10px;"></i>إضافة مستخدم</a></li>
                        
                        <!-- المبيعات -->
                        <li><a href="pos.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-cash-register" style="margin-left: 10px;"></i>المبيعات</a></li>
                        
                        <!-- الدردشة -->
                        <li><a href="chat.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-comments" style="margin-left: 10px;"></i>الدردشة</a></li>
                        
                        <!-- المرتجعات -->
                        <li><a href="returns.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-exchange-alt" style="margin-left: 10px;"></i>المرتجعات</a></li>
                        
                        <!-- البنك -->
                        <li><a href="bank.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-university" style="margin-left: 10px;"></i>البنك</a></li>
                        
                        <!-- الموردين -->
                        <li><a href="suppliers.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-truck" style="margin-left: 10px;"></i>الموردين</a></li>
                        
                        <!-- العملات -->
                        <li><a href="currencies.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-money-bill-wave" style="margin-left: 10px;"></i>العملات</a></li>
                        
                        <!-- العلامات التجارية -->
                        <li><a href="brand.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-tag" style="margin-left: 10px;"></i>العلامات التجارية</a></li>
                        
                        <!-- العروض -->
                        <li><a href="offers.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-percentage" style="margin-left: 10px;"></i>العروض</a></li>
                        
                        <!-- المدونة -->
                        <li><a href="blog.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-blog" style="margin-left: 10px;"></i>المدونة</a></li>
                        
                        <!-- الاستيراد والتصدير -->
                        <li><a href="import_export.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-file-import" style="margin-left: 10px;"></i>استيراد وتصدير</a></li>
                        
                        <!-- الإعدادات -->
                        <li><a href="settings.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-cog" style="margin-left: 10px;"></i>الإعدادات</a></li>
                        
                        <!-- طرق الدفع -->
                        <li><a href="payment_methods.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-credit-card" style="margin-left: 10px;"></i>طرق الدفع</a></li>
                        
                        <!-- القسائم -->
                        <li><a href="coupons.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none;"><i class="fas fa-ticket-alt" style="margin-left: 10px;"></i>القسائم</a></li>
                        
                        <!-- تسجيل الخروج -->
                        <li><a href="logout.php" style="display: block; padding: 12px 20px; color: white; text-decoration: none; background: rgba(255,0,0,0.1);"><i class="fas fa-sign-out-alt" style="margin-left: 10px;"></i>تسجيل الخروج</a></li>
                    </ul>
                </nav>

        </div>
        
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
                            <button type="submit" style="background: none; border: none; position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                <i class="fas fa-search"></i>
                            </button>
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
                                            <?php 
                                            $status_class = '';
                                            $status_text = '';
                                            switch($product['status']) {
                                                case 'active':
                                                    $status_class = 'status-active';
                                                    $status_text = 'نشط';
                                                    break;
                                                case 'inactive':
                                                    $status_class = 'status-inactive';
                                                    $status_text = 'غير نشط';
                                                    break;
                                                case 'low_stock':
                                                    $status_class = 'status-low_stock';
                                                    $status_text = 'منخفض';
                                                    break;
                                                default:
                                                    $status_class = 'status-active';
                                                    $status_text = 'نشط';
                                            }
                                            ?>
                                            <span class="product-status <?= $status_class ?>">
                                                <?= $status_text ?>
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
                    <input type="hidden" name="import_products" value="1">
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
        // أضف هذا الكود في صفحة HTML

        // تهيئة الصفحة عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            console.log('الصفحة تم تحميلها بنجاح');
            calculatePrices();
            initEventListeners();
            
            // إخفاء الرسائل بعد 5 ثواني
            setTimeout(() => {
                const messages = document.querySelectorAll('.message');
                messages.forEach(msg => {
                    if (msg) {
                        msg.style.opacity = '0';
                        setTimeout(() => msg.remove(), 500);
                    }
                });
            }, 5000);
        });

        // تهيئة مستمعي الأحداث
        function initEventListeners() {
            console.log('تهيئة مستمعي الأحداث...');
            
            // زر إضافة منتج
            document.getElementById('addProductBtn').addEventListener('click', openAddProductModal);
            
            const addFirstBtn = document.getElementById('addFirstProductBtn');
            if (addFirstBtn) {
                addFirstBtn.addEventListener('click', openAddProductModal);
            }
            
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
            const imagesUpload = document.getElementById('productImagesUpload');
            if (imagesUpload) {
                imagesUpload.addEventListener('click', () => document.getElementById('productImages').click());
            }
            
            const productImages = document.getElementById('productImages');
            if (productImages) {
                productImages.addEventListener('change', previewImages);
            }
            
            // التحكم في الكمية
            const increaseBtn = document.getElementById('increaseQuantity');
            const decreaseBtn = document.getElementById('decreaseQuantity');
            
            if (increaseBtn) {
                increaseBtn.addEventListener('click', () => changeQuantity(1));
            }
            
            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', () => changeQuantity(-1));
            }
            
            // توليد الباركود
            const generateBarcodeBtn = document.getElementById('generateBarcode');
            if (generateBarcodeBtn) {
                generateBarcodeBtn.addEventListener('click', generateBarcode);
            }
            
            // إضافة مقاس ولون
            const addSizeColorBtn = document.getElementById('addSizeColorBtn');
            if (addSizeColorBtn) {
                addSizeColorBtn.addEventListener('click', addSizeColor);
            }
            
            // زر القائمة الجانبية للجوال
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
            }
            
            // تحرير المنتج من نافذة العرض
            const editViewBtn = document.getElementById('editViewProduct');
            if (editViewBtn) {
                editViewBtn.addEventListener('click', editProductFromView);
            }
            
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
            
            // إضافة مستمعات للفورم عند التغيير لحساب الأسعار
            const priceInputs = ['basePrice', 'taxRate', 'oldPrice', 'discount'];
            priceInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.addEventListener('change', calculatePrices);
                    input.addEventListener('input', calculatePrices);
                }
            });
            
            console.log('تم تهيئة جميع مستمعي الأحداث بنجاح');
        }

        // فتح نافذة إضافة منتج
        function openAddProductModal() {
            console.log('فتح نافذة إضافة منتج...');
            document.getElementById('productModalTitle').textContent = 'إضافة منتج جديد';
            const form = document.getElementById('productForm');
            if (form) {
                form.reset();
            }
            document.getElementById('productId').value = '';
            
            const preview = document.getElementById('productImagesPreview');
            if (preview) {
                preview.innerHTML = '';
            }
            
            const sizesContainer = document.getElementById('sizesColorsContainer');
            if (sizesContainer) {
                sizesContainer.innerHTML = `
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
            }
            
            sizeColorCounter = 1;
            calculatePrices();
            
            const modal = document.getElementById('productModal');
            if (modal) {
                modal.style.display = 'flex';
            }
            
            console.log('تم فتح نافذة إضافة المنتج');
        }

        // إغلاق نافذة المنتج
        function closeProductModal() {
            const modal = document.getElementById('productModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // فتح نافذة الاستيراد
        function openImportModal() {
            console.log('فتح نافذة الاستيراد...');
            const modal = document.getElementById('importModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        // إغلاق نافذة الاستيراد
        function closeImportModal() {
            const modal = document.getElementById('importModal');
            if (modal) {
                modal.style.display = 'none';
            }
            const form = document.getElementById('importForm');
            if (form) {
                form.reset();
            }
        }

        // إغلاق نافذة العرض
        function closeViewProductModal() {
            const modal = document.getElementById('viewProductModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // حفظ المنتج
        function saveProduct() {
            console.log('حفظ المنتج...');
            
            // إضافة حقل مخفي لتحديد نوع العملية
            const productId = document.getElementById('productId')?.value;
            const form = document.getElementById('productForm');
            
            if (!form) {
                alert('حدث خطأ في النموذج');
                return;
            }
            
            if (productId) {
                // إذا كان هناك productId، فهذا تعديل
                const updateInput = document.createElement('input');
                updateInput.type = 'hidden';
                updateInput.name = 'update_product';
                updateInput.value = '1';
                form.appendChild(updateInput);
            } else {
                // إذا لم يكن هناك productId، فهذا إضافة
                const addInput = document.createElement('input');
                addInput.type = 'hidden';
                addInput.name = 'add_product';
                addInput.value = '1';
                form.appendChild(addInput);
            }
            
            // التحقق من الحقول المطلوبة
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
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
            
            // إرسال النموذج
            form.submit();
        }

        // معاينة الصور
        function previewImages(event) {
            const input = event.target;
            const preview = document.getElementById('productImagesPreview');
            
            if (!preview) return;
            
            preview.innerHTML = '';
            
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imagePreview = document.createElement('div');
                    imagePreview.className = 'image-preview';
                    imagePreview.innerHTML = `
                        <img src="${e.target.result}" alt="معاينة الصورة">
                        <button type="button" class="remove-image" onclick="removeImage(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(imagePreview);
                }
                
                reader.readAsDataURL(file);
            }
        }

        function removeImage(button) {
            const imagePreview = button.closest('.image-preview');
            if (imagePreview) {
                imagePreview.remove();
            }
        }

        // إضافة مقاس ولون جديد
        function addSizeColor() {
            const container = document.getElementById('sizesColorsContainer');
            if (!container) return;
            
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
                    const removeBtn = item.querySelector('.remove-size-color');
                    if (removeBtn) {
                        removeBtn.style.display = 'block';
                    }
                });
            }
            
            sizeColorCounter++;
        }

        function removeSizeColor(button) {
            const item = button.closest('.size-color-item');
            if (item) {
                item.remove();
            }
            
            // إعادة ترقيم العناصر
            const container = document.getElementById('sizesColorsContainer');
            if (!container) return;
            
            const items = container.querySelectorAll('.size-color-item');
            
            if (items.length === 1) {
                const removeBtn = items[0].querySelector('.remove-size-color');
                if (removeBtn) {
                    removeBtn.style.display = 'none';
                }
            }
        }

        // تحديث اسم اللون
        function updateColorName(input) {
            const colorPreview = input.nextElementSibling;
            const colorNameInput = colorPreview.nextElementSibling;
            
            if (colorPreview) {
                colorPreview.style.backgroundColor = input.value;
            }
            
            // يمكن إضافة منطق لتحديد اسم اللون بناءً على قيمته
        }

        // التحكم في الكمية
        function changeQuantity(amount) {
            const quantityInput = document.getElementById('productQuantity');
            if (!quantityInput) return;
            
            let currentValue = parseInt(quantityInput.value) || 0;
            currentValue += amount;
            
            if (currentValue < 0) currentValue = 0;
            quantityInput.value = currentValue;
        }

        // توليد الباركود
        function generateBarcode() {
            const barcodeInput = document.getElementById('productBarcode');
            if (!barcodeInput) return;
            
            const timestamp = Date.now().toString();
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const barcode = 'PROD-' + timestamp.substr(-8) + '-' + randomNum;
            barcodeInput.value = barcode;
        }
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.querySelector('input[name="barcode"]');
            if (barcodeInput) {
                barcodeInput.addEventListener('blur', function() {
                    const barcode = this.value;
                    if (barcode) {
                        fetch(`check_barcode.php?barcode=${encodeURIComponent(barcode)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.exists) {
                                    alert('تحذير: هذا الباركود موجود بالفعل!');
                                }
                            });
                    }
                });
            }
        });
        // حساب الأسعار
        function calculatePrices() {
            const basePrice = parseFloat(document.getElementById('basePrice')?.value) || 0;
            const taxRate = parseFloat(document.getElementById('taxRate')?.value) || 15;
            const discount = parseFloat(document.getElementById('discount')?.value) || 0;
            
            const taxAmount = basePrice * (taxRate / 100);
            const discountAmount = basePrice * (discount / 100);
            const finalPrice = basePrice + taxAmount - discountAmount;
            
            // تحديث العرض
            const displayBasePrice = document.getElementById('displayBasePrice');
            const displayTaxRate = document.getElementById('displayTaxRate');
            const displayTaxAmount = document.getElementById('displayTaxAmount');
            const displayDiscount = document.getElementById('displayDiscount');
            const displayFinalPrice = document.getElementById('displayFinalPrice');
            
            if (displayBasePrice) displayBasePrice.textContent = basePrice.toFixed(2);
            if (displayTaxRate) displayTaxRate.textContent = taxRate;
            if (displayTaxAmount) displayTaxAmount.textContent = taxAmount.toFixed(2);
            if (displayDiscount) displayDiscount.textContent = discountAmount.toFixed(2);
            if (displayFinalPrice) displayFinalPrice.textContent = finalPrice.toFixed(2);
        }

        // عرض المنتج
        function viewProduct(productId) {
            console.log('عرض المنتج:', productId);
            currentProductId = productId;
            
            // إظهار مؤشر التحميل
            const loading = document.getElementById('productDetailLoading');
            const content = document.getElementById('productDetailContent');
            
            if (loading) loading.style.display = 'block';
            if (content) content.style.display = 'none';
            
            // فتح النافذة
            const modal = document.getElementById('viewProductModal');
            if (modal) {
                modal.style.display = 'flex';
            }
            
            // جلب بيانات المنتج (محاكاة)
            setTimeout(() => {
                const viewBtn = document.querySelector(`.action-btn.view[data-id="${productId}"]`);
                if (viewBtn) {
                    // تحديث البيانات في النافذة
                    const productName = document.getElementById('viewProductName');
                    const productCategory = document.getElementById('viewProductCategory');
                    const productPrice = document.getElementById('viewProductPrice');
                    const oldPrice = document.getElementById('viewOldPrice');
                    const productDescription = document.getElementById('viewProductDescription');
                    const productQuantity = document.getElementById('viewProductQuantity');
                    const productBarcode = document.getElementById('viewProductBarcode');
                    const productStatus = document.getElementById('viewProductStatus');
                    
                    if (productName) productName.textContent = viewBtn.getAttribute('data-name') || '-';
                    if (productCategory) productCategory.textContent = viewBtn.getAttribute('data-category') || '-';
                    
                    const price = viewBtn.getAttribute('data-price') || '0';
                    const currency = viewBtn.getAttribute('data-currency') || '';
                    if (productPrice) productPrice.textContent = price + ' ' + currency;
                    
                    const oldPriceValue = viewBtn.getAttribute('data-old-price');
                    if (oldPrice && oldPriceValue && oldPriceValue !== '0') {
                        oldPrice.textContent = oldPriceValue + ' ' + currency;
                        oldPrice.style.display = 'inline';
                    } else if (oldPrice) {
                        oldPrice.style.display = 'none';
                    }
                    
                    if (productDescription) {
                        const description = viewBtn.getAttribute('data-description');
                        productDescription.textContent = description || 'لا يوجد وصف للمنتج';
                    }
                    
                    if (productQuantity) productQuantity.textContent = viewBtn.getAttribute('data-quantity') || '0';
                    if (productBarcode) productBarcode.textContent = viewBtn.getAttribute('data-barcode') || 'غير متوفر';
                    
                    const status = viewBtn.getAttribute('data-status');
                    let statusText = 'غير معروف';
                    if (status === 'active') statusText = 'نشط';
                    else if (status === 'inactive') statusText = 'غير نشط';
                    else if (status === 'low_stock') statusText = 'منخفض المخزون';
                    
                    if (productStatus) productStatus.textContent = statusText;
                }
                
                // إخفاء مؤشر التحميل وإظهار المحتوى
                if (loading) loading.style.display = 'none';
                if (content) content.style.display = 'grid';
            }, 500);
        }

        // تحرير المنتج
        function editProduct(productId) {
            console.log('تحرير المنتج:', productId);
            
            // في التطبيق الحقيقي، ستكون هذه بيانات حقيقية من الخادم
            const viewBtn = document.querySelector(`.action-btn.view[data-id="${productId}"]`);
            
            if (viewBtn) {
                document.getElementById('productModalTitle').textContent = 'تعديل المنتج';
                document.getElementById('productId').value = productId;
                document.getElementById('productName').value = viewBtn.getAttribute('data-name') || '';
                document.getElementById('productDescription').value = viewBtn.getAttribute('data-description') || '';
                
                // هنا يمكنك إضافة المزيد من الحقول لتعبئتها
                
                document.getElementById('saveProduct').textContent = 'تحديث المنتج';
                
                // فتح النافذة
                const modal = document.getElementById('productModal');
                if (modal) {
                    modal.style.display = 'flex';
                }
            }
        }

        // تحرير المنتج من نافذة العرض
        function editProductFromView() {
            closeViewProductModal();
            if (currentProductId) {
                setTimeout(() => {
                    editProduct(currentProductId);
                }, 300);
            }
        }

        // إرسال نموذج الاستيراد
        function submitImport() {
            const importFile = document.getElementById('importFile');
            if (!importFile || !importFile.files.length) {
                alert('يرجى اختيار ملف للاستيراد');
                return;
            }
            
            document.getElementById('importForm').submit();
        }

        // مشاركة المنتج
        function shareOnFacebook() {
            const productName = document.getElementById('viewProductName')?.textContent || 'منتج';
            const currentUrl = window.location.href;
            const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}&quote=${encodeURIComponent('اطلع على هذا المنتج: ' + productName)}`;
            window.open(shareUrl, '_blank');
        }

        function shareOnTwitter() {
            const productName = document.getElementById('viewProductName')?.textContent || 'منتج';
            const currentUrl = window.location.href;
            const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent('اطلع على هذا المنتج: ' + productName + ' ' + currentUrl)}`;
            window.open(shareUrl, '_blank');
        }

        function shareOnWhatsApp() {
            const productName = document.getElementById('viewProductName')?.textContent || 'منتج';
            const currentUrl = window.location.href;
            const shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent('اطلع على هذا المنتج: ' + productName + ' ' + currentUrl)}`;
            window.open(shareUrl, '_blank');
        }

        // التحكم في القائمة الجانبية للجوال
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (sidebar && mobileMenuBtn) {
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

        // إغلاق القائمة عند النقر خارجها (للجوال)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
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
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth > 768 && sidebar) {
                sidebar.classList.remove('active');
                if (mobileMenuBtn) {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileMenuBtn.style.background = 'linear-gradient(135deg, #007bff, #0056b3)';
                }
            }
        });

        // توفير الدوال للمستمعات في HTML
        window.removeImage = removeImage;
        window.removeSizeColor = removeSizeColor;
        window.updateColorName = updateColorName;
        window.shareOnFacebook = shareOnFacebook;
        window.shareOnTwitter = shareOnTwitter;
        window.shareOnWhatsApp = shareOnWhatsApp;
    </script>
</body>
</html>