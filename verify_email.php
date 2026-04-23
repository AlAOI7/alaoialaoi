<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['verification_email'])) {
    header('Location: register.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['verification_code'];
    $email = $_SESSION['verification_email'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // تفعيل الحساب
            $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL WHERE email = ?");
            $stmt->execute([$email]);
            
            unset($_SESSION['verification_email']);
            $_SESSION['success'] = "تم تفعيل حسابك بنجاح! يمكنك الآن تسجيل الدخول.";
            header('Location: index.php');
            exit();
        } else {
            $error = "رمز التحقق غير صحيح";
        }
    } catch (PDOException $e) {
        $error = "حدث خطأ في النظام: " . $e->getMessage();
    }
}

// إعادة إرسال الرمز
if (isset($_GET['resend'])) {
    require_once 'send_email.php';
    
    $stmt = $pdo->prepare("SELECT name, verification_code FROM users WHERE email = ?");
    $stmt->execute([$_SESSION['verification_email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if (sendVerificationEmail($_SESSION['verification_email'], $user['name'], $user['verification_code'])) {
            $success = "تم إعادة إرسال رمز التحقق إلى بريدك الإلكتروني";
        } else {
            $error = "فشل إعادة إرسال رمز التحقق";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من البريد الإلكتروني - Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .verification-code {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <div class="card shadow-lg p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-danger">التحقق من البريد الإلكتروني</h2>
                        <p class="text-muted">أدخل رمز التحقق الذي تم إرساله إلى بريدك الإلكتروني</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="verification_code" class="form-label">رمز التحقق المكون من 6 أرقام</label>
                            <input type="text" class="form-control verification-code" id="verification_code" 
                                   name="verification_code" required maxlength="6" pattern="[0-9]{6}" 
                                   placeholder="000000">
                        </div>
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-check-circle"></i> تحقق
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center">
                        <p class="text-muted">لم تستلم الرمز؟</p>
                        <a href="verify_email.php?resend=1" class="btn btn-outline-danger">
                            <i class="fas fa-redo"></i> إعادة إرسال الرمز
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تحسين تجربة إدخال رمز التحقق
        document.getElementById('verification_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>