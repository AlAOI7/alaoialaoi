<?php
require_once 'config/database.php';

// التحقق من صلاحية المشرف
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// جلب البيانات الحالية
$query = "SELECT * FROM about WHERE is_active = 1 LIMIT 1";
$result = $conn->query($query);
$about = $result->fetch_assoc();

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // جمع البيانات
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $vision = $conn->real_escape_string($_POST['vision']);
    $mission = $conn->real_escape_string($_POST['mission']);
    $story = $conn->real_escape_string($_POST['story']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $facebook = $conn->real_escape_string($_POST['facebook']);
    $instagram = $conn->real_escape_string($_POST['instagram']);
    $twitter = $conn->real_escape_string($_POST['twitter']);
    $working_hours = $conn->real_escape_string($_POST['working_hours']);
    $shipping_info = $conn->real_escape_string($_POST['shipping_info']);
    $return_policy = $conn->real_escape_string($_POST['return_policy']);
    $privacy_policy = $conn->real_escape_string($_POST['privacy_policy']);
    $terms_conditions = $conn->real_escape_string($_POST['terms_conditions']);
    $meta_title = $conn->real_escape_string($_POST['meta_title']);
    $meta_description = $conn->real_escape_string($_POST['meta_description']);
    $meta_keywords = $conn->real_escape_string($_POST['meta_keywords']);
    
    // تجهيز قيم JSON
    $values = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($_POST["value_name_$i"]) && !empty($_POST["value_desc_$i"])) {
            $values[$_POST["value_name_$i"]] = $_POST["value_desc_$i"];
        }
    }
    $values_json = json_encode($values, JSON_UNESCAPED_UNICODE);
    
    $features = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($_POST["feature_name_$i"]) && !empty($_POST["feature_desc_$i"])) {
            $features[$_POST["feature_name_$i"]] = $_POST["feature_desc_$i"];
        }
    }
    $features_json = json_encode($features, JSON_UNESCAPED_UNICODE);
    
    // معالجة رفع الصور
    $logo = $about['logo'] ?? '';
    $hero_image = $about['hero_image'] ?? '';
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo = uploadImage($_FILES['logo'], 'logo');
    }
    
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $hero_image = uploadImage($_FILES['hero_image'], 'hero');
    }
    
    // تحديث البيانات
    if ($about) {
        // تحديث سجل موجود
        $stmt = $conn->prepare("
            UPDATE about SET 
                company_name = ?,
                logo = ?,
                hero_image = ?,
                vision = ?,
                mission = ?,
                story = ?,
                values = ?,
                features = ?,
                address = ?,
                phone = ?,
                email = ?,
                whatsapp = ?,
                facebook = ?,
                instagram = ?,
                twitter = ?,
                working_hours = ?,
                shipping_info = ?,
                return_policy = ?,
                privacy_policy = ?,
                terms_conditions = ?,
                meta_title = ?,
                meta_description = ?,
                meta_keywords = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "sssssssssssssssssssssssi",
            $company_name, $logo, $hero_image, $vision, $mission, $story,
            $values_json, $features_json, $address, $phone, $email, $whatsapp,
            $facebook, $instagram, $twitter, $working_hours, $shipping_info,
            $return_policy, $privacy_policy, $terms_conditions, $meta_title,
            $meta_description, $meta_keywords, $about['id']
        );
    } else {
        // إضافة سجل جديد
        $stmt = $conn->prepare("
            INSERT INTO about (
                company_name, logo, hero_image, vision, mission, story,
                values, features, address, phone, email, whatsapp,
                facebook, instagram, twitter, working_hours, shipping_info,
                return_policy, privacy_policy, terms_conditions, meta_title,
                meta_description, meta_keywords
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "sssssssssssssssssssssss",
            $company_name, $logo, $hero_image, $vision, $mission, $story,
            $values_json, $features_json, $address, $phone, $email, $whatsapp,
            $facebook, $instagram, $twitter, $working_hours, $shipping_info,
            $return_policy, $privacy_policy, $terms_conditions, $meta_title,
            $meta_description, $meta_keywords
        );
    }
    
    if ($stmt->execute()) {
        $success_message = "تم تحديث بيانات 'من نحن' بنجاح!";
        // جلب البيانات المحدثة
        $result = $conn->query($query);
        $about = $result->fetch_assoc();
    } else {
        $error_message = "حدث خطأ أثناء تحديث البيانات: " . $conn->error;
    }
    $stmt->close();
}

// دالة رفع الصور
function uploadImage($file, $type) {
    $upload_dir = 'uploads/about/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return '';
    }
    
    if ($file['size'] > $max_size) {
        return '';
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return '';
}

// تحويل JSON إلى arrays للعرض
$values = !empty($about['values']) ? json_decode($about['values'], true) : [];
$features = !empty($about['features']) ? json_decode($about['features'], true) : [];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة "من نحن" | لوحة التحكم</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote Editor -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #6c757d;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fc;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-right: 4px solid var(--success-color);
        }
        
        .sidebar-menu i {
            width: 20px;
            text-align: center;
            margin-left: 10px;
        }
        
        .main-content {
            margin-right: 250px;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border: 1px solid #d1d3e2;
            border-radius: 10px;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #2e59d9;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 10px;
            border: 2px dashed #ddd;
            padding: 5px;
        }
        
        .nav-tabs .nav-link {
            color: var(--secondary-color);
            border: none;
            padding: 12px 25px;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background: transparent;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }
        
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        .table th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- الشريط الجانبي -->
    <nav class="sidebar">
        <div class="sidebar-header text-center">
            <h4 class="mb-0">Be Pretty</h4>
            <small class="text-white-50">لوحة التحكم</small>
        </div>
        
        <ul class="sidebar-menu mt-4">
            <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
            <li><a href="admin-products.php"><i class="fas fa-box"></i> المنتجات</a></li>
            <li><a href="admin-categories.php"><i class="fas fa-th-large"></i> الفئات</a></li>
            <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
            <li><a href="admin-users.php"><i class="fas fa-users"></i> المستخدمين</a></li>
            <li><a href="admin-blogs.php"><i class="fas fa-blog"></i> المدونة</a></li>
            <li><a href="admin-about.php" class="active"><i class="fas fa-info-circle"></i> من نحن</a></li>
            <li><a href="admin-settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
        </ul>
    </nav>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="fas fa-info-circle me-2"></i> إدارة "من نحن"</h2>
            <div class="d-flex gap-2">
                <a href="about.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-eye me-1"></i> معاينة الصفحة
                </a>
            </div>
        </div>

        <!-- رسائل التنبيه -->
        <?php if(isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- نموذج التعديل -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i> تعديل بيانات "من نحن"</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <!-- علامات التبويب -->
                    <ul class="nav nav-tabs mb-4" id="aboutTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">المعلومات الأساسية</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button">المحتوى</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="values-tab" data-bs-toggle="tab" data-bs-target="#values" type="button">القيم والمميزات</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">معلومات الاتصال</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="policies-tab" data-bs-toggle="tab" data-bs-target="#policies" type="button">السياسات</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button">SEO</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="aboutTabContent">
                        <!-- علامة التبويب 1: المعلومات الأساسية -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="company_name" 
                                           value="<?php echo htmlspecialchars($about['company_name'] ?? 'Be Pretty'); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($about['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الشعار</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                    <?php if(!empty($about['logo'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($about['logo']); ?>" class="preview-image" alt="الشعار الحالي">
                                        <small class="text-muted d-block mt-1">الشعار الحالي</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">صورة الهيدر</label>
                                    <input type="file" class="form-control" name="hero_image" accept="image/*">
                                    <?php if(!empty($about['hero_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($about['hero_image']); ?>" class="preview-image" alt="صورة الهيدر الحالية">
                                        <small class="text-muted d-block mt-1">الصورة الحالية</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- علامة التبويب 2: المحتوى -->
                        <div class="tab-pane fade" id="content" role="tabpanel">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">قصة الشركة <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="story" rows="5" required><?php echo htmlspecialchars($about['story'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الرؤية <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="vision" rows="4" required><?php echo htmlspecialchars($about['vision'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الرسالة <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="mission" rows="4" required><?php echo htmlspecialchars($about['mission'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- علامة التبويب 3: القيم والمميزات -->
                        <div class="tab-pane fade" id="values" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="mb-3">قيم الشركة (أقصى 5 قيم)</h6>
                                </div>
                                
                                <?php for($i = 1; $i <= 5; $i++): 
                                    $value_name = '';
                                    $value_desc = '';
                                    if (!empty($values)) {
                                        $keys = array_keys($values);
                                        if (isset($keys[$i-1])) {
                                            $value_name = $keys[$i-1];
                                            $value_desc = $values[$value_name];
                                        }
                                    }
                                ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اسم القيمة <?php echo $i; ?></label>
                                    <input type="text" class="form-control" name="value_name_<?php echo $i; ?>" 
                                           value="<?php echo htmlspecialchars($value_name); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">وصف القيمة <?php echo $i; ?></label>
                                    <input type="text" class="form-control" name="value_desc_<?php echo $i; ?>" 
                                           value="<?php echo htmlspecialchars($value_desc); ?>">
                                </div>
                                <?php endfor; ?>
                                
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3">مميزات الشركة (أقصى 5 مميزات)</h6>
                                </div>
                                
                                <?php for($i = 1; $i <= 5; $i++): 
                                    $feature_name = '';
                                    $feature_desc = '';
                                    if (!empty($features)) {
                                        $keys = array_keys($features);
                                        if (isset($keys[$i-1])) {
                                            $feature_name = $keys[$i-1];
                                            $feature_desc = $features[$feature_name];
                                        }
                                    }
                                ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اسم الميزة <?php echo $i; ?></label>
                                    <input type="text" class="form-control" name="feature_name_<?php echo $i; ?>" 
                                           value="<?php echo htmlspecialchars($feature_name); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">وصف الميزة <?php echo $i; ?></label>
                                    <input type="text" class="form-control" name="feature_desc_<?php echo $i; ?>" 
                                           value="<?php echo htmlspecialchars($feature_desc); ?>">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- علامة التبويب 4: معلومات الاتصال -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">العنوان</label>
                                    <input type="text" class="form-control" name="address" 
                                           value="<?php echo htmlspecialchars($about['address'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الهاتف</label>
                                    <input type="text" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars($about['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">واتساب</label>
                                    <input type="text" class="form-control" name="whatsapp" 
                                           value="<?php echo htmlspecialchars($about['whatsapp'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ساعات العمل</label>
                                    <input type="text" class="form-control" name="working_hours" 
                                           value="<?php echo htmlspecialchars($about['working_hours'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">فيسبوك</label>
                                    <input type="url" class="form-control" name="facebook" 
                                           value="<?php echo htmlspecialchars($about['facebook'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">انستجرام</label>
                                    <input type="url" class="form-control" name="instagram" 
                                           value="<?php echo htmlspecialchars($about['instagram'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تويتر</label>
                                    <input type="url" class="form-control" name="twitter" 
                                           value="<?php echo htmlspecialchars($about['twitter'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- علامة التبويب 5: السياسات -->
                        <div class="tab-pane fade" id="policies" role="tabpanel">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">معلومات الشحن والتوصيل</label>
                                    <textarea class="form-control" name="shipping_info" rows="4"><?php echo htmlspecialchars($about['shipping_info'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">سياسة الإرجاع والاستبدال</label>
                                    <textarea class="form-control" name="return_policy" rows="4"><?php echo htmlspecialchars($about['return_policy'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">سياسة الخصوصية</label>
                                    <textarea class="form-control" name="privacy_policy" rows="4"><?php echo htmlspecialchars($about['privacy_policy'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">الشروط والأحكام</label>
                                    <textarea class="form-control" name="terms_conditions" rows="4"><?php echo htmlspecialchars($about['terms_conditions'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- علامة التبويب 6: SEO -->
                        <div class="tab-pane fade" id="seo" role="tabpanel">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">عنوان الصفحة (Meta Title)</label>
                                    <input type="text" class="form-control" name="meta_title" 
                                           value="<?php echo htmlspecialchars($about['meta_title'] ?? ''); ?>">
                                    <small class="text-muted">أقصى 60 حرفاً للحصول على أفضل نتائج SEO</small>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">وصف الصفحة (Meta Description)</label>
                                    <textarea class="form-control" name="meta_description" rows="3"><?php echo htmlspecialchars($about['meta_description'] ?? ''); ?></textarea>
                                    <small class="text-muted">أقصى 160 حرفاً</small>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">كلمات مفتاحية (Meta Keywords)</label>
                                    <textarea class="form-control" name="meta_keywords" rows="3"><?php echo htmlspecialchars($about['meta_keywords'] ?? ''); ?></textarea>
                                    <small class="text-muted">افصل الكلمات بفواصل</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> حفظ التغييرات
                        </button>
                        <button type="reset" class="btn btn-secondary ms-2">إعادة تعيين</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- معاينة البيانات -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-eye me-2"></i> معاينة البيانات الحالية</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>المجال</th>
                                <th>القيمة الحالية</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>اسم الشركة</strong></td>
                                <td><?php echo htmlspecialchars($about['company_name'] ?? 'غير محدد'); ?></td>
                                <td><span class="badge bg-success">نشط</span></td>
                            </tr>
                            <tr>
                                <td><strong>الشعار</strong></td>
                                <td>
                                    <?php if(!empty($about['logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($about['logo']); ?>" style="max-height: 50px;" alt="الشعار">
                                    <?php else: ?>
                                    <span class="text-muted">غير محدد</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo !empty($about['logo']) ? '<span class="badge bg-success">موجود</span>' : '<span class="badge bg-warning">مفقود</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>عدد القيم</strong></td>
                                <td><?php echo count($values); ?> قيم</td>
                                <td>
                                    <?php echo count($values) > 0 ? '<span class="badge bg-success">مكتمل</span>' : '<span class="badge bg-warning">ناقص</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>عدد المميزات</strong></td>
                                <td><?php echo count($features); ?> مميزات</td>
                                <td>
                                    <?php echo count($features) > 0 ? '<span class="badge bg-success">مكتمل</span>' : '<span class="badge bg-warning">ناقص</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>آخر تحديث</strong></td>
                                <td><?php echo date('Y/m/d H:i', strtotime($about['updated_at'] ?? 'now')); ?></td>
                                <td><span class="badge bg-info">محدث</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-ar-AR.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // تفعيل محرر النصوص لبعض الحقول
            $('textarea[name="story"], textarea[name="vision"], textarea[name="mission"]').summernote({
                height: 200,
                lang: 'ar-AR',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // عرض معاينة الصور قبل الرفع
            $('input[type="file"]').on('change', function() {
                const input = this;
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = $(input).closest('.mb-3').find('.preview-image');
                        if (preview.length === 0) {
                            $(input).after('<div class="mt-2"><img src="' + e.target.result + '" class="preview-image" alt="معاينة"></div>');
                        } else {
                            preview.attr('src', e.target.result);
                        }
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
            
            // إضافة صفوف ديناميكية للقيم والمميزات
            $('#addValueRow').on('click', function() {
                const rowCount = $('.value-row').length + 1;
                if (rowCount <= 5) {
                    $('#valuesContainer').append(`
                        <div class="row value-row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم القيمة ${rowCount}</label>
                                <input type="text" class="form-control" name="value_name_${rowCount}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">وصف القيمة ${rowCount}</label>
                                <input type="text" class="form-control" name="value_desc_${rowCount}">
                            </div>
                        </div>
                    `);
                }
            });
            
            // التحقق من صحة البيانات قبل الإرسال
            $('form').on('submit', function(e) {
                let isValid = true;
                
                // التحقق من الحقول المطلوبة
                const requiredFields = $('[required]');
                requiredFields.each(function() {
                    if ($(this).val().trim() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('يرجى ملء جميع الحقول المطلوبة');
                }
            });
        });
    </script>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>