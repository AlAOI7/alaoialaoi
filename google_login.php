<?php
session_start();
require_once 'config.php';

// إعدادات جوجل OAuth - سيتم تعبئتها لاحقاً
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', 'http://localhost/be-pretty/google_login.php');

if (isset($_GET['code'])) {
    try {
        // الحصول على token من جوجل
        $token = getGoogleAccessToken($_GET['code']);
        
        // الحصول على معلومات المستخدم
        $user_info = getGoogleUserInfo($token);
        
        // التحقق من صحة البيانات
        if (!isset($user_info['email'])) {
            throw new Exception('لم نتمكن من الحصول على البريد الإلكتروني من جوجل');
        }
        
        // التحقق من وجود المستخدم في قاعدة البيانات
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$user_info['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // تسجيل مستخدم جديد
            $name = $user_info['name'] ?? $user_info['email'];
            $email = $user_info['email'];
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, email_verified) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$name, $email, '', 'user']);
            $user_id = $pdo->lastInsertId();
            $user_type = 'user';
        } else {
            $user_id = $user['id'];
            $name = $user['name'];
            $user_type = $user['user_type'];
        }
        
        // تسجيل الدخول
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['email'] = $user_info['email'];
        
        // التوجيه للصفحة المناسبة
        if ($user_type == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: user_dashboard.php');
        }
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "فشل تسجيل الدخول باستخدام جوجل: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
} else {
    // إعادة توجيه إلى صفحة تسجيل الدخول بجوجل
    $auth_url = getGoogleAuthUrl();
    header('Location: ' . $auth_url);
    exit();
}

function getGoogleAuthUrl() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

function getGoogleAccessToken($code) {
    $url = 'https://oauth2.googleapis.com/token';
    
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === FALSE) {
        throw new Exception('فشل في الاتصال بخادم جوجل');
    }
    
    $token_data = json_decode($response, true);
    
    if (isset($token_data['access_token'])) {
        return $token_data['access_token'];
    } else {
        $error = $token_data['error'] ?? 'خطأ غير معروف';
        throw new Exception('فشل في الحصول على token: ' . $error);
    }
}

function getGoogleUserInfo($access_token) {
    $url = 'https://www.googleapis.com/oauth2/v2/userinfo?fields=name,email,picture';
    
    $options = [
        'http' => [
            'header' => "Authorization: Bearer $access_token\r\n"
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === FALSE) {
        throw new Exception('فشل في الحصول على معلومات المستخدم');
    }
    
    $user_info = json_decode($response, true);
    
    if (isset($user_info['email'])) {
        return $user_info;
    } else {
        throw new Exception('لم نتمكن من الحصول على معلومات المستخدم الكاملة');
    }
}
?>