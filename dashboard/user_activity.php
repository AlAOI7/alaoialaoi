
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
    <link rel="stylesheet" href="style.css">
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