<?php
        session_start();
        require_once 'config.php'; // يحتوي على $conn (mysqli)

        // تشغيل عرض الأخطاء للتصحيح
        error_reporting(E_ALL);
        ini_set('display_errors', 1);



        // دوال مساعدة
        function getPurchases() {
            global $conn;
            $purchases = [];
            
            try {
                // جلب المشتريات الأساسية
                $sql = "SELECT p.*, s.name as supplier_name 
                        FROM purchases p 
                        LEFT JOIN suppliers s ON p.supplier_id = s.id
                        ORDER BY p.created_at DESC";
                
                $res = $conn->query($sql);
                
                if (!$res) {
                    throw new Exception("خطأ SQL: " . $conn->error);
                }
                
                while ($purchase = $res->fetch_assoc()) {
                    $purchase_id = $purchase['id'];
                    
                    // جلب تفاصيل المنتجات
                    $details_sql = "SELECT pd.*, pr.name as product_name 
                                FROM purchase_details pd 
                                LEFT JOIN products pr ON pd.product_id = pr.id 
                                WHERE pd.purchase_id = $purchase_id";
                    
                    $details_res = $conn->query($details_sql);
                    
                    $products = [];
                    $items_count = 0;
                    $total_amount = 0;
                    $product_names = [];
                    
                    if ($details_res) {
                        while ($detail = $details_res->fetch_assoc()) {
                            $products[] = $detail;
                            $items_count += $detail['quantity'];
                            $total_amount += $detail['total'];
                            $product_names[] = $detail['product_name'];
                        }
                    }
                    
                    // حساب الإجمالي من تفاصيل المنتجات إذا كان فارغاً في الجدول الرئيسي
                    if ($total_amount > 0 && empty($purchase['total'])) {
                        $purchase['total'] = $total_amount;
                    }
                    
                    $purchase['products'] = $products;
                    $purchase['items_count'] = $items_count;
                    $purchase['product_names'] = !empty($product_names) ? implode(', ', $product_names) : 'لا توجد منتجات';
                    
                    $purchases[] = $purchase;
                }
            } catch (Exception $e) {
                error_log("خطأ في getPurchases: " . $e->getMessage());
                return [];
            }
            
            return $purchases;
        }

        function getProducts() {
            global $conn;
            $products = [];
            
            try {
                $res = $conn->query("SELECT id, name, selling_price, IFNULL(stock, 0) as stock FROM products WHERE is_active=1 ORDER BY name");
                if ($res) {
                    while ($row = $res->fetch_assoc()) {
                        $products[] = $row;
                    }
                }
            } catch (Exception $e) {
                error_log("خطأ في getProducts: " . $e->getMessage());
            }
            
            return $products;
        }

        function getSuppliers() {
            global $conn;
            $suppliers = [];
            
            try {
                $res = $conn->query("SELECT id, name FROM suppliers ORDER BY name");
                if ($res) {
                    while ($row = $res->fetch_assoc()) {
                        $suppliers[] = $row;
                    }
                }
            } catch (Exception $e) {
                error_log("خطأ في getSuppliers: " . $e->getMessage());
            }
            
            return $suppliers;
        }

        function generatePurchaseNumber() {
            global $conn;

            $year  = date('Y');
            $month = date('m');

            try {
                $sql = "SELECT purchase_number 
                        FROM purchases 
                        WHERE purchase_number LIKE 'PUR-$year-$month-%'
                        ORDER BY id DESC 
                        LIMIT 1";

                $res = $conn->query($sql);

                if ($res && $res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $lastNumber = (int)substr($row['purchase_number'], -4);
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }
            } catch (Exception $e) {
                $newNumber = 1;
                error_log("خطأ في generatePurchaseNumber: " . $e->getMessage());
            }

            return sprintf("PUR-%s-%s-%04d", $year, $month, $newNumber);
        }

        function addPurchase($data) {
            global $conn;
            
            error_log("=== بدء إضافة مشتريات جديدة ===");
            
            $supplier_id = isset($data['supplier_id']) ? (int)$data['supplier_id'] : 0;
            $purchase_date = isset($data['purchase_date']) ? $conn->real_escape_string($data['purchase_date']) : date('Y-m-d');
            $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
            $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'in-stock';
            
            // تسجيل البيانات المستلمة
            error_log("supplier_id: $supplier_id");
            error_log("purchase_date: $purchase_date");
            error_log("notes: $notes");
            error_log("status: $status");
            
            // التحقق من البيانات الأساسية
            if ($supplier_id <= 0) {
                error_log("خطأ: supplier_id غير صالح: $supplier_id");
                $_SESSION['error'] = "يجب اختيار مورد صالح";
                return false;
            }
            
            // التحقق من وجود المنتجات
            if (!isset($data['products']) || !is_array($data['products']) || count($data['products']) == 0) {
                error_log("خطأ: لا توجد منتجات في البيانات");
                $_SESSION['error'] = "يجب إضافة منتج واحد على الأقل";
                return false;
            }
            
            error_log("عدد المنتجات: " . count($data['products']));
            
            // توليد رقم شراء تلقائي
            $purchase_number = generatePurchaseNumber();
            error_log("رقم الشراء المولد: $purchase_number");
            
            // بدء المعاملة
            $conn->begin_transaction();
            
            try {
                // 1. إدخال رأس الفاتورة
                $sql = "INSERT INTO purchases 
                        (purchase_number, supplier_id, purchase_date, notes, status, total, created_at)
                        VALUES
                        ('$purchase_number', $supplier_id, '$purchase_date', '$notes', '$status', 0, NOW())";
                
                error_log("استعلام إدخال رأس الفاتورة: $sql");
                
                if (!$conn->query($sql)) {
                    throw new Exception("خطأ في إدخال رأس الفاتورة: " . $conn->error);
                }
                
                $purchase_id = $conn->insert_id;
                error_log("تم إنشاء الشراء برقم: $purchase_id");
                
                $total = 0;
                $products_added = 0;
                
                // 2. إدخال المنتجات
                foreach ($data['products'] as $product) {
                    $product_id = isset($product['product_id']) ? (int)$product['product_id'] : 0;
                    $quantity = isset($product['quantity']) ? (int)$product['quantity'] : 0;
                    $unit_price = isset($product['unit_price']) ? (float)$product['unit_price'] : 0;
                    
                    error_log("معالجة المنتج: ID=$product_id, الكمية=$quantity, السعر=$unit_price");
                    
                    if ($product_id > 0 && $quantity > 0 && $unit_price > 0) {
                        $item_total = $quantity * $unit_price;
                        
                        $sql = "INSERT INTO purchase_details 
                                (purchase_id, product_id, quantity, unit_price, total, created_at)
                                VALUES
                                ($purchase_id, $product_id, $quantity, $unit_price, $item_total, NOW())";
                        
                        error_log("استعلام إدخال المنتج: $sql");
                        
                        if (!$conn->query($sql)) {
                            throw new Exception("خطأ في إدخال تفاصيل المنتج (ID: $product_id): " . $conn->error);
                        }
                        
                        $total += $item_total;
                        $products_added++;
                        
                        // 3. تحديث المخزون
                        $update_sql = "UPDATE products SET stock = IFNULL(stock, 0) + $quantity WHERE id = $product_id";
                        error_log("استعلام تحديث المخزون: $update_sql");
                        
                        if (!$conn->query($update_sql)) {
                            throw new Exception("خطأ في تحديث المخزون للمنتج $product_id: " . $conn->error);
                        }
                        
                        error_log("تم إضافة المنتج $product_id وتحديث المخزون بنجاح");
                    } else {
                        error_log("منتج غير صالح تم تخطيه: ID=$product_id, الكمية=$quantity, السعر=$unit_price");
                    }
                }
                
                if ($products_added == 0) {
                    throw new Exception("لم تتم إضافة أي منتجات صالحة");
                }
                
                error_log("إجمالي المنتجات المضافة: $products_added");
                error_log("الإجمالي الكلي: $total");
                
                // 4. تحديث إجمالي الفاتورة
                $update_total_sql = "UPDATE purchases SET total = $total WHERE id = $purchase_id";
                error_log("استعلام تحديث الإجمالي: $update_total_sql");
                
                if (!$conn->query($update_total_sql)) {
                    throw new Exception("خطأ في تحديث الإجمالي: " . $conn->error);
                }
                
                $conn->commit();
                error_log("=== تم إضافة المشتريات بنجاح برقم: $purchase_id ===");
                
                $_SESSION['success'] = "تم إضافة المشتريات بنجاح. رقم الفاتورة: $purchase_number";
                return $purchase_id;
                
            } catch (Exception $e) {
                $conn->rollback();
                error_log("خطأ في addPurchase: " . $e->getMessage());
                $_SESSION['error'] = "حدث خطأ أثناء إضافة المشتريات: " . $e->getMessage();
                return false;
            }
        }

        // دالة مبسطة لتحديث المشتريات
        function updatePurchase($id, $data) {
            global $conn;
            
            $id = (int)$id;
            
            $conn->begin_transaction();
            
            try {
                // حذف التفاصيل القديمة
                $conn->query("DELETE FROM purchase_details WHERE purchase_id = $id");
                
                // تحديث رأس الفاتورة
                $supplier_id = (int)$data['supplier_id'];
                $purchase_date = $conn->real_escape_string($data['purchase_date']);
                $notes = $conn->real_escape_string($data['notes'] ?? '');
                $status = $conn->real_escape_string($data['status'] ?? 'in-stock');
                
                $sql = "UPDATE purchases SET 
                        supplier_id = $supplier_id,
                        purchase_date = '$purchase_date',
                        notes = '$notes',
                        status = '$status',
                        updated_at = NOW()
                        WHERE id = $id";
                
                if (!$conn->query($sql)) {
                    throw new Exception("خطأ في تحديث رأس الفاتورة: " . $conn->error);
                }
                
                // إضافة المنتجات الجديدة
                $total = 0;
                
                if (isset($data['products']) && is_array($data['products'])) {
                    foreach ($data['products'] as $product) {
                        $product_id = (int)$product['product_id'];
                        $quantity = (int)$product['quantity'];
                        $unit_price = (float)$product['unit_price'];
                        
                        if ($product_id > 0 && $quantity > 0) {
                            $item_total = $quantity * $unit_price;
                            
                            $sql = "INSERT INTO purchase_details 
                                    (purchase_id, product_id, quantity, unit_price, total)
                                    VALUES
                                    ($id, $product_id, $quantity, $unit_price, $item_total)";
                            
                            if (!$conn->query($sql)) {
                                throw new Exception("خطأ في إدخال تفاصيل المنتج: " . $conn->error);
                            }
                            
                            $total += $item_total;
                        }
                    }
                }
                
                // تحديث الإجمالي
                $conn->query("UPDATE purchases SET total = $total WHERE id = $id");
                
                $conn->commit();
                return true;
                
            } catch (Exception $e) {
                $conn->rollback();
                error_log("خطأ في updatePurchase: " . $e->getMessage());
                return false;
            }
        }

        function deletePurchase($id) {
            global $conn;
            
            $id = (int)$id;
            
            $conn->begin_transaction();
            
            try {
                // حذف التفاصيل أولاً
                $conn->query("DELETE FROM purchase_details WHERE purchase_id = $id");
                
                // ثم حذف رأس الفاتورة
                $conn->query("DELETE FROM purchases WHERE id = $id");
                
                $conn->commit();
                return true;
                
            } catch (Exception $e) {
                $conn->rollback();
                error_log("خطأ في deletePurchase: " . $e->getMessage());
                return false;
            }
        }

        // معالجة طلبات POST مع تفصيل الأخطاء
        $message = '';
        $message_type = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            error_log("=== تم استقبال طلب POST ===");
            error_log("الإجراء: " . ($_POST['action'] ?? 'غير محدد'));
            
            // تنظيف رسائل الجلسة القديمة
            unset($_SESSION['error'], $_SESSION['success']);
            
            switch ($_POST['action']) {
                case 'add':
                    $products = [];
                    $has_valid_products = false;
                        
                    // تسجيل بيانات POST للتتبع
                    error_log("بيانات POST الواردة:");
                    foreach ($_POST as $key => $value) {
                        if (is_array($value)) {
                            error_log("$key: " . print_r($value, true));
                        } else {
                            error_log("$key: $value");
                        }
                    }
                    
                    // معالجة المنتجات من النموذج
                    if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                        foreach ($_POST['product_id'] as $index => $product_id) {
                            if (!empty($product_id) && 
                                isset($_POST['quantity'][$index]) && 
                                isset($_POST['unit_price'][$index])) {
                                
                                $quantity = (int)$_POST['quantity'][$index];
                                $unit_price = (float)$_POST['unit_price'][$index];
                                
                                error_log("معالجة منتج [$index]: ID=$product_id, الكمية=$quantity, السعر=$unit_price");
                                
                                if ($quantity > 0 && $unit_price > 0) {
                                    $products[] = [
                                        'product_id' => $product_id,
                                        'quantity' => $quantity,
                                        'unit_price' => $unit_price
                                    ];
                                    $has_valid_products = true;
                                    error_log("منتج صالح تمت إضافته");
                                }
                            }
                        }
                    }
                    
                    if (!$has_valid_products) {
                        $message = "يجب إضافة منتج واحد على الأقل مع كمية وسعر صالحين";
                        $message_type = "error";
                        $_SESSION['error'] = $message;
                        error_log("خطأ: لم يتم العثور على منتجات صالحة");
                    } else {
                        // إعداد البيانات
                        $data = [
                            'supplier_id' => $_POST['supplier_id'] ?? 0,
                            'purchase_date' => $_POST['purchase_date'] ?? date('Y-m-d'),
                            'notes' => $_POST['notes'] ?? '',
                            'status' => $_POST['status'] ?? 'in-stock',
                            'products' => $products
                        ];
                        
                        error_log("جاري استدعاء addPurchase مع البيانات:");
                        error_log(print_r($data, true));
                        
                        $result = addPurchase($data);
                        if ($result !== false) {
                            error_log("تمت الإضافة بنجاح. معرف الشراء: $result");
                        } else {
                            error_log("فشل إضافة المشتريات");
                        }
                    }
                    break;
                    
                case 'update':
                    $products = [];
                    $has_valid_products = false;
                    
                    if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                        foreach ($_POST['product_id'] as $index => $product_id) {
                            if (!empty($product_id) && 
                                isset($_POST['quantity'][$index]) && 
                                isset($_POST['unit_price'][$index]) &&
                                $_POST['quantity'][$index] > 0 &&
                                $_POST['unit_price'][$index] > 0) {
                                
                                $products[] = [
                                    'product_id' => $product_id,
                                    'quantity' => $_POST['quantity'][$index],
                                    'unit_price' => $_POST['unit_price'][$index]
                                ];
                                $has_valid_products = true;
                            }
                        }
                    }
                    
                    if (!$has_valid_products) {
                        $message = "يجب إضافة منتج واحد على الأقل مع كمية وسعر صالحين";
                        $message_type = "error";
                        $_SESSION['error'] = $message;
                    } else {
                        // إعداد البيانات
                        $data = [
                            'supplier_id' => $_POST['supplier_id'] ?? 0,
                            'purchase_date' => $_POST['purchase_date'] ?? date('Y-m-d'),
                            'notes' => $_POST['notes'] ?? '',
                            'status' => $_POST['status'] ?? 'in-stock',
                            'products' => $products
                        ];
                        
                        if (updatePurchase($_POST['id'], $data)) {
                            $message = "تم تحديث المشتريات بنجاح";
                            $message_type = "success";
                            $_SESSION['success'] = $message;
                        } else {
                            $message = "حدث خطأ أثناء تحديث المشتريات";
                            $message_type = "error";
                            $_SESSION['error'] = $message;
                        }
                    }
                    break;
                    
                case 'delete':
                    if (isset($_POST['id']) && !empty($_POST['id'])) {
                        if (deletePurchase($_POST['id'])) {
                            $message = "تم حذف المشتريات بنجاح";
                            $message_type = "success";
                            $_SESSION['success'] = $message;
                        } else {
                            $message = "حدث خطأ أثناء حذف المشتريات";
                            $message_type = "error";
                            $_SESSION['error'] = $message;
                        }
                    } else {
                        $message = "لم يتم تحديد معرف الشراء للحذف";
                        $message_type = "error";
                        $_SESSION['error'] = $message;
                    }
                    break;
            }
            
            // إعادة التوجيه لمنع إعادة إرسال النموذج
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }

        // جلب البيانات للعرض
        $purchases = getPurchases(); // تم التصحيح من addPurchase() إلى getPurchases()
        $products = getProducts();
        $suppliers = getSuppliers();

        // إحصائيات
        $total_purchases = count($purchases);
        $total_items = 0;
        $total = 0;

        foreach ($purchases as $purchase) {
            $total_items += isset($purchase['items_count']) ? $purchase['items_count'] : 0;
            $total += isset($purchase['total']) ? $purchase['total'] : 0;
        }

        $total_suppliers = count($suppliers);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المشتريات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* جميع الأنماط السابقة تبقى كما هي */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            padding: 20px 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .products-container {
            margin-bottom: 20px;
        }
        
        .product-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .product-row:last-child {
            margin-bottom: 0;
        }
        
        .product-select {
            flex: 2;
        }
        
        .product-quantity {
            flex: 1;
        }
        
        .product-price {
            flex: 1;
        }
        
        .product-total {
            flex: 1;
            display: flex;
            align-items: center;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .product-actions {
            flex: 0.5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-remove-product {
            background: #e74c3c;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-remove-product:hover {
            background: #c0392b;
        }
        
        .btn-add-product {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .btn-add-product:hover {
            background: #2980b9;
        }
        
        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            padding: 15px;
            background: #e8f4fc;
            border-radius: 8px;
            border: 1px solid #3498db;
        }
        
        .summary-item {
            margin-left: 30px;
            text-align: center;
        }
        
        .summary-label {
            display: block;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .purchase-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .detail-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .detail-label {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 500;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .products-table th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: right;
        }
        
        .products-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
            text-align: right;
        }
        
        .products-table tr:hover {
            background: #f8f9fa;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .product-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .summary-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .summary-item {
                margin-left: 0;
            }
            
            .modal-content {
                width: 95%;
            }
        }
        
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-title h2 {
            font-size: 24px;
            color: #2c3e50;
        }
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .card-1 .stat-icon { background-color: #3498db; }
        .card-2 .stat-icon { background-color: #2ecc71; }
        .card-3 .stat-icon { background-color: #9b59b6; }
        .card-4 .stat-icon { background-color: #e74c3c; }
        
        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .purchases-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.with-supplier {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status.out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-cell {
            display: flex;
            gap: 5px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            color: #2c3e50;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-actions {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .message {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
           @media (max-width: 576px) {
            .modal-content {
                width: 95%;
                padding: 15px;
            }
            
            .action-cell {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <style>
/* أنماط النافذة المنبثقة */
.modal-open {
    overflow: hidden;
}

/* تحسين ظهور الصفوف */
.table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: #f5f5f5;
}

/* تحسين أزرار الإجراءات */
.action-cell {
    cursor: default;
}

.action-cell button {
    cursor: pointer;
}

/* للجوال */
@media (max-width: 768px) {
    #purchaseModal > div {
        width: 95% !important;
        padding: 15px !important;
    }
    
    table {
        font-size: 14px !important;
    }
}
</style>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <div class="page-content">
                <div class="page-title">
                    <h2>إدارة المشتريات</h2>
                    <div class="date"><?php echo date('Y-m-d'); ?></div>
                </div>

                <!-- عرض الرسائل -->
                <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <div class="actions-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="البحث في المشتريات..." id="searchInput">
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-primary" id="addPurchaseBtn">
                            <i class="fas fa-plus"></i>
                            إضافة مشتريات
                        </button>
                    </div>
                </div>

                <div class="stats-cards">
                    <div class="stat-card card-1">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_purchases; ?></h3>
                            <p>إجمالي المشتريات</p>
                        </div>
                    </div>
                    <div class="stat-card card-2">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_items; ?></h3>
                            <p>المنتجات المشتراة</p>
                        </div>
                    </div>
                    <div class="stat-card card-3">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($total, 2); ?></h3>
                            <p>إجمالي قيمة المشتريات</p>
                        </div>
                    </div>
                    <div class="stat-card card-4">
                        <div class="stat-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_suppliers; ?></h3>
                            <p>عدد الموردين</p>
                        </div>
                    </div>
                </div>

                <div class="purchases-table">
                    <div class="table-header">
                        <h3>سجل المشتريات</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم الشراء</th>
                                <th>المنتجات</th>
                                <th>المورد</th>
                                <th>عدد المنتجات</th>
                                <th>المجموع</th>
                                <th>تاريخ الشراء</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="purchasesTableBody">
                            <?php foreach($purchases as $purchase): ?>
                            <tr>
                                <td><?php echo $purchase['purchase_number']; ?></td>
                                <td><?php echo $purchase['product_names']; ?></td>
                                <td><?php echo $purchase['supplier_name']; ?></td>
                                <td><?php echo $purchase['items_count']; ?></td>
                                <td>$<?php echo number_format($purchase['total'], 2); ?></td>
                                <td><?php echo $purchase['purchase_date']; ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    switch($purchase['status']) {
                                        case 'in-stock':
                                            $status_class = 'in-stock';
                                            $status_text = 'في المخزن';
                                            break;
                                        case 'with-supplier':
                                            $status_class = 'with-supplier';
                                            $status_text = 'مع المورد';
                                            break;
                                        case 'out-of-stock':
                                            $status_class = 'out-of-stock';
                                            $status_text = 'نفذت';
                                            break;
                                        default:
                                            $status_class = 'in-stock';
                                            $status_text = 'في المخزن';
                                    }
                                    ?>
                                    <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td class="action-cell">
                                    <button class="action-btn view-btn" data-id="<?php echo $purchase['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                        عرض
                                    </button>
                                    <button class="action-btn edit-btn" data-id="<?php echo $purchase['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                        تعديل
                                    </button>
                                    <button class="action-btn delete-btn" data-id="<?php echo $purchase['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                        حذف
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- نموذج إضافة/تعديل مشتريات -->
    <div class="modal" id="purchaseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">إضافة مشتريات جديدة</h3>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <form id="purchaseForm" method="POST">
                <input type="hidden" name="id" id="purchaseId">
                <input type="hidden" name="action" id="formAction" value="add">
                
                <div style="padding: 20px;">
                    <!-- معلومات الفاتورة الأساسية -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier">المورد</label>
                            <select class="form-control" id="supplier" name="supplier_id" required>
                                <option value="">اختر المورد</option>
                                <?php foreach($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchaseDate">تاريخ الشراء</label>
                            <input type="date" class="form-control" id="purchaseDate" name="purchase_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">حالة الفاتورة</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="in-stock">في المخزن</option>
                                <option value="with-supplier">مع المورد</option>
                                <option value="out-of-stock">نفذت</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- المنتجات -->
                    <div class="products-container">
                        <h4 style="margin-bottom: 15px; color: #2c3e50;">المنتجات</h4>
                        <div id="productsList">
                            <!-- صف المنتج الأول -->
                            <div class="product-row">
                                <div class="product-select">
                                    <label>المنتج</label>
                                    <select class="form-control product-select" name="product_id[]" required onchange="updatePrice(this)">
                                        <option value="">اختر المنتج</option>
                                        <?php foreach($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['selling_price']; ?>">
                                            <?php echo $product['name']; ?> ($<?php echo number_format($product['selling_price'], 2); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="product-quantity">
                                    <label>الكمية</label>
                                    <input type="number" class="form-control" name="quantity[]" min="1" value="1" required oninput="calculateTotal(this)">
                                </div>
                                <div class="product-price">
                                    <label>سعر الوحدة</label>
                                    <input type="number" class="form-control" name="unit_price[]" min="0.01" step="0.01" required oninput="calculateTotal(this)">
                                </div>
                                <div class="product-total">
                                    <label>المجموع</label>
                                    <span class="item-total">$0.00</span>
                                </div>
                                <div class="product-actions">
                                    <button type="button" class="btn-remove-product" onclick="removeProductRow(this)" disabled>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn-add-product" onclick="addProductRow()">
                            <i class="fas fa-plus"></i>
                            إضافة منتج آخر
                        </button>
                    </div>
                    
                    <!-- الإجماليات -->
                    <div class="summary-row">
                        <div class="summary-item">
                            <span class="summary-label">إجمالي العناصر:</span>
                            <span class="summary-value" id="totalItems">0</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">المجموع الكلي:</span>
                            <span class="summary-value" id="grandTotal">$0.00</span>
                        </div>
                    </div>
                    
                    <!-- ملاحظات -->
                    <div class="form-group">
                        <label for="notes">ملاحظات</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-warning" id="cancelPurchaseBtn">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="savePurchaseBtn">حفظ المشتريات</button>
                </div>
            </form>
        </div>
    </div>
<script>
$(document).ready(function() {
    // عند النقر على صف المشتريات
    $('#purchasesTableBody').on('click', 'tr', function(e) {
        // إذا تم النقر على أزرار الإجراءات لا تعمل
        if ($(e.target).closest('.action-cell').length) return;
        
        const purchaseId = $(this).find('.view-btn').data('id');
        showPurchaseDetails(purchaseId);
    });
    
    // عند النقر على زر العرض
    $(document).on('click', '.view-btn', function(e) {
        e.stopPropagation();
        const purchaseId = $(this).data('id');
        showPurchaseDetails(purchaseId);
    });
    
    // إغلاق النافذة
    $(document).on('click', '.close-modal', function() {
        $('#purchaseModal').remove();
    });
});

function showPurchaseDetails(purchaseId) {
    $.ajax({
        url: 'get_purchase_details.php',
        type: 'GET',
        data: { id: purchaseId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayModal(response.data);
            } else {
                alert('حدث خطأ: ' + response.message);
            }
        },
        error: function() {
            alert('خطأ في تحميل البيانات');
        }
    });
}

function displayModal(purchase) {
    // إزالة أي نافذة سابقة
    $('#purchaseModal').remove();
    
    // إنشاء محتوى النافذة
    const modalHTML = `
        <div id="purchaseModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        ">
            <div style="
                background: white;
                width: 90%;
                max-width: 800px;
                max-height: 90vh;
                overflow-y: auto;
                border-radius: 10px;
                padding: 20px;
            ">
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                ">
                    <h3 style="margin: 0; color: #007bff;">تفاصيل الفاتورة #${purchase.purchase_number}</h3>
                    <button class="close-modal" style="
                        background: none;
                        border: none;
                        font-size: 24px;
                        cursor: pointer;
                        color: #666;
                    ">&times;</button>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <p><strong>المورد:</strong> ${purchase.supplier_name}</p>
                        <p><strong>تاريخ الشراء:</strong> ${purchase.purchase_date}</p>
                        <p><strong>تاريخ الإنشاء:</strong> ${purchase.created_at}</p>
                    </div>
                    <div>
                        <p><strong>الحالة:</strong> <span class="status ${purchase.status_class}" style="
                            padding: 3px 10px;
                            border-radius: 15px;
                            font-size: 12px;
                            ${purchase.status_class === 'in-stock' ? 'background: #28a745; color: white;' : 
                              purchase.status_class === 'with-supplier' ? 'background: #ffc107; color: black;' : 
                              'background: #dc3545; color: white;'}
                        ">${purchase.status_text}</span></p>
                        <p><strong>ملاحظات:</strong> ${purchase.notes || 'لا توجد'}</p>
                    </div>
                </div>
                
                <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px;">المنتجات</h4>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 10px; text-align: right; border: 1px solid #dee2e6;">#</th>
                            <th style="padding: 10px; text-align: right; border: 1px solid #dee2e6;">المنتج</th>
                            <th style="padding: 10px; text-align: right; border: 1px solid #dee2e6;">الكمية</th>
                            <th style="padding: 10px; text-align: right; border: 1px solid #dee2e6;">سعر الوحدة</th>
                            <th style="padding: 10px; text-align: right; border: 1px solid #dee2e6;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${purchase.products.map((product, index) => `
                            <tr>
                                <td style="padding: 10px; border: 1px solid #dee2e6;">${index + 1}</td>
                                <td style="padding: 10px; border: 1px solid #dee2e6;">${product.product_name}</td>
                                <td style="padding: 10px; border: 1px solid #dee2e6;">${product.quantity}</td>
                                <td style="padding: 10px; border: 1px solid #dee2e6;">$${parseFloat(product.unit_price).toFixed(2)}</td>
                                <td style="padding: 10px; border: 1px solid #dee2e6;">$${parseFloat(product.total).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot style="background: #f8f9fa; font-weight: bold;">
                        <tr>
                            <td colspan="4" style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">الإجمالي</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">$${parseFloat(purchase.total).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">عدد المنتجات</td>
                            <td style="padding: 10px; border: 1px solid #dee2e6;">${purchase.items_count}</td>
                        </tr>
                    </tfoot>
                </table>
                
                <div style="text-align: left;">
                    <button class="close-modal" style="
                        padding: 8px 20px;
                        background: #6c757d;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                    ">إغلاق</button>
                </div>
            </div>
        </div>
    `;
    
    // إضافة النافذة إلى الصفحة
    $('body').append(modalHTML);
}
</script>
    <script>
        // عناصر DOM
        const addPurchaseBtn = document.getElementById('addPurchaseBtn');
        const purchaseModal = document.getElementById('purchaseModal');
        const closeModal = document.getElementById('closeModal');
        const cancelPurchaseBtn = document.getElementById('cancelPurchaseBtn');
        const purchaseForm = document.getElementById('purchaseForm');
        const modalTitle = document.getElementById('modalTitle');
        const purchaseId = document.getElementById('purchaseId');
        const formAction = document.getElementById('formAction');
        const searchInput = document.getElementById('searchInput');
        const productsList = document.getElementById('productsList');

        // فتح نموذج الإضافة
        addPurchaseBtn.addEventListener('click', () => {
            modalTitle.textContent = 'إضافة مشتريات جديدة';
            formAction.value = 'add';
            purchaseForm.reset();
            purchaseId.value = '';
            document.getElementById('purchaseDate').value = '<?php echo date('Y-m-d'); ?>';
            
            // إعادة تعيين المنتجات لصف واحد
            productsList.innerHTML = `
                <div class="product-row">
                    <div class="product-select">
                        <label>المنتج</label>
                        <select class="form-control product-select" name="product_id[]" required onchange="updatePrice(this)">
                            <option value="">اختر المنتج</option>
                            <?php foreach($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['selling_price']; ?>">
                                <?php echo $product['name']; ?> ($<?php echo number_format($product['selling_price'], 2); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="product-quantity">
                        <label>الكمية</label>
                        <input type="number" class="form-control" name="quantity[]" min="1" value="1" required oninput="calculateTotal(this)">
                    </div>
                    <div class="product-price">
                        <label>سعر الوحدة</label>
                        <input type="number" class="form-control" name="unit_price[]" min="0.01" step="0.01" required oninput="calculateTotal(this)">
                    </div>
                    <div class="product-total">
                        <label>المجموع</label>
                        <span class="item-total">$0.00</span>
                    </div>
                    <div class="product-actions">
                        <button type="button" class="btn-remove-product" onclick="removeProductRow(this)" disabled>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            updateTotals();
            purchaseModal.style.display = 'flex';
        });

        // إغلاق النماذج
        closeModal.addEventListener('click', () => {
            purchaseModal.style.display = 'none';
        });

        cancelPurchaseBtn.addEventListener('click', () => {
            purchaseModal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === purchaseModal) {
                purchaseModal.style.display = 'none';
            }
        });

        // وظائف إدارة المنتجات
        function addProductRow() {
            const productRow = document.createElement('div');
            productRow.className = 'product-row';
            productRow.innerHTML = `
                <div class="product-select">
                    <label>المنتج</label>
                    <select class="form-control product-select" name="product_id[]" required onchange="updatePrice(this)">
                        <option value="">اختر المنتج</option>
                        <?php foreach($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['selling_price']; ?>">
                            <?php echo $product['name']; ?> ($<?php echo number_format($product['selling_price'], 2); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="product-quantity">
                    <label>الكمية</label>
                    <input type="number" class="form-control" name="quantity[]" min="1" value="1" required oninput="calculateTotal(this)">
                </div>
                <div class="product-price">
                    <label>سعر الوحدة</label>
                    <input type="number" class="form-control" name="unit_price[]" min="0.01" step="0.01" required oninput="calculateTotal(this)">
                </div>
                <div class="product-total">
                    <label>المجموع</label>
                    <span class="item-total">$0.00</span>
                </div>
                <div class="product-actions">
                    <button type="button" class="btn-remove-product" onclick="removeProductRow(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            productsList.appendChild(productRow);
            updateTotals();
            
            // تفعيل أزرار الحذف
            const removeButtons = productsList.querySelectorAll('.btn-remove-product');
            removeButtons.forEach(btn => {
                btn.disabled = false;
            });
        }

        function removeProductRow(button) {
            const productRow = button.closest('.product-row');
            if (productsList.children.length > 1) {
                productRow.remove();
                updateTotals();
                
                // تفعيل/تعطيل أزرار الحذف
                const removeButtons = productsList.querySelectorAll('.btn-remove-product');
                if (removeButtons.length === 1) {
                    removeButtons[0].disabled = true;
                }
            }
        }

        function updatePrice(select) {
            const price = select.options[select.selectedIndex]?.dataset.price || 0;
            const row = select.closest('.product-row');
            const priceInput = row.querySelector('input[name="unit_price[]"]');
            
            priceInput.value = price;
            calculateTotal(select);
        }

        function calculateTotal(input) {
            const row = input.closest('.product-row');
            const quantity = row.querySelector('input[name="quantity[]"]').value || 0;
            const price = row.querySelector('input[name="unit_price[]"]').value || 0;
            const total = (quantity * price).toFixed(2);
            
            row.querySelector('.item-total').textContent = `$${total}`;
            updateTotals();
        }

        function updateTotals() {
            let totalItems = 0;
            let grandTotal = 0;
            
            document.querySelectorAll('.product-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
                const price = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
                
                if (quantity > 0 && price > 0) {
                    totalItems++;
                    grandTotal += (quantity * price);
                }
            });
            
            document.getElementById('totalItems').textContent = totalItems;
            document.getElementById('grandTotal').textContent = `$${grandTotal.toFixed(2)}`;
        }

        // البحث في المشتريات
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#purchasesTableBody tr');
            
            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let cell of cells) {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        });

        // تعديل المشتريات
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                editPurchase(id);
            });
        });

        // حذف المشتريات
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                if (confirm('هل أنت متأكد من حذف هذه المشتريات؟')) {
                    deletePurchase(id);
                }
            });
        });

        // دالة لتعديل المشتريات (نموذج مبسط - في التطبيق الحقيقي استخدم AJAX)
        function editPurchase(id) {
            // في التطبيق الحقيقي، يجب استخدام AJAX لجلب بيانات الشراء
            alert('في التطبيق الكامل، يجب استخدام AJAX لجلب بيانات الشراء للتعديل');
            
            modalTitle.textContent = 'تعديل المشتريات';
            formAction.value = 'update';
            purchaseId.value = id;
            
            // هنا يجب جلب البيانات الحقيقية عبر AJAX
            // في هذا المثال سنقوم بإعداد نموذج فارغ
            purchaseForm.reset();
            document.getElementById('purchaseDate').value = '<?php echo date('Y-m-d'); ?>';
            
            purchaseModal.style.display = 'flex';
        }

        // دالة لحذف المشتريات
        function deletePurchase(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            form.appendChild(idInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            
            form.submit();
        }

        // التحقق من صحة النموذج قبل الإرسال
        purchaseForm.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            // التحقق من المورد
            const supplier = document.getElementById('supplier');
            if (!supplier.value) {
                isValid = false;
                errorMessage += 'يجب اختيار مورد\n';
            }
            
            // التحقق من تاريخ الشراء
            const purchaseDate = document.getElementById('purchaseDate');
            if (!purchaseDate.value) {
                isValid = false;
                errorMessage += 'يجب اختيار تاريخ الشراء\n';
            }
            
            // التحقق من المنتجات
            const productSelects = document.querySelectorAll('.product-select');
            const quantities = document.querySelectorAll('input[name="quantity[]"]');
            const prices = document.querySelectorAll('input[name="unit_price[]"]');
            
            let hasValidProducts = false;
            for (let i = 0; i < productSelects.length; i++) {
                if (productSelects[i].value && quantities[i].value > 0 && prices[i].value > 0) {
                    hasValidProducts = true;
                    break;
                }
            }
            
            if (!hasValidProducts) {
                isValid = false;
                errorMessage += 'يجب إضافة منتج واحد على الأقل مع كمية وسعر صالحين\n';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });

        // تحديث الإجماليات عند التحميل
        document.addEventListener('DOMContentLoaded', updateTotals);
    </script>
</body>
</html>