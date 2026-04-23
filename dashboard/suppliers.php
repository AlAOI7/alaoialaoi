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

// معالجة العمليات
$message = "";
$message_type = ""; // success, error
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_supplier'])) {
        // إضافة مورد جديد
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $account_number = $conn->real_escape_string($_POST['account_number']);
        $type = $conn->real_escape_string($_POST['type']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        // التحقق من عدم وجود المورد بنفس الاسم أو الهاتف
        $check_sql = "SELECT id FROM suppliers WHERE name = '$name' OR phone = '$phone'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $message = "المورد موجود بالفعل بنفس الاسم أو رقم الهاتف!";
            $message_type = "error";
        } else {
            $sql = "INSERT INTO suppliers (name, phone, email, address, account_number, type, notes) 
                    VALUES ('$name', '$phone', '$email', '$address', '$account_number', '$type', '$notes')";
            
            if ($conn->query($sql)) {
                $message = "تم إضافة المورد بنجاح!";
                $message_type = "success";
                header("Location: suppliers.php?message=" . urlencode($message) . "&type=" . $message_type);
                exit();
            } else {
                $message = "خطأ في إضافة المورد: " . $conn->error;
                $message_type = "error";
            }
        }
    } 
    elseif (isset($_POST['update_supplier'])) {
        // تعديل مورد
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $account_number = $conn->real_escape_string($_POST['account_number']);
        $type = $conn->real_escape_string($_POST['type']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        $sql = "UPDATE suppliers SET 
                name='$name', 
                phone='$phone', 
                email='$email', 
                address='$address', 
                account_number='$account_number', 
                type='$type', 
                notes='$notes' 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            $message = "تم تعديل المورد بنجاح!";
            $message_type = "success";
            header("Location: suppliers.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في تعديل المورد: " . $conn->error;
            $message_type = "error";
        }
    } 
    elseif (isset($_POST['delete_supplier'])) {
        // حذف مورد
        $id = (int)$_POST['id'];
        
        // حذف المنتجات المرتبطة أولاً
        $conn->query("DELETE FROM supplier_products WHERE supplier_id = $id");
        // حذف المعاملات المرتبطة
        $conn->query("DELETE FROM supplier_transactions WHERE supplier_id = $id");
        // حذف تحديثات الرصيد المرتبطة
        $conn->query("DELETE FROM supplier_balance_updates WHERE supplier_id = $id");
        
        $sql = "DELETE FROM suppliers WHERE id=$id";
        if ($conn->query($sql)) {
            $message = "تم حذف المورد بنجاح!";
            $message_type = "success";
            header("Location: suppliers.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في حذف المورد: " . $conn->error;
            $message_type = "error";
        }
    }
    elseif (isset($_POST['add_product'])) {
        // إضافة منتج للمورد
        $supplier_id = (int)$_POST['supplier_id'];
        $product_name = $conn->real_escape_string($_POST['product_name']);
        $price = floatval($_POST['price']);
        $category = $conn->real_escape_string($_POST['category']);
        $store_stock = (int)$_POST['store_stock'];
        $supplier_stock = (int)$_POST['supplier_stock'];
        
        $sql = "INSERT INTO supplier_products (supplier_id, product_name, price, category, store_stock, supplier_stock) 
                VALUES ($supplier_id, '$product_name', $price, '$category', $store_stock, $supplier_stock)";
        
        if ($conn->query($sql)) {
            // تحديث عدد المنتجات الإجمالي للمورد
            $conn->query("UPDATE suppliers SET total_products = total_products + 1 WHERE id=$supplier_id");
            $message = "تم إضافة المنتج بنجاح!";
            $message_type = "success";
            header("Location: suppliers.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في إضافة المنتج: " . $conn->error;
            $message_type = "error";
        }
    }
    elseif (isset($_POST['add_transaction'])) {
        // إضافة معاملة
        $supplier_id = (int)$_POST['supplier_id'];
        $type = $conn->real_escape_string($_POST['type']);
        $amount = floatval($_POST['amount']);
        $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : NULL;
        $quantity = (int)$_POST['quantity'];
        $notes = $conn->real_escape_string($_POST['notes']);
        $transaction_date = $_POST['transaction_date'];
        
        $sql = "INSERT INTO supplier_transactions (supplier_id, type, amount, product_id, quantity, notes, transaction_date) 
                VALUES ($supplier_id, '$type', $amount, '$product_id', $quantity, '$notes', '$transaction_date')";
        
        if ($conn->query($sql)) {
            // تحديث رصيد المورد بناءً على نوع المعاملة
            $balance_change = 0;
            if ($type == 'purchase') {
                $balance_change = -$amount; // زيادة المديونية
            } elseif ($type == 'payment') {
                $balance_change = $amount; // تقليل المديونية
            } elseif ($type == 'return') {
                $balance_change = $amount; // تقليل المديونية
            } elseif ($type == 'receipt') {
                $balance_change = 0; // استلام لا يؤثر على الرصيد
            }
            
            if ($balance_change != 0) {
                $conn->query("UPDATE suppliers SET balance = balance + $balance_change WHERE id=$supplier_id");
            }
            
            $message = "تم تسجيل المعاملة بنجاح!";
            $message_type = "success";
            header("Location: suppliers.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في تسجيل المعاملة: " . $conn->error;
            $message_type = "error";
        }
    }
    elseif (isset($_POST['update_balance'])) {
        // تحديث رصيد المورد
        $supplier_id = (int)$_POST['supplier_id'];
        $update_type = $conn->real_escape_string($_POST['update_type']);
        $amount = floatval($_POST['amount']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        // حفظ تحديث الرصيد في جدول مستقل
        $sql = "INSERT INTO supplier_balance_updates (supplier_id, update_type, amount, notes) 
                VALUES ($supplier_id, '$update_type', $amount, '$notes')";
        
        if ($conn->query($sql)) {
            // تحديث رصيد المورد بناءً على نوع التحديث
            $balance_change = 0;
            if ($update_type == 'debt') {
                $balance_change = -$amount; // زيادة المديونية
            } elseif ($update_type == 'credit') {
                $balance_change = $amount; // تقليل المديونية
            } elseif ($update_type == 'payment') {
                $balance_change = $amount; // تقليل المديونية
            }
            
            if ($balance_change != 0) {
                $conn->query("UPDATE suppliers SET balance = balance + $balance_change WHERE id=$supplier_id");
            }
            
            $message = "تم تحديث الرصيد بنجاح!";
            $message_type = "success";
            header("Location: suppliers.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في تحديث الرصيد: " . $conn->error;
            $message_type = "error";
        }
    }
}

// عرض الرسالة من URL إذا وجدت
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'];
}

// جلب البيانات للعرض
// إحصائيات الموردين
$total_suppliers = $conn->query("SELECT COUNT(*) as total FROM suppliers")->fetch_assoc()['total'] ?? 0;
$total_products = $conn->query("SELECT SUM(total_products) as total FROM suppliers")->fetch_assoc()['total'] ?? 0;
$total_balance = $conn->query("SELECT SUM(balance) as total FROM suppliers")->fetch_assoc()['total'] ?? 0;
$total_transactions = $conn->query("SELECT COUNT(*) as total FROM supplier_transactions")->fetch_assoc()['total'] ?? 0;

// جلب الموردين للعرض
$where_clause = "";
if ($search) {
    $where_clause = " WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%'";
}

$suppliers_result = $conn->query("SELECT * FROM suppliers $where_clause ORDER BY created_at DESC");

// أنواع الموردين
$supplier_types = ['electronics' => 'إلكترونيات', 'home' => 'أثاث منزلي', 'office' => 'قرطاسية ومكتب', 'other' => 'أخرى'];

// فئات المنتجات
$product_categories = ['electronics' => 'إلكترونيات', 'home' => 'أثاث منزلي', 'office' => 'قرطاسية ومكتب', 'clothing' => 'ملابس', 'food' => 'مواد غذائية'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الموردين</title>
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
            min-height: 100vh;
            direction: rtl;
        }

        /* تنسيق لوحة التحكم */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f5f7fa;
        }
        
        .page-content {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .page-title h2 {
            color: var(--dark);
            font-size: 24px;
            font-weight: 600;
        }
        
        .page-title .date {
            color: #666;
            font-size: 14px;
        }

        /* رسالة التأكيد */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* إحصائيات سريعة */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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

        .stat-trend {
            font-size: 0.85em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }

        /* إدارة الموردين */
        .supplier-management {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .supplier-stats {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .supplier-stats h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .stats-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }

        .stat-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .balance-positive {
            color: var(--success);
        }

        .balance-negative {
            color: var(--danger);
        }

        .supplier-list {
            background: white;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .supplier-list h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 800px;
        }

        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 15px;
            text-align: right;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            position: sticky;
            top: 0;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
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
        .action-btn.operations:hover { background: #17a2b8; color: white; }
        .action-btn.edit:hover { background: #28a745; color: white; }
        .action-btn.delete:hover { background: #dc3545; color: white; }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-warning { background: var(--warning); color: #212529; }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
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

        /* ===================== */
        /* تنسيق النوافذ المنبثقة */
        /* ===================== */
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.3rem;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #777;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .close-modal:hover {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            position: sticky;
            bottom: 0;
            background: white;
            border-radius: 0 0 10px 10px;
        }

        /* تفاصيل المورد */
        .supplier-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .supplier-info, .supplier-financial {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .supplier-info h4, .supplier-financial h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
        }

        /* تبويبات العمليات */
        .operations-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            flex-wrap: wrap;
        }

        .operations-tab {
            padding: 10px 20px;
            background: #f8f9fa;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .operations-tab.active {
            background: #007bff;
            color: white;
        }

        .operations-tab:hover:not(.active) {
            background: #e9ecef;
        }

        .operations-content {
            display: none;
        }

        .operations-content.active {
            display: block;
        }

        .product-form, .transaction-form, .balance-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .product-form h4, .transaction-form h4, .balance-form h4 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .report-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 1024px) {
            .supplier-management {
                grid-template-columns: 1fr;
            }
            
            .supplier-details {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .operations-tabs {
                flex-direction: column;
            }
            
            .report-actions {
                flex-direction: column;
            }
            
            .table {
                font-size: 0.9em;
            }
            
            .table th, .table td {
                padding: 10px 5px;
            }
            
            .action-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <?php include 'header.php'; ?>

            <div class="page-content">
                <div class="page-title">
                    <h2>نظام إدارة الموردين</h2>
                    <div class="date"><?php echo date('l، j F Y'); ?></div>
                </div>

                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <!-- بطاقات الإحصائيات -->
                <div class="stats-cards">
                    <div class="stat-card card-1">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $total_suppliers ?></h3>
                            <p>عدد الموردين</p>
                        </div>
                    </div>
                    <div class="stat-card card-2">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $total_products ?></h3>
                            <p>المنتجات من الموردين</p>
                        </div>
                    </div>
                    <div class="stat-card card-3">
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= number_format(abs($total_balance), 2) ?> ر.س</h3>
                            <p>مديونية الموردين</p>
                        </div>
                    </div>
                    <div class="stat-card card-4">
                        <div class="stat-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $total_transactions ?></h3>
                            <p>فواتير الموردين</p>
                        </div>
                    </div>
                </div>

                <!-- إدارة الموردين -->
                <div class="supplier-management">
                    <div class="supplier-stats">
                        <h3>إحصائيات سريعة</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label">إجمالي الموردين</span>
                                <span class="stat-value"><?= $total_suppliers ?></span>
                            </div>
                            <?php 
                            $active_suppliers = $conn->query("SELECT COUNT(*) as total FROM suppliers WHERE status='active'")->fetch_assoc()['total'] ?? 0;
                            ?>
                            <div class="stat-item">
                                <span class="stat-label">موردين نشطين</span>
                                <span class="stat-value"><?= $active_suppliers ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">مديونية الموردين</span>
                                <span class="stat-value balance-negative"><?= number_format(abs($total_balance), 2) ?> ر.س</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" style="width: 100%; margin-top: 20px;" id="add-supplier-btn">
                            <i class="fas fa-plus"></i> إضافة مورد جديد
                        </button>
                    </div>

                    <div class="supplier-list">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                            <h3>قائمة الموردين</h3>
                            <div>
                                <form method="GET" style="display: flex; gap: 10px;">
                                    <input type="text" class="form-control" name="search" placeholder="بحث عن مورد..." 
                                           value="<?= htmlspecialchars($search) ?>" style="width: 200px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if ($search): ?>
                                        <a href="?" class="btn btn-danger">إلغاء</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>اسم المورد</th>
                                        <th>رقم الهاتف</th>
                                        <th>المنتجات</th>
                                        <th>المديونية</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($suppliers_result && $suppliers_result->num_rows > 0): ?>
                                        <?php while($supplier = $suppliers_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($supplier['name']) ?></td>
                                                <td><?= htmlspecialchars($supplier['phone']) ?></td>
                                                <td><?= $supplier['total_products'] ?> منتج</td>
                                                <td class="<?= $supplier['balance'] < 0 ? 'balance-negative' : 'balance-positive' ?>">
                                                    <?= number_format(abs($supplier['balance']), 2) ?> ر.س
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="action-btn view" 
                                                                data-id="<?= $supplier['id'] ?>"
                                                                data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                                                data-phone="<?= htmlspecialchars($supplier['phone']) ?>"
                                                                data-email="<?= htmlspecialchars($supplier['email']) ?>"
                                                                data-address="<?= htmlspecialchars($supplier['address']) ?>"
                                                                data-account="<?= htmlspecialchars($supplier['account_number']) ?>"
                                                                data-type="<?= htmlspecialchars($supplier['type']) ?>"
                                                                data-notes="<?= htmlspecialchars($supplier['notes']) ?>"
                                                                data-balance="<?= $supplier['balance'] ?>"
                                                                data-products="<?= $supplier['total_products'] ?>"
                                                                data-created="<?= $supplier['created_at'] ?>"
                                                                data-status="<?= $supplier['status'] ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="action-btn operations" 
                                                                data-id="<?= $supplier['id'] ?>"
                                                                data-name="<?= htmlspecialchars($supplier['name']) ?>">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <button class="action-btn edit" 
                                                                data-id="<?= $supplier['id'] ?>"
                                                                data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                                                data-phone="<?= htmlspecialchars($supplier['phone']) ?>"
                                                                data-email="<?= htmlspecialchars($supplier['email']) ?>"
                                                                data-address="<?= htmlspecialchars($supplier['address']) ?>"
                                                                data-account="<?= htmlspecialchars($supplier['account_number']) ?>"
                                                                data-type="<?= htmlspecialchars($supplier['type']) ?>"
                                                                data-notes="<?= htmlspecialchars($supplier['notes']) ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="action-btn delete" 
                                                                data-id="<?= $supplier['id'] ?>"
                                                                data-name="<?= htmlspecialchars($supplier['name']) ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                                                <?= $search ? 'لم يتم العثور على موردين يطابقون بحثك' : 'لا توجد موردين مضافة بعد' ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($suppliers_result && $suppliers_result->num_rows == 0 && !$search): ?>
                            <div style="text-align: center; padding: 20px; color: #666;">
                                <p>لا توجد موردين مضافة بعد. <a href="#" id="add-first-supplier">إضافة أول مورد</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== -->
    <!-- النوافذ المنبثقة -->
    <!-- ==================== -->

    <!-- شاشة إضافة مورد -->
    <div class="modal" id="add-supplier-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إضافة مورد جديد</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="add-supplier-form">
                    <input type="hidden" name="add_supplier" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier-name">اسم المورد *</label>
                            <input type="text" id="supplier-name" name="name" class="form-control" placeholder="أدخل اسم المورد" required>
                        </div>
                        <div class="form-group">
                            <label for="supplier-phone">رقم الهاتف *</label>
                            <input type="text" id="supplier-phone" name="phone" class="form-control" placeholder="أدخل رقم الهاتف" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier-email">البريد الإلكتروني</label>
                            <input type="email" id="supplier-email" name="email" class="form-control" placeholder="أدخل البريد الإلكتروني">
                        </div>
                        <div class="form-group">
                            <label for="supplier-address">عنوان المتجر</label>
                            <input type="text" id="supplier-address" name="address" class="form-control" placeholder="أدخل عنوان المتجر">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier-account">حساب المورد</label>
                            <input type="text" id="supplier-account" name="account_number" class="form-control" placeholder="أدخل رقم الحساب">
                        </div>
                        <div class="form-group">
                            <label for="supplier-type">نوع المورد</label>
                            <select id="supplier-type" name="type" class="form-control">
                                <?php foreach($supplier_types as $value => $label): ?>
                                    <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="supplier-notes">ملاحظات إضافية</label>
                        <textarea id="supplier-notes" name="notes" class="form-control" rows="3" placeholder="أدخل أي ملاحظات إضافية"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger close-modal">إلغاء</button>
                <button class="btn btn-primary" id="submit-add-supplier">إضافة مورد</button>
            </div>
        </div>
    </div>

    <!-- شاشة تعديل مورد -->
    <div class="modal" id="edit-supplier-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تعديل بيانات المورد</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="edit-supplier-form">
                    <input type="hidden" id="edit-supplier-id" name="id">
                    <input type="hidden" name="update_supplier" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-supplier-name">اسم المورد *</label>
                            <input type="text" id="edit-supplier-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-supplier-phone">رقم الهاتف *</label>
                            <input type="text" id="edit-supplier-phone" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-supplier-email">البريد الإلكتروني</label>
                            <input type="email" id="edit-supplier-email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-supplier-address">عنوان المتجر</label>
                            <input type="text" id="edit-supplier-address" name="address" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-supplier-account">حساب المورد</label>
                            <input type="text" id="edit-supplier-account" name="account_number" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-supplier-type">نوع المورد</label>
                            <select id="edit-supplier-type" name="type" class="form-control">
                                <?php foreach($supplier_types as $value => $label): ?>
                                    <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-supplier-notes">ملاحظات إضافية</label>
                        <textarea id="edit-supplier-notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger close-modal">إلغاء</button>
                <button class="btn btn-primary" id="submit-edit-supplier">حفظ التعديلات</button>
            </div>
        </div>
    </div>

    <!-- شاشة عرض تفاصيل المورد -->
    <div class="modal" id="view-supplier-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تفاصيل المورد</h3>
                <div>
                    <button class="close-modal">&times;</button>
                </div>
            </div>
            <div class="modal-body">
                <div class="supplier-details">
                    <div class="supplier-info">
                        <h4>المعلومات الأساسية</h4>
                        <div class="info-item">
                            <span class="info-label">اسم المورد:</span>
                            <span class="info-value" id="view-supplier-name">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">رقم الهاتف:</span>
                            <span class="info-value" id="view-supplier-phone">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">البريد الإلكتروني:</span>
                            <span class="info-value" id="view-supplier-email">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">عنوان المتجر:</span>
                            <span class="info-value" id="view-supplier-address">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">حساب المورد:</span>
                            <span class="info-value" id="view-supplier-account">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">نوع المورد:</span>
                            <span class="info-value" id="view-supplier-type">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">تاريخ الإضافة:</span>
                            <span class="info-value" id="view-supplier-created">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">حالة المورد:</span>
                            <span class="info-value" id="view-supplier-status">-</span>
                        </div>
                    </div>
                    <div class="supplier-financial">
                        <h4>المعلومات المالية</h4>
                        <div class="info-item">
                            <span class="info-label">إجمالي المنتجات:</span>
                            <span class="info-value" id="view-supplier-products">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">الرصيد الحالي:</span>
                            <span class="info-value balance-negative" id="view-supplier-balance">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">آخر تحديث:</span>
                            <span class="info-value" id="view-supplier-updated">-</span>
                        </div>
                    </div>
                </div>
                <div class="supplier-info" style="margin-top: 20px;">
                    <h4>الملاحظات</h4>
                    <div class="form-control" style="background: #f9f9f9; min-height: 80px;" id="view-supplier-notes">
                        لا توجد ملاحظات
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary close-modal">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- شاشة عمليات المورد -->
    <div class="modal" id="operations-supplier-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="operations-supplier-title">عمليات المورد</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="operations-tabs">
                    <div class="operations-tab active" data-tab="products">المنتجات</div>
                    <div class="operations-tab" data-tab="transactions">المعاملات</div>
                    <div class="operations-tab" data-tab="balance">الرصيد والمديونية</div>
                    <div class="operations-tab" data-tab="reports">التقارير</div>
                </div>

                <!-- قسم المنتجات -->
                <div class="operations-content active" id="products-tab">
                    <div class="product-form">
                        <h4>إضافة منتج جديد</h4>
                        <form method="POST" id="add-product-form">
                            <input type="hidden" id="product-supplier-id" name="supplier_id">
                            <input type="hidden" name="add_product" value="1">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="product-name">اسم المنتج *</label>
                                    <input type="text" id="product-name" name="product_name" class="form-control" placeholder="أدخل اسم المنتج" required>
                                </div>
                                <div class="form-group">
                                    <label for="product-price">السعر *</label>
                                    <input type="number" id="product-price" name="price" class="form-control" placeholder="أدخل السعر" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="product-category">الفئة</label>
                                    <select id="product-category" name="category" class="form-control">
                                        <option value="">اختر الفئة</option>
                                        <?php foreach($product_categories as $value => $label): ?>
                                            <option value="<?= $value ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="product-store-stock">في مخزن المتجر</label>
                                    <input type="number" id="product-store-stock" name="store_stock" class="form-control" placeholder="الكمية في مخزن المتجر" value="0" min="0">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="product-supplier-stock">في مخزن المورد</label>
                                    <input type="number" id="product-supplier-stock" name="supplier_stock" class="form-control" placeholder="الكمية في مخزن المورد" value="0" min="0">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> إضافة منتج
                            </button>
                        </form>
                    </div>

                    <h4>قائمة المنتجات</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>اسم المنتج</th>
                                    <th>السعر</th>
                                    <th>الفئة</th>
                                    <th>في مخزن المتجر</th>
                                    <th>في مخزن المورد</th>
                                </tr>
                            </thead>
                            <tbody id="operations-products-table">
                                <!-- سيتم ملء البيانات عبر JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- قسم المعاملات -->
                <div class="operations-content" id="transactions-tab">
                    <div class="transaction-form">
                        <h4>تسجيل معاملة جديدة</h4>
                        <form method="POST" id="add-transaction-form">
                            <input type="hidden" id="transaction-supplier-id" name="supplier_id">
                            <input type="hidden" name="add_transaction" value="1">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="transaction-type">نوع المعاملة *</label>
                                    <select id="transaction-type" name="type" class="form-control" required>
                                        <option value="purchase">شراء من المورد</option>
                                        <option value="return">إرجاع للمورد</option>
                                        <option value="payment">دفعة للمورد</option>
                                        <option value="receipt">استلام من المورد</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="transaction-amount">المبلغ *</label>
                                    <input type="number" id="transaction-amount" name="amount" class="form-control" placeholder="أدخل المبلغ" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="transaction-date">التاريخ *</label>
                                    <input type="date" id="transaction-date" name="transaction_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="transaction-product">المنتج (إن وجد)</label>
                                    <select id="transaction-product" name="product_id" class="form-control">
                                        <option value="">اختر المنتج</option>
                                        <!-- سيتم ملء المنتجات عبر JavaScript -->
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="transaction-quantity">الكمية</label>
                                    <input type="number" id="transaction-quantity" name="quantity" class="form-control" placeholder="أدخل الكمية" value="1" min="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="transaction-notes">ملاحظات</label>
                                <textarea id="transaction-notes" name="notes" class="form-control" rows="3" placeholder="أدخل أي ملاحظات إضافية"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> تسجيل المعاملة
                            </button>
                        </form>
                    </div>

                    <h4>سجل المعاملات</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>نوع المعاملة</th>
                                    <th>المنتج</th>
                                    <th>المبلغ</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody id="operations-transactions-table">
                                <!-- سيتم ملء البيانات عبر JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- قسم الرصيد والمديونية -->
                <div class="operations-content" id="balance-tab">
                    <div class="supplier-details">
                        <div class="supplier-info">
                            <h4>الرصيد الحالي</h4>
                            <div class="info-item">
                                <span class="info-label">رصيد المورد لدى المتجر:</span>
                                <span class="info-value balance-negative" id="current-supplier-balance">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">آخر تحديث:</span>
                                <span class="info-value" id="current-balance-updated">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">إجمالي المشتريات:</span>
                                <span class="info-value" id="total-purchases">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">إجمالي المدفوعات:</span>
                                <span class="info-value" id="total-payments">-</span>
                            </div>
                        </div>
                        <div class="supplier-financial">
                            <h4>تحديث الرصيد</h4>
                            <form method="POST" id="update-balance-form">
                                <input type="hidden" id="balance-supplier-id" name="supplier_id">
                                <input type="hidden" name="update_balance" value="1">
                                <div class="form-group">
                                    <label for="balance-type">نوع التحديث *</label>
                                    <select id="balance-type" name="update_type" class="form-control" required>
                                        <option value="debt">زيادة مديونية المورد</option>
                                        <option value="credit">تقليل مديونية المورد</option>
                                        <option value="payment">دفعة للمورد</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="balance-amount">المبلغ *</label>
                                    <input type="number" id="balance-amount" name="amount" class="form-control" placeholder="أدخل المبلغ" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="balance-notes">سبب التحديث</label>
                                    <textarea id="balance-notes" name="notes" class="form-control" rows="3" placeholder="أدخل سبب تحديث الرصيد"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync"></i> تحديث الرصيد
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- قسم التقارير -->
                <div class="operations-content" id="reports-tab">
                    <h4>تقارير المورد</h4>
                    <div class="stats-cards">
                        <div class="stat-card card-1">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="report-products-count">-</h3>
                                <p>المنتجات</p>
                            </div>
                        </div>
                        <div class="stat-card card-2">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="report-purchases-count">-</h3>
                                <p>المشتريات</p>
                            </div>
                        </div>
                        <div class="stat-card card-3">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="report-payments-count">-</h3>
                                <p>الدفعات</p>
                            </div>
                        </div>
                        <div class="stat-card card-4">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="report-success-rate">-</h3>
                                <p>معدل الإنجاز</p>
                            </div>
                        </div>
                    </div>

                    <div class="report-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                        <button class="btn btn-primary" id="print-products-report">
                            <i class="fas fa-print"></i> طباعة تقرير المنتجات
                        </button>
                        <button class="btn btn-success" id="export-transactions-report">
                            <i class="fas fa-file-export"></i> تصدير تقرير المعاملات
                        </button>
                        <button class="btn btn-warning" id="print-balance-report">
                            <i class="fas fa-file-invoice"></i> تقرير الرصيد
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary close-modal">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- نافذة تأكيد الحذف -->
    <div class="modal" id="delete-supplier-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>حذف المورد</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 15px;"></i>
                    <h4 style="margin-bottom: 10px;">هل أنت متأكد من حذف هذا المورد؟</h4>
                    <p style="color: #666;">سيتم حذف المورد "<span id="delete-supplier-name"></span>" وجميع منتجاته ومعاملاته بشكل دائم ولا يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <form method="POST" id="delete-supplier-form" style="display: none;">
                    <input type="hidden" id="delete-supplier-id" name="id">
                    <input type="hidden" name="delete_supplier" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger close-modal">إلغاء</button>
                <button class="btn btn-primary" id="confirm-delete-supplier">تأكيد الحذف</button>
            </div>
        </div>
    </div>

    <script>
        // ======================
        // دالات إدارة النوافذ
        // ======================
        
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // ======================
        // معالجة النقر على الأزرار
        // ======================
        
        document.addEventListener('DOMContentLoaded', function() {
            
            // فتح نافذة إضافة مورد
            document.getElementById('add-supplier-btn').addEventListener('click', function() {
                document.getElementById('add-supplier-form').reset();
                openModal('add-supplier-modal');
            });
            
            // فتح نافذة إضافة المورد الأولى
            const addFirstBtn = document.getElementById('add-first-supplier');
            if (addFirstBtn) {
                addFirstBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('add-supplier-form').reset();
                    openModal('add-supplier-modal');
                });
            }
            
            // فتح نافذة تعديل مورد
            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const phone = this.getAttribute('data-phone');
                    const email = this.getAttribute('data-email');
                    const address = this.getAttribute('data-address');
                    const account = this.getAttribute('data-account');
                    const type = this.getAttribute('data-type');
                    const notes = this.getAttribute('data-notes');
                    
                    document.getElementById('edit-supplier-id').value = id;
                    document.getElementById('edit-supplier-name').value = name;
                    document.getElementById('edit-supplier-phone').value = phone;
                    document.getElementById('edit-supplier-email').value = email || '';
                    document.getElementById('edit-supplier-address').value = address || '';
                    document.getElementById('edit-supplier-account').value = account || '';
                    document.getElementById('edit-supplier-type').value = type || 'other';
                    document.getElementById('edit-supplier-notes').value = notes || '';
                    
                    openModal('edit-supplier-modal');
                });
            });
            
            // فتح نافذة عرض تفاصيل المورد
            document.querySelectorAll('.action-btn.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const phone = this.getAttribute('data-phone');
                    const email = this.getAttribute('data-email');
                    const address = this.getAttribute('data-address');
                    const account = this.getAttribute('data-account');
                    const type = this.getAttribute('data-type');
                    const notes = this.getAttribute('data-notes');
                    const balance = this.getAttribute('data-balance');
                    const products = this.getAttribute('data-products');
                    const created = this.getAttribute('data-created');
                    const status = this.getAttribute('data-status');
                    
                    document.getElementById('view-supplier-name').textContent = name;
                    document.getElementById('view-supplier-phone').textContent = phone;
                    document.getElementById('view-supplier-email').textContent = email || '-';
                    document.getElementById('view-supplier-address').textContent = address || '-';
                    document.getElementById('view-supplier-account').textContent = account || '-';
                    document.getElementById('view-supplier-type').textContent = getSupplierTypeLabel(type);
                    document.getElementById('view-supplier-created').textContent = formatDate(created);
                    document.getElementById('view-supplier-updated').textContent = formatDate(created);
                    document.getElementById('view-supplier-products').textContent = products + ' منتج';
                    
                    const balanceElement = document.getElementById('view-supplier-balance');
                    if (balance < 0) {
                        balanceElement.textContent = Math.abs(balance).toFixed(2) + ' ر.س (ندين للمورد)';
                        balanceElement.className = 'info-value balance-negative';
                    } else if (balance > 0) {
                        balanceElement.textContent = Math.abs(balance).toFixed(2) + ' ر.س (يدين لنا المورد)';
                        balanceElement.className = 'info-value balance-positive';
                    } else {
                        balanceElement.textContent = '0.00 ر.س';
                        balanceElement.className = 'info-value';
                    }
                    
                    document.getElementById('view-supplier-status').textContent = status === 'active' ? 'نشط' : 'غير نشط';
                    document.getElementById('view-supplier-notes').textContent = notes || 'لا توجد ملاحظات';
                    
                    openModal('view-supplier-modal');
                });
            });
            
            // فتح نافذة عمليات المورد
            document.querySelectorAll('.action-btn.operations').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('operations-supplier-title').textContent = 'عمليات المورد - ' + name;
                    document.getElementById('product-supplier-id').value = id;
                    document.getElementById('transaction-supplier-id').value = id;
                    document.getElementById('balance-supplier-id').value = id;
                    
                    // جلب بيانات العمليات
                    loadOperationsData(id);
                    
                    openModal('operations-supplier-modal');
                });
            });
            
            // فتح نافذة تأكيد الحذف
            document.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('delete-supplier-name').textContent = name;
                    document.getElementById('delete-supplier-id').value = id;
                    openModal('delete-supplier-modal');
                });
            });
            
            // ======================
            // إغلاق النوافذ المنبثقة
            // ======================
            
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        closeModal(modal.id);
                    }
                });
            });
            
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this.id);
                    }
                });
            });
            
            // ======================
            // التبويبات
            // ======================
            
            document.querySelectorAll('.operations-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    document.querySelectorAll('.operations-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    document.querySelectorAll('.operations-tab').forEach(t => {
                        t.classList.remove('active');
                    });
                    
                    document.getElementById(tabId + '-tab').classList.add('active');
                    this.classList.add('active');
                });
            });
            
            // ======================
            // إرسال النماذج
            // ======================
            
            document.getElementById('submit-add-supplier').addEventListener('click', function() {
                const name = document.getElementById('supplier-name').value.trim();
                const phone = document.getElementById('supplier-phone').value.trim();
                
                if (!name) {
                    alert('يرجى إدخال اسم المورد');
                    document.getElementById('supplier-name').focus();
                    return;
                }
                
                if (!phone) {
                    alert('يرجى إدخال رقم الهاتف');
                    document.getElementById('supplier-phone').focus();
                    return;
                }
                
                document.getElementById('add-supplier-form').submit();
            });
            
            document.getElementById('submit-edit-supplier').addEventListener('click', function() {
                document.getElementById('edit-supplier-form').submit();
            });
            
            document.getElementById('confirm-delete-supplier').addEventListener('click', function() {
                document.getElementById('delete-supplier-form').submit();
            });
            
            // ======================
            // دوال مساعدة
            // ======================
            
            function getSupplierTypeLabel(type) {
                const types = {
                    'electronics': 'إلكترونيات',
                    'home': 'أثاث منزلي',
                    'office': 'قرطاسية ومكتب',
                    'other': 'أخرى'
                };
                return types[type] || type;
            }
            
            function formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('ar-SA');
            }
            
            async function loadOperationsData(supplierId) {
                try {
                    // جلب منتجات المورد
                    const productsResponse = await fetch(`get_supplier_products.php?supplier_id=${supplierId}`);
                    const products = await productsResponse.json();
                    
                    const productsTable = document.getElementById('operations-products-table');
                    const productSelect = document.getElementById('transaction-product');
                    
                    productsTable.innerHTML = '';
                    productSelect.innerHTML = '<option value="">اختر المنتج</option>';
                    
                    if (products && products.length > 0) {
                        products.forEach(product => {
                            // إضافة إلى الجدول
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${product.product_name}</td>
                                <td>${product.price} ر.س</td>
                                <td>${getCategoryLabel(product.category)}</td>
                                <td>${product.store_stock}</td>
                                <td>${product.supplier_stock}</td>
                            `;
                            productsTable.appendChild(row);
                            
                            // إضافة إلى القائمة المنسدلة
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = product.product_name;
                            productSelect.appendChild(option);
                        });
                        
                        document.getElementById('report-products-count').textContent = products.length;
                    } else {
                        productsTable.innerHTML = '<tr><td colspan="5" style="text-align: center;">لا توجد منتجات</td></tr>';
                        document.getElementById('report-products-count').textContent = '0';
                    }
                    
                    // جلب معاملات المورد
                    const transactionsResponse = await fetch(`get_supplier_transactions.php?supplier_id=${supplierId}`);
                    const transactions = await transactionsResponse.json();
                    
                    const transactionsTable = document.getElementById('operations-transactions-table');
                    transactionsTable.innerHTML = '';
                    
                    let totalPurchases = 0;
                    let totalPayments = 0;
                    
                    if (transactions && transactions.length > 0) {
                        transactions.forEach(transaction => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${formatDate(transaction.transaction_date)}</td>
                                <td>${getTransactionTypeLabel(transaction.type)}</td>
                                <td>${transaction.product_name || '-'}</td>
                                <td class="${transaction.type === 'purchase' ? 'balance-negative' : 'balance-positive'}">
                                    ${Math.abs(transaction.amount).toLocaleString()} ر.س
                                </td>
                                <td>${transaction.notes || '-'}</td>
                            `;
                            transactionsTable.appendChild(row);
                            
                            if (transaction.type === 'purchase') {
                                totalPurchases += parseFloat(transaction.amount);
                            } else if (transaction.type === 'payment') {
                                totalPayments += parseFloat(transaction.amount);
                            }
                        });
                        
                        document.getElementById('report-purchases-count').textContent = transactions.filter(t => t.type === 'purchase').length;
                        document.getElementById('report-payments-count').textContent = transactions.filter(t => t.type === 'payment').length;
                        
                        document.getElementById('total-purchases').textContent = totalPurchases.toFixed(2) + ' ر.س';
                        document.getElementById('total-payments').textContent = totalPayments.toFixed(2) + ' ر.س';
                    } else {
                        transactionsTable.innerHTML = '<tr><td colspan="5" style="text-align: center;">لا توجد معاملات</td></tr>';
                        document.getElementById('report-purchases-count').textContent = '0';
                        document.getElementById('report-payments-count').textContent = '0';
                    }
                    
                    // جلب رصيد المورد
                    const balanceResponse = await fetch(`get_supplier_balance.php?supplier_id=${supplierId}`);
                    const balanceData = await balanceResponse.json();
                    
                    const balanceElement = document.getElementById('current-supplier-balance');
                    if (balanceData.balance < 0) {
                        balanceElement.textContent = Math.abs(balanceData.balance).toFixed(2) + ' ر.س (ندين للمورد)';
                        balanceElement.className = 'info-value balance-negative';
                    } else if (balanceData.balance > 0) {
                        balanceElement.textContent = Math.abs(balanceData.balance).toFixed(2) + ' ر.س (يدين لنا المورد)';
                        balanceElement.className = 'info-value balance-positive';
                    } else {
                        balanceElement.textContent = '0.00 ر.س';
                        balanceElement.className = 'info-value';
                    }
                    
                    document.getElementById('current-balance-updated').textContent = formatDate(balanceData.updated_at);
                    
                    // حساب معدل الإنجاز
                    const successRate = products.length > 0 ? '95%' : '0%';
                    document.getElementById('report-success-rate').textContent = successRate;
                    
                    // تعيين تاريخ اليوم كافتراضي
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('transaction-date').value = today;
                    
                } catch (error) {
                    console.error('Error loading operations data:', error);
                }
            }
            
            function getCategoryLabel(category) {
                const categories = {
                    'electronics': 'إلكترونيات',
                    'home': 'أثاث منزلي',
                    'office': 'قرطاسية ومكتب',
                    'clothing': 'ملابس',
                    'food': 'مواد غذائية'
                };
                return categories[category] || category;
            }
            
            function getTransactionTypeLabel(type) {
                const types = {
                    'purchase': 'شراء من المورد',
                    'return': 'إرجاع للمورد',
                    'payment': 'دفعة للمورد',
                    'receipt': 'استلام من المورد'
                };
                return types[type] || type;
            }
            
            // تعيين تاريخ اليوم كافتراضي عند تحميل الصفحة
            const today = new Date().toISOString().split('T')[0];
            if (document.getElementById('transaction-date')) {
                document.getElementById('transaction-date').value = today;
            }
            
            // طباعة التقارير
            document.getElementById('print-products-report').addEventListener('click', function() {
                window.print();
            });
            
            document.getElementById('print-balance-report').addEventListener('click', function() {
                window.print();
            });
            
            document.getElementById('export-transactions-report').addEventListener('click', function() {
                alert('سيتم تصدير تقرير المعاملات');
            });
            
        });
        
        // إغلاق النافذة بالضغط على زر ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    if (modal.style.display === 'flex') {
                        closeModal(modal.id);
                    }
                });
            }
        });
    </script>
</body>
</html>