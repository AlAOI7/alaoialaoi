document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            alert('تم إضافة المنتج إلى السلة!');
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const closeMenuBtn = document.getElementById('close-menu');
    const sidebar = document.getElementById('sidebar-menu');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.add('open');
    });

    closeMenuBtn.addEventListener('click', () => {
        sidebar.classList.remove('open');
    });

    // You can add more JavaScript logic here for other features
    // For example:
    // - Add to cart functionality
    // - Favorite button click
    // - Product detail popups
    // - Handling form submissions (search)
});
$(document).ready(function() {
    $('#menu-toggle').click(function() {
        $('#sidebar-menu').addClass('open');
    });

    $('#close-menu').click(function() {
        $('#sidebar-menu').removeClass('open');
    });

    // You can add more jQuery logic here for other features like
    // adding items to cart, showing product details, etc.
});
$(document).ready(function() {
    // Check if the favorites list is empty on page load
    function checkEmptyState() {
        if ($('.favorites-section .col').length === 0) {
            $('#empty-favorites-message').removeClass('d-none');
        } else {
            $('#empty-favorites-message').addClass('d-none');
        }
    }

    // Call on initial load
    checkEmptyState();

    // Handle removing from favorites
    $('.favorites-section').on('click', '.remove-fav-btn', function() {
        const productCard = $(this).closest('.col');
        productCard.fadeOut(300, function() {
            $(this).remove();
            checkEmptyState();
            alert('تمت إزالة المنتج من المفضلة.');
        });
    });

    // Handle adding to cart
    $('.favorites-section').on('click', '.add-to-cart-btn', function() {
        alert('تمت إضافة المنتج إلى السلة.');
    });
});
$(document).ready(function() {
    const shippingCost = 15; // تكلفة الشحن الثابتة

    // Function to update totals
    function updateTotals() {
        let subtotal = 0;
        $('.cart-item').each(function() {
            const price = parseFloat($(this).data('price'));
            const quantity = parseInt($(this).find('.quantity').text());
            const itemTotal = price * quantity;
            $(this).find('.item-total-price').text(itemTotal.toFixed(2));
            subtotal += itemTotal;
        });

        const total = subtotal + shippingCost;
        $('#subtotal').text(subtotal.toFixed(2));
        $('#total-price').text(total.toFixed(2));
    }

    // Initial calculation on page load
    updateTotals();

    // Handle quantity changes
    $('.cart-items-section').on('click', '.quantity-btn', function() {
        const action = $(this).data('action');
        const quantitySpan = $(this).siblings('.quantity');
        let quantity = parseInt(quantitySpan.text());

        if (action === 'plus') {
            quantity++;
        } else if (action === 'minus' && quantity > 1) {
            quantity--;
        }

        quantitySpan.text(quantity);
        updateTotals();
    });

    // Handle item removal
    $('.cart-items-section').on('click', '.remove-btn', function() {
        $(this).closest('.cart-item').fadeOut(300, function() {
            $(this).remove();
            updateTotals();
        });
    });

    // Handle checkout button click
    $('#checkout-btn').on('click', function() {
        alert('تمت عملية الدفع بنجاح! شكراً لك.');
    });
});
$(document).ready(function() {
    // Handle view toggle button click
    $('#view-toggle').on('click', function() {
        const gridView = $('#grid-view');
        const listView = $('#list-view');
        const icon = $(this).find('i');

        if (gridView.hasClass('d-none')) {
            // Switch to Grid View
            gridView.removeClass('d-none');
            listView.addClass('d-none');
            icon.removeClass('fa-list').addClass('fa-th-large');
        } else {
            // Switch to List View
            gridView.addClass('d-none');
            listView.removeClass('d-none');
            icon.removeClass('fa-th-large').addClass('fa-list');
        }
    });
$(document).ready(function() {
    // This is a placeholder for dynamic data.
    // In a real application, this data would be fetched from a backend.
    const product = {
        id: '123',
        name: 'منتج العناية بالبشرة',
        price: '150 ر.س',
        description: 'هذا المنتج هو منتج مثالي للعناية بالبشرة، يمنحك ترطيباً عميقاً ونضارة لا مثيل لها.',
        image: 'product1.jpg'
    };

    // Populate product details on page load
    $('#product-title-header').text(product.name);
    $('#product-main-image').attr('src', product.image);
    $('#product-name').text(product.name);
    $('#product-price').text(product.price);
    $('#product-description').text(product.description);

    // Handle add comment button click
    $('#add-comment-btn').on('click', function() {
        const commentText = $('#comment-input').val().trim();
        if (commentText !== '') {
            const newCommentHtml = `
                <div class="d-flex align-items-start mb-3 border-bottom pb-3">
                    <img src="user-placeholder.png" class="rounded-circle me-3" style="width: 40px; height: 40px;">
                    <div>
                        <h6 class="fw-bold mb-1">مستخدم جديد</h6>
                        <p class="small mb-0">${commentText}</p>
                    </div>
                </div>
            `;
            $('#comments-container').prepend(newCommentHtml);
            $('#comment-input').val('');
        }
    });

    // Handle add to cart button click
    $('.add-to-cart-btn').on('click', function() {
        alert('تمت إضافة المنتج إلى السلة.');
    });

    // Handle add to favorites button click
    $('.fa-heart').on('click', function() {
        alert('تمت إضافة المنتج إلى المفضلة.');
    });
});
$(document).ready(function() {
    // 1. Data simulation (This would be fetched from a backend)
    const orderDetails = {
        items: [
            { id: 1, name: 'منتج العناية بالبشرة', quantity: 1, price: 150, image: 'product1.jpg' },
            { id: 2, name: 'مجموعة المكياج', quantity: 1, price: 250, image: 'product2.jpg' }
        ],
        subtotal: 400,
        shipping: 15,
        discount: 0
    };

    const bankDetails = {
        'yemeni-bank': {
            qrCode: 'qr-yemeni.png',
            accountNumber: '1234567890',
            currency: 'ر.ي'
        },
        'saudi-bank': {
            qrCode: 'qr-saudi.png',
            accountNumber: '9876543210',
            currency: 'ر.س'
        },
        'dolar-bank': {
            qrCode: 'qr-dolar.png',
            accountNumber: '1122334455',
            currency: '$'
        }
    };

    // 2. Function to update UI with order details
    function renderOrderSummary() {
        let itemsHtml = '';
        orderDetails.items.forEach(item => {
            itemsHtml += `
                <div class="d-flex align-items-center border-bottom pb-2">
                    <img src="${item.image}" class="img-fluid rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <p class="mb-0 fw-bold">${item.name} <small class="text-muted">(x${item.quantity})</small></p>
                    </div>
                    <span class="fw-bold">${item.price * item.quantity} ر.س</span>
                </div>
            `;
        });
        $('#order-items').html(itemsHtml);
        
        // Update totals
        updateTotals();
    }

    // 3. Function to update totals
    function updateTotals() {
        const total = orderDetails.subtotal + orderDetails.shipping - orderDetails.discount;
        $('#subtotal').text(orderDetails.subtotal);
        $('#discount').text(orderDetails.discount);
        $('#shipping').text(orderDetails.shipping);
        $('#total-price').text(total);
    }

    // 4. Handle payment method selection
    $('.payment-option').on('click', function() {
        $('.payment-option').removeClass('active');
        $(this).addClass('active');

        const method = $(this).data('method');
        $('#cash-form, #bank-form').addClass('d-none');
        if (method === 'cash') {
            $('#cash-form').removeClass('d-none');
        } else if (method === 'bank') {
            $('#bank-form').removeClass('d-none');
            // Trigger bank select change to show initial bank info
            $('#bank-select').trigger('change');
        } else {
            // For future methods like credit card
            alert('طريقة الدفع هذه ستكون متاحة قريباً.');
        }
    });

    // 5. Handle bank selection change
    $('#bank-select').on('change', function() {
        const selectedBank = $(this).val();
        const details = bankDetails[selectedBank];
        const bankHtml = `
            <img src="${details.qrCode}" class="img-fluid mb-3" style="width: 150px; height: 150px;">
            <p class="mb-1 fw-bold">رقم الحساب: <span id="account-number">${details.accountNumber}</span></p>
            <p class="mb-0 text-muted">العملة: ${details.currency}</p>
            <button class="btn btn-sm btn-outline-secondary mt-2 copy-btn" data-clipboard-text="${details.accountNumber}">
                <i class="fas fa-copy me-2"></i>نسخ رقم الحساب
            </button>
        `;
        $('#bank-info').html(bankHtml);
    });

    // 6. Handle coupon code application
    $('#apply-coupon-btn').on('click', function() {
        const couponCode = $('#coupon-code').val().trim();
        if (couponCode === 'DISCOUNT20') {
            orderDetails.discount = 20;
            updateTotals();
            alert('تم تطبيق قسيمة الخصم بنجاح! تم خصم 20 ر.س.');
        } else {
            orderDetails.discount = 0;
            updateTotals();
            alert('رمز القسيمة غير صالح.');
        }
    });

    // 7. Handle form submission
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();
        // Here you would send the data to a backend server
        alert('تم إرسال طلبك بنجاح! سيتم مراجعته.');
    });

    // 8. Handle copy button click
    $(document).on('click', '.copy-btn', function() {
        const textToCopy = $(this).data('clipboard-text');
        navigator.clipboard.writeText(textToCopy).then(() => {
            alert('تم نسخ رقم الحساب بنجاح.');
        });
    });

    // Initial render
    renderOrderSummary();
});

