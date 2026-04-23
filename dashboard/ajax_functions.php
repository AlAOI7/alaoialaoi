<?php
session_start();
require_once '../config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

header('Content-Type: application/json');

// التحقق من وجود action
if (!isset($_GET['action'])) {
    echo json_encode(['error' => 'لم يتم تحديد الإجراء']);
    exit();
}

try {
    switch ($_GET['action']) {
        
        // ========== العملاء ==========
        case 'get_customers_list':
            $search = isset($_GET['search']) ? "%{$_GET['search']}%" : "%";
            $sql = "SELECT id, name, phone, email 
                    FROM users 
                    WHERE user_type = 'user' 
                    AND status = 'active' 
                    AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)
                    ORDER BY name 
                    LIMIT 50";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$search, $search, $search]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        case 'get_customer_info':
            if (isset($_GET['customer_id']) && is_numeric($_GET['customer_id'])) {
                $sql = "SELECT id, name, phone, email 
                        FROM users 
                        WHERE id = ? AND user_type = 'user'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['customer_id']]);
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($customer ?: []);
            }
            break;
            
        // ========== الطلبات ==========
        case 'get_customer_orders':
            if (isset($_GET['customer_id']) && is_numeric($_GET['customer_id'])) {
                $sql = "SELECT o.id, o.invoice_number, o.order_date, o.total_amount, o.status, 
                               o.payment_method, o.delivery_method
                        FROM orders o 
                        WHERE o.customer_id = ? 
                        ORDER BY o.order_date DESC 
                        LIMIT 50";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['customer_id']]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
            
        case 'get_order_details':
            if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
                // جلب بيانات الطلب الأساسية
                $sql = "SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email 
                        FROM orders o
                        LEFT JOIN users u ON o.customer_id = u.id
                        WHERE o.id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['order_id']]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($order) {
                    // جلب منتجات الطلب
                    $sql_items = "SELECT oi.*, p.name as original_product_name, 
                                 p.selling_price as original_price,
                                 p.size as original_size,
                                 p.color as original_color
                                 FROM order_items oi
                                 LEFT JOIN products p ON oi.product_id = p.id
                                 WHERE oi.order_id = ?";
                    $stmt_items = $pdo->prepare($sql_items);
                    $stmt_items->execute([$_GET['order_id']]);
                    $order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                }
                
                echo json_encode($order ?: []);
            }
            break;
            
        case 'get_order_products':
            if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
                $sql = "SELECT DISTINCT oi.product_id, oi.product_name, oi.size, oi.color, oi.quantity
                        FROM order_items oi
                        WHERE oi.order_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['order_id']]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
            
        // ========== المنتجات ==========
        case 'get_products_list':
            $search = isset($_GET['search']) ? "%{$_GET['search']}%" : "%";
            $sql = "SELECT id, name, selling_price as price, size, color, quantity 
                    FROM products 
                    WHERE is_active = 1 AND status = 'active' 
                    AND name LIKE ? 
                    ORDER BY name 
                    LIMIT 50";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$search]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        case 'get_product_details':
            if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
                $sql = "SELECT id, name, selling_price, size, color, quantity, 
                               description, barcode, category_id, brand_id
                        FROM products 
                        WHERE id = ? AND is_active = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['product_id']]);
                echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
            }
            break;
            
        case 'get_order_specific_products':
            if (isset($_GET['order_id']) && is_numeric($_GET['order_id']) && 
                isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
                $sql = "SELECT oi.*, p.name as original_product_name, p.selling_price
                        FROM order_items oi
                        LEFT JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ? AND oi.product_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['order_id'], $_GET['product_id']]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;
            
        // ========== المرتجعات ==========
        case 'check_existing_return':
            if (isset($_GET['order_id']) && is_numeric($_GET['order_id']) && 
                isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
                $sql = "SELECT SUM(quantity) as returned_quantity 
                        FROM returns 
                        WHERE order_id = ? AND product_id = ? 
                        AND return_status IN ('pending', 'approved', 'completed')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['order_id'], $_GET['product_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['returned_quantity' => $result['returned_quantity'] ?? 0]);
            }
            break;
            
        // ========== معلومات عامة ==========
        case 'get_product_categories':
            $sql = "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        case 'get_product_brands':
            $sql = "SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
            
        // ========== الإحصائيات ==========
        case 'get_return_stats':
            $stats = [];
            
            // إجمالي المرتجعات
            $sql = "SELECT COUNT(*) as total_returns FROM returns";
            $stmt = $pdo->query($sql);
            $stats['total_returns'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_returns'];
            
            // المرتجعات قيد المعالجة
            $sql = "SELECT COUNT(*) as pending_returns FROM returns WHERE return_status = 'pending'";
            $stmt = $pdo->query($sql);
            $stats['pending_returns'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_returns'];
            
            // المرتجعات المكتملة
            $sql = "SELECT COUNT(*) as completed_returns FROM returns WHERE return_status = 'completed'";
            $stmt = $pdo->query($sql);
            $stats['completed_returns'] = $stmt->fetch(PDO::FETCH_ASSOC)['completed_returns'];
            
            // المرتجعات المرفوضة
            $sql = "SELECT COUNT(*) as rejected_returns FROM returns WHERE return_status = 'rejected'";
            $stmt = $pdo->query($sql);
            $stats['rejected_returns'] = $stmt->fetch(PDO::FETCH_ASSOC)['rejected_returns'];
            
            // قيمة المرتجعات
            $sql = "SELECT SUM(return_amount) as total_amount FROM returns WHERE return_status IN ('completed', 'approved')";
            $stmt = $pdo->query($sql);
            $stats['total_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_amount'] ?? 0;
            
            echo json_encode($stats);
            break;
            
        default:
            echo json_encode(['error' => 'إجراء غير معروف']);
            break;
    }
} catch (PDOException $e) {
    error_log("Database error in ajax_functions.php: " . $e->getMessage());
    echo json_encode(['error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in ajax_functions.php: " . $e->getMessage());
    echo json_encode(['error' => 'حدث خطأ: ' . $e->getMessage()]);
}
?>