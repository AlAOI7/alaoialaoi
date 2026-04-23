<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب الطلبات الجارية (غير المكتملة)
$current_orders_sql = "SELECT 
    o.id, 
    o.invoice_number, 
    o.order_date, 
    o.total_amount, 
    o.status, 
    o.delivery_status,
    o.delivery_method,
    o.estimated_delivery,
    da.city as delivery_city,
    da.district as delivery_district,
    COUNT(oi.id) as items_count,
    oi.product_name as first_product_name
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
WHERE o.customer_id = ? 
AND o.status IN ('pending', 'approved', 'not_paid', 'in_delivery')
GROUP BY o.id
ORDER BY o.order_date DESC";

$current_stmt = $conn->prepare($current_orders_sql);
$current_stmt->bind_param("i", $user_id);
$current_stmt->execute();
$current_result = $current_stmt->get_result();
$current_orders = $current_result->fetch_all(MYSQLI_ASSOC);

// جلب الطلبات السابقة (المكتملة)
$history_orders_sql = "SELECT 
    o.id, 
    o.invoice_number, 
    o.order_date, 
    o.total_amount, 
    o.status, 
    o.delivery_status,
    o.delivery_method,
    o.delivered_at,
    da.city as delivery_city,
    da.district as delivery_district,
    COUNT(oi.id) as items_count,
    oi.product_name as first_product_name
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
WHERE o.customer_id = ? 
AND o.status = 'completed'
GROUP BY o.id
ORDER BY o.order_date DESC";

$history_stmt = $conn->prepare($history_orders_sql);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$history_orders = $history_result->fetch_all(MYSQLI_ASSOC);

// وظيفة لتحويل حالة الطلب إلى نص عربي
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

// وظيفة لتحويل حالة التوصيل إلى نص عربي
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

// وظيفة لتحويل لون حالة الطلب
function getOrderStatusClass($status, $delivery_status = null) {
    if ($status == 'completed') return 'badge-completed';
    if ($status == 'in_delivery') return 'badge-shipped';
    if ($status == 'approved') return 'badge-review';
    if ($status == 'not_paid') return 'badge-danger';
    if ($delivery_status == 'delivered') return 'badge-completed';
    if ($delivery_status == 'shipped' || $delivery_status == 'out_for_delivery') return 'badge-shipped';
    return 'badge-review';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff3366;
            --accent-color: #ff3366;
            --dark-color: #2c2c54;
            --light-color: #f7f7f7;
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
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
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
        
        .icon-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .header-icons {
            display: flex;
            gap: 10px;
        }
        
        .main-content {
            margin-top: 80px;
            margin-bottom: 20px;
        }
        
        .nav-pills .nav-link {
            color: #666;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            transition: all 0.3s;
            border: none;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        
        .order-card .card-body {
            padding: 20px;
        }
        
        .order-badge {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-review {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .order-actions .btn {
            font-size: 0.85rem;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #777;
            font-size: 0.8rem;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item i {
            font-size: 1.2rem;
            margin-bottom: 3px;
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
        
        footer {
            background: var(--dark-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 40px;
        }
        
        footer h5, footer h6 {
            color: white;
            margin-bottom: 15px;
        }
        
        footer ul {
            padding: 0;
            list-style: none;
        }
        
        footer ul li {
            margin-bottom: 8px;
        }
        
        footer a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        footer a:hover {
            color: var(--primary-color);
        }
        
        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-left: 10px;
        }
        
        .social-links a:hover {
            background: var(--primary-color);
        }
        
        .empty-orders {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-orders-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .delivery-info {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .items-count {
            background-color: #f0f0f0;
            color: #666;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        
        .product-icon {
            background-color: #f8f9fa;
            border-radius: 10px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-top">
            <a href="profile.php" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
            <h5 class="mb-0 fw-bold">طلباتي</h5>
            <div class="header-icons">
                <button class="icon-btn"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </header>

    <div class="main-content container-fluid py-4">
        <!-- تبويبات الطلبات -->
        <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="pills-current-tab" data-bs-toggle="pill" data-bs-target="#pills-current" type="button" role="tab">
                    طلبات جارية <span class="badge bg-light text-dark ms-1"><?php echo count($current_orders); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab">
                    طلبات سابقة <span class="badge bg-light text-dark ms-1"><?php echo count($history_orders); ?></span>
                </button>
            </li>
        </ul>

        <!-- محتوى التبويبات -->
        <div class="tab-content" id="pills-tabContent">
            <!-- تبويب الطلبات الجارية -->
            <div class="tab-pane fade show active" id="pills-current" role="tabpanel">
                <div class="d-flex flex-column gap-3">
                    <?php if (empty($current_orders)): ?>
                        <div id="no-current-orders" class="empty-orders">
                            <div class="empty-orders-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3 class="text-muted">لا توجد طلبات جارية</h3>
                            <p class="text-muted mb-4">لم تقم بإجراء أي طلبات بعد</p>
                            <a href="categories.php" class="btn btn-danger rounded-pill px-4">تصفح المنتجات</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($current_orders as $order): ?>
                            <div class="card order-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0">طلب #<?php echo htmlspecialchars($order['invoice_number']); ?></h6>
                                        <span class="order-badge <?php echo getOrderStatusClass($order['status'], $order['delivery_status']); ?>">
                                            <?php echo getOrderStatusText($order['status']); ?>
                                            <?php if ($order['delivery_status'] && $order['status'] != 'completed'): ?>
                                                <br><small><?php echo getDeliveryStatusText($order['delivery_status']); ?></small>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        تاريخ الطلب: <?php echo date('Y/m/d', strtotime($order['order_date'])); ?>
                                    </p>
                                    
                                    <?php if ($order['estimated_delivery']): ?>
                                        <p class="small text-muted mb-2">
                                            <i class="fas fa-truck me-1"></i>
                                            التوصيل المتوقع: <?php echo date('Y/m/d', strtotime($order['estimated_delivery'])); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['delivery_city']): ?>
                                        <p class="delivery-info">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($order['delivery_district']); ?> - <?php echo htmlspecialchars($order['delivery_city']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <hr class="my-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="product-icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-bold">
                                                <span class="items-count"><?php echo $order['items_count']; ?> منتج</span>
                                            </p>
                                            <p class="mb-0 small text-muted">
                                                <?php echo !empty($order['first_product_name']) ? htmlspecialchars($order['first_product_name']) : 'منتجات متنوعة'; ?>
                                            </p>
                                            <p class="mb-0 small text-muted">
                                                الإجمالي: <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                            </p>
                                        </div>
                                    </div>
                                    <div class="order-actions text-start mt-3">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                            <i class="fas fa-eye me-1"></i>عرض التفاصيل
                                        </a>
                                        
                                        <?php if ($order['delivery_status'] == 'shipped' || $order['delivery_status'] == 'out_for_delivery'): ?>
                                            <a href="track_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger rounded-pill fw-bold ms-2">
                                                <i class="fas fa-truck me-1"></i>تتبع الطلب
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($order['status'] == 'not_paid'): ?>
                                            <a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-warning rounded-pill fw-bold ms-2">
                                                <i class="fas fa-credit-card me-1"></i>إتمام الدفع
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- تبويب الطلبات السابقة -->
            <div class="tab-pane fade" id="pills-history" role="tabpanel">
                <div class="d-flex flex-column gap-3">
                    <?php if (empty($history_orders)): ?>
                        <div id="no-history-orders" class="empty-orders">
                            <div class="empty-orders-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3 class="text-muted">لا توجد طلبات سابقة</h3>
                            <p class="text-muted mb-4">لم تقم بإجراء أي طلبات بعد</p>
                            <a href="categories.php" class="btn btn-danger rounded-pill px-4">تصفح المنتجات</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($history_orders as $order): ?>
                            <div class="card order-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0">طلب #<?php echo htmlspecialchars($order['invoice_number']); ?></h6>
                                        <span class="order-badge badge-completed">
                                            <?php echo getOrderStatusText($order['status']); ?>
                                            <?php if ($order['delivered_at']): ?>
                                                <br><small>تم التوصيل: <?php echo date('Y/m/d', strtotime($order['delivered_at'])); ?></small>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        تاريخ الطلب: <?php echo date('Y/m/d', strtotime($order['order_date'])); ?>
                                    </p>
                                    
                                    <?php if ($order['delivery_city']): ?>
                                        <p class="delivery-info">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($order['delivery_district']); ?> - <?php echo htmlspecialchars($order['delivery_city']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <hr class="my-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="product-icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-bold">
                                                <span class="items-count"><?php echo $order['items_count']; ?> منتج</span>
                                            </p>
                                            <p class="mb-0 small text-muted">
                                                <?php echo !empty($order['first_product_name']) ? htmlspecialchars($order['first_product_name']) : 'منتجات متنوعة'; ?>
                                            </p>
                                            <p class="mb-0 small text-muted">
                                                الإجمالي: <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                            </p>
                                        </div>
                                    </div>
                                    <div class="order-actions text-start mt-3">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                            <i class="fas fa-eye me-1"></i>عرض التفاصيل
                                        </a>
                                        <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger rounded-pill fw-bold ms-2">
                                            <i class="fas fa-file-invoice me-1"></i>عرض الفاتورة
                                        </a>
                                        <a href="reorder.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-success rounded-pill fw-bold ms-2">
                                            <i class="fas fa-redo me-1"></i>إعادة الطلب
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light pt-5 pb-3">
        <div class="container">
            <div class="row">
                <!-- معلومات المتجر -->
                <div class="col-md-4 mb-4">
                    <h5>Be Pretty</h5>
                    <p>متجرك الأول لمستحضرات التجميل والعناية بالبشرة.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-snapchat"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
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
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Be Pretty. جميع الحقوق محفوظة.</p>
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
            // التحقق من وجود طلبات وعرض الرسالة المناسبة
            function checkEmptyOrders() {
                // الطلبات الجارية
                if ($('#pills-current .order-card').length === 0) {
                    $('#no-current-orders').removeClass('d-none');
                } else {
                    $('#no-current-orders').addClass('d-none');
                }
                
                // الطلبات السابقة
                if ($('#pills-history .order-card').length === 0) {
                    $('#no-history-orders').removeClass('d-none');
                } else {
                    $('#no-history-orders').addClass('d-none');
                }
            }
            
            // استدعاء الدالة عند التحميل
            checkEmptyOrders();
            
            // إضافة تأثيرات تفاعلية للبطاقات
            $('.order-card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                }
            );
            
            // تحديث حالة التبويب النشط في شريط التنقل السفلي
            $('.bottom-tab-bar .tab-item').click(function() {
                $('.bottom-tab-bar .tab-item').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>