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

// إنشاء مجلد للشعارات إذا لم يكن موجوداً
if (!file_exists('banks_logos')) {
    mkdir('banks_logos', 0777, true);
}

// معالجة العمليات
$message = "";
$message_type = ""; // success, error, warning, info

// معالجة إضافة/تعديل البنك
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_bank'])) {
        $bank_id = isset($_POST['bank_id']) ? (int)$_POST['bank_id'] : 0;
        $name = $conn->real_escape_string(trim($_POST['name']));
        $website = $conn->real_escape_string(trim($_POST['website']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $status = $conn->real_escape_string($_POST['status']);

        // التحقق من الحقول المطلوبة
        if (empty($name)) {
            $message = "اسم البنك مطلوب!";
            $message_type = "error";
        } else {
            // معالجة رفع الشعار
            $logo = NULL;
            $upload_success = true;
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                $file_type = $_FILES['logo']['type'];
                $file_size = $_FILES['logo']['size'];
                $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $file_name = time() . '_' . uniqid() . '.' . $file_extension;

                if (in_array($file_type, $allowed_types)) {
                    if ($file_size <= 5 * 1024 * 1024) {
                        $logo = 'banks_logos/' . $file_name;
                        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo)) {
                            $upload_success = false;
                            $message = "فشل في تحميل الصورة!";
                            $message_type = "error";
                        }
                    } else {
                        $upload_success = false;
                        $message = "حجم الصورة كبير جداً! الحد الأقصى 5MB";
                        $message_type = "error";
                    }
                } else {
                    $upload_success = false;
                    $message = "نوع الملف غير مدعوم! يرجى استخدام الصور فقط";
                    $message_type = "error";
                }
            }

            if ($upload_success) {
                if ($bank_id > 0) {
                    // تحديث البنك
                    $logo_sql = $logo ? ", logo='$logo'" : "";
                    $sql = "UPDATE banks SET 
                            name='$name', 
                            website='$website', 
                            description='$description', 
                            status='$status' 
                            $logo_sql 
                            WHERE id=$bank_id";
                    
                    if ($conn->query($sql)) {
                        $message = "تم تحديث البنك بنجاح!";
                        $message_type = "success";
                    } else {
                        $message = "خطأ في التحديث: " . $conn->error;
                        $message_type = "error";
                    }
                } else {
                    // إضافة بنك جديد
                    $logo_value = $logo ? "'$logo'" : "NULL";
                    $sql = "INSERT INTO banks (name, logo, website, description, status) 
                            VALUES ('$name', $logo_value, '$website', '$description', '$status')";
                    
                    if ($conn->query($sql)) {
                        $message = "تم إضافة البنك بنجاح!";
                        $message_type = "success";
                    } else {
                        $message = "خطأ في الإضافة: " . $conn->error;
                        $message_type = "error";
                    }
                }
            }
        }
    } 
    // معالجة حفظ الحسابات
    elseif (isset($_POST['save_accounts'])) {
        $bank_id = (int)$_POST['bank_id'];
        $accounts = $_POST['accounts'];
        
        // حذف الحسابات الحالية وإضافة الجديدة
        $conn->query("DELETE FROM bank_accounts WHERE bank_id = $bank_id");
        
        foreach ($accounts as $account) {
            $account_number = $conn->real_escape_string(trim($account['account_number']));
            $account_holder = $conn->real_escape_string(trim($account['account_holder']));
            $currency = $conn->real_escape_string($account['currency']);
            $iban = $conn->real_escape_string(trim($account['iban']));
            $swift_code = $conn->real_escape_string(trim($account['swift_code']));
            $branch_name = $conn->real_escape_string(trim($account['branch_name']));
            $balance = floatval($account['balance']);
            $status = $conn->real_escape_string($account['status']);
            $is_primary = isset($account['is_primary']) ? 1 : 0;
            $notes = $conn->real_escape_string(trim($account['notes']));
            
            $sql = "INSERT INTO bank_accounts 
                    (bank_id, account_number, account_holder, currency, iban, swift_code, branch_name, balance, status, is_primary, notes) 
                    VALUES 
                    ($bank_id, '$account_number', '$account_holder', '$currency', '$iban', '$swift_code', '$branch_name', $balance, '$status', $is_primary, '$notes')";
            
            $conn->query($sql);
        }
        
        $message = "تم حفظ الحسابات بنجاح!";
        $message_type = "success";
    }
    // معالجة حذف البنك
    elseif (isset($_POST['delete_bank'])) {
        $bank_id = (int)$_POST['bank_id'];
        
        // حذف الشعار إذا كان موجوداً
        $logo_result = $conn->query("SELECT logo FROM banks WHERE id=$bank_id");
        if ($logo_result && $logo_result->num_rows > 0) {
            $logo = $logo_result->fetch_assoc()['logo'];
            if ($logo && file_exists($logo)) {
                unlink($logo);
            }
        }
        
        $sql = "DELETE FROM banks WHERE id=$bank_id";
        if ($conn->query($sql)) {
            $message = "تم حذف البنك بنجاح!";
            $message_type = "success";
        } else {
            $message = "خطأ في الحذف: " . $conn->error;
            $message_type = "error";
        }
    }
}

// جلب جميع البنوك مع عدد حساباتها
$banks_result = $conn->query("
    SELECT b.*, 
           COUNT(ba.id) as accounts_count,
           SUM(CASE WHEN ba.status = 'active' THEN 1 ELSE 0 END) as active_accounts
    FROM banks b
    LEFT JOIN bank_accounts ba ON b.id = ba.bank_id
    GROUP BY b.id
    ORDER BY b.created_at DESC
");

// جلب بيانات البنك للتعديل إذا طُلب
$edit_bank = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $bank_id = (int)$_GET['edit'];
    $bank_result = $conn->query("SELECT * FROM banks WHERE id = $bank_id");
    if ($bank_result && $bank_result->num_rows > 0) {
        $edit_bank = $bank_result->fetch_assoc();
    }
}

// جلب حسابات البنك للتعديل إذا طُلب
$bank_accounts = [];
if (isset($_GET['accounts']) && is_numeric($_GET['accounts'])) {
    $bank_id = (int)$_GET['accounts'];
    $accounts_result = $conn->query("SELECT * FROM bank_accounts WHERE bank_id = $bank_id ORDER BY is_primary DESC, id ASC");
    if ($accounts_result) {
        while ($account = $accounts_result->fetch_assoc()) {
            $bank_accounts[] = $account;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البنوك والحسابات البنكية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
     <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .page-title {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .page-title h2 {
            font-size: 2.2em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .date {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .banks-container {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }

        .bank-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .bank-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .bank-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .bank-name {
            font-size: 1.4em;
            font-weight: 700;
            color: #2c3e50;
        }

        .bank-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #e9ecef;
            color: #007bff;
        }

        .bank-accounts {
            margin-bottom: 20px;
        }

        .account-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .account-info {
            flex: 1;
        }

        .account-number {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .account-holder {
            color: #6c757d;
            font-size: 0.9em;
        }

        .account-currency {
            background: #e9ecef;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .account-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .add-account-btn, .add-bank-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .add-account-btn:hover, .add-bank-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .add-bank-btn {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            min-height: 120px;
            border: 2px dashed #dee2e6;
            flex-direction: column;
        }

        .add-bank-btn:hover {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }

        .add-bank-btn i {
            font-size: 2em;
        }

        /* النافذة المنبثقة */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 25px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.5em;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.2em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.2);
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .accounts-list {
            margin: 25px 0;
        }

        .account-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .account-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .account-card-title {
            font-weight: 600;
            color: #2c3e50;
        }

        .account-card-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .account-detail {
            display: flex;
            flex-direction: column;
        }

        .account-detail-label {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #28a745;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .message {
            padding: 15px;
            margin: 20px 30px;
            border-radius: 8px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            font-weight: 500;
            text-align: center;
        }

        @media (max-width: 768px) {
            .banks-container {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .account-card-body {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
       <style>
        :root {
            --primary: #6C63FF;
            --primary-light: #8A84FF;
            --primary-dark: #524BC2;
            --secondary: #FF6584;
            --accent: #36D1DC;
            --success: #4ECDC4;
            --warning: #FF9A76;
            --info: #6A89CC;
            --light: #F8F9FD;
            --dark: #2D3748;
            --gray: #718096;
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f2f8 100%);
            color: var(--dark);
            direction: rtl;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* التصميم الرئيسي */
        .dashboard {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* الشريط الجانبي */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            transition: var(--transition);
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .sidebar.collapsed {
            transform: translateX(100%);
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .logo h1 {
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .logo p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            border-right: 4px solid transparent;
            position: relative;
        }

        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-right-color: var(--accent);
        }

        .menu-item i {
            margin-left: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .menu-item span {
            font-size: 14px;
            font-weight: 500;
        }

        .submenu {
            background-color: rgba(0, 0, 0, 0.15);
            display: none;
        }

        .submenu-item {
            padding: 10px 20px 10px 45px;
            font-size: 13px;
            transition: var(--transition);
        }

        .submenu-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .menu-item.active .submenu {
            display: block;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-right: 0;
        }

        /* الهيدر */
        .header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 0 20px;
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
            margin-left: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .toggle-sidebar:hover {
            background-color: var(--light);
            color: var(--primary);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .header-icon {
            position: relative;
            margin-left: 15px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .header-icon:hover {
            background-color: var(--light);
        }

        .header-icon i {
            font-size: 18px;
            color: var(--gray);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: linear-gradient(135deg, var(--secondary), var(--warning));
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
            transition: var(--transition);
        }

        .user-profile:hover {
            background-color: var(--light);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            margin-left: 10px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 11px;
            color: var(--gray);
        }

        /* محتوى الصفحة */
        .page-content {
            flex: 1;
            padding: 20px;
        }

        .page-title {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 24px;
            color: var(--dark);
            font-weight: 700;
        }

        .page-title .date {
            color: var(--gray);
            font-size: 14px;
        }

        /* بطاقات البنوك */
        .banks-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .bank-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .bank-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .bank-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .bank-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .bank-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }

        .bank-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .bank-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--light);
            color: var(--gray);
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background-color: var(--primary);
            color: white;
        }

        .bank-accounts {
            margin-top: 15px;
        }

        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .account-item:last-child {
            border-bottom: none;
        }

        .account-info {
            flex: 1;
        }

        .account-number {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .account-holder {
            font-size: 12px;
            color: var(--gray);
        }

        .account-currency {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 12px;
            background-color: var(--light);
            font-weight: 600;
        }

        .account-status {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .status-active {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
        }

        .status-pending {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .status-inactive {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .add-account-btn {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background-color: var(--light);
            border: 1px dashed var(--gray);
            border-radius: var(--radius);
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        .add-account-btn:hover {
            background-color: var(--primary-light);
            color: white;
            border-color: var(--primary);
        }

        /* زر إضافة بنك جديد */
        .add-bank-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
            border: 2px dashed var(--gray);
            flex-direction: column;
            height: 100%;
            min-height: 200px;
        }

        .add-bank-btn:hover {
            border-color: var(--primary);
            background-color: var(--light);
        }

        .add-bank-btn i {
            font-size: 40px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .add-bank-btn span {
            color: var(--gray);
            font-weight: 600;
        }

        /* النافذة المنبثقة */
        .modal-overlay {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: var(--transition);
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .close-modal:hover {
            background-color: var(--light);
            color: var(--dark);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--light);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .btn-danger {
            background-color: var(--secondary);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e04e6d;
        }

        .accounts-list {
            margin-top: 20px;
        }

        .account-card {
            background-color: var(--light);
            border-radius: var(--radius);
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
        }

        .account-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .account-card-title {
            font-weight: 600;
            color: var(--dark);
        }

        .account-card-actions {
            display: flex;
            gap: 5px;
        }

        .account-card-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .account-detail {
            font-size: 13px;
        }

        .account-detail-label {
            color: var(--gray);
            margin-bottom: 3px;
        }

        .account-detail-value {
            font-weight: 600;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: var(--transition);
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: var(--transition);
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* التجاوب */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .mobile-menu-btn {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .header {
                padding: 0 15px;
            }
            
            .user-info {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .banks-container {
                grid-template-columns: 1fr;
            }
            
            .page-content {
                padding: 15px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .account-card-body {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .modal {
                width: 95%;
            }
            
            .account-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .account-card-actions {
                align-self: flex-end;
            }
        }
    </style>
   
    <style>
        /* إضافة الأنماط السابقة مع بعض التعديلات */

        /* إضافة أنماط للرسائل */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background-color: rgba(78, 205, 196, 0.15);
            border-left: 4px solid var(--success);
            color: #0d625c;
        }

        .alert-error {
            background-color: rgba(255, 101, 132, 0.15);
            border-left: 4px solid var(--secondary);
            color: #cc2e5d;
        }

        .alert-warning {
            background-color: rgba(255, 154, 118, 0.15);
            border-left: 4px solid var(--warning);
            color: #cc6a3d;
        }

        .alert-info {
            background-color: rgba(106, 137, 204, 0.15);
            border-left: 4px solid var(--info);
            color: #3a4a80;
        }

        .alert .close-alert {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: var(--transition);
        }

        .alert .close-alert:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* تعديلات على بطاقات البنوك */
        .bank-card {
            position: relative;
        }

        .bank-logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow);
        }

        .bank-stats {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 12px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            background-color: rgba(108, 99, 255, 0.1);
            border-radius: 12px;
            color: var(--primary);
        }

        /* تعديلات على النافذة المنبثقة */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .account-card {
            position: relative;
        }

        .primary-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--primary);
            color: white;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .account-fields {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .account-fields .form-group {
            margin-bottom: 0;
        }

        /* تنسيق حقول الحساب */
        .form-control-sm {
            padding: 8px 12px;
            font-size: 13px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        /* تخصيص زر الإضافة */
        .add-bank-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 200px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-bank-btn:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }

        /* تحسين العرض على الجوال */
        @media (max-width: 768px) {
            .account-fields {
                grid-template-columns: 1fr;
            }
            
            .bank-stats {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- الشريط الجانبي -->
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <!-- الهيدر -->
            <?php include 'header.php'; ?>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <div class="page-title">
                    <h2>البنوك والحسابات البنكية</h2>
                    <div class="date"><?php echo date('l، j F Y'); ?></div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?>">
                        <div>
                            <i class="fas fa-<?= 
                                $message_type == 'success' ? 'check-circle' : 
                                ($message_type == 'error' ? 'exclamation-circle' : 
                                ($message_type == 'warning' ? 'exclamation-triangle' : 'info-circle')) 
                            ?>"></i>
                            <span style="margin-right: 10px;"><?= $message ?></span>
                        </div>
                        <button class="close-alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- بطاقات البنوك -->
                <div class="banks-container">
                    <?php if ($banks_result->num_rows > 0): ?>
                        <?php while ($bank = $banks_result->fetch_assoc()): ?>
                            <div class="bank-card">
                                <div class="bank-header">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if ($bank['logo']): ?>
                                            <img src="<?= $bank['logo'] ?>" alt="<?= htmlspecialchars($bank['name']) ?>" class="bank-logo-img">
                                        <?php else: ?>
                                            <div class="bank-logo">
                                                <?= mb_substr($bank['name'], 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="bank-name"><?= htmlspecialchars($bank['name']) ?></div>
                                            <?php if ($bank['description']): ?>
                                                <div style="font-size: 12px; color: var(--gray);"><?= htmlspecialchars($bank['description']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="bank-actions">
                                        <button class="action-btn edit-bank" data-bank-id="<?= $bank['id'] ?>" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn manage-accounts" data-bank-id="<?= $bank['id'] ?>" title="إدارة الحسابات">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف البنك؟');">
                                            <input type="hidden" name="bank_id" value="<?= $bank['id'] ?>">
                                            <button type="submit" name="delete_bank" class="action-btn" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="bank-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-wallet"></i>
                                        <span><?= $bank['accounts_count'] ?> حساب</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span><?= $bank['active_accounts'] ?> نشط</span>
                                    </div>
                                    <div class="stat-item" style="background-color: <?= $bank['status'] == 'active' ? 'rgba(78, 205, 196, 0.15)' : 'rgba(255, 154, 118, 0.15)' ?>; color: <?= $bank['status'] == 'active' ? 'var(--success)' : 'var(--warning)' ?>;">
                                        <i class="fas fa-circle"></i>
                                        <span><?= $bank['status'] == 'active' ? 'نشط' : 'غير نشط' ?></span>
                                    </div>
                                </div>

                                <!-- عرض الحسابات -->
                                <div class="bank-accounts">
                                    <?php 
                                    $accounts_result = $conn->query("
                                        SELECT * FROM bank_accounts 
                                        WHERE bank_id = {$bank['id']} 
                                        ORDER BY is_primary DESC, id ASC 
                                        LIMIT 3
                                    ");
                                    if ($accounts_result->num_rows > 0):
                                        while ($account = $accounts_result->fetch_assoc()):
                                    ?>
                                        <div class="account-item">
                                            <div class="account-info">
                                                <div class="account-number">
                                                    <?= htmlspecialchars($account['account_number']) ?>
                                                    <?php if ($account['is_primary']): ?>
                                                        <span style="color: var(--primary); font-size: 10px; margin-right: 5px;">(رئيسي)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="account-holder"><?= htmlspecialchars($account['account_holder']) ?></div>
                                            </div>
                                            <div class="account-currency"><?= $account['currency'] ?></div>
                                            <div class="account-status status-<?= $account['status'] ?>">
                                                <?= $account['status'] == 'active' ? 'مفعل' : ($account['status'] == 'pending' ? 'بانتظار' : 'غير مفعل') ?>
                                            </div>
                                        </div>
                                    <?php 
                                        endwhile; 
                                        if ($accounts_result->num_rows == 3):
                                    ?>
                                        <div style="text-align: center; padding: 10px; color: var(--gray); font-size: 12px;">
                                            <i class="fas fa-ellipsis-h"></i>
                                            <span>والمزيد...</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php else: ?>
                                        <div style="text-align: center; padding: 15px; color: var(--gray);">
                                            <i class="fas fa-wallet" style="font-size: 20px; margin-bottom: 5px; display: block;"></i>
                                            <span>لا توجد حسابات</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button class="add-account-btn manage-accounts" data-bank-id="<?= $bank['id'] ?>">
                                    <i class="fas fa-plus"></i>
                                    <span>إدارة الحسابات</span>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bank-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <i class="fas fa-university" style="font-size: 50px; color: var(--gray); margin-bottom: 20px; display: block;"></i>
                            <h3 style="color: var(--gray); margin-bottom: 15px;">لا توجد بنوك مضافة</h3>
                            <p style="color: var(--gray); margin-bottom: 20px;">ابدأ بإضافة أول بنك لك</p>
                        </div>
                    <?php endif; ?>

                    <!-- زر إضافة بنك جديد -->
                    <div class="add-bank-btn" id="addBankBtn">
                        <i class="fas fa-plus-circle" style="font-size: 40px; color: var(--gray); margin-bottom: 10px;"></i>
                        <span style="color: var(--gray); font-weight: 600;">إضافة بنك جديد</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- النافذة المنبثقة لإضافة/تعديل البنك -->
    <div class="modal-overlay" id="bankModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">إضافة بنك جديد</h3>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="bankForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="bank_id" id="bankId">
                    <input type="hidden" name="save_bank" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">شعار البنك</label>
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                            <div id="logoPreview" style="width: 80px; height: 80px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <i class="fas fa-building" style="font-size: 30px; color: #999;"></i>
                            </div>
                            <div>
                                <input type="file" id="logoUpload" name="logo" accept="image/*" style="display: none;">
                                <label for="logoUpload" class="upload-btn" style="display: inline-block; padding: 10px 20px; background-color: var(--light); border-radius: 8px; cursor: pointer; transition: var(--transition);">
                                    <i class="fas fa-upload"></i> تحميل الشعار
                                </label>
                                <p style="font-size: 12px; color: #666; margin-top: 5px;">
                                    الحجم الموصى به: 200x200 بكسل<br>
                                    الأنواع المسموحة: JPG, PNG, GIF, SVG
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="bankName">اسم البنك *</label>
                        <input type="text" class="form-control" id="bankName" name="name" placeholder="أدخل اسم البنك" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="bankWebsite">الموقع الإلكتروني</label>
                            <input type="url" class="form-control" id="bankWebsite" name="website" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="bankStatus">الحالة</label>
                            <select class="form-control" id="bankStatus" name="status">
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="bankDescription">وصف البنك</label>
                        <textarea class="form-control" id="bankDescription" name="description" rows="3" placeholder="أدخل وصفاً للبنك"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- النافذة المنبثقة لإدارة الحسابات -->
    <div class="modal-overlay" id="accountsModal">
        <div class="modal" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">إدارة الحسابات البنكية</h3>
                <button class="close-modal" id="closeAccountsModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="accountsForm" method="POST">
                    <input type="hidden" name="bank_id" id="accountsBankId">
                    <input type="hidden" name="save_accounts" value="1">
                    
                    <div id="accountsList">
                        <!-- الحسابات ستضاف هنا ديناميكياً -->
                    </div>
                    
                    <button type="button" class="add-account-btn" id="addNewAccountBtn" style="margin-top: 10px; width: 100%;">
                        <i class="fas fa-plus"></i>
                        <span>إضافة حساب جديد</span>
                    </button>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelAccountsBtn">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ الحسابات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // عناصر DOM
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const addBankBtn = document.getElementById('addBankBtn');
        const bankModal = document.getElementById('bankModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const modalTitle = document.getElementById('modalTitle');
        const bankForm = document.getElementById('bankForm');
        const bankIdInput = document.getElementById('bankId');
        const bankNameInput = document.getElementById('bankName');
        const bankWebsiteInput = document.getElementById('bankWebsite');
        const bankStatusSelect = document.getElementById('bankStatus');
        const bankDescriptionInput = document.getElementById('bankDescription');
        const logoPreview = document.getElementById('logoPreview');
        const logoUpload = document.getElementById('logoUpload');
        
        // عناصر إدارة الحسابات
        const accountsModal = document.getElementById('accountsModal');
        const closeAccountsModal = document.getElementById('closeAccountsModal');
        const cancelAccountsBtn = document.getElementById('cancelAccountsBtn');
        const accountsBankId = document.getElementById('accountsBankId');
        const accountsList = document.getElementById('accountsList');
        const addNewAccountBtn = document.getElementById('addNewAccountBtn');
        const accountsForm = document.getElementById('accountsForm');

        // تبديل الشريط الجانبي
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }

        // فتح نافذة إضافة بنك جديد
        addBankBtn.addEventListener('click', () => {
            modalTitle.textContent = 'إضافة بنك جديد';
            bankForm.reset();
            bankIdInput.value = '';
            logoPreview.innerHTML = '<i class="fas fa-building" style="font-size: 30px; color: #999;"></i>';
            bankModal.classList.add('active');
        });

        // فتح نافذة تعديل البنك
        document.querySelectorAll('.edit-bank').forEach(button => {
            button.addEventListener('click', function() {
                const bankId = this.getAttribute('data-bank-id');
                
                // جلب بيانات البنك عبر AJAX
                fetch(`get_bank.php?id=${bankId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modalTitle.textContent = `تعديل بنك ${data.bank.name}`;
                            bankIdInput.value = data.bank.id;
                            bankNameInput.value = data.bank.name;
                            bankWebsiteInput.value = data.bank.website || '';
                            bankStatusSelect.value = data.bank.status;
                            bankDescriptionInput.value = data.bank.description || '';
                            
                            // عرض الشعار إذا كان موجوداً
                            if (data.bank.logo) {
                                logoPreview.innerHTML = `<img src="${data.bank.logo}" alt="${data.bank.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
                            } else {
                                logoPreview.innerHTML = '<i class="fas fa-building" style="font-size: 30px; color: #999;"></i>';
                            }
                            
                            bankModal.classList.add('active');
                        } else {
                            alert('حدث خطأ في جلب بيانات البنك');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال بالخادم');
                    });
            });
        });

        // فتح نافذة إدارة الحسابات
        document.querySelectorAll('.manage-accounts').forEach(button => {
            button.addEventListener('click', function() {
                const bankId = this.getAttribute('data-bank-id');
                accountsBankId.value = bankId;
                
                // جلب حسابات البنك عبر AJAX
                fetch(`get_accounts.php?bank_id=${bankId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            accountsList.innerHTML = '';
                            if (data.accounts.length > 0) {
                                data.accounts.forEach((account, index) => {
                                    addAccountToForm(account, index + 1);
                                });
                            } else {
                                addAccountToForm(null, 1);
                            }
                            accountsModal.classList.add('active');
                        } else {
                            alert('حدث خطأ في جلب الحسابات');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال بالخادم');
                    });
            });
        });

        // إضافة حساب جديد للنموذج
        function addAccountToForm(account = null, index = null) {
            if (!index) {
                index = accountsList.querySelectorAll('.account-card').length + 1;
            }
            
            const accountId = account ? account.id : '';
            const isPrimary = account ? account.is_primary : false;
            
            const accountCard = document.createElement('div');
            accountCard.className = 'account-card';
            accountCard.innerHTML = `
                ${isPrimary ? '<span class="primary-badge">رئيسي</span>' : ''}
                <div class="account-card-header">
                    <div class="account-card-title">الحساب #${index}</div>
                    <div class="account-card-actions">
                        <button type="button" class="action-btn remove-account" ${accountsList.querySelectorAll('.account-card').length === 0 ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="account-fields">
                    <input type="hidden" name="accounts[${index}][id]" value="${accountId}">
                    
                    <div class="form-group">
                        <label class="form-label">رقم الحساب *</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][account_number]" 
                               value="${account ? account.account_number : ''}" 
                               placeholder="أدخل رقم الحساب" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">اسم صاحب الحساب *</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][account_holder]" 
                               value="${account ? account.account_holder : ''}" 
                               placeholder="أدخل اسم صاحب الحساب" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">العملة</label>
                        <select class="form-control form-control-sm" name="accounts[${index}][currency]">
                            <option value="SAR" ${account && account.currency === 'SAR' ? 'selected' : ''}>ريال سعودي</option>
                            <option value="USD" ${account && account.currency === 'USD' ? 'selected' : ''}>دولار أمريكي</option>
                            <option value="YER" ${account && account.currency === 'YER' ? 'selected' : ''}>ريال يمني</option>
                            <option value="EUR" ${account && account.currency === 'EUR' ? 'selected' : ''}>يورو</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">الرصيد</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" 
                               name="accounts[${index}][balance]" 
                               value="${account ? account.balance : '0.00'}" 
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رقم الآيبان (IBAN)</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][iban]" 
                               value="${account ? account.iban : ''}" 
                               placeholder="SA00 0000 0000 0000 0000 0000">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رمز السويفت (SWIFT)</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][swift_code]" 
                               value="${account ? account.swift_code : ''}" 
                               placeholder="BNKSARSAXXX">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">اسم الفرع</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][branch_name]" 
                               value="${account ? account.branch_name : ''}" 
                               placeholder="أدخل اسم الفرع">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">الحالة</label>
                        <select class="form-control form-control-sm" name="accounts[${index}][status]">
                            <option value="active" ${account && account.status === 'active' ? 'selected' : ''}>نشط</option>
                            <option value="pending" ${account && account.status === 'pending' ? 'selected' : ''}>بانتظار الموافقة</option>
                            <option value="inactive" ${account && account.status === 'inactive' ? 'selected' : ''}>غير نشط</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">حساب رئيسي</label>
                        <div style="display: flex; align-items: center; height: 38px;">
                            <label class="toggle-switch">
                                <input type="checkbox" name="accounts[${index}][is_primary]" 
                                       ${isPrimary ? 'checked' : ''} 
                                       onchange="setAsPrimary(this, ${index})">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" name="accounts[${index}][notes]" rows="2" 
                                  placeholder="أدخل أي ملاحظات">${account ? account.notes : ''}</textarea>
                    </div>
                </div>
            `;
            
            accountsList.appendChild(accountCard);
            
            // إضافة حدث لحذف الحساب
            const removeBtn = accountCard.querySelector('.remove-account');
            removeBtn.addEventListener('click', function() {
                if (accountsList.querySelectorAll('.account-card').length > 1) {
                    accountCard.remove();
                    updateAccountTitles();
                } else {
                    alert('يجب أن يكون هناك حساب واحد على الأقل');
                }
            });
        }

        // تعيين حساب كرئيسي
        function setAsPrimary(checkbox, index) {
            if (checkbox.checked) {
                // إلغاء تحديد جميع الحسابات الأخرى
                document.querySelectorAll('input[name$="[is_primary]"]').forEach(otherCheckbox => {
                    if (otherCheckbox !== checkbox) {
                        otherCheckbox.checked = false;
                        otherCheckbox.closest('.account-card').querySelector('.primary-badge')?.remove();
                    }
                });
                
                // إضافة بادج الحساب الرئيسي
                const accountCard = checkbox.closest('.account-card');
                if (!accountCard.querySelector('.primary-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'primary-badge';
                    badge.textContent = 'رئيسي';
                    accountCard.prepend(badge);
                }
            } else {
                checkbox.closest('.account-card').querySelector('.primary-badge')?.remove();
            }
        }

        // تحديث عناوين الحسابات
        function updateAccountTitles() {
            const accountCards = accountsList.querySelectorAll('.account-card');
            accountCards.forEach((card, index) => {
                const title = card.querySelector('.account-card-title');
                title.textContent = `الحساب #${index + 1}`;
                
                // تحديث أسماء الحقول في المصفوفة
                const inputs = card.querySelectorAll('[name^="accounts["]');
                inputs.forEach(input => {
                    const oldName = input.getAttribute('name');
                    const newName = oldName.replace(/accounts\[\d+\]/, `accounts[${index + 1}]`);
                    input.setAttribute('name', newName);
                });
            });
        }

        // زر إضافة حساب جديد في نافذة الحسابات
        addNewAccountBtn.addEventListener('click', () => {
            addAccountToForm();
            updateAccountTitles();
        });

        // معاينة الشعار
        logoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.innerHTML = `<img src="${e.target.result}" alt="شعار البنك" style="width: 100%; height: 100%; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // إغلاق النوافذ المنبثقة
        closeModal.addEventListener('click', () => {
            bankModal.classList.remove('active');
        });

        cancelBtn.addEventListener('click', () => {
            bankModal.classList.remove('active');
        });

        closeAccountsModal.addEventListener('click', () => {
            accountsModal.classList.remove('active');
        });

        cancelAccountsBtn.addEventListener('click', () => {
            accountsModal.classList.remove('active');
        });

        // إغلاق النوافذ عند النقر خارجها
        window.addEventListener('click', (e) => {
            if (e.target === bankModal) {
                bankModal.classList.remove('active');
            }
            if (e.target === accountsModal) {
                accountsModal.classList.remove('active');
            }
        });

        // التحقق من صحة النماذج
        bankForm.addEventListener('submit', function(e) {
            if (!bankNameInput.value.trim()) {
                e.preventDefault();
                alert('يرجى إدخال اسم البنك');
                bankNameInput.focus();
                return false;
            }
        });

        accountsForm.addEventListener('submit', function(e) {
            const accountNumbers = document.querySelectorAll('input[name$="[account_number]"]');
            let isValid = true;
            
            accountNumbers.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ff4757';
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('يرجى ملء جميع حقول أرقام الحسابات');
                return false;
            }
        });

        // إغلاق رسائل التنبيه
        document.querySelectorAll('.close-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert').style.display = 'none';
            });
        });

        // تهيئة الأحداث عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            // إغلاق رسائل التنبيه تلقائياً بعد 5 ثوانٍ
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>