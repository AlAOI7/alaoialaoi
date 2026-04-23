<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// إنشاء مجلد للشعارات إذا لم يكن موجوداً
if (!file_exists('banks_logos')) {
    mkdir('banks_logos', 0777, true);
}

// معالجة العمليات
$message = "";
$message_type = ""; // success, error, warning, info

// معالجة إضافة/تعديل البنك
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_bank'])) {
        $bank_id = isset($_POST['bank_id']) ? (int)$_POST['bank_id'] : 0;
        $name = $conn->real_escape_string(trim($_POST['name']));
        $website = $conn->real_escape_string(trim($_POST['website']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $status = $conn->real_escape_string($_POST['status']);

        // التحقق من الحقول المطلوبة
        if (empty($name)) {
            $message = "اسم البنك مطلوب!";
            $message_type = "error";
        } else {
            // معالجة رفع الشعار
            $logo = NULL;
            $upload_success = true;
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                $file_type = $_FILES['logo']['type'];
                $file_size = $_FILES['logo']['size'];
                $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $file_name = time() . '_' . uniqid() . '.' . $file_extension;

                if (in_array($file_type, $allowed_types)) {
                    if ($file_size <= 5 * 1024 * 1024) {
                        $logo = 'banks_logos/' . $file_name;
                        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo)) {
                            $upload_success = false;
                            $message = "فشل في تحميل الصورة!";
                            $message_type = "error";
                        }
                    } else {
                        $upload_success = false;
                        $message = "حجم الصورة كبير جداً! الحد الأقصى 5MB";
                        $message_type = "error";
                    }
                } else {
                    $upload_success = false;
                    $message = "نوع الملف غير مدعوم! يرجى استخدام الصور فقط";
                    $message_type = "error";
                }
            }

            if ($upload_success) {
                if ($bank_id > 0) {
                    // تحديث البنك
                    $logo_sql = $logo ? ", logo='$logo'" : "";
                    $sql = "UPDATE banks SET 
                            name='$name', 
                            website='$website', 
                            description='$description', 
                            status='$status' 
                            $logo_sql 
                            WHERE id=$bank_id";
                    
                    if ($conn->query($sql)) {
                        $message = "تم تحديث البنك بنجاح!";
                        $message_type = "success";
                    } else {
                        $message = "خطأ في التحديث: " . $conn->error;
                        $message_type = "error";
                    }
                } else {
                    // إضافة بنك جديد
                    $logo_value = $logo ? "'$logo'" : "NULL";
                    $sql = "INSERT INTO banks (name, logo, website, description, status) 
                            VALUES ('$name', $logo_value, '$website', '$description', '$status')";
                    
                    if ($conn->query($sql)) {
                        $message = "تم إضافة البنك بنجاح!";
                        $message_type = "success";
                    } else {
                        $message = "خطأ في الإضافة: " . $conn->error;
                        $message_type = "error";
                    }
                }
            }
        }
    } 
    // معالجة حفظ الحسابات
    elseif (isset($_POST['save_accounts'])) {
        $bank_id = (int)$_POST['bank_id'];
        $accounts = $_POST['accounts'];
        
        // حذف الحسابات الحالية وإضافة الجديدة
        $conn->query("DELETE FROM bank_accounts WHERE bank_id = $bank_id");
        
        foreach ($accounts as $account) {
            $account_number = $conn->real_escape_string(trim($account['account_number']));
            $account_holder = $conn->real_escape_string(trim($account['account_holder']));
            $currency = $conn->real_escape_string($account['currency']);
            $iban = $conn->real_escape_string(trim($account['iban']));
            $swift_code = $conn->real_escape_string(trim($account['swift_code']));
            $branch_name = $conn->real_escape_string(trim($account['branch_name']));
            $balance = floatval($account['balance']);
            $status = $conn->real_escape_string($account['status']);
            $is_primary = isset($account['is_primary']) ? 1 : 0;
            $notes = $conn->real_escape_string(trim($account['notes']));
            
            $sql = "INSERT INTO bank_accounts 
                    (bank_id, account_number, account_holder, currency, iban, swift_code, branch_name, balance, status, is_primary, notes) 
                    VALUES 
                    ($bank_id, '$account_number', '$account_holder', '$currency', '$iban', '$swift_code', '$branch_name', $balance, '$status', $is_primary, '$notes')";
            
            $conn->query($sql);
        }
        
        $message = "تم حفظ الحسابات بنجاح!";
        $message_type = "success";
    }
    // معالجة حذف البنك
    elseif (isset($_POST['delete_bank'])) {
        $bank_id = (int)$_POST['bank_id'];
        
        // حذف الشعار إذا كان موجوداً
        $logo_result = $conn->query("SELECT logo FROM banks WHERE id=$bank_id");
        if ($logo_result && $logo_result->num_rows > 0) {
            $logo = $logo_result->fetch_assoc()['logo'];
            if ($logo && file_exists($logo)) {
                unlink($logo);
            }
        }
        
        $sql = "DELETE FROM banks WHERE id=$bank_id";
        if ($conn->query($sql)) {
            $message = "تم حذف البنك بنجاح!";
            $message_type = "success";
        } else {
            $message = "خطأ في الحذف: " . $conn->error;
            $message_type = "error";
        }
    }
}

// جلب جميع البنوك مع عدد حساباتها
$banks_result = $conn->query("
    SELECT b.*, 
           COUNT(ba.id) as accounts_count,
           SUM(CASE WHEN ba.status = 'active' THEN 1 ELSE 0 END) as active_accounts
    FROM banks b
    LEFT JOIN bank_accounts ba ON b.id = ba.bank_id
    GROUP BY b.id
    ORDER BY b.created_at DESC
");

// جلب بيانات البنك للتعديل إذا طُلب
$edit_bank = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $bank_id = (int)$_GET['edit'];
    $bank_result = $conn->query("SELECT * FROM banks WHERE id = $bank_id");
    if ($bank_result && $bank_result->num_rows > 0) {
        $edit_bank = $bank_result->fetch_assoc();
    }
}

// جلب حسابات البنك للتعديل إذا طُلب
$bank_accounts = [];
if (isset($_GET['accounts']) && is_numeric($_GET['accounts'])) {
    $bank_id = (int)$_GET['accounts'];
    $accounts_result = $conn->query("SELECT * FROM bank_accounts WHERE bank_id = $bank_id ORDER BY is_primary DESC, id ASC");
    if ($accounts_result) {
        while ($account = $accounts_result->fetch_assoc()) {
            $bank_accounts[] = $account;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البنوك والحسابات البنكية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
     <link rel="stylesheet" href="style.css">
       <link rel="stylesheet" href="style.css">
   
    <style>
        /* إضافة الأنماط السابقة مع بعض التعديلات */

        /* إضافة أنماط للرسائل */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background-color: rgba(78, 205, 196, 0.15);
            border-left: 4px solid var(--success);
            color: #0d625c;
        }

        .alert-error {
            background-color: rgba(255, 101, 132, 0.15);
            border-left: 4px solid var(--secondary);
            color: #cc2e5d;
        }

        .alert-warning {
            background-color: rgba(255, 154, 118, 0.15);
            border-left: 4px solid var(--warning);
            color: #cc6a3d;
        }

        .alert-info {
            background-color: rgba(106, 137, 204, 0.15);
            border-left: 4px solid var(--info);
            color: #3a4a80;
        }

        .alert .close-alert {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: var(--transition);
        }

        .alert .close-alert:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* تعديلات على بطاقات البنوك */
        .bank-card {
            position: relative;
        }

        .bank-logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow);
        }

        .bank-stats {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 12px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            background-color: rgba(108, 99, 255, 0.1);
            border-radius: 12px;
            color: var(--primary);
        }

        /* تعديلات على النافذة المنبثقة */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .account-card {
            position: relative;
        }

        .primary-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--primary);
            color: white;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .account-fields {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .account-fields .form-group {
            margin-bottom: 0;
        }

        /* تنسيق حقول الحساب */
        .form-control-sm {
            padding: 8px 12px;
            font-size: 13px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        /* تخصيص زر الإضافة */
        .add-bank-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 200px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-bank-btn:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }

        /* تحسين العرض على الجوال */
        @media (max-width: 768px) {
            .account-fields {
                grid-template-columns: 1fr;
            }
            
            .bank-stats {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- الشريط الجانبي -->
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <!-- الهيدر -->
            <?php include 'header.php'; ?>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <div class="page-title">
                    <h2>البنوك والحسابات البنكية</h2>
                    <div class="date"><?php echo date('l، j F Y'); ?></div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?>">
                        <div>
                            <i class="fas fa-<?= 
                                $message_type == 'success' ? 'check-circle' : 
                                ($message_type == 'error' ? 'exclamation-circle' : 
                                ($message_type == 'warning' ? 'exclamation-triangle' : 'info-circle')) 
                            ?>"></i>
                            <span style="margin-right: 10px;"><?= $message ?></span>
                        </div>
                        <button class="close-alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- بطاقات البنوك -->
                <div class="banks-container">
                    <?php if ($banks_result->num_rows > 0): ?>
                        <?php while ($bank = $banks_result->fetch_assoc()): ?>
                            <div class="bank-card">
                                <div class="bank-header">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if ($bank['logo']): ?>
                                            <img src="<?= $bank['logo'] ?>" alt="<?= htmlspecialchars($bank['name']) ?>" class="bank-logo-img">
                                        <?php else: ?>
                                            <div class="bank-logo">
                                                <?= mb_substr($bank['name'], 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="bank-name"><?= htmlspecialchars($bank['name']) ?></div>
                                            <?php if ($bank['description']): ?>
                                                <div style="font-size: 12px; color: var(--gray);"><?= htmlspecialchars($bank['description']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="bank-actions">
                                        <button class="action-btn edit-bank" data-bank-id="<?= $bank['id'] ?>" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn manage-accounts" data-bank-id="<?= $bank['id'] ?>" title="إدارة الحسابات">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف البنك؟');">
                                            <input type="hidden" name="bank_id" value="<?= $bank['id'] ?>">
                                            <button type="submit" name="delete_bank" class="action-btn" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="bank-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-wallet"></i>
                                        <span><?= $bank['accounts_count'] ?> حساب</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span><?= $bank['active_accounts'] ?> نشط</span>
                                    </div>
                                    <div class="stat-item" style="background-color: <?= $bank['status'] == 'active' ? 'rgba(78, 205, 196, 0.15)' : 'rgba(255, 154, 118, 0.15)' ?>; color: <?= $bank['status'] == 'active' ? 'var(--success)' : 'var(--warning)' ?>;">
                                        <i class="fas fa-circle"></i>
                                        <span><?= $bank['status'] == 'active' ? 'نشط' : 'غير نشط' ?></span>
                                    </div>
                                </div>

                                <!-- عرض الحسابات -->
                                <div class="bank-accounts">
                                    <?php 
                                    $accounts_result = $conn->query("
                                        SELECT * FROM bank_accounts 
                                        WHERE bank_id = {$bank['id']} 
                                        ORDER BY is_primary DESC, id ASC 
                                        LIMIT 3
                                    ");
                                    if ($accounts_result->num_rows > 0):
                                        while ($account = $accounts_result->fetch_assoc()):
                                    ?>
                                        <div class="account-item">
                                            <div class="account-info">
                                                <div class="account-number">
                                                    <?= htmlspecialchars($account['account_number']) ?>
                                                    <?php if ($account['is_primary']): ?>
                                                        <span style="color: var(--primary); font-size: 10px; margin-right: 5px;">(رئيسي)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="account-holder"><?= htmlspecialchars($account['account_holder']) ?></div>
                                            </div>
                                            <div class="account-currency"><?= $account['currency'] ?></div>
                                            <div class="account-status status-<?= $account['status'] ?>">
                                                <?= $account['status'] == 'active' ? 'مفعل' : ($account['status'] == 'pending' ? 'بانتظار' : 'غير مفعل') ?>
                                            </div>
                                        </div>
                                    <?php 
                                        endwhile; 
                                        if ($accounts_result->num_rows == 3):
                                    ?>
                                        <div style="text-align: center; padding: 10px; color: var(--gray); font-size: 12px;">
                                            <i class="fas fa-ellipsis-h"></i>
                                            <span>والمزيد...</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php else: ?>
                                        <div style="text-align: center; padding: 15px; color: var(--gray);">
                                            <i class="fas fa-wallet" style="font-size: 20px; margin-bottom: 5px; display: block;"></i>
                                            <span>لا توجد حسابات</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button class="add-account-btn manage-accounts" data-bank-id="<?= $bank['id'] ?>">
                                    <i class="fas fa-plus"></i>
                                    <span>إدارة الحسابات</span>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bank-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <i class="fas fa-university" style="font-size: 50px; color: var(--gray); margin-bottom: 20px; display: block;"></i>
                            <h3 style="color: var(--gray); margin-bottom: 15px;">لا توجد بنوك مضافة</h3>
                            <p style="color: var(--gray); margin-bottom: 20px;">ابدأ بإضافة أول بنك لك</p>
                        </div>
                    <?php endif; ?>

                    <!-- زر إضافة بنك جديد -->
                    <div class="add-bank-btn" id="addBankBtn">
                        <i class="fas fa-plus-circle" style="font-size: 40px; color: var(--gray); margin-bottom: 10px;"></i>
                        <span style="color: var(--gray); font-weight: 600;">إضافة بنك جديد</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- النافذة المنبثقة لإضافة/تعديل البنك -->
    <div class="modal-overlay" id="bankModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">إضافة بنك جديد</h3>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="bankForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="bank_id" id="bankId">
                    <input type="hidden" name="save_bank" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">شعار البنك</label>
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                            <div id="logoPreview" style="width: 80px; height: 80px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <i class="fas fa-building" style="font-size: 30px; color: #999;"></i>
                            </div>
                            <div>
                                <input type="file" id="logoUpload" name="logo" accept="image/*" style="display: none;">
                                <label for="logoUpload" class="upload-btn" style="display: inline-block; padding: 10px 20px; background-color: var(--light); border-radius: 8px; cursor: pointer; transition: var(--transition);">
                                    <i class="fas fa-upload"></i> تحميل الشعار
                                </label>
                                <p style="font-size: 12px; color: #666; margin-top: 5px;">
                                    الحجم الموصى به: 200x200 بكسل<br>
                                    الأنواع المسموحة: JPG, PNG, GIF, SVG
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="bankName">اسم البنك *</label>
                        <input type="text" class="form-control" id="bankName" name="name" placeholder="أدخل اسم البنك" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="bankWebsite">الموقع الإلكتروني</label>
                            <input type="url" class="form-control" id="bankWebsite" name="website" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="bankStatus">الحالة</label>
                            <select class="form-control" id="bankStatus" name="status">
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="bankDescription">وصف البنك</label>
                        <textarea class="form-control" id="bankDescription" name="description" rows="3" placeholder="أدخل وصفاً للبنك"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- النافذة المنبثقة لإدارة الحسابات -->
    <div class="modal-overlay" id="accountsModal">
        <div class="modal" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">إدارة الحسابات البنكية</h3>
                <button class="close-modal" id="closeAccountsModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="accountsForm" method="POST">
                    <input type="hidden" name="bank_id" id="accountsBankId">
                    <input type="hidden" name="save_accounts" value="1">
                    
                    <div id="accountsList">
                        <!-- الحسابات ستضاف هنا ديناميكياً -->
                    </div>
                    
                    <button type="button" class="add-account-btn" id="addNewAccountBtn" style="margin-top: 10px; width: 100%;">
                        <i class="fas fa-plus"></i>
                        <span>إضافة حساب جديد</span>
                    </button>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="cancelAccountsBtn">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ الحسابات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // عناصر DOM
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const addBankBtn = document.getElementById('addBankBtn');
        const bankModal = document.getElementById('bankModal');
        const closeModal = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const modalTitle = document.getElementById('modalTitle');
        const bankForm = document.getElementById('bankForm');
        const bankIdInput = document.getElementById('bankId');
        const bankNameInput = document.getElementById('bankName');
        const bankWebsiteInput = document.getElementById('bankWebsite');
        const bankStatusSelect = document.getElementById('bankStatus');
        const bankDescriptionInput = document.getElementById('bankDescription');
        const logoPreview = document.getElementById('logoPreview');
        const logoUpload = document.getElementById('logoUpload');
        
        // عناصر إدارة الحسابات
        const accountsModal = document.getElementById('accountsModal');
        const closeAccountsModal = document.getElementById('closeAccountsModal');
        const cancelAccountsBtn = document.getElementById('cancelAccountsBtn');
        const accountsBankId = document.getElementById('accountsBankId');
        const accountsList = document.getElementById('accountsList');
        const addNewAccountBtn = document.getElementById('addNewAccountBtn');
        const accountsForm = document.getElementById('accountsForm');

        // تبديل الشريط الجانبي
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }

        // فتح نافذة إضافة بنك جديد
        addBankBtn.addEventListener('click', () => {
            modalTitle.textContent = 'إضافة بنك جديد';
            bankForm.reset();
            bankIdInput.value = '';
            logoPreview.innerHTML = '<i class="fas fa-building" style="font-size: 30px; color: #999;"></i>';
            bankModal.classList.add('active');
        });

        // فتح نافذة تعديل البنك
        document.querySelectorAll('.edit-bank').forEach(button => {
            button.addEventListener('click', function() {
                const bankId = this.getAttribute('data-bank-id');
                
                // جلب بيانات البنك عبر AJAX
                fetch(`get_bank.php?id=${bankId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modalTitle.textContent = `تعديل بنك ${data.bank.name}`;
                            bankIdInput.value = data.bank.id;
                            bankNameInput.value = data.bank.name;
                            bankWebsiteInput.value = data.bank.website || '';
                            bankStatusSelect.value = data.bank.status;
                            bankDescriptionInput.value = data.bank.description || '';
                            
                            // عرض الشعار إذا كان موجوداً
                            if (data.bank.logo) {
                                logoPreview.innerHTML = `<img src="${data.bank.logo}" alt="${data.bank.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
                            } else {
                                logoPreview.innerHTML = '<i class="fas fa-building" style="font-size: 30px; color: #999;"></i>';
                            }
                            
                            bankModal.classList.add('active');
                        } else {
                            alert('حدث خطأ في جلب بيانات البنك');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال بالخادم');
                    });
            });
        });

        // فتح نافذة إدارة الحسابات
        document.querySelectorAll('.manage-accounts').forEach(button => {
            button.addEventListener('click', function() {
                const bankId = this.getAttribute('data-bank-id');
                accountsBankId.value = bankId;
                
                // جلب حسابات البنك عبر AJAX
                fetch(`get_accounts.php?bank_id=${bankId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            accountsList.innerHTML = '';
                            if (data.accounts.length > 0) {
                                data.accounts.forEach((account, index) => {
                                    addAccountToForm(account, index + 1);
                                });
                            } else {
                                addAccountToForm(null, 1);
                            }
                            accountsModal.classList.add('active');
                        } else {
                            alert('حدث خطأ في جلب الحسابات');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال بالخادم');
                    });
            });
        });

        // إضافة حساب جديد للنموذج
        function addAccountToForm(account = null, index = null) {
            if (!index) {
                index = accountsList.querySelectorAll('.account-card').length + 1;
            }
            
            const accountId = account ? account.id : '';
            const isPrimary = account ? account.is_primary : false;
            
            const accountCard = document.createElement('div');
            accountCard.className = 'account-card';
            accountCard.innerHTML = `
                ${isPrimary ? '<span class="primary-badge">رئيسي</span>' : ''}
                <div class="account-card-header">
                    <div class="account-card-title">الحساب #${index}</div>
                    <div class="account-card-actions">
                        <button type="button" class="action-btn remove-account" ${accountsList.querySelectorAll('.account-card').length === 0 ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="account-fields">
                    <input type="hidden" name="accounts[${index}][id]" value="${accountId}">
                    
                    <div class="form-group">
                        <label class="form-label">رقم الحساب *</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][account_number]" 
                               value="${account ? account.account_number : ''}" 
                               placeholder="أدخل رقم الحساب" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">اسم صاحب الحساب *</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][account_holder]" 
                               value="${account ? account.account_holder : ''}" 
                               placeholder="أدخل اسم صاحب الحساب" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">العملة</label>
                        <select class="form-control form-control-sm" name="accounts[${index}][currency]">
                            <option value="SAR" ${account && account.currency === 'SAR' ? 'selected' : ''}>ريال سعودي</option>
                            <option value="USD" ${account && account.currency === 'USD' ? 'selected' : ''}>دولار أمريكي</option>
                            <option value="YER" ${account && account.currency === 'YER' ? 'selected' : ''}>ريال يمني</option>
                            <option value="EUR" ${account && account.currency === 'EUR' ? 'selected' : ''}>يورو</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">الرصيد</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" 
                               name="accounts[${index}][balance]" 
                               value="${account ? account.balance : '0.00'}" 
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رقم الآيبان (IBAN)</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][iban]" 
                               value="${account ? account.iban : ''}" 
                               placeholder="SA00 0000 0000 0000 0000 0000">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رمز السويفت (SWIFT)</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][swift_code]" 
                               value="${account ? account.swift_code : ''}" 
                               placeholder="BNKSARSAXXX">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">اسم الفرع</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="accounts[${index}][branch_name]" 
                               value="${account ? account.branch_name : ''}" 
                               placeholder="أدخل اسم الفرع">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">الحالة</label>
                        <select class="form-control form-control-sm" name="accounts[${index}][status]">
                            <option value="active" ${account && account.status === 'active' ? 'selected' : ''}>نشط</option>
                            <option value="pending" ${account && account.status === 'pending' ? 'selected' : ''}>بانتظار الموافقة</option>
                            <option value="inactive" ${account && account.status === 'inactive' ? 'selected' : ''}>غير نشط</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">حساب رئيسي</label>
                        <div style="display: flex; align-items: center; height: 38px;">
                            <label class="toggle-switch">
                                <input type="checkbox" name="accounts[${index}][is_primary]" 
                                       ${isPrimary ? 'checked' : ''} 
                                       onchange="setAsPrimary(this, ${index})">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" name="accounts[${index}][notes]" rows="2" 
                                  placeholder="أدخل أي ملاحظات">${account ? account.notes : ''}</textarea>
                    </div>
                </div>
            `;
            
            accountsList.appendChild(accountCard);
            
            // إضافة حدث لحذف الحساب
            const removeBtn = accountCard.querySelector('.remove-account');
            removeBtn.addEventListener('click', function() {
                if (accountsList.querySelectorAll('.account-card').length > 1) {
                    accountCard.remove();
                    updateAccountTitles();
                } else {
                    alert('يجب أن يكون هناك حساب واحد على الأقل');
                }
            });
        }

        // تعيين حساب كرئيسي
        function setAsPrimary(checkbox, index) {
            if (checkbox.checked) {
                // إلغاء تحديد جميع الحسابات الأخرى
                document.querySelectorAll('input[name$="[is_primary]"]').forEach(otherCheckbox => {
                    if (otherCheckbox !== checkbox) {
                        otherCheckbox.checked = false;
                        otherCheckbox.closest('.account-card').querySelector('.primary-badge')?.remove();
                    }
                });
                
                // إضافة بادج الحساب الرئيسي
                const accountCard = checkbox.closest('.account-card');
                if (!accountCard.querySelector('.primary-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'primary-badge';
                    badge.textContent = 'رئيسي';
                    accountCard.prepend(badge);
                }
            } else {
                checkbox.closest('.account-card').querySelector('.primary-badge')?.remove();
            }
        }

        // تحديث عناوين الحسابات
        function updateAccountTitles() {
            const accountCards = accountsList.querySelectorAll('.account-card');
            accountCards.forEach((card, index) => {
                const title = card.querySelector('.account-card-title');
                title.textContent = `الحساب #${index + 1}`;
                
                // تحديث أسماء الحقول في المصفوفة
                const inputs = card.querySelectorAll('[name^="accounts["]');
                inputs.forEach(input => {
                    const oldName = input.getAttribute('name');
                    const newName = oldName.replace(/accounts\[\d+\]/, `accounts[${index + 1}]`);
                    input.setAttribute('name', newName);
                });
            });
        }

        // زر إضافة حساب جديد في نافذة الحسابات
        addNewAccountBtn.addEventListener('click', () => {
            addAccountToForm();
            updateAccountTitles();
        });

        // معاينة الشعار
        logoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.innerHTML = `<img src="${e.target.result}" alt="شعار البنك" style="width: 100%; height: 100%; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // إغلاق النوافذ المنبثقة
        closeModal.addEventListener('click', () => {
            bankModal.classList.remove('active');
        });

        cancelBtn.addEventListener('click', () => {
            bankModal.classList.remove('active');
        });

        closeAccountsModal.addEventListener('click', () => {
            accountsModal.classList.remove('active');
        });

        cancelAccountsBtn.addEventListener('click', () => {
            accountsModal.classList.remove('active');
        });

        // إغلاق النوافذ عند النقر خارجها
        window.addEventListener('click', (e) => {
            if (e.target === bankModal) {
                bankModal.classList.remove('active');
            }
            if (e.target === accountsModal) {
                accountsModal.classList.remove('active');
            }
        });

        // التحقق من صحة النماذج
        bankForm.addEventListener('submit', function(e) {
            if (!bankNameInput.value.trim()) {
                e.preventDefault();
                alert('يرجى إدخال اسم البنك');
                bankNameInput.focus();
                return false;
            }
        });

        accountsForm.addEventListener('submit', function(e) {
            const accountNumbers = document.querySelectorAll('input[name$="[account_number]"]');
            let isValid = true;
            
            accountNumbers.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ff4757';
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('يرجى ملء جميع حقول أرقام الحسابات');
                return false;
            }
        });

        // إغلاق رسائل التنبيه
        document.querySelectorAll('.close-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert').style.display = 'none';
            });
        });

        // تهيئة الأحداث عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            // إغلاق رسائل التنبيه تلقائياً بعد 5 ثوانٍ
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>