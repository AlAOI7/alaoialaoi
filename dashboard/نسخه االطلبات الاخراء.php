<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الطلبات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* الشريط الجانبي */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
        }

        .logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .logo h2 {
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu {
            list-style: none;
        }

        .menu li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu li a:hover,
        .menu li a.active {
            background: rgba(255,255,255,0.1);
            border-right: 3px solid white;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .page-title {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h2 {
            color: #2c3e50;
            font-size: 28px;
        }

        .date {
            background: white;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* شريط الإجراءات */
        .page-actions {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* فلتر الطلبات */
        .orders-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* جدول الطلبات */
        .orders-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .orders-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: right;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .orders-table tr:hover {
            background: #f8f9fa;
        }

        /* معلومات العميل */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .customer-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .customer-details p {
            color: #666;
            font-size: 12px;
        }

        /* حالات الطلب */
        .order-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .order-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .order-status.approved {
            background: #d1ecf1;
            color: #0c5460;
        }

        .order-status.not_paid {
            background: #f8d7da;
            color: #721c24;
        }

        .order-status.in_delivery {
            background: #d4edda;
            color: #155724;
        }

        .order-status.completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* أزرار الإجراءات */
        .order-actions {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .action-btn.view {
            background: #3498db;
            color: white;
        }

        .action-btn.print {
            background: #95a5a6;
            color: white;
        }

        .action-btn.contact {
            background: #27ae60;
            color: white;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        /* النوافذ المنبثقة */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .modal-header h3 {
            color: #2c3e50;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* التنسيقات الخاصة للفاتورة */
        .invoice-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .customer-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .customer-card, .payment-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            color: #2c3e50;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .products-table th,
        .products-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        .products-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-image {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .invoice-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .status-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .status-btn {
            padding: 10px 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .status-btn:hover {
            background: #f8f9fa;
            transform: translateX(-5px);
        }

        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .total-row {
            display: flex;
            justify-content: between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
            border-bottom: none;
        }

        /* التنسيقات للتواصل */
        .contact-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        .contact-option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .contact-option:hover {
            background: #f8f9fa;
            transform: translateX(-5px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .contact-icon.phone {
            background: #27ae60;
        }

        .contact-icon.whatsapp {
            background: #25d366;
        }

        .contact-icon.email {
            background: #3498db;
        }

        /* التبويب */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 20px;
        }

        .pagination button {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination button.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* رسائل التنبيه */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- الشريط الجانبي -->
        <div class="sidebar">
            <div class="logo">
                <h2><i class="fas fa-shopping-cart"></i> نظام الطلبات</h2>
            </div>
            <ul class="menu">
                <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
                <li><a href="#"><i class="fas fa-boxes"></i> المنتجات</a></li>
                <li><a href="#"><i class="fas fa-users"></i> العملاء</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> التقارير</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> الإعدادات</a></li>
            </ul>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <div class="page-content">
                <div class="page-title">
                    <h2>إدارة الطلبات</h2>
                    <div class="date"><?php echo date('Y-m-d'); ?></div>
                </div>

                <!-- عرض رسائل التنبيه -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                <?php endif; ?>

                <!-- شريط البحث والإجراءات -->
                <form method="POST" id="filterForm">
                    <input type="hidden" name="action" value="filter">
                    <div class="page-actions">
                        <div class="search-box">
                            <input type="text" id="searchInput" name="search" placeholder="ابحث برقم الفاتورة أو اسم العميل..." value="<?php echo $filters['search'] ?? ''; ?>">
                            <i class="fas fa-search" id="searchButton"></i>
                        </div>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary">
                                <i class="fas fa-filter"></i>
                                تصفية
                            </button>
                            <button type="button" class="btn btn-primary" id="printAllButton">
                                <i class="fas fa-print"></i>
                                طباعة التقارير
                            </button>
                        </div>
                    </div>
                </form>

                <!-- فلتر الطلبات -->
                <div class="orders-filter">
                    <button class="filter-btn <?php echo ($filters['status'] ?? 'all') === 'all' ? 'active' : ''; ?>" data-filter="all">الكل</button>
                    <button class="filter-btn <?php echo ($filters['status'] ?? '') === 'pending' ? 'active' : ''; ?>" data-filter="pending">قيد المراجعة</button>
                    <button class="filter-btn <?php echo ($filters['status'] ?? '') === 'approved' ? 'active' : ''; ?>" data-filter="approved">تمت الموافقة</button>
                    <button class="filter-btn <?php echo ($filters['status'] ?? '') === 'not_paid' ? 'active' : ''; ?>" data-filter="not_paid">لم يتم الدفع</button>
                    <button class="filter-btn <?php echo ($filters['status'] ?? '') === 'in_delivery' ? 'active' : ''; ?>" data-filter="in_delivery">قيد التوصيل</button>
                    <button class="filter-btn <?php echo ($filters['status'] ?? '') === 'completed' ? 'active' : ''; ?>" data-filter="completed">تم التسليم</button>
                </div>

                <!-- جدول الطلبات -->
                <div class="orders-container">
                    <div class="orders-header">
                        <h3>قائمة الطلبات</h3>
                        <div class="view-toggle">
                            <span>عرض 1-<?php echo count($orders); ?> من <?php echo count($orders); ?></span>
                        </div>
                    </div>

                    <table class="orders-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>طريقة التوصيل</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <?php foreach($orders as $order): ?>
                            <tr data-order-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status']; ?>">
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar"><?php echo mb_substr($order['customer_name'], 0, 1); ?></div>
                                        <div class="customer-details">
                                            <h4 class="customer-name"><?php echo $order['customer_name']; ?></h4>
                                            <p><?php echo $order['customer_email']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="order-id">#<?php echo $order['invoice_number']; ?></div>
                                </td>
                                <td><?php echo $order['order_date']; ?></td>
                                <td>
                                    <div class="order-total">$<?php echo number_format($order['total_amount'], 2); ?></div>
                                </td>
                                <td><?php echo getPaymentMethodText($order['payment_method']); ?></td>
                                <td><?php echo getDeliveryMethodText($order['delivery_method']); ?></td>
                                <td>
                                    <span class="order-status <?php echo $order['status']; ?>">
                                        <?php echo getStatusText($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="order-actions">
                                        <button class="action-btn view" data-order="<?php echo $order['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn print" data-order="<?php echo $order['id']; ?>">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button class="action-btn contact" data-customer="<?php echo $order['customer_id']; ?>">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="pagination">
                        <button class="active">1</button>
                        <button>2</button>
                        <button>3</button>
                        <button>4</button>
                        <button>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة عرض الفاتورة -->
    <div class="modal-overlay" id="invoiceModal">
        <div class="modal">
            <div class="modal-header">
                <h3>فاتورة الطلب</h3>
                <button class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="invoiceModalBody">
                <!-- سيتم تعبئة محتوى الفاتورة من خلال JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeInvoice">إغلاق</button>
                <button class="btn btn-primary" id="printInvoiceButton">
                    <i class="fas fa-print"></i>
                    طباعة الفاتورة
                </button>
            </div>
        </div>
    </div>

    <!-- نافذة التواصل مع العميل -->
    <div class="modal-overlay" id="contactModal">
        <div class="modal contact-modal">
            <div class="modal-header">
                <h3 id="contactModalTitle">التواصل مع العميل</h3>
                <button class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="contactModalBody">
                <!-- سيتم تعبئة محتوى التواصل من خلال JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeContact">إغلاق</button>
            </div>
        </div>
    </div>

    <script>
        // إعداد مستمعي الأحداث
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
        });

        // إعداد مستمعي الأحداث
        function setupEventListeners() {
            // تصفية الطلبات
            document.querySelectorAll('.filter-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // تحديث الأزرار النشطة
                    document.querySelectorAll('.filter-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // إرسال النموذج مع الفلتر الجديد
                    document.querySelector('input[name="status"]')?.remove();
                    
                    const form = document.getElementById('filterForm');
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = filter;
                    form.appendChild(statusInput);
                    
                    form.submit();
                });
            });

            // البحث عن الطلبات
            document.getElementById('searchButton').addEventListener('click', function() {
                document.getElementById('filterForm').submit();
            });

            // فتح نافذة الفاتورة
            document.addEventListener('click', function(event) {
                if (event.target.closest('.action-btn.view')) {
                    const orderId = event.target.closest('.action-btn.view').getAttribute('data-order');
                    openInvoiceModal(orderId);
                }
                
                if (event.target.closest('.order-id')) {
                    const orderId = event.target.closest('tr').getAttribute('data-order-id');
                    openInvoiceModal(orderId);
                }
            });

            // فتح نافذة التواصل مع العميل
            document.addEventListener('click', function(event) {
                if (event.target.closest('.action-btn.contact')) {
                    const customerId = event.target.closest('.action-btn.contact').getAttribute('data-customer');
                    openContactModal(customerId);
                }
            });

            // إغلاق النوافذ المنبثقة
            document.querySelectorAll('.close-modal, #closeInvoice, #closeContact').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.modal-overlay').forEach(modal => {
                        modal.classList.remove('active');
                    });
                });
            });

            // طباعة الفاتورة
            document.getElementById('printInvoiceButton').addEventListener('click', function() {
                printInvoice();
            });

            // طباعة جميع التقارير
            document.getElementById('printAllButton').addEventListener('click', function() {
                printAllReports();
            });

            // تغيير حالة الطلب
            document.addEventListener('click', function(event) {
                if (event.target.closest('.status-btn')) {
                    const statusBtn = event.target.closest('.status-btn');
                    const status = statusBtn.classList[1];
                    const orderId = document.getElementById('invoiceModal').getAttribute('data-order-id');
                    
                    changeOrderStatus(orderId, status);
                }
            });
        }

        // فتح نافذة الفاتورة
        function openInvoiceModal(orderId) {
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(order => {
                    const modalBody = document.getElementById('invoiceModalBody');
                    modalBody.innerHTML = generateInvoiceHTML(order);
                    document.getElementById('invoiceModal').setAttribute('data-order-id', orderId);
                    document.getElementById('invoiceModal').classList.add('active');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء جلب بيانات الطلب');
                });
        }

        // توليد HTML للفاتورة
        function generateInvoiceHTML(order) {
            const statusText = {
                'pending': 'قيد المراجعة',
                'approved': 'تمت الموافقة', 
                'not_paid': 'لم يتم الدفع',
                'in_delivery': 'قيد التوصيل',
                'completed': 'تم التسليم'
            };

            const paymentMethodText = {
                'credit_card': 'بطاقة ائتمان',
                'bank_transfer': 'تحويل بنكي',
                'cash_on_delivery': 'الدفع عند الاستلام'
            };

            const deliveryMethodText = {
                'fast_delivery': 'توصيل سريع',
                'normal_delivery': 'توصيل عادي'
            };

            // حساب المجموع من العناصر
            const subtotal = order.items.reduce((sum, item) => sum + parseFloat(item.total_price), 0);
            const tax = subtotal * 0.15; // افتراضي 15%
            const shipping = order.delivery_method === 'fast_delivery' ? 25 : 10;
            const final_total = subtotal + tax + shipping;

            return `
                <div class="invoice-header">
                    <div class="invoice-info">
                        <h2>فاتورة #${order.invoice_number}</h2>
                        <div class="invoice-details">
                            <p>تاريخ الفاتورة: ${order.order_date}</p>
                            <p>وقت الفاتورة: ${new Date(order.created_at).toLocaleTimeString('ar-SA')}</p>
                        </div>
                    </div>
                    <div class="invoice-status">
                        <span class="order-status ${order.status}">${statusText[order.status]}</span>
                    </div>
                </div>

                <div class="customer-section">
                    <div class="customer-card">
                        <h4>معلومات العميل</h4>
                        <div class="customer-details-grid">
                            <div class="detail-item">
                                <div class="detail-label">الاسم الكامل</div>
                                <div class="detail-value">${order.customer_name}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">البريد الإلكتروني</div>
                                <div class="detail-value">${order.customer_email}</div>
                            </div>
                        </div>
                    </div>
                    <div class="payment-card">
                        <h4>معلومات الدفع والتوصيل</h4>
                        <div class="payment-details-grid">
                            <div class="detail-item">
                                <div class="detail-label">طريقة الدفع</div>
                                <div class="detail-value">${paymentMethodText[order.payment_method]}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">طريقة التوصيل</div>
                                <div class="detail-value">${deliveryMethodText[order.delivery_method]}</div>
                            </div>
                            ${order.tracking_number ? `
                            <div class="detail-item">
                                <div class="detail-label">رقم التتبع</div>
                                <div class="detail-value">${order.tracking_number}</div>
                            </div>
                            ` : ''}
                            ${order.estimated_delivery ? `
                            <div class="detail-item">
                                <div class="detail-label">موعد التوصيل المتوقع</div>
                                <div class="detail-value">${order.estimated_delivery}</div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>

                ${order.bank_receipt ? `
                <div class="receipt-section">
                    <h4>إيصال الدفع</h4>
                    <img src="${order.bank_receipt}" alt="إيصال الدفع" class="receipt-image" style="max-width: 300px; height: auto;">
                    <p>تم الدفع عبر: ${paymentMethodText[order.payment_method]}</p>
                </div>
                ` : ''}

                <div class="products-section">
                    <h4>المنتجات المطلوبة</h4>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>المواصفات</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.items.map(item => `
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div class="product-details">
                                                <h5>${item.product_name}</h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        ${item.size ? `المقاس: ${item.size}` : ''} 
                                        ${item.color ? `${item.size ? ' - ' : ''}اللون: ${item.color}` : ''}
                                    </td>
                                    <td>${item.quantity}</td>
                                    <td>
                                        <div class="product-price">$${parseFloat(item.unit_price).toFixed(2)}</div>
                                    </td>
                                    <td>
                                        <div class="product-price">$${parseFloat(item.total_price).toFixed(2)}</div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>

                <div class="invoice-summary">
                    <div class="status-actions">
                        <h4>تغيير حالة الطلب</h4>
                        <button class="status-btn pending">
                            <i class="fas fa-clock"></i>
                            قيد المراجعة
                        </button>
                        <button class="status-btn approved">
                            <i class="fas fa-check"></i>
                            تمت الموافقة
                        </button>
                        <button class="status-btn not_paid">
                            <i class="fas fa-times"></i>
                            لم يتم الدفع
                        </button>
                        <button class="status-btn in_delivery">
                            <i class="fas fa-truck"></i>
                            قيد التوصيل
                        </button>
                        <button class="status-btn completed">
                            <i class="fas fa-box"></i>
                            تم التسليم
                        </button>
                    </div>
                    <div class="total-section">
                        <h4>ملخص الفاتورة</h4>
                        <div class="total-row">
                            <span>المجموع الجزئي:</span>
                            <span>$${subtotal.toFixed(2)}</span>
                        </div>
                        <div class="total-row">
                            <span>ضريبة القيمة المضافة (15%):</span>
                            <span>$${tax.toFixed(2)}</span>
                        </div>
                        <div class="total-row">
                            <span>رسوم الشحن:</span>
                            <span>$${shipping.toFixed(2)}</span>
                        </div>
                        <div class="total-row final">
                            <span>المبلغ الإجمالي:</span>
                            <span>$${final_total.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        // فتح نافذة التواصل مع العميل
        function openContactModal(customerId) {
            // محاكاة بيانات العميل - في التطبيق الحقيقي ستجلب من الخادم
            const customer = {
                name: 'أحمد محمد',
                phone: '+966501234567',
                email: 'ahmed@example.com',
                avatar: 'أ'
            };
            
            const modalBody = document.getElementById('contactModalBody');
            modalBody.innerHTML = `
                <div class="customer-info">
                    <div class="customer-avatar" style="width: 60px; height: 60px; font-size: 24px;">${customer.avatar}</div>
                    <div class="customer-details">
                        <h4>${customer.name}</h4>
                        <p>عميل منذ: يناير 2023</p>
                    </div>
                </div>

                <div class="contact-options">
                    <div class="contact-option" id="phoneContact">
                        <div class="contact-icon phone">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h4>الاتصال هاتفياً</h4>
                            <p>${customer.phone}</p>
                        </div>
                    </div>
                    <div class="contact-option" id="whatsappContact">
                        <div class="contact-icon whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="contact-details">
                            <h4>التواصل عبر واتساب</h4>
                            <p>${customer.phone}</p>
                        </div>
                    </div>
                    <div class="contact-option" id="emailContact">
                        <div class="contact-icon email">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h4>إرسال بريد إلكتروني</h4>
                            <p>${customer.email}</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('contactModalTitle').textContent = `التواصل مع ${customer.name}`;
            document.getElementById('contactModal').classList.add('active');
            
            // إضافة مستمعي الأحداث للاتصال
            document.getElementById('phoneContact').addEventListener('click', function() {
                alert(`سيتم الاتصال بالرقم: ${customer.phone}`);
            });
            
            document.getElementById('whatsappContact').addEventListener('click', function() {
                alert(`سيتم فتح تطبيق واتساب للرقم: ${customer.phone}`);
            });
            
            document.getElementById('emailContact').addEventListener('click', function() {
                alert(`سيتم فتح بريد إلكتروني إلى: ${customer.email}`);
            });
        }

        // تغيير حالة الطلب
        function changeOrderStatus(orderId, status) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('order_id', orderId);
            formData.append('status', status);
            
            fetch('', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) {
                    alert('تم تحديث حالة الطلب بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء تحديث حالة الطلب');
                }
            });
        }

        // طباعة الفاتورة
        function printInvoice() {
            alert('سيتم فتح نافذة الطباعة للفاتورة');
            // في التطبيق الحقيقي، ستقوم بفتح نافذة الطباعة
        }

        // طباعة جميع التقارير
        function printAllReports() {
            alert('سيتم فتح نافذة الطباعة لجميع التقارير');
            // في التطبيق الحقيقي، ستقوم بفتح نافذة الطباعة
        }
    </script>
</body>
</html>