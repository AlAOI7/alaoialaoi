// أزرار المفضلة التفاعلية
class WishlistButtons {
    constructor() {
        this.buttons = document.querySelectorAll('.wishlist-btn');
        this.init();
    }

    init() {
        this.buttons.forEach(button => {
            const productId = button.dataset.productId;

            // التحقق من حالة المنتج عند التحميل
            this.checkWishlistStatus(productId, button);

            // إضافة حدث النقر
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleWishlist(productId, button);
            });
        });
    }

    checkWishlistStatus(productId, button) {
        fetch('ajax_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=check&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.in_wishlist) {
                    this.setActive(button);
                } else {
                    this.setInactive(button);
                }
            })
            .catch(error => {
                console.error('Error checking wishlist status:', error);
            });
    }

    toggleWishlist(productId, button) {
        fetch('ajax_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.in_wishlist) {
                        this.setActive(button);
                        this.showNotification('تمت إضافة المنتج إلى المفضلة', 'success');
                    } else {
                        this.setInactive(button);
                        this.showNotification('تمت إزالة المنتج من المفضلة', 'info');
                    }

                    // تحديث العداد في الهيدر إذا كان موجودًا
                    this.updateWishlistCount();
                } else {
                    this.showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error toggling wishlist:', error);
                this.showNotification('حدث خطأ، يرجى المحاولة مرة أخرى', 'error');
            });
    }

    setActive(button) {
        button.classList.remove('btn-outline-danger');
        button.classList.add('btn-danger');
        button.innerHTML = '<i class="fas fa-heart"></i>';
        button.title = 'إزالة من المفضلة';
    }

    setInactive(button) {
        button.classList.remove('btn-danger');
        button.classList.add('btn-outline-danger');
        button.innerHTML = '<i class="far fa-heart"></i>';
        button.title = 'أضف إلى المفضلة';
    }

    showNotification(message, type) {
        // يمكنك استخدام مكتبة مثل Toastify.js أو إنشاء إشعار مخصص
        console.log(`${type}: ${message}`);
        // أو إضافة كود لعرض الإشعار هنا
    }

    updateWishlistCount() {
        // تحديث العداد في الهيدر
        const countElement = document.getElementById('wishlist-count');
        if (countElement) {
            // يمكنك جلب العدد الجديد من السيرفر
            // أو زيادة/تقليل العدد محليًا
        }
    }
}

// تهيئة الأزرار عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    new WishlistButtons();
});