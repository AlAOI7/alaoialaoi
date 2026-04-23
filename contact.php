<?php
// =========== إعدادات قاعدة البيانات ===========
session_start();

// معلومات الاتصال بقاعدة البيانات - اضبطها حسب إعداداتك
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'be_pretty';

// إنشاء الاتصال
$conn = new mysqli($host, $username, $password, $database);

// التحقق من الاتصال
if ($conn->connect_error) {
    // إذا فشل الاتصال، استخدم بيانات وهمية للعرض
    $conn = null;
}

// تعيين الترميز
if ($conn) {
    $conn->set_charset("utf8mb4");
}

// =========== جلب البيانات من قاعدة البيانات ===========
$contact_methods = [];
$contact_subjects = [];
$contact_settings = [];

if ($conn) {
    // جلب وسائل التواصل النشطة
    $sql = "SELECT * FROM contact_methods WHERE is_active = 1 ORDER BY order_index";
    $result = $conn->query($sql);
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $contact_methods[] = $row;
        }
    }
    
    // جلب مواضيع التواصل النشطة
    $sql = "SELECT * FROM contact_subjects WHERE is_active = 1 ORDER BY order_index";
    $result = $conn->query($sql);
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $contact_subjects[] = $row;
        }
    }
    
    // جلب الإعدادات
    $sql = "SELECT setting_key, setting_value FROM contact_settings";
    $result = $conn->query($sql);
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $contact_settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

// =========== معالجة إرسال النموذج ===========
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $subject_id = intval($_POST['subject_id']);
    $message_text = trim($_POST['message']);
    
    // التحقق من البيانات
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'الاسم يجب أن يكون على الأقل حرفين';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صحيح';
    }
    
    if ($subject_id == 0) {
        $errors[] = 'يرجى اختيار موضوع الرسالة';
    }
    
    if (empty($message_text) || strlen($message_text) < 10) {
        $errors[] = 'الرسالة يجب أن تكون 10 أحرف على الأقل';
    }
    
    if (empty($errors)) {
        if ($conn) {
            // حفظ الرسالة في قاعدة البيانات
            $sql = "INSERT INTO contact_messages (name, email, phone, subject_id, message, ip_address, user_agent, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'new', NOW())";
            
            $stmt = $conn->prepare($sql);
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt->bind_param("sssisss", $name, $email, $phone, $subject_id, $message_text, $ip, $agent);
            
            if ($stmt->execute()) {
                $message = $contact_settings['contact_form_success_message'] ?? 'شكراً لك! تم إرسال رسالتك بنجاح وسنقوم بالرد في أقرب وقت.';
                $message_type = 'success';
                
                // إعادة تعيين النموذج
                $_POST = [];
            } else {
                $message = 'حدث خطأ أثناء حفظ الرسالة';
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'شكراً لك! تم إرسال رسالتك بنجاح. (وضع العرض التجريبي)';
            $message_type = 'success';
            $_POST = [];
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $contact_settings['contact_page_title'] ?? 'تواصل معنا - Be Pretty'; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #e83e8c;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', 'Tahoma', 'Geneva', 'Verdana', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            padding-top: 70px;
            padding-bottom: 80px;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
        }
        
        .bottom-nav {
            background: white;
            box-shadow: 0 -2px 15px rgba(0,0,0,0.1);
            position: fixed;
            bottom: 0;
            right: 0;
            left: 0;
            z-index: 1000;
        }
        
        .nav-item {
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            padding: 10px 5px;
            display: block;
            text-align: center;
        }
        
        .nav-item:hover, .nav-item.active {
            color: var(--primary);
        }
        
        .nav-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 5px;
        }
        
        .nav-item span {
            font-size: 12px;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        
        .social-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            color: white;
            font-size: 20px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .social-icon:hover {
            transform: scale(1.1);
            text-decoration: none;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(232, 62, 140, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d81b60);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(232, 62, 140, 0.3);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .badge {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-box {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-right: 4px solid var(--primary);
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
                padding-bottom: 70px;
            }
            
            .contact-card {
                padding: 15px;
            }
            
            .contact-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<!-- الهيدر العلوي -->
<div class="header py-2">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="home.php" class="btn btn-light btn-sm rounded-circle">
                <i class="fas fa-arrow-right"></i>
            </a>
            
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-headset text-primary me-2"></i>
                <?php echo $contact_settings['contact_page_title'] ?? 'تواصل معنا'; ?>
            </h5>
            
            <a href="profile.php" class="btn btn-light btn-sm rounded-circle">
                <i class="fas fa-user"></i>
            </a>
        </div>
    </div>
</div>

<!-- المحتوى الرئيسي -->
<main class="container py-3">
    <!-- رسالة الترحيب -->
    <div class="text-center mb-4">
        <h1 class="fw-bold mb-2" style="color: var(--primary);">
            <i class="fas fa-hands-helping me-2"></i>
            خدمة عملاء Be Pretty
        </h1>
        <p class="text-muted">
            <?php echo $contact_settings['contact_page_description'] ?? 'نحن هنا لمساعدتك! تواصل معنا بأي طريقة تناسبك.'; ?>
        </p>
    </div>
    
    <!-- رسائل التوجيه -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="info-box">
                <i class="fas fa-clock text-primary fa-lg mb-2"></i>
                <h6 class="fw-bold mb-1">ساعات العمل</h6>
                <p class="mb-0 small">
                    <?php echo $contact_settings['contact_working_hours'] ?? 'الأحد - الخميس: 9 صباحاً - 11 مساءً'; ?>
                </p>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="info-box">
                <i class="fas fa-bolt text-warning fa-lg mb-2"></i>
                <h6 class="fw-bold mb-1">سرعة الرد</h6>
                <p class="mb-0 small">
                    خلال 
                    <?php echo $contact_settings['contact_response_time'] ?? '24'; ?>
                    ساعة خلال أوقات العمل
                </p>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="info-box">
                <i class="fas fa-shield-alt text-success fa-lg mb-2"></i>
                <h6 class="fw-bold mb-1">خصوصية تامة</h6>
                <p class="mb-0 small">
                    <?php echo $contact_settings['contact_privacy_note'] ?? 'نحن نحترم خصوصيتك ولا نشارك بياناتك.'; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- رسالة النجاح/الخطأ -->
    <?php if($message): ?>
    <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-3 fa-lg"></i>
            <div>
                <?php echo $message; ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- وسائل التواصل السريعة -->
    <div class="row g-3 mb-4">
        <?php
        // إذا كانت قاعدة البيانات متصلة وتعرض البيانات
        if (!empty($contact_methods)) {
            foreach($contact_methods as $method) {
                $link = '#';
                $icon = 'fas fa-question';
                $bg_color = '#6c757d';
                
                switch($method['type']) {
                    case 'phone':
                        $link = 'tel:' . preg_replace('/[^0-9+]/', '', $method['value']);
                        $icon = $method['icon'] ?? 'fas fa-phone-alt';
                        $bg_color = $method['color'] ?? '#28a745';
                        break;
                    case 'email':
                        $link = 'mailto:' . $method['value'] . '?subject=استفسار عن Be Pretty';
                        $icon = $method['icon'] ?? 'fas fa-envelope';
                        $bg_color = $method['color'] ?? '#dc3545';
                        break;
                    case 'whatsapp':
                        $link = 'https://wa.me/' . preg_replace('/[^0-9+]/', '', $method['value']) . '?text=' . urlencode('مرحباً، أود الاستفسار عن Be Pretty');
                        $icon = $method['icon'] ?? 'fab fa-whatsapp';
                        $bg_color = $method['color'] ?? '#25d366';
                        break;
                    case 'instagram':
                        $link = $method['value'];
                        $icon = $method['icon'] ?? 'fab fa-instagram';
                        $bg_color = $method['color'] ?? '#e4405f';
                        break;
                    case 'snapchat':
                        $link = $method['value'];
                        $icon = $method['icon'] ?? 'fab fa-snapchat';
                        $bg_color = $method['color'] ?? '#fffc00';
                        break;
                    case 'tiktok':
                        $link = $method['value'];
                        $icon = $method['icon'] ?? 'fab fa-tiktok';
                        $bg_color = $method['color'] ?? '#000000';
                        break;
                    case 'location':
                        $link = '#map';
                        $icon = $method['icon'] ?? 'fas fa-map-marker-alt';
                        $bg_color = $method['color'] ?? '#007bff';
                        break;
                }
                
                echo '
                <div class="col-md-4 col-6">
                    <a href="' . $link . '" class="contact-card text-center" target="_blank">
                        <div class="contact-icon" style="background-color: ' . $bg_color . '; color: white;">
                            <i class="' . $icon . '"></i>
                        </div>
                        <h6 class="fw-bold mb-1">' . htmlspecialchars($method['title']) . '</h6>
                        <p class="text-muted small mb-0">' . htmlspecialchars($method['value']) . '</p>
                        <span class="badge mt-2" style="background-color: ' . $bg_color . '; color: white;">
                            انقر للتواصل
                        </span>
                    </a>
                </div>';
            }
        } else {
            // بيانات افتراضية للعرض
            $default_methods = [
                ['phone', 'الاتصال الهاتفي', '+966 500 000 000', 'fas fa-phone-alt', '#28a745'],
                ['whatsapp', 'واتساب', '+966 500 000 000', 'fab fa-whatsapp', '#25d366'],
                ['email', 'البريد الإلكتروني', 'info@bepretty.com', 'fas fa-envelope', '#dc3545'],
                ['instagram', 'انستجرام', '@bepretty', 'fab fa-instagram', '#e4405f'],
                ['snapchat', 'سناب شات', '@bepretty', 'fab fa-snapchat', '#fffc00'],
                ['location', 'العنوان', 'الرياض - السعودية', 'fas fa-map-marker-alt', '#007bff']
            ];
            
            foreach($default_methods as $method) {
                echo '
                <div class="col-md-4 col-6">
                    <a href="#" class="contact-card text-center">
                        <div class="contact-icon" style="background-color: ' . $method[4] . '; color: white;">
                            <i class="' . $method[3] . '"></i>
                        </div>
                        <h6 class="fw-bold mb-1">' . $method[1] . '</h6>
                        <p class="text-muted small mb-0">' . $method[2] . '</p>
                        <span class="badge mt-2" style="background-color: ' . $method[4] . '; color: white;">
                            انقر للتواصل
                        </span>
                    </a>
                </div>';
            }
        }
        ?>
    </div>
    
    <!-- روابط التواصل الاجتماعي -->
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-3"><i class="fas fa-hashtag text-primary me-2"></i>تابعنا على</h5>
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <a href="https://wa.me/966500000000" class="social-icon" style="background-color: #25d366;">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="https://instagram.com/bepretty" class="social-icon" style="background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="https://snapchat.com/add/bepretty" class="social-icon" style="background-color: #fffc00; color: #000;">
                <i class="fab fa-snapchat"></i>
            </a>
            <a href="https://tiktok.com/@bepretty" class="social-icon" style="background-color: #000000;">
                <i class="fab fa-tiktok"></i>
            </a>
            <a href="https://facebook.com/bepretty" class="social-icon" style="background-color: #1877f2;">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/bepretty" class="social-icon" style="background-color: #1da1f2;">
                <i class="fab fa-twitter"></i>
            </a>
        </div>
    </div>
    
    <!-- نموذج التواصل -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold mb-3 text-primary">
                <i class="fas fa-envelope me-2"></i>
                <?php echo $contact_settings['contact_form_title'] ?? 'أرسل لنا رسالة'; ?>
            </h4>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">الاسم الكامل *</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo $_POST['name'] ?? ''; ?>" 
                               required minlength="2" placeholder="أدخل اسمك الكامل">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">البريد الإلكتروني *</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo $_POST['email'] ?? ''; ?>" 
                               required placeholder="example@email.com">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">رقم الهاتف (اختياري)</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo $_POST['phone'] ?? ''; ?>" 
                               placeholder="05XXXXXXXX">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">موضوع الرسالة *</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">اختر موضوع الرسالة</option>
                            <?php
                            if (!empty($contact_subjects)) {
                                foreach($contact_subjects as $subject) {
                                    $selected = (isset($_POST['subject_id']) && $_POST['subject_id'] == $subject['id']) ? 'selected' : '';
                                    echo '<option value="' . $subject['id'] . '" ' . $selected . '>' . 
                                         htmlspecialchars($subject['name']) . '</option>';
                                }
                            } else {
                                $default_subjects = [
                                    ['id' => 1, 'name' => 'استفسار عام'],
                                    ['id' => 2, 'name' => 'استفسار عن منتج'],
                                    ['id' => 3, 'name' => 'استفسار عن طلب'],
                                    ['id' => 4, 'name' => 'شكوى'],
                                    ['id' => 5, 'name' => 'اقتراح'],
                                    ['id' => 6, 'name' => 'شراكة']
                                ];
                                
                                foreach($default_subjects as $subject) {
                                    $selected = (isset($_POST['subject_id']) && $_POST['subject_id'] == $subject['id']) ? 'selected' : '';
                                    echo '<option value="' . $subject['id'] . '" ' . $selected . '>' . 
                                         $subject['name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <?php if(!empty($contact_subjects)): ?>
                        <small class="text-muted">سيتم إرسال الرسالة للقسم المختص</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">رسالتك *</label>
                    <textarea name="message" class="form-control" rows="5" required 
                              minlength="10" placeholder="اكتب رسالتك هنا..."><?php echo $_POST['message'] ?? ''; ?></textarea>
                    <div class="form-text d-flex justify-content-between">
                        <span>الحد الأدنى: 10 أحرف</span>
                        <span id="charCount">0 حرف</span>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="submit_contact" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>إرسال الرسالة
                    </button>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo $contact_settings['contact_form_success_message'] ?? 'سنقوم بالرد عليك في أقرب وقت ممكن.'; ?>
                    </small>
                </div>
            </form>
        </div>
    </div>
    
    <!-- الخريطة -->
    <?php if(isset($contact_settings['contact_google_map_url']) && !empty($contact_settings['contact_google_map_url'])): ?>
    <div class="card border-0 shadow mb-4">
        <div class="card-body p-4">
            <h4 class="card-title fw-bold mb-3 text-primary">
                <i class="fas fa-map-marker-alt me-2"></i>
                موقعنا على الخريطة
            </h4>
            
            <div class="map-container">
                <iframe 
                    src="<?php echo $contact_settings['contact_google_map_url']; ?>" 
                    width="100%" 
                    height="300" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
    <?php endif; ?>
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
                <a href="order.php" class="nav-item">
                    <i class="fas fa-list-alt"></i>
                    <span>الطلبات</span>
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

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // عداد أحرف الرسالة
    const messageTextarea = document.querySelector('textarea[name="message"]');
    const charCount = document.getElementById('charCount');
    
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + ' حرف';
            
            if (length < 10) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // تحديث العداد عند التحميل
        charCount.textContent = messageTextarea.value.length + ' حرف';
    }
    
    // تأثيرات عند تمرير الماوس على بطاقات التواصل
    const contactCards = document.querySelectorAll('.contact-card');
    contactCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // تحسين تجربة الإدخال
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // زر الرجوع للخلف
    document.querySelector('.header .btn').addEventListener('click', function(e) {
        e.preventDefault();
        window.history.back();
    });
    
    // تحديث الشعارات التفاعلية
    const socialIcons = document.querySelectorAll('.social-icon');
    socialIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // التحقق من النموذج قبل الإرسال
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
                
                // إظهار رسالة الخطأ
                if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'هذا الحقل مطلوب';
                    field.parentElement.appendChild(errorDiv);
                }
            } else {
                field.classList.remove('is-invalid');
                
                // إزالة رسالة الخطأ إذا كانت موجودة
                const errorDiv = field.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
        
        // تحقق خاص للبريد الإلكتروني
        const emailField = form.querySelector('input[type="email"]');
        if (emailField && emailField.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                isValid = false;
                
                if (!emailField.parentElement.querySelector('.invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'البريد الإلكتروني غير صحيح';
                    emailField.parentElement.appendChild(errorDiv);
                }
            }
        }
        
        // تحقق خاص للرسالة
        const messageField = form.querySelector('textarea[name="message"]');
        if (messageField && messageField.value.trim().length < 10) {
            messageField.classList.add('is-invalid');
            isValid = false;
            
            if (!messageField.parentElement.querySelector('.invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'الرسالة يجب أن تكون 10 أحرف على الأقل';
                messageField.parentElement.appendChild(errorDiv);
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // التمرير للحقل الأول الذي به خطأ
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                firstError.focus();
            }
        }
    });
});
</script>
</body>
</html>