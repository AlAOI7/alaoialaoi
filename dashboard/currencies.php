<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// إنشاء مجلد لصور الأعلام إذا لم يكن موجوداً
if (!file_exists('currency_flags')) {
    mkdir('currency_flags', 0777, true);
}

// معالجة العمليات
$message = "";
$message_type = ""; // success, error
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_currency'])) {
        // إضافة عملة جديدة
        $name = $conn->real_escape_string($_POST['name']);
        $code = strtoupper($conn->real_escape_string($_POST['code']));
        $symbol = $conn->real_escape_string($_POST['symbol']);
        $country = $conn->real_escape_string($_POST['country']);
        $exchange_rate = floatval($_POST['exchange_rate']);
        $status = $conn->real_escape_string($_POST['status']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // معالجة رفع العلم
        $flag = NULL;
        if (isset($_FILES['flag']) && $_FILES['flag']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $file_type = $_FILES['flag']['type'];
            $file_ext = strtolower(pathinfo($_FILES['flag']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_type, $allowed_types) || in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['flag']['name']));
                $flag = 'currency_flags/' . $filename;
                
                // إنشاء المجلد إذا لم يكن موجوداً
                if (!is_dir('currency_flags')) {
                    mkdir('currency_flags', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['flag']['tmp_name'], $flag)) {
                    // تم رفع الصورة بنجاح
                } else {
                    $flag = NULL;
                }
            }
        }
        
        // حساب تغيير السعر (نسبة مئوية عشوائية لأغراض العرض)
        $change_rate = round((rand(-500, 500) / 1000), 4);
        
        // التحقق من عدم وجود العملة مسبقاً
        $check_sql = "SELECT id FROM currencies WHERE code = '$code'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $message = "هذه العملة موجودة بالفعل!";
            $message_type = "error";
        } else {
            $sql = "INSERT INTO currencies (name, code, symbol, country, flag, exchange_rate, change_rate, status, description) 
                    VALUES ('$name', '$code', '$symbol', '$country', '$flag', $exchange_rate, $change_rate, '$status', '$description')";
            
            if ($conn->query($sql)) {
                $message = "تم إضافة العملة بنجاح!";
                $message_type = "success";
                // إعادة التوجيه لتجنب إعادة الإرسال
                header("Location: currencies.php?message=" . urlencode($message) . "&type=" . $message_type);
                exit();
            } else {
                $message = "خطأ في الإضافة: " . $conn->error;
                $message_type = "error";
            }
        }
    } 
    elseif (isset($_POST['update_currency'])) {
        // تعديل عملة
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $code = strtoupper($conn->real_escape_string($_POST['code']));
        $symbol = $conn->real_escape_string($_POST['symbol']);
        $country = $conn->real_escape_string($_POST['country']);
        $exchange_rate = floatval($_POST['exchange_rate']);
        $status = $conn->real_escape_string($_POST['status']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // معالجة رفع العلم الجديد
        $flag_sql = "";
        if (isset($_FILES['flag']) && $_FILES['flag']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $file_type = $_FILES['flag']['type'];
            $file_ext = strtolower(pathinfo($_FILES['flag']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_type, $allowed_types) || in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                // حذف العلم القديم إذا كان موجوداً
                $old_flag = $conn->query("SELECT flag FROM currencies WHERE id=$id")->fetch_assoc()['flag'];
                if ($old_flag && file_exists($old_flag) && $old_flag != '') {
                    unlink($old_flag);
                }
                
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['flag']['name']));
                $flag = 'currency_flags/' . $filename;
                
                if (move_uploaded_file($_FILES['flag']['tmp_name'], $flag)) {
                    $flag_sql = ", flag='$flag'";
                }
            }
        }
        
        $sql = "UPDATE currencies SET 
                name='$name', 
                code='$code', 
                symbol='$symbol', 
                country='$country', 
                exchange_rate=$exchange_rate, 
                status='$status', 
                description='$description' 
                $flag_sql 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            $message = "تم تعديل العملة بنجاح!";
            $message_type = "success";
            header("Location: currencies.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في التعديل: " . $conn->error;
            $message_type = "error";
        }
    } 
    elseif (isset($_POST['delete_currency'])) {
        // حذف عملة
        $id = (int)$_POST['id'];
        
        // حذف العلم إذا كان موجوداً
        $result = $conn->query("SELECT flag FROM currencies WHERE id=$id");
        if ($result && $result->num_rows > 0) {
            $flag = $result->fetch_assoc()['flag'];
            if ($flag && file_exists($flag) && $flag != '') {
                unlink($flag);
            }
        }
        
        $sql = "DELETE FROM currencies WHERE id=$id";
        if ($conn->query($sql)) {
            $message = "تم حذف العملة بنجاح!";
            $message_type = "success";
            header("Location: currencies.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        } else {
            $message = "خطأ في الحذف: " . $conn->error;
            $message_type = "error";
        }
    }
}

// عرض الرسالة من URL إذا وجدت
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message'];
    $message_type = $_GET['type'];
}

// جلب بيانات العملات
$where_clause = "";
if ($search) {
    $where_clause = " WHERE name LIKE '%$search%' OR code LIKE '%$search%' OR country LIKE '%$search%'";
}

$currencies_result = $conn->query("SELECT * FROM currencies $where_clause ORDER BY created_at DESC");

// بيانات البلدان
$countries = [
    'us' => 'الولايات المتحدة',
    'eu' => 'الاتحاد الأوروبي',
    'sa' => 'المملكة العربية السعودية',
    'ae' => 'الإمارات العربية المتحدة',
    'gb' => 'المملكة المتحدة',
    'jp' => 'اليابان',
    'cn' => 'الصين',
    'eg' => 'مصر',
    'kw' => 'الكويت',
    'qa' => 'قطر',
    'om' => 'عمان',
    'bh' => 'البحرين',
    'jo' => 'الأردن',
    'lb' => 'لبنان',
    'ma' => 'المغرب',
    'dz' => 'الجزائر',
    'tn' => 'تونس',
    'sd' => 'السودان',
    'de' => 'ألمانيا',
    'fr' => 'فرنسا',
    'it' => 'إيطاليا',
    'es' => 'إسبانيا',
    'ru' => 'روسيا',
    'ca' => 'كندا',
    'au' => 'أستراليا',
    'in' => 'الهند',
    'kr' => 'كوريا الجنوبية'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة العملات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            direction: rtl;
        }
        
        /* تنسيق لوحة التحكم */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f5f7fa;
        }
        
        .page-content {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .page-title h2 {
            color: var(--secondary);
            font-size: 24px;
            font-weight: 600;
        }
        
        .page-title .date {
            color: #666;
            font-size: 14px;
        }
        
        /* رسالة التأكيد */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        /* تنسيق الجدول */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-header h2 {
            color: var(--secondary);
            font-size: 20px;
            margin: 0;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #219653;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }
        
        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: right;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #e0e0e0;
        }
        
        .table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .currency-flag {
            width: 40px;
            height: 25px;
            border-radius: 3px;
            object-fit: cover;
            border: 1px solid #e0e0e0;
        }
        
        .change-positive {
            color: #28a745;
            font-weight: bold;
        }
        
        .change-negative {
            color: #dc3545;
            font-weight: bold;
        }
        
        .change-neutral {
            color: #6c757d;
        }
        
        .status-active {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-inactive {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .action-btn.view {
            background: var(--success);
            color: white;
        }
        
        .action-btn.edit {
            background: var(--primary);
            color: white;
        }
        
        .action-btn.delete {
            background: var(--danger);
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
            transform: scale(1.1);
        }
        
        #no-currencies-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        #no-currencies-message a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        #no-currencies-message a:hover {
            text-decoration: underline;
        }
        
        /* ===================== */
        /* تنسيق النوافذ المنبثقة */
        /* ===================== */
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--secondary);
            font-size: 1.3rem;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #777;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .close-modal:hover {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            position: sticky;
            bottom: 0;
            background: white;
            border-radius: 0 0 10px 10px;
        }
        
        /* تنسيق تحميل العلم */
        .flag-upload {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            flex-wrap: wrap;
        }
        
        .flag-preview {
            width: 100px;
            height: 60px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: white;
            position: relative;
        }
        
        .flag-preview img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }
        
        .upload-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .upload-btn:hover {
            background-color: #2980b9;
        }
        
        .upload-btn i {
            margin-left: 5px;
        }
        
        /* تنسيق النماذج */
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        /* تنسيق عرض بيانات العملة */
        .currency-details {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .currency-flag-large {
            width: 120px;
            height: 80px;
            border-radius: 8px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .currency-flag-large img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .currency-info {
            flex: 1;
            min-width: 250px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            width: 120px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        .info-value {
            flex: 1;
            color: #333;
            font-size: 14px;
        }
        
        /* نافذة تأكيد الحذف */
        #delete-currency-modal .modal-body {
            text-align: center;
        }
        
        #delete-currency-name {
            font-weight: bold;
            color: var(--danger);
        }
        
        /* التجاوب مع الشاشات الصغيرة */
        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-control {
                width: 100%;
            }
            
            .modal {
                padding: 10px;
            }
            
            .modal-content {
                max-height: 95vh;
            }
            
            .currency-details {
                flex-direction: column;
            }
            
            .currency-flag-large {
                width: 100%;
                height: 100px;
            }
            
            .flag-upload {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .modal-footer .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <?php include 'header.php'; ?>

            <div class="page-content">
                <div class="page-title">
                    <h2>نظام إدارة العملات</h2>
                    <div class="date"><?php echo date('l، j F Y'); ?></div>
                </div>

                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="table-container">
                    <div class="table-header">
                        <h2>قائمة العملات</h2>
                        <div class="search-box">
                            <form method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <input type="text" class="form-control" name="search" placeholder="بحث في العملات..." 
                                       value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 200px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                                <?php if ($search): ?>
                                    <a href="?" class="btn btn-danger">إلغاء البحث</a>
                                <?php endif; ?>
                            </form>
                            <button class="btn btn-success" id="add-currency-btn">
                                <i class="fas fa-plus"></i> إضافة عملة
                            </button>
                            <button class="btn btn-primary" id="refresh-btn">
                                <i class="fas fa-sync-alt"></i> تحديث
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="currencies-table">
                            <thead>
                                <tr>
                                    <th>العلم</th>
                                    <th>اسم العملة</th>
                                    <th>الرمز</th>
                                    <th>سعر الصرف</th>
                                    <th>التغيير</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="currencies-table-body">
                                <?php 
                                if ($currencies_result && $currencies_result->num_rows > 0): 
                                    while($currency = $currencies_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td>
                                            <?php if ($currency['flag'] && file_exists($currency['flag'])): ?>
                                                <img src="<?= $currency['flag'] ?>" alt="علم العملة" class="currency-flag">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 25px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 0.7em;">
                                                    <i class="fas fa-flag"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($currency['name']) ?></td>
                                        <td><strong><?= htmlspecialchars($currency['code']) ?></strong></td>
                                        <td><?= number_format($currency['exchange_rate'], 4) ?></td>
                                        <td>
                                            <?php 
                                            $change_class = 'change-neutral';
                                            if ($currency['change_rate'] > 0) {
                                                $change_class = 'change-positive';
                                            } elseif ($currency['change_rate'] < 0) {
                                                $change_class = 'change-negative';
                                            }
                                            ?>
                                            <span class="<?= $change_class ?>">
                                                <?= $currency['change_rate'] > 0 ? '+' : '' ?><?= number_format($currency['change_rate'], 4) ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?= $currency['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                                <?= $currency['status'] == 'active' ? 'نشط' : 'غير نشط' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" 
                                                        data-id="<?= $currency['id'] ?>"
                                                        data-name="<?= htmlspecialchars($currency['name']) ?>"
                                                        data-code="<?= htmlspecialchars($currency['code']) ?>"
                                                        data-symbol="<?= htmlspecialchars($currency['symbol']) ?>"
                                                        data-country="<?= htmlspecialchars($currency['country']) ?>"
                                                        data-rate="<?= $currency['exchange_rate'] ?>"
                                                        data-change="<?= $currency['change_rate'] ?>"
                                                        data-status="<?= $currency['status'] ?>"
                                                        data-description="<?= htmlspecialchars($currency['description']) ?>"
                                                        data-flag="<?= $currency['flag'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit" 
                                                        data-id="<?= $currency['id'] ?>"
                                                        data-name="<?= htmlspecialchars($currency['name']) ?>"
                                                        data-code="<?= htmlspecialchars($currency['code']) ?>"
                                                        data-symbol="<?= htmlspecialchars($currency['symbol']) ?>"
                                                        data-country="<?= htmlspecialchars($currency['country']) ?>"
                                                        data-rate="<?= $currency['exchange_rate'] ?>"
                                                        data-status="<?= $currency['status'] ?>"
                                                        data-description="<?= htmlspecialchars($currency['description']) ?>"
                                                        data-flag="<?= $currency['flag'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" 
                                                        data-id="<?= $currency['id'] ?>"
                                                        data-name="<?= htmlspecialchars($currency['name']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px; color: #6c757d;">
                                            <?= $search ? 'لم يتم العثور على عملات تطابق بحثك' : 'لا توجد عملات مسجلة بعد' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($currencies_result && $currencies_result->num_rows == 0 && !$search): ?>
                        <div id="no-currencies-message">
                            <p>لا توجد عملات مسجلة بعد. <a href="#" id="add-first-currency">إضافة أول عملة</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== -->
    <!-- النوافذ المنبثقة -->
    <!-- ==================== -->

    <!-- نافذة إضافة عملة -->
    <div class="modal" id="add-currency-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إضافة عملة جديدة</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="flag-upload">
                    <div class="flag-preview">
                        <img src="" alt="علم العملة" id="currency-flag-preview">
                        <span id="no-flag">لا يوجد علم</span>
                    </div>
                    <div>
                        <input type="file" id="currency-flag-upload" name="flag" accept="image/*" style="display: none;">
                        <label for="currency-flag-upload" class="upload-btn">
                            <i class="fas fa-upload"></i> تحميل العلم
                        </label>
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">الحجم الموصى به: 100x60 بكسل</p>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data" id="add-currency-form">
                    <input type="hidden" name="add_currency" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currency-name">اسم العملة *</label>
                            <input type="text" id="currency-name" name="name" class="form-control" placeholder="أدخل اسم العملة" required>
                        </div>
                        <div class="form-group">
                            <label for="currency-code">رمز العملة *</label>
                            <input type="text" id="currency-code" name="code" class="form-control" placeholder="مثل: USD, EUR" maxlength="3" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currency-symbol">رمز العملة</label>
                            <input type="text" id="currency-symbol" name="symbol" class="form-control" placeholder="مثل: $, €">
                        </div>
                        <div class="form-group">
                            <label for="currency-country">البلد *</label>
                            <select id="currency-country" name="country" class="form-control" required>
                                <option value="">اختر البلد</option>
                                <?php foreach($countries as $code => $name): ?>
                                    <option value="<?= $code ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currency-rate">سعر الصرف *</label>
                            <input type="number" id="currency-rate" name="exchange_rate" class="form-control" placeholder="مثال: 1.0000" step="0.0001" min="0" required>
                            <small style="color: #666; font-size: 12px;">سعر الصرف مقابل العملة الأساسية</small>
                        </div>
                        <div class="form-group">
                            <label for="currency-status">حالة العملة *</label>
                            <select id="currency-status" name="status" class="form-control" required>
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="currency-description">وصف العملة</label>
                        <textarea id="currency-description" name="description" class="form-control" rows="3" placeholder="أدخل وصفاً للعملة"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger close-modal">إلغاء</button>
                <button class="btn btn-primary" id="submit-add-currency">إضافة العملة</button>
            </div>
        </div>
    </div>

    <!-- نافذة تعديل عملة -->
    <div class="modal" id="edit-currency-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تعديل العملة</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="flag-upload">
                    <div class="flag-preview">
                        <img src="" alt="علم العملة" id="edit-currency-flag-preview">
                        <span id="edit-no-flag">لا يوجد علم</span>
                    </div>
                    <div>
                        <input type="file" id="edit-currency-flag-upload" name="flag" accept="image/*" style="display: none;">
                        <label for="edit-currency-flag-upload" class="upload-btn">
                            <i class="fas fa-upload"></i> تغيير العلم
                        </label>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data" id="edit-currency-form">
                    <input type="hidden" id="edit-currency-id" name="id">
                    <input type="hidden" name="update_currency" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-currency-name">اسم العملة *</label>
                            <input type="text" id="edit-currency-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-currency-code">رمز العملة *</label>
                            <input type="text" id="edit-currency-code" name="code" class="form-control" maxlength="3" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-currency-symbol">رمز العملة</label>
                            <input type="text" id="edit-currency-symbol" name="symbol" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-currency-country">البلد *</label>
                            <select id="edit-currency-country" name="country" class="form-control" required>
                                <?php foreach($countries as $code => $name): ?>
                                    <option value="<?= $code ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-currency-rate">سعر الصرف *</label>
                            <input type="number" id="edit-currency-rate" name="exchange_rate" class="form-control" step="0.0001" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-currency-status">حالة العملة *</label>
                            <select id="edit-currency-status" name="status" class="form-control" required>
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-currency-description">وصف العملة</label>
                        <textarea id="edit-currency-description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger close-modal">إلغاء</button>
                <button class="btn btn-primary" id="submit-edit-currency">حفظ التعديلات</button>
            </div>
        </div>
    </div>

    <!-- نافذة عرض بيانات العملة -->
    <div class="modal" id="view-currency-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>بيانات العملة</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="currency-details">
                    <div class="currency-flag-large" id="view-currency-flag">
                        <span>لا يوجد علم</span>
                    </div>
                    <div class="currency-info">
                        <div class="info-row">
                            <div class="info-label">اسم العملة:</div>
                            <div class="info-value" id="view-currency-name">-</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">رمز العملة:</div>
                            <div class="info-value" id="view-currency-code">-</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">رمز العملة:</div>
                            <div class="info-value" id="view-currency-symbol">-</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">البلد:</div>
                            <div class="info-value" id="view-currency-country">-</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">سعر الصرف:</div>
                            <div class="info-value" id="view-currency-rate">-</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">التغيير:</div>
                            <div class="info-value" id="view-currency-change">-</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">الحالة:</div>
                            <div class="info-value" id="view-currency-status">-</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>وصف العملة:</label>
                    <div class="form-control" style="background: #f9f9f9; min-height: 80px;" id="view-currency-description">
                        لا يوجد وصف
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary close-modal">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- نافذة تأكيد الحذف -->
    <div class="modal" id="delete-currency-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>حذف العملة</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 15px;"></i>
                    <h4 style="margin-bottom: 10px;">هل أنت متأكد من حذف هذه العملة؟</h4>
                    <p style="color: #666;">سيتم حذف العملة "<span id="delete-currency-name"></span>" بشكل دائم ولا يمكن التراجع عن هذا الإجراء.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger close-modal">إلغاء</button>
                <button class="btn btn-primary" id="confirm-delete-currency">تأكيد الحذف</button>
            </div>
        </div>
    </div>

    <script>
        // ======================
        // دالات إدارة النوافذ
        // ======================
        
        // دالة لفتح نافذة منبثقة
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // منع التمرير في الخلفية
            }
        }
        
        // دالة لإغلاق نافذة منبثقة
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // إعادة التمرير
            }
        }
        
        // ======================
        // معالجة النقر على الأزرار
        // ======================
        
        // إضافة event listeners عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            
            // ======================
            // فتح النوافذ المنبثقة
            // ======================
            
            // فتح نافذة إضافة عملة
            document.getElementById('add-currency-btn').addEventListener('click', function() {
                // إعادة تعيين النموذج
                document.getElementById('add-currency-form').reset();
                document.getElementById('no-flag').style.display = 'block';
                document.getElementById('currency-flag-preview').style.display = 'none';
                document.getElementById('currency-flag-preview').src = '';
                
                openModal('add-currency-modal');
            });
            
            // فتح نافذة إضافة العملة الأولى
            const addFirstBtn = document.getElementById('add-first-currency');
            if (addFirstBtn) {
                addFirstBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('add-currency-form').reset();
                    document.getElementById('no-flag').style.display = 'block';
                    document.getElementById('currency-flag-preview').style.display = 'none';
                    document.getElementById('currency-flag-preview').src = '';
                    
                    openModal('add-currency-modal');
                });
            }
            
            // فتح نافذة عرض العملة
            document.querySelectorAll('.action-btn.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const symbol = this.getAttribute('data-symbol');
                    const country = this.getAttribute('data-country');
                    const rate = this.getAttribute('data-rate');
                    const change = this.getAttribute('data-change');
                    const status = this.getAttribute('data-status');
                    const description = this.getAttribute('data-description');
                    const flag = this.getAttribute('data-flag');
                    
                    // تعبئة البيانات في النافذة
                    document.getElementById('view-currency-name').textContent = name;
                    document.getElementById('view-currency-code').textContent = code;
                    document.getElementById('view-currency-symbol').textContent = symbol || '-';
                    document.getElementById('view-currency-country').textContent = getCountryName(country);
                    document.getElementById('view-currency-rate').textContent = parseFloat(rate).toFixed(4);
                    
                    const changeClass = parseFloat(change) > 0 ? 'change-positive' : (parseFloat(change) < 0 ? 'change-negative' : 'change-neutral');
                    document.getElementById('view-currency-change').innerHTML = `<span class="${changeClass}">${parseFloat(change) > 0 ? '+' : ''}${parseFloat(change).toFixed(4)}%</span>`;
                    
                    document.getElementById('view-currency-status').innerHTML = status === 'active' ? 
                        '<span class="status-active">نشط</span>' : '<span class="status-inactive">غير نشط</span>';
                    
                    document.getElementById('view-currency-description').textContent = description || 'لا يوجد وصف';
                    
                    // عرض العلم إذا كان موجوداً
                    const flagContainer = document.getElementById('view-currency-flag');
                    if (flag && flag !== '') {
                        flagContainer.innerHTML = `<img src="${flag}" alt="علم العملة">`;
                    } else {
                        flagContainer.innerHTML = '<span>لا يوجد علم</span>';
                    }
                    
                    openModal('view-currency-modal');
                });
            });
            
            // فتح نافذة تعديل العملة
            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const symbol = this.getAttribute('data-symbol');
                    const country = this.getAttribute('data-country');
                    const rate = this.getAttribute('data-rate');
                    const status = this.getAttribute('data-status');
                    const description = this.getAttribute('data-description');
                    const flag = this.getAttribute('data-flag');
                    
                    // تعبئة البيانات في النموذج
                    document.getElementById('edit-currency-id').value = id;
                    document.getElementById('edit-currency-name').value = name;
                    document.getElementById('edit-currency-code').value = code;
                    document.getElementById('edit-currency-symbol').value = symbol || '';
                    document.getElementById('edit-currency-country').value = country;
                    document.getElementById('edit-currency-rate').value = rate;
                    document.getElementById('edit-currency-status').value = status;
                    document.getElementById('edit-currency-description').value = description || '';
                    
                    // عرض العلم إذا كان موجوداً
                    const preview = document.getElementById('edit-currency-flag-preview');
                    const noFlag = document.getElementById('edit-no-flag');
                    if (flag && flag !== '') {
                        preview.src = flag;
                        preview.style.display = 'block';
                        noFlag.style.display = 'none';
                    } else {
                        preview.style.display = 'none';
                        noFlag.style.display = 'block';
                    }
                    
                    openModal('edit-currency-modal');
                });
            });
            
            // فتح نافذة تأكيد الحذف
            document.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('delete-currency-name').textContent = name;
                    document.getElementById('confirm-delete-currency').setAttribute('data-id', id);
                    openModal('delete-currency-modal');
                });
            });
            
            // ======================
            // إغلاق النوافذ المنبثقة
            // ======================
            
            // إغلاق النوافذ عند النقر على زر الإغلاق
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        closeModal(modal.id);
                    }
                });
            });
            
            // إغلاق النوافذ عند النقر خارج المحتوى
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this.id);
                    }
                });
            });
            
            // ======================
            // معالجة تحميل الصور
            // ======================
            
            // معاينة العلم عند التحميل (إضافة)
            document.getElementById('currency-flag-upload').addEventListener('change', function(e) {
                handleFlagPreview(e, 'currency-flag-preview', 'no-flag');
            });
            
            // معاينة العلم عند التحميل (تعديل)
            document.getElementById('edit-currency-flag-upload').addEventListener('change', function(e) {
                handleFlagPreview(e, 'edit-currency-flag-preview', 'edit-no-flag');
            });
            
            function handleFlagPreview(event, previewId, noFlagId) {
                const file = event.target.files[0];
                const preview = document.getElementById(previewId);
                const noFlag = document.getElementById(noFlagId);
                
                if (file) {
                    // التحقق من حجم الملف (2MB كحد أقصى)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('حجم الملف يجب أن لا يتجاوز 2 ميجابايت');
                        event.target.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        noFlag.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                }
            }
            
            // ======================
            // إرسال النماذج
            // ======================
            
            // إرسال نموذج إضافة العملة
            document.getElementById('submit-add-currency').addEventListener('click', function() {
                // التحقق من الحقول المطلوبة
                const name = document.getElementById('currency-name').value.trim();
                const code = document.getElementById('currency-code').value.trim();
                const country = document.getElementById('currency-country').value;
                const rate = document.getElementById('currency-rate').value;
                
                if (!name) {
                    alert('يرجى إدخال اسم العملة');
                    document.getElementById('currency-name').focus();
                    return;
                }
                
                if (!code) {
                    alert('يرجى إدخال رمز العملة');
                    document.getElementById('currency-code').focus();
                    return;
                }
                
                if (code.length !== 3) {
                    alert('رمز العملة يجب أن يتكون من 3 أحرف');
                    document.getElementById('currency-code').focus();
                    return;
                }
                
                if (!country) {
                    alert('يرجى اختيار البلد');
                    document.getElementById('currency-country').focus();
                    return;
                }
                
                if (!rate || parseFloat(rate) <= 0) {
                    alert('يرجى إدخال سعر صرف صحيح');
                    document.getElementById('currency-rate').focus();
                    return;
                }
                
                document.getElementById('add-currency-form').submit();
            });
            
            // إرسال نموذج تعديل العملة
            document.getElementById('submit-edit-currency').addEventListener('click', function() {
                // التحقق من الحقول المطلوبة
                const name = document.getElementById('edit-currency-name').value.trim();
                const code = document.getElementById('edit-currency-code').value.trim();
                const country = document.getElementById('edit-currency-country').value;
                const rate = document.getElementById('edit-currency-rate').value;
                
                if (!name) {
                    alert('يرجى إدخال اسم العملة');
                    document.getElementById('edit-currency-name').focus();
                    return;
                }
                
                if (!code) {
                    alert('يرجى إدخال رمز العملة');
                    document.getElementById('edit-currency-code').focus();
                    return;
                }
                
                if (code.length !== 3) {
                    alert('رمز العملة يجب أن يتكون من 3 أحرف');
                    document.getElementById('edit-currency-code').focus();
                    return;
                }
                
                if (!country) {
                    alert('يرجى اختيار البلد');
                    document.getElementById('edit-currency-country').focus();
                    return;
                }
                
                if (!rate || parseFloat(rate) <= 0) {
                    alert('يرجى إدخال سعر صرف صحيح');
                    document.getElementById('edit-currency-rate').focus();
                    return;
                }
                
                document.getElementById('edit-currency-form').submit();
            });
            
            // تأكيد حذف العملة
            document.getElementById('confirm-delete-currency').addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                // إنشاء نموذج وإرساله
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_currency';
                deleteInput.value = '1';
                
                form.appendChild(idInput);
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            });
            
            // ======================
            // أزرار إضافية
            // ======================
            
            // تحديث الصفحة
            document.getElementById('refresh-btn').addEventListener('click', function() {
                location.reload();
            });
            
            // إغلاق النافذة بالضغط على زر ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal').forEach(modal => {
                        if (modal.style.display === 'flex') {
                            closeModal(modal.id);
                        }
                    });
                }
            });
            
        });
        
        // دالة للحصول على اسم البلد
        function getCountryName(code) {
            const countries = {
                'us': 'الولايات المتحدة',
                'eu': 'الاتحاد الأوروبي',
                'sa': 'المملكة العربية السعودية',
                'ae': 'الإمارات العربية المتحدة',
                'gb': 'المملكة المتحدة',
                'jp': 'اليابان',
                'cn': 'الصين',
                'eg': 'مصر',
                'kw': 'الكويت',
                'qa': 'قطر',
                'om': 'عمان',
                'bh': 'البحرين',
                'jo': 'الأردن',
                'lb': 'لبنان',
                'ma': 'المغرب',
                'dz': 'الجزائر',
                'tn': 'تونس',
                'sd': 'السودان',
                'de': 'ألمانيا',
                'fr': 'فرنسا',
                'it': 'إيطاليا',
                'es': 'إسبانيا',
                'ru': 'روسيا',
                'ca': 'كندا',
                'au': 'أستراليا',
                'in': 'الهند',
                'kr': 'كوريا الجنوبية'
            };
            return countries[code] || code;
        }
    </script>
</body>
</html>