<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // التحقق من البيانات المطلوبة
    if (!isset($_POST['product_id'])) {
        throw new Exception('معرف المنتج مطلوب');
    }
    
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1; // الكمية الافتراضية = 1
    
    if ($product_id <= 0) {
        throw new Exception('معرف منتج غير صحيح');
    }
    
    if ($quantity <= 0) {
        $quantity = 1; // التأكد من أن الكمية على الأقل 1
    }
    
    // جلب بيانات المنتج
    $product_sql = "SELECT id, name, selling_price, COALESCE(stock, quantity) as stock 
                    FROM products 
                    WHERE id = ? AND (is_active = 1 OR status = 'active')";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        throw new Exception('المنتج غير موجود أو غير متاح');
    }
    
    // التحقق من المخزون
    if ($product['stock'] < $quantity) {
        throw new Exception('الكمية المطلوبة غير متوفرة في المخزون');
    }
    
    // جلب الصورة الرئيسية
    $image_sql = "SELECT image_path FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1";
    $img_stmt = $conn->prepare($image_sql);
    $img_stmt->bind_param("i", $product_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result()->fetch_assoc();
    $product['image'] = $img_result['image_path'] ?? ($product['image'] ?? 'img/default.jpg');
    
    // التحقق من تسجيل الدخول
    $is_logged_in = isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    
    if ($is_logged_in) {
        // للمستخدمين المسجلين: حفظ في قاعدة البيانات
        $user_id = $_SESSION['user_id'];
        
        // التحقق من وجود المنتج في السلة
        $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // تحديث الكمية - زيادة بمقدار الكمية المطلوبة (ليس استبدال)
            $new_quantity = $existing['quantity'] + $quantity;
            
            // التحقق من المخزون
            if ($new_quantity > $product['stock']) {
                throw new Exception('الكمية الإجمالية تتجاوز المخزون المتاح');
            }
            
            $update_sql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_quantity, $existing['id']);
            $update_stmt->execute();
            
            $message = 'تم تحديث الكمية في السلة';
        } else {
            // إضافة منتج جديد
            $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $insert_stmt->execute();
            
            $message = 'تمت الإضافة إلى السلة بنجاح';
        }
        
        // حساب عدد المنتجات في السلة
        $count_sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $cart_count = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
        
    } else {
        // للزوار: استخدام الجلسة
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // البحث عن المنتج في السلة
        $found = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $product_id) {
                // زيادة الكمية
                $new_quantity = $item['quantity'] + $quantity;
                
                if ($new_quantity > $product['stock']) {
                    throw new Exception('الكمية الإجمالية تتجاوز المخزون المتاح');
                }
                
                $_SESSION['cart'][$key]['quantity'] = $new_quantity;
                $found = true;
                $message = 'تم تحديث الكمية في السلة';
                break;
            }
        }
        
        if (!$found) {
            // إضافة منتج جديد
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'selling_price' => $product['selling_price'],
                'quantity' => $quantity,
                'image' => $product['image']
            ];
            $message = 'تمت الإضافة إلى السلة بنجاح';
        }
        
        // حساب العدد
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $cart_count,
        'product_name' => $product['name']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>