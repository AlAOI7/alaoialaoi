<?php
session_start();
require_once 'config.php';

// التحقق من وجود بيانات التحقق في الجلسة
if (!isset($_SESSION['verification_email']) || !isset($_SESSION['verification_code'])) {
    header('Location: register.php');
    exit();
}

$email = $_SESSION['verification_email'];
$verification_code = $_SESSION['verification_code'];
$user_name = $_SESSION['user_name'] ?? '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = trim($_POST['verification_code']);
    
    if (empty($entered_code)) {
        $error = "الرجاء إدخال رمز التحقق";
    } elseif ($entered_code == $verification_code) {
        try {
            // تفعيل الحساب في قاعدة البيانات
            $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, status = 'active' WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                // جلب بيانات المستخدم لتسجيل الدخول التلقائي
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // تسجيل الدخول التلقائي
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['logged_in'] = true;
                    
                    // تنظيف بيانات الجلسة
                    unset($_SESSION['verification_email']);
                    unset($_SESSION['verification_code']);
                    unset($_SESSION['user_name']);
                    
                    // رسالة نجاح
                    $_SESSION['success_message'] = "تم تفعيل حسابك بنجاح! مرحباً بك في متجر Be Pretty";
                    
                    // توجيه إلى الصفحة الرئيسية
                    header('Location: home.php');
                    exit();
                } else {
                    $error = "تعذر العثور على المستخدم";
                }
            } else {
                $error = "لم يتم العثور على المستخدم أو حدث خطأ في التحديث";
            }
        } catch (PDOException $e) {
            $error = "حدث خطأ في تفعيل الحساب: " . $e->getMessage();
        }
    } else {
        $error = "رمز التحقق غير صحيح";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من الحساب - Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .verification-header {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .verification-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
        }
        .code-display {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #ff3366;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }
        .verification-code {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff3366;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            direction: ltr;
        }
        .instruction-box {
            background: #fff3f6;
            border-right: 5px solid #ff3366;
            padding: 1.2rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }
        .btn-verify {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            border: none;
            padding: 14px 30px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 102, 0.4);
        }
        .input-code {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 10px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            direction: ltr;
        }
        .input-code:focus {
            border-color: #ff3366;
            box-shadow: 0 0 0 0.25rem rgba(255, 51, 102, 0.25);
        }
        .timer-box {
            background: #fff8e1;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 1rem;
            margin: 1.5rem 0;
        }
        .timer {
            font-weight: bold;
            color: #ff3366;
            font-size: 1.3rem;
        }
        .user-welcome {
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .btn-copy {
            background: #ff3366;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-copy:hover {
            background: #e62e5c;
            transform: scale(1.05);
        }
        .resend-btn {
            background: #6c757d;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            color: white;
            transition: all 0.3s;
        }
        .resend-btn:not(:disabled):hover {
            background: #5a6268;
        }
        .alert-info {
            background: #e3f2fd;
            border-right: 5px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card verification-card shadow-lg">
                    <div class="verification-header">
                        <div class="verification-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3>تفعيل حسابك</h3>
                        <p class="user-welcome">مرحباً <?php echo htmlspecialchars($user_name); ?></p>
                        <p class="mb-0">تم إنشاء حسابك بنجاح! يرجى تفعيل حسابك للبدء</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> 
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> 
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="instruction-box">
                            <h5><i class="fas fa-info-circle me-2"></i>معلومات التحقق</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="fas fa-envelope me-2"></i>البريد الإلكتروني:</strong></p>
                                    <p class="text-primary"><?php echo htmlspecialchars($email); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="fas fa-user me-2"></i>الاسم:</strong></p>
                                    <p><?php echo htmlspecialchars($user_name); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="code-display">
                            <p class="mb-3"><i class="fas fa-shield-alt me-2"></i>رمز التحقق الخاص بك:</p>
                            <div class="verification-code" id="verificationCode">
                                <?php 
                                $formatted_code = chunk_split($verification_code, 3, ' ');
                                echo htmlspecialchars($formatted_code);
                                ?>
                            </div>
                            <button class="btn btn-copy mt-3" onclick="copyCode()" id="copyBtn">
                                <i class="fas fa-copy me-2"></i>نسخ الرمز
                            </button>
                        </div>

                        <div class="timer-box">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2"><i class="fas fa-clock me-2"></i>هذا الرمز صالح لمدة:</p>
                                    <div class="timer" id="countdown">15:00</div>
                                    <small class="text-muted">سيتم إنشاء رمز جديد بعد انتهاء الوقت</small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-hourglass-half" style="font-size: 2.5rem; color: #ff3366;"></i>
                                </div>
                            </div>
                        </div>

                     <form method="POST" action="" id="verificationForm">
    <div class="mb-4">
        <label for="verification_code" class="form-label fw-bold mb-3">
            <i class="fas fa-key me-2"></i>أدخل رمز التحقق
        </label>
        <input type="text" 
               class="form-control form-control-lg input-code" 
               id="verification_code" 
               name="verification_code" 
               maxlength="7" 
               placeholder="123456"
               required
               autocomplete="off"
               autofocus
               inputmode="numeric">
        <div class="form-text text-center mt-2">
            أدخل الرمز المكون من 6 أرقام
        </div>
        <div class="text-danger small mt-2 d-none" id="codeError">
            <i class="fas fa-exclamation-circle me-1"></i> الرمز يجب أن يكون 6 أرقام فقط
        </div>
    </div>

    <div class="d-grid gap-3">
        <button type="submit" class="btn btn-verify btn-lg py-3" id="submitBtn">
            <i class="fas fa-check-circle me-2"></i> تأكيد وتفعيل الحساب
        </button>
        
        <button type="button" class="btn resend-btn py-3" onclick="resendCode()" id="resendBtn" disabled>
            <i class="fas fa-redo me-2"></i> إعادة إرسال الرمز 
            <span id="resendTimer" class="ms-2"></span>
        </button>
        
        <a href="register.php" class="btn btn-outline-secondary py-3">
            <i class="fas fa-arrow-right me-2"></i> العودة للتسجيل
        </a>
    </div>
</form>


                        <div class="alert alert-info mt-4">
                            <h5><i class="fas fa-lightbulb me-2"></i>ملاحظات هامة:</h5>
                            <ul class="mb-0 ps-3">
                                <li class="mb-2">الرمز صالح لمدة <strong>15 دقيقة</strong> فقط</li>
                                <li class="mb-2">يمكنك نسخ الرمز من الأعلى ولصقه في الحقل</li>
                                <li class="mb-2">بعد التفعيل سيتم تسجيل دخولك تلقائياً</li>
                                <li>في حال عدم تفعيل الحساب، سيبقى معلقاً حتى التأكيد</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // إضافة السكريبتات المطلوبة
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('verification_code');
    const codeError = document.getElementById('codeError');
    const submitBtn = document.getElementById('submitBtn');
    
    // السماح فقط بالأرقام
    codeInput.addEventListener('input', function(e) {
        // إزالة أي حروف غير رقمية
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // تحديد الطول الأقصى
        if (this.value.length > 6) {
            this.value = this.value.substring(0, 6);
        }
        
        // إضافة مسافات للعرض بعد كل 3 أرقام
        const value = this.value.replace(/\s/g, '');
        if (value.length > 3) {
            this.value = value.replace(/(\d{3})(\d{0,3})/, '$1 $2');
        }
        
        // التحقق من الطول وإظهار الخطأ
        const cleanValue = this.value.replace(/\s/g, '');
        if (cleanValue.length === 6) {
            codeError.classList.add('d-none');
            codeInput.classList.remove('is-invalid');
            codeInput.classList.add('is-valid');
        } else {
            codeInput.classList.remove('is-valid');
        }
    });
    
    // التحكم في مفاتيح لوحة المفاتيح
    codeInput.addEventListener('keydown', function(e) {
        // السماح بـ: أرقام، مسافة (space)، backspace، delete، أزرار الأسهم
        const allowedKeys = [
            'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 
            'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'
        ];
        
        if (allowedKeys.includes(e.key)) {
            return true;
        }
        
        // السماح فقط بالأرقام
        if (!/\d/.test(e.key) && e.key !== ' ') {
            e.preventDefault();
            return false;
        }
        
        // إذا كان المستخدم يحاول كتابة رقم بعد اكتمال 6 أرقام
        if (/\d/.test(e.key)) {
            const cleanValue = this.value.replace(/\s/g, '');
            if (cleanValue.length >= 6) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // التحقق من المسافة (space) - لمنعها
    codeInput.addEventListener('keypress', function(e) {
        if (e.key === ' ') {
            e.preventDefault();
            return false;
        }
    });
    
    // عند ترك الحقل (blur)
    codeInput.addEventListener('blur', function() {
        const cleanValue = this.value.replace(/\s/g, '');
        if (cleanValue.length < 6 && cleanValue.length > 0) {
            codeError.classList.remove('d-none');
            codeInput.classList.add('is-invalid');
        }
    });
    
    // عند التركيز (focus)
    codeInput.addEventListener('focus', function() {
        codeError.classList.add('d-none');
        codeInput.classList.remove('is-invalid');
    });
    
    // التحقق قبل الإرسال
    document.getElementById('verificationForm').addEventListener('submit', function(e) {
        const codeInput = document.getElementById('verification_code');
        const cleanValue = codeInput.value.replace(/\s/g, '');
        
        if (cleanValue.length !== 6) {
            e.preventDefault();
            codeError.classList.remove('d-none');
            codeInput.classList.add('is-invalid');
            codeInput.focus();
            
            // هزة للحقل للإشارة للخطأ
            codeInput.style.animation = 'shake 0.5s';
            setTimeout(() => {
                codeInput.style.animation = '';
            }, 500);
            
            return false;
        }
        
        // إزالة المسافات قبل الإرسال
        codeInput.value = cleanValue;
        
        // تعطيل الزر لمنع الإرسال المتعدد
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري التفعيل...';
    });
    
    // إضافة CSS للهزة
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .input-code.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        }
        .input-code.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        }
        .input-code {
            background-repeat: no-repeat;
            background-position: right calc(.375em + .1875rem) center;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem);
        }
    `;
    document.head.appendChild(style);
    
    // تركيز تلقائي مع تحديد النص
    setTimeout(() => {
        codeInput.focus();
        codeInput.select();
    }, 100);
});
        // مؤقت العد التنازلي للصفحة (15 دقيقة)
        let countdown = 15 * 60;
        const countdownElement = document.getElementById('countdown');
        
        // مؤقت إعادة الإرسال (5 دقائق)
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');
        let resendCountdown = 5 * 60;
        
        // تحديث العد التنازلي الرئيسي
        function updateCountdown() {
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            countdownElement.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                countdownElement.textContent = "انتهى الوقت";
                countdownElement.className = "timer text-danger";
                document.getElementById('verificationCode').innerHTML = 
                    '<span class="text-danger">انتهت صلاحية الرمز</span>';
            } else {
                countdown--;
            }
        }
        
        // تحديث مؤقت إعادة الإرسال
        function updateResendTimer() {
            const minutes = Math.floor(resendCountdown / 60);
            const seconds = resendCountdown % 60;
            resendTimer.textContent = `(${minutes}:${seconds.toString().padStart(2, '0')})`;
            
            if (resendCountdown <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendBtn.classList.remove('bg-secondary');
                resendBtn.classList.add('bg-primary');
                resendTimer.textContent = '';
                resendBtn.innerHTML = '<i class="fas fa-redo me-2"></i> إعادة إرسال الرمز';
            } else {
                resendCountdown--;
            }
        }
        
        // بدء المؤقتات
        let countdownInterval = setInterval(updateCountdown, 1000);
        let resendInterval = setInterval(updateResendTimer, 1000);
        
        // تحديث فوري عند التحميل
        updateCountdown();
        updateResendTimer();

        // نسخ الرمز إلى الحافظة
        function copyCode() {
            const code = '<?php echo $verification_code; ?>';
            navigator.clipboard.writeText(code).then(() => {
                const btn = document.getElementById('copyBtn');
                const originalHTML = btn.innerHTML;
                const originalClass = btn.className;
                
                btn.innerHTML = '<i class="fas fa-check me-2"></i>تم النسخ!';
                btn.className = 'btn btn-success mt-3';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.className = originalClass;
                }, 2000);
            }).catch(err => {
                console.error('فشل في النسخ: ', err);
                alert('تعذر نسخ الرمز، حاول يدوياً');
            });
        }

        // إعادة إرسال الرمز
        function resendCode() {
            if (resendBtn.disabled) return;
            
            // تعطيل الزر مؤقتاً
            resendBtn.disabled = true;
            resendBtn.classList.remove('bg-primary');
            resendBtn.classList.add('bg-secondary');
            
            // إعادة تعيين المؤقتات
            countdown = 15 * 60;
            resendCountdown = 5 * 60;
            
            // إعادة تعيين المؤقتات على الشاشة
            updateCountdown();
            updateResendTimer();
            
            // إعادة بدء المؤقتات
            clearInterval(countdownInterval);
            clearInterval(resendInterval);
            countdownInterval = setInterval(updateCountdown, 1000);
            resendInterval = setInterval(updateResendTimer, 1000);
            
            // إنشاء رمز جديد (في الواقع يجب أن يكون عبر AJAX)
            const newCode = generateNewCode();
            document.getElementById('verificationCode').textContent = 
                newCode.match(/.{1,3}/g).join(' ');
            
            // إظهار رسالة نجاح
            alert('تم إنشاء رمز تحقق جديد: ' + newCode);
            
            // في الواقع الفعلي، يجب إرسال طلب AJAX إلى السيرفر
            sendNewCodeRequest(newCode);
        }
        
        // توليد رمز جديد (مؤقت للعرض)
        function generateNewCode() {
            return Math.floor(100000 + Math.random() * 900000).toString();
        }
        
        // إرسال طلب AJAX لإنشاء رمز جديد في السيرفر
        function sendNewCodeRequest(newCode) {
            fetch('resend_verification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: '<?php echo $email; ?>',
                    new_code: newCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('فشل في تحديث الرمز:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // التحقق من صحة الإدخال
        document.getElementById('verification_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
        });

        // إضافة مسافات للعرض فقط (لا تتدخل مع القيمة المرسلة)
        document.getElementById('verification_code').addEventListener('keyup', function(e) {
            if (e.key !== 'Backspace' && e.key !== 'Delete') {
                const value = this.value.replace(/\s/g, '');
                if (value.length === 6) {
                    this.value = value.replace(/(\d{3})(\d{3})/, '$1 $2');
                }
            }
        });

        // التحقق قبل الإرسال
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            const codeInput = document.getElementById('verification_code');
            const codeValue = codeInput.value.replace(/\s/g, '');
            
            if (codeValue.length !== 6) {
                e.preventDefault();
                alert('الرجاء إدخال رمز مكون من 6 أرقام');
                codeInput.focus();
                return false;
            }
            
            // التأكد من إزالة المسافات قبل الإرسال
            codeInput.value = codeValue;
            
            // التحقق من صحة الرمز (اختياري)
            if (codeValue !== '<?php echo $verification_code; ?>') {
                if (!confirm('رمز التحقق غير مطابق للرمز المعروض. هل تريد المتابعة؟')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // التركيز التلقائي على حقل الإدخال
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('verification_code').focus();
        });
        
        // إضافة اختصارات لوحة المفاتيح
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'c') {
                e.preventDefault();
                copyCode();
            }
            if (e.key === 'F5') {
                e.preventDefault();
                if (!resendBtn.disabled) {
                    resendCode();
                }
            }
        });
    </script>
</body>
</html>