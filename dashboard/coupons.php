<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// إنشاء مجلد الصور إذا لم يكن موجوداً
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// دالة توليد كود قسيمة
function generateCouponCode($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

// معالجة طلبات POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // إضافة قسيمة جديدة
    if (isset($_POST['add_coupon'])) {

        $code = $conn->real_escape_string($_POST['code']);
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $type = $_POST['type'];
        $discount_type = $_POST['discount_type'];
        $discount_value = $_POST['discount_value'];
        $min_order_amount = $_POST['min_order_amount'] ?: 0;
        $max_discount_amount = $_POST['max_discount_amount'] ?: NULL;
        $usage_limit = $_POST['usage_limit'] ?: NULL;
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $categories = isset($_POST['categories']) ? implode(',', $_POST['categories']) : NULL;
        $products = isset($_POST['products']) ? implode(',', $_POST['products']) : NULL;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // تحقق من التكرار
        $check = $conn->query("SELECT id FROM coupons WHERE code='$code'");
        if ($check->num_rows > 0) {
            $_SESSION['error'] = "كود القسيمة مسجل مسبقاً!";
            header("Location: coupons.php");
            exit();
        }

        // إدخال
        $sql = "INSERT INTO coupons (code, name, description, type, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, start_date, end_date, categories, products, is_active)
                VALUES ('$code', '$name', '$description', '$type', '$discount_type', '$discount_value', 
                        '$min_order_amount', " . ($max_discount_amount ? "'$max_discount_amount'" : "NULL") . ",
                        " . ($usage_limit ? "'$usage_limit'" : "NULL") . ", 
                        '$start_date', '$end_date', 
                        " . ($categories ? "'$categories'" : "NULL") . ", 
                        " . ($products ? "'$products'" : "NULL") . ", 
                        '$is_active')";

        $conn->query($sql);

        $_SESSION['message'] = "تم إضافة القسيمة بنجاح!";
        header("Location: coupons.php");
        exit();
    }

    // تحديث قسيمة
    if (isset($_POST['update_coupon'])) {

        $id = $_POST['id'];
        $code = $conn->real_escape_string($_POST['code']);
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $type = $_POST['type'];
        $discount_type = $_POST['discount_type'];
        $discount_value = $_POST['discount_value'];
        $min_order_amount = $_POST['min_order_amount'] ?: 0;
        $max_discount_amount = $_POST['max_discount_amount'] ?: NULL;
        $usage_limit = $_POST['usage_limit'] ?: NULL;
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $categories = isset($_POST['categories']) ? implode(',', $_POST['categories']) : NULL;
        $products = isset($_POST['products']) ? implode(',', $_POST['products']) : NULL;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // تحقق من التكرار مع استثناء السجل
        $check = $conn->query("SELECT id FROM coupons WHERE code='$code' AND id!=$id");
        if ($check->num_rows > 0) {
            $_SESSION['error'] = "كود القسيمة مسجل مسبقاً!";
            header("Location: coupons.php");
            exit();
        }

        // تحديث
        $sql = "UPDATE coupons SET
                code='$code', name='$name', description='$description', type='$type',
                discount_type='$discount_type', discount_value='$discount_value',
                min_order_amount='$min_order_amount',
                max_discount_amount=" . ($max_discount_amount ? "'$max_discount_amount'" : "NULL") . ",
                usage_limit=" . ($usage_limit ? "'$usage_limit'" : "NULL") . ",
                start_date='$start_date', end_date='$end_date',
                categories=" . ($categories ? "'$categories'" : "NULL") . ",
                products=" . ($products ? "'$products'" : "NULL") . ",
                is_active='$is_active'
                WHERE id=$id";

        $conn->query($sql);

        $_SESSION['message'] = "تم تحديث القسيمة بنجاح!";
        header("Location: coupons.php");
        exit();
    }

    // حذف قسيمة
    if (isset($_POST['delete_coupon'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM coupons WHERE id=$id");
        $_SESSION['message'] = "تم حذف القسيمة بنجاح!";
        header("Location: coupons.php");
        exit();
    }

    // توليد كود
    if (isset($_POST['generate_code'])) {
        $_SESSION['generated_code'] = generateCouponCode();
        header("Location: coupons.php");
        exit();
    }
}

// جلب قسيمة للتعديل
$edit_coupon = null;
if (isset($_GET['edit'])) {

    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM coupons WHERE id=$id");

    if ($result->num_rows > 0) {
        $edit_coupon = $result->fetch_assoc();
        $edit_coupon['categories'] = $edit_coupon['categories'] ? explode(',', $edit_coupon['categories']) : [];
        $edit_coupon['products'] = $edit_coupon['products'] ? explode(',', $edit_coupon['products']) : [];
    }
}

// جلب كل القسائم
$coupons = [];
$res = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    $coupons[] = $row;
}

// جلب الفئات
$categories = [];
$res = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}

// جلب المنتجات
$products = [];
$res = $conn->query("SELECT id, name FROM products WHERE is_active = 1 ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة القسائم - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            min-height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
        }
        
        .badge-active {
            background-color: #4cc9f0;
        }
        
        .badge-inactive {
            background-color: #6c757d;
        }
        
        .badge-general {
            background-color: #4cc9f0;
        }
        
        .badge-category {
            background-color: #9d4edd;
        }
        
        .badge-product {
            background-color: #f72585;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e1e5ee;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-weight: 700;
        }
        
        .action-buttons .btn {
            margin-left: 5px;
            padding: 5px 10px;
        }
        
        .coupon-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px dashed #dee2e6;
        }
        
        .type-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: white;
        }
        
        .conditional-fields {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-right: 3px solid var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 15px;
            }
        }
        
        .coupon-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
       <?php include 'header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
             <?php include 'sidebar.php'; ?>
            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-ticket-alt me-2"></i> إدارة القسائم</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal">
                        <i class="fas fa-plus me-1"></i> إضافة قسيمة جديدة
                    </button>
                </div>

                <!-- رسائل التنبيه -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- إحصائيات القسائم -->
                <div class="coupon-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($coupons); ?></div>
                        <div class="stat-label">إجمالي القسائم</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            $active_coupons = array_filter($coupons, function($coupon) {
                                return $coupon['is_active'] && $coupon['end_date'] >= date('Y-m-d');
                            });
                            echo count($active_coupons);
                            ?>
                        </div>
                        <div class="stat-label">قسائم نشطة</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            $used_coupons = array_sum(array_column($coupons, 'used_count'));
                            echo $used_coupons;
                            ?>
                        </div>
                        <div class="stat-label">مرات الاستخدام</div>
                    </div>
                </div>

                <!-- جدول القسائم -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>قائمة القسائم</span>
                        <span class="badge bg-primary"><?php echo count($coupons); ?> قسيمة</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>كود القسيمة</th>
                                        <th>اسم القسيمة</th>
                                        <th>نوع القسيمة</th>
                                        <th>الخصم</th>
                                        <th>الفترة</th>
                                        <th>الاستخدام</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($coupons) > 0): ?>
                                        <?php foreach ($coupons as $index => $coupon): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <span class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($coupon['name']); ?></div>
                                                    <small class="text-muted"><?php echo substr(htmlspecialchars($coupon['description']), 0, 50); ?>...</small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $type_badges = [
                                                        'general' => ['class' => 'badge-general', 'text' => 'عامة'],
                                                        'category' => ['class' => 'badge-category', 'text' => 'فئة'],
                                                        'product' => ['class' => 'badge-product', 'text' => 'منتج']
                                                    ];
                                                    $type_info = $type_badges[$coupon['type']];
                                                    ?>
                                                    <span class="type-badge <?php echo $type_info['class']; ?>">
                                                        <?php echo $type_info['text']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">
                                                        <?php 
                                                        if ($coupon['discount_type'] == 'percentage') {
                                                            echo $coupon['discount_value'] . '%';
                                                        } else {
                                                            echo '$' . $coupon['discount_value'];
                                                        }
                                                        ?>
                                                    </span>
                                                    <?php if ($coupon['min_order_amount'] > 0): ?>
                                                        <br>
                                                        <small class="text-muted">الحد الأدنى: $<?php echo $coupon['min_order_amount']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('Y-m-d', strtotime($coupon['start_date'])); ?> <br>
                                                        إلى <?php echo date('Y-m-d', strtotime($coupon['end_date'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if ($coupon['usage_limit']): ?>
                                                        <small>
                                                            <?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small>غير محدود</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $is_expired = $coupon['end_date'] < date('Y-m-d');
                                                    $is_usage_limit_reached = $coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit'];
                                                    ?>
                                                    <span class="badge <?php echo ($coupon['is_active'] && !$is_expired && !$is_usage_limit_reached) ? 'badge-active' : 'badge-inactive'; ?>">
                                                        <?php 
                                                        if (!$coupon['is_active']) {
                                                            echo 'غير نشطة';
                                                        } elseif ($is_expired) {
                                                            echo 'منتهية';
                                                        } elseif ($is_usage_limit_reached) {
                                                            echo 'مستنفذة';
                                                        } else {
                                                            echo 'نشطة';
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="coupons.php?edit=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه القسيمة؟');">
                                                        <input type="hidden" name="id" value="<?php echo $coupon['id']; ?>">
                                                        <button type="submit" name="delete_coupon" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-ticket-alt fa-2x mb-3"></i>
                                                <p>لا توجد قسائم مضافة حالياً</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة/تعديل القسيمة -->
    <div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="couponModalLabel">
                        <?php echo $edit_coupon ? 'تعديل القسيمة' : 'إضافة قسيمة جديدة'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if ($edit_coupon): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_coupon['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">كود القسيمة</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="code" name="code" 
                                           value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['code']) : (isset($_SESSION['generated_code']) ? $_SESSION['generated_code'] : ''); ?>" 
                                           required>
                                    <button type="submit" name="generate_code" class="btn btn-outline-secondary">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <?php unset($_SESSION['generated_code']); ?>
                                <div class="form-text">سيتم استخدام هذا الكود من قبل العملاء لتطبيق الخصم</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">اسم القسيمة</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $edit_coupon ? htmlspecialchars($edit_coupon['name']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف القسيمة</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo $edit_coupon ? htmlspecialchars($edit_coupon['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">نوع القسيمة</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="general" <?php echo ($edit_coupon && $edit_coupon['type'] == 'general') ? 'selected' : ''; ?>>عامة (تنطبق على جميع المنتجات)</option>
                                    <option value="category" <?php echo ($edit_coupon && $edit_coupon['type'] == 'category') ? 'selected' : ''; ?>>لفئة محددة</option>
                                    <option value="product" <?php echo ($edit_coupon && $edit_coupon['type'] == 'product') ? 'selected' : ''; ?>>لمنتجات محددة</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="discount_type" class="form-label">نوع الخصم</label>
                                <select class="form-select" id="discount_type" name="discount_type" required>
                                    <option value="percentage" <?php echo ($edit_coupon && $edit_coupon['discount_type'] == 'percentage') ? 'selected' : ''; ?>>نسبة مئوية</option>
                                    <option value="fixed" <?php echo ($edit_coupon && $edit_coupon['discount_type'] == 'fixed') ? 'selected' : ''; ?>>مبلغ ثابت</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="discount_value" class="form-label">قيمة الخصم</label>
                                <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                       step="0.01" min="0" value="<?php echo $edit_coupon ? $edit_coupon['discount_value'] : ''; ?>" required>
                                <div class="form-text" id="discount_hint">
                                    <?php echo ($edit_coupon && $edit_coupon['discount_type'] == 'percentage') ? 'أدخل النسبة المئوية للخصم' : 'أدخل المبلغ الثابت للخصم'; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="min_order_amount" class="form-label">الحد الأدنى للطلب</label>
                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                       step="0.01" min="0" value="<?php echo $edit_coupon ? $edit_coupon['min_order_amount'] : '0'; ?>">
                                <div class="form-text">الحد الأدنى لقيمة الطلب لتطبيق القسيمة (0 يعني لا يوجد حد أدنى)</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_discount_amount" class="form-label">أقصى مبلغ للخصم</label>
                                <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" 
                                       step="0.01" min="0" value="<?php echo $edit_coupon ? $edit_coupon['max_discount_amount'] : ''; ?>">
                                <div class="form-text">لخصم النسبة المئوية فقط - اتركه فارغاً لعدم تحديد حد أقصى</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="usage_limit" class="form-label">الحد الأقصى للاستخدام</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" 
                                       min="0" value="<?php echo $edit_coupon ? $edit_coupon['usage_limit'] : ''; ?>">
                                <div class="form-text">الحد الأقصى لعدد مرات استخدام القسيمة - اتركه فارغاً لاستخدام غير محدود</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">تاريخ البدء</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo $edit_coupon ? $edit_coupon['start_date'] : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">تاريخ الانتهاء</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo $edit_coupon ? $edit_coupon['end_date'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <!-- الحقول المشروطة حسب نوع القسيمة -->
                        <div id="conditionalFields">
                            <!-- فئات (لنوع category) -->
                            <div id="categoryFields" class="conditional-fields" style="display: <?php echo ($edit_coupon && $edit_coupon['type'] == 'category') ? 'block' : 'none'; ?>;">
                                <h6>الفئات المستهدفة</h6>
                                <div class="row">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="categories[]" 
                                                       value="<?php echo $category['id']; ?>" 
                                                       id="category_<?php echo $category['id']; ?>"
                                                       <?php echo ($edit_coupon && in_array($category['id'], $edit_coupon['categories'])) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- منتجات (لنوع product) -->
                            <div id="productFields" class="conditional-fields" style="display: <?php echo ($edit_coupon && $edit_coupon['type'] == 'product') ? 'block' : 'none'; ?>;">
                                <h6>المنتجات المستهدفة</h6>
                                <div class="row">
                                    <?php foreach ($products as $product): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="products[]" 
                                                       value="<?php echo $product['id']; ?>" 
                                                       id="product_<?php echo $product['id']; ?>"
                                                       <?php echo ($edit_coupon && in_array($product['id'], $edit_coupon['products'])) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="product_<?php echo $product['id']; ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo ($edit_coupon && $edit_coupon['is_active']) || !$edit_coupon ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">القسيمة نشطة</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="<?php echo $edit_coupon ? 'update_coupon' : 'add_coupon'; ?>" class="btn btn-primary">
                            <?php echo $edit_coupon ? 'تحديث القسيمة' : 'إضافة القسيمة'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التحكم في عرض الحقول المشروطة حسب نوع القسيمة
        document.getElementById('type').addEventListener('change', function() {
            const categoryFields = document.getElementById('categoryFields');
            const productFields = document.getElementById('productFields');
            
            categoryFields.style.display = 'none';
            productFields.style.display = 'none';
            
            if (this.value === 'category') {
                categoryFields.style.display = 'block';
            } else if (this.value === 'product') {
                productFields.style.display = 'block';
            }
        });
        
        // تحديث نص التلميح حسب نوع الخصم
        document.getElementById('discount_type').addEventListener('change', function() {
            const discountHint = document.getElementById('discount_hint');
            if (this.value === 'percentage') {
                discountHint.textContent = 'أدخل النسبة المئوية للخصم';
            } else {
                discountHint.textContent = 'أدخل المبلغ الثابت للخصم';
            }
        });
        
        // فتح المودال تلقائياً عند النقر على تعديل
        <?php if ($edit_coupon): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var couponModal = new bootstrap.Modal(document.getElementById('couponModal'));
                couponModal.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>