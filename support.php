<?php
// support.php
session_start();

// =========== إعدادات قاعدة البيانات ===========
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'be_pretty';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// =========== إنشاء الجداول إذا لم تكن موجودة ===========
function createSupportTables($conn) {
    // جدول تذاكر الدعم
    $sql = "CREATE TABLE IF NOT EXISTS support_tickets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        ticket_number VARCHAR(20) UNIQUE,
        subject VARCHAR(200) NOT NULL,
        category ENUM('technical', 'billing', 'account', 'order', 'product', 'general') DEFAULT 'general',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        status ENUM('open', 'in_progress', 'waiting', 'resolved', 'closed') DEFAULT 'open',
        assigned_to INT,
        last_reply_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
    
    // جدول رسائل التذاكر
    $sql = "CREATE TABLE IF NOT EXISTS ticket_messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        attachments TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
        INDEX idx_ticket (ticket_id),
        INDEX idx_user_msg (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
    
    // جدول الأسئلة الشائعة
    $sql = "CREATE TABLE IF NOT EXISTS faqs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        category VARCHAR(100),
        views INT DEFAULT 0,
        helpful INT DEFAULT 0,
        not_helpful INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        order_index INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
    
    // جدول مراكز الاتصال
    $sql = "CREATE TABLE IF NOT EXISTS support_centers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        city VARCHAR(100),
        address TEXT,
        phone VARCHAR(20),
        email VARCHAR(100),
        working_hours TEXT,
        map_link TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        order_index INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
}

createSupportTables($conn);

// =========== بيانات المستخدم ===========
$user_id = $_SESSION['user_id'] ?? 1;
$user_name = "أحمد محمد";

// =========== توليد رقم تذكرة ===========
function generateTicketNumber() {
    $prefix = "TICKET";
    $random = strtoupper(substr(md5(uniqid()), 0, 8));
    return $prefix . '-' . date('Ymd') . '-' . $random;
}

// =========== جلب تذاكر المستخدم ===========
function getUserTickets($conn, $user_id, $status = null, $limit = 10) {
    $sql = "SELECT t.*, 
                   (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id = t.id) as message_count,
                   (SELECT MAX(created_at) FROM ticket_messages WHERE ticket_id = t.id) as last_message_date
            FROM support_tickets t 
            WHERE t.user_id = ?";
    
    if ($status) {
        $sql .= " AND t.status = ?";
    }
    
    $sql .= " ORDER BY t.updated_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $tickets = [];
    
    if ($stmt) {
        if ($status) {
            $stmt->bind_param("isi", $user_id, $status, $limit);
        } else {
            $stmt->bind_param("ii", $user_id, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }
        
        $stmt->close();
    }
    
    return $tickets;
}

// =========== جلب الأسئلة الشائعة ===========
function getFAQs($conn, $category = null, $limit = 10) {
    $sql = "SELECT * FROM faqs WHERE is_active = TRUE";
    
    if ($category) {
        $sql .= " AND category = ?";
    }
    
    $sql .= " ORDER BY order_index ASC, helpful DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $faqs = [];
    
    if ($stmt) {
        if ($category) {
            $stmt->bind_param("si", $category, $limit);
        } else {
            $stmt->bind_param("i", $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        
        $stmt->close();
    }
    
    // بيانات افتراضية إذا لم توجد
    if (empty($faqs)) {
        $faqs = [
            [
                'id' => 1,
                'question' => 'كيف يمكنني تتبع طلبي؟',
                'answer' => 'يمكنك تتبع طلبياتك من خلال الدخول إلى قسم "طلباتي" في حسابك، ستجد هناك تفاصيل الطلب وحالته الحالية.',
                'category' => 'order',
                'views' => 150,
                'helpful' => 120
            ],
            [
                'id' => 2,
                'question' => 'ما هي طرق الدفع المتاحة؟',
                'answer' => 'نحن نقبل الدفع عن طريق: بطاقات الائتمان (فيزا، ماستركارد، مدى)، STC Pay، تحويل بنكي، والدفع عند الاستلام.',
                'category' => 'billing',
                'views' => 200,
                'helpful' => 180
            ],
            [
                'id' => 3,
                'question' => 'كيف يمكنني إرجاع منتج؟',
                'answer' => 'لديك 14 يومًا لإرجاع المنتج من تاريخ الاستلام. توجه إلى قسم "طلباتي"، اختر الطلب المراد إرجاعه واتبع التعليمات.',
                'category' => 'order',
                'views' => 180,
                'helpful' => 150
            ]
        ];
    }
    
    return $faqs;
}

// =========== جلب مراكز الدعم ===========
function getSupportCenters($conn) {
    $sql = "SELECT * FROM support_centers WHERE is_active = TRUE ORDER BY order_index ASC";
    $result = $conn->query($sql);
    
    $centers = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $centers[] = $row;
        }
    } else {
        // بيانات افتراضية
        $centers = [
            [
                'city' => 'الرياض',
                'address' => 'حي العليا، شارع الملك فهد',
                'phone' => '0112345678',
                'email' => 'riyadh@bepretty.com',
                'working_hours' => '9 صباحاً - 10 مساءً',
                'map_link' => 'https://maps.google.com/?q=الرياض'
            ],
            [
                'city' => 'جدة',
                'address' => 'حي الروضة، شارع الأمير سلطان',
                'phone' => '0123456789',
                'email' => 'jeddah@bepretty.com',
                'working_hours' => '10 صباحاً - 11 مساءً',
                'map_link' => 'https://maps.google.com/?q=جدة'
            ]
        ];
    }
    
    return $centers;
}

// =========== جلب التذاكر ===========
$open_tickets = getUserTickets($conn, $user_id, 'open', 5);
$all_tickets = getUserTickets($conn, $user_id, null, 10);
$faqs = getFAQs($conn, null, 10);
$support_centers = getSupportCenters($conn);

// =========== معالجة إنشاء تذكرة جديدة ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $subject = trim($_POST['subject']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $message = trim($_POST['message']);
    
    if (!empty($subject) && !empty($message)) {
        $ticket_number = generateTicketNumber();
        
        // إنشاء التذكرة
        $sql = "INSERT INTO support_tickets (user_id, ticket_number, subject, category, priority) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("issss", $user_id, $ticket_number, $subject, $category, $priority);
            
            if ($stmt->execute()) {
                $ticket_id = $stmt->insert_id;
                
                // إضافة الرسالة الأولى
                $sql2 = "INSERT INTO ticket_messages (ticket_id, user_id, message) 
                         VALUES (?, ?, ?)";
                
                $stmt2 = $conn->prepare($sql2);
                
                if ($stmt2) {
                    $stmt2->bind_param("iis", $ticket_id, $user_id, $message);
                    $stmt2->execute();
                    $stmt2->close();
                }
                
                $success_message = "تم إنشاء التذكرة #$ticket_number بنجاح! سيتم الرد عليك في أقرب وقت.";
                
                // تحديث التذاكر
                $open_tickets = getUserTickets($conn, $user_id, 'open', 5);
                $all_tickets = getUserTickets($conn, $user_id, null, 10);
                
                // إعادة تعيين النموذج
                $_POST = [];
            }
            
            $stmt->close();
        }
    } else {
        $error_message = "يرجى ملء جميع الحقول المطلوبة";
    }
}

// =========== معالجة الإبلاغ عن سؤال مفيد/غير مفيد ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faq_feedback'])) {
    $faq_id = intval($_POST['faq_id']);
    $feedback_type = $_POST['feedback_type']; // helpful or not_helpful
    
    if ($faq_id > 0) {
        $column = $feedback_type === 'helpful' ? 'helpful' : 'not_helpful';
        $sql = "UPDATE faqs SET $column = $column + 1 WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $faq_id);
            $stmt->execute();
            $stmt->close();
            
            // تحديث الأسئلة الشائعة
            $faqs = getFAQs($conn, null, 10);
        }
    }
}

// =========== دالة للحصول على نص الحالة ===========
function getStatusText($status) {
    $statuses = [
        'open' => 'مفتوحة',
        'in_progress' => 'قيد المعالجة',
        'waiting' => 'بانتظار ردك',
        'resolved' => 'تم الحل',
        'closed' => 'مغلقة'
    ];
    
    return $statuses[$status] ?? $status;
}

// =========== دالة للحصول على لون الحالة ===========
function getStatusColor($status) {
    $colors = [
        'open' => 'primary',
        'in_progress' => 'warning',
        'waiting' => 'info',
        'resolved' => 'success',
        'closed' => 'secondary'
    ];
    
    return $colors[$status] ?? 'secondary';
}

// =========== دالة للحصول على نص الأولوية ===========
function getPriorityText($priority) {
    $priorities = [
        'low' => 'منخفضة',
        'medium' => 'متوسطة',
        'high' => 'عالية',
        'urgent' => 'عاجلة'
    ];
    
    return $priorities[$priority] ?? $priority;
}

// =========== دالة للحصول على لون الأولوية ===========
function getPriorityColor($priority) {
    $colors = [
        'low' => 'success',
        'medium' => 'info',
        'high' => 'warning',
        'urgent' => 'danger'
    ];
    
    return $colors[$priority] ?? 'secondary';
}

// =========== دالة للحصول على نص الفئة ===========
function getCategoryText($category) {
    $categories = [
        'technical' => 'فني',
        'billing' => 'مبيعات ودفع',
        'account' => 'حساب',
        'order' => 'طلبات',
        'product' => 'منتجات',
        'general' => 'عام'
    ];
    
    return $categories[$category] ?? $category;
}

// =========== دالة للحصول على أيقونة الفئة ===========
function getCategoryIcon($category) {
    $icons = [
        'technical' => 'fas fa-cogs',
        'billing' => 'fas fa-credit-card',
        'account' => 'fas fa-user',
        'order' => 'fas fa-shopping-bag',
        'product' => 'fas fa-box',
        'general' => 'fas fa-question-circle'
    ];
    
    return $icons[$category] ?? 'fas fa-question-circle';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدعم الفني - Be Pretty</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary: #e83e8c;
            --secondary: #6f42c1;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --danger: #dc3545;
            --gradient: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', 'Cairo', sans-serif;
            background: #f8f9fa;
            padding-top: 70px;
            padding-bottom: 80px;
            min-height: 100vh;
        }
        
        .header {
            background: var(--gradient);
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(232, 62, 140, 0.3);
        }
        
        .bottom-nav {
            background: white;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.1);
            position: fixed;
            bottom: 0;
            right: 0;
            left: 0;
            z-index: 1000;
        }
        
        .nav-item {
            color: #666;
            text-decoration: none;
            padding: 10px 5px;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .nav-item:hover, .nav-item.active {
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        .nav-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 5px;
        }
        
        .nav-item span {
            font-size: 12px;
            font-weight: 500;
        }
        
        .hero-card {
            background: var(--gradient);
            color: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(232, 62, 140, 0.3);
        }
        
        .hero-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }
        
        .ticket-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            border-right: 4px solid;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .ticket-card:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }
        
        .ticket-open { border-right-color: var(--primary); }
        .ticket-in_progress { border-right-color: var(--warning); }
        .ticket-waiting { border-right-color: var(--info); }
        .ticket-resolved { border-right-color: var(--success); }
        .ticket-closed { border-right-color: var(--secondary); }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .priority-badge {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .faq-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .category-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            background: rgba(232, 62, 140, 0.1);
            color: var(--primary);
            display: inline-block;
        }
        
        .support-center-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border-top: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .support-center-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .chat-bubble {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .chat-bubble.user {
            background: var(--primary);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        
        .chat-bubble.support {
            background: #e9ecef;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }
        
        .quick-action-btn {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .quick-action-btn:hover {
            border-color: var(--primary);
            background: rgba(232, 62, 140, 0.05);
            transform: translateY(-3px);
            text-decoration: none;
            color: inherit;
        }
        
        .tab-btn {
            background: none;
            border: none;
            padding: 10px 20px;
            color: #666;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding-right: 40px;
            border-radius: 25px;
        }
        
        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
                padding-bottom: 70px;
            }
            
            .hero-card {
                padding: 20px;
            }
            
            .chat-bubble {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>

<!-- الهيدر العلوي -->
<header class="header py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="profile.php" class="btn btn-light btn-sm rounded-circle">
                <i class="fas fa-arrow-right"></i>
            </a>
            
            <div class="text-center">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-headset me-2"></i>
                    الدعم الفني
                </h4>
                <small class="opacity-75">نحن هنا لمساعدتك</small>
            </div>
            
            <a href="notifications.php" class="btn btn-light btn-sm rounded-circle">
                <i class="fas fa-bell"></i>
            </a>
        </div>
    </div>
</header>

<!-- المحتوى الرئيسي -->
<main class="container py-3">
    <!-- رسائل النجاح والخطأ -->
    <?php if(isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- بطاقة الترحيب -->
    <div class="hero-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h3 class="fw-bold mb-2">مرحباً بك في الدعم الفني 👋</h3>
                <p class="mb-3 opacity-90">فريق الدعم جاهز لمساعدتك على مدار الساعة. كيف يمكننا خدمتك اليوم؟</p>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h5 class="fw-bold mb-0">24/7</h5>
                        <small>دعم على مدار الساعة</small>
                    </div>
                    <div class="me-3">
                        <i class="fas fa-bolt fa-2x mb-2"></i>
                        <h5 class="fw-bold mb-0">2 ساعة</h5>
                        <small>متوسط وقت الرد</small>
                    </div>
                </div>
            </div>
            <div class="bg-white text-primary rounded-circle p-3">
                <i class="fas fa-headset fa-2x"></i>
            </div>
        </div>
    </div>
    
    <!-- التبويبات -->
    <div class="d-flex border-bottom mb-4">
        <button class="tab-btn active" onclick="showTab('tickets')">
            <i class="fas fa-ticket-alt me-2"></i>تذاكري
        </button>
        <button class="tab-btn" onclick="showTab('new')">
            <i class="fas fa-plus-circle me-2"></i>تذكرة جديدة
        </button>
        <button class="tab-btn" onclick="showTab('faq')">
            <i class="fas fa-question-circle me-2"></i>أسئلة شائعة
        </button>
        <button class="tab-btn" onclick="showTab('contact')">
            <i class="fas fa-map-marker-alt me-2"></i>اتصل بنا
        </button>
    </div>
    
    <!-- تبويب التذاكر -->
    <div id="ticketsTab" class="tab-content">
        <!-- إحصائيات سريعة -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <a href="?status=open" class="stats-card text-center">
                    <div class="mb-3">
                        <span class="status-badge bg-primary"><?php echo count(array_filter($all_tickets, fn($t) => $t['status'] === 'open')); ?></span>
                    </div>
                    <h5 class="fw-bold mb-1">مفتوحة</h5>
                    <p class="text-muted small mb-0">تذاكر قيد الانتظار</p>
                </a>
            </div>
            
            <div class="col-md-3 col-6">
                <a href="?status=in_progress" class="stats-card text-center">
                    <div class="mb-3">
                        <span class="status-badge bg-warning"><?php echo count(array_filter($all_tickets, fn($t) => $t['status'] === 'in_progress')); ?></span>
                    </div>
                    <h5 class="fw-bold mb-1">قيد المعالجة</h5>
                    <p class="text-muted small mb-0">يتم مراجعتها الآن</p>
                </a>
            </div>
            
            <div class="col-md-3 col-6">
                <a href="?status=resolved" class="stats-card text-center">
                    <div class="mb-3">
                        <span class="status-badge bg-success"><?php echo count(array_filter($all_tickets, fn($t) => $t['status'] === 'resolved')); ?></span>
                    </div>
                    <h5 class="fw-bold mb-1">تم الحل</h5>
                    <p class="text-muted small mb-0">تذاكر تم حلها</p>
                </a>
            </div>
            
            <div class="col-md-3 col-6">
                <a href="#" class="stats-card text-center" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                    <div class="mb-3">
                        <span class="status-badge bg-info">+</span>
                    </div>
                    <h5 class="fw-bold mb-1">تذكرة جديدة</h5>
                    <p class="text-muted small mb-0">انشئ تذكرة جديدة</p>
                </a>
            </div>
        </div>
        
        <!-- التذاكر المفتوحة -->
        <?php if(!empty($open_tickets)): ?>
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-clock text-warning me-2"></i>
                        التذاكر المفتوحة
                    </h5>
                    <a href="tickets.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                
                <?php foreach($open_tickets as $ticket): ?>
                <a href="ticket.php?id=<?php echo $ticket['id']; ?>" class="ticket-card ticket-<?php echo $ticket['status']; ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="<?php echo getCategoryIcon($ticket['category']); ?> me-2" style="color: var(--primary);"></i>
                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($ticket['subject']); ?></h6>
                            </div>
                            <p class="text-muted small mb-2">
                                <span class="category-badge"><?php echo getCategoryText($ticket['category']); ?></span>
                                <span class="mx-2">•</span>
                                <?php echo $ticket['ticket_number']; ?>
                                <span class="mx-2">•</span>
                                <?php echo $ticket['message_count']; ?> رسائل
                            </p>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                آخر تحديث: <?php echo date('Y-m-d H:i', strtotime($ticket['updated_at'])); ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="status-badge bg-<?php echo getStatusColor($ticket['status']); ?>">
                                <?php echo getStatusText($ticket['status']); ?>
                            </span>
                            <br>
                            <span class="priority-badge bg-<?php echo getPriorityColor($ticket['priority']); ?> mt-2">
                                <?php echo getPriorityText($ticket['priority']); ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- كل التذاكر -->
        <div class="card border-0 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-list-alt text-primary me-2"></i>
                        جميع التذاكر
                    </h5>
                    <div class="search-box">
                        <input type="text" class="form-control form-control-sm" placeholder="ابحث في تذاكرك...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <?php if(!empty($all_tickets)): ?>
                    <?php foreach($all_tickets as $ticket): ?>
                    <a href="ticket.php?id=<?php echo $ticket['id']; ?>" class="ticket-card ticket-<?php echo $ticket['status']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></h6>
                                <p class="text-muted small mb-0">
                                    <?php echo $ticket['ticket_number']; ?>
                                    <span class="mx-2">•</span>
                                    <?php echo date('Y-m-d', strtotime($ticket['created_at'])); ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="status-badge bg-<?php echo getStatusColor($ticket['status']); ?>">
                                    <?php echo getStatusText($ticket['status']); ?>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد تذاكر بعد</p>
                        <button class="btn btn-primary" onclick="showTab('new')">
                            <i class="fas fa-plus-circle me-2"></i>
                            إنشاء أول تذكرة
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- تبويب تذكرة جديدة -->
    <div id="newTab" class="tab-content d-none">
        <div class="card border-0 shadow">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">
                    <i class="fas fa-plus-circle me-2"></i>
                    إنشاء تذكرة جديدة
                </h5>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">عنوان المشكلة</label>
                        <input type="text" class="form-control" name="subject" 
                               value="<?php echo $_POST['subject'] ?? ''; ?>" 
                               placeholder="ما هي المشكلة التي تواجهها؟" required>
                        <div class="form-text">كن واضحاً وموجزاً في وصف المشكلة</div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الفئة</label>
                            <select class="form-select" name="category" required>
                                <option value="">اختر فئة المشكلة</option>
                                <option value="technical" <?php echo ($_POST['category'] ?? '') === 'technical' ? 'selected' : ''; ?>>فني</option>
                                <option value="billing" <?php echo ($_POST['category'] ?? '') === 'billing' ? 'selected' : ''; ?>>مبيعات ودفع</option>
                                <option value="account" <?php echo ($_POST['category'] ?? '') === 'account' ? 'selected' : ''; ?>>حساب</option>
                                <option value="order" <?php echo ($_POST['category'] ?? '') === 'order' ? 'selected' : ''; ?>>طلبات</option>
                                <option value="product" <?php echo ($_POST['category'] ?? '') === 'product' ? 'selected' : ''; ?>>منتجات</option>
                                <option value="general" <?php echo ($_POST['category'] ?? '') === 'general' ? 'selected' : ''; ?>>عام</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الأولوية</label>
                            <select class="form-select" name="priority" required>
                                <option value="">اختر مستوى الأولوية</option>
                                <option value="low" <?php echo ($_POST['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>منخفضة</option>
                                <option value="medium" <?php echo ($_POST['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>متوسطة</option>
                                <option value="high" <?php echo ($_POST['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>عالية</option>
                                <option value="urgent" <?php echo ($_POST['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>عاجلة</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">تفاصيل المشكلة</label>
                        <textarea class="form-control" name="message" rows="6" 
                                  placeholder="صف المشكلة بالتفصيل، متى بدأت، ماذا حاولت لحلها..." required><?php echo $_POST['message'] ?? ''; ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1"></i>
                            كلما كانت المعلومات أكثر تفصيلاً، كلما كان الرد أسرع وأدق
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">مرفقات (اختياري)</label>
                        <div class="border rounded p-3 text-center">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted small mb-2">اسحب وأفلت الملفات هنا أو انقر للاختيار</p>
                            <input type="file" class="form-control d-none" id="fileInput" multiple>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-paperclip me-1"></i>
                                اختيار ملفات
                            </button>
                        </div>
                        <div class="form-text">يمكنك رفع صور أو مستندات توضح المشكلة (الحد الأقصى 5 ملفات، 2MB لكل ملف)</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="create_ticket" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            إرسال التذكرة
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="showTab('faq')">
                            <i class="fas fa-search me-2"></i>
                            تصفح الأسئلة الشائعة أولاً
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- تبويب الأسئلة الشائعة -->
    <div id="faqTab" class="tab-content d-none">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">
                    <i class="fas fa-question-circle me-2"></i>
                    الأسئلة الشائعة
                </h5>
                
                <div class="search-box mb-4">
                    <input type="text" class="form-control" placeholder="ابحث في الأسئلة الشائعة...">
                    <i class="fas fa-search"></i>
                </div>
                
                <!-- فئات الأسئلة -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button class="btn btn-sm btn-outline-primary active">الكل</button>
                    <button class="btn btn-sm btn-outline-secondary">طلبات</button>
                    <button class="btn btn-sm btn-outline-secondary">دفع</button>
                    <button class="btn btn-sm btn-outline-secondary">حساب</button>
                    <button class="btn btn-sm btn-outline-secondary">منتجات</button>
                </div>
                
                <!-- قائمة الأسئلة -->
                <?php foreach($faqs as $faq): ?>
                <div class="faq-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($faq['question']); ?></h6>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($faq['answer']); ?></p>
                            <div class="d-flex align-items-center">
                                <span class="category-badge"><?php echo getCategoryText($faq['category'] ?? 'general'); ?></span>
                                <span class="text-muted small ms-3">
                                    <i class="far fa-eye me-1"></i>
                                    <?php echo $faq['views']; ?> مشاهدات
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                <button type="submit" name="faq_feedback" value="helpful" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span class="ms-1"><?php echo $faq['helpful']; ?></span>
                                </button>
                            </form>
                            <form method="POST" class="d-inline ms-1">
                                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                <button type="submit" name="faq_feedback" value="not_helpful" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span class="ms-1"><?php echo $faq['not_helpful']; ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- لم تجد إجابتك -->
                <div class="text-center py-4">
                    <i class="fas fa-search fa-2x text-muted mb-3"></i>
                    <h6 class="fw-bold mb-2">لم تجد إجابتك؟</h6>
                    <p class="text-muted mb-3">فريق الدعم مستعد للإجابة على جميع استفساراتك</p>
                    <button class="btn btn-primary" onclick="showTab('new')">
                        <i class="fas fa-headset me-2"></i>
                        تواصل مع الدعم
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- تبويب اتصل بنا -->
    <div id="contactTab" class="tab-content d-none">
        <!-- طرق الاتصال السريعة -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="tel:+966112345678" class="quick-action-btn">
                    <i class="fas fa-phone-alt fa-2x mb-3 text-primary"></i>
                    <h6 class="fw-bold mb-1">اتصال هاتفي</h6>
                    <p class="text-muted small mb-0">+966 11 234 5678</p>
                    <small class="text-primary">اتصال فوري</small>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="mailto:support@bepretty.com" class="quick-action-btn">
                    <i class="fas fa-envelope fa-2x mb-3 text-primary"></i>
                    <h6 class="fw-bold mb-1">بريد إلكتروني</h6>
                    <p class="text-muted small mb-0">support@bepretty.com</p>
                    <small class="text-primary">رد خلال 24 ساعة</small>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="https://wa.me/966501234567" class="quick-action-btn" target="_blank">
                    <i class="fab fa-whatsapp fa-2x mb-3 text-success"></i>
                    <h6 class="fw-bold mb-1">واتساب</h6>
                    <p class="text-muted small mb-0">+966 50 123 4567</p>
                    <small class="text-success">دردشة مباشرة</small>
                </a>
            </div>
        </div>
        
        <!-- مراكز الدعم -->
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    مراكز الدعم
                </h5>
                
                <?php foreach($support_centers as $center): ?>
                <div class="support-center-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-map-pin fa-lg text-danger me-2"></i>
                                <h6 class="fw-bold mb-0"><?php echo $center['city']; ?></h6>
                            </div>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo $center['address']; ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo $center['phone']; ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-envelope me-2"></i>
                                <?php echo $center['email']; ?>
                            </p>
                            <p class="text-muted mb-3">
                                <i class="fas fa-clock me-2"></i>
                                <?php echo $center['working_hours']; ?>
                            </p>
                            <a href="<?php echo $center['map_link']; ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-directions me-1"></i>
                                اتبع الاتجاهات
                            </a>
                        </div>
                        <div class="text-end">
                            <div class="bg-light rounded p-2 text-center">
                                <i class="fas fa-users fa-2x text-muted"></i>
                                <p class="small text-muted mb-0 mt-2">خدمة عملاء</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- أوقات الاستجابة -->
        <div class="card border-0 shadow">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">
                    <i class="fas fa-clock me-2"></i>
                    أوقات الاستجابة المتوقعة
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary rounded-circle p-2 me-3">
                                    <i class="fas fa-phone-alt text-white"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">الاتصال الهاتفي</h6>
                                    <small class="text-muted">فوري</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">خط ساخن يعمل 24/7</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success rounded-circle p-2 me-3">
                                    <i class="fab fa-whatsapp text-white"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">واتساب</h6>
                                    <small class="text-muted">دقائق</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">رد خلال 15 دقيقة</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-danger rounded-circle p-2 me-3">
                                    <i class="fas fa-envelope text-white"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">بريد إلكتروني</h6>
                                    <small class="text-muted">24 ساعة</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">رد خلال يوم عمل</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-warning rounded-circle p-2 me-3">
                                    <i class="fas fa-ticket-alt text-white"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">تذاكر الدعم</h6>
                                    <small class="text-muted">2-4 ساعات</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">متوسط وقت الرد</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- التنقل السفلي -->
<nav class="bottom-nav py-2">
    <div class="container">
        <div class="row">
            <div class="col">
                <a href="home.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>الرئيسية</span>
                </a>
            </div>
            <div class="col">
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>الفئات</span>
                </a>
            </div>
            <div class="col">
                <a href="cart.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>السلة</span>
                </a>
            </div>
            <div class="col">
                <a href="support.php" class="nav-item active">
                    <i class="fas fa-headset"></i>
                    <span>الدعم</span>
                </a>
            </div>
            <div class="col">
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-user"></i>
                    <span>حسابي</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- مودال تذكرة جديدة سريعة -->
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تذكرة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">ماهي مشكلتك؟</label>
                        <select class="form-select" name="category" required>
                            <option value="">اختر نوع المشكلة</option>
                            <option value="order">مشكلة في الطلب</option>
                            <option value="product">استفسار عن منتج</option>
                            <option value="technical">مشكلة تقنية</option>
                            <option value="billing">مشكلة في الدفع</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">اشرح مشكلتك باختصار</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="اكتب هنا..." required></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="create_ticket" class="btn btn-primary" onclick="setQuickSubject()">
                            <i class="fas fa-paper-plane me-2"></i>
                            إرسال التذكرة
                        </button>
                    </div>
                    
                    <input type="hidden" name="subject" id="quickSubject" value="مشكلة سريعة">
                    <input type="hidden" name="priority" value="medium">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // وظيفة التبويبات
    window.showTab = function(tabName) {
        // إخفاء جميع التبويبات
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('d-none');
        });
        
        // إظهار التبويب المطلوب
        document.getElementById(tabName + 'Tab').classList.remove('d-none');
        
        // تحديث أزرار التبويب
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        event.target.classList.add('active');
    };
    
    // تعيين موضوع سريع للتذكرة
    window.setQuickSubject = function() {
        const categorySelect = document.querySelector('#newTicketModal select[name="category"]');
        const categoryText = categorySelect.options[categorySelect.selectedIndex].text;
        document.getElementById('quickSubject').value = 'مشكلة في ' + categoryText;
    };
    
    // تأثيرات التمرير
    const ticketCards = document.querySelectorAll('.ticket-card, .faq-item, .support-center-card');
    ticketCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = this.classList.contains('ticket-card') ? 
                'translateX(-5px)' : 
                this.classList.contains('faq-item') ? 
                'translateX(5px)' : 
                'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(0)';
        });
    });
    
    // البحث في الأسئلة الشائعة
    const faqSearch = document.querySelector('#faqTab input');
    if (faqSearch) {
        faqSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('h6').textContent.toLowerCase();
                const answer = item.querySelector('p').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // البحث في التذاكر
    const ticketSearch = document.querySelector('#ticketsTab input');
    if (ticketSearch) {
        ticketSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const ticketItems = document.querySelectorAll('#ticketsTab .ticket-card');
            
            ticketItems.forEach(item => {
                const subject = item.querySelector('h6').textContent.toLowerCase();
                const ticketNumber = item.querySelector('p').textContent.toLowerCase();
                
                if (subject.includes(searchTerm) || ticketNumber.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // فئات الأسئلة الشائعة
    const faqCategories = document.querySelectorAll('#faqTab .btn');
    faqCategories.forEach(btn => {
        btn.addEventListener('click', function() {
            faqCategories.forEach(b => b.classList.remove('active', 'btn-primary'));
            faqCategories.forEach(b => b.classList.add('btn-outline-secondary'));
            
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-primary');
            
            // هنا يمكنك إضافة فلترة حسب الفئة
        });
    });
    
    // تحميل ملفات المرفقات
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            if (files.length > 0) {
                alert(`تم اختيار ${files.length} ملف(ات) للمرفق`);
            }
        });
    }
    
    // تأثيرات عند تحميل الصفحة
    setTimeout(() => {
        document.querySelector('.hero-card').classList.add('animate__animated', 'animate__fadeInUp');
    }, 100);
});
</script>
</body>
</html>