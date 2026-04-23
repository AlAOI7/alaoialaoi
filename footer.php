    <!-- Footer -->
    <footer class="bg-dark text-light pt-5 pb-3">
        <?php
        $site_name = function_exists('get_setting') ? get_setting('site_name', 'Be Pretty') : 'Be Pretty';
        $site_desc = function_exists('get_setting') ? get_setting('site_description', 'متجرك الأول لمستحضرات التجميل والعناية بالبشرة.') : 'متجرك الأول لمستحضرات التجميل والعناية بالبشرة.';
        $snapchat = function_exists('get_setting') ? get_setting('social_snapchat', '#') : '#';
        $tiktok = function_exists('get_setting') ? get_setting('social_tiktok', '#') : '#';
        $instagram = function_exists('get_setting') ? get_setting('social_instagram', '#') : '#';
        $twitter = function_exists('get_setting') ? get_setting('social_twitter', '#') : '#';
        $copyright_year = function_exists('get_setting') ? get_setting('copyright_year', '2023') : '2023';
        ?>
        <div class="container">
            <div class="row">
                <!-- معلومات المتجر -->
                <div class="col-md-4 mb-4">
                    <h5><?= htmlspecialchars($site_name) ?></h5>
                    <p><?= htmlspecialchars($site_desc) ?></p>
                    <div class="social-links">
                        <a href="<?= htmlspecialchars($snapchat) ?>"><i class="fab fa-snapchat"></i></a>
                        <a href="<?= htmlspecialchars($tiktok) ?>"><i class="fab fa-tiktok"></i></a>
                        <a href="<?= htmlspecialchars($instagram) ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars($twitter) ?>"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <!-- روابط سريعة -->
                <div class="col-md-2 mb-4">
                    <h6>روابط سريعة</h6>
                    <ul class="list-unstyled">
                        <li><a href="about.php">من نحن</a></li>
                        <li><a href="contact.php">اتصل بنا</a></li>
                        <li><a href="terms.php">الشروط والأحكام</a></li>
                        <li><a href="blog.php">المدونة</a></li>
                    </ul>
                </div>
                
                <!-- خدمة العملاء -->
                <div class="col-md-3 mb-4">
                    <h6>خدمة العملاء</h6>
                    <ul class="list-unstyled">
                        <li><a href="shipping.php">الشحن والتوصيل</a></li>
                        <li><a href="returns.php">سياسة الإرجاع</a></li>
                        <li><a href="faq.php">الأسئلة الشائعة</a></li>
                        <li><a href="support.php">الدعم الفني</a></li>
                    </ul>
                </div>
                
                <!-- الاشتراك في النشرة البريدية -->
                <div class="col-md-3 mb-4">
                    <h6>اشترك في نشرتنا البريدية</h6>
                    <div class="input-group mb-2">
                        <input type="email" class="form-control" placeholder="بريدك الإلكتروني">
                        <button class="btn btn-danger" type="button">اشتراك</button>
                    </div>
                    <small>احصلي على آخر العروض والتخفيضات</small>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- حقوق النشر -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= htmlspecialchars($copyright_year) ?> <?= htmlspecialchars($site_name) ?>. جميع الحقوق محفوظة.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="https://via.placeholder.com/200x30?text=طرق+الدفع+المتاحة" alt="طرق الدفع" class="img-fluid">
                </div>
            </div>
        </div>
    </footer>

    <nav class="bottom-tab-bar">
        <a href="home.php" class="tab-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="categories.php" class="tab-item">
            <i class="fas fa-th-large"></i>
            <span>الفئات</span>
        </a>
        <a href="cart.php" class="tab-item">
            <i class="fas fa-shopping-cart"></i>
            <span>السلة</span>
        </a>
         <a href="product.php" class="tab-item active">
            <i class="fas fa-user"></i>
            <span>المنتجات</span>
        </a>
        <a href="order.php" class="tab-item">
            <i class="fas fa-list-alt"></i>
            <span>الطلبات</span>
        </a>
        <a href="profile.php" class="tab-item active">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <script>



    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
     <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   
      <script>
        // Dropdown functionality
        $(document).ready(function() {
            const dropdownToggle = $('#user-dropdown-toggle');
            const dropdownMenu = $('#user-dropdown-menu');
            
            dropdownToggle.click(function(e) {
                e.stopPropagation();
                dropdownMenu.toggleClass('show');
            });
            
            // Close dropdown when clicking outside
            $(document).click(function() {
                dropdownMenu.removeClass('show');
            });
            
            // Prevent dropdown from closing when clicking inside it
            dropdownMenu.click(function(e) {
                e.stopPropagation();
            });
            
            // Sidebar functionality
            $('#menu-toggle').click(function() {
                $('#sidebar-menu').addClass('active');
            });
            
            $('#close-menu').click(function() {
                $('#sidebar-menu').removeClass('active');
            });
            
            $(document).mouseup(function(e) {
                const sidebar = $('#sidebar-menu');
                if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0) {
                    sidebar.removeClass('active');
                }
            });
        });
    </script>
    <script>
                  


                // دالة لإضافة منتج إلى السلة
                function addToCart(productId, quantity, button) {
                    if (button) {
                        // إضافة تأثير مؤقت
                        $(button).addClass('loading');
                        $(button).prop('disabled', true);
                    }
                    
                    $.ajax({
                        url: 'ajax/add_to_cart.php',
                        method: 'POST',
                        data: {
                            product_id: productId,
                            quantity: quantity
                        },
                        success: function(response) {
                            if (response.success) {
                                // تحديث عدد العناصر في السلة
                                updateCartCount();
                                
                                // عرض رسالة نجاح
                                showAlert('تمت إضافة المنتج إلى السلة بنجاح!', 'success');
                                
                                // تأثير إضافي على الزر
                                if (button) {
                                    $(button).removeClass('loading').addClass('success');
                                    setTimeout(() => {
                                        $(button).removeClass('success').prop('disabled', false);
                                    }, 1000);
                                }
                            } else {
                                showAlert(response.message || 'حدث خطأ', 'error');
                                if (button) {
                                    $(button).removeClass('loading').prop('disabled', false);
                                }
                            }
                        },
                        error: function() {
                            showAlert('حدث خطأ أثناء إضافة المنتج إلى السلة', 'error');
                            if (button) {
                                $(button).removeClass('loading').prop('disabled', false);
                            }
                        }
                    });
                }

                // دالة للتبديل بين المفضلة
                function toggleFavorite(productId, button) {
                    const isActive = $(button).hasClass('active');
                    const action = isActive ? 'remove' : 'add';
                    
                    $.ajax({
                        url: 'ajax/toggle_favorite.php',
                        method: 'POST',
                        data: {
                            product_id: productId,
                            action: action
                        },
                        success: function(response) {
                            if (response.success) {
                                $(button).toggleClass('active');
                                
                                // تأثير القلب
                                $(button).addClass('heartbeat');
                                setTimeout(() => $(button).removeClass('heartbeat'), 300);
                                
                                // عرض رسالة
                                const message = isActive ? 'تمت إزالة المنتج من المفضلة' : 'تمت إضافة المنتج إلى المفضلة';
                                showAlert(message, 'success');
                                
                                // تحديث عدد المفضلة
                                updateFavoritesCount();
                            } else {
                                showAlert(response.message || 'حدث خطأ', 'error');
                            }
                        },
                        error: function() {
                            showAlert('حدث خطأ أثناء تحديث المفضلة', 'error');
                        }
                    });
                }
              // دالة لعرض تفاصيل المنتج
                function showProductDetails(productId) {
                    $.ajax({
                        url: 'ajax/get_product_details.php',
                        method: 'GET',
                        data: { product_id: productId },
                        success: function(response) {
                            if (response.success) {
                                const product = response.product;
                                
                                // تحديث صورة المنتج
                                const productImage = $('#product-detail-img');
                                productImage.attr('src', product.main_image);
                                productImage.attr('alt', product.name);
                                productImage.on('error', function() {
                                    $(this).attr('src', 'img/1.jpg');
                                });
                                
                                // تحديث اسم المنتج
                                $('#productDetailModalLabel').text('تفاصيل المنتج: ' + product.name);
                                $('#product-detail-name').text(product.name);
                                
                                // تحديث الفئة
                                $('#product-detail-category').text('الفئة: ' + product.category_name);
                                
                                // تحديث الأسعار
                                $('#product-detail-price').text(product.selling_price + ' ر.س');
                                
                                if (product.old_price && product.old_price > 0) {
                                    $('#product-detail-old-price').text(product.old_price + ' ر.س').show();
                                    if (product.discount_percentage > 0) {
                                        $('#product-detail-price').addClass('text-danger fw-bold');
                                        $('#product-detail-old-price').addClass('text-decoration-line-through text-muted');
                                        $('.discount-badge').remove();
                                        $('#product-detail-price').before(
                                            '<span class="discount-badge bg-danger text-white px-2 py-1 rounded ms-2">-' + 
                                            product.discount_percentage + '%</span>'
                                        );
                                    }
                                } else {
                                    $('#product-detail-old-price').hide();
                                    $('#product-detail-price').removeClass('text-danger fw-bold');
                                    $('.discount-badge').remove();
                                }
                                
                                // تحديث التقييم
                                let ratingStars = '';
                                for (let i = 1; i <= 5; i++) {
                                    ratingStars += `<i class="${i <= Math.round(product.rating) ? 'fas' : 'far'} fa-star ${i <= product.rating ? 'text-warning' : ''}"></i>`;
                                }
                                ratingStars += ` <small class="text-muted ms-2">(${product.total_reviews} تقييم)</small>`;
                                $('#product-detail-rating').html(ratingStars);
                                
                                // تحديث الوصف
                                $('#product-detail-description').text(product.description || 'لا يوجد وصف متاح');
                                
                                // تحديث المخزون
                                $('#product-detail-stock').text(product.stock);
                                $('#product-detail-stock').removeClass('text-success text-warning text-danger');
                                $('#product-detail-stock').addClass(product.stock_class);
                                
                                // تحديث الكمية
                                $('#product-detail-quantity').val(response.cart_quantity || 1);
                                
                                // إعداد أزرار الإضافة
                                $('#add-to-cart-detail')
                                    .data('product-id', productId)
                                    .off('click')
                                    .on('click', function() {
                                        const quantity = parseInt($('#product-detail-quantity').val());
                                        addToCart(productId, quantity, this);
                                    });
                                
                                // تحديث حالة زر المفضلة
                                const favoriteBtn = $('#add-to-favorites-detail');
                                favoriteBtn
                                    .data('product-id', productId)
                                    .off('click')
                                    .on('click', function() {
                                        toggleFavorite(productId, this);
                                    });
                                
                                if (response.is_favorite) {
                                    favoriteBtn.addClass('active').html('<i class="fas fa-heart me-2"></i>مضاف للمفضلة');
                                } else {
                                    favoriteBtn.removeClass('active').html('<i class="far fa-heart me-2"></i>أضف للمفضلة');
                                }
                                
                                // عرض الألوان إذا كانت موجودة
                                updateColors(product.colors);
                                
                                // عرض المقاسات إذا كانت موجودة
                                updateSizes(product.sizes);
                                
                                // إضافة أزرار التحكم بالكمية
                                setupQuantityControls();
                                
                                // فتح النافذة المنبثقة
                                $('#productDetailModal').modal('show');
                                
                            } else {
                                showToast('error', response.message || 'حدث خطأ في تحميل المنتج');
                            }
                        },
                        error: function() {
                            showToast('error', 'حدث خطأ في الاتصال بالخادم');
                        }
                    });
                }

                // دالة لتحديث عرض الألوان
                function updateColors(colors) {
                    const colorsContainer = $('#product-colors');
                    colorsContainer.empty();
                    
                    if (colors.length > 0) {
                        colorsContainer.append('<strong class="d-block mb-2">الألوان المتاحة:</strong>');
                        const colorsList = $('<div class="d-flex gap-2 mb-3"></div>');
                        
                        colors.forEach(color => {
                            const colorBtn = $(`
                                <button type="button" class="btn btn-sm border rounded-circle p-3" 
                                        style="background-color: ${color.color_code}" 
                                        title="${color.color_name}"
                                        data-color="${color.color_code}">
                                </button>
                            `);
                            colorsList.append(colorBtn);
                        });
                        
                        colorsContainer.append(colorsList);
                    }
                }

                // دالة لتحديث عرض المقاسات
                function updateSizes(sizes) {
                    const sizesContainer = $('#product-sizes');
                    sizesContainer.empty();
                    
                    if (sizes.length > 0) {
                        sizesContainer.append('<strong class="d-block mb-2">المقاسات المتاحة:</strong>');
                        const sizesList = $('<div class="d-flex flex-wrap gap-2 mb-3"></div>');
                        
                        sizes.forEach(size => {
                            let sizeText = size.size;
                            if (size.length || size.width) {
                                sizeText += ' (';
                                if (size.length) sizeText += size.length + ' سم طول';
                                if (size.length && size.width) sizeText += ' × ';
                                if (size.width) sizeText += size.width + ' سم عرض';
                                sizeText += ')';
                            }
                            
                            const sizeBtn = $(`
                                <button type="button" class="btn btn-outline-primary btn-sm">
                                    ${sizeText}
                                </button>
                            `);
                            sizesList.append(sizeBtn);
                        });
                        
                        sizesContainer.append(sizesList);
                    }
                }

                // دالة لإعداد أزرار التحكم بالكمية
                function setupQuantityControls() {
                    const quantityInput = $('#product-detail-quantity');
                    const productStock = parseInt($('#product-detail-stock').text());
                    
                    $('.quantity-btn').off('click').on('click', function() {
                        const currentQuantity = parseInt(quantityInput.val());
                        const isIncrement = $(this).text() === '+';
                        
                        let newQuantity = currentQuantity;
                        if (isIncrement) {
                            if (currentQuantity < productStock) {
                                newQuantity = currentQuantity + 1;
                            }
                        } else {
                            if (currentQuantity > 1) {
                                newQuantity = currentQuantity - 1;
                            }
                        }
                        
                        quantityInput.val(newQuantity);
                    });
                }

                // دالة لإظهار رسائل التأكيد
                function showToast(type, message) {
                    const toast = $(`
                        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-times-circle'} me-2"></i>
                                        ${message}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    $('body').append(toast);
                    $('.toast').toast('show');
                    
                    setTimeout(() => {
                        $('.toast').toast('hide');
                        setTimeout(() => {
                            $('.toast').remove();
                        }, 300);
                    }, 3000);
                }
            // دالة لتحديث عدد العناصر في السلة
            function updateCartCount() {
                $.ajax({
                    url: 'ajax/get_cart_count.php',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('.cart-count, .notification-badge').text(response.count);
                        }
                    }
                });
            }

                // دالة لتحديث عدد المفضلة
                function updateFavoritesCount() {
                    $.ajax({
                        url: 'ajax/get_favorites_count.php',
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                $('.favorites-count').text(response.count);
                            }
                        }
                    });
                }

                // دالة لعرض رسائل التنبيه
                function showAlert(message, type = 'info') {
                    // إزالة أي رسالة سابقة
                    $('.custom-alert').remove();
                    
                    // إنشاء رسالة جديدة
                    const alertClass = {
                        'success': 'alert-success',
                        'error': 'alert-danger',
                        'warning': 'alert-warning',
                        'info': 'alert-info'
                    }[type] || 'alert-info';
                    
                    const iconClass = {
                        'success': 'fa-check-circle',
                        'error': 'fa-exclamation-circle',
                        'warning': 'fa-exclamation-triangle',
                        'info': 'fa-info-circle'
                    }[type] || 'fa-info-circle';
                    
                    const alertHtml = `
                        <div class="custom-alert alert ${alertClass} alert-dismissible fade show position-fixed" 
                            style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                            <i class="fas ${iconClass} me-2"></i>
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    
                    $('body').append(alertHtml);
                    
                    // إخفاء الرسالة تلقائياً بعد 3 ثواني
                    setTimeout(() => {
                        $('.custom-alert').alert('close');
                    }, 3000);
                }

            // دالة لإعادة إرفاق الأحداث للمنتجات الجديدة
            function attachProductEvents() {
                // الكمية في نافذة التفاصيل
                $('.quantity-btn').off('click').on('click', function() {
                    const input = $(this).siblings('.quantity-input');
                    let value = parseInt(input.val());
                    
                    if ($(this).hasClass('plus')) {
                        value++;
                    } else if ($(this).hasClass('minus') && value > 1) {
                        value--;
                    }
                    
                    input.val(value);
                });
            }


</script>
    <script>

        // في ملف main.js أو في <script>
        $(document).ready(function() {
            // تحديث عدد العناصر في السلة والمفضلة عند التحميل
            updateCartCount();
            updateFavoritesCount();
            
            // فتح Modal السلة
            $('#cartModal').on('show.bs.modal', function() {
                loadCartModal();
            });
            
            // فتح Modal المفضلة
            $('#favoritesModal').on('show.bs.modal', function() {
                loadFavoritesModal();
            });
            
            // تحديث السلة عند إغلاق الـ Modal
            $('#cartModal, #favoritesModal').on('hidden.bs.modal', function() {
                updateCartCount();
                updateFavoritesCount();
            });
         });

        // دالة لتحميل محتوى Modal السلة
        function loadCartModal() {
            $.ajax({
                url: 'ajax/get_cart_modal.php',
                method: 'GET',
                beforeSend: function() {
                    $('#cartModalBody').html(`
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-3">جاري تحميل سلة التسوق...</p>
                        </div>
                    `);
                },
                success: function(response) {
                    if (response.success) {
                        if (response.items.length > 0) {
                            let itemsHtml = '';
                            
                            response.items.forEach(function(item) {
                                itemsHtml += `
                                    <div class="cart-item d-flex align-items-center" data-item-id="${item.id}">
                                        <img src="${item.image}" alt="${item.name}" class="cart-item-img me-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${item.name}</h6>
                                            ${item.options ? `<p class="product-options mb-1">${item.options}</p>` : ''}
                                            <div class="quantity-controls d-flex align-items-center">
                                                <button class="quantity-btn minus" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                                <input type="text" class="quantity-input" value="${item.quantity}" readonly>
                                                <button class="quantity-btn plus" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">+</button>
                                                <span class="ms-3 text-danger fw-bold">${item.total_price} ر.س</span>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger delete-btn ms-2" onclick="removeFromCart(${item.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            });
                            
                            $('#cartModalBody').html(`<div class="cart-items">${itemsHtml}</div>`);
                            $('#cartModalFooter').show();
                            
                            // تحديث المجاميع
                            $('#cart-total-price').text(response.summary.total_price + ' ر.س');
                            $('#cart-tax').text(response.summary.tax + ' ر.س');
                            $('#cart-grand-total').text(response.summary.grand_total + ' ر.س');
                        } else {
                            $('#cartModalBody').html(`
                                <div class="empty-cart">
                                    <i class="fas fa-shopping-cart"></i>
                                    <h4>سلة التسوق فارغة</h4>
                                    <p class="text-muted">أضف بعض المنتجات لتظهر هنا</p>
                                    <button class="btn btn-primary mt-3" data-bs-dismiss="modal">
                                        <i class="fas fa-store me-2"></i>متابعة التسوق
                                    </button>
                                </div>
                            `);
                            $('#cartModalFooter').hide();
                        }
                    } else {
                        $('#cartModalBody').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${response.message || 'حدث خطأ في تحميل السلة'}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#cartModalBody').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            حدث خطأ في الاتصال بالخادم
                        </div>
                    `);
                }
            });
        }

        // دالة لتحميل محتوى Modal المفضلة
        function loadFavoritesModal() {
            $.ajax({
                url: 'ajax/get_favorites_modal.php',
                method: 'GET',
                beforeSend: function() {
                    $('#favoritesModalBody').html(`
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-3">جاري تحميل قائمة المفضلة...</p>
                        </div>
                    `);
                },
                success: function(response) {
                    if (response.success) {
                        if (response.items.length > 0) {
                            let itemsHtml = '';
                            
                            response.items.forEach(function(item) {
                                itemsHtml += `
                                    <div class="favorite-item d-flex align-items-center" data-item-id="${item.id}">
                                        <img src="${item.image}" alt="${item.name}" class="favorite-item-img me-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${item.name}</h6>
                                            <p class="text-muted mb-1">${item.category}</p>
                                            <div class="d-flex align-items-center">
                                                <span class="text-danger fw-bold me-3">${item.price} ر.س</span>
                                                <span class="badge ${item.in_stock ? 'bg-success' : 'bg-danger'}">
                                                    ${item.in_stock ? 'متوفر' : 'غير متوفر'}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            ${item.in_stock ? 
                                                `<button class="btn btn-sm btn-outline-danger me-2" onclick="addToCart(${item.product_id}, 1)">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>` : ''
                                            }
                                            <button class="btn btn-sm btn-outline-secondary delete-btn" onclick="removeFromWishlist(${item.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            $('#favoritesModalBody').html(`<div class="favorite-items">${itemsHtml}</div>`);
                            $('#favoritesModalFooter').show();
                        } else {
                            $('#favoritesModalBody').html(`
                                <div class="empty-favorites">
                                    <i class="fas fa-heart"></i>
                                    <h4>قائمة المفضلة فارغة</h4>
                                    <p class="text-muted">أضف بعض المنتجات لتظهر هنا</p>
                                    <button class="btn btn-primary mt-3" data-bs-dismiss="modal">
                                        <i class="fas fa-store me-2"></i>متابعة التسوق
                                    </button>
                                </div>
                            `);
                            $('#favoritesModalFooter').hide();
                        }
                    } else {
                        $('#favoritesModalBody').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${response.message || 'حدث خطأ في تحميل المفضلة'}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#favoritesModalBody').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            حدث خطأ في الاتصال بالخادم
                        </div>
                    `);
                }
            });
        }

        // دالة لتحديث كمية المنتج في السلة
        function updateCartQuantity(cartItemId, newQuantity) {
            if (newQuantity < 1) {
                removeFromCart(cartItemId);
                return;
            }
            
            $.ajax({
                url: 'ajax/update_cart_quantity.php',
                method: 'POST',
                data: {
                    cart_item_id: cartItemId,
                    quantity: newQuantity
                },
                success: function(response) {
                    if (response.success) {
                        // تحديث السلة مباشرة
                        loadCartModal();
                        updateCartCount();
                        showAlert('تم تحديث الكمية بنجاح', 'success');
                    } else {
                        showAlert(response.message || 'حدث خطأ', 'error');
                    }
                }
            });
        }

                // دالة لحذف منتج من السلة
                function removeFromCart(cartItemId) {
                    if (!confirm('هل تريد حذف هذا المنتج من السلة؟')) return;
                    
                    $.ajax({
                        url: 'ajax/remove_from_cart.php',
                        method: 'POST',
                        data: { cart_item_id: cartItemId },
                        success: function(response) {
                            if (response.success) {
                                loadCartModal();
                                updateCartCount();
                                showAlert('تم حذف المنتج من السلة', 'success');
                            } else {
                                showAlert(response.message || 'حدث خطأ', 'error');
                            }
                        }
                    });
                }

                // دالة لحذف منتج من المفضلة
                function removeFromWishlist(wishlistItemId) {
                    if (!confirm('هل تريد حذف هذا المنتج من المفضلة؟')) return;
                    
                    $.ajax({
                        url: 'ajax/remove_from_wishlist.php',
                        method: 'POST',
                        data: { wishlist_item_id: wishlistItemId },
                        success: function(response) {
                            if (response.success) {
                                loadFavoritesModal();
                                updateFavoritesCount();
                                showAlert('تم حذف المنتج من المفضلة', 'success');
                            } else {
                                showAlert(response.message || 'حدث خطأ', 'error');
                            }
                        }
                    });
                }

                // دالة لتحديث عدد عناصر السلة
                function updateCartCount() {
                    $.ajax({
                        url: 'ajax/get_cart_count.php',
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                $('.cart-count, #cart-badge').text(response.count);
                            }
                        }
                    });
                }

                // دالة لتحديث عدد عناصر المفضلة
                function updateFavoritesCount() {
                    $.ajax({
                        url: 'ajax/get_favorites_count.php',
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                $('.favorites-count, #favorites-badge').text(response.count);
                            }
                        }
                    });
                }
                $(document).ready(function() {
                    // تحديث عدد عناصر السلة
                    function updateCartCount() {
                        $.ajax({
                            url: 'ajax/get_cart_count.php',
                            method: 'GET',
                            success: function(response) {
                                $('#cart-badge').text(response.count);
                            }
                        });
                    }
                    
                    // تحديث عند التحميل
                    updateCartCount();
                    
                    // فلتر المنتجات حسب الفئة
                    $('.category-filter-btn').click(function() {
                        const categoryId = $(this).data('category');
                        
                        // تحديث الحالة النشطة للأزرار
                        $('.category-filter-btn').removeClass('active');
                        $(this).addClass('active');
                        
                        // إخفاء جميع المنتجات أولاً
                        $('.product-card').hide();
                        
                        if (categoryId === 'all') {
                            // إظهار جميع المنتجات
                            $('.product-card').show();
                        } else {
                            // إظهار المنتجات الخاصة بالفئة المحددة
                            $('.product-card[data-category="' + categoryId + '"]').show();
                        }
                    });
                    
                    // إدارة الكمية
                    $('.quantity-btn').click(function() {
                        const input = $(this).siblings('.quantity-input');
                        let value = parseInt(input.val());
                        
                        if ($(this).text() === '+') {
                            value++;
                        } else if ($(this).text() === '-' && value > 1) {
                            value--;
                        }
                        
                        input.val(value);
                    });
                    
                    // إضافة منتج إلى السلة
                    $(document).on('click', '.add-to-cart-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const productId = $(this).data('product-id');
                        
                        $.ajax({
                            url: 'ajax/add_to_cart.php',
                            method: 'POST',
                            data: {
                                product_id: productId,
                                quantity: 1
                            },
                            success: function(response) {
                                if (response.success) {
                                    // تحديث عدد العناصر في السلة
                                    $('#cart-badge').text(response.cart_count);
                                    
                                    // عرض رسالة نجاح
                                    alert(response.message);
                                } else {
                                    alert(response.message);
                                }
                            }
                        });
                    });
                    
                    // عرض تفاصيل المنتج
                    $(document).on('click', '.quick-view-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const productId = $(this).data('product-id');
                        
                        $.ajax({
                            url: 'ajax/get_product_details.php',
                            method: 'GET',
                            data: { product_id: productId },
                            success: function(response) {
                                if (response.success) {
                                    const product = response.product;
                                    
                                    // تعبئة البيانات في النافذة المنبثقة
                                    $('#product-detail-img').attr('src', product.image || 'img/default-product.jpg');
                                    $('#product-detail-name').text(product.name);
                                    $('#product-detail-category').text('الفئة: ' + product.category_name);
                                    $('#product-detail-price').text(product.selling_price + ' ر.س');
                                    
                                    if (product.old_price) {
                                        $('#product-detail-old-price').text(product.old_price + ' ر.س').show();
                                    } else {
                                        $('#product-detail-old-price').hide();
                                    }
                                    
                                    // إنشاء نجوم التقييم
                                    let ratingStars = '';
                                    const rating = product.rating || 0;
                                    for (let i = 1; i <= 5; i++) {
                                        ratingStars += `<i class="${i <= rating ? 'fas' : 'far'} fa-star"></i>`;
                                    }
                                    $('#product-detail-rating').php(ratingStars);
                                    
                                    $('#product-detail-description').text(product.description || 'لا يوجد وصف.');
                                    $('#product-detail-stock').text(product.stock || 0);
                                    $('#product-detail-quantity').val(1);
                                    
                                    // إعداد أزرار الإضافة
                                    $('#add-to-cart-detail').data('product-id', productId);
                                    $('#add-to-favorites-detail').data('product-id', productId);
                                    
                                    // فتح النافذة المنبثقة
                                    $('#productDetailModal').modal('show');
                                }
                            }
                        });
                    });
                    
                    // إضافة منتج إلى السلة من نافذة التفاصيل
                    $('#add-to-cart-detail').click(function() {
                        const productId = $(this).data('product-id');
                        const quantity = parseInt($('#product-detail-quantity').val());
                        
                        $.ajax({
                            url: 'ajax/add_to_cart.php',
                            method: 'POST',
                            data: {
                                product_id: productId,
                                quantity: quantity
                            },
                            success: function(response) {
                                if (response.success) {
                                    // تحديث عدد العناصر في السلة
                                    $('#cart-badge').text(response.cart_count);
                                    
                                    // إغلاق النافذة المنبثقة وعرض رسالة نجاح
                                    $('#productDetailModal').modal('hide');
                                    alert(response.message);
                                } else {
                                    alert(response.message);
                                }
                            }
                        });
                    });
                    
                    // إضافة منتج إلى المفضلة
                    $(document).on('click', '.favorite-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const productId = $(this).data('product-id');
                        const isActive = $(this).hasClass('active');
                        
                        $.ajax({
                            url: 'ajax/toggle_favorite.php',
                            method: 'POST',
                            data: {
                                product_id: productId,
                                action: isActive ? 'remove' : 'add'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // تبديل حالة الزر
                                    $(this).toggleClass('active');
                                    alert(response.message);
                                }
                            }.bind(this)
                        });
                    });
                });
    </script>
</body>
</html>