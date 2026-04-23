<?php
session_start();
require_once '../config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// معالجة عمليات الإضافة والتحديث والحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleFormSubmissions($pdo);
}

// جلب البيانات من قاعدة البيانات
$returns = getAllReturns($pdo);
$return_stats = getReturnStats($pdo);
$products = getAllProducts($pdo);
$customers = getAllCustomers($pdo);

// ========== الدوال المساعدة ==========

/**
 * معالجة جميع طلبات POST
 */
function handleFormSubmissions($pdo) {
    // إضافة مرتجع جديد
    if (isset($_POST['add_return'])) {
        addReturn($pdo);
    }
    
    // تحديث مرتجع
    if (isset($_POST['update_return'])) {
        updateReturn($pdo);
    }
    
    // حذف مرتجع
    if (isset($_POST['delete_return'])) {
        deleteReturn($pdo);
    }
    
    // تحديث حالة المرتجع
    if (isset($_POST['update_return_status'])) {
        updateReturnStatus($pdo);
    }
}

/**
 * إضافة مرتجع جديد مع الحقول المكتملة
 */
function addReturn($pdo) {
    try {
        // توليد رقم مرتجع تلقائي
        $return_number = generateReturnNumber($pdo);
        
        // جلب بيانات المنتج
        $product = getProductById($pdo, $_POST['product_id']);
        $product_name = $product ? $product['name'] : $_POST['product_name'];
        $unit_price = $product ? $product['selling_price'] : $_POST['unit_price'];
        $size = $_POST['size'] ?? null;
        $color = $_POST['color'] ?? null;
        
        $data = [
            $return_number,
            $_POST['order_id'],
            $_POST['customer_id'],
            $_POST['product_id'],
            $product_name,
            $size,
            $color,
            $_POST['quantity'],
            $unit_price,
            $_POST['return_reason'],
            $_POST['return_status'],
            $_POST['return_amount'],
            $_POST['return_notes'],
            $_SESSION['admin_id']
        ];
        
        $sql = "INSERT INTO returns 
                (return_number, order_id, customer_id, product_id, product_name, size, color, quantity, unit_price, return_reason, return_status, return_amount, return_notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            // إضافة سجل في return_logs
            addReturnLog($pdo, $pdo->lastInsertId(), 'طلب إرجاع', 'تم تقديم طلب الإرجاع');
            
            // تحديث كمية المنتج إذا كان الإرجاع مكتملاً
            if ($_POST['return_status'] == 'completed' && $_POST['product_id']) {
                updateProductQuantity($pdo, $_POST['product_id'], $_POST['quantity']);
            }
            
            $_SESSION['message'] = "تم إضافة المرتجع بنجاح!";
            header("Location: returns.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في إضافة المرتجع: " . $e->getMessage();
        header("Location: returns.php");
        exit();
    }
}

/**
 * تحديث مرتجع - معدل لإضافة الحقول الجديدة
 */
function updateReturn($pdo) {
    try {
        // جلب بيانات المنتج للتحديث
        $product = getProductById($pdo, $_POST['product_id']);
        $product_name = $product ? $product['name'] : $_POST['product_name'];
        $size = $_POST['size'] ?? null;
        $color = $_POST['color'] ?? null;
        
        $data = [
            $_POST['order_id'],
            $_POST['customer_id'],
            $_POST['product_id'],
            $product_name,
            $size,
            $color,
            $_POST['quantity'],
            $_POST['unit_price'],
            $_POST['return_reason'],
            $_POST['return_status'],
            $_POST['return_amount'],
            $_POST['return_notes'],
            $_POST['return_id']
        ];
        
        $sql = "UPDATE returns SET 
                order_id=?, 
                customer_id=?, 
                product_id=?, 
                product_name=?, 
                size=?, 
                color=?, 
                quantity=?, 
                unit_price=?, 
                return_reason=?, 
                return_status=?, 
                return_amount=?, 
                return_notes=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            $_SESSION['message'] = "تم تحديث المرتجع بنجاح!";
            header("Location: returns.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في تحديث المرتجع: " . $e->getMessage();
        header("Location: returns.php");
        exit();
    }
}

/**
 * حذف مرتجع
 */
function deleteReturn($pdo) {
    try {
        $sql = "DELETE FROM returns WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$_POST['return_id']])) {
            $_SESSION['message'] = "تم حذف المرتجع بنجاح!";
            header("Location: returns.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في حذف المرتجع: " . $e->getMessage();
        header("Location: returns.php");
        exit();
    }
}

/**
 * تحديث حالة المرتجع
 */
function updateReturnStatus($pdo) {
    try {
        $return_id = $_POST['return_id'];
        $new_status = $_POST['return_status'];
        $notes = $_POST['status_notes'] ?? '';
        
        $sql = "UPDATE returns SET return_status=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$new_status, $return_id])) {
            // إضافة سجل في return_logs
            $action = getStatusAction($new_status);
            $description = "تم تغيير حالة المرتجع إلى: " . getStatusText($new_status);
            if (!empty($notes)) {
                $description .= " - " . $notes;
            }
            
            addReturnLog($pdo, $return_id, $action, $description);
            
            $_SESSION['message'] = "تم تحديث حالة المرتجع بنجاح!";
            header("Location: returns.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في تحديث حالة المرتجع: " . $e->getMessage();
        header("Location: returns.php");
        exit();
    }
}

/**
 * توليد رقم مرتجع تلقائي
 */
function generateReturnNumber($pdo) {
    $year = date('Y');
    $sql = "SELECT COUNT(*) as count FROM returns WHERE return_number LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["RTN-$year-%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $next_number = $result['count'] + 1;
    return "RTN-$year-" . str_pad($next_number, 3, '0', STR_PAD_LEFT);
}

/**
 * إضافة سجل معالجة المرتجع
 */
function addReturnLog($pdo, $return_id, $action, $description) {
    $sql = "INSERT INTO return_logs (return_id, action, description, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$return_id, $action, $description, $_SESSION['admin_id']]);
}

/**
 * جلب جميع المرتجعات مع بيانات العميل والمنتج
 */
function getAllReturns($pdo) {
    try {
        $sql = "SELECT r.*, 
                       u.name as customer_name, 
                       u.phone as customer_phone,
                       u.email as customer_email,
                       p.name as product_name,
                       p.selling_price as product_price,
                       creator.name as created_by_name
                FROM returns r
                LEFT JOIN users u ON r.customer_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users creator ON r.created_by = creator.id
                ORDER BY r.created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting returns: " . $e->getMessage());
        return [];
    }
}

/**
 * جلب بيانات مرتجع محدد للعرض
 */
function getReturnDetails($pdo, $return_id) {
    try {
        $sql = "SELECT r.*, 
                       u.name as customer_name, 
                       u.phone as customer_phone,
                       u.email as customer_email,
                       p.name as product_name,
                       p.selling_price as product_price,
                       creator.name as created_by_name
                FROM returns r
                LEFT JOIN users u ON r.customer_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users creator ON r.created_by = creator.id
                WHERE r.id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$return_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting return details: " . $e->getMessage());
        return null;
    }
}

/**
 * جلب إحصائيات المرتجعات
 */
function getReturnStats($pdo) {
    try {
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
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error getting return stats: " . $e->getMessage());
        return [
            'total_returns' => 0,
            'pending_returns' => 0,
            'completed_returns' => 0,
            'rejected_returns' => 0,
            'total_amount' => 0
        ];
    }
}

/**
 * جلب جميع المنتجات مع الأسعار الصحيحة
 */
function getAllProducts($pdo) {
    try {
        $sql = "SELECT id, name, selling_price as price, quantity, size, color 
                FROM products 
                WHERE is_active = 1 AND status = 'active' 
                ORDER BY name";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

/**
 * جلب جميع العملاء (من جدول users حيث user_type = 'user')
 */
function getAllCustomers($pdo) {
    try {
        $sql = "SELECT id, name, phone, email 
                FROM users 
                WHERE user_type = 'user'
                AND status = 'active' 
                ORDER BY name";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting customers: " . $e->getMessage());
        return [];
    }
}

/**
 * جلب طلبات العميل مع تفاصيلها
 */
function getCustomerOrders($pdo, $customer_id) {
    try {
        $sql = "SELECT o.id, o.invoice_number, o.order_date, o.total_amount, o.status 
                FROM orders o 
                WHERE o.customer_id = ? 
                ORDER BY o.order_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * جلب سجل معالجة المرتجع
 */
function getReturnLogs($pdo, $return_id) {
    try {
        $sql = "SELECT rl.*, u.name as created_by_name 
                FROM return_logs rl 
                LEFT JOIN users u ON rl.created_by = u.id 
                WHERE rl.return_id = ? 
                ORDER BY rl.created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$return_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting return logs: " . $e->getMessage());
        return [];
    }
}

/**
 * جلب بيانات منتج محدد
 */
function getProductById($pdo, $product_id) {
    try {
        $sql = "SELECT id, name, selling_price, size, color, quantity FROM products WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * تحديث كمية المنتج
 */
function updateProductQuantity($pdo, $product_id, $quantity) {
    try {
        $sql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quantity, $product_id]);
    } catch (PDOException $e) {
        error_log("Error updating product quantity: " . $e->getMessage());
    }
}

/**
 * جلب معلومات الطلب
 */
function getOrderInfo($pdo, $order_id) {
    try {
        $sql = "SELECT o.*, u.name as customer_name 
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                WHERE o.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * الحصول على نص الحالة
 */
function getStatusText($status) {
    $statuses = [
        'pending' => 'قيد المعالجة',
        'approved' => 'معتمد',
        'rejected' => 'مرفوض',
        'completed' => 'مكتمل'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * الحصول على إجراء الحالة
 */
function getStatusAction($status) {
    $actions = [
        'pending' => 'طلب إرجاع',
        'approved' => 'موافقة',
        'rejected' => 'رفض',
        'completed' => 'إكمال'
    ];
    return $actions[$status] ?? $status;
}

/**
 * الحصول على نص سبب الإرجاع
 */
function getReasonText($reason) {
    $reasons = [
        'defective' => 'منتج معيب',
        'wrong-item' => 'منتج خاطئ',
        'damaged' => 'منتج تالف',
        'not-needed' => 'غير مرغوب فيه',
        'other' => 'أسباب أخرى'
    ];
    return $reasons[$reason] ?? $reason;
}

// جلب بيانات مرتجع محدد للتعديل
$edit_return = null;
if (isset($_GET['edit_return'])) {
    $return_id = $_GET['edit_return'];
    $sql = "SELECT * FROM returns WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$return_id]);
    $edit_return = $stmt->fetch(PDO::FETCH_ASSOC);
}

// جلب بيانات مرتجع محدد للعرض
$view_return = null;
$return_logs = [];
if (isset($_GET['view_return'])) {
    $return_id = $_GET['view_return'];
    $view_return = getReturnDetails($pdo, $return_id);
    
    if ($view_return) {
        $return_logs = getReturnLogs($pdo, $return_id);
    }
}

// جلب طلبات العميل إذا تم تحديد عميل
$customer_orders = [];
if (isset($_GET['customer_id']) && is_numeric($_GET['customer_id'])) {
    $customer_orders = getCustomerOrders($pdo, $_GET['customer_id']);
}
?>

    <?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المرتجعات - المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }


        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .logo h1 {
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .logo p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            border-right: 4px solid transparent;
            position: relative;
        }

        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-right-color: var(--accent);
        }

        .menu-item i {
            margin-left: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .menu-item span {
            font-size: 14px;
            font-weight: 500;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-right: 0;
        }

        /* الهيدر */
        .header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 0 20px;
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
            margin-left: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .toggle-sidebar:hover {
            background-color: var(--light);
            color: var(--primary);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .header-icon {
            position: relative;
            margin-left: 15px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .header-icon:hover {
            background-color: var(--light);
        }

        .header-icon i {
            font-size: 18px;
            color: var(--gray);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: linear-gradient(135deg, var(--secondary), var(--warning));
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
            transition: var(--transition);
        }

        .user-profile:hover {
            background-color: var(--light);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            margin-left: 10px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 11px;
            color: var(--gray);
        }

        /* محتوى الصفحة */
        .page-content {
            flex: 1;
            padding: 20px;
        }

        .page-title {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 24px;
            color: var(--dark);
            font-weight: 700;
        }

        .page-title .date {
            color: var(--gray);
            font-size: 14px;
        }

        /* تصميمات خاصة بإدارة المرتجعات */
        .returns-management {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .returns-stats, .returns-list {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .returns-stats h3, .returns-list h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-right: 4px solid var(--primary);
        }

        .stat-label {
            font-weight: 500;
            color: var(--dark);
        }

        .stat-value {
            font-weight: 700;
            font-size: 18px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #f0f0f0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .table td {
            font-size: 13px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn.view {
            background-color: rgba(106, 137, 204, 0.15);
            color: var(--info);
        }

        .action-btn.edit {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .action-btn.delete {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        /* بطاقات الإحصائيات */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 15px;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-info {
            flex: 1;
        }

        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .stat-trend {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .trend-up {
            color: var(--success);
        }

        .trend-down {
            color: var(--secondary);
        }

        .card-1 .stat-icon {
            background: linear-gradient(135deg, #6C63FF, #8A84FF);
            color: white;
        }

        .card-2 .stat-icon {
            background: linear-gradient(135deg, #FF6584, #FF9A76);
            color: white;
        }

        .card-3 .stat-icon {
            background: linear-gradient(135deg, #36D1DC, #4ECDC4);
            color: white;
        }

        .card-4 .stat-icon {
            background: linear-gradient(135deg, #6A89CC, #82CCDD);
            color: white;
        }

        /* الأزرار */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-left: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #3BB5AB);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #3BB5AB, var(--success));
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #FF8A5C);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #FF8A5C, var(--warning));
            box-shadow: 0 5px 15px rgba(255, 154, 118, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--secondary), #FF4D6D);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #FF4D6D, var(--secondary));
            box-shadow: 0 5px 15px rgba(255, 101, 132, 0.3);
        }

        /* الشاشات المنبثقة */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--primary);
            font-size: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .close-modal:hover {
            background-color: var(--light);
            color: var(--dark);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* تفاصيل المرتجع */
        .return-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .return-info, .return-customer {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        .return-info h4, .return-customer h4 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: var(--primary);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark);
        }

        .info-value {
            color: var(--gray);
        }

        /* حالة المرتجع */
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status.pending {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .status.approved {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
        }

        .status.rejected {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .status.completed {
            background-color: rgba(106, 137, 204, 0.15);
            color: var(--info);
        }

        /* زر القائمة للشاشات الصغيرة */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
            z-index: 1001;
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
        }

        /* الرسائل */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
            border: 1px solid rgba(78, 205, 196, 0.3);
        }

        .alert-error {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
            border: 1px solid rgba(255, 101, 132, 0.3);
        }

        /* التجاوب */
        @media (max-width: 1200px) {
            .returns-management {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .mobile-menu-btn {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .header {
                padding: 0 15px;
            }
            
            .user-info {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .page-content {
                padding: 15px;
            }
            
            .header-right {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            
            .header-icon {
                margin-left: 10px;
            }
            
            .page-title {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-title .date {
                margin-top: 5px;
            }
            
            .form-row, .return-details {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                max-width: 95%;
            }
        }

        @media (max-width: 576px) {
            .table {
                display: block;
                overflow-x: auto;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
                margin-left: 10px;
            }
            
            .stat-info h3 {
                font-size: 20px;
            }
        }

        @media (max-width: 400px) {
            .header {
                padding: 0 10px;
            }
            
            .page-content {
                padding: 10px;
            }
            
            .returns-stats, .returns-list {
                padding: 15px;
            }
        }
        .return-details {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 20px;
                    }

                    @media (max-width: 768px) {
                        .return-details {
                            grid-template-columns: 1fr;
                        }
                    }

                    .return-info, .return-customer {
                        background: #f8f9fa;
                        padding: 20px;
                        border-radius: 8px;
                        border: 1px solid #dee2e6;
                    }

                    .return-info h4, .return-customer h4 {
                        margin-bottom: 15px;
                        color: #333;
                        border-bottom: 2px solid #007bff;
                        padding-bottom: 8px;
                    }

                    .info-item {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10px;
                        padding: 8px 0;
                        border-bottom: 1px solid #eee;
                    }

                    .info-label {
                        font-weight: bold;
                        color: #555;
                    }

                    .info-value {
                        color: #333;
                    }

                    .status {
                        padding: 4px 12px;
                        border-radius: 4px;
                        font-size: 14px;
                        font-weight: bold;
                    }

                    .status.pending {
                        background: #fff3cd;
                        color: #856404;
                        border: 1px solid #ffeaa7;
                    }

                    .status.approved {
                        background: #d4edda;
                        color: #155724;
                        border: 1px solid #c3e6cb;
                    }

                    .status.rejected {
                        background: #f8d7da;
                        color: #721c24;
                        border: 1px solid #f5c6cb;
                    }

                    .status.completed {
                        background: #d1ecf1;
                        color: #0c5460;
                        border: 1px solid #bee5eb;
                    }

                    /* طباعة */
                    @media print {
                        .modal-header, .modal-footer {
                            display: none !important;
                        }
                        
                        .modal-content {
                            box-shadow: none !important;
                            border: none !important;
                        }
                        
                        .modal {
                            position: static !important;
                            display: block !important;
                        }
                    }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- الشريط الجانبي -->
         <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
          
            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- عرض الرسائل -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $_SESSION['message'] ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- شاشة المرتجعات -->
                <div id="returns-view" class="returns-view">
                    <div class="page-title">
                        <h2>إدارة المرتجعات</h2>
                        <div class="date"><?= date('l، j F Y') ?></div>
                    </div>

                    <!-- بطاقات الإحصائيات -->
                    <div class="stats-cards">
                        <div class="stat-card card-1">
                            <div class="stat-icon">
                                <i class="fas fa-undo-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $return_stats['total_returns'] ?></h3>
                                <p>إجمالي المرتجعات</p>
                            </div>
                        </div>
                        <div class="stat-card card-2">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $return_stats['pending_returns'] ?></h3>
                                <p>مرتجعات قيد المعالجة</p>
                            </div>
                        </div>
                        <div class="stat-card card-3">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $return_stats['completed_returns'] ?></h3>
                                <p>مرتجعات مكتملة</p>
                            </div>
                        </div>
                        <div class="stat-card card-4">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $return_stats['rejected_returns'] ?></h3>
                                <p>مرتجعات مرفوضة</p>
                            </div>
                        </div>
                    </div>

                    <!-- إدارة المرتجعات -->
                    <div class="returns-management">
                        <div class="returns-stats">
                            <h3>إحصائيات سريعة</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-label">إجمالي المرتجعات</span>
                                    <span class="stat-value"><?= $return_stats['total_returns'] ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">قيد المعالجة</span>
                                    <span class="stat-value"><?= $return_stats['pending_returns'] ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">مكتملة</span>
                                    <span class="stat-value"><?= $return_stats['completed_returns'] ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">مرفوضة</span>
                                    <span class="stat-value"><?= $return_stats['rejected_returns'] ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">قيمة المرتجعات</span>
                                    <span class="stat-value"><?= number_format($return_stats['total_amount'], 2) ?> ر.س</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width: 100%; margin-top: 20px;" id="add-return-btn">
                                <i class="fas fa-plus"></i> إضافة مرتجع جديد
                            </button>
                        </div>

                        <div class="returns-list">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3>قائمة المرتجعات</h3>
                                <div>
                                    <input type="text" class="form-control" placeholder="بحث في المرتجعات..." style="width: 200px;">
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>رقم المرتجع</th>
                                            <th>العميل</th>
                                            <th>المنتج</th>
                                            <th>التاريخ</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($returns as $return): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($return['return_number']) ?></td>
                                            <td><?= htmlspecialchars($return['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($return['product_name']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($return['created_at'])) ?></td>
                                            <td>
                                                <span class="status <?= $return['return_status'] ?>">
                                                    <?= getStatusText($return['return_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?view_return=<?= $return['id'] ?>" class="action-btn view">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="?edit_return=<?= $return['id'] ?>" class="action-btn edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المرتجع؟')">
                                                        <input type="hidden" name="return_id" value="<?= $return['id'] ?>">
                                                        <button type="submit" name="delete_return" class="action-btn delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الشاشات المنبثقة -->
    
  <!-- شاشة إضافة مرتجع -->
<!-- شاشة إضافة مرتجع -->
<!-- شاشة إضافة مرتجع -->
<div class="modal <?= isset($_GET['add_return']) ? 'active' : '' ?>" id="add-return-modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3>إضافة مرتجع جديد</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="add-return-form">
                
                <!-- قسم اختيار العميل -->
                <div class="form-section">
                    <h4>اختيار العميل</h4>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="customer_search">بحث عن العميل</label>
                            <div class="search-input-container">
                                <input type="text" id="customer_search" class="form-control" placeholder="ابحث باسم العميل أو الهاتف أو البريد..." 
                                       onkeyup="searchCustomers(this.value)">
                                <button type="button" class="search-btn" onclick="openCustomersModal()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="customer_id">العميل المحدد</label>
                            <div class="selected-item-display">
                                <select id="customer_id" name="customer_id" class="form-control" required 
                                        onchange="loadCustomerOrders(this.value)">
                                    <option value="">اختر العميل</option>
                                    <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['name']) ?> - <?= $customer['phone'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="customer-info" id="selected-customer-info" style="display: none; margin-top: 5px;">
                                    <small id="customer-details"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- قسم اختيار الطلب (يظهر فقط بعد اختيار العميل) -->
                <div class="form-section" id="order-section" style="display: none;">
                    <h4>اختيار الطلب</h4>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="order_search">بحث في طلبات العميل</label>
                            <div class="search-input-container">
                                <input type="text" id="order_search" class="form-control" placeholder="ابحث برقم الفاتورة..." 
                                       onkeyup="filterOrders(this.value)">
                                <button type="button" class="search-btn" onclick="openOrdersModal()">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="order_id">الطلب المحدد</label>
                            <div class="selected-item-display">
                                <select id="order_id" name="order_id" class="form-control" required 
                                        onchange="loadOrderProducts(this.value)">
                                    <option value="">اختر الطلب</option>
                                    <!-- سيتم تعبئته تلقائياً عند اختيار العميل -->
                                </select>
                                <div class="order-info" id="selected-order-info" style="display: none; margin-top: 5px;">
                                    <small id="order-details"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- تفاصيل الطلب المحدد -->
                    <div id="order-details-container" style="display: none; margin-top: 15px;">
                        <div class="order-summary">
                            <h5>تفاصيل الطلب المحدد</h5>
                            <div id="order-full-details"></div>
                        </div>
                    </div>
                </div>

                <!-- قسم اختيار المنتج (يظهر فقط بعد اختيار الطلب) -->
                <div class="form-section" id="product-section" style="display: none;">
                    <h4>اختيار المنتج</h4>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="product_search">بحث عن المنتج</label>
                            <div class="search-input-container">
                                <input type="text" id="product_search" class="form-control" placeholder="ابحث باسم المنتج..." 
                                       onkeyup="searchProducts(this.value)">
                                <button type="button" class="search-btn" onclick="openProductsModal()">
                                    <i class="fas fa-box"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="product_id">المنتج المحدد</label>
                            <div class="selected-item-display">
                                <select id="product_id" name="product_id" class="form-control" required 
                                        onchange="loadProductDetails(this.value)">
                                    <option value="">اختر المنتج</option>
                                    <!-- سيتم تعبئته تلقائياً عند اختيار الطلب -->
                                </select>
                                <div class="product-info" id="selected-product-info" style="display: none; margin-top: 5px;">
                                    <small id="product-full-details"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- قسم تفاصيل المرتجع (يظهر فقط بعد اختيار المنتج) -->
                <div class="form-section" id="return-details-section" style="display: none;">
                    <h4>تفاصيل المرتجع</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="size">المقاس</label>
                            <input type="text" id="size" name="size" class="form-control" placeholder="المقاس" readonly>
                        </div>
                        <div class="form-group">
                            <label for="color">اللون</label>
                            <input type="text" id="color" name="color" class="form-control" placeholder="اللون" readonly>
                        </div>
                        <div class="form-group">
                            <label for="quantity">الكمية المتوفرة</label>
                            <div class="input-group">
                                <input type="number" id="quantity_available" class="form-control" placeholder="الكمية المتاحة" readonly>
                                <span class="input-group-text">قطعة</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">الكمية المراد إرجاعها</label>
                            <div class="input-group">
                                <input type="number" id="quantity" name="quantity" class="form-control" placeholder="الكمية المراد إرجاعها" required min="1" max="1" oninput="calculateReturnAmount()">
                                <span class="input-group-text">قطعة</span>
                            </div>
                            <small class="text-muted" id="quantity-warning"></small>
                        </div>
                        <div class="form-group">
                            <label for="unit_price">سعر الوحدة</label>
                            <div class="input-group">
                                <input type="number" id="unit_price" name="unit_price" class="form-control" placeholder="سعر الوحدة" required step="0.01" min="0" readonly>
                                <span class="input-group-text">ر.س</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="product_name">اسم المنتج</label>
                            <input type="text" id="product_name" name="product_name" class="form-control" placeholder="اسم المنتج" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="return_reason">سبب الإرجاع</label>
                            <select id="return_reason" name="return_reason" class="form-control" required>
                                <option value="">اختر السبب</option>
                                <option value="defective">منتج معيب</option>
                                <option value="wrong-item">منتج خاطئ</option>
                                <option value="damaged">منتج تالف</option>
                                <option value="not-needed">غير مرغوب فيه</option>
                                <option value="other">أسباب أخرى</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="return_status">حالة المرتجع</label>
                            <select id="return_status" name="return_status" class="form-control" required>
                                <option value="pending">قيد المعالجة</option>
                                <option value="approved">معتمد</option>
                                <option value="rejected">مرفوض</option>
                                <option value="completed">مكتمل</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="return_amount">المبلغ المسترجع</label>
                            <div class="input-group">
                                <input type="number" id="return_amount" name="return_amount" class="form-control" placeholder="المبلغ المسترجع" required step="0.01" min="0" readonly>
                                <span class="input-group-text">ر.س</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="return_notes">ملاحظات إضافية</label>
                        <textarea id="return_notes" name="return_notes" class="form-control" rows="3" placeholder="أدخل أي ملاحظات إضافية"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger close-modal">إلغاء</button>
            <button type="submit" form="add-return-form" name="add_return" class="btn btn-primary" id="submit-return-btn" disabled>إضافة مرتجع</button>
        </div>
    </div>
</div>

<!-- شاشة اختيار المنتجات -->
<div class="modal" id="products-modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3>اختيار المنتج</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="search-box">
                <input type="text" id="products-search-input" class="form-control" placeholder="ابحث عن منتج..." onkeyup="searchProductsModal(this.value)">
            </div>
            <div class="products-list" id="products-list-container" style="max-height: 400px; overflow-y: auto;">
                <!-- سيتم تعبئته بالجافاسكريبت -->
            </div>
        </div>
    </div>
</div>

<!-- شاشة اختيار العملاء -->
<div class="modal" id="customers-modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3>اختيار العميل</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="search-box">
                <input type="text" id="customers-search-input" class="form-control" placeholder="ابحث عن عميل..." onkeyup="searchCustomersModal(this.value)">
            </div>
            <div class="customers-list" id="customers-list-container" style="max-height: 400px; overflow-y: auto;">
                <!-- سيتم تعبئته بالجافاسكريبت -->
            </div>
        </div>
    </div>
</div>

<!-- شاشة اختيار الطلبات -->
<div class="modal" id="orders-modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>طلبات العميل</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="orders-list" id="orders-list-container" style="max-height: 500px; overflow-y: auto;">
                <!-- سيتم تعبئته بالجافاسكريبت -->
            </div>
        </div>
    </div>
</div>


<style>
/* تنسيقات إضافية */
.form-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.form-section h4 {
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.search-input-container {
    position: relative;
    display: flex;
}

.search-input-container input {
    flex: 1;
    padding-right: 40px;
}

.search-btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 40px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
}

.product-item, .customer-item, .order-item {
    padding: 12px 15px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.product-item:hover, .customer-item:hover, .order-item:hover {
    background: #e9ecef;
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.product-name, .customer-name {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.product-details, .customer-details, .order-details {
    display: flex;
    gap: 15px;
    color: #666;
    font-size: 14px;
    flex-wrap: wrap;
}

.price { color: #28a745; font-weight: bold; }
.size, .color { background: #f8f9fa; padding: 2px 8px; border-radius: 4px; }
.quantity { color: #6c757d; }

.order-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.invoice-number {
    font-weight: bold;
    color: #007bff;
}

.order-date {
    color: #666;
}

.total {
    font-weight: bold;
    color: #28a745;
}

.status {
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.status.pending { background: #fff3cd; color: #856404; }
.status.approved { background: #d4edda; color: #155724; }
.status.not_paid { background: #f8d7da; color: #721c24; }
.status.in_delivery { background: #cce5ff; color: #004085; }
.status.completed { background: #d1ecf1; color: #0c5460; }

.order-details-card {
    background: white;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    margin-bottom: 15px;
}

.no-results {
    text-align: center;
    padding: 30px;
    color: #666;
    font-style: italic;
    background: #f8f9fa;
    border-radius: 6px;
}

.selected-item-display {
    position: relative;
}

.input-group {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    width: 100%;
}

.input-group .form-control {
    position: relative;
    flex: 1 1 auto;
    width: 1%;
    min-width: 0;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    text-align: center;
    white-space: nowrap;
    background-color: #e9ecef;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
</style>
<!-- JavaScript للتحكم في الشاشة -->
<script>
        // متغيرات عامة
        let currentCustomerId = null;
        let currentOrderId = null;
        let currentProductId = null;

        // دالة البحث عن العملاء
        function searchCustomers(searchTerm) {
            $.ajax({
                url: 'ajax_functions.php?action=get_customers_list',
                type: 'GET',
                data: { search: searchTerm },
                dataType: 'json',
                success: function(data) {
                    const select = $('#customer_id');
                    select.empty().append('<option value="">اختر العميل</option>');
                    
                    $.each(data, function(index, customer) {
                        select.append(`<option value="${customer.id}">
                            ${customer.name} - ${customer.phone}
                        </option>`);
                    });
                }
            });
        }

        // دالة جلب طلبات العميل
        function loadCustomerOrders(customerId) {
            currentCustomerId = customerId;
            
            if (!customerId) {
                $('#order-section').hide();
                $('#product-section').hide();
                $('#return-details-section').hide();
                $('#submit-return-btn').prop('disabled', true);
                return;
            }
            
            // عرض قسم الطلبات
            $('#order-section').show();
            
            // جلب بيانات العميل
            $.ajax({
                url: 'ajax_functions.php?action=get_customer_info',
                type: 'GET',
                data: { customer_id: customerId },
                dataType: 'json',
                success: function(customer) {
                    if (customer) {
                        $('#selected-customer-info').show();
                        $('#customer-details').html(`
                            ${customer.name} | ${customer.phone} | ${customer.email}
                        `);
                    }
                }
            });
            
            // جلب طلبات العميل
            $.ajax({
                url: 'ajax_functions.php?action=get_customer_orders',
                type: 'GET',
                data: { customer_id: customerId },
                dataType: 'json',
                success: function(data) {
                    const select = $('#order_id');
                    select.empty().append('<option value="">اختر الطلب</option>');
                    
                    if (data.length === 0) {
                        select.append('<option value="" disabled>لا توجد طلبات لهذا العميل</option>');
                        return;
                    }
                    
                    $.each(data, function(index, order) {
                        const orderDate = new Date(order.order_date).toLocaleDateString('ar-SA');
                        select.append(`<option value="${order.id}" 
                            data-invoice="${order.invoice_number}"
                            data-date="${order.order_date}"
                            data-total="${order.total_amount}"
                            data-status="${order.status}">
                            ${order.invoice_number} - ${orderDate} - ${parseFloat(order.total_amount).toFixed(2)} ر.س
                        </option>`);
                    });
                    
                    // إخفاء الأقسام الأخرى
                    $('#product-section').hide();
                    $('#return-details-section').hide();
                }
            });
        }

        // دالة جلب منتجات الطلب
        function loadOrderProducts(orderId) {
            currentOrderId = orderId;
            
            if (!orderId) {
                $('#product-section').hide();
                $('#return-details-section').hide();
                $('#order-details-container').hide();
                $('#submit-return-btn').prop('disabled', true);
                return;
            }
            
            // عرض قسم المنتجات
            $('#product-section').show();
            
            // عرض تفاصيل الطلب
            const selectedOption = $('#order_id option:selected');
            if (selectedOption.length > 0) {
                $('#selected-order-info').show();
                const orderDate = new Date(selectedOption.data('date')).toLocaleDateString('ar-SA');
                $('#order-details').html(`
                    الفاتورة: ${selectedOption.data('invoice')} | 
                    التاريخ: ${orderDate} | 
                    الإجمالي: ${parseFloat(selectedOption.data('total')).toFixed(2)} ر.س
                `);
                
                // عرض تفاصيل كاملة للطلب
                $.ajax({
                    url: 'ajax_functions.php?action=get_order_details',
                    type: 'GET',
                    data: { order_id: orderId },
                    dataType: 'json',
                    success: function(order) {
                        if (order) {
                            $('#order-details-container').show();
                            let orderDetailsHtml = `
                                <div class="order-details-card">
                                    <p><strong>رقم الفاتورة:</strong> ${order.invoice_number}</p>
                                    <p><strong>تاريخ الطلب:</strong> ${new Date(order.order_date).toLocaleDateString('ar-SA')}</p>
                                    <p><strong>المبلغ الإجمالي:</strong> ${parseFloat(order.total_amount).toFixed(2)} ر.س</p>
                                    <p><strong>طريقة الدفع:</strong> ${getPaymentMethodText(order.payment_method)}</p>
                                    <p><strong>الحالة:</strong> ${getOrderStatusText(order.status)}</p>
                                </div>
                            `;
                            
                            if (order.items && order.items.length > 0) {
                                orderDetailsHtml += `
                                    <h6>المنتجات في هذا الطلب:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>المنتج</th>
                                                    <th>المقاس</th>
                                                    <th>اللون</th>
                                                    <th>الكمية</th>
                                                    <th>السعر</th>
                                                    <th>المجموع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                `;
                                
                                $.each(order.items, function(index, item) {
                                    orderDetailsHtml += `
                                        <tr>
                                            <td>${item.product_name || item.original_product_name}</td>
                                            <td>${item.size || 'N/A'}</td>
                                            <td>${item.color || 'N/A'}</td>
                                            <td>${item.quantity}</td>
                                            <td>${parseFloat(item.unit_price).toFixed(2)} ر.س</td>
                                            <td>${parseFloat(item.total_price).toFixed(2)} ر.س</td>
                                        </tr>
                                    `;
                                });
                                
                                orderDetailsHtml += `
                                            </tbody>
                                        </table>
                                    </div>
                                `;
                            }
                            
                            $('#order-full-details').html(orderDetailsHtml);
                            
                            // تعبئة قائمة المنتجات
                            const select = $('#product_id');
                            select.empty().append('<option value="">اختر المنتج</option>');
                            
                            if (order.items && order.items.length > 0) {
                                $.each(order.items, function(index, item) {
                                    const productName = item.original_product_name || item.product_name;
                                    select.append(`<option value="${item.product_id}" 
                                        data-price="${item.unit_price}"
                                        data-name="${productName}"
                                        data-size="${item.size || ''}"
                                        data-color="${item.color || ''}"
                                        data-quantity="${item.quantity}">
                                        ${productName} - ${parseFloat(item.unit_price).toFixed(2)} ر.س
                                    </option>`);
                                });
                            }
                        }
                    }
                });
            }
            
            // إخفاء قسم التفاصيل
            $('#return-details-section').hide();
        }

        // دالة جلب تفاصيل المنتج
        function loadProductDetails(productId) {
            currentProductId = productId;
            
            if (!productId) {
                $('#return-details-section').hide();
                $('#submit-return-btn').prop('disabled', true);
                return;
            }
            
            // عرض قسم التفاصيل
            $('#return-details-section').show();
            
            const selectedOption = $('#product_id option:selected');
            if (selectedOption.length > 0) {
                $('#selected-product-info').show();
                $('#product-full-details').html(`
                    ${selectedOption.data('name')} | 
                    السعر: ${parseFloat(selectedOption.data('price')).toFixed(2)} ر.س
                `);
                
                // تعبئة الحقول
                $('#product_name').val(selectedOption.data('name'));
                $('#unit_price').val(selectedOption.data('price'));
                $('#size').val(selectedOption.data('size') || '');
                $('#color').val(selectedOption.data('color') || '');
                
                // جلب الكمية المتاحة
                const orderQuantity = parseInt(selectedOption.data('quantity')) || 1;
                $('#quantity_available').val(orderQuantity);
                $('#quantity').attr('max', orderQuantity);
                
                // تحذير الكمية
                if (orderQuantity > 0) {
                    $('#quantity-warning').html(`الحد الأقصى للكمية: ${orderQuantity} قطعة`);
                    $('#quantity-warning').css('color', '#28a745');
                } else {
                    $('#quantity-warning').html('الكمية غير متاحة');
                    $('#quantity-warning').css('color', '#dc3545');
                }
                
                // حساب المبلغ
                calculateReturnAmount();
                
                // تفعيل زر الإرسال
                $('#submit-return-btn').prop('disabled', false);
            }
        }

        // دالة حساب مبلغ الإرجاع
        function calculateReturnAmount() {
            const quantity = parseInt($('#quantity').val()) || 0;
            const unitPrice = parseFloat($('#unit_price').val()) || 0;
            const returnAmount = quantity * unitPrice;
            $('#return_amount').val(returnAmount.toFixed(2));
        }

        // دالة فتح شاشة المنتجات
        function openProductsModal() {
            if (!currentOrderId) {
                alert('يرجى اختيار طلب أولاً');
                return;
            }
            
            $('#products-modal').addClass('active');
            loadProductsForModal();
        }

        // دالة فتح شاشة العملاء
        function openCustomersModal() {
            $('#customers-modal').addClass('active');
            loadCustomersForModal();
        }

        // دالة فتح شاشة الطلبات
        function openOrdersModal() {
            if (!currentCustomerId) {
                alert('يرجى اختيار عميل أولاً');
                return;
            }
            
            $('#orders-modal').addClass('active');
            loadOrdersForModal(currentCustomerId);
        }

        // دالة تحميل المنتجات للشاشة المنبثقة
        function loadProductsForModal(searchTerm = '') {
            $.ajax({
                url: 'ajax_functions.php?action=get_products_list',
                type: 'GET',
                data: { search: searchTerm },
                dataType: 'json',
                success: function(products) {
                    const container = $('#products-list-container');
                    container.empty();
                    
                    if (products.length === 0) {
                        container.html('<div class="no-results">لا توجد منتجات</div>');
                        return;
                    }
                    
                    // فلترة المنتجات حسب الطلب المحدد
                    const orderId = $('#order_id').val();
                    if (orderId) {
                        // جلب منتجات الطلب أولاً
                        $.ajax({
                            url: 'ajax_functions.php?action=get_order_products',
                            type: 'GET',
                            data: { order_id: orderId },
                            dataType: 'json',
                            success: function(orderProducts) {
                                let filteredProducts = products;
                                
                                if (orderProducts && orderProducts.length > 0) {
                                    // عرض فقط منتجات هذا الطلب
                                    const orderProductIds = orderProducts.map(p => p.product_id);
                                    filteredProducts = products.filter(p => orderProductIds.includes(parseInt(p.id)));
                                }
                                
                                displayProducts(filteredProducts, container);
                            }
                        });
                    } else {
                        displayProducts(products, container);
                    }
                }
            });
        }

        // دالة عرض المنتجات
        function displayProducts(products, container) {
            $.each(products, function(index, product) {
                const productHtml = `
                    <div class="product-item" onclick="selectProductFromModal(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${(product.size || '').replace(/'/g, "\\'")}', '${(product.color || '').replace(/'/g, "\\'")}', ${product.quantity})">
                        <div class="product-name">${product.name}</div>
                        <div class="product-details">
                            <span class="price">${parseFloat(product.price).toFixed(2)} ر.س</span>
                            <span class="size">${product.size || 'N/A'}</span>
                            <span class="color">${product.color || 'N/A'}</span>
                            <span class="quantity">المخزون: ${product.quantity}</span>
                        </div>
                    </div>
                `;
                container.append(productHtml);
            });
        }

        // دالة تحميل العملاء للشاشة المنبثقة
        function loadCustomersForModal(searchTerm = '') {
            $.ajax({
                url: 'ajax_functions.php?action=get_customers_list',
                type: 'GET',
                data: { search: searchTerm },
                dataType: 'json',
                success: function(customers) {
                    const container = $('#customers-list-container');
                    container.empty();
                    
                    if (customers.length === 0) {
                        container.html('<div class="no-results">لا توجد عملاء</div>');
                        return;
                    }
                    
                    $.each(customers, function(index, customer) {
                        const customerHtml = `
                            <div class="customer-item" onclick="selectCustomerFromModal(${customer.id}, '${customer.name.replace(/'/g, "\\'")}', '${customer.phone}', '${customer.email}')">
                                <div class="customer-name">${customer.name}</div>
                                <div class="customer-details">
                                    <span class="phone">${customer.phone}</span>
                                    <span class="email">${customer.email}</span>
                                </div>
                            </div>
                        `;
                        container.append(customerHtml);
                    });
                }
            });
        }

        // دالة تحميل الطلبات للشاشة المنبثقة
        function loadOrdersForModal(customerId) {
            $.ajax({
                url: 'ajax_functions.php?action=get_customer_orders',
                type: 'GET',
                data: { customer_id: customerId },
                dataType: 'json',
                success: function(orders) {
                    const container = $('#orders-list-container');
                    container.empty();
                    
                    if (orders.length === 0) {
                        container.html('<div class="no-results">لا توجد طلبات لهذا العميل</div>');
                        return;
                    }
                    
                    $.each(orders, function(index, order) {
                        const orderDate = new Date(order.order_date).toLocaleDateString('ar-SA');
                        const orderHtml = `
                            <div class="order-item" onclick="selectOrderFromModal(${order.id}, '${order.invoice_number}', '${order.order_date}', ${order.total_amount}, '${order.status}')">
                                <div class="order-header">
                                    <span class="invoice-number">${order.invoice_number}</span>
                                    <span class="order-date">${orderDate}</span>
                                </div>
                                <div class="order-details">
                                    <span class="total">${parseFloat(order.total_amount).toFixed(2)} ر.س</span>
                                    <span class="status ${order.status}">${getOrderStatusText(order.status)}</span>
                                </div>
                            </div>
                        `;
                        container.append(orderHtml);
                    });
                }
            });
        }

        // دالة اختيار منتج من الشاشة المنبثقة
        function selectProductFromModal(productId, productName, price, size, color, quantity) {
            $('#product_id').val(productId);
            loadProductDetails(productId);
            $('#products-modal').removeClass('active');
        }

        // دالة اختيار عميل من الشاشة المنبثقة
        function selectCustomerFromModal(customerId, customerName, phone, email) {
            $('#customer_id').val(customerId);
            loadCustomerOrders(customerId);
            $('#customers-modal').removeClass('active');
        }

        // دالة اختيار طلب من الشاشة المنبثقة
        function selectOrderFromModal(orderId, invoiceNumber, orderDate, totalAmount, status) {
            $('#order_id').val(orderId);
            loadOrderProducts(orderId);
            $('#orders-modal').removeClass('active');
        }

        // دالة البحث في شاشة المنتجات
        function searchProductsModal(searchTerm) {
            loadProductsForModal(searchTerm);
        }

        // دالة البحث في شاشة العملاء
        function searchCustomersModal(searchTerm) {
            loadCustomersForModal(searchTerm);
        }

        // دالة فلترة الطلبات
        function filterOrders(searchTerm) {
            const orders = $('#order_id option');
            orders.each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchTerm.toLowerCase()));
            });
        }

        // دالة البحث في المنتجات
        function searchProducts(searchTerm) {
            const products = $('#product_id option');
            products.each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchTerm.toLowerCase()));
            });
        }

        // دوال مساعدة
        function getOrderStatusText(status) {
            const statusMap = {
                'pending': 'قيد المعالجة',
                'approved': 'معتمد',
                'not_paid': 'غير مدفوع',
                'in_delivery': 'قيد التوصيل',
                'completed': 'مكتمل'
            };
            return statusMap[status] || status;
        }

        function getPaymentMethodText(method) {
            const methodMap = {
                'credit_card': 'بطاقة ائتمان',
                'bank_transfer': 'تحويل بنكي',
                'cash_on_delivery': 'الدفع عند الاستلام'
            };
            return methodMap[method] || method;
        }
</script>
    <!-- شاشة تعديل مرتجع -->
<!-- شاشة تعديل مرتجع -->
<?php if ($edit_return): ?>
<div class="modal active" id="edit-return-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>تعديل بيانات المرتجع</h3>
            <a href="returns.php" class="close-modal">&times;</a>
        </div>
        <div class="modal-body">
            <form method="POST" id="edit-return-form">
                <input type="hidden" name="return_id" value="<?= $edit_return['id'] ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_order_id">رقم الطلب</label>
                        <input type="text" id="edit_order_id" name="order_id" class="form-control" value="<?= htmlspecialchars($edit_return['order_id']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_customer_id">العميل</label>
                        <select id="edit_customer_id" name="customer_id" class="form-control" required>
                            <option value="">اختر العميل</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= $edit_return['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($customer['name']) ?> - <?= $customer['phone'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_product_id">المنتج</label>
                        <select id="edit_product_id" name="product_id" class="form-control" required>
                            <option value="">اختر المنتج</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" 
                                    data-price="<?= $product['price'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-size="<?= $product['size'] ?? '' ?>"
                                    data-color="<?= $product['color'] ?? '' ?>"
                                    <?= $edit_return['product_id'] == $product['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name']) ?> - <?= number_format($product['price'], 2) ?> ر.س
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_quantity">الكمية</label>
                        <input type="number" id="edit_quantity" name="quantity" class="form-control" value="<?= $edit_return['quantity'] ?>" required min="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_size">المقاس</label>
                        <input type="text" id="edit_size" name="size" class="form-control" value="<?= htmlspecialchars($edit_return['size'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_color">اللون</label>
                        <input type="text" id="edit_color" name="color" class="form-control" value="<?= htmlspecialchars($edit_return['color'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_unit_price">سعر الوحدة</label>
                        <input type="number" id="edit_unit_price" name="unit_price" class="form-control" value="<?= $edit_return['unit_price'] ?>" required step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="edit_product_name">اسم المنتج</label>
                        <input type="text" id="edit_product_name" name="product_name" class="form-control" value="<?= htmlspecialchars($edit_return['product_name']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_return_reason">سبب الإرجاع</label>
                        <select id="edit_return_reason" name="return_reason" class="form-control" required>
                            <option value="defective" <?= $edit_return['return_reason'] == 'defective' ? 'selected' : '' ?>>منتج معيب</option>
                            <option value="wrong-item" <?= $edit_return['return_reason'] == 'wrong-item' ? 'selected' : '' ?>>منتج خاطئ</option>
                            <option value="damaged" <?= $edit_return['return_reason'] == 'damaged' ? 'selected' : '' ?>>منتج تالف</option>
                            <option value="not-needed" <?= $edit_return['return_reason'] == 'not-needed' ? 'selected' : '' ?>>غير مرغوب فيه</option>
                            <option value="other" <?= $edit_return['return_reason'] == 'other' ? 'selected' : '' ?>>أسباب أخرى</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_return_status">حالة المرتجع</label>
                        <select id="edit_return_status" name="return_status" class="form-control" required>
                            <option value="pending" <?= $edit_return['return_status'] == 'pending' ? 'selected' : '' ?>>قيد المعالجة</option>
                            <option value="approved" <?= $edit_return['return_status'] == 'approved' ? 'selected' : '' ?>>معتمد</option>
                            <option value="rejected" <?= $edit_return['return_status'] == 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                            <option value="completed" <?= $edit_return['return_status'] == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_return_amount">المبلغ المسترجع</label>
                        <input type="number" id="edit_return_amount" name="return_amount" class="form-control" value="<?= $edit_return['return_amount'] ?>" required step="0.01" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_return_notes">ملاحظات إضافية</label>
                    <textarea id="edit_return_notes" name="return_notes" class="form-control" rows="3"><?= htmlspecialchars($edit_return['return_notes']) ?></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a href="returns_management.php" class="btn btn-danger">إلغاء</a>
            <button type="submit" form="edit-return-form" name="update_return" class="btn btn-primary">حفظ التعديلات</button>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- شاشة عرض تفاصيل المرتجع -->
  <!-- شاشة عرض تفاصيل المرتجع -->
<?php if ($view_return): ?>
<div class="modal active" id="view-return-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>تفاصيل المرتجع - <?= htmlspecialchars($view_return['return_number']) ?></h3>
            <div>
                <button class="btn btn-primary" id="print-return-details">
                    <i class="fas fa-print"></i> طباعة
                </button>
                <a href="returns.php" class="close-modal">&times;</a>
            </div>
        </div>
        <div class="modal-body">
            <div class="return-details">
                <div class="return-info">
                    <h4>معلومات المرتجع</h4>
                    <div class="info-item">
                        <span class="info-label">رقم المرتجع:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['return_number']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الطلب:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['order_id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المنتج:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['product_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المقاس:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['size'] ?? 'غير محدد') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">اللون:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['color'] ?? 'غير محدد') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الكمية:</span>
                        <span class="info-value"><?= $view_return['quantity'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">سعر الوحدة:</span>
                        <span class="info-value"><?= number_format($view_return['unit_price'], 2) ?> ر.س</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المبلغ المسترجع:</span>
                        <span class="info-value"><?= number_format($view_return['return_amount'], 2) ?> ر.س</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">سبب الإرجاع:</span>
                        <span class="info-value"><?= getReasonText($view_return['return_reason']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التاريخ:</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($view_return['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">آخر تحديث:</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($view_return['updated_at'] ?? $view_return['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الحالة:</span>
                        <span class="info-value">
                            <span class="status <?= $view_return['return_status'] ?>">
                                <?= getStatusText($view_return['return_status']) ?>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="return-customer">
                    <h4>معلومات العميل</h4>
                    <div class="info-item">
                        <span class="info-label">اسم العميل:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['customer_name'] ?? 'غير محدد') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الهاتف:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['customer_phone'] ?? 'غير محدد') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">البريد الإلكتروني:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['customer_email'] ?? 'غير محدد') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">أنشئ بواسطة:</span>
                        <span class="info-value"><?= htmlspecialchars($view_return['created_by_name'] ?? 'غير محدد') ?></span>
                    </div>
                </div>
            </div>
            <div class="return-info" style="margin-top: 20px;">
                <h4>ملاحظات المرتجع</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <p><?= !empty($view_return['return_notes']) ? htmlspecialchars($view_return['return_notes']) : 'لا توجد ملاحظات' ?></p>
                </div>
            </div>
            <div class="return-info" style="margin-top: 20px;">
                <h4>سجل المعالجة</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>الإجراء</th>
                                <th>المستخدم</th>
                                <th>الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($return_logs)): ?>
                                <?php foreach ($return_logs as $log): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td><?= htmlspecialchars($log['created_by_name'] ?? 'نظام') ?></td>
                                    <td><?= htmlspecialchars($log['description']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">لا توجد سجلات معالجة</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="returns.php" class="btn btn-primary">إغلاق</a>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>
<script>
$(document).ready(function() {
    // جلب طلبات العميل عند اختيار العميل
    $('#customer_id, #edit_customer_id').change(function() {
        var customerId = $(this).val();
        var selectId = $(this).attr('id') === 'edit_customer_id' ? '#edit_order_id' : '#order_id';
        
        if (customerId) {
            $.ajax({
                url: 'create_json_data.php?action=get_customer_orders',
                type: 'GET',
                data: { customer_id: customerId },
                dataType: 'json',
                success: function(data) {
                    var orderSelect = $(selectId);
                    orderSelect.empty().append('<option value="">اختر الطلب</option>');
                    
                    $.each(data, function(index, order) {
                        orderSelect.append('<option value="' + order.id + '">' + 
                            order.invoice_number + ' - ' + 
                            new Date(order.order_date).toLocaleDateString('ar-SA') + ' - ' + 
                            parseFloat(order.total_amount).toFixed(2) + ' ر.س</option>');
                    });
                },
                error: function() {
                    console.log('خطأ في جلب طلبات العميل');
                }
            });
        }
    });
    
    // جلب تفاصيل المنتج في إضافة مرتجع
    $('#product_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            $('#unit_price').val(selectedOption.data('price'));
            $('#product_name').val(selectedOption.data('name'));
            $('#size').val(selectedOption.data('size'));
            $('#color').val(selectedOption.data('color'));
            
            // حساب مبلغ الإرجاع
            calculateReturnAmount();
        }
    });
    
    // جلب تفاصيل المنتج في تعديل مرتجع
    $('#edit_product_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            $('#edit_unit_price').val(selectedOption.data('price'));
            $('#edit_product_name').val(selectedOption.data('name'));
            $('#edit_size').val(selectedOption.data('size'));
            $('#edit_color').val(selectedOption.data('color'));
            
            // حساب مبلغ الإرجاع في التعديل
            calculateEditReturnAmount();
        }
    });
    
    // حساب مبلغ الإرجاع في إضافة مرتجع
    $('#quantity, #unit_price').on('input', function() {
        calculateReturnAmount();
    });
    
    // حساب مبلغ الإرجاع في تعديل مرتجع
    $('#edit_quantity, #edit_unit_price').on('input', function() {
        calculateEditReturnAmount();
    });
    
    function calculateReturnAmount() {
        var quantity = parseInt($('#quantity').val()) || 0;
        var unitPrice = parseFloat($('#unit_price').val()) || 0;
        var returnAmount = quantity * unitPrice;
        $('#return_amount').val(returnAmount.toFixed(2));
    }
    
    function calculateEditReturnAmount() {
        var quantity = parseInt($('#edit_quantity').val()) || 0;
        var unitPrice = parseFloat($('#edit_unit_price').val()) || 0;
        var returnAmount = quantity * unitPrice;
        $('#edit_return_amount').val(returnAmount.toFixed(2));
    }
    
    // طباعة تفاصيل المرتجع
    $('#print-return-details').click(function() {
        window.print();
    });
    
    // إذا كان هناك إرجاع قيد التعديل، احسب المبلغ عند التحميل
    <?php if ($edit_return): ?>
    $(document).ready(function() {
        var quantity = parseInt($('#edit_quantity').val()) || 0;
        var unitPrice = parseFloat($('#edit_unit_price').val()) || 0;
        var returnAmount = quantity * unitPrice;
        $('#edit_return_amount').val(returnAmount.toFixed(2));
    });
    <?php endif; ?>
});
</script>
    <script>
        // التحكم في الشريط الجانبي
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // التحكم في القوائم
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                // إزالة النشاط من جميع العناصر
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // إضافة النشاط للعنصر المحدد
                this.classList.add('active');
                
                // تحديث عنوان الصفحة
                if (this.id === 'returns-menu') {
                    document.getElementById('page-title').textContent = 'إدارة المرتجعات';
                } else if (this.id === 'admin-menu') {
                    document.getElementById('page-title').textContent = 'لوحة التحكم';
                } else if (this.id === 'supplier-menu') {
                    document.getElementById('page-title').textContent = 'إدارة الموردين';
                }
            });
        });

        // التحكم في الشاشات المنبثقة
        const addReturnModal = document.getElementById('add-return-modal');
        const editReturnModal = document.getElementById('edit-return-modal');
        const viewReturnModal = document.getElementById('view-return-modal');

        // فتح شاشة إضافة مرتجع
        document.getElementById('add-return-btn').addEventListener('click', function() {
            addReturnModal.classList.add('active');
        });

        // إغلاق الشاشات المنبثقة
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                addReturnModal.classList.remove('active');
            });
        });

        // طباعة تفاصيل المرتجع
        document.getElementById('print-return-details')?.addEventListener('click', function() {
            window.print();
        });

        // البحث في الجدول
        document.querySelector('input[placeholder="بحث في المرتجعات..."]').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // حساب المبلغ التلقائي عند اختيار المنتج والكمية
        document.getElementById('product_id')?.addEventListener('change', function() {
            calculateReturnAmount();
        });

        document.getElementById('quantity')?.addEventListener('input', function() {
            calculateReturnAmount();
        });

        function calculateReturnAmount() {
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const amountInput = document.getElementById('return_amount');
            
            if (productSelect && quantityInput && amountInput) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const productText = selectedOption.text;
                    const priceMatch = productText.match(/(\d+\.?\d*)\s*ر\.س/);
                    
                    if (priceMatch) {
                        const price = parseFloat(priceMatch[1]);
                        const quantity = parseInt(quantityInput.value) || 0;
                        const totalAmount = price * quantity;
                        amountInput.value = totalAmount.toFixed(2);
                    }
                }
            }
        }

        // إغلاق الرسائل تلقائياً بعد 5 ثوانٍ
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>