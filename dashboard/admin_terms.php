<?php
// admin_terms.php
session_start();
require_once 'config.php';

// تحقق من تسجيل دخول المدير (يمكنك إضافة نظام تسجيل دخول)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// معالجة إضافة شروط جديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_term'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $note = $_POST['note'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO terms (title, content, note, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $note, $display_order, $is_active]);
        
        // تحديث تاريخ آخر تحديث
        $updateDate = date('d F Y', strtotime('today'));
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'terms_last_update'");
        $stmt->execute([$updateDate]);
        
        $success = "تم إضافة الشرط بنجاح!";
    } catch(PDOException $e) {
        $error = "خطأ في إضافة الشرط: " . $e->getMessage();
    }
}

// معالجة تعديل الشرط
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_term'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $note = $_POST['note'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE terms SET title = ?, content = ?, note = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$title, $content, $note, $display_order, $is_active, $id]);
        
        // تحديث تاريخ آخر تحديث
        $updateDate = date('d F Y', strtotime('today'));
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'terms_last_update'");
        $stmt->execute([$updateDate]);
        
        $success = "تم تحديث الشرط بنجاح!";
    } catch(PDOException $e) {
        $error = "خطأ في تحديث الشرط: " . $e->getMessage();
    }
}

// معالجة حذف الشرط
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM terms WHERE id = ?");
        $stmt->execute([$id]);
        
        // تحديث تاريخ آخر تحديث
        $updateDate = date('d F Y', strtotime('today'));
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'terms_last_update'");
        $stmt->execute([$updateDate]);
        
        $success = "تم حذف الشرط بنجاح!";
    } catch(PDOException $e) {
        $error = "خطأ في حذف الشرط: " . $e->getMessage();
    }
}

// استرجاع جميع الشروط
try {
    $stmt = $pdo->query("SELECT * FROM terms ORDER BY display_order ASC, created_at DESC");
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // استرجاع تاريخ آخر تحديث
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'terms_last_update'");
    $lastUpdate = $stmt->fetchColumn();
} catch(PDOException $e) {
    $error = "خطأ في تحميل البيانات: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الشروط | لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
            color: white;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin: 5px 0;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
            color: #1abc9c;
        }
        .table-actions {
            min-width: 150px;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
        .term-content {
            max-height: 100px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-0">
                <div class="position-sticky pt-3">
                    <div class="p-3">
                        <h4 class="text-center mb-4">
                            <i class="fas fa-crown me-2"></i>لوحة التحكم
                        </h4>
                    </div>
                    <ul class="nav flex-column">
                        <li><a href="admin_dashboard.php"><i class="fas fa-home me-2"></i>الرئيسية</a></li>
                        <li><a href="admin_products.php"><i class="fas fa-box me-2"></i>المنتجات</a></li>
                        <li><a href="admin_orders.php"><i class="fas fa-shopping-cart me-2"></i>الطلبات</a></li>
                        <li><a href="admin_users.php"><i class="fas fa-users me-2"></i>المستخدمين</a></li>
                        <li><a href="admin_terms.php" class="active"><i class="fas fa-file-contract me-2"></i>إدارة الشروط</a></li>
                        <li><a href="admin_settings.php"><i class="fas fa-cog me-2"></i>الإعدادات</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>
                    </ul>
                </div>
            </nav>

            <!-- المحتوى الرئيسي -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark">
                        <i class="fas fa-file-contract me-2 text-primary"></i>
                        إدارة الشروط والأحكام
                    </h2>
                    <div class="last-update-badge">
                        <span class="badge bg-info">
                            <i class="fas fa-history me-1"></i>
                            آخر تحديث: <?php echo htmlspecialchars($lastUpdate); ?>
                        </span>
                    </div>
                </div>

                <!-- رسائل التنبيه -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- نموذج إضافة شرط جديد -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>إضافة شرط جديد</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">عنوان الشرط</label>
                                    <input type="text" name="title" class="form-control" required 
                                           placeholder="أدخل عنوان الشرط">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ترتيب العرض</label>
                                    <input type="number" name="display_order" class="form-control" 
                                           value="0" min="0" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label d-block">الحالة</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                                        <label class="form-check-label">نشط</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">محتوى الشرط</label>
                                <textarea name="content" class="form-control" rows="4" required 
                                          placeholder="أدخل محتوى الشرط"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ملاحظة (اختياري)</label>
                                <textarea name="note" class="form-control" rows="2" 
                                          placeholder="أدخل ملاحظة إضافية"></textarea>
                            </div>
                            <button type="submit" name="add_term" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ الشرط
                            </button>
                        </form>
                    </div>
                </div>

                <!-- جدول عرض الشروط -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>قائمة الشروط</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($terms) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>العنوان</th>
                                            <th>المحتوى</th>
                                            <th>الترتيب</th>
                                            <th>الحالة</th>
                                            <th>تاريخ الإنشاء</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($terms as $term): ?>
                                        <tr>
                                            <td><?php echo $term['id']; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($term['title']); ?></td>
                                            <td>
                                                <div class="term-content">
                                                    <?php echo nl2br(htmlspecialchars(substr($term['content'], 0, 100))); ?>
                                                    <?php if (strlen($term['content']) > 100): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo $term['display_order']; ?></td>
                                            <td>
                                                <?php if ($term['is_active']): ?>
                                                    <span class="status-active">
                                                        <i class="fas fa-check-circle me-1"></i>نشط
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-inactive">
                                                        <i class="fas fa-times-circle me-1"></i>غير نشط
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($term['created_at'])); ?></td>
                                            <td class="table-actions">
                                                <!-- زر التعديل -->
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal<?php echo $term['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <!-- زر الحذف -->
                                                <a href="admin_terms.php?delete=<?php echo $term['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('هل أنت متأكد من حذف هذا الشرط؟')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal للتعديل -->
                                        <div class="modal fade" id="editModal<?php echo $term['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title">تعديل الشرط</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $term['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">عنوان الشرط</label>
                                                                <input type="text" name="title" class="form-control" 
                                                                       value="<?php echo htmlspecialchars($term['title']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">محتوى الشرط</label>
                                                                <textarea name="content" class="form-control" rows="5" required><?php echo htmlspecialchars($term['content']); ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">ملاحظة</label>
                                                                <textarea name="note" class="form-control" rows="2"><?php echo htmlspecialchars($term['note']); ?></textarea>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">ترتيب العرض</label>
                                                                    <input type="number" name="display_order" class="form-control" 
                                                                           value="<?php echo $term['display_order']; ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label d-block">الحالة</label>
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" 
                                                                               name="is_active" <?php echo $term['is_active'] ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label">نشط</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                            <button type="submit" name="edit_term" class="btn btn-warning">
                                                                <i class="fas fa-save me-2"></i>حفظ التعديلات
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد شروط مضافة حالياً
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تفعيل الحقول في المودال عند الفتح
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', function () {
                const textarea = this.querySelector('textarea');
                if (textarea) {
                    textarea.focus();
                }
            });
        });
    </script>
</body>
</html>