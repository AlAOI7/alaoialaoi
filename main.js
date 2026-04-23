// main.js أو في قسم <script> في الصفحة

// دالة لإضافة منتج إلى السلة
function addToCart(productId, quantity = 1) {
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
                updateCartCount(response.cart_count);

                // عرض رسالة نجاح
                showAlert('تمت إضافة المنتج إلى السلة بنجاح!', 'success');

                // تأثير زر السلة
                const cartBtn = $(`.add-to-cart-btn[data-product-id="${productId}"]`);
                cartBtn.addClass('clicked');
                setTimeout(() => cartBtn.removeClass('clicked'), 500);
            } else {
                showAlert(response.message || 'حدث خطأ', 'error');
            }
        },
        error: function() {
            showAlert('حدث خطأ أثناء إضافة المنتج إلى السلة', 'error');
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
                updateFavoritesCount(response.favorites_count);
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
    // إظهار مؤشر التحميل
    $('#productDetailModal .modal-body').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <p class="mt-3">جاري تحميل تفاصيل المنتج...</p>
        </div>
    `);

    // تحميل بيانات المنتج
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
                $('#product-detail-rating').html(ratingStars);

                $('#product-detail-description').html(product.description || 'لا يوجد وصف.');
                $('#product-detail-stock').text(product.stock || 0);
                $('#product-detail-quantity').val(1);

                // إعداد أزرار الإضافة
                $('#add-to-cart-detail').off('click').on('click', function() {
                    const quantity = parseInt($('#product-detail-quantity').val());
                    addToCart(productId, quantity);
                    $('#productDetailModal').modal('hide');
                });

                $('#add-to-favorites-detail').off('click').on('click', function() {
                    const isFavorite = response.is_favorite || false;
                    toggleFavorite(productId, $(this));
                    $(this).toggleClass('active', !isFavorite);
                });

                // تعيين حالة زر المفضلة
                $('#add-to-favorites-detail').toggleClass('active', response.is_favorite || false);

                // عرض النافذة المنبثقة
                $('#productDetailModal').modal('show');
            } else {
                showAlert(response.message || 'حدث خطأ في تحميل المنتج', 'error');
            }
        },
        error: function() {
            showAlert('حدث خطأ في الاتصال بالخادم', 'error');
        }
    });
}

// دالة لتحديث عدد العناصر في السلة
function updateCartCount(count) {
    $('.cart-count').text(count);
    $('.notification-badge').text(count);

    // تأثير التحديث
    $('.cart-count').addClass('updated');
    setTimeout(() => $('.cart-count').removeClass('updated'), 500);
}

// دالة لتحديث عدد المفضلة
function updateFavoritesCount(count) {
    $('.favorites-count').text(count);
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
        <div class="custom-alert alert ${alertClass} alert-dismissible fade show" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
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

// إدارة الكمية في نافذة التفاصيل
$(document).ready(function() {
    // زيادة الكمية
    $('.quantity-btn.plus').click(function() {
        const input = $(this).siblings('.quantity-input');
        let value = parseInt(input.val());
        const max = parseInt($('#product-detail-stock').text());

        if (value < max) {
            input.val(value + 1);
        }
    });

    // تقليل الكمية
    $('.quantity-btn.minus').click(function() {
        const input = $(this).siblings('.quantity-input');
        let value = parseInt(input.val());

        if (value > 1) {
            input.val(value - 1);
        }
    });
});

// تأثيرات CSS إضافية
const style = document.createElement('style');
style.textContent = `
    .add-to-cart-btn.clicked {
        animation: bounce 0.5s;
    }
    
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(0.9); }
    }
    
    .favorite-btn.heartbeat {
        animation: heartbeat 0.3s;
    }
    
    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    .cart-count.updated {
        animation: pulse 0.5s;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }
    
    .custom-alert {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(style);