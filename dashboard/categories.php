<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// إنشاء مجلد الصور إذا لم يكن موجوداً
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}

// معالجة العمليات
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // إضافة فئة جديدة
    if (isset($_POST['add'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";

        $check_name = $conn->query("SELECT id FROM categories WHERE name='$name'");
        if ($check_name->num_rows > 0) {
            $message = "⚠️ اسم الفئة موجود بالفعل!";
        } else {
            $image = NULL;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['image']['type'], $allowed_types)) {
                    $image = '../uploads/' . time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $image);
                }
            }
            $image_value = $image ? "'$image'" : "NULL";
            $sql = "INSERT INTO categories (name, parent_id, image) VALUES ('$name', $parent_id, $image_value)";
            if ($conn->query($sql)) {
                $message = "تم إضافة الفئة بنجاح!";
            } else {
                $message = "خطأ في الإضافة: " . $conn->error;
            }
        }
        // بعد الإضافة نعيد التوجيه لنفس الصفحة لتحديث الجدول
        header("Location: {$_SERVER['PHP_SELF']}?page=$page");
        exit();
    }

    // تعديل فئة
    elseif (isset($_POST['update'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : "NULL";

        $check_name = $conn->query("SELECT id FROM categories WHERE name='$name' AND id != $id");
        if ($check_name->num_rows > 0) {
            $message = "⚠️ اسم الفئة موجود بالفعل!";
        } else {
            $image_sql = "";
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['image']['type'], $allowed_types)) {
                    $old_image = $conn->query("SELECT image FROM categories WHERE id=$id")->fetch_assoc()['image'];
                    if ($old_image && file_exists($old_image)) unlink($old_image);
                    $image = '../uploads/' . time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $image);
                    $image_sql = ", image='$image'";
                }
            }
            $sql = "UPDATE categories SET name='$name', parent_id=$parent_id $image_sql WHERE id=$id";
            if ($conn->query($sql)) {
                $message = "تم تعديل الفئة بنجاح!";
            } else {
                $message = "خطأ في التعديل: " . $conn->error;
            }
        }
        header("Location: {$_SERVER['PHP_SELF']}?page=$page");
        exit();
    }

    // حذف فئة
    elseif (isset($_POST['delete']) && $_POST['delete'] == '1') {
        $id = (int)$_POST['id'];
        $check_subcategories = $conn->query("SELECT COUNT(*) as count FROM categories WHERE parent_id=$id");
        $subcategories_count = $check_subcategories->fetch_assoc()['count'];
        if ($subcategories_count > 0) {
            $message = "⚠️ لا يمكن حذف هذه الفئة لأنها تحتوي على فئات فرعية!";
        } else {
            $image_result = $conn->query("SELECT image FROM categories WHERE id=$id");
            if ($image_result && $image_result->num_rows > 0) {
                $image = $image_result->fetch_assoc()['image'];
                if ($image && file_exists($image)) unlink($image);
            }
            $sql = "DELETE FROM categories WHERE id=$id";
            if ($conn->query($sql)) {
                $message = "✅ تم حذف الفئة بنجاح!";
            } else {
                $message = "❌ خطأ في الحذف: " . $conn->error;
            }
        }
        header("Location: {$_SERVER['PHP_SELF']}?page=$page");
        exit();
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// إجمالي الفئات
$total_result = $conn->query("SELECT COUNT(*) as total FROM categories");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// جلب الفئات مع الصفحات
$categories_result = $conn->query("
    SELECT c.*, p.name AS parent_name 
    FROM categories c
    LEFT JOIN categories p ON c.parent_id = p.id
    ORDER BY 
        COALESCE(c.parent_id, c.id),
        c.parent_id IS NOT NULL,
        c.id
    LIMIT $limit OFFSET $offset
");

// إحصائيات الفئات
$total_categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$main_categories = $conn->query("SELECT COUNT(*) as total FROM categories WHERE parent_id IS NULL")->fetch_assoc()['total'];
$sub_categories = $conn->query("SELECT COUNT(*) as total FROM categories WHERE parent_id IS NOT NULL")->fetch_assoc()['total'];

// جلب الفئات الرئيسية للقوائم المنسدلة
$parent_categories = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL");
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
                <h1>🛍️ نظام إدارة الفئات المتقدم</h1>
                <p>أدِر فئات متجرك بكل سهولة واحترافية</p>
                <div class="banner-stats">
                    <div class="stat-item"><span class="stat-number"><?= $total_categories ?></span><span class="stat-label">إجمالي الفئات</span></div>
                    <div class="stat-item"><span class="stat-number"><?= $main_categories ?></span><span class="stat-label">فئة رئيسية</span></div>
                    <div class="stat-item"><span class="stat-number"><?= $sub_categories ?></span><span class="stat-label">فئة فرعية</span></div>
                    <div class="stat-item"><span class="stat-number">🎯</span><span class="stat-label">سهولة الإدارة</span></div>
                </div>
            </div>
        </div>

        <div class="container">
            <ul class="nav-tabs">
                <li><a onclick="showSection('categories')" class="active">📁 إدارة الفئات</a></li>
                <li><a onclick="showSection('stats')">📊 الإحصائيات</a></li>
                <li><a onclick="showSection('help')">❓ المساعدة</a></li>
            </ul>

            <!-- قسم إدارة الفئات -->
            <div id="categories" class="content-section">
                <?php if ($message): ?>
                    <div class="message">✅ <?= $message ?></div>
                <?php endif; ?>

                <!-- شريط الإجراءات مع زر إضافة فئة جديد -->
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="showAddModal()">
                        <i class="fas fa-plus"></i> ➕ إضافة فئة جديدة
                    </button>
                </div>

                <!-- قائمة الفئات -->
                <div class="categories-section">
                    <h3 style="color: #2c3e50; margin-bottom: 20px;">📋 قائمة الفئات</h3>
                    <?php if ($categories_result->num_rows > 0): ?>
                        <div class="table-responsive-scroll">
                            <table class="table">
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
                                                    <img src="<?= $category['image'] ?>" alt="<?= $category['name'] ?>" class="category-image">
                                                <?php else: ?>
                                                    <div style="width:50px; height:50px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold;">
                                                        <?= mb_substr($category['name'], 0, 1) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                            <td>
                                                <?php if ($category['parent_id']): ?>
                                                    <span style="color: #28a745;">فرعية ← <?= htmlspecialchars($category['parent_name']) ?></span>
                                                <?php else: ?>
                                                    <span style="color: #007bff; font-weight: bold;">رئيسية</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($category['created_at'])) ?></td>
                                            <td class="actions">
                                                <button onclick='editCategory(<?= $category['id'] ?>, "<?= htmlspecialchars($category['name']) ?>", <?= $category['parent_id'] ? $category['parent_id'] : 'null' ?>, "<?= $category['image'] ?>")' class="btn-primary" style="padding:6px 12px;">✏️ تعديل</button>
                                                <button onclick='showDeleteModal(<?= $category['id'] ?>, "<?= htmlspecialchars($category['name']) ?>")' class="btn-delete" style="padding:6px 12px;">🗑️ حذف</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page-1 ?>">&laquo; السابق</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page+1 ?>">التالي &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>📭 لا توجد فئات مضافة حالياً</h3>
                            <p>ابدأ بإضافة أول فئة لك باستخدام النافذة المنبثقة</p>
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
                        <li><strong>إضافة فئة جديدة:</strong> انقر على زر "إضافة فئة جديدة" في أعلى الجدول.</li>
                        <li><strong>إنشاء فئة فرعية:</strong> اختر فئة رئيسية من القائمة المنسدلة داخل نافذة الإضافة.</li>
                        <li><strong>تعديل فئة:</strong> انقر على زر "تعديل" بجانب الفئة المطلوبة.</li>
                        <li><strong>حذف فئة:</strong> انقر على زر "حذف" ثم تأكيد الحذف.</li>
                        <li><strong>رفع صورة:</strong> اختر صورة من جهازك لدعم الفئة بصرياً.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== نافذة إضافة الفئة المنبثقة (بتنسيق خاص) ========== -->
<div id="addCategoryModal" class="modal-overlay add-category-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ إضافة فئة جديدة</h3>
            <button class="close-modal" onclick="closeModal('addCategoryModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" action="">
                <div class="form-group">
                    <label>📝 اسم الفئة</label>
                    <input type="text" name="name" class="form-control" required placeholder="أدخل اسم الفئة...">
                </div>
                <div class="form-group">
                    <label>📂 الفئة الأساسية</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- فئة رئيسية --</option>
                        <?php
                        $parent_categories_add = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL");
                        while ($parent = $parent_categories_add->fetch_assoc()):
                        ?>
                            <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>🖼️ صورة الفئة (اختياري)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small style="color: #6c757d;"> JPEG, PNG, GIF</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addCategoryModal')">إلغاء</button>
                    <button type="submit" name="add" class="btn-primary">➕ إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة تعديل الفئة -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ تعديل الفئة</h3>
            <button class="close-modal" onclick="closeModal('editModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" action="">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>📝 اسم الفئة</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>📂 الفئة الأساسية</label>
                    <select name="parent_id" id="edit_parent_id" class="form-select">
                        <option value="">فئة رئيسية</option>
                        <?php
                        $parent_categories_edit = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL");
                        while ($parent = $parent_categories_edit->fetch_assoc()):
                        ?>
                            <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>🖼️ صورة الفئة (اختياري)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editModal')">إلغاء</button>
                    <button type="submit" name="update" class="btn-primary">💾 حفظ التعديل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة تأكيد الحذف -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>🗑️ تأكيد الحذف</h3>
            <button class="close-modal" onclick="closeModal('deleteModal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 48px; color: #dc3545; margin-bottom: 15px;">⚠️</div>
                <h4>هل أنت متأكد من حذف الفئة؟</h4>
                <p style="color: #6c757d; margin: 15px 0;">سيتم حذف الفئة "<strong id="deleteName"></strong>" نهائياً</p>
                <p style="color: #dc3545; font-size: 14px;">هذا الإجراء لا يمكن التراجع عنه!</p>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">إلغاء</button>
                    <button type="submit" name="delete" value="1" class="btn-delete">🗑️ حذف نهائياً</button>
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
        document.getElementById('addCategoryModal').classList.add('active');
    }

    function showDeleteModal(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteModal').classList.add('active');
    }

    function editCategory(id, name, parent_id, image) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_parent_id').value = parent_id || '';
        document.getElementById('editModal').classList.add('active');
    }

    // إغلاق المودال عند النقر خارجها
    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        showSection('categories');
    });
</script>
</body>
</html>