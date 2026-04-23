<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

// إنشاء مجلد الصور إذا لم يكن موجوداً
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// معالجة الفلاتر وتغيير الحالة
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'filter':
                $filters = [
                    'status' => $_POST['status'] ?? 'all',
                    'search' => $_POST['search'] ?? ''
                ];
                break;
            case 'update_status':
                if (updateOrderStatus($_POST['order_id'], $_POST['status'])) {
                    $message = "تم تحديث حالة الطلب بنجاح";
                    $message_type = "success";
                } else {
                    $message = "حدث خطأ أثناء تحديث حالة الطلب";
                    $message_type = "error";
                }
                break;
        }
    }
}

// جلب البيانات
$orders = getOrders($filters);
?>
   <?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الطلبات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* الأنماط الأساسية */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
      
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
      
        
        .logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
            margin-bottom: 20px;
        }
        
        .logo h2 {
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            color: #3498db;
        }
        
        .menu {
            list-style: none;
        }
        
        .menu li {
            margin-bottom: 5px;
        }
        
        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu a:hover, .menu a.active {
            background-color: #34495e;
            color: white;
            border-right: 3px solid #3498db;
        }
        
        .menu i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-title h2 {
            font-size: 24px;
            color: #2c3e50;
        }
        
        .date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            cursor: pointer;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
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
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .orders-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .orders-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .orders-header h3 {
            color: #2c3e50;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .orders-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .customer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .customer-details h4 {
            margin-bottom: 2px;
            color: #2c3e50;
        }
        
        .customer-details p {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .order-id {
            font-weight: 600;
            color: #3498db;
            cursor: pointer;
        }
        
        .order-total {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .order-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .order-status.approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .order-status.not_paid {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-status.in_delivery {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .order-status.completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .order-actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .action-btn.view {
            background-color: #17a2b8;
            color: white;
        }
        
        .action-btn.print {
            background-color: #6c757d;
            color: white;
        }
        
        .action-btn.contact {
            background-color: #28a745;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        .pagination {
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }
        
        .pagination button {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pagination button.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
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
            color: #7f8c8d;
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
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
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
        
        .customer-card h4, .payment-card h4 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .receipt-section {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .receipt-image {
            max-width: 300px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
            cursor: pointer;
        }
        
        .products-section {
            margin-bottom: 30px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th, .products-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .products-table th {
            background-color: #f8f9fa;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7f8c8d;
        }
        
        .product-details h5 {
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .product-details p {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .product-price {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .invoice-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .status-actions h4, .total-section h4 {
            margin-bottom: 15px;
            color: #2c3e50;
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
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .status-btn:hover {
            background-color: #f8f9fa;
        }
        
        .status-btn.pending {
            border-color: #fff3cd;
        }
        
        .status-btn.approved {
            border-color: #d4edda;
        }
        
        .status-btn.not_paid {
            border-color: #f8d7da;
        }
        
        .status-btn.in_delivery {
            border-color: #d1ecf1;
        }
        
        .status-btn.completed {
            border-color: #d4edda;
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .total-row.final {
            font-weight: bold;
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .contact-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
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
            background-color: #f8f9fa;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .contact-icon.phone {
            background-color: #28a745;
        }
        
        .contact-icon.whatsapp {
            background-color: #25d366;
        }
        
        .contact-icon.email {
            background-color: #17a2b8;
        }
        
        .contact-details h4 {
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .customer-orders-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .customer-orders-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }
        
        .customer-orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .customer-orders-table th, .customer-orders-table td {
            padding: 10px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .customer-orders-table th {
            background-color: #f8f9fa;
        }
        
        .image-modal {
            max-width: 600px;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 400px;
            display: block;
            margin: 0 auto 15px;
        }
        
        .image-details {
            text-align: center;
            color: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .page-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .customer-section, .invoice-summary {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <div class="container">
            
      <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <div class="page-content">
                <div class="page-title">
                    <h2>إدارة الطلبات</h2>
                    <div class="date"><?php echo date('Y-m-d'); ?></div>
                </div>

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
                    <button class="filter-btn active" data-filter="all">الكل</button>
                    <button class="filter-btn" data-filter="pending">قيد المراجعة</button>
                    <button class="filter-btn" data-filter="approved">تمت الموافقة</button>
                    <button class="filter-btn" data-filter="not_paid">لم يتم الدفع</button>
                    <button class="filter-btn" data-filter="in_delivery">قيد التوصيل</button>
                    <button class="filter-btn" data-filter="completed">تم التسليم</button>
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

    <!-- نافذة معاينة الصورة -->
    <div class="modal-overlay" id="imageModal">
        <div class="modal image-modal">
            <div class="modal-header">
                <h3>معاينة الصورة</h3>
                <button class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <img src="" alt="معاينة الصورة" class="image-preview" id="imagePreview">
                <div class="image-details" id="imageDetails">
                    <!-- سيتم تعبئة تفاصيل الصورة من خلال JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeImage">إغلاق</button>
                <button class="btn btn-primary" id="downloadImageButton">
                    <i class="fas fa-download"></i>
                    تحميل الصورة
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

    <!-- نافذة طلبات العميل -->
    <div class="modal-overlay" id="customerOrdersModal">
        <div class="modal customer-orders-modal">
            <div class="modal-header">
                <h3>طلبات العميل</h3>
                <button class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="customerOrdersModalBody">
                <!-- سيتم تعبئة محتوى طلبات العميل من خلال JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeCustomerOrders">إغلاق</button>
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
                
                if (event.target.closest('.customer-name')) {
                    const orderId = event.target.closest('tr').getAttribute('data-order-id');
                    // في التطبيق الحقيقي، ستحتاج إلى جلب معرف العميل من البيانات
                    openCustomerOrdersModal(orderId);
                }
            });

            // إغلاق النوافذ المنبثقة
            document.querySelectorAll('.close-modal, #closeInvoice, #closeImage, #closeContact, #closeCustomerOrders').forEach(button => {
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
            // في التطبيق الحقيقي، ستقوم بإرسال طلب AJAX إلى الخادم
            // هنا سنقوم بمحاكاة البيانات
            fetchOrderDetails(orderId).then(order => {
                const modalBody = document.getElementById('invoiceModalBody');
                modalBody.innerHTML = generateInvoiceHTML(order);
                document.getElementById('invoiceModal').setAttribute('data-order-id', orderId);
                document.getElementById('invoiceModal').classList.add('active');
            });
        }

        // جلب تفاصيل الطلب (محاكاة)
        function fetchOrderDetails(orderId) {
            // في التطبيق الحقيقي، ستقوم بإرسال طلب AJAX إلى الخادم
            return Promise.resolve({
                id: orderId,
                invoice_number: 'INV-2023-' + orderId.toString().padStart(3, '0'),
                order_date: '2023-05-18',
                status: 'pending',
                customer_name: 'أحمد محمد',
                customer_email: 'ahmed@example.com',
                customer_phone: '+966501234567',
                customer_address: 'الرياض، حي العليا، شارع الملك فهد',
                payment_method: 'credit_card',
                delivery_method: 'fast_delivery',
                bank_receipt: 'https://via.placeholder.com/300x200?text=صورة+إيصال+الدفع',
                tracking_number: 'TRK-789456123',
                estimated_delivery: '2023-05-20',
                items: [
                    { product_name: 'تيشيرت قطني رياضي', size: 'L', color: 'أزرق', quantity: 2, unit_price: 75, total_price: 150 },
                    { product_name: 'حذاء رياضي', size: '42', color: 'أسود', quantity: 1, unit_price: 450, total_price: 450 },
                    { product_name: 'ساعة ذكية', size: 'قاس واحد', color: 'فضي', quantity: 1, unit_price: 500, total_price: 500 }
                ],
                subtotal: 1100,
                tax: 165,
                shipping: 25,
                final_total: 1290
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

            return `
                <div class="invoice-header">
                    <div class="invoice-info">
                        <h2>فاتورة #${order.invoice_number}</h2>
                        <div class="invoice-details">
                            <p>تاريخ الفاتورة: ${order.order_date}</p>
                            <p>وقت الفاتورة: 10:30 صباحاً</p>
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
                            <div class="detail-item">
                                <div class="detail-label">رقم الهاتف</div>
                                <div class="detail-value">${order.customer_phone}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">العنوان</div>
                                <div class="detail-value">${order.customer_address}</div>
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
                            <div class="detail-item">
                                <div class="detail-label">رقم التتبع</div>
                                <div class="detail-value">${order.tracking_number}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">موعد التوصيل المتوقع</div>
                                <div class="detail-value">${order.estimated_delivery}</div>
                            </div>
                        </div>
                    </div>
                </div>

                ${order.bank_receipt ? `
                <div class="receipt-section">
                    <h4>إيصال الدفع</h4>
                    <img src="${order.bank_receipt}" alt="إيصال الدفع" class="receipt-image">
                    <p>تم الدفع عبر: ${paymentMethodText[order.payment_method]}</p>
                </div>
                ` : ''}

                <div class="products-section">
                    <h4>المنتجات المطلوبة</h4>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
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
                                                <p>${item.size ? `المقاس: ${item.size}` : ''} ${item.color ? `- اللون: ${item.color}` : ''}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${item.quantity}</td>
                                    <td>
                                        <div class="product-price">$${item.unit_price.toFixed(2)}</div>
                                    </td>
                                    <td>
                                        <div class="product-price">$${item.total_price.toFixed(2)}</div>
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
                            <span>$${order.subtotal.toFixed(2)}</span>
                        </div>
                        <div class="total-row">
                            <span>ضريبة القيمة المضافة (15%):</span>
                            <span>$${order.tax.toFixed(2)}</span>
                        </div>
                        <div class="total-row">
                            <span>رسوم الشحن:</span>
                            <span>$${order.shipping.toFixed(2)}</span>
                        </div>
                        <div class="total-row final">
                            <span>المبلغ الإجمالي:</span>
                            <span>$${order.final_total.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        // فتح نافذة التواصل مع العميل
        function openContactModal(customerId) {
            // في التطبيق الحقيقي، ستقوم بإرسال طلب AJAX إلى الخادم
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

        // فتح نافذة طلبات العميل
        function openCustomerOrdersModal(customerId) {
            // في التطبيق الحقيقي، ستقوم بإرسال طلب AJAX إلى الخادم
            const customer = {
                name: 'أحمد محمد',
                email: 'ahmed@example.com',
                phone: '+966501234567',
                avatar: 'أ'
            };
            
            const customerOrders = [
                { id: 1, invoice_number: 'INV-2023-001', order_date: '2023-05-18', total_amount: 1290, status: 'pending' },
                { id: 2, invoice_number: 'INV-2023-015', order_date: '2023-04-22', total_amount: 850, status: 'completed' },
                { id: 3, invoice_number: 'INV-2023-008', order_date: '2023-03-10', total_amount: 450, status: 'completed' }
            ];
            
            const modalBody = document.getElementById('customerOrdersModalBody');
            modalBody.innerHTML = `
                <div class="customer-orders-header">
                    <div class="customer-orders-avatar">${customer.avatar}</div>
                    <div class="customer-orders-info">
                        <h3>${customer.name}</h3>
                        <p>${customer.email} | ${customer.phone}</p>
                    </div>
                </div>
                
                <h4>طلبات العميل (${customerOrders.length})</h4>
                
                <table class="customer-orders-table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${customerOrders.map(order => `
                            <tr>
                                <td>
                                    <div class="order-id">#${order.invoice_number}</div>
                                </td>
                                <td>${order.order_date}</td>
                                <td>
                                    <div class="order-total">$${order.total_amount.toFixed(2)}</div>
                                </td>
                                <td>
                                    <span class="order-status ${order.status}">${getStatusText(order.status)}</span>
                                </td>
                                <td>
                                    <div class="order-actions">
                                        <button class="action-btn view" data-order="${order.id}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn print" data-order="${order.id}">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('customerOrdersModal').classList.add('active');
            
            // إضافة مستمعي الأحداث للأزرار داخل النافذة
            modalBody.querySelectorAll('.action-btn.view').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order');
                    document.getElementById('customerOrdersModal').classList.remove('active');
                    openInvoiceModal(orderId);
                });
            });
        }

        // تغيير حالة الطلب
        function changeOrderStatus(orderId, status) {
            // في التطبيق الحقيقي، ستقوم بإرسال طلب AJAX إلى الخادم
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

        // الحصول على نص الحالة
        function getStatusText(status) {
            const statusMap = {
                'pending': 'قيد المراجعة',
                'approved': 'تمت الموافقة',
                'not_paid': 'لم يتم الدفع',
                'in_delivery': 'قيد التوصيل',
                'completed': 'تم التسليم'
            };
            return statusMap[status] || status;
        }
    </script>
</body>
</html>

