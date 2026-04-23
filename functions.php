<?php
// functions.php - دالة إنشاء بطاقة المنتج مع جميع الميزات

// function generateProductCard($product) {
//     // التأكد من وجود جميع البيانات المطلوبة
//     $productId = $product['id'] ?? 0;
//     $productName = $product['name'] ?? 'منتج غير معروف';
//     $categoryId = $product['category_id'] ?? 0;
//     $categoryName = $product['category_name'] ?? 'فئة غير معروفة';
//     $sellingPrice = $product['selling_price'] ?? 0;
//     $oldPrice = $product['old_price'] ?? null;
//     $rating = $product['rating'] ?? 0;
//     $stock = $product['stock'] ?? 0;
//     $description = $product['description'] ?? '';
//     $isFeatured = $product['featured'] ?? 0;
//     $isNew = $product['new_product'] ?? 1;
    
//     // جلب صورة المنتج الرئيسية
//     $productImage = getProductImage($productId);
    
//     // التحقق مما إذا كان المنتج في المفضلة
//     $isFavorite = isProductInFavorites($productId);
//     $favoriteIconClass = $isFavorite ? 'fas fa-heart' : 'far fa-heart';
//     $favoriteTooltip = $isFavorite ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة';
    
//     // التحقق من حالة المخزون
//     $stockStatus = '';
//     $stockBadgeClass = 'stock-badge';
//     $addToCartDisabled = '';
    
//     if ($stock <= 0) {
//         $stockStatus = '<span class="out-of-stock">نفذ من المخزون</span>';
//         $stockBadgeClass .= ' out-of-stock';
//         $addToCartDisabled = 'disabled';
//     } else if ($stock <= 5) {
//         $stockStatus = '<span class="low-stock">آخر قطعة</span>';
//         $stockBadgeClass .= ' low-stock';
//     }
    
//     // توليد HTML للأسعار القديمة
//     $oldPriceHtml = '';
//     if (!empty($oldPrice) && $oldPrice > 0 && $oldPrice > $sellingPrice) {
//         $discountPercent = round((($oldPrice - $sellingPrice) / $oldPrice) * 100);
//         $oldPriceHtml = '
//             <small class="old-price">
//                 <span class="text-muted text-decoration-line-through">' . number_format($oldPrice, 2) . ' ر.س</span>
//                 <span class="discount-badge">-' . $discountPercent . '%</span>
//             </small>';
//     }
    
//     // توليد النجوم
//     $ratingStars = getRatingStars($rating);
    
//     // توليد HTML البطاقة الكاملة مع جميع الميزات
//     $cardHTML = '
//     <div class="product-card" data-id="' . $productId . '" data-category="' . $categoryId . '">
//         <div class="product-image-container">
//             <img src="' . htmlspecialchars($productImage) . '" 
//                  alt="' . htmlspecialchars($productName) . '" 
//                  class="product-img"
//                  loading="lazy"
//                  onclick="showProductDetails(' . $productId . ')">
//             <div class="product-badges">';
    
//     // إضافة البادجات
//     if ($isFeatured) {
//         $cardHTML .= '<span class="featured-badge" title="منتج مميز"><i class="fas fa-crown"></i> مميز</span>';
//     }
//     if ($isNew) {
//         $cardHTML .= '<span class="new-badge" title="منتج جديد"><i class="fas fa-bolt"></i> جديد</span>';
//     }
//     if (!empty($oldPrice) && $oldPrice > $sellingPrice) {
//         $cardHTML .= '<span class="discount-badge" title="خصم"><i class="fas fa-tag"></i> خصم</span>';
//     }
    
//  $cardHTML .= '
//             </div>
//             ' . $stockStatus . '
//             <button class="quick-view-icon" onclick="showProductDetails(' . $productId . ')" title="عرض سريع">
//                 <i class="fas fa-eye"></i>
//             </button>
            
//             <button class="floating-cart-btn ' . $addToCartDisabled . '" 
//                     onclick="addToCart(' . $productId . ', 1)"
//                     ' . ($stock <= 0 ? 'disabled title="غير متاح"' : 'title="أضف إلى السلة"') . '>
//                 <i class="fas fa-shopping-cart"></i>
//                 <span class="floating-cart-tooltip">' . ($stock <= 0 ? 'غير متوفر' : 'أضف إلى السلة') . '</span>
//             </button>
//         </div>
        
//         <div class="product-info">
//             <h3 class="product-title" onclick="showProductDetails(' . $productId . ')">' . htmlspecialchars($productName) . '</h3>
//             <div class="product-meta">
//                 <span class="product-category" onclick="filterByCategory(' . $categoryId . ')">
//                     <i class="fas fa-tag"></i> ' . htmlspecialchars($categoryName) . '
//                 </span>
//                 <div class="rating">
//                     ' . $ratingStars . '
//                     <span class="rating-number">(' . number_format($rating, 1) . ')</span>
//                 </div>
//             </div>
            
//             <div class="product-price-container">
//                 <div class="product-price">
//                     <span class="current-price">
//                         <span class="price-value">' . number_format($sellingPrice, 2) . '</span>
//                         <span class="currency">ر.س</span>
//                     </span>
//                     ' . $oldPriceHtml . '
//                 </div>
//                 <div class="' . $stockBadgeClass . '" title="الكمية المتاحة">
//                     <i class="fas fa-box"></i> ' . $stock . ' متبقي
//                 </div>
//             </div>
            
//             <p class="product-description" title="' . htmlspecialchars(strip_tags($description)) . '">
//                 ' . mb_substr(strip_tags($description), 0, 70) . (mb_strlen(strip_tags($description)) > 70 ? '...' : '') . '
//             </p>
//         </div>
        
//         <div class="product-actions">
//             <button class="add-to-cart-btn ' . $addToCartDisabled . '" 
//                     onclick="addToCart(' . $productId . ', 1)" 
//                     data-product-id="' . $productId . '"
//                     ' . ($stock <= 0 ? 'disabled' : '') . '>
//                 <span class="cart-icon">
//                     <i class="fas fa-shopping-cart"></i>
//                 </span>
//                 <span class="btn-text">
//                     ' . ($stock <= 0 ? 'غير متوفر' : 'أضف للسلة') . '
//                 </span>
//                 <span class="cart-animation">
//                     <i class="fas fa-check"></i>
//                 </span>
//             </button>
            
//             <div class="secondary-actions">
//                 <button class="favorite-btn ' . ($isFavorite ? 'active' : '') . '" 
//                         onclick="toggleFavorite(' . $productId . ', this)" 
//                         data-product-id="' . $productId . '"
//                         title="' . $favoriteTooltip . '">
//                     <i class="' . $favoriteIconClass . '"></i>
//                     <span class="btn-tooltip">' . $favoriteTooltip . '</span>
//                 </button>
                
//                 <button class="compare-btn" 
//                         onclick="addToCompare(' . $productId . ')" 
//                         data-product-id="' . $productId . '"
//                         title="مقارنة المنتج">
//                     <i class="fas fa-exchange-alt"></i>
//                     <span class="btn-tooltip">مقارنة</span>
//                 </button>
                
//                 <div class="share-dropdown">
//                     <button class="share-btn" title="مشاركة المنتج">
//                         <i class="fas fa-share-alt"></i>
//                         <span class="btn-tooltip">مشاركة</span>
//                     </button>
//                     <div class="share-dropdown-content">
//                         <a href="javascript:void(0)" onclick="shareOnFacebook(' . $productId . ')" title="مشاركة على فيسبوك">
//                             <i class="fab fa-facebook-f"></i> فيسبوك
//                         </a>
//                         <a href="javascript:void(0)" onclick="shareOnTwitter(' . $productId . ')" title="مشاركة على تويتر">
//                             <i class="fab fa-twitter"></i> تويتر
//                         </a>
//                         <a href="javascript:void(0)" onclick="shareOnWhatsApp(' . $productId . ')" title="مشاركة على واتساب">
//                             <i class="fab fa-whatsapp"></i> واتساب
//                         </a>
//                         <a href="javascript:void(0)" onclick="copyProductLink(' . $productId . ')" title="نسخ الرابط">
//                             <i class="fas fa-link"></i> نسخ الرابط
//                         </a>
//                     </div>
//                 </div>
//             </div>
//         </div>
//     </div>';
    
//     return $cardHTML;
// }
function generateProductCard($product) {
    // التأكد من وجود جميع البيانات المطلوبة
    $productId = $product['id'] ?? 0;
    $productName = $product['name'] ?? 'منتج غير معروف';
    $categoryId = $product['category_id'] ?? 0;
    $categoryName = $product['category_name'] ?? 'فئة غير معروفة';
    $sellingPrice = $product['selling_price'] ?? 0;
    $oldPrice = $product['old_price'] ?? null;
    $rating = $product['rating'] ?? 0;
    $stock = $product['stock'] ?? 0;
    $description = $product['description'] ?? '';
    $isFeatured = $product['featured'] ?? 0;
    $isNew = $product['new_product'] ?? 1;
    
    // جلب صورة المنتج الرئيسية
    $productImage = getProductImage($productId);
    
    // التحقق مما إذا كان المنتج في المفضلة
    $isFavorite = isProductInFavorites($productId);
    $favoriteIconClass = $isFavorite ? 'fas fa-heart' : 'far fa-heart';
    $favoriteTooltip = $isFavorite ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة';
    
    // التحقق من حالة المخزون
    $stockStatus = '';
    $stockBadgeClass = 'stock-badge';
    $addToCartDisabled = '';
    
    if ($stock <= 0) {
        $stockStatus = '<span class="out-of-stock">نفذ من المخزون</span>';
        $stockBadgeClass .= ' out-of-stock';
        $addToCartDisabled = 'disabled';
    } else if ($stock <= 5) {
        $stockStatus = '<span class="low-stock">آخر ' . $stock . ' قطع</span>';
        $stockBadgeClass .= ' low-stock';
    } else {
        $stockStatus = '<span class="in-stock">' . $stock . ' متبقي</span>';
    }
    
    // توليد HTML للأسعار القديمة
    $oldPriceHtml = '';
    if (!empty($oldPrice) && $oldPrice > 0 && $oldPrice > $sellingPrice) {
        $discountPercent = round((($oldPrice - $sellingPrice) / $oldPrice) * 100);
        $oldPriceHtml = '
            <small class="old-price">
                <span class="text-muted text-decoration-line-through">' . number_format($oldPrice, 2) . ' ر.س</span>
                <span class="discount-badge">-' . $discountPercent . '%</span>
            </small>';
    }
    
    // توليد النجوم
    $ratingStars = getRatingStars($rating);
    
    // توليد HTML البطاقة الكاملة مع جميع الميزات
    $cardHTML = '
    <div class="product-card" data-id="' . $productId . '" data-category="' . $categoryId . '">
        <div class="product-image-container">
            <a href="product-details.php?id=' . $productId . '" class="product-image-link">
                <img src="' . htmlspecialchars($productImage) . '" 
                     alt="' . htmlspecialchars($productName) . '" 
                     class="product-img"
                     loading="lazy">
            </a>
            <div class="product-badges">';
    
    // إضافة البادجات
    $badgeCount = 0;
    if ($isFeatured) {
        $cardHTML .= '<span class="featured-badge" title="منتج مميز"><i class="fas fa-crown"></i> مميز</span>';
        $badgeCount++;
    }
    if ($isNew) {
        $cardHTML .= '<span class="new-badge" title="منتج جديد"><i class="fas fa-bolt"></i> جديد</span>';
        $badgeCount++;
    }
    if (!empty($oldPrice) && $oldPrice > $sellingPrice) {
        $cardHTML .= '<span class="discount-badge" title="خصم"><i class="fas fa-tag"></i> خصم</span>';
        $badgeCount++;
    }
    
    $cardHTML .= '
            </div>
            ' . $stockStatus . '
            <button class="quick-view-icon" onclick="window.location.href=\'product-details.php?id=' . $productId . '\'" title="عرض سريع">
                <i class="fas fa-eye"></i>
            </button>
            
            <button class="floating-cart-btn ' . $addToCartDisabled . '" 
                    onclick="addToCart(' . $productId . ', 1)"
                    ' . ($stock <= 0 ? 'disabled title="غير متاح"' : 'title="أضف إلى السلة"') . '>
                <i class="fas fa-shopping-cart"></i>
                <span class="floating-cart-tooltip">' . ($stock <= 0 ? 'غير متوفر' : 'أضف إلى السلة') . '</span>
            </button>
        </div>
        
        <div class="product-info">
            <a href="product-details.php?id=' . $productId . '" class="product-title-link">
                <h3 class="product-title">' . htmlspecialchars($productName) . '</h3>
            </a>
            <div class="product-meta">
                <span class="product-category" onclick="filterByCategory(' . $categoryId . ')">
                    <i class="fas fa-tag"></i> ' . htmlspecialchars($categoryName) . '
                </span>
                <div class="rating">
                    ' . $ratingStars . '
                    <span class="rating-number">(' . number_format($rating, 1) . ')</span>
                </div>
            </div>
            
            <div class="product-price-container">
                <div class="product-price">
                    <span class="current-price">
                        <span class="price-value">' . number_format($sellingPrice, 2) . '</span>
                        <span class="currency">ر.س</span>
                    </span>
                    ' . $oldPriceHtml . '
                </div>
                <div class="' . $stockBadgeClass . '" title="الكمية المتاحة">
                    <i class="fas fa-box"></i> ' . ($stock <= 0 ? 'نفذ' : $stock . ' متبقي') . '
                </div>
            </div>
            
            <p class="product-description" title="' . htmlspecialchars(strip_tags($description)) . '">
                ' . mb_substr(strip_tags($description), 0, 70) . (mb_strlen(strip_tags($description)) > 70 ? '...' : '') . '
            </p>
        </div>
        
        <div class="product-actions">
            <button class="add-to-cart-btn ' . $addToCartDisabled . '" 
                    onclick="addToCart(' . $productId . ', 1)" 
                    data-product-id="' . $productId . '"
                    ' . ($stock <= 0 ? 'disabled' : '') . '>
                <span class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </span>
                <span class="btn-text">
                    ' . ($stock <= 0 ? 'غير متوفر' : 'أضف للسلة') . '
                </span>
                <span class="cart-animation">
                    <i class="fas fa-check"></i>
                </span>
            </button>
            
            <div class="secondary-actions">
                <button class="favorite-btn ' . ($isFavorite ? 'active' : '') . '" 
                        onclick="toggleFavorite(' . $productId . ', this)" 
                        data-product-id="' . $productId . '"
                        title="' . $favoriteTooltip . '">
                    <i class="' . $favoriteIconClass . '"></i>
                    <span class="btn-tooltip">' . $favoriteTooltip . '</span>
                </button>
                
                <button class="compare-btn" 
                        onclick="addToCompare(' . $productId . ')" 
                        data-product-id="' . $productId . '"
                        title="مقارنة المنتج">
                    <i class="fas fa-exchange-alt"></i>
                    <span class="btn-tooltip">مقارنة</span>
                </button>
                
                <div class="share-dropdown">
                    <button class="share-btn" title="مشاركة المنتج">
                        <i class="fas fa-share-alt"></i>
                        <span class="btn-tooltip">مشاركة</span>
                    </button>
                    <div class="share-dropdown-content">
                        <a href="javascript:void(0)" onclick="shareOnFacebook(' . $productId . ')" title="مشاركة على فيسبوك">
                            <i class="fab fa-facebook-f"></i> فيسبوك
                        </a>
                        <a href="javascript:void(0)" onclick="shareOnTwitter(' . $productId . ')" title="مشاركة على تويتر">
                            <i class="fab fa-twitter"></i> تويتر
                        </a>
                        <a href="javascript:void(0)" onclick="shareOnWhatsApp(' . $productId . ')" title="مشاركة على واتساب">
                            <i class="fab fa-whatsapp"></i> واتساب
                        </a>
                        <a href="javascript:void(0)" onclick="copyProductLink(' . $productId . ')" title="نسخ الرابط">
                            <i class="fas fa-link"></i> نسخ الرابط
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    
    return $cardHTML;
}
// دالة للتحقق من وجود المنتج في المفضلة
function isProductInFavorites($productId) {
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }
    return in_array($productId, $_SESSION['favorites']);
}

// دالة لإنشاء نجوم التقييم مع دعم التقييمات الكسرية
function getRatingStars($rating) {
    $stars = '';
    $rating = min(5, max(0, $rating));
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    // النجوم الممتلئة
    for ($i = 0; $i < $fullStars; $i++) {
        $stars .= '<i class="fas fa-star filled"></i>';
    }
    
    // نجمة نصف ممتلئة
    if ($hasHalfStar) {
        $stars .= '<i class="fas fa-star-half-alt filled"></i>';
    }
    
    // النجوم الفارغة
    for ($i = 0; $i < $emptyStars; $i++) {
        $stars .= '<i class="far fa-star"></i>';
    }
    
    return $stars;
}

// دالة لجلب صورة المنتج الرئيسية مع صور افتراضية
function getProductImage($productId) {
    global $conn;
    
    $defaultImage = 'img/products/default.jpg';
    $fallbackImages = [
        'img/1.jpg',
        'img/products/2.jpg',
        'img/products/3.jpg'
    ];
    
    if (!$conn) {
        return $fallbackImages[array_rand($fallbackImages)];
    }
    
    try {
        $query = "SELECT image_path FROM product_images 
                  WHERE product_id = ? AND (is_main = 1 OR is_main IS NULL) 
                  ORDER BY is_main DESC, id ASC 
                  LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $imagePath = $row['image_path'];
                
                // التحقق من وجود الصورة فعلياً
                if (file_exists($imagePath)) {
                    return $imagePath;
                }
            }
        }
    } catch (Exception $e) {
        // في حالة حدوث خطأ، نعود للصورة الافتراضية
        error_log("خطأ في جلب صورة المنتج: " . $e->getMessage());
    }
    
    // محاولة استخدام الصور البديلة
    foreach ($fallbackImages as $image) {
        if (file_exists($image)) {
            return $image;
        }
    }
    
    return $defaultImage;
}

// دالة مساعدة لعرض السعر بشكل أنيق
function formatPrice($price) {
    return number_format($price, 2, '.', ',');
}

?>

<script>
// دالة لإضافة المنتج للمقارنة
function addToCompare(productId) {
    const compareBtn = event.currentTarget;
    
    // إضافة تأثير الاهتزاز
    compareBtn.classList.add('added');
    
    // إرسال طلب AJAX لإضافة المنتج للمقارنة
    fetch('ajax/add_to_compare.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // عرض إشعار
            showNotification('تمت إضافة المنتج للمقارنة', 'success');
            
            // إزالة التأثير بعد ثانية
            setTimeout(() => {
                compareBtn.classList.remove('added');
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ أثناء إضافة المنتج للمقارنة', 'error');
    });
}

// دوال المشاركة على وسائل التواصل الاجتماعي
function shareOnFacebook(productId) {
    const url = window.location.origin + '/product.php?id=' + productId;
    const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

function shareOnTwitter(productId) {
    const url = window.location.origin + '/product.php?id=' + productId;
    const text = encodeURIComponent('تحقق من هذا المنتج الرائع!');
    const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${text}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp(productId) {
    const url = window.location.origin + '/product.php?id=' + productId;
    const text = encodeURIComponent('تحقق من هذا المنتج الرائع! ');
    const shareUrl = `https://wa.me/?text=${text}${encodeURIComponent(url)}`;
    window.open(shareUrl, '_blank', 'width=600,height=600');
}

function copyProductLink(productId) {
    const url = window.location.origin + '/product.php?id=' + productId;
    navigator.clipboard.writeText(url).then(() => {
        showNotification('تم نسخ الرابط إلى الحافظة', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('فشل نسخ الرابط', 'error');
    });
}

// دالة إضافة المنتج إلى السلة مع تأثيرات
function addToCart(productId, quantity = 1) {
    // إرسال طلب AJAX
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // تحديث عداد السلة
            updateCartCount(data.cart_count);
            
            // عرض إشعار
            showNotification('تمت إضافة المنتج إلى السلة', 'success');
        } else {
            showNotification(data.message || 'حدث خطأ', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ أثناء إضافة المنتج إلى السلة', 'error');
    });
}

// دالة تبديل المفضلة
function toggleFavorite(productId, button) {
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch('ajax/toggle_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // تحديث الأيقونة
            if (button) {
                const icon = button.querySelector('i');
                const tooltip = button.querySelector('.btn-tooltip');
                
                if (data.is_favorite) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    button.classList.add('active');
                    if (tooltip) tooltip.textContent = 'إزالة من المفضلة';
                    showNotification('تمت إضافة المنتج للمفضلة', 'success');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    button.classList.remove('active');
                    if (tooltip) tooltip.textContent = 'إضافة إلى المفضلة';
                    showNotification('تمت إزالة المنتج من المفضلة', 'info');
                }
            }
        } else {
            // إذا لم يكن المستخدم مسجل دخول
            if (data.message && data.message.includes('تسجيل الدخول')) {
                showNotification('يجب تسجيل الدخول أولاً', 'error');
                setTimeout(() => { window.location.href = 'login.php'; }, 1500);
            } else {
                showNotification(data.message || 'حدث خطأ', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في الاتصال', 'error');
    });
}

// دوال مساعدة
function updateCartCount(count) {
    const cartCounter = document.querySelector('.cart-count');
    if (cartCounter) {
        cartCounter.textContent = count;
        cartCounter.classList.add('updated');
        setTimeout(() => cartCounter.classList.remove('updated'), 300);
    }
}

function showNotification(message, type = 'success') {
    // إنشاء عنصر الإشعار
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // إضافة الإشعار إلى الصفحة
    document.body.appendChild(notification);
    
    // إظهار الإشعار مع تأثير
    setTimeout(() => notification.classList.add('show'), 10);
    
    // إزالة الإشعار بعد 3 ثوان
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
<style>
/* تحسينات لبطاقة المنتج */
.product-card {
    position: relative;
    overflow: hidden;
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-image-link {
    display: block;
    position: relative;
}

.product-img {
    width: 100%;
    height: auto;
    display: block;
}

.product-badges {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.featured-badge, .new-badge, .discount-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    color: white;
    text-shadow: 0 1px 1px rgba(0,0,0,0.2);
}

.featured-badge {
    background: linear-gradient(45deg, #FFD700, #FFA500);
}

.new-badge {
    background: linear-gradient(45deg, #4CAF50, #2E7D32);
}

.discount-badge {
    background: linear-gradient(45deg, #FF5722, #D32F2F);
}

.out-of-stock, .low-stock, .in-stock {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    color: white;
}

.out-of-stock {
    background: linear-gradient(45deg, #f44336, #d32f2f);
}

.low-stock {
    background: linear-gradient(45deg, #FF9800, #F57C00);
}

.in-stock {
    background: linear-gradient(45deg, #4CAF50, #2E7D32);
}

.quick-view-icon {
    position: absolute;
    bottom: 60px;
    right: 10px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 3;
    transition: all 0.3s;
}

.quick-view-icon:hover {
    background: white;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.floating-cart-btn {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(45deg, #2196F3, #1976D2);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 3;
    transition: all 0.3s;
}

.floating-cart-btn:not(:disabled):hover {
    background: linear-gradient(45deg, #1976D2, #0D47A1);
    transform: scale(1.1);
    box-shadow: 0 2px 10px rgba(33, 150, 243, 0.4);
}

.floating-cart-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    opacity: 0.7;
}

.floating-cart-tooltip {
    display: none;
    position: absolute;
    bottom: 100%;
    right: 50%;
    transform: translateX(50%);
    background: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    margin-bottom: 5px;
}

.floating-cart-btn:hover .floating-cart-tooltip {
    display: block;
}

.product-info {
    padding: 15px;
}

.product-title-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.product-title {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    cursor: pointer;
    transition: color 0.3s;
}

.product-title:hover {
    color: #2196F3;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 13px;
}

.product-category {
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.rating {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #FF9800;
}

.rating i {
    font-size: 12px;
}

.rating-number {
    font-size: 12px;
    color: #666;
}

.product-price-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.product-price {
    display: flex;
    flex-direction: column;
}

.current-price {
    font-size: 18px;
    font-weight: bold;
    color: #2196F3;
    display: flex;
    align-items: baseline;
    gap: 2px;
}

.price-value {
    font-size: 22px;
}

.currency {
    font-size: 14px;
}

.old-price {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 3px;
}

.stock-badge {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.stock-badge.low-stock {
    color: #FF9800;
}

.stock-badge.out-of-stock {
    color: #f44336;
}

.product-description {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
    margin: 10px 0;
    height: 40px;
    overflow: hidden;
}

.product-actions {
    padding: 0 15px 15px;
    display: flex;
    gap: 10px;
}

.add-to-cart-btn {
    flex: 1;
    background: linear-gradient(45deg, #4CAF50, #2E7D32);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
}

.add-to-cart-btn:hover:not(:disabled) {
    background: linear-gradient(45deg, #388E3C, #1B5E20);
}

.add-to-cart-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.secondary-actions {
    display: flex;
    gap: 5px;
}

.favorite-btn, .compare-btn, .share-btn {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: white;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.favorite-btn:hover, .compare-btn:hover, .share-btn:hover {
    background: #f5f5f5;
    border-color: #2196F3;
    color: #2196F3;
}

.favorite-btn.active {
    background: #FFEBEE;
    border-color: #F44336;
    color: #F44336;
}

.btn-tooltip {
    display: none;
    position: absolute;
    bottom: 100%;
    right: 50%;
    transform: translateX(50%);
    background: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    margin-bottom: 5px;
}

.favorite-btn:hover .btn-tooltip,
.compare-btn:hover .btn-tooltip,
.share-btn:hover .btn-tooltip {
    display: block;
}

.share-dropdown {
    position: relative;
}

.share-dropdown-content {
    display: none;
    position: absolute;
    bottom: 100%;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    min-width: 150px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 10;
}

.share-dropdown:hover .share-dropdown-content {
    display: block;
}

.share-dropdown-content a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    text-decoration: none;
    color: #333;
    border-radius: 3px;
    transition: background 0.3s;
}

.share-dropdown-content a:hover {
    background: #f5f5f5;
}
</style>

<style>
            /* تنسيقات بطاقة المنتج */
            .product-card {
                border: 1px solid #e0e0e0;
                border-radius: 12px;
                padding: 15px;
                background: white;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .product-card:hover {
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                transform: translateY(-5px);
                border-color: #007bff;
            }

            /* منطقة الصورة */
            .product-image-container {
                position: relative;
                overflow: hidden;
                border-radius: 8px;
                margin-bottom: 15px;
                height: 220px;
                background: #f8f9fa;
                display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 10px;
                }

                .product-img {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                    transition: transform 0.5s ease;
                    cursor: pointer;
                }

                .product-img:hover {
                    transform: scale(1.08);
                }

                /* زر السلة العائم */
                .floating-cart-btn {
                    position: absolute;
                    top: 12px;
                    left: 12px;
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, #4CAF50, #2E7D32);
                    color: white;
                    border: none;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    z-index: 2;
                    box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
                    opacity: 0;
                    transform: translateY(-10px);
                }

                .product-card:hover .floating-cart-btn {
                    opacity: 1;
                    transform: translateY(0);
                }

                .floating-cart-btn:hover:not(:disabled) {
                    background: linear-gradient(135deg, #43A047, #1B5E20);
                    transform: scale(1.1) rotate(5deg);
                    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
                }

                .floating-cart-btn:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                    opacity: 0.7;
                    transform: translateY(0);
                }

                .floating-cart-btn i {
                    font-size: 16px;
                    transition: transform 0.3s ease;
                }

                .floating-cart-btn:hover:not(:disabled) i {
                    transform: scale(1.2);
                }

                .floating-cart-tooltip {
                    position: absolute;
                    top: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 11px;
                    white-space: nowrap;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    margin-top: 5px;
                    z-index: 10;
                }

                .floating-cart-btn:hover .floating-cart-tooltip {
                    opacity: 1;
                    visibility: visible;
                }

                /* البادجات */
                .product-badges {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                    z-index: 2;
                }

                .featured-badge, .new-badge, .discount-badge {
                    padding: 6px 12px;
                    border-radius: 6px;
                    font-size: 12px;
                    font-weight: bold;
                    color: white;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                    min-width: 70px;
                    justify-content: center;
                }

                .featured-badge {
                    background: linear-gradient(135deg, #FF9800, #FF5722);
                }

                .new-badge {
                    background: linear-gradient(135deg, #4CAF50, #2E7D32);
                }

                .discount-badge {
                    background: linear-gradient(135deg, #F44336, #D32F2F);
                }

                /* حالات المخزون */
                .out-of-stock {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(244, 67, 54, 0.95);
                    color: white;
                    padding: 10px 20px;
                    border-radius: 25px;
                    font-weight: bold;
                    font-size: 14px;
                    z-index: 3;
                    text-align: center;
                    width: 80%;
                    box-shadow: 0 3px 10px rgba(244, 67, 54, 0.3);
                }

                .low-stock {
                    position: absolute;
                    bottom: 12px;
                    right: 12px;
                    background: rgba(255, 152, 0, 0.95);
                    color: white;
                    padding: 6px 12px;
                    border-radius: 15px;
                    font-size: 12px;
                    font-weight: bold;
                    z-index: 3;
                    box-shadow: 0 2px 5px rgba(255, 152, 0, 0.3);
                }

                /* أيقونة العرض السريع */
                .quick-view-icon {
                    position: absolute;
                    bottom: 12px;
                    left: 12px;
                    background: rgba(255, 255, 255, 0.95);
                    border: none;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    z-index: 2;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                    color: #333;
                }

                .quick-view-icon:hover {
                    background: #007bff;
                    color: white;
                    transform: scale(1.1);
                }

                /* معلومات المنتج */
                .product-info {
                    margin-bottom: 15px;
                    flex: 1;
                }

                .product-title {
                    font-size: 16px;
                    font-weight: 600;
                    margin-bottom: 10px;
                    color: #333;
                    line-height: 1.4;
                    cursor: pointer;
                    transition: color 0.3s ease;
                    height: 45px;
                    overflow: hidden;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                }

                .product-title:hover {
                    color: #007bff;
                }

                .product-meta {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #f0f0f0;
                }

                .product-category {
                    font-size: 13px;
                    color: #666;
                    cursor: pointer;
                    transition: color 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }

                .product-category:hover {
                    color: #007bff;
                }

                .product-category i {
                    font-size: 12px;
                }

                .rating {
                    display: flex;
                    align-items: center;
                    gap: 3px;
                }

                .rating i {
                    font-size: 14px;
                }

                .rating i.filled {
                    color: #FFC107;
                }

                .rating i.fas.fa-star-half-alt.filled {
                    color: #FFC107;
                }

                .rating i.far.fa-star {
                    color: #ddd;
                }

                .rating-number {
                    font-size: 12px;
                    color: #666;
                    margin-right: 5px;
                    font-weight: 500;
                }

                /* السعر والمخزون */
                .product-price-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                    padding-bottom: 12px;
                    border-bottom: 1px solid #f0f0f0;
                }

                .product-price {
                    display: flex;
                    flex-direction: column;
                    gap: 5px;
                }

                .current-price {
                    font-size: 22px;
                    font-weight: bold;
                    color: #d32f2f;
                    display: flex;
                    align-items: baseline;
                    gap: 3px;
                }

                .currency {
                    font-size: 14px;
                    font-weight: normal;
                }

                .old-price {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .old-price .text-decoration-line-through {
                    font-size: 14px;
                    color: #999;
                    font-weight: 500;
                }

                .old-price .discount-badge {
                    background: linear-gradient(135deg, #F44336, #D32F2F);
                    color: white;
                    padding: 3px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                    min-width: auto;
                }

                /* شارة المخزون */
                .stock-badge {
                    background: #f8f9fa;
                    padding: 8px 14px;
                    border-radius: 20px;
                    font-size: 12px;
                    color: #666;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    border: 1px solid #e9ecef;
                    transition: all 0.3s ease;
                }

                .stock-badge i {
                    font-size: 13px;
                }

                .stock-badge.low-stock {
                    background: #FFF3E0;
                    color: #F57C00;
                    border-color: #FFE0B2;
                }

                .stock-badge.out-of-stock {
                    background: #FFEBEE;
                    color: #D32F2F;
                    border-color: #FFCDD2;
                }

                /* الوصف */
                .product-description {
                    font-size: 13px;
                    color: #666;
                    line-height: 1.5;
                    margin-bottom: 15px;
                    height: 40px;
                    overflow: hidden;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                }

                /* منطقة الأزرار */
                .product-actions {
                    display: flex;
                    gap: 12px;
                    align-items: center;
                    padding-top: 15px;
                    border-top: 1px solid #f0f0f0;
                }

                /* زر السلة الرئيسي */
                .add-to-cart-btn {
                    flex: 1;
                    background: linear-gradient(135deg, #007bff, #0056b3);
                    color: white;
                    border: none;
                    padding: 12px 15px;
                    border-radius: 8px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    font-weight: 600;
                    font-size: 14px;
                    transition: all 0.3s ease;
                    box-shadow: 0 2px 5px rgba(0,123,255,0.2);
                    min-height: 46px;
                    position: relative;
                    overflow: hidden;
                }

                .add-to-cart-btn:hover:not(:disabled) {
                    background: linear-gradient(135deg, #0056b3, #004494);
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
                }

                .add-to-cart-btn:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                    box-shadow: none;
                    transform: none;
                }

                .add-to-cart-btn .cart-icon {
                    font-size: 16px;
                    transition: transform 0.3s ease;
                }

                .add-to-cart-btn:hover:not(:disabled) .cart-icon {
                    transform: scale(1.2) translateY(-2px);
                }

                .btn-text {
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }

                .cart-animation {
                    position: absolute;
                    right: 15px;
                    opacity: 0;
                    transform: translateX(20px);
                    transition: all 0.3s ease;
                    color: #4CAF50;
                }

                .add-to-cart-btn.added .btn-text {
                    opacity: 0;
                    transform: translateX(-10px);
                }

                .add-to-cart-btn.added .cart-animation {
                    opacity: 1;
                    transform: translateX(0);
                    animation: checkBounce 0.5s ease;
                }

                @keyframes checkBounce {
                    0% { transform: translateX(20px) scale(0.5); opacity: 0; }
                    50% { transform: translateX(0) scale(1.2); }
                    100% { transform: translateX(0) scale(1); opacity: 1; }
                }

                /* الأزرار الثانوية */
                .secondary-actions {
                    display: flex;
                    gap: 8px;
                    position: relative;
                }

                .favorite-btn, .compare-btn, .share-btn {
                    width: 44px;
                    height: 44px;
                    border-radius: 50%;
                    border: 1px solid #e0e0e0;
                    background: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    color: #666;
                    font-size: 15px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                    position: relative;
                }

                .favorite-btn:hover, .compare-btn:hover, .share-btn:hover {
                    background: #f8f9fa;
                    border-color: #007bff;
                    color: #007bff;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,123,255,0.15);
                }

                .favorite-btn.active {
                    background: #ff4757;
                    border-color: #ff4757;
                    color: white;
                    box-shadow: 0 2px 8px rgba(255,71,87,0.3);
                }

                .favorite-btn.active:hover {
                    background: #ff3742;
                    border-color: #ff3742;
                }

                /* أدوات التلميح للأزرار */
                .btn-tooltip {
                    position: absolute;
                    bottom: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 6px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    white-space: nowrap;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    margin-bottom: 8px;
                    z-index: 10;
                }

                .favorite-btn:hover .btn-tooltip,
                .compare-btn:hover .btn-tooltip,
                .share-btn:hover .btn-tooltip {
                    opacity: 1;
                    visibility: visible;
                }

                /* قائمة مشاركة منسدلة */
                .share-dropdown {
                    position: relative;
                }

                .share-dropdown-content {
                    position: absolute;
                    bottom: 100%;
                    right: 0;
                    background: white;
                    min-width: 180px;
                    border-radius: 8px;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(10px);
                    transition: all 0.3s ease;
                    z-index: 100;
                    margin-bottom: 10px;
                    border: 1px solid #e0e0e0;
                }

                .share-dropdown:hover .share-dropdown-content {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0);
                }

                .share-dropdown-content a {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 12px 15px;
                    color: #333;
                    text-decoration: none;
                    font-size: 13px;
                    border-bottom: 1px solid #f5f5f5;
                    transition: all 0.2s ease;
                }

                .share-dropdown-content a:last-child {
                    border-bottom: none;
                }

                .share-dropdown-content a:hover {
                    background: #f8f9fa;
                    color: #007bff;
                    padding-right: 20px;
                }

                .share-dropdown-content i {
                    font-size: 14px;
                    width: 16px;
                    text-align: center;
                }

                .share-dropdown-content a:nth-child(1):hover { color: #1877F2; } /* Facebook */
                .share-dropdown-content a:nth-child(2):hover { color: #1DA1F2; } /* Twitter */
                .share-dropdown-content a:nth-child(3):hover { color: #25D366; } /* WhatsApp */
                .share-dropdown-content a:nth-child(4):hover { color: #007bff; } /* Copy Link */

                /* تصميم متجاوب */
                @media (max-width: 992px) {
                    .product-image-container {
                        height: 200px;
                    }
                    
                    .product-card {
                        padding: 12px;
                    }
                    
                    .featured-badge, .new-badge, .discount-badge {
                        padding: 5px 10px;
                        font-size: 11px;
                        min-width: 65px;
                    }
                    
                    .floating-cart-btn {
                        width: 36px;
                        height: 36px;
                        font-size: 14px;
                    }
                }

                @media (max-width: 768px) {
                    .product-card {
                        padding: 10px;
                        margin-bottom: 15px;
                    }
                    
                    .product-image-container {
                        height: 180px;
                        margin-bottom: 12px;
                    }
                    
                    .product-title {
                        font-size: 15px;
                        height: 40px;
                    }
                    
                    .current-price {
                        font-size: 20px;
                    }
                    
                    .product-actions {
                        flex-direction: column;
                        gap: 10px;
                    }
                    
                    .secondary-actions {
                        width: 100%;
                        justify-content: center;
                        order: 2;
                    }
                    
                    .add-to-cart-btn {
                        width: 100%;
                        order: 1;
                        padding: 10px;
                        min-height: 44px;
                    }
                    
                    .favorite-btn, .compare-btn, .share-btn {
                        width: 42px;
                        height: 42px;
                        font-size: 14px;
                    }
                    
                    .floating-cart-btn {
                        width: 34px;
                        height: 34px;
                        top: 8px;
                        left: 8px;
                        font-size: 13px;
                    }
                    
                    .share-dropdown-content {
                        min-width: 160px;
                        right: 50%;
                        transform: translateX(50%) translateY(10px);
                    }
                    
                    .share-dropdown:hover .share-dropdown-content {
                        transform: translateX(50%) translateY(0);
                    }
                }

                @media (max-width: 576px) {
                    .product-image-container {
                        height: 160px;
                    }
                    
                    .product-meta {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 8px;
                    }
                    
                    .rating {
                        width: 100%;
                        justify-content: flex-start;
                    }
                    
                    .product-price-container {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 10px;
                    }
                    
                    .stock-badge {
                        align-self: flex-start;
                    }
                    
                    .product-description {
                        font-size: 12px;
                    }
                    
                    .btn-text {
                        font-size: 13px;
                    }
                    
                    .floating-cart-btn {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                /* تأثيرات إضافية */
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }

                .new-badge {
                    animation: pulse 2s infinite;
                }

                /* تأثير التحميل للصور */
                .product-img {
                    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                    background-size: 200% 100%;
                    animation: loading 1.5s infinite;
                }

                @keyframes loading {
                    0% { background-position: 200% 0; }
                    100% { background-position: -200% 0; }
                }

                /* تحسين الظهور */
                .product-card {
                    animation: fadeIn 0.5s ease-out;
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                /* تأثير اهتزاز للمقارنة */
                @keyframes compareShake {
                    0%, 100% { transform: rotate(0deg); }
                    25% { transform: rotate(-10deg); }
                    75% { transform: rotate(10deg); }
                }

                .compare-btn.added {
                    animation: compareShake 0.5s ease;
                    background: #4CAF50;
                    color: white;
                    border-color: #4CAF50;
                }
</style>
