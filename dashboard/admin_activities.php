<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// تحديد نطاق الإداريين (يمكن تعديل حسب النظام)
$admin_types = ['admin', 'manager', 'supervisor'];

// استعلام لجلب حركات الإداريين فقط
$where_conditions = [];
$params = [];
$types = '';

// فلترة حسب المستخدم إذا تم اختياره
if (isset($_GET['admin_id']) && !empty($_GET['admin_id'])) {
    $where_conditions[] = "u.id = ?";
    $params[] = $_GET['admin_id'];
    $types .= 'i';
}

// فلترة حسب نوع النشاط
if (isset($_GET['activity_type']) && !empty($_GET['activity_type'])) {
    $where_conditions[] = "a.activity_type = ?";
    $params[] = $_GET['activity_type'];
    $types .= 's';
}

// فلترة حسب التاريخ
if (isset($_GET['activity_date']) && !empty($_GET['activity_date'])) {
    $where_conditions[] = "DATE(a.created_at) = ?";
    $params[] = $_GET['activity_date'];
    $types .= 's';
}

// فلترة حسب وقت الجلسة (مثال: نشاطات اليوم)
$today = date('Y-m-d');

// بناء استعلام SQL
$sql = "SELECT 
            u.id as user_id,
            u.name,
            u.email,
            u.user_type,
            u.phone,
            u.last_activity,
            u.status as user_status,
            a.id as activity_id,
            a.activity_type,
            a.activity_details,
            a.ip_address,
            a.device_type,
            a.browser_info,
            a.status as activity_status,
            a.created_at as activity_time
        FROM users u
        LEFT JOIN user_activities a ON u.id = a.user_id
        WHERE u.user_type IN ('" . implode("','", $admin_types) . "')";

// إضافة شروط الفلترة إذا كانت موجودة
if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY a.created_at DESC LIMIT 100";

// تنفيذ الاستعلام
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$activities = $result->fetch_all(MYSQLI_ASSOC);

// إحصائيات خاصة بالإداريين
$stats_sql = "SELECT 
                COUNT(DISTINCT u.id) as total_admins,
                SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_admins,
                COUNT(DISTINCT CASE WHEN DATE(a.created_at) = CURDATE() THEN u.id END) as admins_active_today,
                COUNT(CASE WHEN DATE(a.created_at) = CURDATE() AND a.activity_type = 'login' THEN 1 END) as login_today,
                COUNT(CASE WHEN DATE(a.created_at) = CURDATE() AND a.activity_type = 'logout' THEN 1 END) as logout_today,
                COUNT(CASE WHEN DATE(a.created_at) = CURDATE() THEN 1 END) as total_activities_today,
                SUM(CASE WHEN a.device_type = 'desktop' THEN 1 ELSE 0 END) as desktop_activities,
                SUM(CASE WHEN a.device_type = 'mobile' THEN 1 ELSE 0 END) as mobile_activities
            FROM users u
            LEFT JOIN user_activities a ON u.id = a.user_id 
                AND DATE(a.created_at) = CURDATE()
            WHERE u.user_type IN ('" . implode("','", $admin_types) . "')";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// جلب قائمة الإداريين للفلتر
$admins_sql = "SELECT id, name, user_type FROM users 
               WHERE user_type IN ('" . implode("','", $admin_types) . "') 
               ORDER BY name";
$admins_result = $conn->query($admins_sql);
$admins_list = $admins_result->fetch_all(MYSQLI_ASSOC);

// وظائف الإداريين حسب الدور
$admin_roles = [
    'admin' => 'مدير النظام',
    'manager' => 'مدير قسم',
    'supervisor' => 'مشرف',
    'sales' => 'مدير مبيعات',
    'support' => 'مدير دعم'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل حركات الإداريين - لوحة التحكم</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
                <!-- شاشة حركات الإداريين -->
                <div id="admin-activity-view" class="admin-activity-view">
                    <div class="page-title">
                        <h2>سجل حركات الإداريين</h2>
                        <div class="date"><?php echo date('l، j F Y'); ?></div>
                    </div>

                    <!-- بطاقات الإحصائيات -->
                    <div class="stats-cards">
                        <div class="stat-card card-1">
                            <div class="stat-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_admins']; ?></h3>
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
                                <h3><?php echo $stats['active_admins']; ?></h3>
                                <p>الإداريين النشطين</p>
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
                                <h3><?php echo $stats['login_today']; ?></h3>
                                <p>عمليات تسجيل الدخول اليوم</p>
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
                                <h3><?php echo $stats['total_activities_today']; ?></h3>
                                <p>إجمالي العمليات اليوم</p>
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
                                    <span class="stat-value"><?php echo $stats['total_admins']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الإداريين النشطين</span>
                                    <span class="stat-value"><?php echo $stats['active_admins']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الدخول اليوم</span>
                                    <span class="stat-value"><?php echo $stats['login_today']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الخروج اليوم</span>
                                    <span class="stat-value"><?php echo $stats['logout_today']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الإداريين النشطين اليوم</span>
                                    <span class="stat-value"><?php echo $stats['admins_active_today']; ?></span>
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
                                    <input type="text" class="form-control" id="search-input" 
                                           placeholder="بحث في السجل..." style="width: 250px;">
                                </div>
                            </div>

                            <!-- الفلاتر -->
                            <form method="GET" action="" class="filters">
                                <div class="filter-group">
                                    <label for="admin-filter">الإداري</label>
                                    <select id="admin-filter" name="admin_id" class="filter-control">
                                        <option value="">جميع الإداريين</option>
                                        <?php foreach ($admins_list as $admin): ?>
                                            <option value="<?php echo $admin['id']; ?>" 
                                                <?php echo (isset($_GET['admin_id']) && $_GET['admin_id'] == $admin['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($admin['name']); ?> 
                                                (<?php echo $admin_roles[$admin['user_type']] ?? $admin['user_type']; ?>)
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
                                        <option value="download" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'download') ? 'selected' : ''; ?>>تنزيل</option>
                                        <option value="upload" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'upload') ? 'selected' : ''; ?>>رفع</option>
                                        <option value="print" <?php echo (isset($_GET['activity_type']) && $_GET['activity_type'] == 'print') ? 'selected' : ''; ?>>طباعة</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="date-filter">التاريخ</label>
                                    <input type="date" id="date-filter" name="activity_date" class="filter-control" 
                                           value="<?php echo isset($_GET['activity_date']) ? $_GET['activity_date'] : date('Y-m-d'); ?>">
                                </div>
                                <div class="filter-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary" style="height: 42px;">
                                        <i class="fas fa-filter"></i> تطبيق الفلاتر
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table" id="admin-activities-table">
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
                                        <?php if (empty($activities)): ?>
                                            <tr>
                                                <td colspan="6" style="text-align: center; padding: 40px;">
                                                    <i class="fas fa-info-circle" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                                    <p>لا توجد حركات إداريين لعرضها</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($activities as $activity): ?>
                                                <?php if (!empty($activity['activity_id'])): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($activity['name']); ?></strong><br>
                                                        <small style="color: #666;"><?php echo htmlspecialchars($activity['email']); ?></small>
                                                        <br>
                                                        <span class="user-status status-<?php echo $activity['user_status']; ?>">
                                                            <?php 
                                                                $status_text = [
                                                                    'active' => 'نشط',
                                                                    'inactive' => 'غير نشط',
                                                                    'pending' => 'قيد الانتظار'
                                                                ];
                                                                echo $status_text[$activity['user_status']] ?? $activity['user_status'];
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="role-badge role-<?php echo $activity['user_type']; ?>">
                                                            <?php echo $admin_roles[$activity['user_type']] ?? $activity['user_type']; ?>
                                                        </span>
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
                                                                    'view' => 'عرض',
                                                                    'download' => 'تنزيل',
                                                                    'upload' => 'رفع',
                                                                    'print' => 'طباعة'
                                                                ];
                                                                echo $activity_types[$activity['activity_type']] ?? $activity['activity_type'];
                                                            ?>
                                                        </span>
                                                        <?php if ($activity['activity_status'] != 'success'): ?>
                                                            <br>
                                                            <small style="color: #e74c3c;">(<?php echo $activity['activity_status']; ?>)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="max-width: 300px; word-wrap: break-word;">
                                                        <?php echo htmlspecialchars($activity['activity_details']); ?>
                                                        <?php if (!empty($activity['ip_address'])): ?>
                                                            <br>
                                                            <small style="color: #7f8c8d;">
                                                                <i class="fas fa-network-wired"></i> 
                                                                <?php echo $activity['ip_address']; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            $date = new DateTime($activity['activity_time']);
                                                            echo $date->format('d/m/Y H:i:s');
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="action-btn view" 
                                                                    onclick="viewAdminActivity(<?php echo $activity['activity_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="action-btn print" 
                                                                    onclick="printActivity(<?php echo $activity['activity_id']; ?>)">
                                                                <i class="fas fa-print"></i>
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
    <div class="modal" id="view-admin-activity-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تفاصيل نشاط الإداري</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="admin-activity-details-content">
                <!-- سيتم تحميل المحتوى هنا عبر AJAX -->
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

    <script>
        // التحكم في الشريط الجانبي
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // البحث في الجدول
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#admin-activities-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // إنشاء تقرير شامل
        document.getElementById('generate-admin-report-btn').addEventListener('click', function() {
            alert('جاري إنشاء التقرير الشامل... سيتم تحميله خلال لحظات.');
            // يمكن إضافة دالة لإنشاء PDF أو Excel
            generateAdminReport();
        });

        // عرض تفاصيل النشاط
        function viewAdminActivity(activityId) {
            fetch(`get_admin_activity_details.php?id=${activityId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('admin-activity-details-content').innerHTML = data;
                    document.getElementById('view-admin-activity-modal').classList.add('active');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء جلب التفاصيل');
                });
        }

        // طباعة النشاط
        function printActivity(activityId) {
            if (confirm('هل تريد طباعة كشف الحركة؟')) {
                window.open(`print_activity.php?id=${activityId}`, '_blank');
            }
        }

        // إنشاء تقرير
        function generateAdminReport() {
            const formData = new FormData();
            formData.append('type', 'admin_activities');
            formData.append('date', document.getElementById('date-filter').value);
            
            fetch('generate_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'admin_activities_report_' + new Date().toISOString().split('T')[0] + '.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء إنشاء التقرير');
            });
        }

        // إغلاق النافذة المنبثقة
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('view-admin-activity-modal').classList.remove('active');
            });
        });

        // إغلاق النافذة عند الضغط على ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('view-admin-activity-modal').classList.remove('active');
            }
        });
    </script>
</body>
</html>