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


// إنشاء مجلد الصور إذا لم يكن موجوداً
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// معالجة الإجراءات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                // تنظيف المدخلات
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone'] ?? '');
                $password = $_POST['password'];
                $role = $_POST['role'];
                $status = $_POST['status'];
                
                // التحقق من البيانات
                if (empty($first_name) || empty($email) || empty($password)) {
                    $message = "الرجاء تعبئة جميع الحقول الإلزامية";
                    $message_type = "error";
                    break;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $message = "البريد الإلكتروني غير صالح";
                    $message_type = "error";
                    break;
                }
                
                // التحقق من قوة كلمة المرور
                if (strlen($password) < 6) {
                    $message = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
                    $message_type = "error";
                    break;
                }
                
                $data = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role,
                    'status' => $status
                ];
                
                $result = addUser($data);
                if (is_array($result)) {
                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';
                } else {
                    $message = "حدث خطأ غير معروف أثناء الإضافة";
                    $message_type = "error";
                }
                break;
                
            case 'update_user':
                // تنظيف المدخلات
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone'] ?? '');
                $password = !empty($_POST['password']) ? $_POST['password'] : null;
                $role = $_POST['role'];
                $status = $_POST['status'];
                $id = $_POST['id'];
                
                // التحقق من البيانات
                if (empty($first_name) || empty($email) || empty($id)) {
                    $message = "الرجاء تعبئة جميع الحقول الإلزامية";
                    $message_type = "error";
                    break;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $message = "البريد الإلكتروني غير صالح";
                    $message_type = "error";
                    break;
                }
                
                $data = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => $role,
                    'status' => $status
                ];
                
                if ($password) {
                    if (strlen($password) < 6) {
                        $message = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
                        $message_type = "error";
                        break;
                    }
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $result = updateUser($id, $data);
                if (is_array($result)) {
                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';
                } else {
                    $message = "حدث خطأ غير معروف أثناء التحديث";
                    $message_type = "error";
                }
                break;
                
            case 'delete_user':
                $id = $_POST['id'];
                
                if (empty($id)) {
                    $message = "معرف المستخدم غير صالح";
                    $message_type = "error";
                    break;
                }
                
                // منع حذف المستخدم الحالي
                if ($id == $_SESSION['user_id']) {
                    $message = "لا يمكن حذف حسابك الخاص";
                    $message_type = "error";
                    break;
                }
                
                $result = deleteUser($id);
                if (is_array($result)) {
                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';
                } else {
                    $message = "حدث خطأ غير معروف أثناء الحذف";
                    $message_type = "error";
                }
                break;
        }
    }
}

// جلب جميع المستخدمين
$users = getAllUsers();
$stats = getUserStats();

// تعريف نص الأدوار
$role_titles = [
    'admin' => 'مدير النظام',
    'manager' => 'مدير المبيعات', 
    'sales' => 'مندوب مبيعات',
    'support' => 'دعم العملاء',
    'user' => 'مستخدم عادي'
];

// تعريف نص الحالات
$status_text = [
    'active' => 'نشط',
    'inactive' => 'معطل',
    'pending' => 'في الانتظار'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            direction: rtl;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            color: white;
            position: fixed;
            height: 100vh;
            right: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo h2 {
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .menu {
            list-style: none;
            padding: 20px 0;
        }

        .menu li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            gap: 10px;
        }

        .menu li a:hover, .menu li a.active {
            background: rgba(255,255,255,0.1);
            border-right: 4px solid white;
        }

        .menu li a i {
            width: 20px;
            text-align: center;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            transition: all 0.3s;
        }

        .page-content {
            padding: 20px;
        }

        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-title h2 {
            color: var(--dark);
        }

        .date {
            color: var(--gray);
        }

        /* زر القائمة للجوال */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            z-index: 1100;
            cursor: pointer;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* الأزرار */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* شريط الإجراءات */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        /* إحصائيات المستخدمين */
        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .card-1 .stat-icon { background: var(--primary); }
        .card-2 .stat-icon { background: var(--success); }
        .card-3 .stat-icon { background: var(--warning); }
        .card-4 .stat-icon { background: var(--danger); }

        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--gray);
            margin-bottom: 5px;
        }

        .stat-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }

        /* جدول المستخدمين */
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .avatar-admin { background: var(--danger); }
        .avatar-manager { background: var(--warning); }
        .avatar-sales { background: var(--success); }
        .avatar-support { background: var(--info); }
        .avatar-user { background: var(--gray); }

        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-admin { background: #ffe6e6; color: var(--danger); }
        .role-manager { background: #fff3cd; color: var(--warning); }
        .role-sales { background: #d1ecf1; color: var(--success); }
        .role-support { background: #e2e3e5; color: var(--gray); }
        .role-user { background: #f8f9fa; color: var(--dark); }

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }

        .action-cell {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .edit-btn { background: #d1ecf1; color: var(--info); }
        .delete-btn { background: #f8d7da; color: var(--danger); }

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        /* النافذة المنبثقة */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
        }

        .role-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .role-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .role-option.selected {
            border-color: var(--primary);
            background: #f0f4ff;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .role-admin .role-icon { color: var(--danger); }
        .role-manager .role-icon { color: var(--warning); }
        .role-sales .role-icon { color: var(--success); }
        .role-support .role-icon { color: var(--info); }
        .role-user .role-icon { color: var(--gray); }

        .form-actions {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* الرسائل */
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* التجاوب */
        @media (max-width: 768px) {
            .sidebar {
                right: -100%;
            }
            
            .sidebar.active {
                right: 0;
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .overlay.active {
                display: block;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .action-cell {
                flex-direction: column;
            }
            
            .role-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- طبقة التعتيم للخلفية -->
    <div class="overlay" id="overlay"></div>

    <!-- لوحة التحكم -->
    <div class="container">
        <?php 
        // ملفات الجانبية
        $sidebar_file = 'sidebar.php';
        $header_file = 'header.php';
        
        if (file_exists($sidebar_file)) {
            include $sidebar_file;
        } else {
            echo '<div class="sidebar">';
            echo '<div class="logo">';
            echo '<h2><i class="fas fa-users-cog"></i> لوحة التحكم</h2>';
            echo '</div>';
            echo '<ul class="menu">';
            echo '<li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> الرئيسية</a></li>';
            echo '<li><a href="adduser.php" class="active"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>';
            echo '<li><a href="#"><i class="fas fa-cog"></i> الإعدادات</a></li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content" id="mainContent">
            <?php 
            if (file_exists($header_file)) {
                include $header_file;
            } else {
                echo '<div style="background: white; padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
                echo '<div>';
                echo '<h3 style="margin: 0;">مرحباً، ' . ($current_user['first_name'] ?? 'المستخدم') . '</h3>';
                echo '</div>';
                echo '<div style="display: flex; align-items: center; gap: 15px;">';
                echo '<a href="logout.php" style="color: var(--danger); text-decoration: none;">';
                echo '<i class="fas fa-sign-out-alt"></i> تسجيل خروج';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
            ?>
            
            <!-- محتوى صفحة المستخدمين -->
            <div class="page-content">
                <div class="page-title">
                    <h2>إدارة المستخدمين</h2>
                    <div class="date"><?php echo date('Y-m-d'); ?></div>
                </div>

                <!-- عرض الرسائل -->
                <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <div class="actions-bar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchUsers" placeholder="البحث في المستخدمين...">
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-primary" id="addUserBtn">
                            <i class="fas fa-plus"></i>
                            إضافة مستخدم جديد
                        </button>
                    </div>
                </div>

                <div class="users-stats">
                    <div class="stat-card card-1">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>إجمالي المستخدمين</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>12% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-2">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['active_users']; ?></h3>
                            <p>المستخدمين النشطين</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>8% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-3">
                        <div class="stat-icon">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending_users']; ?></h3>
                            <p>في انتظار التفعيل</p>
                            <div class="stat-trend trend-down">
                                <i class="fas fa-arrow-down"></i>
                                <span>2 أقل</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-4">
                        <div class="stat-icon">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['inactive_users']; ?></h3>
                            <p>المستخدمين المعطلين</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>1 زيادة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="users-table">
                    <div class="table-header">
                        <h3>قائمة المستخدمين</h3>
                        <div class="table-actions">
                            <button class="btn btn-success" id="refreshUsersBtn">
                                <i class="fas fa-sync"></i>
                                تحديث
                            </button>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الدور</th>
                                <th>الحالة</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (!empty($users)): ?>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="user-avatar-small avatar-<?php echo $user['role']; ?>">
                                            <?php 
                                            $first_char = '';
                                            if (!empty($user['first_name'])) {
                                                $first_char = mb_substr($user['first_name'], 0, 1);
                                            } elseif (!empty($user['name'])) {
                                                $first_char = mb_substr($user['name'], 0, 1);
                                            } else {
                                                $first_char = '?';
                                            }
                                            echo $first_char;
                                            ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo $user['first_name'] . ' ' . ($user['last_name'] ?? ''); ?></div>
                                            <div style="font-size: 12px; color: var(--gray);">
                                                <?php echo $role_titles[$user['role']] ?? $user['role']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $user['email']; ?></td>
                                <td><span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo $role_titles[$user['role']] ?? $user['role']; ?>
                                </span></td>
                                <td>
                                    <span class="status status-<?php echo $user['status']; ?>">
                                        <?php echo $status_text[$user['status']] ?? $user['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td class="action-cell">
                                    <button class="action-btn edit-btn" data-user-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                        تعديل
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button class="action-btn delete-btn" data-user-id="<?php echo $user['id']; ?>" data-user-name="<?php echo $user['first_name'] . ' ' . ($user['last_name'] ?? ''); ?>">
                                        <i class="fas fa-trash"></i>
                                        حذف
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-users" style="font-size: 48px; color: #ddd; margin-bottom: 10px;"></i>
                                    <p>لا توجد مستخدمين</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة منبثقة لإضافة/تعديل مستخدم -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="userModalTitle">إضافة مستخدم جديد</h3>
                <button class="close-btn" id="closeUserModal">&times;</button>
            </div>
            <form id="userForm" method="POST">
                <input type="hidden" name="id" id="userId">
                <input type="hidden" name="action" id="formAction" value="add_user">
                <div style="padding: 20px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">الاسم الأول *</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" placeholder="أدخل الاسم الأول" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">اسم العائلة</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" placeholder="أدخل اسم العائلة">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="أدخل البريد الإلكتروني" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="أدخل رقم الهاتف">
                    </div>
                    <div class="form-row" id="passwordFields">
                        <div class="form-group">
                            <label for="password">كلمة المرور *</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">تأكيد كلمة المرور *</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="confirmPassword" placeholder="أكد كلمة المرور" required>
                                <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>الدور *</label>
                        <div class="role-options">
                            <div class="role-option role-user" data-role="user">
                                <input type="radio" name="role" id="role-user" value="user" checked>
                                <div class="role-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">مستخدم عادي</div>
                                    <div style="font-size: 12px; color: var(--gray);">صلاحيات محدودة</div>
                                </div>
                            </div>
                            <div class="role-option role-admin" data-role="admin">
                                <input type="radio" name="role" id="role-admin" value="admin">
                                <div class="role-icon">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">مدير النظام</div>
                                    <div style="font-size: 12px; color: var(--gray);">صلاحيات كاملة</div>
                                </div>
                            </div>
                            <div class="role-option role-manager" data-role="manager">
                                <input type="radio" name="role" id="role-manager" value="manager">
                                <div class="role-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">مدير المبيعات</div>
                                    <div style="font-size: 12px; color: var(--gray);">إدارة المبيعات والطلبات</div>
                                </div>
                            </div>
                            <div class="role-option role-sales" data-role="sales">
                                <input type="radio" name="role" id="role-sales" value="sales">
                                <div class="role-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">مندوب مبيعات</div>
                                    <div style="font-size: 12px; color: var(--gray);">إضافة وعرض الطلبات</div>
                                </div>
                            </div>
                            <div class="role-option role-support" data-role="support">
                                <input type="radio" name="role" id="role-support" value="support">
                                <div class="role-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">دعم العملاء</div>
                                    <div style="font-size: 12px; color: var(--gray);">إدارة العملاء والتذاكر</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="userStatus">حالة المستخدم *</label>
                        <select class="form-control" id="userStatus" name="status" required>
                            <option value="active">نشط</option>
                            <option value="inactive">معطل</option>
                            <option value="pending" selected>في انتظار التفعيل</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-warning" id="cancelUserBtn">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">حفظ المستخدم</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // عناصر DOM
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const overlay = document.getElementById('overlay');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.getElementById('mainContent');
        const addUserBtn = document.getElementById('addUserBtn');
        const userModal = document.getElementById('userModal');
        const closeUserModal = document.getElementById('closeUserModal');
        const cancelUserBtn = document.getElementById('cancelUserBtn');
        const userForm = document.getElementById('userForm');
        const userModalTitle = document.getElementById('userModalTitle');
        const userId = document.getElementById('userId');
        const formAction = document.getElementById('formAction');
        const passwordFields = document.getElementById('passwordFields');
        const searchUsers = document.getElementById('searchUsers');
        const usersTableBody = document.getElementById('usersTableBody');
        const refreshUsersBtn = document.getElementById('refreshUsersBtn');

        // تهيئة التطبيق
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            setupRoleOptions();
        });

        // إعداد مستمعي الأحداث
        function setupEventListeners() {
            // تبديل القائمة في الشاشات الصغيرة
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleMobileMenu);
            }
            if (overlay) {
                overlay.addEventListener('click', toggleMobileMenu);
            }

            // فتح نافذة إضافة مستخدم
            if (addUserBtn) {
                addUserBtn.addEventListener('click', openUserModal);
            }

            // إغلاق نافذة المستخدم
            if (closeUserModal) {
                closeUserModal.addEventListener('click', closeUserModalHandler);
            }
            if (cancelUserBtn) {
                cancelUserBtn.addEventListener('click', closeUserModalHandler);
            }

            // إغلاق النافذة عند النقر خارج المحتوى
            window.addEventListener('click', (e) => {
                if (e.target === userModal) {
                    closeUserModalHandler();
                }
            });

            // إظهار/إخفاء كلمة المرور
            const togglePasswordBtn = document.getElementById('togglePassword');
            const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
            
            if (togglePasswordBtn) {
                togglePasswordBtn.addEventListener('click', function() {
                    togglePasswordVisibility('password');
                });
            }
            
            if (toggleConfirmPasswordBtn) {
                toggleConfirmPasswordBtn.addEventListener('click', function() {
                    togglePasswordVisibility('confirmPassword');
                });
            }

            // البحث في المستخدمين
            if (searchUsers) {
                searchUsers.addEventListener('input', searchUsersHandler);
            }

            // تحديث قائمة المستخدمين
            if (refreshUsersBtn) {
                refreshUsersBtn.addEventListener('click', function() {
                    location.reload();
                });
            }

            // تعديل المستخدمين
            document.addEventListener('click', function(event) {
                if (event.target.closest('.edit-btn')) {
                    const userId = event.target.closest('.edit-btn').getAttribute('data-user-id');
                    editUser(userId);
                }
                
                if (event.target.closest('.delete-btn')) {
                    const userId = event.target.closest('.delete-btn').getAttribute('data-user-id');
                    const userName = event.target.closest('.delete-btn').getAttribute('data-user-name');
                    deleteUser(userId, userName);
                }
            });

            // إرسال نموذج المستخدم
            if (userForm) {
                userForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveUser();
                });
            }
        }

        // إعداد خيارات الأدوار
        function setupRoleOptions() {
            document.querySelectorAll('.role-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.role-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.classList.add('selected');
                    this.querySelector('input[type="radio"]').checked = true;
                });
            });
        }

        // تبديل القائمة في الشاشات الصغيرة
        function toggleMobileMenu() {
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
            if (overlay) {
                overlay.classList.toggle('active');
            }
        }

        // فتح نافذة إضافة مستخدم
        function openUserModal() {
            userModalTitle.textContent = 'إضافة مستخدم جديد';
            formAction.value = 'add_user';
            userForm.reset();
            userId.value = '';
            passwordFields.style.display = 'flex';
            document.getElementById('password').required = true;
            document.getElementById('confirmPassword').required = true;
            
            // إعادة تعيين اختيارات الدور
            document.querySelectorAll('.role-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.getAttribute('data-role') === 'user') {
                    opt.classList.add('selected');
                    opt.querySelector('input[type="radio"]').checked = true;
                }
            });
            
            userModal.style.display = 'flex';
        }

        // إغلاق نافذة المستخدم
        function closeUserModalHandler() {
            userModal.style.display = 'none';
        }

        // إظهار/إخفاء كلمة المرور
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('toggle' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1)).querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // البحث في المستخدمين
        function searchUsersHandler() {
            const searchTerm = searchUsers.value.toLowerCase();
            const rows = usersTableBody.getElementsByTagName('tr');
            
            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let cell of cells) {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        }

        // تعديل مستخدم
        function editUser(userId) {
            // إنشاء نموذج AJAX بسيط لجلب بيانات المستخدم
            const formData = new FormData();
            formData.append('user_id', userId);
            
            fetch('get_user_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userModalTitle.textContent = 'تعديل المستخدم';
                    formAction.value = 'update_user';
                    document.getElementById('userId').value = data.id;
                    document.getElementById('firstName').value = data.first_name;
                    document.getElementById('lastName').value = data.last_name || '';
                    document.getElementById('email').value = data.email;
                    document.getElementById('phone').value = data.phone || '';
                    document.getElementById('userStatus').value = data.status;
                    
                    // إخفاء حقلي كلمة المرور في حالة التعديل
                    passwordFields.style.display = 'none';
                    document.getElementById('password').required = false;
                    document.getElementById('confirmPassword').required = false;
                    
                    // تحديد الدور
                    document.querySelectorAll('.role-option').forEach(opt => {
                        opt.classList.remove('selected');
                        if (opt.getAttribute('data-role') === data.role) {
                            opt.classList.add('selected');
                            opt.querySelector('input[type="radio"]').checked = true;
                        }
                    });
                    
                    userModal.style.display = 'flex';
                } else {
                    alert(data.message || 'حدث خطأ أثناء تحميل بيانات المستخدم');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء تحميل بيانات المستخدم');
            });
        }

        // حذف مستخدم
        function deleteUser(userId, userName) {
            if (confirm(`هل أنت متأكد من حذف المستخدم "${userName}"؟`)) {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('id', userId);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('حدث خطأ أثناء حذف المستخدم');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء حذف المستخدم');
                });
            }
        }

        // حفظ المستخدم
        function saveUser() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const formActionValue = formAction.value;
            
            // التحقق من كلمة المرور في حالة الإضافة
            if (formActionValue === 'add_user') {
                if (password !== confirmPassword) {
                    alert('كلمة المرور وتأكيدها غير متطابقتين');
                    return;
                }
                if (password.length < 6) {
                    alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                    return;
                }
            }
            
            // التحقق من كلمة المرور في حالة التعديل (إذا تم إدخالها)
            if (formActionValue === 'update_user' && password) {
                if (password !== confirmPassword) {
                    alert('كلمة المرور وتأكيدها غير متطابقتين');
                    return;
                }
                if (password.length < 6) {
                    alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                    return;
                }
            }
            
            // إرسال النموذج
            userForm.submit();
        }
    </script>
</body>
</html>