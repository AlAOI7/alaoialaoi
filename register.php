<?php
session_start();
require_once 'config.php';

// إذا كان المستخدم مسجل الدخول بالفعل، توجيهه
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    // التحقق من البيانات المطلوبة
    $required_fields = [
        'first_name' => 'الاسم الأول',
        'last_name' => 'الاسم الأخير',
        'username' => 'اسم المستخدم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'confirm_password' => 'تأكيد كلمة المرور'
    ];
    
    foreach ($required_fields as $field => $field_name) {
        if (empty($$field)) {
            $error = "حقل {$field_name} مطلوب";
            break;
        }
    }
    
    // إذا لم يكن هناك خطأ في الحقول المطلوبة، تابع التحقق
    if (empty($error)) {
        if ($password !== $confirm_password) {
            $error = "كلمات المرور غير متطابقة";
        } elseif (strlen($password) < 6) {
            $error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "البريد الإلكتروني غير صالح";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = "اسم المستخدم يجب أن يحتوي على أحرف إنجليزية وأرقام وشرطة سفلية فقط";
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = "اسم المستخدم يجب أن يكون بين 3 و 50 حرفاً";
        } else {
            try {
                // التحقق من وجود البريد الإلكتروني أو اسم المستخدم
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$email, $username]);
                
                if ($stmt->rowCount() > 0) {
                    // التحقق أيهما مستخدم
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->rowCount() > 0) {
                        $error = "البريد الإلكتروني مسجل مسبقاً";
                    } else {
                        $error = "اسم المستخدم مسجل مسبقاً";
                    }
                } else {
                    // إنشاء الاسم الكامل من الاسم الأول والأخير
                    if (empty($name)) {
                        $name = $first_name . ' ' . $last_name;
                    }
                    
                    // إنشاء اسم العرض إذا لم يتم تقديمه
                    $display_name = !empty($name) ? $name : $username;
                    
                    // إنشاء رمز التحقق
                    $verification_code = rand(100000, 999999);
                    
                    // تشفير كلمة المرور
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // إدخال المستخدم في قاعدة البيانات
                    $stmt = $pdo->prepare("INSERT INTO users 
                        (first_name, last_name, name, username, display_name, email, phone, password, 
                         user_type, verification_code, email_verified, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user', ?, 0, 'pending', NOW())");
                    
                    if ($stmt->execute([
                        $first_name, 
                        $last_name, 
                        $name, 
                        $username, 
                        $display_name, 
                        $email, 
                        $phone, 
                        $hashed_password, 
                        $verification_code
                    ])) {
                        // حفظ بيانات التحقق في الجلسة
                        $_SESSION['verification_email'] = $email;
                        $_SESSION['user_name'] = $display_name;
                        $_SESSION['username'] = $username;
                        $_SESSION['verification_code'] = $verification_code;
                        
                        // توجيه إلى صفحة التحقق
                        header('Location: manual_verification.php');
                        exit();
                    } else {
                        $error = "حدث خطأ أثناء إنشاء الحساب";
                    }
                }
            } catch (PDOException $e) {
                $error = "حدث خطأ في النظام: " . $e->getMessage();
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
    <title>إنشاء حساب جديد - Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 20px auto;
        }
        .register-header {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .register-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .form-step {
            display: none;
            animation: fadeIn 0.5s;
        }
        .form-step.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ddd;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            z-index: 2;
            transition: all 0.3s;
        }
        .step.active {
            background: #ff3366;
            color: white;
            transform: scale(1.1);
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .step-line {
            position: absolute;
            top: 50%;
            left: 20px;
            right: 20px;
            height: 2px;
            background: #ddd;
            transform: translateY(-50%);
            z-index: 1;
        }
        .progress-line {
            position: absolute;
            top: 50%;
            left: 20px;
            height: 2px;
            background: #ff3366;
            transform: translateY(-50%);
            z-index: 1;
            transition: width 0.3s;
        }
        .btn-pretty {
            background: linear-gradient(135deg, #ff3366, #ff6699);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-pretty:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 102, 0.3);
        }
        .password-strength {
            height: 4px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s;
        }
        .strength-0 { width: 0%; background: #dc3545; }
        .strength-1 { width: 25%; background: #dc3545; }
        .strength-2 { width: 50%; background: #ffc107; }
        .strength-3 { width: 75%; background: #28a745; }
        .strength-4 { width: 100%; background: #28a745; }
        
        .username-check {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .username-available {
            color: #28a745;
        }
        .username-taken {
            color: #dc3545;
        }
        
        .form-control:focus {
            border-color: #ff3366;
            box-shadow: 0 0 0 0.25rem rgba(255, 51, 102, 0.25);
        }
        
        .terms-link {
            color: #ff3366;
            text-decoration: none;
            font-weight: bold;
        }
        
        .terms-link:hover {
            text-decoration: underline;
        }
        
        .name-suggestions {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            display: none;
        }
        
        .name-suggestion {
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .name-suggestion:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card register-card">
                    <div class="register-header">
                        <div class="register-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3>إنشاء حساب جديد</h3>
                        <p class="mb-0">انضم إلينا واستمتع بخصومات حصرية</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="step-indicator">
                            <div class="step-line"></div>
                            <div class="progress-line" id="progressLine"></div>
                            <div class="step active" id="step1-indicator">1</div>
                            <div class="step" id="step2-indicator">2</div>
                            <div class="step" id="step3-indicator">3</div>
                        </div>

                        <form id="registerForm" method="POST" action="">
                            <!-- الخطوة 1: البيانات الشخصية -->
                            <div class="form-step active" id="step1">
                                <div class="mb-4">
                                    <label for="first_name" class="form-label fw-bold mb-3">
                                        <i class="fas fa-user me-2"></i>الاسم الأول *
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" required
                                           placeholder="أحمد" 
                                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="last_name" class="form-label fw-bold mb-3">
                                        <i class="fas fa-user me-2"></i>الاسم الأخير *
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" required
                                           placeholder="محمد" 
                                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-bold mb-3">
                                        <i class="fas fa-user-tag me-2"></i>الاسم الكامل (اختياري)
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name"
                                           placeholder="أحمد محمد (سيتم إنشاؤه تلقائياً)" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    <div class="form-text">
                                        <small>سيتم إنشاء الاسم الكامل تلقائياً من الاسم الأول والأخير</small>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-pretty w-100 py-3" onclick="nextStep()">
                                    التالي <i class="fas fa-arrow-left ms-2"></i>
                                </button>
                            </div>
                            
                            <!-- الخطوة 2: بيانات الحساب -->
                            <div class="form-step" id="step2">
                                <div class="mb-4">
                                    <label for="username" class="form-label fw-bold mb-3">
                                        <i class="fas fa-at me-2"></i>اسم المستخدم *
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="username" name="username" required
                                           placeholder="ahmed123" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                           oninput="checkUsernameAvailability(this.value)">
                                    <div class="form-text">
                                        <small>يجب أن يحتوي على أحرف إنجليزية وأرقام وشرطة سفلية فقط (3-50 حرفاً)</small>
                                    </div>
                                    <div class="username-check" id="usernameCheck"></div>
                                    <div class="name-suggestions" id="usernameSuggestions"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-bold mb-3">
                                        <i class="fas fa-envelope me-2"></i>البريد الإلكتروني *
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required
                                           placeholder="example@email.com" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="phone" class="form-label fw-bold mb-3">
                                        <i class="fas fa-phone me-2"></i>رقم الهاتف (اختياري)
                                    </label>
                                    <input type="tel" class="form-control form-control-lg" id="phone" name="phone"
                                           placeholder="05XXXXXXXX" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                
                                <div class="d-grid gap-3">
                                    <button type="button" class="btn btn-outline-secondary py-3" onclick="prevStep()">
                                        <i class="fas fa-arrow-right me-2"></i> السابق
                                    </button>
                                    <button type="button" class="btn btn-pretty py-3" onclick="nextStep()">
                                        التالي <i class="fas fa-arrow-left ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- الخطوة 3: كلمة المرور والشروط -->
                            <div class="form-step" id="step3">
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-bold mb-3">
                                        <i class="fas fa-lock me-2"></i>كلمة المرور *
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required
                                           placeholder="أدخل كلمة مرور قوية" 
                                           oninput="checkPasswordStrength(this.value)">
                                    <div class="password-strength" id="passwordStrength"></div>
                                    <div class="form-text">
                                        <small class="text-muted">يجب أن تحتوي على 6 أحرف على الأقل</small>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-bold mb-3">
                                        <i class="fas fa-lock me-2"></i>تأكيد كلمة المرور *
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required
                                           placeholder="أعد إدخال كلمة المرور">
                                    <div class="form-text" id="passwordMatch"></div>
                                </div>
                                
                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        أوافق على 
                                        <a href="terms.php" class="terms-link">الشروط والأحكام</a> 
                                        و 
                                        <a href="privacy.php" class="terms-link">سياسة الخصوصية</a>
                                    </label>
                                </div>
                                
                                <div class="d-grid gap-3">
                                    <button type="button" class="btn btn-outline-secondary py-3" onclick="prevStep()">
                                        <i class="fas fa-arrow-right me-2"></i> السابق
                                    </button>
                                    <button type="submit" class="btn btn-pretty py-3" id="submitBtn">
                                        <i class="fas fa-user-plus me-2"></i> إنشاء حساب
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="text-center mt-5 pt-4 border-top">
                            <p class="mb-3">لديك حساب بالفعل؟</p>
                            <a href="login.php" class="btn btn-outline-danger w-100 py-3">
                                <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 3;
        let usernameAvailable = false;
        
        function updateProgress() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressLine').style.width = progress + '%';
            
            // تحديث مؤشرات الخطوات
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step${i}-indicator`);
                indicator.classList.toggle('active', currentStep >= i);
                indicator.classList.toggle('completed', currentStep > i);
            }
        }
        
        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;
            updateProgress();
            
            // تعبئة الاسم الكامل تلقائياً إذا كان فارغاً في الخطوة الأولى
            if (step === 1) {
                updateFullName();
            }
        }
        
        function nextStep() {
            // التحقق من صحة بيانات الخطوة الحالية
            let valid = true;
            let errorMessage = '';
            
            if (currentStep === 1) {
                const firstName = document.getElementById('first_name').value.trim();
                const lastName = document.getElementById('last_name').value.trim();
                
                if (!firstName) {
                    errorMessage = 'الرجاء إدخال الاسم الأول';
                    document.getElementById('first_name').focus();
                    valid = false;
                } else if (!lastName) {
                    errorMessage = 'الرجاء إدخال الاسم الأخير';
                    document.getElementById('last_name').focus();
                    valid = false;
                }
            } else if (currentStep === 2) {
                const username = document.getElementById('username').value.trim();
                const email = document.getElementById('email').value.trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const usernamePattern = /^[a-zA-Z0-9_]+$/;
                
                if (!username) {
                    errorMessage = 'الرجاء إدخال اسم المستخدم';
                    document.getElementById('username').focus();
                    valid = false;
                } else if (username.length < 3 || username.length > 50) {
                    errorMessage = 'اسم المستخدم يجب أن يكون بين 3 و 50 حرفاً';
                    document.getElementById('username').focus();
                    valid = false;
                } else if (!usernamePattern.test(username)) {
                    errorMessage = 'اسم المستخدم يجب أن يحتوي على أحرف إنجليزية وأرقام وشرطة سفلية فقط';
                    document.getElementById('username').focus();
                    valid = false;
                } else if (!usernameAvailable && username.length >= 3) {
                    errorMessage = 'اسم المستخدم غير متاح، يرجى اختيار اسم آخر';
                    document.getElementById('username').focus();
                    valid = false;
                } else if (!email || !emailPattern.test(email)) {
                    errorMessage = 'الرجاء إدخال بريد إلكتروني صحيح';
                    document.getElementById('email').focus();
                    valid = false;
                }
            }
            
            if (!valid) {
                alert(errorMessage);
                return;
            }
            
            if (currentStep < totalSteps) {
                showStep(currentStep + 1);
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }
        
        function updateFullName() {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const fullNameInput = document.getElementById('name');
            
            if (firstName && lastName) {
                if (!fullNameInput.value.trim()) {
                    fullNameInput.value = firstName + ' ' + lastName;
                }
            }
        }
        
        // التحقق من توفر اسم المستخدم
        function checkUsernameAvailability(username) {
            const usernameCheck = document.getElementById('usernameCheck');
            const suggestionsDiv = document.getElementById('usernameSuggestions');
            
            // تنظيف اسم المستخدم
            username = username.replace(/[^a-zA-Z0-9_]/g, '').toLowerCase();
            document.getElementById('username').value = username;
            
            if (username.length < 3) {
                usernameCheck.innerHTML = '<small class="text-muted">يجب أن يكون 3 أحرف على الأقل</small>';
                suggestionsDiv.style.display = 'none';
                usernameAvailable = false;
                return;
            }
            
            if (username.length > 50) {
                usernameCheck.innerHTML = '<small class="text-danger">لا يمكن أن يزيد عن 50 حرفاً</small>';
                suggestionsDiv.style.display = 'none';
                usernameAvailable = false;
                return;
            }
            
            // التحقق من التوفر عبر AJAX
            fetch('check_username.php?username=' + encodeURIComponent(username))
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        usernameCheck.innerHTML = '<small class="username-available"><i class="fas fa-check-circle"></i> اسم المستخدم متاح</small>';
                        usernameAvailable = true;
                        suggestionsDiv.style.display = 'none';
                    } else {
                        usernameCheck.innerHTML = '<small class="username-taken"><i class="fas fa-times-circle"></i> اسم المستخدم غير متاح</small>';
                        usernameAvailable = false;
                        
                        // عرض اقتراحات
                        showUsernameSuggestions(username);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    usernameCheck.innerHTML = '<small class="text-muted">جارٍ التحقق...</small>';
                });
        }
        
        function showUsernameSuggestions(baseUsername) {
            const suggestionsDiv = document.getElementById('usernameSuggestions');
            const suggestions = [
                baseUsername + '_2024',
                baseUsername + '123',
                baseUsername + Math.floor(Math.random() * 1000),
                'my_' + baseUsername,
                baseUsername + '_user'
            ];
            
            let html = '<small class="text-muted">اقتراحات:</small><br>';
            suggestions.forEach(suggestion => {
                html += `<div class="name-suggestion" onclick="useSuggestion('${suggestion}')">${suggestion}</div>`;
            });
            
            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
        }
        
        function useSuggestion(username) {
            document.getElementById('username').value = username;
            checkUsernameAvailability(username);
            document.getElementById('usernameSuggestions').style.display = 'none';
        }
        
        function checkPasswordStrength(password) {
            let strength = 0;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strength = Math.min(strength, 4);
            strengthBar.className = 'password-strength strength-' + strength;
            
            checkPasswordMatch();
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword) {
                if (password === confirmPassword) {
                    matchText.innerHTML = '<small class="text-success"><i class="fas fa-check"></i> كلمات المرور متطابقة</small>';
                } else {
                    matchText.innerHTML = '<small class="text-danger"><i class="fas fa-times"></i> كلمات المرور غير متطابقة</small>';
                }
            } else {
                matchText.innerHTML = '';
            }
        }
        
        // تهيئة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
            
            // تحديث الاسم الكامل عند تغيير الاسم الأول أو الأخير
            document.getElementById('first_name').addEventListener('input', updateFullName);
            document.getElementById('last_name').addEventListener('input', updateFullName);
            
            // إضافة مستمعين لأحداث الإدخال
            document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
            
            // التحقق قبل الإرسال
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const terms = document.getElementById('terms');
                if (!terms.checked) {
                    e.preventDefault();
                    alert('الرجاء الموافقة على الشروط والأحكام');
                    terms.focus();
                    return;
                }
                
                if (!usernameAvailable) {
                    e.preventDefault();
                    alert('الرجاء اختيار اسم مستخدم متاح');
                    document.getElementById('username').focus();
                }
            });
        });
    </script>
</body>
</html>