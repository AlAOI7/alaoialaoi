<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول العميل
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$return_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// جلب بيانات طلب الإرجاع
$sql = "SELECT * FROM returns WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $return_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$return = $result->fetch_assoc();

// التحقق من وجود الطلب وإمكانية تعديله
if (!$return || $return['return_status'] != 'pending') {
    $_SESSION['error_message'] = "لا يمكن تعديل هذا الطلب أو غير موجود";
    header('Location: customer_returns.php');
    exit();
}

// معالجة تحديث طلب الإرجاع
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $return_reason = $_POST['return_reason'];
    $return_notes = $_POST['return_notes'];
    
    // حساب المبلغ المسترد الجديد
    $return_amount = $unit_price * $quantity;
    
    // تحديث بيانات طلب الإرجاع
    $update_sql = "UPDATE returns SET 
                    product_name = ?, 
                    size = ?, 
                    color = ?, 
                    quantity = ?, 
                    unit_price = ?, 
                    return_reason = ?, 
                    return_amount = ?, 
                    return_notes = ?,
                    updated_at = CURRENT_TIMESTAMP 
                   WHERE id = ? AND customer_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssidssiii", $product_name, $size, $color, 
                           $quantity, $unit_price, $return_reason, 
                           $return_amount, $return_notes, $return_id, $customer_id);
    
    if ($update_stmt->execute()) {
        // إضافة سجل في return_logs
        $log_sql = "INSERT INTO return_logs (return_id, action, description, created_by) 
                    VALUES (?, 'تعديل طلب الإرجاع', 'تم تعديل بيانات طلب الإرجاع', ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("ii", $return_id, $customer_id);
        $log_stmt->execute();
        
        $_SESSION['success_message'] = "تم تحديث طلب الإرجاع بنجاح!";
        header('Location: customer_returns.php');
        exit();
    } else {
        $error_message = "حدث خطأ أثناء تحديث طلب الإرجاع";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل طلب إرجاع</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold">
                    <i class="fas fa-edit me-2"></i>تعديل طلب إرجاع
                </h2>
                <p class="text-muted">رقم الطلب: <?php echo htmlspecialchars($return['return_number']); ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">تعديل بيانات طلب الإرجاع</h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">رقم الإرجاع</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($return['return_number']); ?>" 
                                   readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">حالة الطلب</label>
                            <input type="text" class="form-control" 
                                   value="قيد المراجعة" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_name" class="form-label required">اسم المنتج</label>
                            <input type="text" class="form-control" id="product_name" 
                                   name="product_name" required 
                                   value="<?php echo htmlspecialchars($return['product_name']); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="size" class="form-label">المقاس</label>
                            <input type="text" class="form-control" id="size" 
                                   name="size" 
                                   value="<?php echo htmlspecialchars($return['size']); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="color" class="form-label">اللون</label>
                            <input type="text" class="form-control" id="color" 
                                   name="color" 
                                   value="<?php echo htmlspecialchars($return['color']); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="quantity" class="form-label required">الكمية</label>
                            <input type="number" class="form-control" id="quantity" 
                                   name="quantity" min="1" required 
                                   value="<?php echo htmlspecialchars($return['quantity']); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="unit_price" class="form-label required">سعر الوحدة (ريال)</label>
                            <input type="number" class="form-control" id="unit_price" 
                                   name="unit_price" step="0.01" min="0" required 
                                   value="<?php echo htmlspecialchars($return['unit_price']); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="return_reason" class="form-label required">سبب الإرجاع</label>
                            <select class="form-select" id="return_reason" name="return_reason" required>
                                <option value="defective" <?php echo $return['return_reason'] == 'defective' ? 'selected' : ''; ?>>منتج معيب</option>
                                <option value="wrong-item" <?php echo $return['return_reason'] == 'wrong-item' ? 'selected' : ''; ?>>منتج خاطئ</option>
                                <option value="damaged" <?php echo $return['return_reason'] == 'damaged' ? 'selected' : ''; ?>>منتج تالف</option>
                                <option value="not-needed" <?php echo $return['return_reason'] == 'not-needed' ? 'selected' : ''; ?>>لم أعد أحتاجه</option>
                                <option value="other" <?php echo $return['return_reason'] == 'other' ? 'selected' : ''; ?>>سبب آخر</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="return_notes" class="form-label">ملاحظات إضافية</label>
                            <textarea class="form-control" id="return_notes" 
                                      name="return_notes" rows="4"><?php echo htmlspecialchars($return['return_notes']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>ملاحظة:</h6>
                        <p class="mb-0">يمكنك تعديل طلب الإرجاع فقط عندما يكون في حالة "قيد المراجعة".</p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="customer_returns.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>