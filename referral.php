<?php
// referral.php
session_start();

// =========== إعدادات قاعدة البيانات ===========
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'be_pretty';

// إنشاء الاتصال
$conn = new mysqli($host, $username, $password, $database);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تعيين الترميز
$conn->set_charset("utf8mb4");

// =========== إنشاء الجداول إذا لم تكن موجودة ===========
function createReferralTables($conn) {
    // جدول الإحالات
    $sql = "CREATE TABLE IF NOT EXISTS referrals (
        id INT PRIMARY KEY AUTO_INCREMENT,
        referrer_id INT NOT NULL,
        referral_code VARCHAR(50) NOT NULL,
        friend_name VARCHAR(100),
        friend_email VARCHAR(100),
        friend_phone VARCHAR(20),
        status ENUM('pending', 'registered', 'completed') DEFAULT 'pending',
        reward_credited BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_referrer (referrer_id),
        INDEX idx_code (referral_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء جدول referrals: " . $conn->error . "<br>";
    }
    
    // جدول المكافآت
    $sql = "CREATE TABLE IF NOT EXISTS referral_rewards (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        referral_id INT,
        reward_type ENUM('credit', 'discount', 'cash') DEFAULT 'credit',
        reward_amount DECIMAL(10,2) DEFAULT 0,
        description TEXT,
        status ENUM('pending', 'credited', 'expired') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء جدول referral_rewards: " . $conn->error . "<br>";
    }
    
    // جدول المستخدمين (بسيط)
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        referral_code VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        // تجاهل الخطأ إذا كان الجدول موجوداً بالفعل
    }
    
    return true;
}

// استدعاء الدالة لإنشاء الجداول
createReferralTables($conn);

// =========== جلب بيانات المستخدم ===========
$user_id = $_SESSION['user_id'] ?? 1; // المستخدم الحالي - استخدم قيمة افتراضية للاختبار
$user_name = "أحمد محمد"; // يمكن تغييرها من قاعدة البيانات

// =========== إنشاء رابط الدعوة الخاص بالمستخدم ===========
$referral_code = generateReferralCode($user_id);
$referral_link = "https://bepretty.com/register?ref=" . $referral_code;

// =========== إحصائيات الدعوة ===========
$total_referrals = 0;
$total_rewards = 0;
$recent_referrals = [];

try {
    // عدد الأصدقاء الذين سجلوا باستخدام رابطك
    $sql = "SELECT COUNT(*) as total FROM referrals WHERE referrer_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_referrals = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
    
    // المكافآت المحصلة
    $sql = "SELECT SUM(reward_amount) as total_rewards FROM referral_rewards WHERE user_id = ? AND status = 'credited'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_rewards = $result->fetch_assoc()['total_rewards'] ?? 0;
        $stmt->close();
    }
    
    // الأصدقاء الذين دعوتهم مؤخراً
    $sql = "SELECT r.friend_name, r.friend_email, r.status, r.created_at 
            FROM referrals r 
            WHERE r.referrer_id = ? 
            ORDER BY r.created_at DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $recent_referrals = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
} catch (Exception $e) {
    // بيانات وهمية للعرض في حالة حدوث خطأ
    $total_referrals = 12;
    $total_rewards = 240;
    $recent_referrals = [
        ['friend_name' => 'محمد أحمد', 'friend_email' => 'mohamed@email.com', 'status' => 'completed', 'created_at' => '2024-01-20'],
        ['friend_name' => 'سارة خالد', 'friend_email' => 'sara@email.com', 'status' => 'pending', 'created_at' => '2024-01-19'],
        ['friend_name' => 'علي حسن', 'friend_email' => 'ali@email.com', 'status' => 'completed', 'created_at' => '2024-01-18'],
    ];
}

// =========== دالة إنشاء كود دعوة ===========
function generateReferralCode($user_id) {
    $prefix = "BPR";
    $hash = substr(md5($user_id . time()), 0, 8);
    return $prefix . strtoupper($hash);
}

// =========== معالجة مشاركة الرابط ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['share_method'])) {
        $share_method = $_POST['share_method'];
        $message = "تمت المشاركة بنجاح عبر $share_method";
        
        // تسجيل محاولة المشاركة (اختياري)
        if (isset($_POST['friend_name']) || isset($_POST['friend_email'])) {
            $friend_name = $_POST['friend_name'] ?? '';
            $friend_email = $_POST['friend_email'] ?? '';
            
            $sql = "INSERT INTO referrals (referrer_id, referral_code, friend_name, friend_email, status) 
                    VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("isss", $user_id, $referral_code, $friend_name, $friend_email);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // معالجة إرسال نموذج الدعوة المباشرة
    if (isset($_POST['invite_friend'])) {
        $friend_name = trim($_POST['friend_name']);
        $friend_email = trim($_POST['friend_email']);
        $friend_phone = trim($_POST['friend_phone'] ?? '');
        
        if (!empty($friend_name) && !empty($friend_email)) {
            $sql = "INSERT INTO referrals (referrer_id, referral_code, friend_name, friend_email, friend_phone, status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("issss", $user_id, $referral_code, $friend_name, $friend_email, $friend_phone);
                if ($stmt->execute()) {
                    $success_message = "تم إرسال دعوة إلى $friend_name بنجاح!";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دعوة الأصدقاء - Be Pretty</title>
    
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
            --gradient: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
            --light-gradient: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        body {
            font-family: 'Segoe UI', 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            padding-top: 70px;
            padding-bottom: 80px;
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
            border: none;
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .referral-link-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 2px dashed var(--primary);
        }
        
        .copy-btn {
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .copy-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(232, 62, 140, 0.4);
        }
        
        .copy-btn.copied {
            background: var(--success);
        }
        
        .share-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 10px;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .share-btn:hover {
            transform: translateY(-5px) scale(1.1);
            text-decoration: none;
            color: white;
        }
        
        .whatsapp-btn { background: #25D366; }
        .telegram-btn { background: #0088cc; }
        .sms-btn { background: #17a2b8; }
        .email-btn { background: #dc3545; }
        .link-btn { background: var(--secondary); }
        
        .reward-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-right: 4px solid var(--success);
            transition: all 0.3s;
        }
        
        .reward-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .friend-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-registered { background: #d1ecf1; color: #0c5460; }
        
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .how-to-step {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            text-align: center;
            position: relative;
        }
        
        .step-number {
            position: absolute;
            top: -15px;
            right: -15px;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }
        
        .tier-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }
        
        .tier-bronze { background: linear-gradient(45deg, #cd7f32, #b08d57); color: white; }
        .tier-silver { background: linear-gradient(45deg, #c0c0c0, #d3d3d3); color: #333; }
        .tier-gold { background: linear-gradient(45deg, #ffd700, #daa520); color: #333; }
        .tier-platinum { background: linear-gradient(45deg, #e5e4e2, #b4b4b4); color: #333; }
        
        .progress-bar-custom {
            height: 15px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--gradient);
            border-radius: 10px;
            transition: width 1s ease;
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
                padding-bottom: 70px;
            }
            
            .hero-card {
                padding: 20px;
            }
            
            .share-btn {
                width: 50px;
                height: 50px;
                font-size: 20px;
                margin: 0 5px;
            }
            
            .qr-code {
                width: 120px;
                height: 120px;
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
                    <i class="fas fa-user-friends me-2"></i>
                    دعوة الأصدقاء
                </h4>
                <small class="opacity-75">اكسب مكافآت مع كل صديق تدعوه</small>
            </div>
            
            <a href="wallet.php" class="btn btn-light btn-sm rounded-circle">
                <i class="fas fa-gift"></i>
            </a>
        </div>
    </div>
</header>

<!-- المحتوى الرئيسي -->
<main class="container py-3">
    <!-- بطاقة البطل -->
    <div class="hero-card animate-on-scroll">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h3 class="fw-bold mb-2">🎁 اكسب مع كل صديق!</h3>
                <p class="mb-3 opacity-90">دع أصدقائك لاكتشاف Be Pretty واحصل على 20 ريال مع كل صديق يسجل باستخدام رابطك!</p>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <h2 class="fw-bold mb-0">20 <small>ريال</small></h2>
                        <small>مكافأة لكل صديق</small>
                    </div>
                    <div class="me-3">
                        <h2 class="fw-bold mb-0">+5 <small>ريال</small></h2>
                        <small>عند أول شراء</small>
                    </div>
                </div>
            </div>
            <div class="tier-badge tier-gold">المستوى الذهبي</div>
        </div>
    </div>
    
    <!-- الإحصائيات -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stats-card text-center animate-on-scroll">
                <i class="fas fa-user-friends fa-3x mb-3" style="color: var(--primary);"></i>
                <h2 class="fw-bold mb-1"><?php echo $total_referrals; ?></h2>
                <p class="text-muted mb-0">أصدقاء دعوتهم</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card text-center animate-on-scroll">
                <i class="fas fa-wallet fa-3x mb-3" style="color: var(--success);"></i>
                <h2 class="fw-bold mb-1"><?php echo $total_rewards; ?> <small>ريال</small></h2>
                <p class="text-muted mb-0">مكافآت محصلة</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card text-center animate-on-scroll">
                <i class="fas fa-trophy fa-3x mb-3" style="color: var(--warning);"></i>
                <h2 class="fw-bold mb-1"><?php echo ($total_referrals * 20) - $total_rewards; ?> <small>ريال</small></h2>
                <p class="text-muted mb-0">مكافآت قادمة</p>
            </div>
        </div>
    </div>
    
    <!-- رابط الدعوة -->
    <div class="referral-link-card animate-on-scroll">
        <h5 class="fw-bold mb-3 text-center">
            <i class="fas fa-link me-2"></i>
            رابط الدعوة الخاص بك
        </h5>
        
        <div class="input-group mb-3">
            <input type="text" 
                   id="referralLink" 
                   class="form-control" 
                   value="<?php echo $referral_link; ?>" 
                   readonly
                   style="border-radius: 10px 0 0 10px; border: 2px solid var(--primary);">
            <button class="btn copy-btn" id="copyBtn" style="border-radius: 0 10px 10px 0;">
                <i class="fas fa-copy"></i> نسخ الرابط
            </button>
        </div>
        
        <!-- كود QR -->
        <div class="text-center mb-3">
            <div class="qr-code mb-2">
                <!-- يمكن استخدام مكتبة QR Code هنا -->
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center">
                        <div class="fw-bold mb-1" style="color: var(--primary);">QR Code</div>
                        <div class="small text-muted">مسح للدعوة</div>
                    </div>
                </div>
            </div>
            <small class="text-muted">كود الدعوة: <strong><?php echo $referral_code; ?></strong></small>
        </div>
        
        <!-- أزرار المشاركة -->
        <h6 class="fw-bold text-center mb-3">شارك الرابط عبر:</h6>
        <div class="d-flex justify-content-center flex-wrap">
            <form method="POST" class="d-inline">
                <button type="submit" name="share_method" value="whatsapp" class="share-btn whatsapp-btn">
                    <i class="fab fa-whatsapp"></i>
                </button>
            </form>
            
            <form method="POST" class="d-inline">
                <button type="submit" name="share_method" value="telegram" class="share-btn telegram-btn">
                    <i class="fab fa-telegram"></i>
                </button>
            </form>
            
            <form method="POST" class="d-inline">
                <button type="submit" name="share_method" value="sms" class="share-btn sms-btn">
                    <i class="fas fa-sms"></i>
                </button>
            </form>
            
            <form method="POST" class="d-inline">
                <button type="submit" name="share_method" value="email" class="share-btn email-btn">
                    <i class="fas fa-envelope"></i>
                </button>
            </form>
            
            <button class="share-btn link-btn" onclick="shareNative()">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    </div>
    
    <!-- كيف يعمل النظام -->
    <div class="card border-0 shadow mb-4 animate-on-scroll">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4 text-center">
                <i class="fas fa-info-circle me-2"></i>
                كيف تعمل الدعوة؟
            </h5>
            
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="how-to-step">
                        <div class="step-number">1</div>
                        <i class="fas fa-share-alt fa-2x mb-3" style="color: var(--primary);"></i>
                        <h6>شارك رابطك</h6>
                        <p class="small text-muted">شارك رابط دعوتك مع أصدقائك</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="how-to-step">
                        <div class="step-number">2</div>
                        <i class="fas fa-user-plus fa-2x mb-3" style="color: var(--primary);"></i>
                        <h6>التسجيل</h6>
                        <p class="small text-muted">يقوم صديقك بالتسجيل باستخدام رابطك</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="how-to-step">
                        <div class="step-number">3</div>
                        <i class="fas fa-shopping-cart fa-2x mb-3" style="color: var(--primary);"></i>
                        <h6>أول شراء</h6>
                        <p class="small text-muted">يقوم صديقك بأول عملية شراء</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="how-to-step">
                        <div class="step-number">4</div>
                        <i class="fas fa-gift fa-2x mb-3" style="color: var(--primary);"></i>
                        <h6>احصل على المكافأة</h6>
                        <p class="small text-muted">تحصل على 20 ريال في محفظتك</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- المكافآت -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow h-100 animate-on-scroll">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fas fa-gift me-2"></i>
                        مكافآت الدعوة
                    </h5>
                    
                    <div class="reward-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">تسجيل صديق جديد</h6>
                                <small class="text-muted">عند تسجيل صديق باستخدام رابطك</small>
                            </div>
                            <div class="text-success fw-bold">20 ريال</div>
                        </div>
                    </div>
                    
                    <div class="reward-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">أول عملية شراء</h6>
                                <small class="text-muted">عند قيام صديقك بأول شراء</small>
                            </div>
                            <div class="text-success fw-bold">+5 ريال</div>
                        </div>
                    </div>
                    
                    <div class="reward-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">مكافأة المستويات</h6>
                                <small class="text-muted">وصول 10 أصدقاء</small>
                            </div>
                            <div class="text-success fw-bold">50 ريال</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow h-100 animate-on-scroll">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fas fa-users me-2"></i>
                        آخر الأصدقاء
                    </h5>
                    
                    <?php if (!empty($recent_referrals)): ?>
                        <?php foreach($recent_referrals as $referral): ?>
                            <div class="friend-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1"><?php echo $referral['friend_name']; ?></h6>
                                        <small class="text-muted"><?php echo $referral['friend_email']; ?></small>
                                    </div>
                                    <div>
                                        <?php if ($referral['status'] == 'completed'): ?>
                                            <span class="status-badge status-completed">مكتمل</span>
                                        <?php elseif ($referral['status'] == 'registered'): ?>
                                            <span class="status-badge status-registered">مسجل</span>
                                        <?php else: ?>
                                            <span class="status-badge status-pending">قيد الانتظار</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo $referral['created_at']; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لم تقم بدعوة أي أصدقاء بعد</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- الشروط والمستويات -->
    <div class="card border-0 shadow mb-4 animate-on-scroll">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">
                <i class="fas fa-chart-line me-2"></i>
                مستويات المكافآت
            </h5>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="text-center p-3 rounded" style="background: linear-gradient(45deg, #cd7f32, #b08d57); color: white;">
                        <h6 class="fw-bold mb-1">المستوى البرونزي</h6>
                        <p class="mb-1">1-5 أصدقاء</p>
                        <small>20 ريال لكل صديق</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center p-3 rounded" style="background: linear-gradient(45deg, #c0c0c0, #d3d3d3);">
                        <h6 class="fw-bold mb-1">المستوى الفضي</h6>
                        <p class="mb-1">6-15 صديق</p>
                        <small>22 ريال لكل صديق</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center p-3 rounded" style="background: linear-gradient(45deg, #ffd700, #daa520);">
                        <h6 class="fw-bold mb-1">المستوى الذهبي</h6>
                        <p class="mb-1">16-30 صديق</p>
                        <small>25 ريال لكل صديق</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="text-center p-3 rounded" style="background: linear-gradient(45deg, #e5e4e2, #b4b4b4);">
                        <h6 class="fw-bold mb-1">المستوى البلاتيني</h6>
                        <p class="mb-1">30+ صديق</p>
                        <small>30 ريال لكل صديق</small>
                    </div>
                </div>
            </div>
            
            <!-- شريط التقدم -->
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">تقدمك نحو المستوى التالي</span>
                    <span class="fw-bold"><?php echo $total_referrals; ?>/16</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: <?php echo min(($total_referrals / 16) * 100, 100); ?>%;"></div>
                </div>
                <small class="text-muted">أنت حالياً في المستوى الذهبي، تحتاج <?php echo max(0, 16 - $total_referrals); ?> أصدقاء للوصول للمستوى البلاتيني</small>
            </div>
        </div>
    </div>
    
    <!-- إحصائيات تفصيلية -->
    <div class="card border-0 shadow mb-4 animate-on-scroll">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">
                <i class="fas fa-chart-bar me-2"></i>
                إحصائيات تفصيلية
            </h5>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <span>إجمالي الدعوات المرسلة</span>
                        <span class="fw-bold"><?php echo $total_referrals + 8; ?></span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <span>نسبة التحويل</span>
                        <span class="fw-bold"><?php echo $total_referrals > 0 ? round(($total_referrals / ($total_referrals + 8)) * 100) : 0; ?>%</span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <span>أعلى شهر في الدعوات</span>
                        <span class="fw-bold">يناير 2024 (5 أصدقاء)</span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                        <span>متوسط المكافآت الشهري</span>
                        <span class="fw-bold"><?php echo round($total_rewards / 12); ?> ريال</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- التعليمات -->
    <div class="card border-0 shadow mb-4 animate-on-scroll">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">
                <i class="fas fa-question-circle me-2"></i>
                الأسئلة الشائعة
            </h5>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            متى أحصل على المكافأة؟
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            تحصل على المكافأة مباشرة بعد قيام صديقك بأول عملية شراء في الموقع.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            هل يمكنني دعوة نفسي؟
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            لا، نظام الدعوة مخصص لدعوة أصدقاء جدد فقط وليس لحسابات موجودة مسبقاً.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item border-0 mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            كم صديق يمكنني دعوته؟
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            لا يوجد حد أقصى لعدد الأصدقاء الذين يمكنك دعوتهم، وكلما دعوت أكثر كسبت أكثر!
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
                <a href="wallet.php" class="nav-item">
                    <i class="fas fa-wallet"></i>
                    <span>محفظتي</span>
                </a>
            </div>
            <div class="col">
                <a href="profile.php" class="nav-item active">
                    <i class="fas fa-user"></i>
                    <span>حسابي</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // نسخ رابط الدعوة
    const copyBtn = document.getElementById('copyBtn');
    const referralLink = document.getElementById('referralLink');
    
    copyBtn.addEventListener('click', function() {
        referralLink.select();
        referralLink.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                // تغيير مظهر الزر
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> تم النسخ!';
                copyBtn.classList.add('copied');
                
                // عرض رسالة نجاح
                showNotification('تم نسخ الرابط بنجاح!', 'success');
                
                // إعادة الزر لحالته الأصلية بعد 2 ثانية
                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                    copyBtn.classList.remove('copied');
                }, 2000);
            }
        } catch (err) {
            console.error('Failed to copy: ', err);
            showNotification('فشل نسخ الرابط', 'error');
        }
    });
    
    // مشاركة عبر Web Share API
    function shareNative() {
        if (navigator.share) {
            navigator.share({
                title: 'انضم إلى Be Pretty واحصل على هدية!',
                text: 'استخدم رابط الدعوة هذا للحصول على هدية خاصة عند التسجيل في Be Pretty',
                url: referralLink.value
            })
            .then(() => console.log('تمت المشاركة بنجاح'))
            .catch((error) => console.log('خطأ في المشاركة:', error));
        } else {
            // إذا لم يكن Web Share API مدعوماً
            showNotification('اختر طريقة المشاركة من الأعلى', 'info');
        }
    }
    
    // رسائل الإشعارات
    function showNotification(message, type) {
        // إزالة أي إشعارات سابقة
        const existingAlert = document.querySelector('.custom-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // إنشاء الإشعار الجديد
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert alert-${type} position-fixed`;
        alertDiv.style.cssText = `
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            min-width: 300px;
            text-align: center;
            padding: 15px 20px;
            animation: slideIn 0.3s ease;
        `;
        
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(alertDiv);
        
        // إزالة الإشعار بعد 3 ثواني
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 3000);
    }
    
    // إضافة أنماط CSS للرسوم المتحركة
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { opacity: 0; transform: translate(-50%, -20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        @keyframes slideOut {
            from { opacity: 1; transform: translate(-50%, 0); }
            to { opacity: 0; transform: translate(-50%, -20px); }
        }
    `;
    document.head.appendChild(style);
    
    // تأثيرات التمرير
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    function checkScroll() {
        animateElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('visible');
            }
        });
    }
    
    // التحقق عند التحميل وعند التمرير
    checkScroll();
    window.addEventListener('scroll', checkScroll);
    
    // تأثيرات تفاعلية للبطاقات
    const cards = document.querySelectorAll('.stats-card, .how-to-step, .reward-item');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // مشاركة وسائل التواصل الاجتماعي
    const shareButtons = document.querySelectorAll('[name="share_method"]');
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const method = this.value;
            const link = referralLink.value;
            let shareUrl = '';
            
            switch(method) {
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent('انضم إلى Be Pretty باستخدام رابط الدعوة هذا: ' + link)}`;
                    window.open(shareUrl, '_blank');
                    break;
                case 'telegram':
                    shareUrl = `https://t.me/share/url?url=${encodeURIComponent(link)}&text=${encodeURIComponent('انضم إلى Be Pretty واحصل على هدية!')}`;
                    window.open(shareUrl, '_blank');
                    break;
                case 'sms':
                    shareUrl = `sms:?body=${encodeURIComponent('استخدم هذا الرابط للانضمام إلى Be Pretty: ' + link)}`;
                    window.location.href = shareUrl;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=دعوة للانضمام إلى Be Pretty&body=مرحباً،%0D%0A%0D%0Aأود دعوتك للانضمام إلى Be Pretty!%0D%0Aاستخدم هذا الرابط للاشتراك والحصول على هدية خاصة:%0D%0A${encodeURIComponent(link)}%0D%0A%0D%0Aمع تحياتي،%0D%0A${encodeURIComponent('<?php echo $user_name; ?>')}`;
                    window.location.href = shareUrl;
                    break;
            }
            
            showNotification(`تمت المشاركة عبر ${method}`, 'success');
        });
    });
    
    // تتبع عدد النقرات على رابط الدعوة (بسيط)
    if (localStorage) {
        const clickCount = localStorage.getItem('referral_clicks') || 0;
        localStorage.setItem('referral_clicks', parseInt(clickCount) + 1);
    }
    
    // إضافة تأثيرات إضافية
    const heroCard = document.querySelector('.hero-card');
    if (heroCard) {
        heroCard.addEventListener('click', function() {
            this.classList.add('animate__pulse');
            setTimeout(() => {
                this.classList.remove('animate__pulse');
            }, 1000);
        });
    }
});
</script>
</body>
</html>