<?php
// process_order_simple.php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// التحقق من وجود مستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يرجى تسجيل الدخول أولاً']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. إنشاء رقم فاتورة
    $invoice_number = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // 2. الحصول على العنوان
    $address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
    
    // 3. تحديد طريقة الدفع
    $payment_method = 'cash_on_delivery';
    if (isset($_POST['payment_method_id'])) {
        $stmt = $conn->prepare("SELECT type FROM payment_methods WHERE id = ?");
        $stmt->bind_param("i", $_POST['payment_method_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['type'] == 'bank') {
                $payment_method = 'bank_transfer';
            }
        }
        $stmt->close();
    }
    
    // 4. إنشاء الطلب
    $sql = "INSERT INTO orders (invoice_number, customer_id, order_date, total_amount, 
            payment_method, status, delivery_option_id, delivery_address_id, 
            delivery_cost, created_at) 
            VALUES (?, ?, NOW(), ?, ?, 'pending', ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisdiid", 
        $invoice_number,
        $user_id,
        $_POST['total'],
        $payment_method,
        $_POST['delivery_option_id'] ?? 1,
        $address_id,
        $_POST['shipping_cost'] ?? 0
    );
    
    if (!$stmt->execute()) {
        throw new Exception("فشل في إنشاء الطلب: " . $stmt->error);
    }
    
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // 5. إضافة عناصر الطلب
    if (isset($_POST['cart_items'])) {
        $cart_items = json_decode($_POST['cart_items'], true);
        if (is_array($cart_items)) {
            foreach ($cart_items as $item) {
                $sql = "INSERT INTO order_items (order_id, product_name, quantity, 
                        unit_price, total_price, product_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isiddi", 
                    $order_id,
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['total'],
                    $item['id']
                );
                $stmt->execute();
                $stmt->close();
                
                // تحديث المخزون
                $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $item['quantity'], $item['id']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // 6. إضافة التحويل البنكي إذا كان موجوداً
    if (isset($_POST['bank_transfer']) && !empty($_POST['bank_transfer'])) {
        $bank_data = json_decode($_POST['bank_transfer'], true);
        if ($bank_data) {
            $sql = "INSERT INTO order_bank_transfers (order_id, bank_account_id, 
                    transferee_name, transfer_date, transfer_amount, 
                    transfer_reference, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissds", 
                $order_id,
                $bank_data['bank_id'],
                $bank_data['transferee_name'],
                $bank_data['transfer_date'],
                $bank_data['transfer_amount'],
                $bank_data['transfer_reference'] ?? ''
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // 7. رفع الإيصال إذا كان موجوداً
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['receipt']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/receipts/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = 'receipt_' . $order_id . '_' . time() . '.jpg';
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $filepath)) {
                // تحديث الطلب
                $sql = "UPDATE orders SET bank_receipt = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $filepath, $order_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // 8. تطبيق القسيمة إذا كانت موجودة
    if (isset($_SESSION['applied_coupon'])) {
        $coupon = $_SESSION['applied_coupon'];
        
        $sql = "INSERT INTO order_coupons (order_id, coupon_id, coupon_code, 
                discount_amount, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisd", 
            $order_id,
            $coupon['id'],
            $coupon['code'],
            $coupon['discount']
        );
        $stmt->execute();
        $stmt->close();
        
        // زيادة عداد الاستخدام
        $sql = "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $coupon['id']);
        $stmt->execute();
        $stmt->close();
        
        unset($_SESSION['applied_coupon']);
    }
    
    // 9. تفريغ السلة
    unset($_SESSION['cart']);
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'invoice_number' => $invoice_number,
        'message' => 'تم إنشاء الطلب بنجاح'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ]);
}
?>