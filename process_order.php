<?php
// بدء الجلسة واستدعاء الاتصال
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'config/database.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'انتهت الجلسة، يرجى تسجيل الدخول']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    if (!$conn) {
        throw new Exception("فشل الاتصال بقاعدة البيانات");
    }
    
    $conn->begin_transaction();

    // 1. استلام البيانات
    $address_id         = intval($_POST['address_id'] ?? 0);
    $delivery_option_id = intval($_POST['delivery_option_id'] ?? 1);
    $total              = floatval($_POST['total'] ?? 0);
    $subtotal           = floatval($_POST['subtotal'] ?? 0);
    $delivery_cost      = floatval($_POST['shipping_cost'] ?? 0);
    $notes              = $_POST['delivery_notes'] ?? '';
    $payment_method_id  = intval($_POST['payment_method_id'] ?? 0);

    // 2. تحديد نوع الدفع
    $payment_enum = 'cash_on_delivery';
    if ($payment_method_id > 0) {
        $pm_query = $conn->query("SELECT type FROM payment_methods WHERE id = $payment_method_id");
        if ($pm_query && $row = $pm_query->fetch_assoc()) {
            if ($row['type'] === 'bank') $payment_enum = 'bank_transfer';
            elseif ($row['type'] === 'card') $payment_enum = 'credit_card';
        }
    }

    // 3. إنشاء رقم الفاتورة
    $invoice = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

    // 4. إدخال الطلب الرئيسي (تم ضبط عدد الـ ? لتكون 11)
    $sql_order = "INSERT INTO orders (
        invoice_number, customer_id, order_date, total_amount, subtotal, 
        payment_method, delivery_method, status, delivery_option_id, 
        delivery_address_id, delivery_cost, delivery_notes, created_at
    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql_order);
    if (!$stmt) {
        throw new Exception("فشل إعداد الاستعلام: " . $conn->error);
    }

    $delivery_method = 'normal_delivery';
    $status = 'pending';
    
    // الترتيب الصحيح لـ 11 علامة استفهام:
    // 1.invoice(s), 2.user(i), 3.total(d), 4.subtotal(d), 5.payment(s), 6.delivery_m(s), 
    // 7.status(s), 8.option_id(i), 9.address_id(i), 10.cost(d), 11.notes(s)
    $stmt->bind_param(
        "sidddsssids", 
        $invoice, 
        $userId, 
        $total, 
        $subtotal, 
        $payment_enum, 
        $delivery_method,
        $status,
        $delivery_option_id, 
        $address_id, 
        $delivery_cost, 
        $notes
    );
    
    if (!$stmt->execute()) {
        throw new Exception("فشل إدخال الطلب الرئيسي: " . $stmt->error);
    }
    $orderId = $stmt->insert_id;
    $stmt->close();

    // 5. جلب المنتجات من السلة
    $res_cart = $conn->query("SELECT c.*, p.name, p.selling_price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = '$userId'");
    
    if (!$res_cart || $res_cart->num_rows === 0) {
        throw new Exception("السلة فارغة");
    }

    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    
    while ($item = $res_cart->fetch_assoc()) {
        $item_total = $item['selling_price'] * $item['quantity'];
        $stmt_item->bind_param(
            "iisidd", 
            $orderId, 
            $item['product_id'], 
            $item['name'], 
            $item['quantity'], 
            $item['selling_price'], 
            $item_total
        );
        $stmt_item->execute();
        
        // تحديث المخزون
        $conn->query("UPDATE products SET quantity = quantity - {$item['quantity']} WHERE id = {$item['product_id']}");
    }
    $stmt_item->close();

    // 6. معالجة الإيصال البنكي
    if ($payment_enum === 'bank_transfer' && isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $upload_dir = 'uploads/receipts/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        
        $file_ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $file_name = 'receipt_' . $orderId . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {
            $conn->query("UPDATE orders SET bank_receipt = '$target_path' WHERE id = $orderId");
        }
    }

    // 7. حذف السلة
    $conn->query("DELETE FROM cart WHERE user_id = '$userId'");
    
    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId, 'invoice_number' => $invoice]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) $conn->close();