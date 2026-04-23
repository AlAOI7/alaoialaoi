<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول العميل
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// جلب طلبات العميل
$orders_sql = "SELECT id, invoice_number, total_amount, order_date 
               FROM orders 
               WHERE customer_id = ? 
               AND status = 'completed'
               ORDER BY order_date DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

// معالجة إنشاء طلب إرجاع جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $product_name = $_POST['product_name'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $return_reason = $_POST['return_reason'];
    $return_notes = $_POST['return_notes'];
    
    // حساب المبلغ المسترد
    $return_amount = $unit_price * $quantity;
    
    // إنشاء رقم إرجاع فريد
    $year = date('Y');
    $return_number = 'RET-' . $year . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // إدخال طلب الإرجاع
    $insert_sql = "INSERT INTO returns (return_number, order_id, customer_id, 
                    product_name, size, color, quantity, unit_price, 
                    return_reason, return_status, return_amount, return_notes, created_by) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("siisssidssi", $return_number, $order_id, $customer_id, 
                      $product_name, $size, $color, $quantity, $unit_price,
                      $return_reason, $return_amount, $return_notes, $customer_id);
    
    if ($stmt->execute()) {
        $return_id = $stmt->insert_id;
        
        // إضافة سجل في return_logs
        $log_sql = "INSERT INTO return_logs (return_id, action, description, created_by) 
                    VALUES (?, 'طلب إرجاع جديد', ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_desc = "تم إنشاء طلب إرجاع جديد للمنتج: $product_name";
        $log_stmt->bind_param("isi", $return_id, $log_desc, $customer_id);
        $log_stmt->execute();
        
        $_SESSION['success_message'] = "تم إنشاء طلب الإرجاع بنجاح! رقم الطلب: $return_number";
        header('Location: customer_returns.php');
        exit();
    } else {
        $error_message = "حدث خطأ أثناء إنشاء طلب الإرجاع";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب إرجاع جديد</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        .form-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .required:after {
            content: " *";
            color: red;
        }
        
        .order-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-right: 4px solid #4361ee;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold">
                    <i class="fas fa-exchange-alt me-2"></i>طلب إرجاع جديد
                </h2>
                <p class="text-muted">املأ النموذج التالي لإنشاء طلب إرجاع جديد</p>
            </div>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات طلب الإرجاع</h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="order_id" class="form-label required">اختر الطلب</label>
                            <select class="form-select" id="order_id" name="order_id" required>
                                <option value="">-- اختر الطلب --</option>
                                <?php foreach ($orders as $order): ?>
                                    <option value="<?php echo $order['id']; ?>">
                                        فاتورة #<?php echo $order['invoice_number']; ?> 
                                        - <?php echo date('Y/m/d', strtotime($order['order_date'])); ?> 
                                        - <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($orders)): ?>
                                <small class="text-danger">لا توجد طلبات مكتملة متاحة للإرجاع</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="product_name" class="form-label required">اسم المنتج</label>
                            <input type="text" class="form-control" id="product_name" 
                                   name="product_name" required 
                                   placeholder="أدخل اسم المنتج المراد إرجاعه">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="size" class="form-label">المقاس</label>
                            <input type="text" class="form-control" id="size" 
                                   name="size" placeholder="مثال: L, XL, 42, etc.">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="color" class="form-label">اللون</label>
                            <input type="text" class="form-control" id="color" 
                                   name="color" placeholder="أدخل لون المنتج">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="quantity" class="form-label required">الكمية</label>
                            <input type="number" class="form-control" id="quantity" 
                                   name="quantity" min="1" required value="1">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="unit_price" class="form-label required">سعر الوحدة (ريال)</label>
                            <input type="number" class="form-control" id="unit_price" 
                                   name="unit_price" step="0.01" min="0" required 
                                   placeholder="0.00">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="return_reason" class="form-label required">سبب الإرجاع</label>
                            <select class="form-select" id="return_reason" name="return_reason" required>
                                <option value="">-- اختر سبب الإرجاع --</option>
                                <option value="defective">منتج معيب</option>
                                <option value="wrong-item">منتج خاطئ</option>
                                <option value="damaged">منتج تالف</option>
                                <option value="not-needed">لم أعد أحتاجه</option>
                                <option value="other">سبب آخر</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="return_notes" class="form-label">ملاحظات إضافية</label>
                            <textarea class="form-control" id="return_notes" 
                                      name="return_notes" rows="4"
                                      placeholder="أدخل أي ملاحظات إضافية تفيد في معالجة طلب الإرجاع..."></textarea>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>معلومات مهمة:</h6>
                        <ul class="mb-0">
                            <li>سيتم مراجعة طلب الإرجاع خلال 1-3 أيام عمل</li>
                            <li>يجب أن يكون المنتج في حالته الأصلية للإرجاع</li>
                            <li>سيتم استرداد المبلغ عبر نفس طريقة الدفع الأصلية</li>
                            <li>للاستفسارات، يمكنك التواصل مع خدمة العملاء</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="customer_returns.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-1"></i>عودة
                        </a>
                        <button type="submit" class="btn btn-primary" 
                                <?php echo empty($orders) ? 'disabled' : ''; ?>>
                            <i class="fas fa-paper-plane me-1"></i>إرسال طلب الإرجاع
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
     <?php include 'footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // عرض معلومات الطلب عند الاختيار
        document.getElementById('order_id').addEventListener('change', function() {
            // يمكن إضافة جلب تفاصيل المنتجات الخاصة بالطلب هنا
        });
    </script>
</body>
</html>