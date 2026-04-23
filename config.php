<?php

// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'be_pretty');


// إنشاء الاتصال
// try {
//     $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $pdo->exec("set names utf8");
// } catch(PDOException $e) {
//     die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
// }

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}


// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'dotnetala@gmail.com');
define('SMTP_PASSWORD', 'klvp iuya phmq hyez'); // استخدم كلمة مرور التطبيق
define('SMTP_FROM', 'dotnetala@gmail.com');
define('SMTP_FROM_NAME', 'Be Pretty');
// إعدادات جوجل OAuth (استبدل بالقيم الحقيقية من Google Cloud Console)
// إعدادات جوجل OAuth
define('GOOGLE_CLIENT_ID', '353486617608-g93mobmolt1adtl42h2tdugvum5gjsr1.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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'); 
define('GOOGLE_REDIRECT_URI', 'http://localhost/Storthory-main7/google_login.php');

// إعدادات الموقع
define('SITE_NAME', 'Be Pretty');
define('SITE_URL', 'http://localhost/altoryaphp/');
define('ADMIN_EMAIL', 'admin@bepretty.com');

// إعدادات الجلسة
// ini_set('session.cookie_lifetime', 86400); // 24 ساعة
// ini_set('session.gc_maxlifetime', 86400);

// دالة للحصول على رابط الموقع
function site_url($path = '') {
    return SITE_URL . $path;
}

// دالة للتوجيه
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// دالة لتسجيل الدخول التلقائي
function auto_login() {
    global $pdo;
    
    if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
        $token = $_COOKIE['remember_token'];
        
        $stmt = $pdo->prepare("SELECT user_id FROM user_sessions WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $session = $stmt->fetch();
        
        if ($session) {
            $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
            $user_stmt->execute([$session['user_id']]);
            $user = $user_stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // تحديث آخر نشاط
                $update_stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
                $update_stmt->execute([$user['id']]);
            }
        }
    }
}

// تفعيل تسجيل الدخول التلقائي
auto_login();

// دالة للتحقق من تسجيل الدخول
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// دالة للتحقق من صلاحيات المستخدم
function check_user_type($allowed_types = []) {
    if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowed_types)) {
        redirect('login.php');
    }
}

// دالة لتسجيل الدخول
function login_user($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        
        // تحديث آخر نشاط
        $update_stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $update_stmt->execute([$user['id']]);
        
        return true;
    }
    
    return false;
}

// دالة لتسجيل الخروج
function logout_user() {
    if (isset($_SESSION['user_id'])) {
        global $pdo;
        
        // حذف رمز التذكر إذا كان موجوداً
        if (isset($_COOKIE['remember_token'])) {
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE token = ?");
            $stmt->execute([$_COOKIE['remember_token']]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        session_destroy();
    }
}
// ���� ���� ��������� �� ����� ��������
function get_setting($key, $default = "") {
    global $pdo;
    if (!$pdo) return $default;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result["setting_value"] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

