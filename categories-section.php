<?php
// أولاً: جلب بيانات الفئات من قاعدة البيانات
require_once 'config/database.php';

// استعلام لجلب الفئات مع عدد المنتجات
$categories_query = "
    SELECT 
        c.*,
        COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id 
        AND p.status = 'active' 
        AND p.is_active = 1
    WHERE c.status = 'active' 
        AND c.is_active = 1
        AND c.type = 'product'
    GROUP BY c.id
    ORDER BY c.name
";

$all_categories_result = mysqli_query($conn, $categories_query);

// التحقق من وجود أخطاء في الاستعلام
if (!$all_categories_result) {
    echo "خطأ في جلب الفئات: " . mysqli_error($conn);
    exit();
}
?>
<section class="categories-section mb-5">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title m-0">🛍️ فئات المتجر</h2>
        
        <!-- أزرار التمرير -->
        <div class="scroll-controls">
            <button class="scroll-btn" id="scroll-left" onclick="scrollCategories('left')">
                <i class="fas fa-chevron-right"></i>
            </button>
            <button class="scroll-btn" id="scroll-right" onclick="scrollCategories('right')">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
    </div>
    
    <!-- فلتر الفئات -->
    <!-- <div class="category-filter mb-4" id="category-filter">
        <button class="category-filter-btn active" data-category="all">
            <i class="fas fa-th-large me-1"></i> الكل
        </button>
        <?php 
        mysqli_data_seek($all_categories_result, 0);
        while($category = mysqli_fetch_assoc($all_categories_result)):
            $category_id = isset($category['id']) ? $category['id'] : 0;
            $category_name = isset($category['name']) ? htmlspecialchars($category['name']) : 'غير معروف';
            $product_count = isset($category['product_count']) ? $category['product_count'] : 0;
        ?>
            <button class="category-filter-btn" data-category="<?php echo $category_id; ?>">
                <?php echo $category_name; ?>
                <span class="badge bg-secondary ms-1"><?php echo $product_count; ?></span>
            </button>
        <?php endwhile; ?>
    </div> -->
    
    <!-- حاوية الفئات مع شريط التمرير الأفقي -->
    <div class="categories-scroll-container">
        <div class="categories-container" id="categories-container">
            <?php 
            mysqli_data_seek($all_categories_result, 0);
            while($category = mysqli_fetch_assoc($all_categories_result)): 
                $category_id = isset($category['id']) ? $category['id'] : 0;
                $category_name = isset($category['name']) ? htmlspecialchars($category['name']) : 'غير معروف';
                $category_image = isset($category['image']) && !empty($category['image']) && file_exists($category['image']) ? 
                    $category['image'] : 'img/1.jpg';
                $product_count = isset($category['product_count']) ? $category['product_count'] : 0;
                $is_featured = isset($category['featured']) ? $category['featured'] : 0;
                $category_description = isset($category['description']) ? htmlspecialchars(substr($category['description'], 0, 60)) : '';
            ?>
                <div class="category-item" 
                     data-category="<?php echo $category_id; ?>"
                     onclick="loadCategoryProducts(<?php echo $category_id; ?>, '<?php echo addslashes($category_name); ?>')">
                    <div class="category-img-container">
                        <img src="<?php echo $category_image; ?>" 
                             alt="<?php echo $category_name; ?>" 
                             class="category-img"
                             onerror="this.src='img/1.jpg'">
                        <?php if($is_featured): ?>
                            <span class="featured-badge">
                                <i class="fas fa-star"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <h5 class="category-name"><?php echo $category_name; ?></h5>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- مؤشر التمرير -->
        <div class="scroll-indicator">
            <div class="scroll-bar">
                <div class="scroll-thumb" id="scroll-thumb"></div>
            </div>
        </div>
    </div>
    
    <!-- حاوية عرض منتجات الفئة -->
    <div id="category-products-section" class="category-products-section mt-5" style="display:none;">
        <div class="category-products-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 id="category-products-title" class="m-0"></h3>
                <button class="btn-close-products" onclick="closeCategoryProducts()">
                    <i class="fas fa-times"></i> رجوع
                </button>
            </div>
        </div>
        
        <div class="row mt-4" id="category-products-grid">
            <!-- سيتم تعبئة المنتجات هنا -->
        </div>
    </div>
</section>

<style>
    /* ===== تنسيقات القسم الرئيسي ===== */
    .categories-section {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }
    
    .categories-section::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, rgba(255,51,102,0.1) 0%, rgba(255,102,153,0.05) 100%);
        border-radius: 0 0 0 100px;
    }
    
    /* ===== ترويسة القسم ===== */
    .section-header {
        position: relative;
        z-index: 2;
    }
    
    .section-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: #2d3748;
        position: relative;
        display: inline-block;
        background: linear-gradient(135deg, #ff3366, #ff6699);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .scroll-controls {
        display: flex;
        gap: 10px;
    }
    
    .scroll-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 14px;
    }
    
    .scroll-btn:hover {
        background: linear-gradient(135deg, #ff3366, #ff6699);
        border-color: transparent;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(255, 51, 102, 0.2);
    }
    
    /* ===== فلتر الفئات ===== */
    .category-filter {
        display: flex;
        gap: 12px;
        padding: 15px 0;
        margin-bottom: 25px;
        border-bottom: 2px solid #f1f5f9;
        overflow-x: auto;
        scrollbar-width: none;
    }
    
    .category-filter::-webkit-scrollbar {
        display: none;
    }
    
    .category-filter-btn {
        padding: 10px 22px;
        border: 2px solid #e2e8f0;
        border-radius: 50px;
        background: white;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }
    
    .category-filter-btn:hover:not(.active) {
        border-color: #ff3366;
        color: #ff3366;
        transform: translateY(-2px);
    }
    
    .category-filter-btn.active {
        background: linear-gradient(135deg, #ff3366, #ff6699);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 15px rgba(255, 51, 102, 0.25);
        transform: translateY(-2px);
    }
    
    /* ===== حاوية التمرير ===== */
    .categories-scroll-container {
        position: relative;
        padding-bottom: 20px;
    }
    
    .categories-container {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
        gap: 25px;
        padding: 20px 10px 30px;
        margin-bottom: 15px;
        scrollbar-width: none;
    }
    
    .categories-container::-webkit-scrollbar {
        display: none;
    }
    
    /* ===== بطاقات الفئات ===== */
    .category-item {
        flex: 0 0 auto;
        width: 130px;
        background: white;
        border-radius: 15px;
        padding: 15px 10px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        border: 1px solid rgba(255, 51, 102, 0.1);
    }
    
    .category-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(255, 51, 102, 0.15);
        border-color: rgba(255, 51, 102, 0.3);
    }
    
    .category-img-container {
        position: relative;
        margin-bottom: 10px;
    }
    
    .category-img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto;
        display: block;
        border: 3px solid #fff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        will-change: transform;
    }
    
    .category-item:hover .category-img {
        transform: scale(1.1);
        box-shadow: 0 5px 20px rgba(255, 51, 102, 0.2);
    }
    
    .featured-badge {
        position: absolute;
        top: 0;
        right: 10px;
        background: linear-gradient(135deg, #FFD700, #FFED4E);
        color: #333;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 3px 8px rgba(255, 215, 0, 0.3);
        z-index: 2;
    }
    
    .category-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        max-height: 2.6em;
        transition: color 0.3s ease;
    }
    
    .category-item:hover .category-name {
        color: #ff3366;
    }
    
    /* ===== مؤشر التمرير ===== */
    .scroll-indicator {
        width: 100%;
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        position: relative;
        display: none;
    }
    
    .scroll-bar {
        width: 100%;
        height: 100%;
        position: relative;
    }
    
    .scroll-thumb {
        position: absolute;
        height: 100%;
        background: linear-gradient(135deg, #ff3366, #ff6699);
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    
    /* ===== منتجات الفئة ===== */
    .category-products-section {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        animation: slideIn 0.5s ease;
    }
    
    .category-products-header {
        background: linear-gradient(135deg, #ff3366, #ff6699);
        color: white;
        padding: 20px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
    }
    
    .category-products-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .btn-close-products {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-close-products:hover {
        background: rgba(255,255,255,0.3);
        transform: translateX(-5px);
    }
    
    /* ===== تصميم متجاوب ===== */
    @media (max-width: 768px) {
        .categories-section {
            padding: 15px;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .scroll-controls {
            align-self: flex-end;
        }
        
        .categories-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-auto-rows: auto;
            gap: 12px;
            overflow-x: auto;
            overflow-y: visible;
            padding: 15px 5px;
        }
        
        .category-item {
            width: 100%;
            padding: 12px 8px;
        }
        
        .category-img {
            width: 70px;
            height: 70px;
        }
        
        .category-name {
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 480px) {
        .category-item {
            padding: 10px 6px;
        }
        
        .category-img {
            width: 60px;
            height: 60px;
        }
        
        .category-name {
            font-size: 0.75rem;
        }
        
        .featured-badge {
            width: 24px;
            height: 24px;
            font-size: 10px;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .category-img {
            transition: none;
        }
    }
    
    /* ===== أنيميشن ===== */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* ===== تلميحات التمرير ===== */
    .scroll-hint {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ff3366;
        font-size: 18px;
        opacity: 0;
        pointer-events: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: opacity 0.3s;
        z-index: 10;
    }
    
    .scroll-hint-left {
        left: 10px;
    }
    
    .scroll-hint-right {
        right: 10px;
    }
    
    .categories-container:hover ~ .scroll-hint,
    .scroll-hint:hover {
        opacity: 1;
    }
</style>

<script>
// ===== دالة التمرير الأفقي =====
function scrollCategories(direction) {
    const container = document.getElementById('categories-container');
    const scrollAmount = 350;
    
    if (direction === 'left') {
        container.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else if (direction === 'right') {
        container.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
    
    // تحديث مؤشر التمرير
    updateScrollThumb();
}

// ===== تحديث مؤشر التمرير =====
function updateScrollThumb() {
    const container = document.getElementById('categories-container');
    const thumb = document.getElementById('scroll-thumb');
    const indicator = document.querySelector('.scroll-indicator');
    
    if (container.scrollWidth > container.clientWidth) {
        indicator.style.display = 'block';
        
        const scrollPercentage = (container.scrollLeft / (container.scrollWidth - container.clientWidth)) * 100;
        const thumbWidth = (container.clientWidth / container.scrollWidth) * 100;
        
        thumb.style.width = Math.max(thumbWidth, 20) + '%';
        thumb.style.left = scrollPercentage + '%';
    } else {
        indicator.style.display = 'none';
    }
}

// ===== فلتر الفئات =====
$(document).ready(function() {
    $('.category-filter-btn').click(function() {
        const categoryId = $(this).data('category');
        
        $('.category-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        $('#category-products-section').hide().empty();
        
        if (categoryId === 'all') {
            $('.category-item').show();
        } else {
            $('.category-item').hide();
            $('.category-item[data-category="' + categoryId + '"]').show();
        }
        
        // التمرير للقسم
        $('html, body').animate({
            scrollTop: $('.categories-section').offset().top - 50
        }, 500);
    });
    
    // تحديث مؤشر التمرير عند التحميل والتمرير
    updateScrollThumb();
    $('#categories-container').on('scroll', updateScrollThumb);
    
    // التمرير بالماوس
    const categoriesContainer = document.getElementById('categories-container');
    let isDragging = false;
    let startX;
    let scrollLeft;
    
    categoriesContainer.addEventListener('mousedown', (e) => {
        isDragging = true;
        categoriesContainer.classList.add('grabbing');
        startX = e.pageX - categoriesContainer.offsetLeft;
        scrollLeft = categoriesContainer.scrollLeft;
    });
    
    categoriesContainer.addEventListener('mouseleave', () => {
        isDragging = false;
        categoriesContainer.classList.remove('grabbing');
    });
    
    categoriesContainer.addEventListener('mouseup', () => {
        isDragging = false;
        categoriesContainer.classList.remove('grabbing');
    });
    
    categoriesContainer.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - categoriesContainer.offsetLeft;
        const walk = (x - startX) * 2;
        categoriesContainer.scrollLeft = scrollLeft - walk;
        updateScrollThumb();
    });
    
    // التمرير باللمس للأجهزة المحمولة
    categoriesContainer.addEventListener('touchstart', (e) => {
        startX = e.touches[0].pageX - categoriesContainer.offsetLeft;
        scrollLeft = categoriesContainer.scrollLeft;
    });
    
    categoriesContainer.addEventListener('touchmove', (e) => {
        const x = e.touches[0].pageX - categoriesContainer.offsetLeft;
        const walk = (x - startX);
        categoriesContainer.scrollLeft = scrollLeft - walk;
        updateScrollThumb();
    });
});

// ===== دالة تحميل منتجات الفئة (كما هي) =====
function loadCategoryProducts(categoryId, categoryName) {
    const productsSection = $('#category-products-section');
    const productsTitle = $('#category-products-title');
    const productsGrid = $('#category-products-grid');
    
    productsSection.fadeIn();
    productsTitle.text('منتجات ' + categoryName);
    productsGrid.html('<div class="col-12 text-center py-5"><div class="spinner-border text-danger"></div><p class="mt-3">جاري جلب المنتجات...</p></div>');
    
    $.ajax({
        url: 'ajax/get_category_products.php',
        method: 'GET',
        data: { category_id: categoryId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.products) {
                let productsHtml = '';
                if (response.products.length > 0) {
                    response.products.forEach(function(product) {
                        let ratingStars = '';
                        const rating = parseFloat(product.rating) || 0;
                        for (let i = 1; i <= 5; i++) {
                            ratingStars += i <= Math.round(rating) ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-warning"></i>';
                        }

                        let oldPriceHtml = (product.old_price && parseFloat(product.old_price) > parseFloat(product.selling_price)) 
                            ? `<small class="text-muted text-decoration-line-through ms-2">${product.old_price} ر.س</small>` : '';
                        let favoriteClass = product.is_favorite ? 'active' : '';
                        let favoriteIcon = product.is_favorite ? 'fas fa-heart' : 'far fa-heart';

                        productsHtml += `
                        <div class="col-md-3 col-6 mb-4">
                            <div class="product-card shadow-sm border-0 position-relative">
                                <div class="product-img-container position-relative overflow-hidden">
                                    <img src="${product.image}" class="product-img w-100" alt="${product.name}" onerror="this.src='img/1.jpg'">
                                    <div class="product-overlay-actions">
                                        <button class="btn btn-light btn-sm rounded-circle shadow-sm" onclick="showProductDetails(${JSON.stringify(product).replace(/"/g, '&quot;')})" title="عرض سريع">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm rounded-circle shadow-sm ${favoriteClass}" onclick="toggleFavorite(${product.id}, this)" title="المفضلة">
                                            <i class="${favoriteIcon} text-danger"></i>
                                        </button>
                                    </div>
                                    ${product.featured ? '<span class="badge bg-warning position-absolute top-0 start-0 m-2">مميز</span>' : ''}
                                </div>
                                <div class="product-info text-center p-3">
                                    <h6 class="product-title text-truncate">
                                        <a href="product-details.php?id=${product.id}" class="text-decoration-none text-dark fw-bold">${product.name}</a>
                                    </h6>
                                    <div class="rating mb-2">${ratingStars}</div>
                                    <div class="price-container">
                                        <span class="text-danger fw-bold">${product.selling_price} ر.س</span>
                                        ${oldPriceHtml}
                                    </div>
                                    <button class="btn btn-sm btn-danger mt-3 w-100" 
                                            onclick="addToCart(${product.id}, 1, this)" 
                                            ${product.stock <= 0 ? 'disabled' : ''}>
                                        <i class="fas fa-shopping-cart"></i> ${product.stock > 0 ? 'أضف للسلة' : 'نفذ من المخزون'}
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
                    productsGrid.html(productsHtml);
                } else {
                    productsGrid.html('<div class="col-12 text-center py-5"><div class="alert alert-info">لا توجد منتجات حالياً.</div></div>');
                }
            }
            
            $('html, body').animate({ scrollTop: productsSection.offset().top - 50 }, 500);
        }
    });
}

// ===== الدوال الأخرى (كما هي) =====
function closeCategoryProducts() {
    document.getElementById('category-products-section').style.display = 'none';
}

function showProductDetails(product) {
    $('#product-detail-img').attr('src', product.image);
    $('#product-detail-name').text(product.name);
    $('#product-detail-category').html('<i class="fas fa-tag me-1"></i> الفئة: ' + (product.category_name || 'عام'));
    $('#product-detail-price').text(product.selling_price + ' ر.س');
    
    if (product.old_price && parseFloat(product.old_price) > parseFloat(product.selling_price)) {
        $('#product-detail-old-price').text(product.old_price + ' ر.س').show();
    } else {
        $('#product-detail-old-price').hide();
    }

    $('#product-detail-description').text(product.description || 'لا يوجد وصف متاح لهذا المنتج حالياً.');
    $('#product-detail-stock').text(product.stock);
    $('#product-detail-quantity').val(1);

    let ratingStars = '';
    const rating = parseFloat(product.rating) || 0;
    for (let i = 1; i <= 5; i++) {
        ratingStars += i <= Math.round(rating) ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-warning"></i>';
    }
    $('#product-detail-rating').html(ratingStars);

    $('#add-to-cart-detail').off('click').on('click', function() {
        const qty = $('#product-detail-quantity').val();
        addToCart(product.id, qty, this);
    });

    $('#add-to-favorites-detail').off('click').on('click', function() {
        toggleFavorite(product.id, this);
    });

    var myModal = new bootstrap.Modal(document.getElementById('productDetailModal'));
    myModal.show();
}

$(document).on('click', '.quantity-btn', function() {
    let input = $('#product-detail-quantity');
    let currentVal = parseInt(input.val());
    let maxStock = parseInt($('#product-detail-stock').text());

    if ($(this).text() === '+') {
        if (currentVal < maxStock) input.val(currentVal + 1);
    } else {
        if (currentVal > 1) input.val(currentVal - 1);
    }
});

function addToCart(productId, quantity, button) {
    if (button) {
        $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>جاري الإضافة');
    }
    
    setTimeout(function() {
        alert('تمت إضافة المنتج إلى السلة بنجاح!');
        if (button) {
            $(button).prop('disabled', false).html('<i class="fas fa-shopping-cart me-2"></i>أضف للسلة');
        }
    }, 1000);
}

function toggleFavorite(productId, button) {
    const isActive = $(button).hasClass('active');
    $(button).toggleClass('active');
    $(button).html(`<i class="${isActive ? 'far' : 'fas'} fa-heart"></i>`);
    
    if (isActive) {
        alert('تمت إزالة المنتج من المفضلة');
    } else {
        alert('تمت إضافة المنتج إلى المفضلة');
    }
}
</script>