<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$user_sql = "SELECT name, email FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// جلب العناوين
$addresses_sql = "SELECT * FROM user_addresses WHERE user_id = ? AND is_active = 1 ORDER BY is_default DESC, created_at DESC";
$addresses_stmt = $conn->prepare($addresses_sql);
$addresses_stmt->bind_param("i", $user_id);
$addresses_stmt->execute();
$addresses_result = $addresses_stmt->get_result();
$addresses = [];
while ($row = $addresses_result->fetch_assoc()) {
    $addresses[] = $row;
}

// جلب المدن والمناطق (إذا كان لديك جدول للمدن)
$cities_sql = "SELECT * FROM cities ORDER BY name";
$cities_result = $conn->query($cities_sql);
$cities = [];
while ($row = $cities_result->fetch_assoc()) {
    $cities[] = $row;
}

// معالجة إضافة عنوان جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_address') {
        $title = trim($_POST['title']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $country = trim($_POST['country']);
        $postal_code = trim($_POST['postal_code']);
        $phone = trim($_POST['phone']);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        $errors = [];
        
        // التحقق من البيانات
        if (empty($title)) $errors[] = 'عنوان العناوين مطلوب';
        if (empty($address)) $errors[] = 'العنوان التفصيلي مطلوب';
        if (empty($city)) $errors[] = 'المدينة مطلوبة';
        if (empty($country)) $errors[] = 'البلد مطلوب';
        if (empty($phone)) $errors[] = 'رقم الهاتف مطلوب';
        
        // تحقق من صحة رقم الهاتف
        if (!preg_match('/^[0-9+\-\s]{9,20}$/', $phone)) {
            $errors[] = 'رقم الهاتف غير صحيح';
        }
        
        if (empty($errors)) {
            // إذا كان العنوان الجديد هو الافتراضي، قم بإلغاء الافتراضي من العناوين الأخرى
            if ($is_default) {
                $reset_sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
                $reset_stmt = $conn->prepare($reset_sql);
                $reset_stmt->bind_param("i", $user_id);
                $reset_stmt->execute();
            }
            
            // إضافة العنوان الجديد
            $insert_sql = "INSERT INTO user_addresses 
                          (user_id, title, address, city, country, postal_code, phone, is_default, is_active, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issssssi", $user_id, $title, $address, $city, $country, $postal_code, $phone, $is_default);
            
            if ($insert_stmt->execute()) {
                $success_message = 'تم إضافة العنوان بنجاح';
                // تحديث قائمة العناوين
                header('Location: addresses.php?success=added');
                exit();
            } else {
                $errors[] = 'حدث خطأ أثناء إضافة العنوان';
            }
        }
    }
    
    elseif ($action == 'edit_address') {
        $address_id = $_POST['address_id'];
        $title = trim($_POST['title']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $country = trim($_POST['country']);
        $postal_code = trim($_POST['postal_code']);
        $phone = trim($_POST['phone']);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        $errors = [];
        
        // التحقق من البيانات
        if (empty($title)) $errors[] = 'عنوان العناوين مطلوب';
        if (empty($address)) $errors[] = 'العنوان التفصيلي مطلوب';
        if (empty($city)) $errors[] = 'المدينة مطلوبة';
        if (empty($country)) $errors[] = 'البلد مطلوب';
        if (empty($phone)) $errors[] = 'رقم الهاتف مطلوب';
        
        if (empty($errors)) {
            // إذا كان العنوان الجديد هو الافتراضي، قم بإلغاء الافتراضي من العناوين الأخرى
            if ($is_default) {
                $reset_sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND id != ?";
                $reset_stmt = $conn->prepare($reset_sql);
                $reset_stmt->bind_param("ii", $user_id, $address_id);
                $reset_stmt->execute();
            }
            
            // تحديث العنوان
            $update_sql = "UPDATE user_addresses SET 
                          title = ?, address = ?, city = ?, country = ?, 
                          postal_code = ?, phone = ?, is_default = ?, 
                          updated_at = NOW() 
                          WHERE id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssiii", $title, $address, $city, $country, $postal_code, $phone, $is_default, $address_id, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'تم تحديث العنوان بنجاح';
                header('Location: addresses.php?success=updated');
                exit();
            } else {
                $errors[] = 'حدث خطأ أثناء تحديث العنوان';
            }
        }
    }
    
    elseif ($action == 'delete_address') {
        $address_id = $_POST['address_id'];
        
        // حذف العنوان (تعطيله)
        $delete_sql = "UPDATE user_addresses SET is_active = 0 WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $address_id, $user_id);
        
        if ($delete_stmt->execute()) {
            $success_message = 'تم حذف العنوان بنجاح';
            header('Location: addresses.php?success=deleted');
            exit();
        } else {
            $errors[] = 'حدث خطأ أثناء حذف العنوان';
        }
    }
    
    elseif ($action == 'set_default') {
        $address_id = $_POST['address_id'];
        
        // إلغاء الافتراضي من جميع العناوين
        $reset_sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
        $reset_stmt = $conn->prepare($reset_sql);
        $reset_stmt->bind_param("i", $user_id);
        $reset_stmt->execute();
        
        // تعيين العنوان الجديد كافتراضي
        $default_sql = "UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?";
        $default_stmt = $conn->prepare($default_sql);
        $default_stmt->bind_param("ii", $address_id, $user_id);
        
        if ($default_stmt->execute()) {
            $success_message = 'تم تعيين العنوان كافتراضي بنجاح';
            header('Location: addresses.php?success=default_set');
            exit();
        } else {
            $errors[] = 'حدث خطأ أثناء تعيين العنوان كافتراضي';
        }
    }
}

// جلب تفاصيل عنوان للتعديل (إذا طُلب)
$edit_address = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM user_addresses WHERE id = ? AND user_id = ? AND is_active = 1";
    $edit_stmt = $conn->prepare($edit_sql);
    $edit_stmt->bind_param("ii", $edit_id, $user_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    if ($edit_result->num_rows > 0) {
        $edit_address = $edit_result->fetch_assoc();
    }
}

// رسائل النجاح
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success_message = 'تم إضافة العنوان بنجاح';
            break;
        case 'updated':
            $success_message = 'تم تحديث العنوان بنجاح';
            break;
        case 'deleted':
            $success_message = 'تم حذف العنوان بنجاح';
            break;
        case 'default_set':
            $success_message = 'تم تعيين العنوان كافتراضي بنجاح';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>العناوين - <?php echo htmlspecialchars($user['name'] ?? 'حسابي'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #6c757d;
            --accent-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #218838);
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }
        
        .header-btn {
            color: white;
            font-size: 20px;
            text-decoration: none;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .header-btn:hover {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .header-title {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-content {
            padding: 15px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--dark-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-link {
            font-size: 14px;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .address-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .address-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .address-card.default {
            border: 2px solid var(--primary-color);
            background-color: #f8fff9;
        }
        
        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .address-title {
            font-weight: bold;
            color: var(--dark-color);
            font-size: 16px;
        }
        
        .address-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        
        .address-details {
            color: var(--secondary-color);
            line-height: 1.6;
        }
        
        .address-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .action-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .edit-btn {
            background-color: #e7f3ff;
            color: #0066cc;
        }
        
        .edit-btn:hover {
            background-color: #d0e7ff;
        }
        
        .delete-btn {
            background-color: #ffeaea;
            color: #dc3545;
        }
        
        .delete-btn:hover {
            background-color: #ffdada;
        }
        
        .set-default-btn {
            background-color: #e8f5e9;
            color: var(--primary-color);
        }
        
        .set-default-btn:hover {
            background-color: #d4edda;
        }
        
        .add-address-btn {
            background: linear-gradient(135deg, var(--primary-color), #218838);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 20px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .add-address-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary-color);
        }
        
        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #218838);
            color: white;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), #218838);
            border: none;
            padding: 10px 30px;
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #218838, var(--primary-color));
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .nav-item {
            text-align: center;
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        
        .nav-item i {
            font-size: 20px;
        }
        
        .nav-item.active {
            color: var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .city-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .city-option {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .city-option:hover,
        .city-option.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>

    <!-- الهيدر -->
    <header class="main-header">
        <div class="header-content">
            <a href="profile.php" class="header-btn">
                <i class="fas fa-arrow-right"></i>
            </a>
            <h1 class="header-title">عناوين الشحن</h1>
            <div class="header-actions">
                <a href="notifications.php" class="header-btn position-relative">
                    <i class="fas fa-bell"></i>
                </a>
                <a href="cart.php" class="header-btn position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="notification-badge"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="main-content">
        
        <!-- رسائل النجاح والخطأ -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php foreach ($errors as $error): ?>
                    <div><i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?></div>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- قسم العناوين -->
        <section class="addresses-section">
            <div class="section-header mb-4">
                <h2 class="section-title">
                    عناويني
                    <span class="badge bg-primary"><?php echo count($addresses); ?></span>
                </h2>
            </div>
            
            <?php if (empty($addresses)): ?>
                <div class="empty-state">
                    <i class="fas fa-map-marker-alt"></i>
                    <h4>لا توجد عناوين</h4>
                    <p>لم تقم بإضافة أي عناوين بعد</p>
                    <p class="text-muted small">أضف عنوانك الأول لتسهيل عملية الشراء</p>
                </div>
            <?php else: ?>
                <?php foreach ($addresses as $address): ?>
                    <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                        <div class="address-header">
                            <h3 class="address-title">
                                <?php echo htmlspecialchars($address['title']); ?>
                                <?php if ($address['is_default']): ?>
                                    <span class="address-badge">افتراضي</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                        
                        <div class="address-details">
                            <p><strong>العنوان:</strong> <?php echo htmlspecialchars($address['address']); ?></p>
                            <p><strong>المدينة:</strong> <?php echo htmlspecialchars($address['city']); ?></p>
                            <p><strong>البلد:</strong> <?php echo htmlspecialchars($address['country']); ?></p>
                            <?php if ($address['postal_code']): ?>
                                <p><strong>الرمز البريدي:</strong> <?php echo htmlspecialchars($address['postal_code']); ?></p>
                            <?php endif; ?>
                            <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($address['phone']); ?></p>
                        </div>
                        
                        <div class="address-actions">
                            <button class="action-btn edit-btn" 
                                    onclick="editAddress(<?php echo $address['id']; ?>)">
                                <i class="fas fa-edit"></i> تعديل
                            </button>
                            
                            <?php if (!$address['is_default']): ?>
                                <button class="action-btn set-default-btn" 
                                        onclick="setDefaultAddress(<?php echo $address['id']; ?>)">
                                    <i class="fas fa-check-circle"></i> تعيين كافتراضي
                                </button>
                            <?php endif; ?>
                            
                            <button class="action-btn delete-btn" 
                                    onclick="deleteAddress(<?php echo $address['id']; ?>)">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <button class="add-address-btn" onclick="openAddModal()">
                <i class="fas fa-plus"></i> إضافة عنوان جديد
            </button>
        </section>
        
        <!-- نصائح -->
        <div class="alert alert-info mt-4">
            <h5><i class="fas fa-lightbulb"></i> نصائح للعناوين:</h5>
            <ul class="mb-0">
                <li>أضف عنواناً صحيحاً ودقيقاً لتسهيل عملية التوصيل</li>
                <li>يمكنك تعيين عنوان افتراضي لتسهيل عملية الشراء</li>
                <li>تأكد من صحة رقم الهاتف للتواصل معك</li>
                <li>يمكنك إضافة أكثر من عنوان للشحن إلى أماكن مختلفة</li>
            </ul>
        </div>
    </main>

    <!-- مودال إضافة عنوان -->
    <div class="modal fade" id="addAddressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة عنوان جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="addAddressForm">
                        <input type="hidden" name="action" value="add_address">
                        
                        <div class="mb-3">
                            <label class="form-label">عنوان العناوين (مثال: المنزل، العمل)</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="title" 
                                   placeholder="مثل: العنوان الرئيسي" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">العنوان التفصيلي</label>
                            <textarea class="form-control" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="الشارع، الحي، الحي..." 
                                      required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المدينة</label>
                                <select class="form-select" name="city" required>
                                    <option value="">اختر المدينة</option>
                                    <option value="الرياض">الرياض</option>
                                    <option value="جدة">جدة</option>
                                    <option value="مكة">مكة</option>
                                    <option value="المدينة">المدينة المنورة</option>
                                    <option value="الدمام">الدمام</option>
                                    <option value="الخبر">الخبر</option>
                                    <option value="الطائف">الطائف</option>
                                    <option value="تبوك">تبوك</option>
                                    <option value="أبها">أبها</option>
                                    <option value="حائل">حائل</option>
                                    <option value="نجران">نجران</option>
                                    <option value="جازان">جازان</option>
                                    <option value="الباحة">الباحة</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البلد</label>
                                <select class="form-select" name="country" required>
                                    <option value="">اختر البلد</option>
                                    <option value="السعودية" selected>المملكة العربية السعودية</option>
                                    <option value="الإمارات">الإمارات العربية المتحدة</option>
                                    <option value="الكويت">الكويت</option>
                                    <option value="قطر">قطر</option>
                                    <option value="عمان">سلطنة عمان</option>
                                    <option value="البحرين">البحرين</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الرمز البريدي (اختياري)</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="postal_code" 
                                       placeholder="12345">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" 
                                       class="form-control" 
                                       name="phone" 
                                       placeholder="05xxxxxxxx" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_default" 
                                   id="addIsDefault"
                                   checked>
                            <label class="form-check-label" for="addIsDefault">
                                تعيين كعنوان افتراضي
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save"></i> حفظ العنوان
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تعديل عنوان -->
    <div class="modal fade" id="editAddressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل العنوان</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="editAddressForm">
                        <input type="hidden" name="action" value="edit_address">
                        <input type="hidden" name="address_id" id="editAddressId">
                        
                        <div class="mb-3">
                            <label class="form-label">عنوان العناوين</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="title" 
                                   id="editTitle" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">العنوان التفصيلي</label>
                            <textarea class="form-control" 
                                      name="address" 
                                      id="editAddress" 
                                      rows="3" 
                                      required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">المدينة</label>
                                <select class="form-select" name="city" id="editCity" required>
                                    <option value="">اختر المدينة</option>
                                    <option value="الرياض">الرياض</option>
                                    <option value="جدة">جدة</option>
                                    <option value="مكة">مكة</option>
                                    <option value="المدينة">المدينة المنورة</option>
                                    <option value="الدمام">الدمام</option>
                                    <option value="الخبر">الخبر</option>
                                    <option value="الطائف">الطائف</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">البلد</label>
                                <select class="form-select" name="country" id="editCountry" required>
                                    <option value="">اختر البلد</option>
                                    <option value="السعودية">المملكة العربية السعودية</option>
                                    <option value="الإمارات">الإمارات العربية المتحدة</option>
                                    <option value="الكويت">الكويت</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">الرمز البريدي (اختياري)</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="postal_code" 
                                       id="editPostalCode">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="tel" 
                                       class="form-control" 
                                       name="phone" 
                                       id="editPhone" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_default" 
                                   id="editIsDefault">
                            <label class="form-check-label" for="editIsDefault">
                                تعيين كعنوان افتراضي
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تأكيد الحذف -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>هل أنت متأكد من حذف هذا العنوان؟</h5>
                    <p class="text-muted">لا يمكنك التراجع عن هذه العملية</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form method="POST" action="" id="deleteForm">
                        <input type="hidden" name="action" value="delete_address">
                        <input type="hidden" name="address_id" id="deleteAddressId">
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- الشريط السفلي -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="categories.php" class="nav-item">
            <i class="fas fa-th-large"></i>
            <span>الفئات</span>
        </a>
        <a href="cart.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>السلة</span>
        </a>
        <a href="order.php" class="nav-item">
            <i class="fas fa-list-alt"></i>
            <span>الطلبات</span>
        </a>
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // فتح مودال إضافة عنوان
        function openAddModal() {
            const modal = new bootstrap.Modal(document.getElementById('addAddressModal'));
            modal.show();
        }
        
        // تحرير العنوان
        function editAddress(addressId) {
            // جلب بيانات العنوان من الخادم
            $.ajax({
                url: 'get_address.php',
                method: 'GET',
                data: { id: addressId },
                success: function(response) {
                    if (response.success) {
                        const address = response.data;
                        document.getElementById('editAddressId').value = address.id;
                        document.getElementById('editTitle').value = address.title;
                        document.getElementById('editAddress').value = address.address;
                        document.getElementById('editCity').value = address.city;
                        document.getElementById('editCountry').value = address.country;
                        document.getElementById('editPostalCode').value = address.postal_code || '';
                        document.getElementById('editPhone').value = address.phone;
                        document.getElementById('editIsDefault').checked = address.is_default == 1;
                        
                        const modal = new bootstrap.Modal(document.getElementById('editAddressModal'));
                        modal.show();
                    } else {
                        alert('حدث خطأ في جلب بيانات العنوان');
                    }
                },
                error: function() {
                    alert('حدث خطأ في الاتصال بالخادم');
                }
            });
        }
        
        // حذف العنوان
        function deleteAddress(addressId) {
            document.getElementById('deleteAddressId').value = addressId;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // تعيين العنوان كافتراضي
        function setDefaultAddress(addressId) {
            if (confirm('هل تريد تعيين هذا العنوان كافتراضي؟')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'set_default';
                form.appendChild(actionInput);
                
                const addressInput = document.createElement('input');
                addressInput.type = 'hidden';
                addressInput.name = 'address_id';
                addressInput.value = addressId;
                form.appendChild(addressInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // إغلاق المودال بعد النجاح
        $(document).ready(function() {
            <?php if (isset($success_message)): ?>
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            <?php endif; ?>
            
            // إغلاق مودال الإضافة بعد النجاح
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
                if (modal) modal.hide();
            }
            
            // تأثيرات الواجهة
            $('.address-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
        
        // التحقق من رقم الهاتف
        document.getElementById('addAddressForm')?.addEventListener('submit', function(e) {
            const phone = this.querySelector('input[name="phone"]').value;
            if (!/^7[0-9]{8}$/.test(phone)) {
                e.preventDefault();
                alert('يرجى إدخال رقم هاتف سعودي صحيح (مثال: 774252137)');
            }
        });
        
        document.getElementById('editAddressForm')?.addEventListener('submit', function(e) {
            const phone = this.querySelector('input[name="phone"]').value;
            if (!/^7[0-9]{8}$/.test(phone)) {
                e.preventDefault();
                alert('يرجى إدخال رقم هاتف سعودي صحيح (مثال: 0512345678)');
            }
        });
    </script>
</body>
</html>