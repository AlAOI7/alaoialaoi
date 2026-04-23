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
    <link rel="stylesheet" href="style.css">
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