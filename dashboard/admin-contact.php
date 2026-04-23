<?php
require_once 'config/database.php';

// التحقق من صلاحية المشرف
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// جلب الإحصائيات
$statsQuery = "
    SELECT 
        COUNT(*) as total_messages,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_messages,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_messages,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_messages,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_messages,
        SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_messages,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_messages
    FROM contact_messages
";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// جلب الرسائل مع التفاصيل
$messagesQuery = "
    SELECT m.*, s.name as subject_name, u.username as user_name
    FROM contact_messages m
    LEFT JOIN contact_subjects s ON m.subject_id = s.id
    LEFT JOIN users u ON m.user_id = u.id
    ORDER BY 
        CASE WHEN priority = 'urgent' THEN 1
             WHEN priority = 'high' THEN 2
             WHEN priority = 'normal' THEN 3
             ELSE 4 END,
        created_at DESC
    LIMIT 100
";
$messagesResult = $conn->query($messagesQuery);

// معالجة تغيير حالة الرسالة
if (isset($_GET['action']) && isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    switch($action) {
        case 'read':
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            break;
        case 'reply':
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
            break;
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            break;
        case 'urgent':
            $stmt = $conn->prepare("UPDATE contact_messages SET priority = 'urgent' WHERE id = ?");
            break;
        default:
            $stmt = false;
    }
    
    if ($stmt) {
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-contact.php");
        exit;
    }
}

// معالجة إرسال رد
if (isset($_POST['send_reply'])) {
    $message_id = intval($_POST['message_id']);
    $response = $conn->real_escape_string(trim($_POST['response']));
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("
        UPDATE contact_messages 
        SET response = ?, status = ?, response_by = ?, response_at = NOW() 
        WHERE id = ?
    ");
    $admin_id = $_SESSION['admin_id'] ?? 1;
    $stmt->bind_param("ssii", $response, $status, $admin_id, $message_id);
    
    if ($stmt->execute()) {
        $success_message = "تم إرسال الرد بنجاح!";
        
        // إرسال الرد بالبريد الإلكتروني
        $emailQuery = "SELECT email, name FROM contact_messages WHERE id = ?";
        $emailStmt = $conn->prepare($emailQuery);
        $emailStmt->bind_param("i", $message_id);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        $messageData = $emailResult->fetch_assoc();
        $emailStmt->close();
        
        if ($messageData) {
            sendReplyEmail($messageData['email'], $messageData['name'], $response);
        }
    } else {
        $error_message = "حدث خطأ أثناء إرسال الرد: " . $conn->error;
    }
    $stmt->close();
}

// دالة إرسال رد بالبريد
function sendReplyEmail($to_email, $to_name, $response) {
    $subject = "رد على رسالتك - Be Pretty";
    
    $email_body = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px; }
                .response { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-right: 4px solid #667eea; }
                .footer { text-align: center; color: #666; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Be Pretty</h2>
                    <p>رد على رسالتك</p>
                </div>
                <div class='content'>
                    <h3>عزيزي/عزيزتي $to_name،</h3>
                    <p>نشكرك على تواصلك معنا. إليك ردنا على رسالتك:</p>
                    
                    <div class='response'>
                        $response
                    </div>
                    
                    <p>إذا كان لديك أي استفسارات أخرى، لا تتردد في التواصل معنا مرة أخرى.</p>
                    
                    <p>مع أطيب التحيات،<br>
                    <strong>فريق الدعم</strong><br>
                    Be Pretty</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Be Pretty. جميع الحقوق محفوظة.</p>
                    <p>هذا رد على رسالتك في موقع Be Pretty. يرجى عدم الرد على هذا البريد.</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: دعم Be Pretty <support@bepretty.com>" . "\r\n";
    $headers .= "Reply-To: no-reply@bepretty.com" . "\r\n";
    
    mail($to_email, $subject, $email_body, $headers);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التواصل | لوحة التحكم</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Summernote CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #6c757d;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fc;
            color: #333;
        }
        
        /* الشريط الجانبي */
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0 0 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border-right: 3px solid transparent;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-right-color: var(--success-color);
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-right-color: var(--success-color);
        }
        
        .sidebar-menu i {
            width: 20px;
            text-align: center;
            margin-left: 10px;
        }
        
        /* المحتوى الرئيسي */
        .main-content {
            margin-right: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        /* البطاقات */
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
            transition: transform 0.3s;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            border-radius: 15px 15px 0 0;
        }
        
        .stats-card-body {
            padding: 1.5rem;
        }
        
        /* الألوان للبطاقات */
        .card-primary {
            border-left: 5px solid var(--primary-color);
        }
        
        .card-success {
            border-left: 5px solid var(--success-color);
        }
        
        .card-danger {
            border-left: 5px solid var(--danger-color);
        }
        
        .card-warning {
            border-left: 5px solid var(--warning-color);
        }
        
        .card-info {
            border-left: 5px solid var(--info-color);
        }
        
        /* العناوين */
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e3e6f0;
        }
        
        /* الجدول */
        .dataTable {
            width: 100% !important;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .dataTable th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .dataTable td {
            padding: 12px 15px;
            vertical-align: middle;
            border-top: 1px solid #e3e6f0;
        }
        
        .dataTable tr:hover {
            background-color: #f8f9fc;
        }
        
        /* الشارات */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-new {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-read {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .badge-replied {
            background-color: #fff3e0;
            color: #f57c00;
        }
        
        .badge-pending {
            background-color: #fce4ec;
            color: #c2185b;
        }
        
        .priority-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .priority-urgent {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }
        
        .priority-high {
            background-color: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffe0b2;
        }
        
        .priority-normal {
            background-color: #e8f5e9;
            color: #388e3c;
            border: 1px solid #c8e6c9;
        }
        
        .priority-low {
            background-color: #f5f5f5;
            color: #616161;
            border: 1px solid #e0e0e0;
        }
        
        /* الأزرار */
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 3px;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: scale(1.1);
        }
        
        /* المودال */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
        
        /* البحث والتصفية */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        /* متجاوب */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .stats-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- الشريط الجانبي -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">Be Pretty</h4>
            <small class="text-white-50">لوحة التحكم</small>
        </div>
        
        <ul class="sidebar-menu mt-4">
            <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
            <li><a href="admin-products.php"><i class="fas fa-box"></i> المنتجات</a></li>
            <li><a href="admin-categories.php"><i class="fas fa-th-large"></i> الفئات</a></li>
            <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
            <li><a href="admin-users.php"><i class="fas fa-users"></i> المستخدمين</a></li>
            <li><a href="admin-blogs.php"><i class="fas fa-blog"></i> المدونة</a></li>
            <li><a href="admin-contact.php" class="active"><i class="fas fa-comments"></i> التواصل</a></li>
            <li><a href="admin-about.php"><i class="fas fa-info-circle"></i> من نحن</a></li>
            <li><a href="admin-settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <!-- العنوان والإحصائيات -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title"><i class="fas fa-comments me-2"></i> إدارة التواصل</h2>
            <div class="d-flex gap-2">
                <a href="contact.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-eye me-1"></i> معاينة الصفحة
                </a>
                <a href="admin-contact-methods.php" class="btn btn-primary">
                    <i class="fas fa-cog me-1"></i> إدارة وسائل التواصل
                </a>
            </div>
        </div>

        <!-- الإحصائيات -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card card-primary">
                    <div class="stats-card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    إجمالي الرسائل
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo number_format($stats['total_messages']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-envelope fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card card-success">
                    <div class="stats-card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    رسائل اليوم
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo number_format($stats['today_messages']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card card-warning">
                    <div class="stats-card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    رسائل جديدة
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo number_format($stats['new_messages']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bell fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card card-danger">
                    <div class="stats-card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    عاجل
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo number_format($stats['urgent_messages']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- قسم التصفية -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i> تصفية الرسائل</h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="بحث في الرسائل...">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">جميع الحالات</option>
                        <option value="new">جديد</option>
                        <option value="read">مقروء</option>
                        <option value="replied">تم الرد</option>
                        <option value="pending">قيد الانتظار</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="priorityFilter">
                        <option value="">جميع الأولويات</option>
                        <option value="urgent">عاجل</option>
                        <option value="high">عالي</option>
                        <option value="normal">عادي</option>
                        <option value="low">منخفض</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" id="dateFilter">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-secondary w-100" id="resetFilters">
                        <i class="fas fa-redo me-1"></i> إعادة تعيين
                    </button>
                </div>
            </div>
        </div>

        <!-- جدول الرسائل -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">الرسائل الواردة</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-success" onclick="exportMessages('csv')">
                        <i class="fas fa-file-csv me-1"></i> تصدير CSV
                    </button>
                    <button class="btn btn-sm btn-info" onclick="exportMessages('excel')">
                        <i class="fas fa-file-excel me-1"></i> تصدير Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover dataTable" id="messagesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المرسل</th>
                                <th>البريد الإلكتروني</th>
                                <th>الموضوع</th>
                                <th>الحالة</th>
                                <th>الأولوية</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($message = $messagesResult->fetch_assoc()): 
                                $statusClass = 'badge-' . $message['status'];
                                $priorityClass = 'priority-' . ($message['priority'] ?? 'normal');
                            ?>
                            <tr class="<?php echo $message['status'] == 'new' ? 'table-info' : ''; ?>">
                                <td><?php echo $message['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    <?php if(!empty($message['user_name'])): ?>
                                    <br><small class="text-muted">مستخدم: <?php echo htmlspecialchars($message['user_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($message['email']); ?>
                                    </a>
                                    <?php if(!empty($message['phone'])): ?>
                                    <br><small><i class="fas fa-phone text-muted me-1"></i><?php echo htmlspecialchars($message['phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($message['subject_name'] ?? $message['subject_other'] ?? 'غير محدد'); ?></div>
                                    <small class="text-muted"><?php echo substr(htmlspecialchars($message['message']), 0, 50); ?>...</small>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php 
                                        $statusLabels = [
                                            'new' => 'جديد',
                                            'read' => 'مقروء',
                                            'replied' => 'تم الرد',
                                            'pending' => 'قيد الانتظار',
                                            'resolved' => 'تم الحل'
                                        ];
                                        echo $statusLabels[$message['status']] ?? $message['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge <?php echo $priorityClass; ?>">
                                        <?php 
                                        $priorityLabels = [
                                            'urgent' => 'عاجل',
                                            'high' => 'عالي',
                                            'normal' => 'عادي',
                                            'low' => 'منخفض'
                                        ];
                                        echo $priorityLabels[$message['priority']] ?? 'عادي';
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('Y/m/d', strtotime($message['created_at'])); ?><br>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($message['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-action btn-info" 
                                                onclick="viewMessage(<?php echo $message['id']; ?>)"
                                                title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn btn-action btn-success"
                                                onclick="replyMessage(<?php echo $message['id']; ?>)"
                                                title="رد على الرسالة">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        
                                        <?php if($message['status'] == 'new'): ?>
                                        <a href="?action=read&id=<?php echo $message['id']; ?>" 
                                           class="btn btn-action btn-primary"
                                           title="تحديد كمقروء">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if($message['priority'] != 'urgent'): ?>
                                        <a href="?action=urgent&id=<?php echo $message['id']; ?>" 
                                           class="btn btn-action btn-warning"
                                           title="تعيين كعاجل">
                                            <i class="fas fa-exclamation"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <a href="?action=delete&id=<?php echo $message['id']; ?>" 
                                           class="btn btn-action btn-danger"
                                           title="حذف الرسالة"
                                           onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- رسائل التنبيه -->
        <?php if(isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    </div>

    <!-- مودال عرض الرسالة -->
    <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">تفاصيل الرسالة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="messageDetails">
                    <!-- سيتم ملؤه بالجافاسكريبت -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" onclick="replyMessage(currentMessageId)">
                        <i class="fas fa-reply me-1"></i> رد على الرسالة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال الرد على الرسالة -->
    <div class="modal fade" id="replyMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">رد على الرسالة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="replyForm">
                    <div class="modal-body">
                        <input type="hidden" name="message_id" id="replyMessageId">
                        
                        <div class="mb-3">
                            <label for="replyStatus" class="form-label">تحديد الحالة</label>
                            <select class="form-select" id="replyStatus" name="status" required>
                                <option value="replied">تم الرد</option>
                                <option value="pending">قيد الانتظار</option>
                                <option value="resolved">تم الحل</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="response" class="form-label">الرد</label>
                            <textarea class="form-control" id="response" name="response" rows="8" required 
                                      placeholder="اكتب ردك هنا..."></textarea>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email" checked>
                            <label class="form-check-label" for="sendEmail">
                                إرسال الرد بالبريد الإلكتروني للمرسل
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" name="send_reply" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> إرسال الرد
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-ar-AR.min.js"></script>
    
    <script>
        // تهيئة DataTable
        let messagesTable;
        $(document).ready(function() {
            messagesTable = $('#messagesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                },
                pageLength: 25,
                order: [[0, 'desc']],
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>><"row"<"col-md-12"tr>><"row"<"col-md-6"i><"col-md-6"p>>'
            });
            
            // تفعيل محرر النصوص
            $('#response').summernote({
                height: 200,
                lang: 'ar-AR',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // البحث في الجدول
            $('#searchInput').on('keyup', function() {
                messagesTable.search(this.value).draw();
            });
            
            // تصفية حسب الحالة
            $('#statusFilter').on('change', function() {
                messagesTable.column(4).search(this.value).draw();
            });
            
            // تصفية حسب الأولوية
            $('#priorityFilter').on('change', function() {
                messagesTable.column(5).search(this.value).draw();
            });
            
            // تصفية حسب التاريخ
            $('#dateFilter').on('change', function() {
                if (this.value) {
                    messagesTable.column(6).search(this.value).draw();
                }
            });
            
            // إعادة تعيين الفلاتر
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#statusFilter').val('');
                $('#priorityFilter').val('');
                $('#dateFilter').val('');
                messagesTable.search('').columns().search('').draw();
            });
            
            // تحديث عداد الرسائل الجديدة
            setInterval(updateNewMessagesCount, 30000); // كل 30 ثانية
        });
        
        let currentMessageId = null;
        
        // عرض تفاصيل الرسالة
        function viewMessage(messageId) {
            currentMessageId = messageId;
            
            $.ajax({
                url: 'get-message-details.php',
                method: 'GET',
                data: { id: messageId },
                success: function(response) {
                    $('#messageDetails').html(response);
                    $('#viewMessageModal').modal('show');
                },
                error: function() {
                    alert('حدث خطأ أثناء جلب تفاصيل الرسالة');
                }
            });
        }
        
        // الرد على الرسالة
        function replyMessage(messageId) {
            $('#replyMessageId').val(messageId);
            $('#replyMessageModal').modal('show');
            
            // تحميل الرسالة الأصلية
            $.ajax({
                url: 'get-message-details.php',
                method: 'GET',
                data: { id: messageId, simple: true },
                success: function(response) {
                    // يمكن إضافة الرسالة الأصلية كنص للرد
                }
            });
        }
        
        // تحديث عدد الرسائل الجديدة
        function updateNewMessagesCount() {
            $.ajax({
                url: 'get-new-messages-count.php',
                method: 'GET',
                success: function(count) {
                    if (count > 0) {
                        $('#newMessagesCount').text(count);
                        $('#newMessagesCount').removeClass('d-none');
                        
                        // إشعار صوتي إذا كانت هناك رسائل جديدة
                        if (count > parseInt($('#newMessagesCount').data('last-count') || 0)) {
                            playNotificationSound();
                        }
                        $('#newMessagesCount').data('last-count', count);
                    } else {
                        $('#newMessagesCount').addClass('d-none');
                    }
                }
            });
        }
        
        // تشغيل صوت الإشعار
        function playNotificationSound() {
            const audio = new Audio('notification.mp3');
            audio.play().catch(e => console.log('لا يمكن تشغيل الصوت'));
        }
        
        // تصدير البيانات
        function exportMessages(format) {
            let url = 'export-messages.php?format=' + format;
            
            // إضافة الفلاتر الحالية
            const search = $('#searchInput').val();
            const status = $('#statusFilter').val();
            const priority = $('#priorityFilter').val();
            const date = $('#dateFilter').val();
            
            if (search) url += '&search=' + encodeURIComponent(search);
            if (status) url += '&status=' + status;
            if (priority) url += '&priority=' + priority;
            if (date) url += '&date=' + date;
            
            window.open(url, '_blank');
        }
        
        // إرسال النموذج بالجافاسكريبت
        $('#replyForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'save-reply.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert('تم إرسال الرد بنجاح!');
                        $('#replyMessageModal').modal('hide');
                        location.reload();
                    } else {
                        alert('حدث خطأ: ' + result.error);
                    }
                },
                error: function() {
                    alert('حدث خطأ أثناء إرسال الرد');
                }
            });
        });
    </script>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>