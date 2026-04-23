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



// معالجة الإضافة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_offer'])) {

        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $type = $conn->real_escape_string($_POST['type']);
        $product_id = ($type == 'product') ? intval($_POST['product_id']) : "NULL";
        $discount_type = $conn->real_escape_string($_POST['discount_type']);
        $discount_value = $conn->real_escape_string($_POST['discount_value']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        $status = $conn->real_escape_string($_POST['status']);

        $sql = "INSERT INTO offers 
                (name, description, type, product_id, discount_type, discount_value, start_date, end_date, status)
                VALUES 
                ('$name', '$description', '$type', $product_id, '$discount_type', '$discount_value', '$start_date', '$end_date', '$status')";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "تم إضافة العرض بنجاح!";
        } else {
            $_SESSION['message'] = "خطأ: " . $conn->error;
        }

        header("Location: offers.php");
        exit();
    }

    // تحديث عرض
    if (isset($_POST['update_offer'])) {

        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $type = $conn->real_escape_string($_POST['type']);
        $product_id = ($type == 'product') ? intval($_POST['product_id']) : "NULL";
        $discount_type = $conn->real_escape_string($_POST['discount_type']);
        $discount_value = $conn->real_escape_string($_POST['discount_value']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        $status = $conn->real_escape_string($_POST['status']);

        $sql = "UPDATE offers SET 
                name='$name',
                description='$description',
                type='$type',
                product_id=$product_id,
                discount_type='$discount_type',
                discount_value='$discount_value',
                start_date='$start_date',
                end_date='$end_date',
                status='$status'
                WHERE id=$id";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "تم تحديث العرض بنجاح!";
        } else {
            $_SESSION['message'] = "خطأ: " . $conn->error;
        }

        header("Location: offers.php");
        exit();
    }

    // حذف عرض
    if (isset($_POST['delete_offer'])) {

        $id = intval($_POST['id']);

        $sql = "DELETE FROM offers WHERE id=$id";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "تم حذف العرض بنجاح!";
        } else {
            $_SESSION['message'] = "خطأ: " . $conn->error;
        }

        header("Location: offers.php");
        exit();
    }
}

// جلب بيانات عرض واحد للتعديل
$edit_offer = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $sql = "SELECT * FROM offers WHERE id=$id";
    $result = $conn->query($sql);
    $edit_offer = $result->fetch_assoc();
}

// جلب جميع العروض
$offers = [];
$sql = "SELECT * FROM offers ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $offers[] = $row;
    }
}

// جلب المنتجات للقائمة
$products = [];
$sql_products = "SELECT id, name FROM products";
$result_products = $conn->query($sql_products);
if ($result_products) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العروض - لوحة التحكم</title>
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
        
        .offer-type-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
               <?php include 'header.php'; ?>
    <div class="container-fluid">
        <div class="row">
                 <?php include 'sidebar.php'; ?>

 
            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tags me-2"></i> إدارة العروض</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#offerModal">
                        <i class="fas fa-plus me-1"></i> إضافة عرض جديد
                    </button>
                </div>

                <!-- رسائل التنبيه -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- جدول العروض -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>قائمة العروض</span>
                        <span class="badge bg-primary"><?php echo count($offers); ?> عرض</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العرض</th>
                                        <th>النوع</th>
                                        <th>الخصم</th>
                                        <th>الفترة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($offers) > 0): ?>
                                        <?php foreach ($offers as $index => $offer): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($offer['name']); ?></div>
                                                    <small class="text-muted"><?php echo substr(htmlspecialchars($offer['description']), 0, 50); ?>...</small>
                                                </td>
                                                <td>
                                                    <span class="offer-type-badge">
                                                        <?php echo $offer['type'] == 'product' ? 'عرض منتج' : 'عرض عام'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">
                                                        <?php 
                                                        if ($offer['discount_type'] == 'percentage') {
                                                            echo $offer['discount_value'] . '%';
                                                        } else {
                                                            echo '$' . $offer['discount_value'];
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('Y-m-d', strtotime($offer['start_date'])); ?> <br>
                                                        إلى <?php echo date('Y-m-d', strtotime($offer['end_date'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $offer['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                                        <?php echo $offer['status'] == 'active' ? 'نشط' : 'غير نشط'; ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="offers.php?edit=<?php echo $offer['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا العرض؟');">
                                                        <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                                        <button type="submit" name="delete_offer" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <p>لا توجد عروض مضافة حالياً</p>
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

    <!-- Modal إضافة/تعديل العرض -->
    <div class="modal fade" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="offerModalLabel">
                        <?php echo $edit_offer ? 'تعديل العرض' : 'إضافة عرض جديد'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if ($edit_offer): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_offer['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">اسم العرض</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $edit_offer ? htmlspecialchars($edit_offer['name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">نوع العرض</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="general" <?php echo ($edit_offer && $edit_offer['type'] == 'general') ? 'selected' : ''; ?>>عرض عام</option>
                                    <option value="product" <?php echo ($edit_offer && $edit_offer['type'] == 'product') ? 'selected' : ''; ?>>عرض منتج</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف العرض</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_offer ? htmlspecialchars($edit_offer['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3" id="productField" style="<?php echo ($edit_offer && $edit_offer['type'] == 'product') ? '' : 'display: none;'; ?>">
                                <label for="product_id" class="form-label">المنتج</label>
                                <select class="form-select" id="product_id" name="product_id">
                                    <option value="">اختر المنتج</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" 
                                                <?php echo ($edit_offer && $edit_offer['product_id'] == $product['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="discount_type" class="form-label">نوع الخصم</label>
                                <select class="form-select" id="discount_type" name="discount_type" required>
                                    <option value="percentage" <?php echo ($edit_offer && $edit_offer['discount_type'] == 'percentage') ? 'selected' : ''; ?>>نسبة مئوية</option>
                                    <option value="fixed" <?php echo ($edit_offer && $edit_offer['discount_type'] == 'fixed') ? 'selected' : ''; ?>>مبلغ ثابت</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="discount_value" class="form-label">قيمة الخصم</label>
                                <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                       step="0.01" min="0" value="<?php echo $edit_offer ? $edit_offer['discount_value'] : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">الحالة</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($edit_offer && $edit_offer['status'] == 'active') ? 'selected' : ''; ?>>نشط</option>
                                    <option value="inactive" <?php echo ($edit_offer && $edit_offer['status'] == 'inactive') ? 'selected' : ''; ?>>غير نشط</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">تاريخ البدء</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo $edit_offer ? $edit_offer['start_date'] : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">تاريخ الانتهاء</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo $edit_offer ? $edit_offer['end_date'] : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="<?php echo $edit_offer ? 'update_offer' : 'add_offer'; ?>" class="btn btn-primary">
                            <?php echo $edit_offer ? 'تحديث العرض' : 'إضافة العرض'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التحكم في عرض حقل المنتج بناءً على نوع العرض
        document.getElementById('type').addEventListener('change', function() {
            const productField = document.getElementById('productField');
            if (this.value === 'product') {
                productField.style.display = 'block';
                document.getElementById('product_id').setAttribute('required', 'required');
            } else {
                productField.style.display = 'none';
                document.getElementById('product_id').removeAttribute('required');
            }
        });
        
        // فتح المودال تلقائياً عند النقر على تعديل
        <?php if ($edit_offer): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var offerModal = new bootstrap.Modal(document.getElementById('offerModal'));
                offerModal.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>