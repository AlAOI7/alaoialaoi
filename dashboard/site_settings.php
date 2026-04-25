<?php
// dashboard/site_settings.php - إعدادات الموقع العامة
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

$message = '';
$error = '';

// حفظ الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $allowed_keys = [
        'site_name', 'site_logo', 'about_text', 'footer_text',
        'contact_phone', 'contact_email', 'whatsapp', 'address',
        'facebook', 'instagram', 'twitter', 'snapchat', 'tiktok',
        'shipping_cost', 'free_shipping_min', 'tax_rate', 'currency',
    ];

    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($allowed_keys as $key) {
        $value = trim($_POST[$key] ?? '');
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
    }

    // معالجة رفع الشعار
    if (!empty($_FILES['logo_file']['name'])) {
        $upload_dir = '../uploads/settings/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
            $filename = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $upload_dir . $filename)) {
                $logo_path = 'uploads/settings/' . $filename;
                $stmt->bind_param("ss", ...['site_logo', $logo_path]);
                $stmt->execute();
            }
        }
    }

    // معالجة رفع صورة الخلفية
    if (!empty($_FILES['bg_file']['name'])) {
        $upload_dir = '../uploads/settings/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['bg_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'bg_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['bg_file']['tmp_name'], $upload_dir . $filename)) {
                $bg_path = 'uploads/settings/' . $filename;
                $stmt->bind_param("ss", ...['background_image', $bg_path]);
                $stmt->execute();
            }
        }
    }

    $message = '✅ تم حفظ الإعدادات بنجاح';
}

// جلب الإعدادات الحالية
$settings = [];
$res = $conn->query("SELECT setting_key, setting_value FROM settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

function sv($key, $default = '') {
    global $settings;
    return htmlspecialchars($settings[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الموقع | لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #6C63FF;
            --primary-light: #8A84FF;
            --primary-dark: #524BC2;
            --sidebar-width: 280px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --radius: 12px;
        }
        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f2f8 100%);
            direction: rtl;
            min-height: 100vh;
        }
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
        }
        .banner {
            background: linear-gradient(135deg, #2c3e50 0%, #df4803ff 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        .banner-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .banner h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0;
        }
        .banner p {
            margin: 10px 0 0;
            font-size: 1.2em;
            opacity: 0.9;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .settings-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 24px; background: white; }
        .settings-card .card-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 16px 16px 0 0; padding: 16px 20px; font-weight: bold; }
        .preview-logo { max-height: 80px; border-radius: 8px; border: 2px dashed #ddd; padding: 4px; }
        @media (max-width: 768px) {
            .main-content {
                margin-right: 0;
            }
        }
        .sticky-bottom-action {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="banner">
            <div class="banner-content">
                <h1>⚙️ إعدادات الموقع</h1>
                <p>تخصيص إعدادات المتجر العامة ومعلومات التواصل</p>
            </div>
        </div>

        <div class="dashboard-container" style="padding-bottom: 100px;">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">تفاصيل الإعدادات</h3>
                <button type="submit" name="save_settings" class="btn btn-primary btn-lg px-4 shadow-sm" style="border-radius: 10px; font-weight: bold;">
                    <i class="fas fa-save me-2"></i>حفظ جميع الإعدادات
                </button>
            </div>
            
            <div class="row">
                <!-- معلومات الموقع -->
                <div class="col-lg-6">
                    <div class="card settings-card">
                        <div class="card-header"><i class="fas fa-store me-2"></i>معلومات الموقع</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">اسم الموقع</label>
                                <input type="text" class="form-control" name="site_name" value="<?= sv('site_name', 'Be Pretty') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">الشعار الحالي</label>
                                <div class="mb-2">
                                    <?php if (!empty($settings['site_logo'])): ?>
                                        <img src="../<?= htmlspecialchars($settings['site_logo']) ?>" class="preview-logo" onerror="this.style.display='none'">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="form-control" name="logo_file" accept="image/*">
                                <small class="text-muted">أو أدخل مسار الشعار:</small>
                                <input type="text" class="form-control mt-1" name="site_logo" value="<?= sv('site_logo', 'img/logo.png') ?>" placeholder="img/logo.png">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">وصف الموقع / من نحن</label>
                                <textarea class="form-control" name="about_text" rows="3"><?= sv('about_text') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">نص الفوتر</label>
                                <input type="text" class="form-control" name="footer_text" value="<?= sv('footer_text', 'جميع الحقوق محفوظة') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">صورة خلفية الصفحة الرئيسية</label>
                                <input type="file" class="form-control" name="bg_file" accept="image/*">
                                <input type="text" class="form-control mt-1" name="background_image" value="<?= sv('background_image') ?>" placeholder="أو أدخل رابط الصورة">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- معلومات التواصل -->
                <div class="col-lg-6">
                    <div class="card settings-card">
                        <div class="card-header"><i class="fas fa-phone me-2"></i>معلومات التواصل</div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">رقم الهاتف</label>
                                <input type="text" class="form-control" name="contact_phone" value="<?= sv('contact_phone') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">رقم الواتساب (أرقام فقط)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                    <input type="text" class="form-control" name="whatsapp" value="<?= sv('whatsapp', '966500000000') ?>" placeholder="966500000000">
                                </div>
                                <small class="text-muted">مثال: 967712345678 (بدون علامة +)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="contact_email" value="<?= sv('contact_email') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">العنوان</label>
                                <input type="text" class="form-control" name="address" value="<?= sv('address') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- روابط التواصل الاجتماعي -->
                    <div class="card settings-card">
                        <div class="card-header"><i class="fas fa-share-alt me-2"></i>روابط التواصل الاجتماعي</div>
                        <div class="card-body p-4">
                            <?php
                            $socials = [
                                'facebook'  => ['fab fa-facebook', 'فيسبوك'],
                                'instagram' => ['fab fa-instagram', 'انستغرام'],
                                'twitter'   => ['fab fa-twitter', 'تويتر'],
                                'snapchat'  => ['fab fa-snapchat', 'سناب شات'],
                                'tiktok'    => ['fab fa-tiktok', 'تيك توك'],
                            ];
                            foreach ($socials as $key => [$icon, $label]):
                            ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="<?= $icon ?> me-1"></i><?= $label ?></label>
                                <input type="url" class="form-control" name="<?= $key ?>" value="<?= sv($key, '#') ?>" placeholder="https://...">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- إعدادات المتجر -->
                <div class="col-12">
                    <div class="card settings-card">
                        <div class="card-header"><i class="fas fa-shopping-cart me-2"></i>إعدادات المتجر</div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">تكلفة الشحن (ر.س)</label>
                                    <input type="number" class="form-control" name="shipping_cost" value="<?= sv('shipping_cost', '15') ?>" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">حد الشحن المجاني (ر.س)</label>
                                    <input type="number" class="form-control" name="free_shipping_min" value="<?= sv('free_shipping_min', '200') ?>" step="0.01">
                                    <small class="text-muted">0 = بدون شحن مجاني</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">نسبة الضريبة (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" value="<?= sv('tax_rate', '0') ?>" step="0.01" min="0" max="100">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">العملة الافتراضية</label>
                                    <select class="form-select" name="currency">
                                        <?php
                                        $cur_res = $conn->query("SELECT code, name, symbol FROM currencies WHERE status='active' ORDER BY is_default DESC, code");
                                        if ($cur_res) {
                                            while ($cur = $cur_res->fetch_assoc()) {
                                                $sel = ($settings['currency'] ?? 'SAR') === $cur['code'] ? 'selected' : '';
                                                echo "<option value='{$cur['code']}' $sel>{$cur['symbol']} {$cur['name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky-bottom-action">
                <button type="submit" name="save_settings" class="btn btn-primary btn-lg px-5 shadow-sm" style="border-radius: 10px; font-weight: bold;">
                    <i class="fas fa-save me-2"></i>حفظ جميع الإعدادات
                </button>
            </div>
        </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
