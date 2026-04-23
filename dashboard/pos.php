<?php
require_once 'config.php';
checkAuth();

// التحقق من صلاحيات المستخدم
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'support') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getUser($user_id);

// دالة للحصول على إحصائيات المبيعات
function getSalesStats($filters = []) {
    global $conn;
    
    $sql = "
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_sales,
            AVG(total_amount) as avg_order_value,
            COUNT(DISTINCT customer_id) as total_customers
        FROM orders 
        WHERE 1=1
    ";
    
    if (!empty($filters['date_range'])) {
        switch($filters['date_range']) {
            case '7days':
                $sql .= " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $sql .= " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case '3months':
                $sql .= " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                break;
            case 'this_year':
                $sql .= " AND YEAR(order_date) = YEAR(CURDATE())";
                break;
        }
    }
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// دالة للحصول على الإيرادات الشهرية
function getMonthlyRevenue($year = null) {
    global $conn;
    
    if (!$year) {
        $year = date('Y');
    }
    
    $sql = "
        SELECT 
            MONTH(order_date) as month,
            SUM(total_amount) as revenue
        FROM orders 
        WHERE YEAR(order_date) = ?
        GROUP BY MONTH(order_date)
        ORDER BY month
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// دالة للحصول على توزيع المبيعات حسب الفئة
// function getSalesByCategory($filters = []) {
//     global $conn;
    
//     $sql = "
//         SELECT 
//             c.name as category_name,
//             SUM(oi.total_price) as total_sales,
//             COUNT(oi.id) as total_orders
//         FROM order_items oi
//         JOIN products p ON oi.product_id = p.id
//         JOIN categories c ON p.category_id = c.id
//         JOIN orders o ON oi.order_id = o.id
//         WHERE 1=1
//     ";
    
//     if (!empty($filters['date_range'])) {
//         switch($filters['date_range']) {
//             case '7days':
//                 $sql .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
//                 break;
//             case '30days':
//                 $sql .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
//                 break;
//             case '3months':
//                 $sql .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
//                 break;
//             case 'this_year':
//                 $sql .= " AND YEAR(o.order_date) = YEAR(CURDATE())";
//                 break;
//         }
//     }
    
//     $sql .= " GROUP BY c.id, c.name ORDER BY total_sales DESC";
    
//     $result = $conn->query($sql);
//     return $result->fetch_all(MYSQLI_ASSOC);
// }

// دالة للحصول على الفئات
function getCategories() {
    global $conn;
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// تم حذف دالة getOrders() لأنها موجودة في config.php
?>

<?php
// معالجة الفلاتر
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'date_range' => $_POST['date_range'] ?? '',
        'category' => $_POST['category'] ?? '',
        'customer_type' => $_POST['customer_type'] ?? ''
    ];
}

// جلب البيانات باستخدام الدوال الموجودة في config.php
$stats = getSalesStats($filters);
$monthly_revenue = getMonthlyRevenue(date('Y'));
// $sales_by_category = getSalesByCategory($filters);
$categories = getCategories();

// استخدام دالة getOrders() من config.php (بدون تعريفها هنا)
$orders = []; // أو استدعاء الدالة من config.php إذا كانت تتعامل مع الفلاتر

// إذا كانت دالة getOrders() في config.php لا تأخذ فلاتر، اضف نسخة معدلة هنا
function getFilteredOrders($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE 1=1";
    
    if (!empty($filters['date_range'])) {
        switch($filters['date_range']) {
            case '7days':
                $sql .= " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $sql .= " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case '3months':
                $sql .= " AND order_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                break;
            case 'this_year':
                $sql .= " AND YEAR(order_date) = YEAR(CURDATE())";
                break;
        }
    }
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$orders = getFilteredOrders($filters);

// إعداد بيانات الرسوم البيانية
$months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
$revenue_data = array_fill(0, 12, 0);
foreach ($monthly_revenue as $data) {
    $revenue_data[$data['month'] - 1] = $data['revenue'];
}

$category_names = [];
$category_sales = [];
// foreach ($sales_by_category as $category) {
//     $category_names[] = $category['category_name'];
//     $category_sales[] = $category['total_sales'];
// }
?>

<?php
// معالجة الفلاتر
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'date_range' => $_POST['date_range'] ?? '',
        'category' => $_POST['category'] ?? '',
        'customer_type' => $_POST['customer_type'] ?? ''
    ];
}

// جلب البيانات
$orders = getOrders($filters);
$stats = getSalesStats($filters);
$monthly_revenue = getMonthlyRevenue(date('Y'));
// $sales_by_category = getSalesByCategory($filters);
$categories = getCategories();

// إعداد بيانات الرسوم البيانية
$months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
$revenue_data = array_fill(0, 12, 0);
foreach ($monthly_revenue as $data) {
    $revenue_data[$data['month'] - 1] = $data['revenue'];
}

$category_names = [];
$category_sales = [];
// foreach ($sales_by_category as $category) {
//     $category_names[] = $category['category_name'];
//     $category_sales[] = $category['total_sales'];
// }
?> 
  <?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام تقارير المبيعات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* الأنماط الأساسية */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            padding: 20px 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            min-width: 150px;
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
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .card-1 .stat-icon { background-color: #3498db; }
        .card-2 .stat-icon { background-color: #2ecc71; }
        .card-3 .stat-icon { background-color: #9b59b6; }
        .card-4 .stat-icon { background-color: #e74c3c; }
        
        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .stat-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .trend-up { color: #2ecc71; }
        .trend-down { color: #e74c3c; }
        
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-header h3 {
            color: #2c3e50;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .sales-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .table-header h3 {
            color: #2c3e50;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-cell {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .view-btn {
            background-color: #17a2b8;
            color: white;
        }
        
        .print-btn {
            background-color: #6c757d;
            color: white;
        }
        
        .export-btn {
            background-color: #28a745;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        .modal {
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
        
        .modal-content {
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
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .sale-details {
            padding: 20px;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-items th, .order-items td {
            padding: 10px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .order-items th {
            background-color: #e9ecef;
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        .summary-row.total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        .modal-actions {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-options {
                justify-content: center;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .action-cell {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
   
    <div class="container">
        <!-- الشريط الجانبي -->
          <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <div class="page-content">
                <div class="page-title">
                    <h2>تقارير المبيعات</h2>
                    <div class="date"><?php echo date('Y-m-d'); ?></div>
                </div>

                <!-- شريط الفلاتر والإجراءات -->
                <form method="POST" id="filterForm">
                    <div class="actions-bar">
                        <div class="filter-options">
                            <select class="filter-select" name="date_range">
                                <option value="">آخر 7 أيام</option>
                                <option value="7days" <?php echo ($filters['date_range'] ?? '') == '7days' ? 'selected' : ''; ?>>آخر 7 أيام</option>
                                <option value="30days" <?php echo ($filters['date_range'] ?? '') == '30days' ? 'selected' : ''; ?>>آخر 30 يوم</option>
                                <option value="3months" <?php echo ($filters['date_range'] ?? '') == '3months' ? 'selected' : ''; ?>>آخر 3 أشهر</option>
                                <option value="this_year" <?php echo ($filters['date_range'] ?? '') == 'this_year' ? 'selected' : ''; ?>>هذا العام</option>
                            </select>
                            <select class="filter-select" name="category">
                                <option value="">جميع المنتجات</option>
                                <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($filters['category'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <select class="filter-select" name="customer_type">
                                <option value="">جميع العملاء</option>
                                <option value="new" <?php echo ($filters['customer_type'] ?? '') == 'new' ? 'selected' : ''; ?>>عملاء جدد</option>
                                <option value="premium" <?php echo ($filters['customer_type'] ?? '') == 'premium' ? 'selected' : ''; ?>>عملاء متميزون</option>
                            </select>
                        </div>
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary" id="applyFilterBtn">
                                <i class="fas fa-filter"></i>
                                تطبيق التصفية
                            </button>
                            <button type="button" class="btn btn-success" id="exportAllExcelBtn">
                                <i class="fas fa-file-excel"></i>
                                تصدير الكل لإكسل
                            </button>
                            <button type="button" class="btn btn-warning" id="exportAllPdfBtn">
                                <i class="fas fa-file-pdf"></i>
                                تصدير الكل لPDF
                            </button>
                        </div>
                    </div>
                </form>

                <!-- إحصائيات سريعة -->
                <div class="stats-cards">
                    <div class="stat-card card-1">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                            <p>إجمالي الطلبات</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>12.5% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-2">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($stats['total_sales'] ?? 0, 2); ?></h3>
                            <p>إجمالي المبيعات</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>15.2% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-3">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>4.2%</h3>
                            <p>معدل التحويل</p>
                            <div class="stat-trend trend-down">
                                <i class="fas fa-arrow-down"></i>
                                <span>0.5% انخفاض</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-4">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($stats['avg_order_value'] ?? 0, 2); ?></h3>
                            <p>متوسط قيمة الطلب</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>3.2% زيادة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الرسوم البيانية -->
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>إيرادات المبيعات الشهرية</h3>
                            <select class="filter-select" id="yearSelect">
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>توزيع المبيعات حسب الفئة</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="categoriesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- جدول الطلبات -->
                <div class="sales-table">
                    <div class="table-header">
                        <h3>أحدث الطلبات</h3>
                        <div class="table-actions">
                            <button class="btn btn-primary">
                                <i class="fas fa-filter"></i>
                                تصفية
                            </button>
                            <button class="btn btn-success">
                                <i class="fas fa-sync"></i>
                                تحديث
                            </button>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>طريقة الدفع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_number']; ?></td>
                                <td><?php echo $order['customer_name']; ?></td>
                                <td><?php echo $order['order_date']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $status_class = $order['status'];
                                    $status_text = '';
                                    switch($order['status']) {
                                        case 'completed':
                                            $status_text = 'مكتمل';
                                            break;
                                        case 'pending':
                                            $status_text = 'قيد الانتظار';
                                            break;
                                        case 'cancelled':
                                            $status_text = 'ملغي';
                                            break;
                                    }
                                    ?>
                                    <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo $order['payment_method']; ?></td>
                                <td class="action-cell">
                                    <button class="action-btn view-btn" data-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                        عرض
                                    </button>
                                    <button class="action-btn print-btn" data-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-print"></i>
                                        طباعة
                                    </button>
                                    <button class="action-btn export-btn" data-id="<?php echo $order['id']; ?>">
                                        <i class="fas fa-file-excel"></i>
                                        إكسل
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة منبثقة لعرض تفاصيل المبيعات -->
    <div class="modal" id="saleDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تفاصيل الطلب</h3>
                <button class="close-btn" id="closeSaleModal">&times;</button>
            </div>
            <div class="sale-details" id="saleDetailsContent">
                <!-- سيتم ملء هذا القسم بالبيانات من JavaScript -->
            </div>
            <div class="modal-actions">
                <button class="btn btn-warning" id="printSaleBtn">
                    <i class="fas fa-print"></i>
                    طباعة الطلب
                </button>
                <button class="btn btn-success" id="exportSaleExcelBtn">
                    <i class="fas fa-file-excel"></i>
                    تصدير لإكسل
                </button>
                <button class="btn btn-info" id="exportSalePdfBtn">
                    <i class="fas fa-file-pdf"></i>
                    تصدير لPDF
                </button>
            </div>
        </div>
    </div>

    <script>
        // عناصر DOM
        const saleDetailsModal = document.getElementById('saleDetailsModal');
        const closeSaleModal = document.getElementById('closeSaleModal');
        const saleDetailsContent = document.getElementById('saleDetailsContent');
        const viewButtons = document.querySelectorAll('.view-btn');
        const exportAllExcelBtn = document.getElementById('exportAllExcelBtn');
        const exportAllPdfBtn = document.getElementById('exportAllPdfBtn');

        // إغلاق النماذج
        closeSaleModal.addEventListener('click', () => {
            saleDetailsModal.style.display = 'none';
        });

        // إغلاق النماذج عند النقر خارج المحتوى
        window.addEventListener('click', (e) => {
            if (e.target === saleDetailsModal) {
                saleDetailsModal.style.display = 'none';
            }
        });

        // عرض تفاصيل الطلب
        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const orderId = button.getAttribute('data-id');
                showOrderDetails(orderId);
            });
        });

        // تصدير التقارير (محاكاة)
        exportAllExcelBtn.addEventListener('click', () => {
            alert('سيتم تصدير جميع البيانات إلى ملف Excel');
            // في التطبيق الحقيقي، ستقوم بإرسال طلب إلى الخادم لتصدير البيانات
        });

        exportAllPdfBtn.addEventListener('click', () => {
            alert('سيتم تصدير جميع البيانات إلى ملف PDF');
            // في التطبيق الحقيقي، ستقوم بإرسال طلب إلى الخادم لتصدير البيانات
        });

        // دالة لعرض تفاصيل الطلب
        function showOrderDetails(orderId) {
            // في التطبيق الحقيقي، ستقوم بإرسال طلب AJAX إلى الخادم
            // هنا سنقوم بمحاكاة البيانات
            const orderDetails = {
                order_number: '#ORD-1024',
                customer_name: 'أحمد محمد',
                customer_email: 'ahmed@example.com',
                customer_phone: '+966500000000',
                order_date: '2023-10-15',
                status: 'completed',
                payment_method: 'بطاقة ائتمان',
                items: [
                    { product_name: 'هاتف سامسونج جالاكسي', quantity: 1, unit_price: 199.99, total_price: 199.99 },
                    { product_name: 'غطاء حماية', quantity: 2, unit_price: 15.00, total_price: 30.00 },
                    { product_name: 'شاحن لاسلكي', quantity: 1, unit_price: 25.00, total_price: 25.00 }
                ],
                subtotal: 254.99,
                tax: 15.30,
                shipping: 10.00,
                total: 280.29
            };
            
            let itemsHtml = '';
            orderDetails.items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.unit_price.toFixed(2)}</td>
                        <td>$${item.total_price.toFixed(2)}</td>
                    </tr>
                `;
            });
            
            saleDetailsContent.innerHTML = `
                <div class="order-info">
                    <h4>معلومات الطلب</h4>
                    <div class="summary-row">
                        <span>رقم الطلب:</span>
                        <span>${orderDetails.order_number}</span>
                    </div>
                    <div class="summary-row">
                        <span>اسم العميل:</span>
                        <span>${orderDetails.customer_name}</span>
                    </div>
                    <div class="summary-row">
                        <span>البريد الإلكتروني:</span>
                        <span>${orderDetails.customer_email}</span>
                    </div>
                    <div class="summary-row">
                        <span>الهاتف:</span>
                        <span>${orderDetails.customer_phone}</span>
                    </div>
                    <div class="summary-row">
                        <span>تاريخ الطلب:</span>
                        <span>${orderDetails.order_date}</span>
                    </div>
                    <div class="summary-row">
                        <span>طريقة الدفع:</span>
                        <span>${orderDetails.payment_method}</span>
                    </div>
                    <div class="summary-row">
                        <span>الحالة:</span>
                        <span class="status ${orderDetails.status}">
                            ${orderDetails.status === 'completed' ? 'مكتمل' : orderDetails.status}
                        </span>
                    </div>
                </div>
                
                <div class="order-items">
                    <h4>المنتجات المطلوبة</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>سعر الوحدة</th>
                                <th>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
                
                <div class="order-summary">
                    <h4>ملخص الطلب</h4>
                    <div class="summary-row">
                        <span>المجموع الجزئي:</span>
                        <span>$${orderDetails.subtotal.toFixed(2)}</span>
                    </div>
                    <div class="summary-row">
                        <span>الضريبة:</span>
                        <span>$${orderDetails.tax.toFixed(2)}</span>
                    </div>
                    <div class="summary-row">
                        <span>الشحن:</span>
                        <span>$${orderDetails.shipping.toFixed(2)}</span>
                    </div>
                    <div class="summary-row total">
                        <span>المجموع الكلي:</span>
                        <span>$${orderDetails.total.toFixed(2)}</span>
                    </div>
                </div>
            `;
            
            saleDetailsModal.style.display = 'flex';
        }

        // الرسوم البيانية
        document.addEventListener('DOMContentLoaded', function() {
            // رسم بياني للإيرادات الشهرية
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                    datasets: [{
                        label: 'الإيرادات ($)',
                        data: <?php echo json_encode($revenue_data); ?>,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            rtl: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });

            // رسم بياني دائري لتوزيع المبيعات حسب الفئة
            const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
            const categoriesChart = new Chart(categoriesCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($category_names); ?>,
                    datasets: [{
                        data: <?php echo json_encode($category_sales); ?>,
                        backgroundColor: [
                            '#3498db', '#2ecc71', '#9b59b6', '#e74c3c', 
                            '#f39c12', '#1abc9c', '#34495e', '#d35400'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'left',
                            rtl: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>