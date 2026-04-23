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

// معالجة العمليات
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /*==============================
       إضافة فئة جديدة
    ==============================*/
    if (isset($_POST['add'])) {

        $name = $conn->real_escape_string($_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";

        // معالجة رفع الصورة
        $image = NULL;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($_FILES['image']['type'], $allowed_types)) {

                $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $image);
            }
        }

        // إذا لا توجد صورة → استخدم NULL
        $image_value = $image ? "'$image'" : "NULL";

        // تنفيذ الاستعلام
        $sql = "INSERT INTO categories (name, parent_id, image) 
                VALUES ('$name', $parent_id, $image_value)";

        if ($conn->query($sql)) {
            $message = "تم إضافة الفئة بنجاح!";
        } else {
            $message = "خطأ في الإضافة: " . $conn->error;
        }
    }

    /*==============================
        تعديل فئة
    ==============================*/ elseif (isset($_POST['update'])) {

        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";

        // تعديل الصورة
        $image_sql = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($_FILES['image']['type'], $allowed_types)) {

                // حذف القديمة
                $old_image = $conn->query("SELECT image FROM categories WHERE id=$id")->fetch_assoc()['image'];

                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }

                // رفع الجديدة
                $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $image);

                $image_sql = ", image='$image'";
            }
        }

        // تنفيذ التعديل
        $sql = "UPDATE categories 
                SET name='$name', parent_id=$parent_id $image_sql 
                WHERE id=$id";

        if ($conn->query($sql)) {
            $message = "تم تعديل الفئة بنجاح!";
        } else {
            $message = "خطأ في التعديل: " . $conn->error;
        }
    }

    /*==============================
        حذف فئة
    ==============================*/ elseif (isset($_POST['delete'])) {

        $id = (int)$_POST['id'];

        // حذف الصورة
        $image = $conn->query("SELECT image FROM categories WHERE id=$id")->fetch_assoc()['image'];
        if ($image && file_exists($image)) {
            unlink($image);
        }

        $sql = "DELETE FROM categories WHERE id=$id";

        if ($conn->query($sql)) {
            $message = "تم حذف الفئة بنجاح!";
        } else {
            $message = "خطأ في الحذف: " . $conn->error;
        }
    }
}

// جلب الفئات الرئيسية
$parent_categories = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL");

// جلب كل الفئات
$categories_result = $conn->query("
    SELECT c.*, p.name AS parent_name 
    FROM categories c
    LEFT JOIN categories p ON c.parent_id = p.id
    ORDER BY 
        COALESCE(c.parent_id, c.id),
        c.parent_id IS NOT NULL,
        c.id
");
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفئات - Storthory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* التصميم الرئيسي */
        .dashboard {
            display: flex;
            min-height: 100vh;
            position: relative;
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

        .page-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            background-color: white;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: var(--radius);
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-secondary {
            background-color: white;
            color: var(--dark);
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: var(--light);
            border-color: var(--primary);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 15px;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-info {
            flex: 1;
        }

        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .card-1 .stat-icon {
            background: linear-gradient(135deg, #6C63FF, #8A84FF);
            color: white;
        }

        .card-2 .stat-icon {
            background: linear-gradient(135deg, #FF6584, #FF9A76);
            color: white;
        }

        .card-3 .stat-icon {
            background: linear-gradient(135deg, #36D1DC, #4ECDC4);
            color: white;
        }

        /* عرض الفئات */
        .categories-container {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
        }

        .categories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .categories-header h3 {
            font-size: 18px;
            color: var(--dark);
        }

        .category-list {
            display: grid;
            gap: 15px;
        }

        .category-card {
            background: var(--light);
            border-radius: var(--radius);
            padding: 15px;
            transition: var(--transition);
            border: 1px solid #e2e8f0;
        }

        .category-card.main-category {
            background: white;
            border-left: 4px solid var(--primary);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .category-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
        }

        .category-details h4 {
            font-size: 16px;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .category-details p {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 5px;
        }

        .category-meta {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }

        .category-id {
            font-size: 12px;
            color: var(--gray);
            background-color: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .category-date {
            font-size: 12px;
            color: var(--gray);
        }

        .category-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #e2e8f0;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background-color: var(--light);
        }

        .action-btn.edit:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        .action-btn.delete:hover {
            color: var(--secondary);
            border-color: var(--secondary);
        }

        .action-btn.add-sub:hover {
            color: var(--success);
            border-color: var(--success);
        }

        .subcategories {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #e2e8f0;
            display: none;
        }

        .subcategories.show {
            display: block;
        }

        .subcategory-list {
            display: grid;
            gap: 10px;
        }

        .subcategory-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .subcategory-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .subcategory-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 12px;
        }

        .subcategory-details h5 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .subcategory-details p {
            font-size: 12px;
            color: var(--gray);
        }

        .toggle-subcategories {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
        }

        /* النوافذ المنبثقة - الإصلاح النهائي */
        .modal-overlay {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
            backdrop-filter: blur(3px);
        }

        .modal-overlay.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        .modal {
            background-color: white;
            border-radius: var(--radius);
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            z-index: 10000;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
            opacity: 1;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 18px;
            color: var(--dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--gray);
            cursor: pointer;
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

        .form-group {
            margin-bottom: 20px;
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
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #e2e8f0;
        }

        /* تحسينات للاستجابة على الأجهزة المحمولة */
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }

            .page-content {
                padding: 15px;
            }

            .page-actions {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }

            .category-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .category-actions {
                align-self: flex-end;
            }

            .modal-overlay {
                padding: 10px;
                align-items: flex-end;
            }

            .modal {
                max-height: 90vh;
                border-radius: var(--radius) var(--radius) 0 0;
            }

            .modal-overlay.active .modal {
                transform: translateY(0);
            }
        }

        /* التأكد من أن السايدبار والهيدر لديهم z-index أقل */
        header {
            z-index: 100 !important;
        }

        .sidebar {
            z-index: 100 !important;
        }

        .main-content {
            z-index: 1 !important;
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* تصميم البنر */
        .banner {
            background: linear-gradient(135deg, #2c3e50 0%, #df4803ff 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .banner-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .banner h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .banner p {
            margin: 10px 0 0;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .banner-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .stat-number {
            display: block;
            font-size: 1.8em;
            font-weight: bold;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.8;
        }

        /* بقية التصميمات */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            padding: 0;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .nav-tabs {
            background: #f8f9fa;
            padding: 0;
            margin: 0;
            list-style: none;
            display: flex;
            border-bottom: 1px solid #dee2e6;
        }

        .nav-tabs li {
            margin: 0;
        }

        .nav-tabs a {
            display: block;
            padding: 15px 25px;
            text-decoration: none;
            color: #495057;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs a:hover,
        .nav-tabs a.active {
            color: #007bff;
            border-bottom-color: #000000ff;
            background: white;
        }

        .content-section {
            padding: 30px;
        }

        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            font-weight: 500;
        }

        .form-section,
        .categories-section {
            margin: 25px 0;
            padding: 25px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        button {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 15px;
            text-align: right;
        }

        th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            color: #495057;
        }

        .category-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e9ecef;
        }

        .main-category {
            background-color: #e8f5e8;
            font-weight: bold;
        }

        .sub-category {
            background-color: #f8f9fa;
            padding-right: 40px !important;
        }

        .actions {
            white-space: nowrap;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                margin: 15px;
                border-radius: 10px;
            }

            .banner h1 {
                font-size: 2em;
            }

            .banner-stats {
                gap: 15px;
            }

            .stat-item {
                padding: 10px 15px;
            }

            .nav-tabs {
                flex-direction: column;
            }

            .content-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
             <?php include 'header.php'; ?>
            <!-- البنر الإعلاني -->
            <div class="banner">
                <div class="banner-content">
                    <h1>🛍️ نظام إدارة الفئات المتقدم</h1>
                    <p>أدِر فئات متجرك بكل سهولة واحترافية</p>

                    <div class="banner-stats">
                        <?php
                        // إحصائيات الفئات
                        $total_categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
                        $main_categories = $conn->query("SELECT COUNT(*) as total FROM categories WHERE parent_id IS NULL")->fetch_assoc()['total'];
                        $sub_categories = $conn->query("SELECT COUNT(*) as total FROM categories WHERE parent_id IS NOT NULL")->fetch_assoc()['total'];
                        ?>

                        <div class="stat-item">
                            <span class="stat-number"><?= $total_categories ?></span>
                            <span class="stat-label">إجمالي الفئات</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $main_categories ?></span>
                            <span class="stat-label">فئة رئيسية</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $sub_categories ?></span>
                            <span class="stat-label">فئة فرعية</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">🎯</span>
                            <span class="stat-label">سهولة الإدارة</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <!-- شريط التنقل -->
                <ul class="nav-tabs">
                    <li><a href="#categories" class="active" onclick="showSection('categories')">📁 إدارة الفئات</a></li>
                    <li><a href="#stats" onclick="showSection('stats')">📊 الإحصائيات</a></li>
                    <li><a href="#help" onclick="showSection('help')">❓ المساعدة</a></li>
                </ul>

                <!-- قسم إدارة الفئات -->
                <div id="categories" class="content-section">
                    <?php if ($message): ?>
                        <div class="message">✅ <?= $message ?></div>
                    <?php endif; ?>

                    <!-- نموذج إضافة/تعديل الفئات -->
                    <div class="form-section">
                        <h3 style="color: #2c3e50; margin-bottom: 20px;" id="form-title">
                            <span id="form-icon">➕</span>
                            <span id="form-text">إضافة فئة جديدة</span>
                        </h3>
                        <form method="POST" enctype="multipart/form-data" id="categoryForm">
                            <input type="hidden" name="id" id="editId">

                            <div class="form-group">
                                <label for="name">📝 اسم الفئة:</label>
                                <input type="text" name="name" id="editName" required placeholder="أدخل اسم الفئة هنا...">
                            </div>

                            <div class="form-group">
                                <label for="parent_id">🏷️ الفئة الرئيسية:</label>
                                <select name="parent_id" id="editParentId">
                                    <option value="">-- اختر فئة رئيسية --</option>
                                    <?php
                                    $parent_categories = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL");
                                    while ($parent = $parent_categories->fetch_assoc()):
                                    ?>
                                        <option value="<?= $parent['id'] ?>"><?= $parent['name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="image">🖼️ صورة الفئة:</label>
                                <input type="file" name="image" id="editImage" accept="image/*">
                                <small style="color: #6c757d; display: block; margin-top: 5px;">
                                    يُسمح بصور JPEG, PNG, GIF - الحد الأقصى 2MB
                                </small>
                                <div id="currentImage" style="margin-top: 10px;"></div>
                            </div>

                            <div style="margin-top: 25px;">
                                <button type="submit" name="add" id="addBtn">➕ إضافة فئة</button>
                                <button type="submit" name="update" id="updateBtn" class="hidden">✏️ تحديث الفئة</button>
                                <button type="button" onclick="cancelEdit()" id="cancelBtn" class="hidden btn-cancel">❌ إلغاء</button>
                            </div>
                        </form>
                    </div>

                    <!-- قائمة الفئات -->
                    <div class="categories-section">
                        <h3 style="color: #2c3e50; margin-bottom: 20px;">📋 قائمة الفئات</h3>
                        <?php
                        $categories_result = $conn->query("
                            SELECT c.*, p.name as parent_name 
                            FROM categories c 
                            LEFT JOIN categories p ON c.parent_id = p.id 
                            ORDER BY 
                                COALESCE(c.parent_id, c.id),
                                c.parent_id IS NOT NULL,
                                c.id
                        ");


                        if ($categories_result->num_rows > 0): ?>
                            <div style="overflow-x: auto; margin-top: 20px;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>🖼️ الصورة</th>
                                        <th>📝 الاسم</th>
                                        <th>📂 النوع</th>
                                        <th>📅 التاريخ</th>
                                        <th>⚙️ الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                                        <tr class="<?= $category['parent_id'] ? 'sub-category' : 'main-category' ?>">
                                            <td><strong><?= $category['id'] ?></strong></td>
                                            <td>
                                                <?php if ($category['image']): ?>
                                                    <img src="../<?= $category['image'] ?>" alt="<?= $category['name'] ?>" class="category-image">
                                                <?php else: ?>
                                                    <div class="category-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                                                        <?= mb_substr($category['name'], 0, 1) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($category['parent_id']): ?>
                                                    <span style="color: #28a745;">فرعية ← <?= htmlspecialchars($category['parent_name']) ?></span>
                                                <?php else: ?>
                                                    <span style="color: #007bff; font-weight: bold;">رئيسية</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($category['created_at'])) ?></td>
                                            <td class="actions">
                                                <button onclick="editCategory(
                                            <?= $category['id'] ?>, 
                                            '<?= htmlspecialchars($category['name']) ?>', 
                                            <?= $category['parent_id'] ? $category['parent_id'] : 'null' ?>,
                                            '<?= $category['image'] ?>'
                                        )">✏️ تعديل</button>

                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                    <button type="submit" name="delete" class="btn-delete"
                                                        onclick="return confirm('⚠️ هل أنت متأكد من حذف الفئة؟ هذا الإجراء لا يمكن التراجع عنه.')">🗑️ حذف</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px; color: #6c757d;">
                                <h3>📭 لا توجد فئات مضافة حالياً</h3>
                                <p>ابدأ بإضافة أول فئة لك باستخدام النموذج أعلاه</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- قسم الإحصائيات -->
                <div id="stats" class="content-section hidden">
                    <h3 style="color: #2c3e50; margin-bottom: 20px;">📊 إحصائيات النظام</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 25px; border-radius: 10px; text-align: center;">
                            <h4 style="margin: 0 0 10px;">إجمالي الفئات</h4>
                            <div style="font-size: 2.5em; font-weight: bold;"><?= $total_categories ?></div>
                        </div>
                        <div style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 25px; border-radius: 10px; text-align: center;">
                            <h4 style="margin: 0 0 10px;">الفئات الرئيسية</h4>
                            <div style="font-size: 2.5em; font-weight: bold;"><?= $main_categories ?></div>
                        </div>
                        <div style="background: linear-gradient(135deg, #ffc107, #e0a800); color: white; padding: 25px; border-radius: 10px; text-align: center;">
                            <h4 style="margin: 0 0 10px;">الفئات الفرعية</h4>
                            <div style="font-size: 2.5em; font-weight: bold;"><?= $sub_categories ?></div>
                        </div>
                    </div>
                </div>

                <!-- قسم المساعدة -->
                <div id="help" class="content-section hidden">
                    <h3 style="color: #2c3e50; margin-bottom: 20px;">❓ دليل استخدام النظام</h3>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-right: 4px solid #007bff;">
                        <h4>🎯 كيفية استخدام النظام:</h4>
                        <ul style="line-height: 2;">
                            <li><strong>إضافة فئة جديدة:</strong> املأ النموذج واضغط على "إضافة فئة"</li>
                            <li><strong>إنشاء فئة فرعية:</strong> اختر فئة رئيسية من القائمة المنسدلة</li>
                            <li><strong>تعديل فئة:</strong> انقر على زر "تعديل" بجانب الفئة المطلوبة</li>
                            <li><strong>حذف فئة:</strong> انقر على زر "حذف" مع التأكيد</li>
                            <li><strong>رفع صورة:</strong> اختر صورة من جهازك لدعم الفئة بصرياً</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function editCategory(id, name, parentId, image) {
                document.getElementById('form-title').innerHTML = '✏️ <span id="form-text">تعديل الفئة</span>';
                document.getElementById('editId').value = id;
                document.getElementById('editName').value = name;
                document.getElementById('editParentId').value = parentId || '';

                // إظهار الصورة الحالية إذا كانت موجودة
                const currentImageDiv = document.getElementById('currentImage');
                if (image) {
                    currentImageDiv.innerHTML = `
            <div style="background: white; padding: 15px; border-radius: 8px; border: 2px solid #e9ecef;">
                <p style="margin: 0 0 10px; font-weight: bold;">🖼️ الصورة الحالية:</p>
                <img src="${image}" alt="Current" style="max-width: 150px; border-radius: 8px; border: 2px solid #007bff;">
            </div>`;
                } else {
                    currentImageDiv.innerHTML = '';
                }

                // تبديل الأزرار
                document.getElementById('addBtn').classList.add('hidden');
                document.getElementById('updateBtn').classList.remove('hidden');
                document.getElementById('cancelBtn').classList.remove('hidden');

                // التمرير للنموذج
                document.querySelector('.form-section').scrollIntoView({
                    behavior: 'smooth'
                });
            }

            function cancelEdit() {
                document.getElementById('form-title').innerHTML = '➕ <span id="form-text">إضافة فئة جديدة</span>';
                document.getElementById('categoryForm').reset();
                document.getElementById('currentImage').innerHTML = '';

                // تبديل الأزرار
                document.getElementById('addBtn').classList.remove('hidden');
                document.getElementById('updateBtn').classList.add('hidden');
                document.getElementById('cancelBtn').classList.add('hidden');
            }

            function showSection(sectionId) {
                // إخفاء جميع الأقسام
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.add('hidden');
                });

                // إظهار القسم المطلوب
                document.getElementById(sectionId).classList.remove('hidden');

                // تحديث التبويبات النشطة
                document.querySelectorAll('.nav-tabs a').forEach(tab => {
                    tab.classList.remove('active');
                });
                event.target.classList.add('active');
            }

            // التأكد من إظهار قسم الفئات عند التحميل
            document.addEventListener('DOMContentLoaded', function() {
                showSection('categories');
            });
        </script>

</body>

</html>