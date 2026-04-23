<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العلامات التجارية</title>
    <style>
        /* CSS لتنسيق الصور */
        .brand-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 2px;
        }
        
        .logo-preview img {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 2px solid #ddd;
            border-radius: 10px;
            background-color: #f8f9fa;
            padding: 5px;
            display: none;
        }
        
        .logo-preview {
            width: 150px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- قسم عرض العلامات -->
    <div class="content-section <?= $current_view == 'list' ? 'active' : '' ?>" id="brands-list-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>قائمة العلامات التجارية</h3>
            <form method="GET" class="search-box">
                <input type="hidden" name="view" value="list">
                <input type="text" name="search" class="form-control" placeholder="بحث في العلامات..."
                    value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> بحث
                </button>
                <?php if ($search): ?>
                    <a href="?view=list" class="btn btn-danger btn-sm">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الشعار</th>
                        <th>اسم العلامة</th>
                        <th>البلد</th>
                        <th>عدد المنتجات</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($brands_result->num_rows > 0): ?>
                        <?php 
                        // إعادة تعيين المؤشر
                        $brands_result->data_seek(0);
                        while ($brand = $brands_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td>
                                    <?php if ($brand['logo'] && file_exists($brand['logo'])): ?>
                                        <img src="<?= $brand['logo'] ?>?t=<?= time() ?>" 
                                             alt="شعار <?= htmlspecialchars($brand['name']) ?>" 
                                             class="brand-logo"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIGZpbGw9IiNGMEYyRjUiLz48cGF0aCBkPSJNMTUgMjBIMzVWMzVIMTVWMjBaIiBmaWxsPSIjQjVCNUI1Ii8+PHBhdGggZD0iTTIwIDE1SDMwVjIwSDIwVjE1WiIgZmlsbD0iI0I1QjVCNSIvPjwvc3ZnPg=='">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($brand['name']) ?></td>
                                <td><?= htmlspecialchars($brand['country']) ?></td>
                                <td>
                                    <span class="status-badge <?= $brand['products_count'] > 0 ? 'status-active' : 'status-inactive' ?>">
                                        <?= $brand['products_count'] ?> منتج
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?= $brand['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                        <?= $brand['status'] == 'active' ? 'نشط' : 'غير نشط' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-small">
                                        <a href="?view=edit&edit=<?= $brand['id'] ?>" class="action-btn edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="id" value="<?= $brand['id'] ?>">
                                            <button type="submit" name="delete_brand" class="action-btn delete"
                                                onclick="return confirm('هل أنت متأكد من حذف العلامة التجارية <?= htmlspecialchars($brand['name']) ?>؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php if ($brand['website']): ?>
                                            <a href="<?= $brand['website'] ?>" target="_blank" class="action-btn view" title="زيارة الموقع">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                <i class="fas fa-box-open" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                <?= $search ? 'لم يتم العثور على علامات تجارية تطابق بحثك' : 'لا توجد علامات تجارية مضافة' ?>
                                <?php if (!$search): ?>
                                    <br>
                                    <button class="btn btn-success mt-3" id="add-brand-from-empty">
                                        <i class="fas fa-plus"></i> إضافة أول علامة تجارية
                                    </button>
                                <?php endif; ?>
                                </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- قسم إضافة علامة -->
    <div class="content-section <?= $current_view == 'add' ? 'active' : '' ?>" id="add-brand-section">
        <h3>إضافة علامة تجارية جديدة</h3>

        <div class="logo-upload">
            <div class="logo-preview" id="brand-logo-preview-container" onclick="document.getElementById('brand-logo-upload').click()">
                <img src="" alt="شعار العلامة" id="brand-logo-preview">
                <span id="no-logo" style="color: #999; display: flex; flex-direction: column; align-items: center;">
                    <i class="fas fa-image" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                    <span>انقر لتحميل الشعار</span>
                </span>
            </div>
            <div>
                <input type="file" id="brand-logo-upload" name="logo" accept="image/jpeg, image/png, image/gif" style="display: none;">
                <p style="font-size: 12px; color: #666; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i>
                    الحجم الموصى به: 200x200 بكسل<br>
                    الأنواع المسموحة: JPG, PNG, GIF<br>
                </p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="add-brand-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="brand-name">اسم العلامة التجارية <span style="color: #ff4757;">*</span></label>
                    <input type="text" id="brand-name" name="name" class="form-control"
                        placeholder="أدخل اسم العلامة التجارية" required
                        value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="brand-country">البلد</label>
                    <select id="brand-country" name="country" class="form-control">
                        <option value="">اختر البلد</option>
                        <option value="SA" <?= (isset($_POST['country']) && $_POST['country'] == 'SA') ? 'selected' : '' ?>>المملكة العربية السعودية</option>
                        <option value="US" <?= (isset($_POST['country']) && $_POST['country'] == 'US') ? 'selected' : '' ?>>الولايات المتحدة</option>
                        <option value="KR" <?= (isset($_POST['country']) && $_POST['country'] == 'KR') ? 'selected' : '' ?>>كوريا الجنوبية</option>
                        <option value="CN" <?= (isset($_POST['country']) && $_POST['country'] == 'CN') ? 'selected' : '' ?>>الصين</option>
                        <option value="JP" <?= (isset($_POST['country']) && $_POST['country'] == 'JP') ? 'selected' : '' ?>>اليابان</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="brand-website">الموقع الإلكتروني</label>
                    <input type="url" id="brand-website" name="website" class="form-control"
                        placeholder="https://example.com"
                        value="<?= isset($_POST['website']) ? htmlspecialchars($_POST['website']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="brand-status">حالة العلامة</label>
                    <select id="brand-status" name="status" class="form-control">
                        <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : 'selected' ?>>نشط</option>
                        <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : '' ?>>غير نشط</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="brand-description">وصف العلامة التجارية</label>
                <textarea id="brand-description" name="description" class="form-control" rows="4"
                    placeholder="أدخل وصفاً للعلامة التجارية"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="add_brand">
                    <i class="fas fa-plus"></i> إضافة العلامة
                </button>
                <button type="button" class="btn btn-danger" id="cancel-add-brand">
                    <i class="fas fa-times"></i> إلغاء
                </button>
            </div>
        </form>
    </div>

    <!-- JavaScript لمعالجة عرض الصورة -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // معاينة الصورة قبل الرفع
            const logoUpload = document.getElementById('brand-logo-upload');
            const logoPreview = document.getElementById('brand-logo-preview');
            const noLogoText = document.getElementById('no-logo');
            const previewContainer = document.getElementById('brand-logo-preview-container');
            
            if (logoUpload) {
                logoUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            logoPreview.src = e.target.result;
                            logoPreview.style.display = 'block';
                            noLogoText.style.display = 'none';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // النقر على container لفتح ملف الرفع
            if (previewContainer) {
                previewContainer.addEventListener('click', function() {
                    logoUpload.click();
                });
            }
            
            // زر إلغاء الإضافة
            const cancelBtn = document.getElementById('cancel-add-brand');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    window.location.href = '?view=list';
                });
            }
            
            // زر إضافة من صفحة فارغة
            const addFromEmptyBtn = document.getElementById('add-brand-from-empty');
            if (addFromEmptyBtn) {
                addFromEmptyBtn.addEventListener('click', function() {
                    window.location.href = '?view=add';
                });
            }
        });
        
        // دالة لتحويل الصور المكسورة إلى صورة افتراضية
        function handleImageError(img) {
            img.onerror = null;
            img.style.display = 'none';
            img.parentElement.innerHTML = '<div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;"><i class="fas fa-image"></i></div>';
        }
    </script>
</body>
</html>