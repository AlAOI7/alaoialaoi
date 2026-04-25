<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // 1. التحقق من طريقة الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('طريقة طلب غير صحيحة');
    }

    // 2. التحقق من الجلسة والسلة
    $user_id = null;
    $cart_items = [];
    
    if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
        $user_id = $_SESSION['user_id'];
        
        // جلب السلة من قاعدة البيانات
        $cart_sql = "SELECT c.*, p.name, p.selling_price
                     FROM cart c
                     JOIN products p ON c.product_id = p.id
                     WHERE c.user_id = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        
        while ($item = $cart_result->fetch_assoc()) {
            $cart_items[] = [
                'id' => $item['product_id'],
                'name' => $item['name'],
                'selling_price' => $item['selling_price'],
                'quantity' => $item['quantity']
            ];
        }
    } elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // للزوار: استخدام سلة الجلسة
        $cart_items = $_SESSION['cart'];
        $user_id = 0; // زائر
    }
    
    if (empty($cart_items)) {
        throw new Exception('السلة فارغة. الرجاء إضافة منتجات أولاً');
    }
    
    // 3. جلب بيانات النموذج
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'cod');
    
    // التحقق من البيانات الأساسية
    if (empty($full_name) || empty($phone) || empty($city)) {
        throw new Exception('الرجاء ملء جميع الحقول المطلوبة (الاسم، الهاتف، المدينة)');
    }
    
    // حفظ أو تحديث العنوان للمستخدم المسجل
    if ($user_id > 0) {
        // تحديث الاسم والهاتف في جدول users إذا لزم الأمر
        $update_user = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ? AND (full_name IS NULL OR phone IS NULL)");
        $update_user->bind_param("ssi", $full_name, $phone, $user_id);
        $update_user->execute();

        // التحقق من وجود عنوان للمستخدم
        $check_addr = $conn->prepare("SELECT id FROM delivery_addresses WHERE user_id = ? AND is_default = 1");
        $check_addr->bind_param("i", $user_id);
        $check_addr->execute();
        $addr_res = $check_addr->get_result();

        if ($addr_res->num_rows > 0) {
            // تحديث العنوان الحالي
            $update_addr = $conn->prepare("UPDATE delivery_addresses SET city = ?, district = ?, street = ? WHERE user_id = ? AND is_default = 1");
            $update_addr->bind_param("sssi", $city, $district, $street, $user_id);
            $update_addr->execute();
        } else {
            // إضافة عنوان جديد وافتراضي
            $insert_addr = $conn->prepare("INSERT INTO delivery_addresses (user_id, city, district, street, is_default) VALUES (?, ?, ?, ?, 1)");
            $insert_addr->bind_param("isss", $user_id, $city, $district, $street);
            $insert_addr->execute();
        }
    }
    
    // 4. حساب المجاميع
    $subtotal = 0;
    foreach ($cart_items as $item) {
        if (isset($item['selling_price']) && isset($item['quantity'])) {
            $subtotal += $item['selling_price'] * $item['quantity'];
        }
    }
    
    $shipping_cost = 15;
    $total_amount = $subtotal + $shipping_cost;
    
    // 5. تحديد حالة الطلب بناءً على طريقة الدفع
    $order_status = 'pending';
    $payment_status = 'pending';
    $reservation_expires_at = null;
    
    if ($payment_method == 'cod') {
        $order_status = 'confirmed';
        $payment_status = 'pending';
    } elseif ($payment_method == 'reserve') {
        $order_status = 'reserved';
        $payment_status = 'pending';
        $reservation_expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    } elseif ($payment_method == 'bank_transfer') {
        $order_status = 'pending';
        $payment_status = 'pending';
    }
    
    // 6. بدء المعاملة
    $conn->begin_transaction();
    
    // 7. إنشاء الطلب
    $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    $order_date = date('Y-m-d H:i:s');
    
    $order_sql = "INSERT INTO orders (
        invoice_number, customer_id, order_date, subtotal, total_amount,
        full_name, phone, city, district, street, notes,
        payment_method, status, payment_status, delivery_cost, reservation_expires_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($order_sql);
    if (!$stmt) {
        throw new Exception('خطأ في قاعدة البيانات: ' . $conn->error);
    }
    
    $stmt->bind_param("sissddssssssssds", 
        $invoice_number, $user_id, $order_date, $subtotal, $total_amount,
        $full_name, $phone, $city, $district, $street, $notes,
        $payment_method, $order_status, $payment_status, $shipping_cost, $reservation_expires_at
    );
    
    if (!$stmt->execute()) {
        throw new Exception('فشل في حفظ الطلب: ' . $stmt->error);
    }
    
    $order_id = $conn->insert_id;
    
    // 8. إضافة عناصر الطلب
    $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) 
                 VALUES (?, ?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    
    foreach ($cart_items as $item) {
        if (!isset($item['id']) || !isset($item['selling_price']) || !isset($item['quantity'])) {
            continue;
        }
        
        $item_total = $item['selling_price'] * $item['quantity'];
        
        $item_stmt->bind_param("iisidd", 
            $order_id, $item['id'], $item['name'],
            $item['quantity'], $item['selling_price'], $item_total
        );
        $item_stmt->execute();
        
        // تحديث المخزون (للدفع عند الاستلام والحجز فقط)
        if ($payment_method == 'reserve' || $payment_method == 'cod') {
            $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $update_stock->bind_param("ii", $item['quantity'], $item['id']);
            $update_stock->execute();
        }
    }
    
    // 9. معالجة التحويل البنكي إذا كان موجوداً
    if ($payment_method == 'bank_transfer') {
        $sender_name = trim($_POST['sender_name'] ?? '');
        $transfer_amount = floatval($_POST['transfer_amount'] ?? 0);
        
        // معالجة الملف المرفق
        $receipt_image = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
            $upload_dir = '../uploads/receipts/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                $filename = 'receipt_' . $order_id . '_' . time() . '.' . $file_ext;
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_dir . $filename)) {
                    $receipt_image = 'uploads/receipts/' . $filename;
                }
            }
        }
        
        // إضافة سجل التحويل البنكي
        $bank_sql = "INSERT INTO bank_transfer_receipts 
                     (order_id, sender_name, transfer_amount, receipt_image, verification_status) 
                     VALUES (?, ?, ?, ?, 'pending')";
        $bank_stmt = $conn->prepare($bank_sql);
        $bank_stmt->bind_param("isds", $order_id, $sender_name, $transfer_amount, $receipt_image);
        $bank_stmt->execute();
    }
    
    // 10. معالجة الحجز
    if ($payment_method == 'reserve') {
        $reserve_sql = "INSERT INTO order_reservations (order_id, expires_at, status) 
                        VALUES (?, ?, 'active')";
        $reserve_stmt = $conn->prepare($reserve_sql);
        $reserve_stmt->bind_param("is", $order_id, $reservation_expires_at);
        $reserve_stmt->execute();
    }
    
    // 11. تفريغ السلة
    if ($user_id > 0) {
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");
    }
    unset($_SESSION['cart']);
    
    // 12. إتمام المعاملة
    $conn->commit();
    
    // 13. إرجاع النجاح
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'invoice_number' => $invoice_number,
        'message' => 'تم إنشاء الطلب بنجاح!'
    ]);
    
} catch (Exception $e) {
    // التراجع عن المعاملة في حالة الفشل
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
