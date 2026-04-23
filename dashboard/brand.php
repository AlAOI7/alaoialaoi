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

// إنشاء مجلد لصور الشعارات إذا لم يكن موجوداً
if (!file_exists('brands_logos')) {
    mkdir('brands_logos', 0777, true);
}

// إنشاء مجلد الصور إذا لم يكن موجوداً
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// معالجة العمليات
$message = "";
$message_type = ""; // success, error, warning, info
$current_view = isset($_GET['view']) ? $_GET['view'] : 'list';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_brand'])) {
        // إضافة علامة تجارية جديدة
        $name = $conn->real_escape_string(trim($_POST['name']));
        $country = $conn->real_escape_string(trim($_POST['country']));
        $website = $conn->real_escape_string(trim($_POST['website']));
        $status = $conn->real_escape_string($_POST['status']);
        $description = $conn->real_escape_string(trim($_POST['description']));

        // التحقق من الحقول المطلوبة
        if (empty($name)) {
            $message = "اسم العلامة التجارية مطلوب!";
            $message_type = "error";
            $current_view = 'add';
        } else {
            // معالجة رفع الشعار
            $logo = NULL;
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (in_array($_FILES['logo']['type'], $allowed_types)) {
                    $logo = 'brands_logos/' . time() . '_' . basename($_FILES['logo']['name']);
                    move_uploaded_file($_FILES['logo']['tmp_name'], $logo);
                }
            }

            $sql = "INSERT INTO brands (name, logo, country, website, status, description) 
                    VALUES ('$name', " . ($logo ? "'$logo'" : "NULL") . ", '$country', '$website', '$status', '$description')";

            if ($conn->query($sql)) {
                $message = "تم إضافة العلامة التجارية بنجاح!";
                $message_type = "success";
                $current_view = 'list';
            } else {
                $message = "خطأ في الإضافة: " . $conn->error;
                $message_type = "error";
                $current_view = 'add';
            }
        }
    } elseif (isset($_POST['update_brand'])) {
        // تعديل علامة تجارية
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string(trim($_POST['name']));
        $country = $conn->real_escape_string(trim($_POST['country']));
        $website = $conn->real_escape_string(trim($_POST['website']));
        $status = $conn->real_escape_string($_POST['status']);
        $description = $conn->real_escape_string(trim($_POST['description']));

        // التحقق من الحقول المطلوبة
        if (empty($name)) {
            $message = "اسم العلامة التجارية مطلوب!";
            $message_type = "error";
            $current_view = 'edit';
        } else {
            // معالجة رفع الشعار الجديد
            $logo_update = "";
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (in_array($_FILES['logo']['type'], $allowed_types)) {
                    // حذف الشعار القديم إذا كان موجوداً
                    $old_logo_result = $conn->query("SELECT logo FROM brands WHERE id=$id");
                    if ($old_logo_result && $old_logo_result->num_rows > 0) {
                        $old_logo = $old_logo_result->fetch_assoc()['logo'];
                        if ($old_logo && file_exists($old_logo)) {
                            unlink($old_logo);
                        }
                    }

                    $logo = 'brands_logos/' . time() . '_' . basename($_FILES['logo']['name']);
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo)) {
                        $logo_update = ", logo='$logo'";
                    }
                }
            }

            $sql = "UPDATE brands SET 
                    name='$name', 
                    country='$country', 
                    website='$website', 
                    status='$status', 
                    description='$description' 
                    $logo_update 
                    WHERE id=$id";

            if ($conn->query($sql)) {
                $message = "تم تعديل العلامة التجارية بنجاح!";
                $message_type = "success";
                $current_view = 'list';
            } else {
                $message = "خطأ في التعديل: " . $conn->error;
                $message_type = "error";
                $current_view = 'edit';
            }
        }
    } elseif (isset($_POST['delete_brand'])) {
        // حذف علامة تجارية
        $id = (int)$_POST['id'];

        // حذف الشعار إذا كان موجوداً
        $logo_result = $conn->query("SELECT logo FROM brands WHERE id=$id");
        if ($logo_result && $logo_result->num_rows > 0) {
            $logo = $logo_result->fetch_assoc()['logo'];
            if ($logo && file_exists($logo)) {
                unlink($logo);
            }
        }

        $sql = "DELETE FROM brands WHERE id=$id";
        if ($conn->query($sql)) {
            $message = "تم حذف العلامة التجارية بنجاح!";
            $message_type = "success";
        } else {
            $message = "خطأ في الحذف: " . $conn->error;
            $message_type = "error";
        }
    }
}

// جلب بيانات العلامات التجارية
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = "";
if ($search) {
    $where_clause = "WHERE name LIKE '%$search%' OR country LIKE '%$search%' OR description LIKE '%$search%'";
}

// جلب عدد المنتجات لكل علامة
$brands_result = $conn->query("
    SELECT b.*, 
           (SELECT COUNT(*) FROM products WHERE brand_id = b.id) as products_count
    FROM brands b
    $where_clause
    ORDER BY created_at DESC
");

// الحصول على بيانات العلامة للتعديل إذا طُلب
$edit_brand = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM brands WHERE id = $edit_id");
    if ($edit_result && $edit_result->num_rows > 0) {
        $edit_brand = $edit_result->fetch_assoc();
        $current_view = 'edit';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العلامات التجارية - المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .dashboard {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

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

        .menu-item:hover,
        .menu-item.active {
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

        .brands-management {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        @media (max-width: 1200px) {
            .brands-management {
                grid-template-columns: 1fr;
            }
        }

        .brands-actions,
        .brands-content {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .brands-actions h3,
        .brands-content h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-left: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #3BB5AB);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #3BB5AB, var(--success));
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #FF8A5C);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #FF8A5C, var(--warning));
            box-shadow: 0 5px 15px rgba(255, 154, 118, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--secondary), #FF4D6D);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #FF4D6D, var(--secondary));
            box-shadow: 0 5px 15px rgba(255, 101, 132, 0.3);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info), #5A6FB8);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #5A6FB8, var(--info));
            box-shadow: 0 5px 15px rgba(106, 137, 204, 0.3);
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 13px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #f0f0f0;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .table td {
            font-size: 13px;
        }

        .action-buttons-small {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn.view {
            background-color: rgba(106, 137, 204, 0.15);
            color: var(--info);
        }

        .action-btn.edit {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .action-btn.delete {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .logo-upload {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 576px) {
            .logo-upload {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .logo-preview {
            width: 100px;
            height: 100px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8f9fa;
        }

        .logo-preview img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }

        .upload-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .upload-btn:hover {
            background: #e9ecef;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--primary);
            font-size: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
            width: 30px;
            height: 30px;
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

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
            z-index: 1001;
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
        }

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

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box .form-control {
            width: 250px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
        }

        .status-inactive {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

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
            .page-content {
                padding: 15px;
            }

            .header-right {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .header-icon {
                margin-left: 10px;
            }

            .page-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-title .date {
                margin-top: 5px;
            }

            .search-box {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box .form-control {
                width: 100%;
            }

            .modal-content {
                max-width: 95%;
            }
        }

        @media (max-width: 576px) {
            .table {
                display: block;
                overflow-x: auto;
            }

            .brands-actions,
            .brands-content {
                padding: 15px;
            }

            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 400px) {
            .header {
                padding: 0 10px;
            }

            .page-content {
                padding: 10px;
            }

            .modal-body {
                padding: 15px;
            }

            .modal-footer {
                padding: 15px;
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

            <?php include 'header.php'; ?>
            
            <div class="page-content">
                <!-- شاشة العلامات التجارية -->
                <div id="brands-view" class="brands-view">
                    <div class="page-title">
                        <h2>إدارة العلامات التجارية</h2>
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

                    <div class="brands-management">
                        <div class="brands-actions">
                            <h3>الإجراءات</h3>
                            <div class="action-buttons">
                                <button class="btn btn-primary" id="show-brands-btn">
                                    <i class="fas fa-list"></i> عرض العلامات
                                </button>
                                <button class="btn btn-success" id="add-brand-btn">
                                    <i class="fas fa-plus"></i> إضافة علامة
                                </button>
                                <?php if ($current_view == 'edit' && $edit_brand): ?>
                                    <a href="?view=edit&edit=<?= $edit_brand['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> تعديل العلامة
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="brands-content">
                            <!-- قسم عرض العلامات -->
                            <div class="content-section <?= $current_view == 'list' ? 'active' : '' ?>" id="brands-list-section">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h3>قائمة العلامات التجارية</h3>
                                    <form method="GET" class="search-box">
                                        <input type="hidden" name="view" value="list">
                                        <input type="text" name="search" class="form-control" placeholder="بحث في العلامات..."
                                            value="<?= htmlspecialchars($search) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search"></i> بحث
                                        </button>
                                        <?php if ($search): ?>
                                            <a href="?view=list" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> إلغاء
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>الشعار</th>
                                                <th>اسم العلامة</th>
                                                <th>البلد</th>
                                                <th>عدد المنتجات</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($brands_result->num_rows > 0): ?>
                                                <?php while ($brand = $brands_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($brand['logo']): ?>
                                                                <img src="<?= $brand['logo'] ?>" alt="شعار <?= htmlspecialchars($brand['name']) ?>" class="brand-logo">
                                                            <?php else: ?>
                                                                
                                                                <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                                                    <i class="fas fa-image"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($brand['name']) ?></td>
                                                        <td><?= htmlspecialchars($brand['country']) ?></td>
                                                        <td>
                                                            <span class="status-badge <?= $brand['products_count'] > 0 ? 'status-active' : 'status-inactive' ?>">
                                                                <?= $brand['products_count'] ?> منتج
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="status-badge <?= $brand['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                                                <?= $brand['status'] == 'active' ? 'نشط' : 'غير نشط' ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons-small">
                                                                <a href="?view=edit&edit=<?= $brand['id'] ?>" class="action-btn edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <form method="POST" style="display:inline">
                                                                    <input type="hidden" name="id" value="<?= $brand['id'] ?>">
                                                                    <button type="submit" name="delete_brand" class="action-btn delete"
                                                                        onclick="return confirm('هل أنت متأكد من حذف العلامة التجارية <?= htmlspecialchars($brand['name']) ?>؟')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                                <?php if ($brand['website']): ?>
                                                                    <a href="<?= $brand['website'] ?>" target="_blank" class="action-btn view" title="زيارة الموقع">
                                                                        <i class="fas fa-external-link-alt"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                                        <i class="fas fa-box-open" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                                        <?= $search ? 'لم يتم العثور على علامات تجارية تطابق بحثك' : 'لا توجد علامات تجارية مضافة' ?>
                                                        <?php if (!$search): ?>
                                                            <br>
                                                            <button class="btn btn-success mt-3" id="add-brand-from-empty">
                                                                <i class="fas fa-plus"></i> إضافة أول علامة تجارية
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- قسم إضافة علامة -->
                            <div class="content-section <?= $current_view == 'add' ? 'active' : '' ?>" id="add-brand-section">
                                <h3>إضافة علامة تجارية جديدة</h3>

                                <div class="logo-upload">
                                    <div class="logo-preview" id="brand-logo-preview-container">
                                        <img src="" alt="شعار العلامة" id="brand-logo-preview">
                                        <span id="no-logo" style="color: #999; display: block;">
                                            <i class="fas fa-image" style="font-size: 24px; margin-bottom: 5px; display: block;"></i>
                                            لا يوجد شعار
                                        </span>
                                    </div>
                                    <div>
                                        <input type="file" id="brand-logo-upload" name="logo" accept="image/jpeg, image/png, image/gif" style="display: none;">
                                        <label for="brand-logo-upload" class="upload-btn">
                                            <i class="fas fa-upload"></i> تحميل الشعار
                                        </label>
                                        <p style="font-size: 12px; color: #666; margin-top: 5px;">
                                            <i class="fas fa-info-circle"></i>
                                            الحجم الموصى به: 200x200 بكسل<br>
                                            الأنواع المسموحة: JPG, PNG, GIF, SVG<br>
                                            الحد الأقصى: 5MB
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" enctype="multipart/form-data" id="add-brand-form">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="brand-name">اسم العلامة التجارية <span style="color: #ff4757;">*</span></label>
                                            <input type="text" id="brand-name" name="name" class="form-control"
                                                placeholder="أدخل اسم العلامة التجارية" required
                                                value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="brand-country">البلد</label>
                                            <select id="brand-country" name="country" class="form-control">
                                                <option value="">اختر البلد</option>
                                                <option value="SA" <?= (isset($_POST['country']) && $_POST['country'] == 'SA') ? 'selected' : '' ?>>المملكة العربية السعودية</option>
                                                <option value="US" <?= (isset($_POST['country']) && $_POST['country'] == 'US') ? 'selected' : '' ?>>الولايات المتحدة</option>
                                                <option value="KR" <?= (isset($_POST['country']) && $_POST['country'] == 'KR') ? 'selected' : '' ?>>كوريا الجنوبية</option>
                                                <option value="CN" <?= (isset($_POST['country']) && $_POST['country'] == 'CN') ? 'selected' : '' ?>>الصين</option>
                                                <option value="JP" <?= (isset($_POST['country']) && $_POST['country'] == 'JP') ? 'selected' : '' ?>>اليابان</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="brand-website">الموقع الإلكتروني</label>
                                            <input type="url" id="brand-website" name="website" class="form-control"
                                                placeholder="https://example.com"
                                                value="<?= isset($_POST['website']) ? htmlspecialchars($_POST['website']) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="brand-status">حالة العلامة</label>
                                            <select id="brand-status" name="status" class="form-control">
                                                <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : 'selected' ?>>نشط</option>
                                                <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : '' ?>>غير نشط</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="brand-description">وصف العلامة التجارية</label>
                                        <textarea id="brand-description" name="description" class="form-control" rows="4"
                                            placeholder="أدخل وصفاً للعلامة التجارية"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary" name="add_brand">
                                            <i class="fas fa-plus"></i> إضافة العلامة
                                        </button>
                                        <button type="button" class="btn btn-danger" id="cancel-add-brand">
                                            <i class="fas fa-times"></i> إلغاء
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- قسم تعديل علامة -->
                            <?php if ($edit_brand): ?>
                                <div class="content-section <?= $current_view == 'edit' ? 'active' : '' ?>" id="edit-brand-section">
                                    <h3>تعديل علامة تجارية</h3>

                                    <div class="logo-upload">
                                        <div class="logo-preview" id="edit-brand-logo-preview-container">
                                            <?php if ($edit_brand['logo']): ?>
                                                <img src="<?= $edit_brand['logo'] ?>" alt="شعار <?= htmlspecialchars($edit_brand['name']) ?>" id="edit-brand-logo-preview" style="display: block;">
                                                <span id="edit-no-logo" style="color: #999; display: none;">
                                                    <i class="fas fa-image" style="font-size: 24px; margin-bottom: 5px; display: block;"></i>
                                                    لا يوجد شعار
                                                </span>
                                            <?php else: ?>
                                                <img src="" alt="شعار العلامة" id="edit-brand-logo-preview" style="display: none;">
                                                <span id="edit-no-logo" style="color: #999; display: block;">
                                                    <i class="fas fa-image" style="font-size: 24px; margin-bottom: 5px; display: block;"></i>
                                                    لا يوجد شعار
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <input type="file" id="edit-brand-logo-upload" name="logo" accept="image/*" style="display: none;">
                                            <label for="edit-brand-logo-upload" class="upload-btn">
                                                <i class="fas fa-upload"></i> تغيير الشعار
                                            </label>
                                            <p style="font-size: 12px; color: #666; margin-top: 5px;">
                                                <i class="fas fa-info-circle"></i>
                                                اترك الحقل فارغاً للحفاظ على الشعار الحالي
                                            </p>
                                        </div>
                                    </div>

                                    <form method="POST" enctype="multipart/form-data" id="edit-brand-form">
                                        <input type="hidden" name="id" value="<?= $edit_brand['id'] ?>">
                                        <input type="hidden" name="update_brand" value="1">

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="edit-brand-name">اسم العلامة التجارية <span style="color: #ff4757;">*</span></label>
                                                <input type="text" id="edit-brand-name" name="name" class="form-control"
                                                    value="<?= htmlspecialchars($edit_brand['name']) ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit-brand-country">البلد</label>
                                                <select id="edit-brand-country" name="country" class="form-control">
                                                    <option value="">اختر البلد</option>
                                                    <option value="SA" <?= $edit_brand['country'] == 'SA' ? 'selected' : '' ?>>المملكة العربية السعودية</option>
                                                    <option value="US" <?= $edit_brand['country'] == 'US' ? 'selected' : '' ?>>الولايات المتحدة</option>
                                                    <option value="KR" <?= $edit_brand['country'] == 'KR' ? 'selected' : '' ?>>كوريا الجنوبية</option>
                                                    <option value="CN" <?= $edit_brand['country'] == 'CN' ? 'selected' : '' ?>>الصين</option>
                                                    <option value="JP" <?= $edit_brand['country'] == 'JP' ? 'selected' : '' ?>>اليابان</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="edit-brand-website">الموقع الإلكتروني</label>
                                                <input type="url" id="edit-brand-website" name="website" class="form-control"
                                                    value="<?= htmlspecialchars($edit_brand['website']) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="edit-brand-status">حالة العلامة</label>
                                                <select id="edit-brand-status" name="status" class="form-control">
                                                    <option value="active" <?= $edit_brand['status'] == 'active' ? 'selected' : '' ?>>نشط</option>
                                                    <option value="inactive" <?= $edit_brand['status'] == 'inactive' ? 'selected' : '' ?>>غير نشط</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-brand-description">وصف العلامة التجارية</label>
                                            <textarea id="edit-brand-description" name="description" class="form-control" rows="4"><?= htmlspecialchars($edit_brand['description']) ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary" name="update_brand">
                                                <i class="fas fa-save"></i> حفظ التعديلات
                                            </button>
                                            <a href="?view=list" class="btn btn-danger">
                                                <i class="fas fa-times"></i> إلغاء
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        // تبديل بين قسمي العرض والإضافة
        document.getElementById('show-brands-btn').addEventListener('click', function() {
            showSection('brands-list-section');
            window.history.pushState({}, '', '?view=list');
        });

        document.getElementById('add-brand-btn').addEventListener('click', function() {
            showSection('add-brand-section');
            window.history.pushState({}, '', '?view=add');
        });

        if (document.getElementById('add-brand-from-empty')) {
            document.getElementById('add-brand-from-empty').addEventListener('click', function() {
                showSection('add-brand-section');
                window.history.pushState({}, '', '?view=add');
            });
        }

        document.getElementById('cancel-add-brand').addEventListener('click', function() {
            showSection('brands-list-section');
            document.getElementById('add-brand-form').reset();
            resetLogoPreview('brand-logo-preview', 'no-logo');
            window.history.pushState({}, '', '?view=list');
        });

        function showSection(sectionId) {
            // إخفاء جميع الأقسام
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // إظهار القسم المطلوب
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('active');
                // إغلاق أي رسائل تنبيه عند تغيير القسم
                closeAllAlerts();
            }
        }

        // معاينة الشعار عند التحميل
        document.getElementById('brand-logo-upload').addEventListener('change', function(e) {
            handleLogoPreview(e, 'brand-logo-preview', 'no-logo');
        });

        if (document.getElementById('edit-brand-logo-upload')) {
            document.getElementById('edit-brand-logo-upload').addEventListener('change', function(e) {
                handleLogoPreview(e, 'edit-brand-logo-preview', 'edit-no-logo');
            });
        }

        function handleLogoPreview(event, previewId, noLogoId) {
            const file = event.target.files[0];
            const preview = document.getElementById(previewId);
            const noLogo = document.getElementById(noLogoId);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (noLogo) noLogo.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        function resetLogoPreview(previewId, noLogoId) {
            const preview = document.getElementById(previewId);
            const noLogo = document.getElementById(noLogoId);

            if (preview) preview.style.display = 'none';
            if (noLogo) noLogo.style.display = 'block';
            if (preview) preview.src = '';
        }

        // إغلاق رسائل التنبيه
        document.querySelectorAll('.close-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert').style.display = 'none';
            });
        });

        function closeAllAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }

        // زر القائمة للجوال
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            const actions = document.querySelector('.brands-actions');
            actions.style.display = actions.style.display === 'none' ? 'block' : 'none';
        });

        // إظهار القسم المناسب عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const view = urlParams.get('view');

            if (view === 'add') {
                showSection('add-brand-section');
            } else if (view === 'edit') {
                showSection('edit-brand-section');
            } else {
                showSection('brands-list-section');
            }

            // إضافة تأثير للأزرار عند الضغط
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('mousedown', function() {
                    this.style.transform = 'scale(0.98)';
                });
                btn.addEventListener('mouseup', function() {
                    this.style.transform = '';
                });
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });

            // تحسين تجربة النماذج
            document.querySelectorAll('.form-control').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });

            // التحقق من صحة النماذج قبل الإرسال
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    // التحقق من الحقول المطلوبة
                    const requiredFields = this.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = '#ff4757';
                            isValid = false;
                            
                            // إضافة رسالة خطأ
                            if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                                const errorMsg = document.createElement('div');
                                errorMsg.className = 'error-message';
                                errorMsg.style.color = '#ff4757';
                                errorMsg.style.fontSize = '12px';
                                errorMsg.style.marginTop = '5px';
                                errorMsg.innerHTML = 'هذا الحقل مطلوب';
                                field.parentNode.appendChild(errorMsg);
                            }
                        } else {
                            field.style.borderColor = '';
                            const errorMsg = field.parentNode.querySelector('.error-message');
                            if (errorMsg) errorMsg.remove();
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        showMessage('يرجى ملء جميع الحقول المطلوبة', 'error');
                    }
                });
            });
        });

        // إظهار رسالة مؤقتة
        function showMessage(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <div>
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    <span style="margin-right: 10px;">${message}</span>
                </div>
                <button class="close-alert">&times;</button>
            `;
            
            const pageTitle = document.querySelector('.page-title');
            if (pageTitle.nextElementSibling && pageTitle.nextElementSibling.classList.contains('alert')) {
                pageTitle.nextElementSibling.remove();
            }
            
            pageTitle.parentNode.insertBefore(alertDiv, pageTitle.nextSibling);
            
            // إضافة حدث إغلاق للرسالة الجديدة
            alertDiv.querySelector('.close-alert').addEventListener('click', function() {
                alertDiv.style.display = 'none';
            });
            
            // إخفاء الرسالة تلقائياً بعد 5 ثوانٍ
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.opacity = '0';
                    alertDiv.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 500);
                }
            }, 5000);
        }

        // تحسين تجربة البحث
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.trim().length > 2 || this.value.trim() === '') {
                        this.form.submit();
                    }
                }, 500);
            });
        }

        // تحسين عرض الصور
        document.querySelectorAll('.brand-logo').forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="%236c757d"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5-7l-3 3.72L9 13l-3 4h12l-4-5z"/></svg>';
                this.style.objectFit = 'contain';
                this.style.padding = '10px';
            });
        });

        // تحسين الوصول للوحة المفاتيح
        document.addEventListener('keydown', function(e) {
            // زر Escape لإغلاق النماذج
            if (e.key === 'Escape') {
                const activeSection = document.querySelector('.content-section.active');
                if (activeSection && activeSection.id !== 'brands-list-section') {
                    showSection('brands-list-section');
                    window.history.pushState({}, '', '?view=list');
                }
            }
            
            // Ctrl + Enter لإرسال النموذج
            if (e.ctrlKey && e.key === 'Enter') {
                const activeForm = document.querySelector('.content-section.active form');
                if (activeForm) {
                    activeForm.submit();
                }
            }
        });

        // تحسين تجربة الهواتف المحمولة
        if ('ontouchstart' in window) {
            document.querySelectorAll('.action-btn, .btn, .menu-item').forEach(element => {
                element.style.minHeight = '44px';
                element.style.minWidth = '44px';
                element.style.display = 'flex';
                element.style.alignItems = 'center';
                element.style.justifyContent = 'center';
            });
        }

        // تحميل ديناميكي للصور
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        }, observerOptions);

        // ملاحظة: يمكنك إضافة data-src للصور بدلاً من src للمساعدة في التحميل البطيء
    </script>
</body>
</html>