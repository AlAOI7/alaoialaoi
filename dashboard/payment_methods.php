<?php
session_start();
require_once '../config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// إنشاء مجلد لصور الشعارات إذا لم يكن موجوداً
if (!file_exists('brands_logos')) {
    mkdir('brands_logos', 0777, true);
}

// معالجة عمليات الإضافة والتحديث والحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleFormSubmissions($pdo);
}

// جلب البيانات للعرض والتعديل
$edit_payment_method = getEditPaymentMethod($pdo);
$edit_bank_account = getEditBankAccount($pdo);
$payment_methods = getAllPaymentMethods($pdo);
$bank_accounts = getAllBankAccounts($pdo);
$banks = getAllBanks($pdo); // جلب جميع البنوك

// رموز Font Awesome المقترحة
$font_awesome_icons = getFontAwesomeIcons();

// ========== الدوال المساعدة ==========

/**
 * معالجة جميع طلبات POST
 */
function handleFormSubmissions($pdo) {
    // معالجة طرق الدفع
    if (isset($_POST['add_payment_method'])) {
        addPaymentMethod($pdo);
    }
    
    if (isset($_POST['update_payment_method'])) {
        updatePaymentMethod($pdo);
    }
    
    if (isset($_POST['delete_payment_method'])) {
        deletePaymentMethod($pdo);
    }
    
    // معالجة الحسابات البنكية
    if (isset($_POST['add_bank_account'])) {
        addBankAccount($pdo);
    }
    
    if (isset($_POST['update_bank_account'])) {
        updateBankAccount($pdo);
    }
    
    if (isset($_POST['delete_bank_account'])) {
        deleteBankAccount($pdo);
    }
    
    // معالجة البنوك (إضافة جديدة)
    if (isset($_POST['add_bank'])) {
        addBank($pdo);
    }
}

/**
 * إضافة طريقة دفع جديدة
 */
function addPaymentMethod($pdo) {
    $data = preparePaymentData();
    
    $sql = "INSERT INTO payment_methods (name, description, type, credentials, is_active, sort_order, icon, additional_info) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($data)) {
        setMessageAndRedirect("تم إضافة طريقة الدفع بنجاح!");
    }
}

/**
 * تحديث طريقة دفع
 */
function updatePaymentMethod($pdo) {
    $data = preparePaymentData();
    $data[] = $_POST['id'];
    
    $sql = "UPDATE payment_methods SET name=?, description=?, type=?, credentials=?, is_active=?, sort_order=?, icon=?, additional_info=? 
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($data)) {
        setMessageAndRedirect("تم تحديث طريقة الدفع بنجاح!");
    }
}

/**
 * حذف طريقة دفع
 */
function deletePaymentMethod($pdo) {
    $sql = "DELETE FROM payment_methods WHERE id=?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$_POST['id']])) {
        setMessageAndRedirect("تم حذف طريقة الدفع بنجاح!");
    }
}

/**
 * إضافة حساب بنكي جديد
 */
function addBankAccount($pdo) {
    $data = prepareBankAccountData();
    
    $sql = "INSERT INTO bank_accounts (bank_id, account_number, account_holder, currency, is_active) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($data)) {
        setMessageAndRedirect("تم إضافة الحساب البنكي بنجاح!");
    }
}

/**
 * تحديث حساب بنكي
 */
function updateBankAccount($pdo) {
    $data = prepareBankAccountData();
    $data[] = $_POST['id'];
    
    $sql = "UPDATE bank_accounts SET bank_id=?, account_number=?, account_holder=?, currency=?, is_active=? 
            WHERE id=?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($data)) {
        setMessageAndRedirect("تم تحديث الحساب البنكي بنجاح!");
    }
}

/**
 * حذف حساب بنكي
 */
function deleteBankAccount($pdo) {
    $sql = "DELETE FROM bank_accounts WHERE id=?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$_POST['id']])) {
        setMessageAndRedirect("تم حذف الحساب البنكي بنجاح!");
    }
}

/**
 * إضافة بنك جديد
 */
function addBank($pdo) {
    $bank_name = trim($_POST['bank_name']);
    
    if (!empty($bank_name)) {
        $sql = "INSERT INTO banks (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$bank_name])) {
            setMessageAndRedirect("تم إضافة البنك بنجاح!");
        }
    }
}

/**
 * تحضير بيانات طريقة الدفع
 */
function preparePaymentData() {
    return [
        $_POST['name'] ?? '',
        $_POST['description'] ?? '',
        $_POST['type'] ?? '',
        $_POST['credentials'] ?? '',
        isset($_POST['is_active']) ? 1 : 0,
        $_POST['sort_order'] ?? 0,
        $_POST['icon'] ?? '',
        $_POST['additional_info'] ?? ''
    ];
}

/**
 * تحضير بيانات الحساب البنكي (باستخدام الجدولين)
 */
function prepareBankAccountData() {
    return [
        $_POST['bank_id'] ?? '',
        $_POST['account_number'] ?? '',
        $_POST['account_holder'] ?? '',
        $_POST['currency'] ?? 'SAR',
        isset($_POST['is_active']) ? 1 : 0
    ];
}

/**
 * تعيين رسالة وإعادة التوجيه
 */
function setMessageAndRedirect($message) {
    $_SESSION['message'] = $message;
    header("Location: payment_methods.php");
    exit();
}

/**
 * جلب بيانات طريقة الدفع للتعديل
 */
function getEditPaymentMethod($pdo) {
    if (isset($_GET['edit_payment'])) {
        $sql = "SELECT * FROM payment_methods WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['edit_payment']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

/**
 * جلب بيانات الحساب البنكي للتعديل
 */
function getEditBankAccount($pdo) {
    if (isset($_GET['edit_bank'])) {
        $sql = "SELECT ba.*, b.name as bank_name 
                FROM bank_accounts ba 
                JOIN banks b ON ba.bank_id = b.id 
                WHERE ba.id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['edit_bank']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

/**
 * جلب جميع طرق الدفع
 */
function getAllPaymentMethods($pdo) {
    $sql = "SELECT * FROM payment_methods ORDER BY sort_order, name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * جلب جميع الحسابات البنكية مع معلومات البنك
 */
function getAllBankAccounts($pdo) {
    $sql = "SELECT ba.*, b.name as bank_name 
            FROM bank_accounts ba 
            JOIN banks b ON ba.bank_id = b.id 
            ORDER BY b.name, ba.account_holder";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * جلب جميع البنوك
 */
function getAllBanks($pdo) {
    $sql = "SELECT * FROM banks ORDER BY name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * الحصول على رموز Font Awesome
 */
function getFontAwesomeIcons() {
    return [
        'fas fa-university' => 'بنك',
        'fas fa-credit-card' => 'بطاقة ائتمان',
        'fas fa-money-bill-wave' => 'نقدي',
        'fab fa-paypal' => 'PayPal',
        'fab fa-apple-pay' => 'Apple Pay',
        'fab fa-google-pay' => 'Google Pay',
        'fas fa-mobile-alt' => 'جوال',
        'fas fa-wallet' => 'محفظة',
        'fas fa-hand-holding-usd' => 'يد تحمل مال',
        'fas fa-coins' => 'عملات',
        'fas fa-receipt' => 'إيصال',
        'fas fa-shield-alt' => 'درع أمان'
    ];
}
?>
    <?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة طرق الدفع - لوحة التحكم</title>
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
        
        .payment-type-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .payment-icon {
            font-size: 1.5rem;
            margin-left: 10px;
            color: var(--primary-color);
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: white;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .tab-content {
            background-color: white;
            border-radius: 0 0 12px 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 15px;
            }
        }
        
        .icon-preview {
            font-size: 1.5rem;
            margin-left: 10px;
            color: var(--primary-color);
        }
        
        .bank-account-card {
            border-left: 4px solid var(--primary-color);
        }
    </style>
</head>
<body>
         <?php include 'sidebar.php'; ?>
    <div class="container-fluid">
        
        <div class="row">
            <!-- الشريط الجانبي -->
       

            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-credit-card me-2"></i> إدارة طرق الدفع</h2>
                </div>

                <!-- رسائل التنبيه -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- تبويبات الصفحة -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="paymentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="methods-tab" data-bs-toggle="tab" data-bs-target="#methods" type="button" role="tab">
                                    طرق الدفع
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bank-accounts-tab" data-bs-toggle="tab" data-bs-target="#bank-accounts" type="button" role="tab">
                                    الحسابات البنكية
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="paymentTabsContent">
                            <!-- تبويب طرق الدفع -->
                            <div class="tab-pane fade show active" id="methods" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="mb-0">قائمة طرق الدفع</h4>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentMethodModal">
                                        <i class="fas fa-plus me-1"></i> إضافة طريقة دفع جديدة
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>طريقة الدفع</th>
                                                <th>النوع</th>
                                                <th>الحالة</th>
                                                <th>ترتيب العرض</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($payment_methods) > 0): ?>
                                                <?php foreach ($payment_methods as $index => $method): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if (!empty($method['icon'])): ?>
                                                                    <i class="<?php echo $method['icon']; ?> payment-icon"></i>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <div class="fw-bold"><?php echo htmlspecialchars($method['name']); ?></div>
                                                                    <small class="text-muted"><?php echo substr(htmlspecialchars($method['description']), 0, 50); ?>...</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="payment-type-badge">
                                                                <?php 
                                                                $type_labels = [
                                                                    'bank' => 'بنك',
                                                                    'card' => 'بطاقة ائتمان',
                                                                    'digital' => 'رقمي',
                                                                    'cash' => 'نقدي'
                                                                ];
                                                                echo $type_labels[$method['type']] ?? $method['type'];
                                                                ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $method['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                                <?php echo $method['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo $method['sort_order']; ?>
                                                        </td>
                                                        <td class="action-buttons">
                                                            <a href="payment_methods.php?edit_payment=<?php echo $method['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف طريقة الدفع هذه؟');">
                                                                <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                                                                <button type="submit" name="delete_payment_method" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-credit-card fa-2x mb-3"></i>
                                                        <p>لا توجد طرق دفع مضافة حالياً</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- تبويب الحسابات البنكية -->
                            <div class="tab-pane fade" id="bank-accounts" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="mb-0">الحسابات البنكية</h4>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bankAccountModal">
                                        <i class="fas fa-plus me-1"></i> إضافة حساب بنكي
                                    </button>
                                </div>

                                <div class="row">
                                    <?php if (count($bank_accounts) > 0): ?>
                                        <?php foreach ($bank_accounts as $account): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="card bank-account-card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <h5 class="card-title"><?php echo htmlspecialchars($account['bank_name']); ?></h5>
                                                            <span class="badge <?php echo $account['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                                <?php echo $account['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                                            </span>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>اسم الحساب:</strong> <?php echo htmlspecialchars($account['account_name']); ?>
                                                            
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>رقم الحساب:</strong> <?php echo htmlspecialchars($account['account_number']); ?>
                                                        </div>
                                                        <?php if (!empty($account['iban'])): ?>
                                                            <div class="mb-2">
                                                                <strong>IBAN:</strong> <?php echo htmlspecialchars($account['iban']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($account['branch_name'])): ?>
                                                            <div class="mb-2">
                                                                <strong>الفرع:</strong> <?php echo htmlspecialchars($account['branch_name']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="mb-3">
                                                            <strong>العملة:</strong> <?php echo htmlspecialchars($account['currency']); ?>
                                                        </div>
                                                        <div class="action-buttons">
                                                            <a href="payment_methods.php?edit_bank=<?php echo $account['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit me-1"></i> تعديل
                                                            </a>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب البنكي؟');">
                                                                <input type="hidden" name="id" value="<?php echo $account['id']; ?>">
                                                                <button type="submit" name="delete_bank_account" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash me-1"></i> حذف
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center text-muted py-4">
                                            <i class="fas fa-university fa-2x mb-3"></i>
                                            <p>لا توجد حسابات بنكية مضافة حالياً</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة/تعديل طريقة الدفع -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-labelledby="paymentMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentMethodModalLabel">
                        <?php echo $edit_payment_method ? 'تعديل طريقة الدفع' : 'إضافة طريقة دفع جديدة'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if ($edit_payment_method): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_payment_method['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">اسم طريقة الدفع</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $edit_payment_method ? htmlspecialchars($edit_payment_method['name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">نوع طريقة الدفع</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="bank" <?php echo ($edit_payment_method && $edit_payment_method['type'] == 'bank') ? 'selected' : ''; ?>>بنك</option>
                                    <option value="card" <?php echo ($edit_payment_method && $edit_payment_method['type'] == 'card') ? 'selected' : ''; ?>>بطاقة ائتمان</option>
                                    <option value="digital" <?php echo ($edit_payment_method && $edit_payment_method['type'] == 'digital') ? 'selected' : ''; ?>>دفع رقمي</option>
                                    <option value="cash" <?php echo ($edit_payment_method && $edit_payment_method['type'] == 'cash') ? 'selected' : ''; ?>>نقدي</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف طريقة الدفع</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_payment_method ? htmlspecialchars($edit_payment_method['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="icon" class="form-label">أيقونة طريقة الدفع</label>
                                <select class="form-select" id="icon" name="icon">
                                    <option value="">اختر الأيقونة</option>
                                    <?php foreach ($font_awesome_icons as $icon_class => $icon_name): ?>
                                        <option value="<?php echo $icon_class; ?>" 
                                                <?php echo ($edit_payment_method && $edit_payment_method['icon'] == $icon_class) ? 'selected' : ''; ?>>
                                            <?php echo $icon_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($edit_payment_method && !empty($edit_payment_method['icon'])): ?>
                                    <div class="mt-2">
                                        <span>معاينة الأيقونة:</span>
                                        <i class="<?php echo $edit_payment_method['icon']; ?> icon-preview"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">ترتيب العرض</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo $edit_payment_method ? $edit_payment_method['sort_order'] : '0'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="credentials" class="form-label">بيانات إضافية (API Keys، إلخ)</label>
                            <textarea class="form-control" id="credentials" name="credentials" rows="3"><?php echo $edit_payment_method ? htmlspecialchars($edit_payment_method['credentials']) : ''; ?></textarea>
                            <div class="form-text">استخدم هذا الحقل لتخزين معلومات إضافية مثل مفاتيح API أو إعدادات خاصة بطريقة الدفع.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="additional_info" class="form-label">معلومات إضافية للعملاء</label>
                            <textarea class="form-control" id="additional_info" name="additional_info" rows="3"><?php echo $edit_payment_method ? htmlspecialchars($edit_payment_method['additional_info']) : ''; ?></textarea>
                            <div class="form-text">هذه المعلومات ستظهر للعملاء عند اختيار طريقة الدفع هذه.</div>
                        </div>
                        
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo ($edit_payment_method && $edit_payment_method['is_active']) || !$edit_payment_method ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">طريقة الدفع نشطة</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="<?php echo $edit_payment_method ? 'update_payment_method' : 'add_payment_method'; ?>" class="btn btn-primary">
                            <?php echo $edit_payment_method ? 'تحديث طريقة الدفع' : 'إضافة طريقة الدفع'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal إضافة/تعديل الحساب البنكي -->
    <div class="modal fade" id="bankAccountModal" tabindex="-1" aria-labelledby="bankAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bankAccountModalLabel">
                        <?php echo $edit_bank_account ? 'تعديل الحساب البنكي' : 'إضافة حساب بنكي جديد'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if ($edit_bank_account): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_bank_account['id']; ?>">
                        <?php endif; ?>
                        
                        <!-- <div class="mb-3">
                            <label for="bank_name" class="form-label">اسم البنك</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                   value="<?php echo $edit_bank_account ? htmlspecialchars($edit_bank_account['bank_name']) : ''; ?>" required>
                        </div> -->
                        <select name="bank_id" required>
                    <option value="">اختر البنك</option>
                    <?php foreach ($banks as $bank): ?>
                        <option value="<?= $bank['id'] ?>" <?= isset($edit_bank_account) && $edit_bank_account['bank_id'] == $bank['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bank['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                        <div class="mb-3">
                            <label for="account_name" class="form-label">اسم صاحب الحساب</label>
                            <!-- <input type="text" class="form-control" id="account_name" name="account_name" 
                                   value="<?php echo $bank ? htmlspecialchars($edit_bank_account['account_name']) : ''; ?>" required> -->
                       <input type="text" name="account_holder" placeholder="اسم صاحب الحساب" value="<?= isset($edit_bank_account) ? $edit_bank_account['account_holder'] : '' ?>" required>

                                </div>
                        
                        <div class="mb-3">
                            <label for="account_number" class="form-label">رقم الحساب</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" 
                                   value="<?php echo $edit_bank_account ? htmlspecialchars($edit_bank_account['account_number']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="iban" class="form-label">رقم IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban" 
                                   value="<?php echo $edit_bank_account ? htmlspecialchars($edit_bank_account['iban']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="branch_name" class="form-label">اسم الفرع</label>
                                <input type="text" class="form-control" id="branch_name" name="branch_name" 
                                       value="<?php echo $edit_bank_account ? htmlspecialchars($edit_bank_account['branch_name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">العملة</label>
                                <select class="form-select" id="currency" name="currency" required>
                                    <option value="SAR" <?php echo ($edit_bank_account && $edit_bank_account['currency'] == 'SAR') ? 'selected' : ''; ?>>ريال سعودي (SAR)</option>
                                    <option value="USD" <?php echo ($edit_bank_account && $edit_bank_account['currency'] == 'USD') ? 'selected' : ''; ?>>دولار أمريكي (USD)</option>
                                    <option value="EUR" <?php echo ($edit_bank_account && $edit_bank_account['currency'] == 'EUR') ? 'selected' : ''; ?>>يورو (EUR)</option>
                                    <option value="AED" <?php echo ($edit_bank_account && $edit_bank_account['currency'] == 'AED') ? 'selected' : ''; ?>>درهم إماراتي (AED)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo ($edit_bank_account && $edit_bank_account['is_active']) || !$edit_bank_account ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">الحساب البنكي نشط</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="<?php echo $edit_bank_account ? 'update_bank_account' : 'add_bank_account'; ?>" class="btn btn-primary">
                            <?php echo $edit_bank_account ? 'تحديث الحساب البنكي' : 'إضافة الحساب البنكي'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // فتح المودال تلقائياً عند النقر على تعديل
        <?php if ($edit_payment_method): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var paymentMethodModal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
                paymentMethodModal.show();
            });
        <?php endif; ?>
        
        <?php if ($edit_bank_account): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var bankAccountModal = new bootstrap.Modal(document.getElementById('bankAccountModal'));
                bankAccountModal.show();
                
                // التبديل إلى تبويب الحسابات البنكية
                var bankTab = new bootstrap.Tab(document.getElementById('bank-accounts-tab'));
                bankTab.show();
            });
        <?php endif; ?>
        
        // تحديث معاينة الأيقونة
        document.getElementById('icon').addEventListener('change', function() {
            const iconPreview = document.querySelector('.icon-preview');
            if (iconPreview) {
                iconPreview.className = this.value + ' icon-preview';
            }
        });
    </script>
</body>
</html>
