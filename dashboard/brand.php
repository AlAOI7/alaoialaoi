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

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// إجمالي العلامات للبحث
$total_result = $conn->query("SELECT COUNT(*) as total FROM brands $where_clause");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// جلب العلامات
$brands_result = $conn->query("
    SELECT b.*, 
           (SELECT COUNT(*) FROM products WHERE brand_id = b.id) as products_count
    FROM brands b
    $where_clause
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

// إحصائيات
$total_brands = $conn->query("SELECT COUNT(*) as total FROM brands")->fetch_assoc()['total'];
$active_brands = $conn->query("SELECT COUNT(*) as total FROM brands WHERE status='active'")->fetch_assoc()['total'];
$inactive_brands = $conn->query("SELECT COUNT(*) as total FROM brands WHERE status='inactive'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العلامات التجارية - Storthory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========== التصميم الأصلي (نفس ما كان موجوداً، مع إضافة كلاسات لنافذة الإضافة) ========== */
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

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
        }

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
            cursor: pointer;
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

        .hidden {
            display: none;
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

        .page-actions {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .table-responsive-scroll {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }

        .table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            z-index: 10;
            padding: 15px;
            border-bottom: 2px solid #dee2e6;
        }

        .table td, .table th {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            text-align: right;
            vertical-align: middle;
        }

        .category-image {
            width: 50px;
            height: 50px;
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
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
            background: white;
            transition: 0.2s;
        }

        .pagination a:hover {
            background: #e9ecef;
        }

        .pagination .active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* ========== النوافذ المنبثقة العامة ========== */
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
            display: block;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
            opacity: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            font-weight: 600;
            color: #495057;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* ========== كلاسات خاصة بنافذة إضافة الفئة فقط ========== */
        .add-category-modal .modal {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border: 1px solid rgba(108, 99, 255, 0.2);
        }

        .add-category-modal .modal-header {
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .add-category-modal .modal-header h3 {
            color: white;
        }

        .add-category-modal .modal-header .close-modal {
            color: white;
        }

        .add-category-modal .modal-header .close-modal:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .add-category-modal .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 2px 10px rgba(108, 99, 255, 0.3);
        }

        .add-category-modal .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.4);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-right: 0;
            }
            .table-responsive-scroll {
                max-height: 400px;
            }
        }
    </style>
</head>
<body>
<div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        <div class="banner">
            <div class="banner-content">
                <h1>🏢 نظام إدارة العلامات التجارية</h1>
                <p>أدِر العلامات التجارية لمتجرك بكل سهولة واحترافية</p>
                <div class="banner-stats">
                    <div class="stat-item"><span class="stat-number"><?= $total_brands ?></span><span class="stat-label">إجمالي العلامات</span></div>
                    <div class="stat-item"><span class="stat-number"><?= $active_brands ?></span><span class="stat-label">علامة نشطة</span></div>
                    <div class="stat-item"><span class="stat-number"><?= $inactive_brands ?></span><span class="stat-label">علامة غير نشطة</span></div>
                    <div class="stat-item"><span class="stat-number">🎯</span><span class="stat-label">سهولة الإدارة</span></div>
                </div>
            </div>
        </div>

        <div class="container">
            <ul class="nav-tabs">
                <li><a onclick="showSection('brands')" class="active">🏢 إدارة العلامات التجارية</a></li>
                <li><a onclick="showSection('stats')">📊 الإحصائيات</a></li>
                <li><a onclick="showSection('help')">❓ المساعدة</a></li>
            </ul>

            <!-- قسم إدارة العلامات التجارية -->
            <div id="brands" class="content-section">
                <?php if ($message): ?>
                    <div class="message">✅ <?= $message ?></div>
                <?php endif; ?>

                <div class="page-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="showAddModal()">
                        <i class="fas fa-plus"></i> ➕ إضافة علامة تجارية
                    </button>
                    
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="text" name="search" class="form-control" placeholder="بحث في العلامات..." value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
                        <button type="submit" class="btn btn-primary">بحث</button>
                        <?php if ($search): ?>
                            <a href="brand.php" class="btn btn-secondary">إلغاء</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="categories-section">
                    <h3 style="color: #2c3e50; margin-bottom: 20px;">📋 قائمة العلامات التجارية</h3>
                    <?php if ($brands_result->num_rows > 0): ?>
                        <div class="table-responsive-scroll">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>🖼️ الشعار</th>
                                        <th>📝 الاسم</th>
                                        <th>🌍 البلد</th>
                                        <th>📦 المنتجات</th>
                                        <th>🔄 الحالة</th>
                                        <th>⚙️ الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($brand = $brands_result->fetch_assoc()): ?>
                                        <tr class="main-category">
                                            <td><strong><?= $brand['id'] ?></strong></td>
                                            <td>
                                                <?php if ($brand['logo']): ?>
                                                    <img src="<?= $brand['logo'] ?>" alt="<?= $brand['name'] ?>" class="category-image">
                                                <?php else: ?>
                                                    <div style="width:50px; height:50px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold;">
                                                        <?= mb_substr($brand['name'], 0, 1) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($brand['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($brand['country']) ?></td>
                                            <td><span class="badge bg-info text-dark" style="font-size:14px; padding:6px 10px;"><?= $brand['products_count'] ?> منتج</span></td>
                                            <td>
                                                <?php if ($brand['status'] == 'active'): ?>
                                                    <span style="color: #28a745; font-weight: bold;">نشط</span>
                                                <?php else: ?>
                                                    <span style="color: #dc3545; font-weight: bold;">غير نشط</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions">
                                                <button onclick='editBrand(<?= json_encode($brand) ?>)' class="btn-primary" style="padding:6px 12px;">✏️ تعديل</button>
                                                <button onclick='showDeleteModal(<?= $brand['id'] ?>, "<?= htmlspecialchars($brand['name']) ?>")' class="btn-delete" style="padding:6px 12px;">🗑️ حذف</button>
                                                <?php if ($brand['website']): ?>
                                                    <a href="<?= htmlspecialchars($brand['website']) ?>" target="_blank" class="btn btn-secondary" style="padding:6px 12px; font-size: 14px;"><i class="fas fa-external-link-alt"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">&laquo; السابق</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">التالي &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 50px;">
                            <h3 style="color: #6c757d;">📭 لا توجد علامات تجارية مضافة حالياً</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- قسم الإحصائيات -->
            <div id="stats" class="content-section hidden">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">📊 إحصائيات العلامات التجارية</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 25px; border-radius: 10px; text-align: center;">
                        <h4 style="margin: 0 0 10px;">إجمالي العلامات</h4>
                        <div style="font-size: 2.5em; font-weight: bold;"><?= $total_brands ?></div>
                    </div>
                    <div style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 25px; border-radius: 10px; text-align: center;">
                        <h4 style="margin: 0 0 10px;">العلامات النشطة</h4>
                        <div style="font-size: 2.5em; font-weight: bold;"><?= $active_brands ?></div>
                    </div>
                    <div style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 25px; border-radius: 10px; text-align: center;">
                        <h4 style="margin: 0 0 10px;">العلامات غير النشطة</h4>
                        <div style="font-size: 2.5em; font-weight: bold;"><?= $inactive_brands ?></div>
                    </div>
                </div>
            </div>

            <!-- قسم المساعدة -->
            <div id="help" class="content-section hidden">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">❓ دليل الاستخدام</h3>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-right: 4px solid #007bff;">
                    <h4>🎯 كيفية الإدارة:</h4>
                    <ul style="line-height: 2;">
                        <li><strong>إضافة علامة:</strong> انقر على زر "إضافة علامة تجارية" لفتح النافذة.</li>
                        <li><strong>تعديل العلامة:</strong> يمكنك النقر على أيقونة التعديل بجوار أي علامة لتعديل بياناتها.</li>
                        <li><strong>البحث:</strong> استخدم شريط البحث للعثور على علامة محددة بسرعة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- نافذة إضافة علامة تجارية -->
<div id="addBrandModal" class="modal-overlay add-category-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ إضافة علامة تجارية</h3>
            <button class="close-modal" onclick="closeModal('addBrandModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" action="">
                <div class="form-group">
                    <label>📝 اسم العلامة التجارية</label>
                    <input type="text" name="name" class="form-control" required placeholder="أدخل اسم العلامة...">
                </div>
                <div class="form-group">
                    <label>🌍 البلد</label>
                    <input type="text" name="country" class="form-control" placeholder="مثل: SA, US...">
                </div>
                <div class="form-group">
                    <label>🌐 الموقع الإلكتروني</label>
                    <input type="url" name="website" class="form-control" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>🔄 الحالة</label>
                    <select name="status" class="form-select">
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>📄 الوصف</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>🖼️ الشعار (اختياري)</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addBrandModal')">إلغاء</button>
                    <button type="submit" name="add_brand" class="btn-primary">➕ إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة تعديل العلامة -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ تعديل العلامة التجارية</h3>
            <button class="close-modal" onclick="closeModal('editModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" action="">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="update_brand" value="1">
                <div class="form-group">
                    <label>📝 اسم العلامة</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>🌍 البلد</label>
                    <input type="text" name="country" id="edit_country" class="form-control">
                </div>
                <div class="form-group">
                    <label>🌐 الموقع الإلكتروني</label>
                    <input type="url" name="website" id="edit_website" class="form-control">
                </div>
                <div class="form-group">
                    <label>🔄 الحالة</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>📄 الوصف</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>🖼️ شعار جديد (اختياري)</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editModal')">إلغاء</button>
                    <button type="submit" name="update_brand" class="btn-primary">💾 حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة الحذف -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>🗑️ تأكيد الحذف</h3>
            <button class="close-modal" onclick="closeModal('deleteModal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 48px; color: #dc3545; margin-bottom: 15px;">⚠️</div>
                <h4>هل أنت متأكد من حذف العلامة؟</h4>
                <p style="color: #6c757d; margin: 15px 0;">سيتم حذف "<strong id="deleteName"></strong>" نهائياً</p>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="deleteId">
                <input type="hidden" name="delete_brand" value="1">
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">إلغاء</button>
                    <button type="submit" class="btn-delete">🗑️ حذف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showSection(sectionId) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.add('hidden');
        });
        document.getElementById(sectionId).classList.remove('hidden');
        document.querySelectorAll('.nav-tabs a').forEach(tab => {
            tab.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    function showAddModal() {
        document.getElementById('addBrandModal').classList.add('active');
    }

    function showDeleteModal(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteModal').classList.add('active');
    }

    function editBrand(brand) {
        document.getElementById('edit_id').value = brand.id;
        document.getElementById('edit_name').value = brand.name;
        document.getElementById('edit_country').value = brand.country || '';
        document.getElementById('edit_website').value = brand.website || '';
        document.getElementById('edit_status').value = brand.status || 'active';
        document.getElementById('edit_description').value = brand.description || '';
        document.getElementById('editModal').classList.add('active');
    }

    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        showSection('brands');
    });
</script>
</body>
</html>
