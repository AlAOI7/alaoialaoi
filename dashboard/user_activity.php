
<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// استعلام لجلب حركات المستخدمين
$sql = "SELECT 
            u.id as user_id,
            u.name,
            u.email,
            u.user_type,
            u.last_activity,
            u.status,
            a.id as activity_id,
            a.activity_type,
            a.activity_details,
            a.ip_address,
            a.device_type,
            a.browser_info,
            a.created_at as activity_time
        FROM users u
        LEFT JOIN user_activities a ON u.id = a.user_id
        ORDER BY a.created_at DESC
        LIMIT 100";

$result = $conn->query($sql);
$activities = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}

// إحصائيات
$stats_sql = "SELECT 
                COUNT(DISTINCT u.id) as total_users,
                SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_users,
                COUNT(a.id) as total_activities_today,
                SUM(CASE WHEN a.device_type = 'desktop' THEN 1 ELSE 0 END) as desktop_logins,
                SUM(CASE WHEN a.device_type = 'mobile' THEN 1 ELSE 0 END) as mobile_logins
            FROM users u
            LEFT JOIN user_activities a ON u.id = a.user_id 
                AND DATE(a.created_at) = CURDATE()";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// جلب قائمة المستخدمين للفلتر
$users_sql = "SELECT id, name FROM users ORDER BY name";
$users_result = $conn->query($users_sql);
$users_list = [];
if ($users_result->num_rows > 0) {
    while($row = $users_result->fetch_assoc()) {
        $users_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل حركات المستخدمين - لوحة التحكم</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-activity.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* تحسينات إضافية للجدول */
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: right;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .activity-type {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        
        .activity-type.login { background-color: #d4edda; color: #155724; }
        .activity-type.logout { background-color: #f8d7da; color: #721c24; }
        .activity-type.add { background-color: #cce5ff; color: #004085; }
        .activity-type.edit { background-color: #fff3cd; color: #856404; }
        .activity-type.delete { background-color: #f8d7da; color: #721c24; }
        .activity-type.view { background-color: #d1ecf1; color: #0c5460; }
        
        .device-icon {
            margin-left: 5px;
            font-size: 14px;
        }
        
        .device-desktop { color: #007bff; }
        .device-mobile { color: #28a745; }
        .device-tablet { color: #ffc107; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .action-btn.view {
            background-color: #e9ecef;
            color: #495057;
        }
        
        .action-btn.view:hover {
            background-color: #007bff;
            color: white;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }
        
        .filter-control {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #dee2e6;
            text-align: left;
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

      
        .dashboard {
            display: flex;
            min-height: 100vh;
            position: relative;
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

        .user-activity-management {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .user-stats, .user-activity-content {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .user-stats h3, .user-activity-content h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-right: 4px solid var(--primary);
        }

        .stat-label {
            font-weight: 500;
            color: var(--dark);
        }

        .stat-value {
            font-weight: 700;
            font-size: 18px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
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

        .action-buttons {
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

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
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

        .stat-trend {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .trend-up {
            color: var(--success);
        }

        .trend-down {
            color: var(--secondary);
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

        .card-4 .stat-icon {
            background: linear-gradient(135deg, #6A89CC, #82CCDD);
            color: white;
        }

        .btn {
            padding: 10px 20px;
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
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
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

        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .user-info-card, .user-device-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        .user-info-card h4, .user-device-info h4 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: var(--primary);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark);
        }

        .info-value {
            color: var(--gray);
        }

        .activity-type {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .activity-type.login {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
        }

        .activity-type.logout {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .activity-type.add {
            background-color: rgba(106, 137, 204, 0.15);
            color: var(--info);
        }

        .activity-type.edit {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .activity-type.delete {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .activity-type.view {
            background-color: rgba(54, 209, 220, 0.15);
            color: var(--accent);
        }

        .device-icon {
            font-size: 18px;
            margin-left: 5px;
        }

        .device-desktop {
            color: var(--primary);
        }

        .device-mobile {
            color: var(--info);
        }

        .device-tablet {
            color: var(--warning);
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 12px;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark);
        }

        .filter-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
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

        @media (max-width: 1200px) {
            .user-activity-management {
                grid-template-columns: 1fr;
            }
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
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
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
            
            .filters {
                flex-direction: column;
            }
            
            .user-details {
                grid-template-columns: 1fr;
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
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
                margin-left: 10px;
            }
            
            .stat-info h3 {
                font-size: 20px;
            }
            
            .user-stats, .user-activity-content {
                padding: 15px;
            }
        }

        @media (max-width: 400px) {
            .header {
                padding: 0 10px;
            }
            
            .page-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <!-- الهيدر -->
                <?php include 'header.php'; ?>
            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- شاشة حركات المستخدمين -->
                <div id="user-activity-view" class="user-activity-view">
                    <div class="page-title">
                        <h2>سجل حركات المستخدمين</h2>
                        <div class="date"><?php echo date('l، j F Y'); ?></div>
                    </div>

                    <!-- بطاقات الإحصائيات -->
                    <div class="stats-cards">
                        <div class="stat-card card-1">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_users']; ?></h3>
                                <p>إجمالي المستخدمين</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>5.2% زيادة</span>
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
                                    <span>12.7% زيادة</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-3">
                            <div class="stat-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['desktop_logins']; ?></h3>
                                <p>الدخول من أجهزة سطح المكتب</p>
                                <div class="stat-trend trend-down">
                                    <i class="fas fa-arrow-down"></i>
                                    <span>3.1% انخفاض</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-4">
                            <div class="stat-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['mobile_logins']; ?></h3>
                                <p>الدخول من الأجهزة المحمولة</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>8.4% زيادة</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إدارة حركات المستخدمين -->
                    <div class="user-activity-management">
                        <div class="user-stats">
                            <h3>إحصائيات سريعة</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-label">إجمالي المستخدمين</span>
                                    <span class="stat-value"><?php echo $stats['total_users']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">المستخدمين النشطين</span>
                                    <span class="stat-value"><?php echo $stats['active_users']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الدخول اليوم</span>
                                    <span class="stat-value"><?php echo $stats['total_activities_today']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الأجهزة المستخدمة</span>
                                    <span class="stat-value">3 أنواع</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">متوسط النشاط/مستخدم</span>
                                    <span class="stat-value">8.7</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width: 100%; margin-top: 20px;" id="generate-report-btn">
                                <i class="fas fa-file-export"></i> إنشاء تقرير
                            </button>
                        </div>

                        <div class="user-activity-content">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3>سجل حركات المستخدمين</h3>
                                <div>
                                    <input type="text" class="form-control" id="search-input" placeholder="بحث في السجل..." style="width: 200px;">
                                </div>
                            </div>

                            <!-- الفلاتر -->
                            <form method="GET" action="" class="filters">
                                <div class="filter-group">
                                    <label for="user-filter">المستخدم</label>
                                    <select id="user-filter" name="user_id" class="filter-control">
                                        <option value="">جميع المستخدمين</option>
                                        <?php foreach ($users_list as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" 
                                                <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="activity-filter">نوع النشاط</label>
                                    <select id="activity-filter" name="activity_type" class="filter-control">
                                        <option value="">جميع الأنشطة</option>
                                        <option value="login" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'login') ? 'selected' : ''; ?>>تسجيل الدخول</option>
                                        <option value="logout" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'logout') ? 'selected' : ''; ?>>تسجيل الخروج</option>
                                        <option value="add" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'add') ? 'selected' : ''; ?>>إضافة</option>
                                        <option value="edit" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'edit') ? 'selected' : ''; ?>>تعديل</option>
                                        <option value="delete" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'delete') ? 'selected' : ''; ?>>حذف</option>
                                        <option value="view" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'view') ? 'selected' : ''; ?>>عرض</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="date-filter">التاريخ</label>
                                    <input type="date" id="date-filter" name="activity_date" class="filter-control" 
                                           value="<?php echo isset($_GET['activity_date']) ? $_GET['activity_date'] : ''; ?>">
                                </div>
                                <div class="filter-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary" style="height: 38px;">
                                        <i class="fas fa-filter"></i> تطبيق
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table" id="activities-table">
                                    <thead>
                                        <tr>
                                            <th>المستخدم</th>
                                            <th>النشاط</th>
                                            <th>التفاصيل</th>
                                            <th>التاريخ والوقت</th>
                                            <th>الجهاز</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($activities)): ?>
                                            <tr>
                                                <td colspan="6" style="text-align: center; padding: 40px;">
                                                    <i class="fas fa-info-circle" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                                    <p>لا توجد حركات مستخدمين لعرضها</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($activities as $activity): ?>
                                                <?php if (!empty($activity['activity_id'])): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($activity['name']); ?></strong><br>
                                                        <small style="color: #666;"><?php echo htmlspecialchars($activity['email']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="activity-type <?php echo $activity['activity_type']; ?>">
                                                            <?php 
                                                                $activity_types = [
                                                                    'login' => 'تسجيل دخول',
                                                                    'logout' => 'تسجيل خروج',
                                                                    'add' => 'إضافة',
                                                                    'edit' => 'تعديل',
                                                                    'delete' => 'حذف',
                                                                    'view' => 'عرض'
                                                                ];
                                                                echo $activity_types[$activity['activity_type']] ?? $activity['activity_type'];
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($activity['activity_details']); ?></td>
                                                    <td>
                                                        <?php 
                                                            $date = new DateTime($activity['activity_time']);
                                                            echo $date->format('d/m/Y H:i');
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($activity['device_type'] == 'desktop'): ?>
                                                            <i class="fas fa-laptop device-icon device-desktop"></i> كمبيوتر
                                                        <?php elseif ($activity['device_type'] == 'mobile'): ?>
                                                            <i class="fas fa-mobile-alt device-icon device-mobile"></i> هاتف محمول
                                                        <?php elseif ($activity['device_type'] == 'tablet'): ?>
                                                            <i class="fas fa-tablet-alt device-icon device-tablet"></i> جهاز لوحي
                                                        <?php else: ?>
                                                            <i class="fas fa-question-circle device-icon"></i> غير معروف
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="action-btn view" data-id="<?php echo $activity['activity_id']; ?>"
                                                                    onclick="viewActivityDetails(<?php echo $activity['activity_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة عرض التفاصيل المنبثقة -->
    <div class="modal" id="view-activity-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تفاصيل النشاط</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="activity-details-content">
                <!-- سيتم تحميل المحتوى هنا عبر AJAX -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="print-activity-details">
                    <i class="fas fa-print"></i> طباعة التقرير
                </button>
                <button class="btn btn-danger close-modal">إغلاق</button>
            </div>
        </div>
    </div>

    <script>
        // التحكم في الشريط الجانبي
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // التحكم في النافذة المنبثقة
        const viewActivityModal = document.getElementById('view-activity-modal');

        // إغلاق النافذة المنبثقة
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                viewActivityModal.classList.remove('active');
            });
        });

        // البحث في الجدول
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#activities-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // إنشاء تقرير
        document.getElementById('generate-report-btn').addEventListener('click', function() {
            alert('جاري إنشاء التقرير... سيتم تحميله خلال لحظات.');
            // في التطبيق الحقيقي، سيتم هنا استدعاء دالة إنشاء التقرير
        });

        // طباعة تفاصيل النشاط
        document.getElementById('print-activity-details').addEventListener('click', function() {
            window.print();
        });

        // عرض تفاصيل النشاط
        function viewActivityDetails(activityId) {
            // استخدام AJAX لجلب تفاصيل النشاط
            fetch(`get_activity_details.php?id=${activityId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('activity-details-content').innerHTML = data;
                    viewActivityModal.classList.add('active');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء جلب التفاصيل');
                });
        }

        // إغلاق النافذة عند الضغط على ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                viewActivityModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>