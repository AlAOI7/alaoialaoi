<?php
session_start();
require_once 'config.php'; // يحتوي على $conn

// دالة تحديث إعداد (بدل PDO)
function setSetting($key, $value) {
    global $conn;

    $key = $conn->real_escape_string($key);
    $value = $conn->real_escape_string($value);

    // تحقق إذا الإعداد موجود
    $check = $conn->query("SELECT id FROM settings WHERE setting_key='$key' LIMIT 1");

    if ($check->num_rows > 0) {
        // تحديث
        $conn->query("UPDATE settings SET setting_value='$value' WHERE setting_key='$key'");
    } else {
        // إضافة جديد
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
    }
}


// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // تحديث القيم النصية
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8);
            setSetting($setting_key, $value);
        }
    }

    // معالجة رفع الصور
    if (!empty($_FILES)) {
        $upload_dir = '../uploads/settings/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {

                $setting_key = substr($key, 8);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_name = $setting_key . '_' . time() . "." . $ext;

                $path = $upload_dir . $new_name;

                if (move_uploaded_file($file['tmp_name'], $path)) {
                    setSetting($setting_key, $new_name);
                }
            }
        }
    }

    $_SESSION['message'] = "تم حفظ الإعدادات بنجاح!";
    header("Location: settings.php");
    exit();
}


// جلب الإعدادات من قاعدة البيانات
$sql = "SELECT * FROM settings ORDER BY setting_group, sort_order";
$result = $conn->query($sql);

$settings = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[] = $row;
    }
}


// تجميع الإعدادات حسب المجموعات
$settings_by_group = [];
foreach ($settings as $setting) {
    $group = $setting['setting_group'];

    if (!isset($settings_by_group[$group])) {
        $settings_by_group[$group] = [];
    }

    $settings_by_group[$group][] = $setting;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الإعدادات - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            min-height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e1e5ee;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-weight: 700;
        }
        
        .settings-group {
            margin-bottom: 30px;
        }
        
        .settings-group h4 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .setting-item {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
        }
        
        .setting-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 100px;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 5px;
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: white;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .tab-content {
            background-color: white;
            border-radius: 0 0 12px 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 15px;
            }
        }
        
        .note-editor.note-frame {
            border-radius: 8px;
            border: 1px solid #e1e5ee;
        }
        
        .note-editor.note-frame .note-toolbar {
            border-bottom: 1px solid #e1e5ee;
            border-radius: 8px 8px 0 0;
        }
        
        .note-editor.note-frame .note-statusbar {
            border-radius: 0 0 8px 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-4">لوحة التحكم</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                الرئيسية
                            </a>
                        </li>
                        <li>
                            <a href="products.php" class="nav-link">
                                <i class="fas fa-box me-2"></i>
                                المنتجات
                            </a>
                        </li>
                        <li>
                            <a href="offers.php" class="nav-link">
                                <i class="fas fa-tags me-2"></i>
                                العروض
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="nav-link">
                                <i class="fas fa-shopping-cart me-2"></i>
                                الطلبات
                            </a>
                        </li>
                        <li>
                            <a href="customers.php" class="nav-link">
                                <i class="fas fa-users me-2"></i>
                                العملاء
                            </a>
                        </li>
                        <li>
                            <a href="settings.php" class="nav-link active">
                                <i class="fas fa-cogs me-2"></i>
                                الإعدادات
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-cogs me-2"></i> إدارة الإعدادات</h2>
                </div>

                <!-- رسائل التنبيه -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- نموذج الإعدادات -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">الإعدادات العامة</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab">المحتوى</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">إعدادات النظام</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="tab-content" id="settingsTabsContent">
                                <!-- تبويب الإعدادات العامة -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row">
                                        <?php if (isset($settings_by_group['general'])): ?>
                                            <?php foreach ($settings_by_group['general'] as $setting): ?>
                                                <div class="col-md-6 setting-item">
                                                    <div class="setting-label"><?php echo $setting['display_name']; ?></div>
                                                    <?php if ($setting['setting_type'] == 'text'): ?>
                                                        <input type="text" class="form-control" name="setting_<?php echo $setting['setting_key']; ?>" 
                                                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                    <?php elseif ($setting['setting_type'] == 'textarea'): ?>
                                                        <textarea class="form-control" name="setting_<?php echo $setting['setting_key']; ?>" 
                                                                  rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                                    <?php elseif ($setting['setting_type'] == 'image'): ?>
                                                        <input type="file" class="form-control" name="setting_<?php echo $setting['setting_key']; ?>" 
                                                               accept="image/*">
                                                        <?php if (!empty($setting['setting_value'])): ?>
                                                            <div class="mt-2">
                                                                <img src="../uploads/settings/<?php echo $setting['setting_value']; ?>" 
                                                                     class="image-preview" alt="Preview">
                                                                <div class="form-text">الصورة الحالية</div>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php elseif ($setting['setting_type'] == 'boolean'): ?>
                                                        <select class="form-select" name="setting_<?php echo $setting['setting_key']; ?>">
                                                            <option value="1" <?php echo $setting['setting_value'] == '1' ? 'selected' : ''; ?>>مفعل</option>
                                                            <option value="0" <?php echo $setting['setting_value'] == '0' ? 'selected' : ''; ?>>غير مفعل</option>
                                                        </select>
                                                    <?php endif; ?>
                                                    <div class="setting-description"><?php echo $setting['description']; ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- تبويب المحتوى -->
                                <div class="tab-pane fade" id="content" role="tabpanel">
                                    <?php if (isset($settings_by_group['content'])): ?>
                                        <?php foreach ($settings_by_group['content'] as $setting): ?>
                                            <div class="setting-item">
                                                <div class="setting-label"><?php echo $setting['display_name']; ?></div>
                                                <textarea class="form-control summernote" name="setting_<?php echo $setting['setting_key']; ?>" 
                                                          rows="10"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                                <div class="setting-description"><?php echo $setting['description']; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- تبويب إعدادات النظام -->
                                <div class="tab-pane fade" id="system" role="tabpanel">
                                    <div class="row">
                                        <?php if (isset($settings_by_group['system'])): ?>
                                            <?php foreach ($settings_by_group['system'] as $setting): ?>
                                                <div class="col-md-6 setting-item">
                                                    <div class="setting-label"><?php echo $setting['display_name']; ?></div>
                                                    <?php if ($setting['setting_type'] == 'text'): ?>
                                                        <input type="text" class="form-control" name="setting_<?php echo $setting['setting_key']; ?>" 
                                                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                    <?php elseif ($setting['setting_type'] == 'textarea'): ?>
                                                        <textarea class="form-control" name="setting_<?php echo $setting['setting_key']; ?>" 
                                                                  rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                                    <?php elseif ($setting['setting_type'] == 'boolean'): ?>
                                                        <select class="form-select" name="setting_<?php echo $setting['setting_key']; ?>">
                                                            <option value="1" <?php echo $setting['setting_value'] == '1' ? 'selected' : ''; ?>>مفعل</option>
                                                            <option value="0" <?php echo $setting['setting_value'] == '0' ? 'selected' : ''; ?>>غير مفعل</option>
                                                        </select>
                                                    <?php endif; ?>
                                                    <div class="setting-description"><?php echo $setting['description']; ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> حفظ الإعدادات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            // تهيئة محرر النصوص Summernote
            $('.summernote').summernote({
                height: 200,
                lang: 'ar-AR',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // عرض معاينة الصورة قبل الرفع
            $('input[type="file"]').on('change', function() {
                const input = this;
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    const previewContainer = $(this).siblings('.mt-2');
                    
                    reader.onload = function(e) {
                        if (previewContainer.length > 0) {
                            previewContainer.find('img').attr('src', e.target.result);
                        } else {
                            $(input).after(`
                                <div class="mt-2">
                                    <img src="${e.target.result}" class="image-preview" alt="Preview">
                                    <div class="form-text">معاينة الصورة الجديدة</div>
                                </div>
                            `);
                        }
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            });
        });
    </script>
</body>
</html>
