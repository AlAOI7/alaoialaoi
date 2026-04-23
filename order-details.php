<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// جلب تفاصيل الطلب
$order_sql = "SELECT 
    o.*,
    u.first_name,
    u.last_name,
    u.email,
    u.phone as user_phone,
    da.*,
    do.name as delivery_option_name,
    do.cost as delivery_option_cost,
    do.delivery_time_min,
    do.delivery_time_max,
    do.delivery_time_unit,
    dp.name as delivery_person_name,
    dp.phone as delivery_person_phone
FROM orders o
LEFT JOIN users u ON o.customer_id = u.id
LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
LEFT JOIN delivery_options do ON o.delivery_option_id = do.id
LEFT JOIN users dp ON o.delivery_person_id = dp.id
WHERE o.id = ? AND o.customer_id = ?";

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// جلب عناصر الطلب
$items_sql = "SELECT 
    oi.*
FROM order_items oi
WHERE oi.order_id = ?
ORDER BY oi.id";

$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// جلب معلومات التحويل البنكي إذا كانت موجودة
$bank_transfer_sql = "SELECT 
    obt.*,
    ba.bank_name,
    ba.account_number,
    ba.account_name
FROM order_bank_transfers obt
LEFT JOIN bank_accounts ba ON obt.bank_account_id = ba.id
WHERE obt.order_id = ?";

$bank_transfer_stmt = $conn->prepare($bank_transfer_sql);
$bank_transfer_stmt->bind_param("i", $order_id);
$bank_transfer_stmt->execute();
$bank_transfer_result = $bank_transfer_stmt->get_result();
$bank_transfer = $bank_transfer_result->fetch_assoc();

// جلب معلومات الكوبون إذا كان موجوداً
$coupon_sql = "SELECT 
    oc.coupon_code,
    oc.discount_amount,
    oc.original_total,
    oc.final_total
FROM order_coupons oc
WHERE oc.order_id = ?";

$coupon_stmt = $conn->prepare($coupon_sql);
$coupon_stmt->bind_param("i", $order_id);
$coupon_stmt->execute();
$coupon_result = $coupon_stmt->get_result();
$coupon = $coupon_result->fetch_assoc();

// وظائف مساعدة
function getOrderStatusText($status) {
    $statuses = [
        'pending' => 'قيد المراجعة',
        'approved' => 'تم الموافقة',
        'not_paid' => 'غير مدفوع',
        'in_delivery' => 'قيد التوصيل',
        'completed' => 'مكتمل'
    ];
    return $statuses[$status] ?? $status;
}

function getDeliveryStatusText($status) {
    $statuses = [
        'pending' => 'قيد التحضير',
        'preparing' => 'قيد التحضير',
        'shipped' => 'تم الشحن',
        'out_for_delivery' => 'قيد التوصيل',
        'delivered' => 'تم التوصيل',
        'failed' => 'فشل التوصيل'
    ];
    return $statuses[$status] ?? $status;
}

function getPaymentMethodText($method) {
    $methods = [
        'credit_card' => 'بطاقة ائتمان',
        'bank_transfer' => 'تحويل بنكي',
        'cash_on_delivery' => 'الدفع عند الاستلام'
    ];
    return $methods[$method] ?? $method;
}

function getOrderStatusClass($status) {
    $classes = [
        'pending' => 'bg-warning text-dark',
        'approved' => 'bg-info text-white',
        'not_paid' => 'bg-danger text-white',
        'in_delivery' => 'bg-primary text-white',
        'completed' => 'bg-success text-white'
    ];
    return $classes[$status] ?? 'bg-secondary text-white';
}

function getDeliveryStatusClass($status) {
    $classes = [
        'pending' => 'bg-warning text-dark',
        'preparing' => 'bg-info text-white',
        'shipped' => 'bg-primary text-white',
        'out_for_delivery' => 'bg-primary text-white',
        'delivered' => 'bg-success text-white',
        'failed' => 'bg-danger text-white'
    ];
    return $classes[$status] ?? 'bg-secondary text-white';
}

function formatAddress($address) {
    $parts = [];
    if (!empty($address['street'])) $parts[] = $address['street'];
    if (!empty($address['building'])) $parts[] = "مبنى " . $address['building'];
    if (!empty($address['floor'])) $parts[] = "طابق " . $address['floor'];
    if (!empty($address['apartment'])) $parts[] = "شقة " . $address['apartment'];
    if (!empty($address['district'])) $parts[] = $address['district'];
    if (!empty($address['city'])) $parts[] = $address['city'];
    if (!empty($address['region'])) $parts[] = $address['region'];
    if (!empty($address['country'])) $parts[] = $address['country'];
    if (!empty($address['postal_code'])) $parts[] = "الرمز البريدي: " . $address['postal_code'];
    
    return implode('، ', $parts);
}

// حساب المجموع الفرعي من العناصر
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['total_price'];
}

// حساب المجموع الكلي
$total = $order['total_amount'];
$delivery_cost = $order['delivery_cost'] ?? 0;
$discount = $coupon['discount_amount'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff3366;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding-bottom: 70px;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #a56cc1);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .icon-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .section-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            padding: 20px;
            border: none;
        }
        
        .product-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .product-icon {
            background-color: #f8f9fa;
            border-radius: 10px;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 2rem;
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .order-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .order-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e0e0e0;
        }
        
        .timeline-step {
            position: relative;
            margin-bottom: 20px;
            padding-left: 20px;
        }
        
        .timeline-step::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #ddd;
            border: 3px solid white;
        }
        
        .timeline-step.active::before {
            background-color: var(--primary-color);
        }
        
        .timeline-step.completed::before {
            background-color: #28a745;
        }
        
        .bottom-tab-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #777;
            font-size: 0.8rem;
            transition: all 0.3s;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item i {
            font-size: 1.2rem;
            margin-bottom: 3px;
        }
        
        .info-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-row {
            font-size: 1.1rem;
            font-weight: bold;
            border-top: 2px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="orders.php" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
                <h5 class="mb-0 fw-bold">تفاصيل الطلب</h5>
                <div>
                    <button class="icon-btn" onclick="window.print()">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container py-4" style="margin-top: 80px; margin-bottom: 70px;">
        
        <!-- معلومات الطلب الأساسية -->
        <div class="section-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">الطلب #<?php echo htmlspecialchars($order['invoice_number']); ?></h5>
                <span class="status-badge <?php echo getOrderStatusClass($order['status']); ?>">
                    <?php echo getOrderStatusText($order['status']); ?>
                </span>
            </div>
            
            <div class="info-row">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">تاريخ الطلب:</small>
                        <p class="mb-0 fw-bold"><?php echo date('Y/m/d', strtotime($order['order_date'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">تاريخ التوصيل المتوقع:</small>
                        <p class="mb-0 fw-bold">
                            <?php echo $order['estimated_delivery'] ? date('Y/m/d', strtotime($order['estimated_delivery'])) : 'يتم تحديده لاحقاً'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- تتبع حالة الطلب -->
        <div class="section-card">
            <h5 class="fw-bold mb-3">تتبع حالة الطلب</h5>
            <div class="order-timeline">
                <?php
                $timeline_steps = [
                    ['status' => 'pending', 'label' => 'تم استلام الطلب', 'date' => $order['created_at']],
                    ['status' => 'approved', 'label' => 'تم الموافقة على الطلب', 'date' => null],
                    ['status' => 'preparing', 'label' => 'جاري تحضير الطلب', 'date' => null],
                    ['status' => 'shipped', 'label' => 'تم شحن الطلب', 'date' => null],
                    ['status' => 'delivered', 'label' => 'تم تسليم الطلب', 'date' => $order['delivered_at']]
                ];
                
                $current_status = $order['delivery_status'] ?: $order['status'];
                
                foreach ($timeline_steps as $step):
                    $is_active = false;
                    $is_completed = false;
                    
                    // تحديد حالة الخطوة
                    if ($step['status'] == 'pending') {
                        $is_completed = true;
                    } elseif ($current_status == $step['status']) {
                        $is_active = true;
                    } elseif (array_search($current_status, array_column($timeline_steps, 'status')) >= 
                             array_search($step['status'], array_column($timeline_steps, 'status'))) {
                        $is_completed = true;
                    }
                ?>
                <div class="timeline-step <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_completed ? 'completed' : ''; ?>">
                    <h6 class="fw-bold mb-1"><?php echo $step['label']; ?></h6>
                    <?php if ($step['date']): ?>
                        <p class="small text-muted mb-0">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?php echo date('Y/m/d H:i', strtotime($step['date'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($order['delivery_person_name']): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2"><i class="fas fa-user-tie me-2"></i>معلومات مندوب التوصيل</h6>
                    <p class="mb-1"><strong>الاسم:</strong> <?php echo htmlspecialchars($order['delivery_person_name']); ?></p>
                    <?php if ($order['delivery_person_phone']): ?>
                        <p class="mb-0"><strong>رقم الهاتف:</strong> <a href="tel:<?php echo $order['delivery_person_phone']; ?>"><?php echo htmlspecialchars($order['delivery_person_phone']); ?></a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- المنتجات -->
        <div class="section-card">
            <h5 class="fw-bold mb-3">منتجات الطلب</h5>
            <?php foreach ($order_items as $item): ?>
                <div class="product-item">
                    <div class="row align-items-center">
                        <div class="col-3">
                            <div class="product-icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                            <?php if (!empty($item['size'])): ?>
                                <small class="text-muted">المقاس: <?php echo htmlspecialchars($item['size']); ?></small>
                            <?php endif; ?>
                            <?php if (!empty($item['color'])): ?>
                                <small class="text-muted">اللون: <?php echo htmlspecialchars($item['color']); ?></small>
                            <?php endif; ?>
                            <p class="mb-0 small">الكمية: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="col-3 text-end">
                            <p class="fw-bold mb-0"><?php echo number_format($item['total_price'], 2); ?> ر.س</p>
                            <small class="text-muted"><?php echo number_format($item['unit_price'], 2); ?> ر.س للقطعة</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ملخص الدفع -->
        <div class="section-card">
            <h5 class="fw-bold mb-3">ملخص الدفع</h5>
            
            <div class="price-row">
                <span>المجموع الفرعي:</span>
                <span class="fw-bold"><?php echo number_format($subtotal, 2); ?> ر.س</span>
            </div>
            
            <?php if ($discount > 0): ?>
                <div class="price-row text-success">
                    <span>الخصم:</span>
                    <span class="fw-bold">- <?php echo number_format($discount, 2); ?> ر.س</span>
                </div>
            <?php endif; ?>
            
            <div class="price-row">
                <span>تكاليف الشحن:</span>
                <span class="fw-bold"><?php echo number_format($delivery_cost, 2); ?> ر.س</span>
            </div>
            
            <div class="price-row total-row">
                <span>المجموع الكلي:</span>
                <span class="fw-bold text-danger"><?php echo number_format($total, 2); ?> ر.س</span>
            </div>
            
            <?php if ($coupon): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-1"><i class="fas fa-tag me-2"></i>كود الخصم المستخدم</h6>
                    <p class="mb-0"><strong>الكود:</strong> <?php echo htmlspecialchars($coupon['coupon_code']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- معلومات الدفع والتوصيل -->
        <div class="section-card">
            <h5 class="fw-bold mb-3">معلومات الدفع والتوصيل</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold mb-2">طريقة الدفع</h6>
                    <p class="mb-1"><strong>النوع:</strong> <?php echo getPaymentMethodText($order['payment_method']); ?></p>
                    <p class="mb-0"><strong>الحالة:</strong> 
                        <span class="badge <?php echo $order['status'] == 'not_paid' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                            <?php echo $order['status'] == 'not_paid' ? 'غير مدفوع' : 'مدفوع'; ?>
                        </span>
                    </p>
                    
                    <?php if ($bank_transfer && $order['payment_method'] == 'bank_transfer'): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2">معلومات التحويل البنكي</h6>
                            <p class="mb-1"><strong>اسم المحول:</strong> <?php echo htmlspecialchars($bank_transfer['transferee_name']); ?></p>
                            <p class="mb-1"><strong>تاريخ التحويل:</strong> <?php echo date('Y/m/d', strtotime($bank_transfer['transfer_date'])); ?></p>
                            <p class="mb-1"><strong>المبلغ:</strong> <?php echo number_format($bank_transfer['transfer_amount'], 2); ?> ر.س</p>
                            <p class="mb-0"><strong>الحالة:</strong> 
                                <span class="badge <?php echo $bank_transfer['receipt_verified'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo $bank_transfer['receipt_verified'] ? 'تم التحقق' : 'قيد المراجعة'; ?>
                                </span>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold mb-2">معلومات التوصيل</h6>
                    <p class="mb-1"><strong>طريقة التوصيل:</strong> 
                        <?php echo $order['delivery_method'] == 'fast_delivery' ? 'توصيل سريع' : 'توصيل عادي'; ?>
                    </p>
                    <?php if ($order['delivery_option_name']): ?>
                        <p class="mb-1"><strong>خيار التوصيل:</strong> <?php echo htmlspecialchars($order['delivery_option_name']); ?></p>
                    <?php endif; ?>
                    <p class="mb-0"><strong>تكلفة التوصيل:</strong> <?php echo number_format($delivery_cost, 2); ?> ر.س</p>
                    
                    <?php if ($order['delivery_time_slot']): ?>
                        <p class="mb-0"><strong>وقت التوصيل:</strong> <?php echo htmlspecialchars($order['delivery_time_slot']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($order['full_name']): ?>
                <div class="mt-3">
                    <h6 class="fw-bold mb-2">عنوان التوصيل</h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-1"><strong>الاسم:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p class="mb-1"><strong>الهاتف:</strong> <a href="tel:<?php echo $order['phone']; ?>"><?php echo htmlspecialchars($order['phone']); ?></a></p>
                        <?php if ($order['secondary_phone']): ?>
                            <p class="mb-1"><strong>هاتف آخر:</strong> <a href="tel:<?php echo $order['secondary_phone']; ?>"><?php echo htmlspecialchars($order['secondary_phone']); ?></a></p>
                        <?php endif; ?>
                        <p class="mb-0"><strong>العنوان:</strong> <?php echo formatAddress($order); ?></p>
                        <?php if ($order['nearest_landmark']): ?>
                            <p class="mb-0"><strong>أقرب معلم:</strong> <?php echo htmlspecialchars($order['nearest_landmark']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($order['delivery_notes'])): ?>
                <div class="mt-3">
                    <h6 class="fw-bold mb-2">ملاحظات التوصيل</h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['delivery_notes'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="section-card text-center">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php if ($order['status'] == 'not_paid'): ?>
                    <a href="payment.php?order_id=<?php echo $order_id; ?>" class="btn btn-danger rounded-pill px-4">
                        <i class="fas fa-credit-card me-2"></i>إتمام الدفع
                    </a>
                <?php endif; ?>
                
                <?php if ($order['delivery_status'] == 'shipped' || $order['delivery_status'] == 'out_for_delivery'): ?>
                    <a href="track_order.php?id=<?php echo $order_id; ?>" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-truck me-2"></i>تتبع الشحنة
                    </a>
                <?php endif; ?>
                
                <a href="invoice.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-danger rounded-pill px-4">
                    <i class="fas fa-file-invoice me-2"></i>عرض الفاتورة
                </a>
                
                <?php if ($order['status'] == 'completed'): ?>
                    <a href="reorder.php?order_id=<?php echo $order_id; ?>" class="btn btn-success rounded-pill px-4">
                        <i class="fas fa-redo me-2"></i>إعادة الطلب
                    </a>
                    
                    <a href="return.php?order_id=<?php echo $order_id; ?>" class="btn btn-warning rounded-pill px-4">
                        <i class="fas fa-exchange-alt me-2"></i>طلب إرجاع
                    </a>
                <?php endif; ?>
            </div>
            
            <p class="text-muted mt-3 mb-0">هل لديك مشكلة في الطلب؟</p>
            <a href="contact.php?order=<?php echo $order_id; ?>" class="btn btn-outline-secondary rounded-pill mt-2">
                <i class="fas fa-headset me-2"></i>تواصل مع الدعم الفني
            </a>
        </div>

    </main>

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
        <a href="order.php" class="tab-item active">
            <i class="fas fa-list-alt"></i>
            <span>الطلبات</span>
        </a>
        <a href="profile.php" class="tab-item">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // طباعة تفاصيل الطلب
            function printOrderDetails() {
                window.print();
            }
            
            // تحديث تتبع الطلب كل 30 ثانية
            function updateOrderTracking() {
                $.ajax({
                    url: 'ajax/update_order_status.php',
                    method: 'POST',
                    data: { order_id: <?php echo $order_id; ?> },
                    success: function(response) {
                        if (response.success) {
                            // تحديث حالة الطلب إذا تغيرت
                            if (response.new_status) {
                                location.reload();
                            }
                        }
                    }
                });
            }
            
            // تحديث حالة الطلب كل 30 ثانية
            setInterval(updateOrderTracking, 30000);
            
            // نسخ رقم الطلب
            $('.copy-order-number').click(function() {
                const orderNumber = $('#order-id').text();
                navigator.clipboard.writeText(orderNumber).then(function() {
                    alert('تم نسخ رقم الطلب: ' + orderNumber);
                });
            });
        });
    </script>
</body>
</html>