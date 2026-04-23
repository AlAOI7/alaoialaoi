<?php
// wallet.php
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

$conn->set_charset("utf8mb4");

// =========== إنشاء الجداول إذا لم تكن موجودة ===========
function createWalletTables($conn) {
    // جدول المحفظة
    $sql = "CREATE TABLE IF NOT EXISTS wallets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        balance DECIMAL(10,2) DEFAULT 0.00,
        total_earned DECIMAL(10,2) DEFAULT 0.00,
        total_spent DECIMAL(10,2) DEFAULT 0.00,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء جدول wallets: " . $conn->error . "<br>";
    }
    
    // جدول معاملات المحفظة
    $sql = "CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        transaction_type ENUM('deposit', 'withdrawal', 'purchase', 'refund', 'reward', 'referral', 'bonus') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        reference_id VARCHAR(100),
        status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'completed',
        balance_before DECIMAL(10,2),
        balance_after DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_transactions (user_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء جدول wallet_transactions: " . $conn->error . "<br>";
    }
    
    // جدول طلبات السحب
    $sql = "CREATE TABLE IF NOT EXISTS withdrawal_requests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        withdrawal_method ENUM('bank_transfer', 'stc_pay', 'mada', 'paypal', 'vodafone_cash') NOT NULL,
        account_details TEXT,
        status ENUM('pending', 'approved', 'processing', 'completed', 'rejected') DEFAULT 'pending',
        admin_notes TEXT,
        processed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_withdrawal (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء جدول withdrawal_requests: " . $conn->error . "<br>";
    }
    
    // جدول بطاقات الائتمان
    $sql = "CREATE TABLE IF NOT EXISTS payment_cards (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        card_type ENUM('visa', 'mastercard', 'mada') NOT NULL,
        last_four VARCHAR(4),
        expiry_month VARCHAR(2),
        expiry_year VARCHAR(4),
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_cards (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء جدول payment_cards: " . $conn->error . "<br>";
    }
    
    return true;
}

// إنشاء الجداول
createWalletTables($conn);

// =========== بيانات المستخدم ===========
$user_id = $_SESSION['user_id'] ?? 1; // في الواقع الفعلي: $_SESSION['user_id']

// =========== جلب رصيد المحفظة ===========
function getWalletBalance($conn, $user_id) {
    $sql = "SELECT * FROM wallets WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $balance = 0;
    $total_earned = 0;
    $total_spent = 0;
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $wallet = $result->fetch_assoc();
            $balance = $wallet['balance'];
            $total_earned = $wallet['total_earned'];
            $total_spent = $wallet['total_spent'];
        } else {
            // إنشاء محفظة جديدة للمستخدم
            $sql = "INSERT INTO wallets (user_id, balance) VALUES (?, 0)";
            $stmt2 = $conn->prepare($sql);
            if ($stmt2) {
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $stmt2->close();
            }
        }
        $stmt->close();
    }
    
    return [
        'balance' => $balance,
        'total_earned' => $total_earned,
        'total_spent' => $total_spent
    ];
}

$wallet_data = getWalletBalance($conn, $user_id);
$balance = $wallet_data['balance'];
$total_earned = $wallet_data['total_earned'];
$total_spent = $wallet_data['total_spent'];

// =========== جلب آخر المعاملات ===========
function getRecentTransactions($conn, $user_id, $limit = 10) {
    $sql = "SELECT * FROM wallet_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $transactions = [];
    
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        $stmt->close();
    }
    
    // إذا لم توجد معاملات، عرض معاملات وهمية للعرض
    if (empty($transactions)) {
        $transactions = [
            [
                'id' => 1,
                'transaction_type' => 'reward',
                'amount' => 50.00,
                'description' => 'مكافأة التسجيل',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'id' => 2,
                'transaction_type' => 'purchase',
                'amount' => -120.50,
                'description' => 'شراء منتجات',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'id' => 3,
                'transaction_type' => 'referral',
                'amount' => 20.00,
                'description' => 'دعوة صديق',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ]
        ];
    }
    
    return $transactions;
}

$transactions = getRecentTransactions($conn, $user_id, 10);

// =========== جلب طلبات السحب ===========
function getWithdrawalRequests($conn, $user_id) {
    $sql = "SELECT * FROM withdrawal_requests 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $requests = [];
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }
    
    return $requests;
}

$withdrawal_requests = getWithdrawalRequests($conn, $user_id);

// =========== معالجة طلب إيداع ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit'])) {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];
    
    if ($amount > 0) {
        // هنا يجب تنفيذ عملية الدفع عبر بوابة الدفع
        // للمثال، سنقوم فقط بإضافة الرصيد
        
        $balance_before = $balance;
        $new_balance = $balance + $amount;
        
        // تحديث رصيد المحفظة
        $sql = "UPDATE wallets SET balance = ?, total_earned = total_earned + ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ddi", $new_balance, $amount, $user_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // تسجيل المعاملة
        $sql = "INSERT INTO wallet_transactions (user_id, transaction_type, amount, description, balance_before, balance_after) 
                VALUES (?, 'deposit', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $description = "إيداع عبر $payment_method";
            $stmt->bind_param("idsdd", $user_id, $amount, $description, $balance_before, $new_balance);
            $stmt->execute();
            $stmt->close();
        }
        
        // تحديث البيانات
        $balance = $new_balance;
        $total_earned += $amount;
        
        $success_message = "تم إيداع $amount ريال بنجاح!";
    }
}

// =========== معالجة طلب سحب ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $amount = floatval($_POST['withdraw_amount']);
    $withdrawal_method = $_POST['withdrawal_method'];
    $account_details = $_POST['account_details'] ?? '';
    
    if ($amount > 0 && $amount <= $balance) {
        // إنشاء طلب سحب
        $sql = "INSERT INTO withdrawal_requests (user_id, amount, withdrawal_method, account_details) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("idss", $user_id, $amount, $withdrawal_method, $account_details);
            if ($stmt->execute()) {
                $success_message = "تم تقديم طلب السحب بنجاح، سيتم المراجعة خلال 24-48 ساعة";
                
                // خصم المبلغ من الرصيد مؤقتاً
                $new_balance = $balance - $amount;
                
                $sql = "UPDATE wallets SET balance = ? WHERE user_id = ?";
                $stmt2 = $conn->prepare($sql);
                if ($stmt2) {
                    $stmt2->bind_param("di", $new_balance, $user_id);
                    $stmt2->execute();
                    $stmt2->close();
                }
                
                // تسجيل المعاملة
                $sql = "INSERT INTO wallet_transactions (user_id, transaction_type, amount, description, status, balance_before, balance_after) 
                        VALUES (?, 'withdrawal', ?, ?, 'pending', ?, ?)";
                $stmt3 = $conn->prepare($sql);
                if ($stmt3) {
                    $description = "طلب سحب عبر $withdrawal_method";
                    $stmt3->bind_param("idsdd", $user_id, $amount, $description, $balance, $new_balance);
                    $stmt3->execute();
                    $stmt3->close();
                }
                
                $balance = $new_balance;
                $total_spent += $amount;
            }
            $stmt->close();
        }
    } elseif ($amount > $balance) {
        $error_message = "المبلغ المطلوب يتجاوز رصيدك الحالي";
    }
}

// =========== دالة لتحويل نوع المعاملة إلى نص ===========
function getTransactionTypeText($type) {
    $types = [
        'deposit' => 'إيداع',
        'withdrawal' => 'سحب',
        'purchase' => 'شراء',
        'refund' => 'استرداد',
        'reward' => 'مكافأة',
        'referral' => 'دعوة',
        'bonus' => 'مكافأة'
    ];
    
    return $types[$type] ?? $type;
}

// =========== دالة للحصول على لون المعاملة ===========
function getTransactionColor($type, $amount) {
    if ($amount > 0) {
        return 'success';
    } else {
        return 'danger';
    }
}

// =========== دالة للحصول على أيقونة المعاملة ===========
function getTransactionIcon($type) {
    $icons = [
        'deposit' => 'fas fa-plus-circle',
        'withdrawal' => 'fas fa-minus-circle',
        'purchase' => 'fas fa-shopping-cart',
        'refund' => 'fas fa-undo',
        'reward' => 'fas fa-gift',
        'referral' => 'fas fa-user-friends',
        'bonus' => 'fas fa-star'
    ];
    
    return $icons[$type] ?? 'fas fa-exchange-alt';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محفظتي - Be Pretty</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #e83e8c;
            --secondary: #6f42c1;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --gradient: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
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
        
        .wallet-balance-card {
            background: var(--gradient);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(232, 62, 140, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .wallet-balance-card::before {
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
        
        .balance-amount {
            font-size: 3.5rem;
            font-weight: bold;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px 15px;
            border-radius: 15px;
            background: white;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            height: 100%;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-decoration: none;
            color: var(--primary);
        }
        
        .action-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .transaction-item {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            border-right: 4px solid;
            transition: all 0.3s;
        }
        
        .transaction-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .transaction-success {
            border-right-color: var(--success);
        }
        
        .transaction-danger {
            border-right-color: var(--danger);
        }
        
        .transaction-warning {
            border-right-color: var(--warning);
        }
        
        .transaction-info {
            border-right-color: var(--info);
        }
        
        .payment-method {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover, .payment-method.selected {
            border-color: var(--primary);
            background-color: rgba(232, 62, 140, 0.05);
        }
        
        .amount-box {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .amount-box:hover, .amount-box.selected {
            border-color: var(--primary);
            background-color: rgba(232, 62, 140, 0.05);
        }
        
        .modal-content {
            border-radius: 20px;
            overflow: hidden;
        }
        
        .modal-header {
            background: var(--gradient);
            color: white;
            border-bottom: none;
        }
        
        .withdrawal-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
                padding-bottom: 70px;
            }
            
            .balance-amount {
                font-size: 2.5rem;
            }
            
            .wallet-balance-card {
                padding: 20px;
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
                    <i class="fas fa-wallet me-2"></i>
                    محفظتي
                </h4>
                <small class="opacity-75">إدارة رصيدك ومكافآتك</small>
            </div>
            
            <a href="referral.php" class="btn btn-light btn-sm rounded-circle">
                <i class="fas fa-gift"></i>
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
    
    <!-- بطاقة الرصيد الرئيسية -->
    <div class="wallet-balance-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="opacity-75 mb-2">الرصيد الحالي</h5>
                <div class="balance-amount">
                    <?php echo number_format($balance, 2); ?> <small>ر.س</small>
                </div>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-calendar me-1"></i>
                    آخر تحديث: <?php echo date('Y-m-d'); ?>
                </p>
            </div>
            <div class="text-end">
                <div class="bg-white text-primary rounded-circle p-3 d-inline-block">
                    <i class="fas fa-wallet fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- إحصائيات سريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stats-card text-center">
                <i class="fas fa-money-bill-wave fa-2x mb-3" style="color: var(--success);"></i>
                <h4 class="fw-bold mb-1"><?php echo number_format($total_earned, 2); ?> <small>ر.س</small></h4>
                <p class="text-muted mb-0">إجمالي الإيرادات</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card text-center">
                <i class="fas fa-shopping-cart fa-2x mb-3" style="color: var(--danger);"></i>
                <h4 class="fw-bold mb-1"><?php echo number_format($total_spent, 2); ?> <small>ر.س</small></h4>
                <p class="text-muted mb-0">إجمالي المشتريات</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card text-center">
                <i class="fas fa-exchange-alt fa-2x mb-3" style="color: var(--info);"></i>
                <h4 class="fw-bold mb-1"><?php echo count($transactions); ?></h4>
                <p class="text-muted mb-0">عدد المعاملات</p>
            </div>
        </div>
    </div>
    
    <!-- أزرار الإجراءات السريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="fas fa-plus-circle"></i>
                <span class="fw-bold">إيداع</span>
                <small class="text-muted">زيادة الرصيد</small>
            </a>
        </div>
        
        <div class="col-md-3 col-6">
            <a href="#" class="action-btn" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                <i class="fas fa-minus-circle"></i>
                <span class="fw-bold">سحب</span>
                <small class="text-muted">تحويل للأحباب</small>
            </a>
        </div>
        
        <div class="col-md-3 col-6">
            <a href="transactions.php" class="action-btn">
                <i class="fas fa-history"></i>
                <span class="fw-bold">السجل</span>
                <small class="text-muted">جميع المعاملات</small>
            </a>
        </div>
        
        <div class="col-md-3 col-6">
            <a href="referral.php" class="action-btn">
                <i class="fas fa-user-friends"></i>
                <span class="fw-bold">دعوة</span>
                <small class="text-muted">اكسب المزيد</small>
            </a>
        </div>
    </div>
    
    <!-- آخر المعاملات -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    آخر المعاملات
                </h5>
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            
            <?php if(!empty($transactions)): ?>
                <?php foreach($transactions as $transaction): ?>
                    <?php 
                    $type_text = getTransactionTypeText($transaction['transaction_type']);
                    $icon = getTransactionIcon($transaction['transaction_type']);
                    $color_class = 'transaction-' . getTransactionColor($transaction['transaction_type'], $transaction['amount']);
                    $amount_color = $transaction['amount'] > 0 ? 'text-success' : 'text-danger';
                    $amount_sign = $transaction['amount'] > 0 ? '+' : '';
                    ?>
                    
                    <div class="transaction-item <?php echo $color_class; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="<?php echo $icon; ?> fa-lg" style="color: var(--primary);"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1"><?php echo $type_text; ?></h6>
                                    <p class="text-muted small mb-0">
                                        <?php echo $transaction['description'] ?? 'معاملة'; ?>
                                        <br>
                                        <small>
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?>
                                        </small>
                                    </p>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="fw-bold mb-0 <?php echo $amount_color; ?>">
                                    <?php echo $amount_sign . number_format($transaction['amount'], 2); ?> ر.س
                                </h6>
                                <small class="text-muted">
                                    <?php 
                                    if (isset($transaction['balance_after'])) {
                                        echo 'الرصيد: ' . number_format($transaction['balance_after'], 2) . ' ر.س';
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد معاملات بعد</p>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal">
                        <i class="fas fa-plus-circle me-2"></i>
                        ابدأ بإيداع أول
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- طلبات السحب -->
    <?php if(!empty($withdrawal_requests)): ?>
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">
                <i class="fas fa-hourglass-half me-2"></i>
                طلبات السحب قيد المعالجة
            </h5>
            
            <?php foreach($withdrawal_requests as $request): ?>
                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-2">
                    <div>
                        <h6 class="fw-bold mb-1">سحب رصيد</h6>
                        <p class="text-muted small mb-0">
                            <?php echo number_format($request['amount'], 2); ?> ر.س
                            <br>
                            <small>
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?>
                            </small>
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="withdrawal-status status-<?php echo $request['status']; ?>">
                            <?php 
                            $status_text = [
                                'pending' => 'قيد الانتظار',
                                'approved' => 'مقبول',
                                'processing' => 'قيد المعالجة',
                                'completed' => 'مكتمل',
                                'rejected' => 'مرفوض'
                            ];
                            echo $status_text[$request['status']] ?? $request['status'];
                            ?>
                        </span>
                        <p class="small text-muted mt-1 mb-0">
                            <?php echo ucfirst(str_replace('_', ' ', $request['withdrawal_method'])); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- طرق الدفع المحفوظة -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fas fa-credit-card me-2"></i>
                    طرق الدفع المحفوظة
                </h5>
                <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCardModal">
                    <i class="fas fa-plus me-1"></i>إضافة
                </a>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="payment-method selected">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fab fa-cc-mada fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">مدى</h6>
                                <p class="text-muted small mb-0">**** 1234</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="payment-method">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fab fa-cc-visa fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">فيزا</h6>
                                <p class="text-muted small mb-0">**** 5678</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- معلومات إضافية -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        معلومات مهمة
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            الحد الأدنى للسحب: 50 ريال
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            المعاملات تظهر خلال دقائق
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            لا توجد رسوم على الإيداع
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            السحب خلال 24-48 ساعة عمل
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="fas fa-question-circle me-2 text-warning"></i>
                        الأسئلة الشائعة
                    </h5>
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    كيف يمكنني زيادة رصيدي؟
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    يمكنك زيادة الرصيد عبر الإيداع المباشر أو كسب المكافآت من خلال دعوة الأصدقاء والمشاركة في العروض.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    متى يصل السحب؟
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    تستغرق عملية السحب من 24 إلى 48 ساعة عمل خلال أيام الأسبوع.
                                </div>
                            </div>
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
                <a href="wallet.php" class="nav-item active">
                    <i class="fas fa-wallet"></i>
                    <span>محفظتي</span>
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

<!-- مودال الإيداع -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    إيداع رصيد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="depositForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">المبلغ المطلوب إيداعه</label>
                        <div class="row g-2">
                            <?php $amounts = [50, 100, 200, 500, 1000]; ?>
                            <?php foreach($amounts as $amount): ?>
                            <div class="col">
                                <div class="amount-box" onclick="selectAmount(<?php echo $amount; ?>)">
                                    <h5 class="fw-bold mb-1"><?php echo $amount; ?> ر.س</h5>
                                    <small class="text-muted">إيداع فوري</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div class="col">
                                <div class="amount-box" onclick="showCustomAmount()">
                                    <h5 class="fw-bold mb-1">آخر</h5>
                                    <small class="text-muted">أدخل مبلغ</small>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="amount" id="selectedAmount" required>
                        <div class="mt-3 d-none" id="customAmountDiv">
                            <input type="number" class="form-control" id="customAmount" placeholder="أدخل المبلغ المطلوب" min="10" step="10">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">طريقة الدفع</label>
                        <div class="row g-3">
                            <?php 
                            $payment_methods = [
                                'mada' => ['icon' => 'fab fa-cc-mada', 'text' => 'مدى', 'color' => 'text-success'],
                                'visa' => ['icon' => 'fab fa-cc-visa', 'text' => 'فيزا / ماستركارد', 'color' => 'text-primary'],
                                'stc_pay' => ['icon' => 'fas fa-mobile-alt', 'text' => 'STC Pay', 'color' => 'text-danger'],
                                'apple_pay' => ['icon' => 'fab fa-apple', 'text' => 'Apple Pay', 'color' => 'text-dark']
                            ];
                            ?>
                            <?php foreach($payment_methods as $key => $method): ?>
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectPaymentMethod('<?php echo $key; ?>')">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="<?php echo $method['icon']; ?> fa-2x <?php echo $method['color']; ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo $method['text']; ?></h6>
                                            <small class="text-muted">دفع آمن وسريع</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="payment_method" id="selectedPaymentMethod" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="deposit" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i>
                            تأكيد الإيداع
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- مودال السحب -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-minus-circle me-2"></i>
                    سحب رصيد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="withdrawForm">
                    <div class="mb-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            الحد الأدنى للسحب: 50 ريال | الرصيد الحالي: <?php echo number_format($balance, 2); ?> ريال
                        </div>
                        
                        <label class="form-label fw-bold mb-3">المبلغ المطلوب سحبه</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">ر.س</span>
                            <input type="number" class="form-control" name="withdraw_amount" 
                                   min="50" max="<?php echo $balance; ?>" 
                                   step="10" required 
                                   placeholder="أدخل المبلغ">
                        </div>
                        <div class="form-text">يمكنك سحب المبلغ كاملاً: <?php echo number_format($balance, 2); ?> ريال</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">طريقة السحب</label>
                        <div class="row g-3">
                            <?php 
                            $withdrawal_methods = [
                                'bank_transfer' => ['icon' => 'fas fa-university', 'text' => 'تحويل بنكي', 'desc' => 'للحسابات البنكية'],
                                'stc_pay' => ['icon' => 'fas fa-mobile-alt', 'text' => 'STC Pay', 'desc' => 'للجوال'],
                                'mada' => ['icon' => 'fab fa-cc-mada', 'text' => 'مدى', 'desc' => 'للبطاقات'],
                                'vodafone_cash' => ['icon' => 'fas fa-money-bill-wave', 'text' => 'فودافون كاش', 'desc' => 'للجوال']
                            ];
                            ?>
                            <?php foreach($withdrawal_methods as $key => $method): ?>
                            <div class="col-md-6">
                                <div class="payment-method" onclick="selectWithdrawalMethod('<?php echo $key; ?>')">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="<?php echo $method['icon']; ?> fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo $method['text']; ?></h6>
                                            <small class="text-muted"><?php echo $method['desc']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="withdrawal_method" id="selectedWithdrawalMethod" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">تفاصيل الحساب</label>
                        <textarea class="form-control" name="account_details" rows="3" 
                                  placeholder="أدخل رقم الحساب / الجوال / البطاقة"></textarea>
                        <div class="form-text">تأكد من صحة المعلومات قبل الإرسال</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="withdraw" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            تأكيد طلب السحب
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- مودال إضافة بطاقة -->
<div class="modal fade" id="addCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة بطاقة دفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">رقم البطاقة</label>
                        <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">تاريخ الانتهاء</label>
                            <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رمز الأمان (CVV)</label>
                            <input type="text" class="form-control" placeholder="123" maxlength="3">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">اسم حامل البطاقة</label>
                        <input type="text" class="form-control" placeholder="كما هو مدون على البطاقة">
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="defaultCard">
                        <label class="form-check-label" for="defaultCard">
                            تعيين كبطاقة افتراضية
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            حفظ البطاقة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // اختيار مبلغ الإيداع
    window.selectAmount = function(amount) {
        document.querySelectorAll('.amount-box').forEach(box => {
            box.classList.remove('selected');
        });
        event.target.closest('.amount-box').classList.add('selected');
        document.getElementById('selectedAmount').value = amount;
        document.getElementById('customAmountDiv').classList.add('d-none');
    };
    
    window.showCustomAmount = function() {
        document.querySelectorAll('.amount-box').forEach(box => {
            box.classList.remove('selected');
        });
        event.target.closest('.amount-box').classList.add('selected');
        document.getElementById('customAmountDiv').classList.remove('d-none');
        document.getElementById('customAmount').focus();
    };
    
    document.getElementById('customAmount')?.addEventListener('input', function() {
        document.getElementById('selectedAmount').value = this.value;
    });
    
    // اختيار طريقة الدفع
    window.selectPaymentMethod = function(method) {
        document.querySelectorAll('.payment-method').forEach(item => {
            item.classList.remove('selected');
        });
        event.target.closest('.payment-method').classList.add('selected');
        document.getElementById('selectedPaymentMethod').value = method;
    };
    
    // اختيار طريقة السحب
    window.selectWithdrawalMethod = function(method) {
        document.querySelectorAll('.payment-method').forEach(item => {
            item.classList.remove('selected');
        });
        event.target.closest('.payment-method').classList.add('selected');
        document.getElementById('selectedWithdrawalMethod').value = method;
    };
    
    // التحقق من صحة نموذج الإيداع
    document.getElementById('depositForm')?.addEventListener('submit', function(e) {
        const amount = document.getElementById('selectedAmount').value;
        const paymentMethod = document.getElementById('selectedPaymentMethod').value;
        
        if (!amount || amount < 10) {
            e.preventDefault();
            alert('يرجى اختيار مبلغ صحيح (10 ريال على الأقل)');
            return false;
        }
        
        if (!paymentMethod) {
            e.preventDefault();
            alert('يرجى اختيار طريقة الدفع');
            return false;
        }
        
        // هنا يمكنك إضافة ربط مع بوابة الدفع
        return true;
    });
    
    // التحقق من صحة نموذج السحب
    document.getElementById('withdrawForm')?.addEventListener('submit', function(e) {
        const amountInput = document.querySelector('input[name="withdraw_amount"]');
        const methodInput = document.getElementById('selectedWithdrawalMethod');
        const accountDetails = document.querySelector('textarea[name="account_details"]');
        
        if (!amountInput.value || amountInput.value < 50) {
            e.preventDefault();
            alert('الحد الأدنى للسحب هو 50 ريال');
            amountInput.focus();
            return false;
        }
        
        if (parseFloat(amountInput.value) > parseFloat(<?php echo $balance; ?>)) {
            e.preventDefault();
            alert('المبلغ المطلوب يتجاوز رصيدك الحالي');
            amountInput.focus();
            return false;
        }
        
        if (!methodInput.value) {
            e.preventDefault();
            alert('يرجى اختيار طريقة السحب');
            return false;
        }
        
        if (!accountDetails.value.trim()) {
            e.preventDefault();
            alert('يرجى إدخال تفاصيل الحساب');
            accountDetails.focus();
            return false;
        }
        
        return true;
    });
    
    // تحديث الحد الأقصى للسحب عند تغيير الرصيد
    const withdrawAmountInput = document.querySelector('input[name="withdraw_amount"]');
    if (withdrawAmountInput) {
        withdrawAmountInput.max = <?php echo $balance; ?>;
        withdrawAmountInput.placeholder = `الحد الأقصى: ${<?php echo $balance; ?>} ريال`;
    }
    
    // تأثيرات التمرير
    const transactionItems = document.querySelectorAll('.transaction-item');
    transactionItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(-5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // إظهار الرصيد بتأثير عد
    const balanceElement = document.querySelector('.balance-amount');
    if (balanceElement) {
        const finalBalance = <?php echo $balance; ?>;
        let currentBalance = 0;
        const duration = 1500; // 1.5 ثانية
        const steps = 60;
        const increment = finalBalance / steps;
        
        const timer = setInterval(() => {
            currentBalance += increment;
            if (currentBalance >= finalBalance) {
                currentBalance = finalBalance;
                clearInterval(timer);
            }
            balanceElement.innerHTML = currentBalance.toFixed(2) + ' <small>ر.س</small>';
        }, duration / steps);
    }
});
</script>
</body>
</html>