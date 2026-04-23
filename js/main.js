// تنشيط/إلغاء تنشيط القائمة الجانبية
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const closeMenu = document.getElementById('close-menu');
    const sidebarMenu = document.getElementById('sidebar-menu');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebarMenu.classList.add('active');
        });
    }

    if (closeMenu) {
        closeMenu.addEventListener('click', function() {
            sidebarMenu.classList.remove('active');
        });
    }

    // إغلاق القائمة الجانبية عند النقر خارجها
    document.addEventListener('click', function(event) {
        if (!sidebarMenu.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebarMenu.classList.remove('active');
        }
    });

    // تنشيط/إلغاء تنشيط زر المفضلة
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            this.innerHTML = this.classList.contains('active') ?
                '<i class="fas fa-heart"></i>' :
                '<i class="far fa-heart"></i>';
        });
    });

    // تحديث عداد السلة
    function updateCartBadge() {
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const cartBadge = document.getElementById('cart-badge');
        if (cartBadge) {
            cartBadge.textContent = cartItems.length;
            cartBadge.style.display = cartItems.length > 0 ? 'flex' : 'none';
        }
    }

    updateCartBadge();

    // إضافة منتج إلى السلة
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            // الحصول على معلومات المنتج
            const productCard = this.closest('.product-card');
            const productId = productCard.dataset.productId || Math.floor(Math.random() * 1000);
            const productName = productCard.querySelector('h3').textContent;
            const productPrice = productCard.querySelector('.product-price').textContent;
            const productImg = productCard.querySelector('.product-img').src;

            // الحصول على السلة الحالية
            let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

            // التحقق إذا كان المنتج موجود بالفعل في السلة
            const existingItemIndex = cartItems.findIndex(item => item.id === productId);

            if (existingItemIndex > -1) {
                // زيادة الكمية إذا كان المنتج موجود
                cartItems[existingItemIndex].quantity += 1;
            } else {
                // إضافة منتج جديد إلى السلة
                cartItems.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImg,
                    quantity: 1
                });
            }

            // حفظ السلة في localStorage
            localStorage.setItem('cartItems', JSON.stringify(cartItems));

            // تحديث العداد
            updateCartBadge();

            // عرض رسالة تأكيد
            alert('تمت إضافة المنتج إلى السلة!');

            // تحديث واجهة المستخدم للسلة
            updateCartUI();
        });
    });

    // تحديث واجهة مستخدم السلة
    function updateCartUI() {
        const cartItemsContainer = document.getElementById('cart-items-container');
        if (!cartItemsContainer) return;

        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

        if (cartItems.length === 0) {
            cartItemsContainer.innerHTML = '<p class="text-center py-4">السلة فارغة</p>';
            return;
        }

        let cartHTML = '';
        let total = 0;

        cartItems.forEach(item => {
            const price = parseFloat(item.price.replace(/[^\d.]/g, ''));
            const itemTotal = price * item.quantity;
            total += itemTotal;

            cartHTML += `
                <div class="cart-item">
                    <div class="d-flex">
                        <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                        <div class="flex-grow-1 me-3">
                            <h6 class="mb-1">${item.name}</h6>
                            <p class="mb-1 text-muted">${item.price}</p>
                            <div class="quantity-controls">
                                <button class="quantity-btn minus-btn" data-id="${item.id}">-</button>
                                <input type="text" class="quantity-input" value="${item.quantity}" readonly>
                                <button class="quantity-btn plus-btn" data-id="${item.id}">+</button>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-danger remove-item" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        cartHTML += `
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h5>الإجمالي</h5>
                <h5 class="text-danger">${total.toFixed(2)} ر.س</h5>
            </div>
        `;

        cartItemsContainer.innerHTML = cartHTML;

        // إضافة مستمعي الأحداث للأزرار الجديدة
        document.querySelectorAll('.minus-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                updateQuantity(this.dataset.id, -1);
            });
        });

        document.querySelectorAll('.plus-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                updateQuantity(this.dataset.id, 1);
            });
        });

        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                removeItem(this.dataset.id);
            });
        });
    }

    // تحديث كمية المنتج
    function updateQuantity(productId, change) {
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const itemIndex = cartItems.findIndex(item => item.id === productId);

        if (itemIndex > -1) {
            cartItems[itemIndex].quantity += change;

            // إزالة المنتج إذا كانت الكمية أقل من 1
            if (cartItems[itemIndex].quantity < 1) {
                cartItems.splice(itemIndex, 1);
            }

            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            updateCartBadge();
            updateCartUI();
        }
    }

    // إزالة المنتج من السلة
    function removeItem(productId) {
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        cartItems = cartItems.filter(item => item.id !== productId);

        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        updateCartBadge();
        updateCartUI();
    }

    // تحديث واجهة مستخدم السلة عند تحميل الصفحة
    if (document.getElementById('cart-items-container')) {
        updateCartUI();
    }
});