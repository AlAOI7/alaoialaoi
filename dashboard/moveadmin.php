<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل حركات الإداريين - المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .admin-activity-management {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .admin-stats, .admin-activity-content {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .admin-stats h3, .admin-activity-content h3 {
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

        .action-btn.print {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
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

        .admin-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .admin-info-card, .admin-device-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        .admin-info-card h4, .admin-device-info h4 {
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
            .admin-activity-management {
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
            
            .admin-details {
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
            
            .admin-stats, .admin-activity-content {
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
        <!-- الشريط الجانبي -->
        <div class="sidebar">
            <div class="logo">
                <h1>المتجر الإلكتروني</h1>
                <p>سجل حركات الإداريين</p>
            </div>
            <div class="sidebar-menu">
                <div class="menu-item" id="admin-menu">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>لوحة التحكم</span>
                </div>
                <div class="menu-item" id="supplier-menu">
                    <i class="fas fa-user-tie"></i>
                    <span>إدارة الموردين</span>
                </div>
                <div class="menu-item" id="returns-menu">
                    <i class="fas fa-undo-alt"></i>
                    <span>إدارة المرتجعات</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-boxes"></i>
                    <span>إدارة المنتجات</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>العلامات التجارية</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>الفواتير والمبيعات</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-user-clock"></i>
                    <span>سجل المستخدمين</span>
                </div>
                <div class="menu-item active" id="admin-activity-menu">
                    <i class="fas fa-user-shield"></i>
                    <span>سجل الإداريين</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>الإعدادات</span>
                </div>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <!-- الهيدر -->
            <div class="header">
                <div class="header-left">
                    <button class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-title">
                        <h2 id="page-title">سجل حركات الإداريين</h2>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">إد</div>
                        <div class="user-info">
                            <div class="user-name">إدارة المتجر</div>
                            <div class="user-role">مسؤول النظام</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- شاشة حركات الإداريين -->
                <div id="admin-activity-view" class="admin-activity-view">
                    <div class="page-title">
                        <h2>سجل حركات الإداريين</h2>
                        <div class="date">الثلاثاء، 15 نوفمبر 2023</div>
                    </div>

                    <!-- بطاقات الإحصائيات -->
                    <div class="stats-cards">
                        <div class="stat-card card-1">
                            <div class="stat-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stat-info">
                                <h3>24</h3>
                                <p>إجمالي الإداريين</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>3.2% زيادة</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-2">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3>18</h3>
                                <p>الإداريين النشطين اليوم</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>8.7% زيادة</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-3">
                            <div class="stat-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3>42</h3>
                                <p>عمليات تسجيل الدخول</p>
                                <div class="stat-trend trend-down">
                                    <i class="fas fa-arrow-down"></i>
                                    <span>2.1% انخفاض</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-4">
                            <div class="stat-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-info">
                                <h3>156</h3>
                                <p>إجمالي العمليات</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>12.4% زيادة</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إدارة حركات الإداريين -->
                    <div class="admin-activity-management">
                        <div class="admin-stats">
                            <h3>إحصائيات سريعة</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-label">إجمالي الإداريين</span>
                                    <span class="stat-value">24</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الإداريين النشطين</span>
                                    <span class="stat-value">18</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الدخول اليوم</span>
                                    <span class="stat-value">42</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الخروج اليوم</span>
                                    <span class="stat-value">38</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">متوسط النشاط/إداري</span>
                                    <span class="stat-value">6.5</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width: 100%; margin-top: 20px;" id="generate-admin-report-btn">
                                <i class="fas fa-file-export"></i> إنشاء تقرير شامل
                            </button>
                        </div>

                        <div class="admin-activity-content">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3>سجل حركات الإداريين</h3>
                                <div>
                                    <input type="text" class="form-control" placeholder="بحث في السجل..." style="width: 200px;">
                                </div>
                            </div>

                            <!-- الفلاتر -->
                            <div class="filters">
                                <div class="filter-group">
                                    <label for="admin-filter">الإداري</label>
                                    <select id="admin-filter" class="filter-control">
                                        <option value="">جميع الإداريين</option>
                                        <option value="1">أحمد محمد (مدير النظام)</option>
                                        <option value="2">سارة عبدالله (مديرة المبيعات)</option>
                                        <option value="3">محمد علي (مدير المنتجات)</option>
                                        <option value="4">فاطمة أحمد (مديرة المخازن)</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="activity-filter">نوع النشاط</label>
                                    <select id="activity-filter" class="filter-control">
                                        <option value="">جميع الأنشطة</option>
                                        <option value="login">تسجيل الدخول</option>
                                        <option value="logout">تسجيل الخروج</option>
                                        <option value="add">إضافة</option>
                                        <option value="edit">تعديل</option>
                                        <option value="delete">حذف</option>
                                        <option value="view">عرض</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="date-filter">التاريخ</label>
                                    <input type="date" id="date-filter" class="filter-control" value="2023-11-15">
                                </div>
                                <div class="filter-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary" style="height: 38px;">
                                        <i class="fas fa-filter"></i> تطبيق
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>الإداري</th>
                                            <th>الدور</th>
                                            <th>النشاط</th>
                                            <th>التفاصيل</th>
                                            <th>التاريخ والوقت</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>أحمد محمد</td>
                                            <td>مدير النظام</td>
                                            <td><span class="activity-type login">تسجيل دخول</span></td>
                                            <td>تسجيل دخول ناجح إلى النظام</td>
                                            <td>15/11/2023 08:30 ص</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="1">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn print" data-id="1">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>سارة عبدالله</td>
                                            <td>مديرة المبيعات</td>
                                            <td><span class="activity-type add">إضافة</span></td>
                                            <td>إضافة طلب بيع جديد #ORD-2023-1245</td>
                                            <td>15/11/2023 09:15 ص</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="2">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn print" data-id="2">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>محمد علي</td>
                                            <td>مدير المنتجات</td>
                                            <td><span class="activity-type edit">تعديل</span></td>
                                            <td>تعديل سعر المنتج: هاتف ذكي - موديل X1</td>
                                            <td>15/11/2023 10:45 ص</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="3">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn print" data-id="3">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>فاطمة أحمد</td>
                                            <td>مديرة المخازن</td>
                                            <td><span class="activity-type view">عرض</span></td>
                                            <td>عرض تقرير المخزون الحالي</td>
                                            <td>15/11/2023 11:30 ص</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="4">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn print" data-id="4">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>خالد سعيد</td>
                                            <td>مدير الجودة</td>
                                            <td><span class="activity-type delete">حذف</span></td>
                                            <td>حذف منتج منتهي الصلاحية: #PROD-0452</td>
                                            <td>15/11/2023 01:20 م</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="5">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn print" data-id="5">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>نورة عبدالرحمن</td>
                                            <td>مديرة التسويق</td>
                                            <td><span class="activity-type logout">تسجيل خروج</span></td>
                                            <td>تسجيل خروج من النظام</td>
                                            <td>15/11/2023 04:45 م</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="6">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn print" data-id="6">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
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
    <div class="modal" id="view-admin-activity-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تفاصيل نشاط الإداري</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="admin-details">
                    <div class="admin-info-card">
                        <h4>معلومات الإداري</h4>
                        <div class="info-item">
                            <span class="info-label">اسم الإداري:</span>
                            <span class="info-value">أحمد محمد</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">البريد الإلكتروني:</span>
                            <span class="info-value">ahmed.admin@example.com</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">الدور:</span>
                            <span class="info-value">مدير النظام</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">القسم:</span>
                            <span class="info-value">تقنية المعلومات</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">تاريخ التعيين:</span>
                            <span class="info-value">15/03/2022</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">حالة الحساب:</span>
                            <span class="info-value" style="color: var(--success);">نشط</span>
                        </div>
                    </div>
                    <div class="admin-device-info">
                        <h4>معلومات الجهاز والجلسة</h4>
                        <div class="info-item">
                            <span class="info-label">نوع الجهاز:</span>
                            <span class="info-value">كمبيوتر</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">نظام التشغيل:</span>
                            <span class="info-value">Windows 11</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">المتصفح:</span>
                            <span class="info-value">Chrome 118</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">عنوان IP:</span>
                            <span class="info-value">192.168.1.105</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">الموقع:</span>
                            <span class="info-value">الرياض، السعودية</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">مدة الجلسة:</span>
                            <span class="info-value">4 ساعات و 30 دقيقة</span>
                        </div>
                    </div>
                </div>

                <div class="admin-info-card" style="margin-top: 20px;">
                    <h4>تفاصيل النشاط</h4>
                    <div class="info-item">
                        <span class="info-label">نوع النشاط:</span>
                        <span class="info-value"><span class="activity-type login">تسجيل دخول</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التاريخ والوقت:</span>
                        <span class="info-value">15/11/2023 08:30 ص</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الحالة:</span>
                        <span class="info-value" style="color: var(--success);">ناجح</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التفاصيل:</span>
                        <span class="info-value">تسجيل دخول ناجح إلى النظام من خلال صفحة تسجيل الدخول الرئيسية</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المستوى الأمني:</span>
                        <span class="info-value">عالية</span>
                    </div>
                </div>

                <div class="admin-info-card" style="margin-top: 20px;">
                    <h4>النشاطات المرتبطة خلال الجلسة</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>النشاط</th>
                                    <th>التفاصيل</th>
                                    <th>التاريخ والوقت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="activity-type view">عرض</span></td>
                                    <td>عرض لوحة التحكم الرئيسية</td>
                                    <td>15/11/2023 08:31 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type view">عرض</span></td>
                                    <td>عرض تقرير أداء النظام</td>
                                    <td>15/11/2023 08:45 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type edit">تعديل</span></td>
                                    <td>تعديل صلاحيات المستخدم: سارة عبدالله</td>
                                    <td>15/11/2023 09:20 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type view">عرض</span></td>
                                    <td>عرض سجل الأخطاء النظام</td>
                                    <td>15/11/2023 10:15 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type add">إضافة</span></td>
                                    <td>إضافة مستخدم جديد: خالد سعيد</td>
                                    <td>15/11/2023 11:30 ص</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="print-admin-activity">
                    <i class="fas fa-print"></i> طباعة كشف الحركة
                </button>
                <button class="btn btn-primary" id="export-admin-activity">
                    <i class="fas fa-file-export"></i> تصدير التقرير
                </button>
                <button class="btn btn-danger close-modal">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        // التحكم في الشريط الجانبي
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // التحكم في القوائم
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                // إزالة النشاط من جميع العناصر
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // إضافة النشاط للعنصر المحدد
                this.classList.add('active');
                
                // تحديث عنوان الصفحة
                if (this.id === 'admin-activity-menu') {
                    document.getElementById('page-title').textContent = 'سجل حركات الإداريين';
                }
            });
        });

        // التحكم في النافذة المنبثقة
        const viewAdminActivityModal = document.getElementById('view-admin-activity-modal');

        // فتح نافذة عرض التفاصيل
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                viewAdminActivityModal.classList.add('active');
            });
        });

        // طباعة كشف الحركة
        document.querySelectorAll('.action-btn.print').forEach(btn => {
            btn.addEventListener('click', function() {
                alert('جاري طباعة كشف الحركة...');
                // في التطبيق الحقيقي، سيتم هنا استدعاء دالة الطباعة
            });
        });

        // إغلاق النافذة المنبثقة
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                viewAdminActivityModal.classList.remove('active');
            });
        });

        // إنشاء تقرير شامل
        document.getElementById('generate-admin-report-btn').addEventListener('click', function() {
            alert('جاري إنشاء التقرير الشامل... سيتم تحميله خلال لحظات.');
            // في التطبيق الحقيقي، سيتم هنا استدعاء دالة إنشاء التقرير
        });

        // طباعة كشف الحركة من النافذة المنبثقة
        document.getElementById('print-admin-activity').addEventListener('click', function() {
            alert('جاري تحضير كشف الحركة للطباعة...');
            // في التطبيق الحقيقي، سيتم هنا استدعاء دالة الطباعة
        });

        // تصدير التقرير
        document.getElementById('export-admin-activity').addEventListener('click', function() {
            alert('جاري تصدير التقرير...');
            // في التطبيق الحقيقي، سيتم هنا استدعاء دالة التصدير
        });

        // تطبيق الفلاتر
        document.querySelector('.filters .btn-primary').addEventListener('click', function() {
            alert('تم تطبيق الفلاتر بنجاح!');
            // في التطبيق الحقيقي، سيتم هنا تطبيق الفلاتر على البيانات
        });
    </script>
</body>
</html>