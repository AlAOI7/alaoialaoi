<?php
// admin/add-offer.php (ونفس الملف لـ edit-offer.php)
session_start();
require_once '../config/database.php';
// require_once 'admin_auth.php';

$offer = null;
$page_title = 'إضافة عرض جديد';
$submit_text = 'إضافة العرض';

// إذا كان تعديل
if (isset($_GET['id'])) {
    $offer_id = intval($_GET['id']);
    $sql = "SELECT * FROM offers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $offer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offer = $result->fetch_assoc();
    
    if ($offer) {
        $page_title = 'تعديل العرض: ' . $offer['title'];
        $submit_text = 'تحديث العرض';
    }
}

// جلب المنتجات للربط
$products_sql = "SELECT id, name, selling_price FROM products WHERE (is_active = 1 OR status = 'active') ORDER BY name ASC";
$products_result = $conn->query($products_sql);
$products = $products_result->fetch_all(MYSQLI_ASSOC);

// جلب المنتجات المرتبطة بالعرض (في حالة التعديل)
$selected_products = [];
if ($offer) {
    $selected_sql = "SELECT product_id FROM offer_products WHERE offer_id = ?";
    $selected_stmt = $conn->prepare($selected_sql);
    $selected_stmt->bind_param("i", $offer['id']);
    $selected_stmt->execute();
    $selected_result = $selected_stmt->get_result();
    $selected_products = array_column($selected_result->fetch_all(MYSQLI_ASSOC), 'product_id');
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .image-preview {
            width: 100%;
            height: 250px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8f9fa;
            cursor: pointer;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .preview-placeholder {
            color: #6c757d;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .product-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .selected-products {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-bullhorn text-primary me-2"></i>
                <?php echo $page_title; ?>
            </h2>
            <a href="offers.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
            </a>
        </div>
        
        <form id="offerForm" action="save-offer.php" method="POST" enctype="multipart/form-data">
            <?php if ($offer): ?>
                <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <!-- المعلومات الأساسية -->
                <div class="col-lg-8">
                    <div class="form-section">
                        <h4 class="mb-4 border-bottom pb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            المعلومات الأساسية
                        </h4>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">عنوان العرض *</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo $offer ? htmlspecialchars($offer['title']) : ''; ?>" 
                                   required
                                   placeholder="أدخل عنوان العرض">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">وصف العرض *</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      required
                                      placeholder="أدخل وصف مفصل للعرض"><?php echo $offer ? htmlspecialchars($offer['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label fw-bold">تاريخ البدء *</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="<?php echo $offer ? date('Y-m-d\TH:i', strtotime($offer['start_date'])) : date('Y-m-d\TH:i'); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label fw-bold">تاريخ الانتهاء *</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="<?php echo $offer ? date('Y-m-d\TH:i', strtotime($offer['end_date'])) : date('Y-m-d\TH:i', strtotime('+1 week')); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="button_text" class="form-label fw-bold">نص الزر</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="button_text" 
                                       name="button_text" 
                                       value="<?php echo $offer ? htmlspecialchars($offer['button_text']) : 'اكتشف العروض'; ?>" 
                                       placeholder="نص الزر">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label fw-bold">ترتيب العرض</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="display_order" 
                                       name="display_order" 
                                       value="<?php echo $offer ? $offer['display_order'] : 0; ?>" 
                                       min="0">
                                <small class="text-muted">الأرقام الأقل تظهر أولاً</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- المنتجات -->
                    <div class="form-section">
                        <h4 class="mb-4 border-bottom pb-2">
                            <i class="fas fa-boxes me-2"></i>
                            المنتجات المرتبطة
                        </h4>
                        
                        <div class="mb-3">
                            <label for="product_search" class="form-label fw-bold">بحث عن منتجات</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="product_search" 
                                       placeholder="ابحث عن منتجات...">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="selected-products mb-3" id="selectedProductsContainer">
                            <?php if (!empty($selected_products)): ?>
                                <?php foreach ($selected_products as $product_id): 
                                    $product_info = array_filter($products, fn($p) => $p['id'] == $product_id);
                                    $product_info = reset($product_info);
                                    if ($product_info): ?>
                                        <div class="product-item" data-product-id="<?php echo $product_id; ?>">
                                            <div>
                                                <strong><?php echo htmlspecialchars($product_info['name']); ?></strong>
                                                <br>
                                                <small class="text-muted">السعر: <?php echo number_format($product_info['selling_price'], 2); ?> ر.س</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger remove-product">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <input type="hidden" name="products[]" value="<?php echo $product_id; ?>">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">لم يتم إضافة أي منتجات بعد</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">قائمة المنتجات المتاحة</label>
                            <div id="productsList" style="max-height: 300px; overflow-y: auto;" class="border rounded p-3">
                                <?php foreach ($products as $product): ?>
                                    <div class="form-check mb-2 product-checkbox <?php echo in_array($product['id'], $selected_products) ? 'd-none' : ''; ?>" 
                                         data-product-id="<?php echo $product['id']; ?>"
                                         data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                         data-product-price="<?php echo $product['selling_price']; ?>">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               value="<?php echo $product['id']; ?>" 
                                               id="product_<?php echo $product['id']; ?>"
                                               <?php echo in_array($product['id'], $selected_products) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="product_<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                            <span class="text-muted">(<?php echo number_format($product['selling_price'], 2); ?> ر.س)</span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- الصورة والإعدادات -->
                <div class="col-lg-4">
                    <div class="form-section">
                        <h4 class="mb-4 border-bottom pb-2">
                            <i class="fas fa-image me-2"></i>
                            صورة العرض
                        </h4>
                        
                        <div class="mb-3">
                            <div class="image-preview" id="imagePreview">
                                <?php if ($offer && !empty($offer['image'])): ?>
                                    <img src="../<?php echo $offer['image']; ?>" alt="صورة العرض">
                                <?php else: ?>
                                    <div class="preview-placeholder text-center">
                                        <i class="fas fa-image fa-4x mb-3"></i>
                                        <p>انقر لاختيار صورة</p>
                                        <small class="text-muted">الحجم الموصى به: 1200x450 بكسل</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" 
                                   class="form-control mt-3 d-none" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*">
                            <input type="hidden" 
                                   id="current_image" 
                                   name="current_image" 
                                   value="<?php echo $offer ? $offer['image'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="mb-4 border-bottom pb-2">
                            <i class="fas fa-cog me-2"></i>
                            الإعدادات
                        </h4>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       <?php echo (!$offer || $offer['is_active'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold" for="is_active">
                                    تفعيل العرض
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="link" class="form-label fw-bold">رابط إضافي</label>
                            <input type="url" 
                                   class="form-control" 
                                   id="link" 
                                   name="link" 
                                   value="<?php echo $offer ? htmlspecialchars($offer['link']) : ''; ?>" 
                                   placeholder="https://example.com">
                            <small class="text-muted">رابط خارجي (اختياري)</small>
                        </div>
                    </div>
                    
                    <!-- زر الحفظ -->
                    <div class="form-section bg-light">
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $submit_text; ?>
                        </button>
                        
                        <?php if ($offer): ?>
                            <a href="preview-offer.php?id=<?php echo $offer['id']; ?>" 
                               target="_blank" 
                               class="btn btn-outline-info btn-lg w-100 mt-3 py-3">
                                <i class="fas fa-eye me-2"></i>
                                معاينة العرض
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // معاينة الصورة
        document.getElementById('imagePreview').addEventListener('click', function() {
            document.getElementById('image').click();
        });
        
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML = 
                        `<img src="${e.target.result}" alt="صورة العرض">`;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // إدارة المنتجات
        $(document).ready(function() {
            // البحث في المنتجات
            $('#product_search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.product-checkbox').each(function() {
                    const productName = $(this).data('product-name').toLowerCase();
                    if (productName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            
            // إضافة منتج
            $('.product-checkbox input').on('change', function() {
                const productId = $(this).val();
                const isChecked = $(this).is(':checked');
                
                if (isChecked) {
                    addProduct(productId);
                } else {
                    removeProduct(productId);
                }
            });
            
            // إزالة منتج
            $(document).on('click', '.remove-product', function() {
                const productItem = $(this).closest('.product-item');
                const productId = productItem.data('product-id');
                productItem.remove();
                
                // إظهار المنتج في القائمة
                $(`.product-checkbox[data-product-id="${productId}"]`).show().find('input').prop('checked', false);
            });
        });
        
        function addProduct(productId) {
            const checkbox = $(`.product-checkbox[data-product-id="${productId}"]`);
            const productName = checkbox.data('product-name');
            const productPrice = checkbox.data('product-price');
            
            // إخفاء المنتج من القائمة
            checkbox.hide();
            
            // إضافة إلى القائمة المختارة
            const productHtml = `
                <div class="product-item" data-product-id="${productId}">
                    <div>
                        <strong>${productName}</strong>
                        <br>
                        <small class="text-muted">السعر: ${parseFloat(productPrice).toFixed(2)} ر.س</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-product">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="products[]" value="${productId}">
                </div>
            `;
            
            $('#selectedProductsContainer').append(productHtml);
        }
        
        function removeProduct(productId) {
            $(`.product-item[data-product-id="${productId}"]`).remove();
        }
        
        // التحقق من الصحة قبل الإرسال
        $('#offerForm').on('submit', function(e) {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());
            
            if (endDate <= startDate) {
                alert('يجب أن يكون تاريخ الانتهاء بعد تاريخ البدء');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>