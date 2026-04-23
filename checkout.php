<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول - مرن للزوار والمسجلين
$is_logged_in = isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// التحقق من السلة - للمسجلين والزوار
$cart_items = [];
$cart_empty = true;

if ($is_logged_in) {
    // للمستخدمين المسجلين: جلب من قاعدة البيانات
    $cart_sql = "SELECT c.*, p.name, p.selling_price, p.old_price,
                        COALESCE(pi.image_path, 'img/default.jpg') as image
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    while ($item = $cart_result->fetch_assoc()) {
        $cart_items[] = [
            'id' => $item['product_id'],
            'name' => $item['name'],
            'selling_price' => $item['selling_price'],
            'old_price' => $item['old_price'] ?? 0,
            'quantity' => $item['quantity'],
            'image' => $item['image']
        ];
    }
    $cart_empty = empty($cart_items);
} else {
    // للزوار: استخدام سلة الجلسة
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart_items = $_SESSION['cart'];
        $cart_empty = false;
    }
}

// إعادة التوجيه إذا كانت السلة فارغة
if ($cart_empty) {
    header('Location: cart.php?msg=empty');
    exit;
}

// جلب بيانات العميل والعنوان الافتراضي
$user_sql = "SELECT u.*, da.* 
             FROM users u 
             LEFT JOIN delivery_addresses da ON u.id = da.user_id AND da.is_default = 1
             WHERE u.id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// حساب المجاميع
$cart_items = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart_items as $item) {
    if (isset($item['selling_price']) && isset($item['quantity'])) {
        $subtotal += $item['selling_price'] * $item['quantity'];
    }
}
$shipping_cost = 15; // قيمة ثابتة مؤقتاً
$total = $subtotal + $shipping_cost;

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الطلب | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .checkout-title {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
            font-weight: 800;
        }
        .card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: white;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .card-body-custom {
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #eee;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #555;
        }
        .order-summary-item.total {
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: 800;
            font-size: 1.2rem;
            color: #333;
        }
        .btn-checkout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            width: 100%;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .coupon-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .product-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            margin-left: 15px;
        }
        .summary-product-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .payment-option {
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        .payment-option:hover, .payment-option.active {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .payment-option input {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="checkout-container">
        <h2 class="checkout-title">📦 إتمام الطلب</h2>
        
        <form id="checkoutForm" onsubmit="processOrder(event)">
            <div class="row">
                <!-- قسم بيانات التوصيل والدفع -->
                <div class="col-lg-8">
                    <!-- العنوان -->
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="fas fa-map-marker-alt me-2"></i> عنوان التوصيل
                        </div>
                        <div class="card-body-custom">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الاسم الكامل</label>
                                    <input type="text" class="form-control" name="full_name" required 
                                           value="<?php echo htmlspecialchars($user_data['full_name'] ?? $user_data['name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">رقم الهاتف</label>
                                    <input type="tel" class="form-control" name="phone" required
                                           value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">المدينة</label>
                                    <input type="text" class="form-control" name="city" required
                                           value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الحي / المنطقة</label>
                                    <input type="text" class="form-control" name="district" required
                                           value="<?php echo htmlspecialchars($user_data['district'] ?? ''); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">العنوان التفصيلي (الشارع، رقم المبنى)</label>
                                    <input type="text" class="form-control" name="street" required
                                           value="<?php echo htmlspecialchars($user_data['street'] ?? ''); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">ملاحظات التوصيل (اختياري)</label>
                                    <textarea class="form-control" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                            <!-- طريقة الدفع -->
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <i class="fas fa-credit-card me-2"></i> طريقة الدفع
                                </div>
                                <div class="card-body-custom">
                                    <label class="payment-option active" onclick="selectPayment(this, 'cod')">
                                        <input type="radio" name="payment_method" value="cod" checked>
                                        <div>
                                            <h6 class="mb-0">الدفع عند الاستلام</h6>
                                            <small class="text-muted">ادفع نقداً عند استلام طلبك</small>
                                        </div>
                                    </label>
                                    
                                    <label class="payment-option" onclick="selectPayment(this, 'bank_transfer')">
                                        <input type="radio" name="payment_method" value="bank_transfer">
                                        <div>
                                            <h6 class="mb-0">تحويل بنكي</h6>
                                            <small class="text-muted">قم بالتحويل وإرفاق الإيصال</small>
                                        </div>
                                    </label>

                                    <!-- تفاصيل التحويل البنكي -->
                                    <div id="bank-transfer-details" class="d-none mt-3 p-3 bg-light rounded">
                                        <h6 class="fw-bold mb-3">حساباتنا البنكية:</h6>
                                        
                                        <div class="card p-3 mb-3 border-0 shadow-sm">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-university fa-lg text-primary me-2"></i>
                                                    <strong>مصرف الراجحي</strong>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded border">
                                                <span class="font-monospace ms-2 text-muted">SA20800001234567890123456</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyText('SA20800001234567890123456', this)">
                                                    <i class="far fa-copy"></i> نسخ
                                                </button>
                                            </div>
                                            <p class="mb-0 mt-2 small text-muted"><strong>الاسم:</strong> مؤسسة Be Pretty</p>
                                        </div>

                                        <div class="card p-3 mb-4 border-0 shadow-sm">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-university fa-lg text-success me-2"></i>
                                                    <strong>البنك الأهلي</strong>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded border">
                                                <span class="font-monospace ms-2 text-muted">SA98100009876543210987654</span>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="copyText('SA98100009876543210987654', this)">
                                                    <i class="far fa-copy"></i> نسخ
                                                </button>
                                            </div>
                                            <p class="mb-0 mt-2 small text-muted"><strong>الاسم:</strong> مؤسسة Be Pretty</p>
                                        </div>

                                        <h6 class="fw-bold mb-3">بيانات الحوالة:</h6>
                                        <div class="mb-3">
                                            <label class="form-label small">اسم المحول <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="sender_name" id="sender_name">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small">المبلغ المحول <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="transfer_amount" id="transfer_amount">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small">إرفاق الإيصال</label>
                                            <input type="file" class="form-control" name="payment_proof" id="payment_proof" accept="image/*,application/pdf">
                                        </div>
                                    </div>

                                    <label class="payment-option" onclick="selectPayment(this, 'reserve')">
                                        <input type="radio" name="payment_method" value="reserve">
                                        <div>
                                            <h6 class="mb-0">حجز المنتج (ادفع لاحقاً)</h6>
                                            <small class="text-muted">سيتم حجز المنتجات لمدة 24 ساعة</small>
                                        </div>
                                    </label>
                                    
                                    <!-- تفاصيل الحجز -->
                                    <div id="reserve-details" class="d-none mt-3 p-3 bg-light rounded">
                                        <p class="mb-0 text-warning"><i class="fas fa-clock"></i> سيتم حجز المنتجات لك لمدة 24 ساعة. يرجى التواصل معنا أو إتمام الدفع خلال هذه الفترة لتأكيد الطلب.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ملخص الطلب -->
                        <div class="col-lg-4">
                            <div class="card-custom">
                                <div class="card-header-custom">
                                    <i class="fas fa-shopping-bag me-2"></i> ملخص الطلب
                                </div>
                                <div class="card-body-custom">
                                    <div class="products-summary mb-4" style="max-height: 300px; overflow-y: auto;">
                                        <?php foreach ($cart_items as $item): ?>
                                            <div class="summary-product-item">
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" class="product-thumbnail">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0" style="font-size: 0.9rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted">الكمية: <?php echo $item['quantity']; ?></small>
                                                </div>
                                                <div class="fw-bold">
                                                    <?php echo number_format($item['selling_price'] * $item['quantity'], 2); ?> ر.س
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- القسيمة -->
                                    <div class="coupon-section">
                                        <label class="form-label small">كود الخصم</label>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-sm" id="coupon_code" placeholder="أدخل الكود">
                                            <button class="btn btn-primary btn-sm" type="button" onclick="applyCoupon()">تطبيق</button>
                                        </div>
                                        <div id="coupon-message" class="small"></div>
                                    </div>

                                    <div class="order-summary-item">
                                        <span>المجموع الفرعي</span>
                                        <span><?php echo number_format($subtotal, 2); ?> ر.س</span>
                                    </div>
                                    <div class="order-summary-item">
                                        <span>الشحن</span>
                                        <span><?php echo number_format($shipping_cost, 2); ?> ر.س</span>
                                    </div>
                                    <div class="order-summary-item text-success d-none" id="discount-row">
                                        <span>الخصم</span>
                                        <span>-<span id="discount-amount">0.00</span> ر.س</span>
                                    </div>
                                    <div class="order-summary-item total">
                                        <span>الإجمالي</span>
                                        <span class="text-primary"><span id="final-total"><?php echo number_format($total, 2); ?></span> ر.س</span>
                                    </div>

                                    <input type="hidden" name="coupon_code" id="applied_coupon_code">
                                    
                                    <button type="submit" class="btn-checkout mt-4" id="submitBtn">
                                        تأكيد الطلب <i class="fas fa-check-circle ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
    </div>

    <!-- تضمين ملفات الجافاسكريبت -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentTotal = <?php echo $total; ?>;
        
        function selectPayment(element, method) {
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            element.querySelector('input').checked = true;
            
            // إظهار التفاصيل المناسبة
            document.getElementById('bank-transfer-details').classList.add('d-none');
            document.getElementById('reserve-details').classList.add('d-none');
            
            if (method === 'bank_transfer') {
                document.getElementById('bank-transfer-details').classList.remove('d-none');
            } else if (method === 'reserve') {
                document.getElementById('reserve-details').classList.remove('d-none');
            }
        }

        function applyCoupon() {
            const code = document.getElementById('coupon_code').value;
            if (!code) return;

            const btn = document.querySelector('.coupon-section button');
            const msg = document.getElementById('coupon-message');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('code', code);
            formData.append('total', <?php echo $subtotal; ?>); // الخصم على المجموع الفرعي

            fetch('ajax/apply_coupon.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'تطبيق';

                if (data.success) {
                    msg.innerHTML = `<span class="text-success"><i class="fas fa-check"></i> ${data.message}</span>`;
                    
                    // تحديث الأرقام
                    const discount = parseFloat(data.discount_amount);
                    const newTotal = <?php echo $total; ?> - discount;
                    currentTotal = newTotal;
                    
                    document.getElementById('discount-row').classList.remove('d-none');
                    document.getElementById('discount-amount').textContent = discount.toFixed(2);
                    document.getElementById('final-total').textContent = newTotal.toFixed(2);
                    document.getElementById('applied_coupon_code').value = data.coupon_code;
                    document.getElementById('coupon_code').disabled = true;
                } else {
                    msg.innerHTML = `<span class="text-danger"><i class="fas fa-times"></i> ${data.message}</span>`;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'تطبيق';
                msg.innerHTML = '<span class="text-danger">حدث خطأ في الاتصال</span>';
                console.error(err);
            });
        }

        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
                btn.classList.remove('btn-outline-primary', 'btn-outline-success');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    if (btn.onclick.toString().includes('primary')) {
                       btn.classList.add('btn-outline-primary');
                    } else {
                       btn.classList.add('btn-outline-success');
                    }
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

        function processOrder(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i> جاري المعالجة...';

            const formData = new FormData(e.target);

            fetch('ajax/process_order.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                // التحقق من نوع المحتوى
                const contentType = res.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return res.json();
                } else {
                    return res.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('استجابة غير صالحة من الخادم');
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'invoice.php?order_id=' + data.order_id;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert(data.message || 'حدث خطأ أثناء معالجة الطلب');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('حدث خطأ في الاتصال بالخادم: ' + err.message);
                console.error(err);
            });
        }
    </script>
</body>
</html>