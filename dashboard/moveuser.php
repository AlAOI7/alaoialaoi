<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// فلاتر البحث
$where_clauses = [];
$params = [];
$types = "";

if (!empty($_GET['user_id'])) {
    $where_clauses[] = "ua.user_id = ?";
    $params[] = $_GET['user_id'];
    $types .= "i";
}

if (!empty($_GET['activity_type'])) {
    $where_clauses[] = "ua.activity_type = ?";
    $params[] = $_GET['activity_type'];
    $types .= "s";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// إجمالي الحركات
$count_query = "SELECT COUNT(*) as total FROM user_activities ua " . $where_sql;
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// جلب الحركات
$query = "
    SELECT ua.*, u.name as user_name, u.email as user_email
    FROM user_activities ua
    LEFT JOIN users u ON ua.user_id = u.id
    $where_sql
    ORDER BY ua.created_at DESC
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$activities_result = $stmt->get_result();

// إحصائيات عامة
$total_logins = $conn->query("SELECT COUNT(*) as c FROM user_activities WHERE activity_type='login'")->fetch_assoc()['c'];
$total_devices = $conn->query("SELECT COUNT(DISTINCT device_type) as c FROM user_activities")->fetch_assoc()['c'];

// جلب المستخدمين للفلتر
$users_result = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل حركات المستخدمين - Storthory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6C63FF;
            --primary-light: #8A84FF;
            --primary-dark: #524BC2;
            --secondary: #FF6584;
            --light: #F8F9FD;
            --dark: #2D3748;
            --sidebar-width: 280px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
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

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .filters-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
        }

        .table-responsive-scroll {
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            border-bottom: 2px solid #dee2e6;
        }

        .table td, .table th {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            vertical-align: middle;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .status-success { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }

        .type-login { color: #007bff; font-weight: bold; }
        .type-logout { color: #6c757d; font-weight: bold; }
        .type-add { color: #28a745; font-weight: bold; }
        .type-edit { color: #ffc107; font-weight: bold; }
        .type-delete { color: #dc3545; font-weight: bold; }

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

        @media (max-width: 768px) {
            .main-content {
                margin-right: 0;
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
                <h1>👥 سجل حركات المستخدمين</h1>
                <p>مراقبة وتتبع أنشطة المستخدمين داخل النظام</p>
                <div class="banner-stats">
                    <div class="stat-item"><span class="stat-number"><?= $total_rows ?></span><span class="stat-label">إجمالي الحركات</span></div>
                    <div class="stat-item"><span class="stat-number"><?= $total_logins ?></span><span class="stat-label">عمليات الدخول</span></div>
                    <div class="stat-item"><span class="stat-number"><?= $total_devices ?></span><span class="stat-label">أنواع الأجهزة</span></div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- الفلاتر -->
            <form method="GET" class="filters-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">المستخدم</label>
                        <select name="user_id" class="form-select">
                            <option value="">جميع المستخدمين</option>
                            <?php while($u = $users_result->fetch_assoc()): ?>
                                <option value="<?= $u['id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">نوع النشاط</label>
                        <select name="activity_type" class="form-select">
                            <option value="">جميع الأنشطة</option>
                            <option value="login" <?= (isset($_GET['activity_type']) && $_GET['activity_type'] == 'login') ? 'selected' : '' ?>>تسجيل دخول</option>
                            <option value="logout" <?= (isset($_GET['activity_type']) && $_GET['activity_type'] == 'logout') ? 'selected' : '' ?>>تسجيل خروج</option>
                            <option value="add" <?= (isset($_GET['activity_type']) && $_GET['activity_type'] == 'add') ? 'selected' : '' ?>>إضافة</option>
                            <option value="edit" <?= (isset($_GET['activity_type']) && $_GET['activity_type'] == 'edit') ? 'selected' : '' ?>>تعديل</option>
                            <option value="delete" <?= (isset($_GET['activity_type']) && $_GET['activity_type'] == 'delete') ? 'selected' : '' ?>>حذف</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" style="height: 48px;"><i class="fas fa-filter"></i> تصفية</button>
                    </div>
                </div>
            </form>

            <!-- جدول الحركات -->
            <div class="table-responsive-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>النشاط</th>
                            <th>التفاصيل</th>
                            <th>IP / الجهاز</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($activities_result->num_rows > 0): ?>
                            <?php while ($row = $activities_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['user_name'] ?? 'مستخدم محذوف') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['user_email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="type-<?= $row['activity_type'] ?>">
                                            <?php 
                                                $types_ar = ['login'=>'دخول', 'logout'=>'خروج', 'add'=>'إضافة', 'edit'=>'تعديل', 'delete'=>'حذف', 'view'=>'عرض'];
                                                echo $types_ar[$row['activity_type']] ?? $row['activity_type'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['activity_details']) ?></td>
                                    <td>
                                        <span dir="ltr"><?= $row['ip_address'] ?></span><br>
                                        <small class="text-muted"><i class="fas fa-<?= $row['device_type'] == 'desktop' ? 'laptop' : ($row['device_type'] == 'mobile' ? 'mobile-alt' : 'tablet') ?>"></i> <?= $row['device_type'] ?></small>
                                    </td>
                                    <td><span dir="ltr"><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></span></td>
                                    <td>
                                        <span class="badge-status status-<?= $row['status'] ?>">
                                            <?= $row['status'] == 'success' ? 'ناجح' : ($row['status'] == 'failed' ? 'فشل' : 'معلق') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">لا توجد حركات مسجلة تطابق بحثك.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>&user_id=<?= $_GET['user_id']??'' ?>&activity_type=<?= $_GET['activity_type']??'' ?>">&laquo; السابق</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&user_id=<?= $_GET['user_id']??'' ?>&activity_type=<?= $_GET['activity_type']??'' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?>&user_id=<?= $_GET['user_id']??'' ?>&activity_type=<?= $_GET['activity_type']??'' ?>">التالي &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>