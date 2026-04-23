<?php
// admin/offer-products.php
session_start();
require_once '../config/database.php';

// التحقق من وجود معرف العرض
if (!isset($_GET['id'])) {
    header('Location: offers.php');
    exit();
}

$offer_id = intval($_GET['id']);

// جلب بيانات العرض
$sql = "SELECT id, title, start_date, end_date, is_active FROM offers WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $offer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    header('Location: offers.php');
    exit();
}

// البحث والتصفية
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// جلب الفئات
$categories_sql = "SELECT id, name FROM categories WHERE type = 'product' AND status = 'active' ORDER BY name";
$categories_result = mysqli_query($conn, $categories_sql);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

// بناء استعلام جلب المنتجات
$products_sql = "SELECT p.id, p.name, p.selling_price, p.status, p.stock,
                        c.name as category_name,
                        pi.image_path as image,
                        op.product_id as is_linked
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 LEFT JOIN offer_products op ON p.id = op.product_id AND op.offer_id = ?
                 WHERE p.is_active = 1";

$params = [$offer_id];
$types = "i";

if (!empty($search)) {
    $products_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($category_id > 0) {
    $products_sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if ($status !== 'all') {
    $products_sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

$products_sql .= " ORDER BY p.created_at DESC";

$products_stmt = mysqli_prepare($conn, $products_sql);
if ($types) {
    mysqli_stmt_bind_param($products_stmt, $types, ...$params);
}
mysqli_stmt_execute($products_stmt);
$products_result = mysqli_stmt_get_result($products_stmt);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// جلب المنتجات المرتبطة بالفعل بالعرض
$linked_sql = "SELECT op.product_id, op.created_at 
               FROM offer_products op 
               WHERE op.offer_id = ?";
$linked_stmt = mysqli_prepare($conn, $linked_sql);
mysqli_stmt_bind_param($linked_stmt, "i", $offer_id);
mysqli_stmt_execute($linked_stmt);
$linked_result = mysqli_stmt_get_result($linked_stmt);
$linked_products = array_column(mysqli_fetch_all($linked_result, MYSQLI_ASSOC), 'product_id');

// معالجة إضافة/إزالة المنتجات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['product_id'])) {
        $action = $_POST['action'];
        $product_id = intval($_POST['product_id']);
        
        if ($action === 'add') {
            // إضافة المنتج للعرض
            $add_sql = "INSERT INTO offer_products (offer_id, product_id) VALUES (?, ?)";
            $add_stmt = mysqli_prepare($conn, $add_sql);
            mysqli_stmt_bind_param($add_stmt, "ii", $offer_id, $product_id);
            mysqli_stmt_execute($add_stmt);
            
            echo json_encode(['success' => true, 'message' => 'تمت إضافة المنتج للعرض']);
            exit;
            
        } elseif ($action === 'remove') {
            // إزالة المنتج من العرض
            $remove_sql = "DELETE FROM offer_products WHERE offer_id = ? AND product_id = ?";
            $remove_stmt = mysqli_prepare($conn, $remove_sql);
            mysqli_stmt_bind_param($remove_stmt, "ii", $offer_id, $product_id);
            mysqli_stmt_execute($remove_stmt);
            
            echo json_encode(['success' => true, 'message' => 'تمت إزالة المنتج من العرض']);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة منتجات العرض: <?php echo htmlspecialchars($offer['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-container">
        <!-- رأس الصفحة -->
        <div class="offer-header">
            <div class="header-content">
                <div class="offer-info">
                    <h1>
                        <i class="fas fa-boxes me-2"></i>
                        إدارة منتجات العرض: <?php echo htmlspecialchars($offer['title']); ?>
                    </h1>
                    <div class="offer-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('Y-m-d', strtotime($offer['start_date'])); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-times"></i>
                            <?php echo date('Y-m-d', strtotime($offer['end_date'])); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-boxes"></i>
                            <?php echo count($linked_products); ?> منتج
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <span class="status-badge <?php echo $offer['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $offer['is_active'] ? 'نشط' : 'غير نشط'; ?>
                    </span>
                    <a href="edit-offer.php?id=<?php echo $offer_id; ?>" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-edit me-2"></i>
                        تعديل العرض
                    </a>
                </div>
            </div>
        </div>
        
        <!-- محتوى الصفحة -->
        <div class="products-container">
            <!-- العمود الأيمن: المنتجات المرتبطة -->
            <div class="linked-products-section">
                <div class="section-title">
                    <span>المنتجات المرتبطة</span>
                    <span class="linked-count"><?php echo count($linked_products); ?></span>
                </div>
                
                <div class="linked-products-list" id="linkedProductsList">
                    <?php if (!empty($linked_products)): ?>
                        <?php 
                        // جلب تفاصيل المنتجات المرتبطة
                        $linked_details_sql = "SELECT p.id, p.name, p.selling_price 
                                              FROM products p 
                                              WHERE p.id IN (" . implode(',', $linked_products) . ")";
                        $linked_details_result = mysqli_query($conn, $linked_details_sql);
                        while ($product = mysqli_fetch_assoc($linked_details_result)):
                        ?>
                            <div class="linked-product-item" data-product-id="<?php echo $product['id']; ?>">
                                <div class="product-details">
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-price"><?php echo number_format($product['selling_price'], 2); ?> ر.س</div>
                                </div>
                                <button class="remove-btn" onclick="removeProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>لا توجد منتجات مرتبطة</p>
                            <small class="text-muted">يمكنك إضافة منتجات من القائمة</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- العمود الأيسر: جميع المنتجات -->
            <div class="all-products-section">
                <!-- أدوات البحث والتصفية -->
                <div class="filters-section">
                    <form method="GET" action="" class="row g-3">
                        <input type="hidden" name="id" value="<?php echo $offer_id; ?>">
                        
                        <div class="col-md-6">
                            <div class="search-box">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="ابحث عن منتج..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="category_id" class="form-control">
                                <option value="0">جميع الفئات</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>جميع الحالات</option>
                                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>نشط</option>
                                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                                <option value="low_stock" <?php echo $status == 'low_stock' ? 'selected' : ''; ?>>مخزون منخفض</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="filter-badges">
                                    <a href="?id=<?php echo $offer_id; ?>" class="filter-badge <?php echo empty($search) && $category_id == 0 && $status == 'all' ? 'active' : ''; ?>">
                                        <i class="fas fa-filter"></i>
                                        الكل
                                    </a>
                                    <a href="?id=<?php echo $offer_id; ?>&search=&category_id=0&status=active" class="filter-badge <?php echo $status == 'active' ? 'active' : ''; ?>">
                                        <i class="fas fa-check-circle"></i>
                                        نشط فقط
                                    </a>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- قائمة المنتجات -->
                <div class="products-grid" id="allProductsGrid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php $is_linked = in_array($product['id'], $linked_products); ?>
                            <div class="product-card <?php echo $is_linked ? 'linked' : ''; ?>" 
                                 data-product-id="<?php echo $product['id']; ?>">
                                
                                <?php if ($is_linked): ?>
                                    <div class="linked-indicator">
                                        <i class="fas fa-link me-1"></i> مرتبط
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-header">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <?php endif; ?>
                                    
                                    <div class="product-image-placeholder" 
                                         style="<?php echo empty($product['image']) ? 'display:flex' : 'display:none'; ?>">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    
                                    <div class="product-info">
                                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <?php if (!empty($product['category_name'])): ?>
                                            <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                        <?php endif; ?>
                                        
                                        <div class="product-price-stock">
                                            <div class="product-price">
                                                <?php echo number_format($product['selling_price'], 2); ?> ر.س
                                            </div>
                                            <div class="stock-status <?php 
                                                echo $product['stock'] > 10 ? 'in-stock' : 
                                                     ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock');
                                            ?>">
                                                <?php 
                                                echo $product['stock'] > 10 ? 'متوفر' : 
                                                     ($product['stock'] > 0 ? 'مخزون منخفض' : 'غير متوفر');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <?php if ($is_linked): ?>
                                        <button class="remove-btn-small" onclick="removeProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                            إزالة
                                        </button>
                                    <?php else: ?>
                                        <button class="add-btn" onclick="addProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-plus"></i>
                                            إضافة للعرض
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h4>لم يتم العثور على منتجات</h4>
                            <p class="text-muted">جرب تغيير معايير البحث</p>
                            <a href="?id=<?php echo $offer_id; ?>" class="btn btn-outline-primary mt-3">
                                <i class="fas fa-redo me-2"></i>
                                عرض جميع المنتجات
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- العودة -->
                <div class="text-center mt-5">
                    <a href="offers.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>
                        العودة لقائمة العروض
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // دالة لإضافة منتج للعرض
        function addProduct(productId) {
            const productCard = $(`.product-card[data-product-id="${productId}"]`);
            
            // عرض مؤشر التحميل
            const button = productCard.find('.add-btn');
            const originalHtml = button.html();
            button.html('<i class="fas fa-spinner fa-spin"></i>');
            button.prop('disabled', true);
            
            // إرسال طلب AJAX
            $.ajax({
                url: 'offer-products.php?id=<?php echo $offer_id; ?>',
                method: 'POST',
                data: {
                    action: 'add',
                    product_id: productId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showToast('success', result.message);
                            
                            // تحديث واجهة المستخدم
                            productCard.addClass('linked');
                            productCard.find('.add-btn').replaceWith(`
                                <button class="remove-btn-small" onclick="removeProduct(${productId})">
                                    <i class="fas fa-times"></i>
                                    إزالة
                                </button>
                            `);
                            
                            // إضافة مؤشر مرتبط
                            if (!productCard.find('.linked-indicator').length) {
                                productCard.prepend(`
                                    <div class="linked-indicator">
                                        <i class="fas fa-link me-1"></i> مرتبط
                                    </div>
                                `);
                            }
                            
                            // إضافة للمنتجات المرتبطة في الجانب
                            updateLinkedProductsList('add', productId);
                            
                        } else {
                            showToast('error', result.message || 'حدث خطأ');
                            button.html(originalHtml);
                            button.prop('disabled', false);
                        }
                    } catch (e) {
                        showToast('error', 'حدث خطأ غير متوقع');
                        button.html(originalHtml);
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    showToast('error', 'حدث خطأ في الاتصال بالخادم');
                    button.html(originalHtml);
                    button.prop('disabled', false);
                }
            });
        }
        
        // دالة لإزالة منتج من العرض
        function removeProduct(productId) {
            // إرسال طلب AJAX
            $.ajax({
                url: 'offer-products.php?id=<?php echo $offer_id; ?>',
                method: 'POST',
                data: {
                    action: 'remove',
                    product_id: productId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showToast('success', result.message);
                            
                            // تحديث بطاقة المنتج
                            const productCard = $(`.product-card[data-product-id="${productId}"]`);
                            productCard.removeClass('linked');
                            productCard.find('.linked-indicator').remove();
                            productCard.find('.remove-btn-small, .remove-btn').replaceWith(`
                                <button class="add-btn" onclick="addProduct(${productId})">
                                    <i class="fas fa-plus"></i>
                                    إضافة للعرض
                                </button>
                            `);
                            
                            // إزالة من القائمة المرتبطة
                            updateLinkedProductsList('remove', productId);
                            
                        } else {
                            showToast('error', result.message || 'حدث خطأ');
                        }
                    } catch (e) {
                        showToast('error', 'حدث خطأ غير متوقع');
                    }
                },
                error: function() {
                    showToast('error', 'حدث خطأ في الاتصال بالخادم');
                }
            });
        }
        
        // دالة لتحديث قائمة المنتجات المرتبطة
        function updateLinkedProductsList(action, productId) {
            const productCard = $(`.product-card[data-product-id="${productId}"]`);
            const productName = productCard.find('.product-title').text();
            const productPrice = productCard.find('.product-price').text();
            
            if (action === 'add') {
                // إضافة للقائمة المرتبطة
                const linkedItem = `
                    <div class="linked-product-item" data-product-id="${productId}">
                        <div class="product-details">
                            <div class="product-name">${productName}</div>
                            <div class="product-price">${productPrice}</div>
                        </div>
                        <button class="remove-btn" onclick="removeProduct(${productId})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                $('#linkedProductsList').append(linkedItem);
                
                // تحديث العداد
                const count = parseInt($('.linked-count').text());
                $('.linked-count').text(count + 1);
                
            } else if (action === 'remove') {
                // إزالة من القائمة المرتبطة
                $(`.linked-product-item[data-product-id="${productId}"]`).remove();
                
                // تحديث العداد
                const count = parseInt($('.linked-count').text());
                $('.linked-count').text(count - 1);
            }
            
            // إذا كانت القائمة فارغة، عرض رسالة
            if ($('#linkedProductsList .linked-product-item').length === 0) {
                $('#linkedProductsList').html(`
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>لا توجد منتجات مرتبطة</p>
                        <small class="text-muted">يمكنك إضافة منتجات من القائمة</small>
                    </div>
                `);
            }
        }
        
        // دالة لعرض رسائل التأكيد
        function showToast(type, message) {
            // إزالة أي Toast سابق
            $('.toast').remove();
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
            const toast = $(`
                <div class="toast ${type}">
                    <div class="toast-content">
                        <i class="fas ${icon}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="toast-close">&times;</button>
                </div>
            `);
            
            $('body').append(toast);
            
            // إظهار Toast
            setTimeout(() => {
                toast.addClass('show');
            }, 100);
            
            // إخفاء تلقائي بعد 3 ثواني
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
            
            // إغلاق يدوي
            toast.find('.toast-close').click(function() {
                toast.removeClass('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            });
        }
        
        // البحث المباشر أثناء الكتابة
        $(document).ready(function() {
            let searchTimeout;
            $('#search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    $(this).closest('form').submit();
                }, 500);
            });
            
            // إضافة تأثيرات للبطاقات
            $('.product-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
    </script>
</body>
</html>