<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    
    // توليد رقم فاتورة
    $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // إدخال الطلب
    $orderData = [
        'invoice_number' => $invoiceNumber,
        'customer_id' => $userId,
        'order_date' => date('Y-m-d'),
        'total_amount' => $_POST['total'],
        'payment_method' => 'bank_transfer', // سيتم تحديثه بناءً على نوع الدفع
        'delivery_method' => 'normal_delivery',
        'status' => 'pending',
        'delivery_option_id' => $_POST['delivery_option_id'],
        'delivery_address_id' => $_POST['delivery_address_id'],
        'delivery_cost' => $_POST['delivery_cost']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (invoice_number, customer_id, order_date, total_amount, payment_method, 
         delivery_method, status, delivery_option_id, delivery_address_id, delivery_cost) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $orderData['invoice_number'],
        $orderData['customer_id'],
        $orderData['order_date'],
        $orderData['total_amount'],
        $orderData['payment_method'],
        $orderData['delivery_method'],
        $orderData['status'],
        $orderData['delivery_option_id'],
        $orderData['delivery_address_id'],
        $orderData['delivery_cost']
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // إدخال منتجات الطلب
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.selling_price, 
               COALESCE(pi.image_path, 'img/default.jpg') as image
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $orderItemsStmt = $pdo->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, size, color, quantity, unit_price, total_price, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cartItems as $item) {
        $totalPrice = $item['selling_price'] * $item['quantity'];
        $orderItemsStmt->execute([
            $orderId,
            $item['product_id'],
            $item['name'],
            $item['size_id'],
            $item['color_id'],
            $item['quantity'],
            $item['selling_price'],
            $totalPrice,
            $item['image']
        ]);
        
        // تحديث المخزون
        $updateStmt = $pdo->prepare("
            UPDATE products SET quantity = quantity - ? WHERE id = ?
        ");
        $updateStmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // إذا كان هناك قسيمة
    if (!empty($_POST['coupon_code'])) {
        $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
        $stmt->execute([$_POST['coupon_code']]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon) {
            $orderCouponStmt = $pdo->prepare("
                INSERT INTO order_coupons 
                (order_id, coupon_id, coupon_code, discount_amount, original_total, final_total) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $orderCouponStmt->execute([
                $orderId,
                $coupon['id'],
                $_POST['coupon_code'],
                $_POST['discount'],
                $_POST['subtotal'],
                $_POST['total']
            ]);
            
            // زيادة عدد استخدامات القسيمة
            $updateStmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
            $updateStmt->execute([$coupon['id']]);
        }
    }
    
    // إذا كان الدفع بنكي
    if (isset($_POST['bank_account_id'])) {
        // رفع صورة الإيصال
        $receiptImage = null;
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
            $uploadDir = 'uploads/receipts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['receipt']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetPath)) {
                $receiptImage = $targetPath;
            }
        }
        
        // إدخال بيانات التحويل
        $transferStmt = $pdo->prepare("
            INSERT INTO order_bank_transfers 
            (order_id, bank_account_id, transferee_name, transfer_date, transfer_amount, receipt_image) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $transferStmt->execute([
            $orderId,
            $_POST['bank_account_id'],
            $_POST['transferee_name'],
            $_POST['transfer_date'],
            $_POST['transfer_amount'],
            $receiptImage
        ]);
        
        // تحديث طريقة الدفع في الطلب
        $updateStmt = $pdo->prepare("UPDATE orders SET payment_method = 'bank_transfer' WHERE id = ?");
        $updateStmt->execute([$orderId]);
    } else {
        // تحديث طريقة الدفع للدفع عند الاستلام
        $updateStmt = $pdo->prepare("UPDATE orders SET payment_method = 'cash_on_delivery' WHERE id = ?");
        $updateStmt->execute([$orderId]);
    }
    
    // حذف سلة المشتريات
    $deleteStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $deleteStmt->execute([$userId]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'تم إرسال الطلب بنجاح',
        'order_id' => $orderId,
        'order_number' => $invoiceNumber
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ في إرسال الطلب: ' . $e->getMessage()
    ]);
}
?>