<?php
// invoice.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    header('Location: home.php');
    exit;
}

// جلب بيانات الفاتورة
$sql = "SELECT o.*, da.*, do.name as delivery_option_name, do.cost as delivery_cost
        FROM orders o
        LEFT JOIN delivery_addresses da ON o.customer_id = da.user_id AND da.is_default = 1
        LEFT JOIN delivery_options do ON o.delivery_cost = do.cost 
        WHERE o.id = ? AND o.customer_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    // محاولة البحث بدون delivery addresses إذا لم يكن هناك عنوان
    $sql = "SELECT o.* FROM orders o WHERE o.id = ? AND o.customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        echo "لم يتم العثور على الفاتورة";
        exit;
    }
}

// جلب عناصر الطلب
$item_sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($item_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$order_items = [];
while ($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
}
   
// جلب رقم الواتساب من جدول about (إذا وجد)
$whatsapp = '966500000000'; // رقم افتراضي
// $about_result = $conn->query("SELECT whatsapp FROM about WHERE id = 1");
// if ($about_result && $about_result->num_rows > 0) {
//     $whatsapp = $about_result->fetch_assoc()['whatsapp'];
// }

// تنسيق رسالة الواتساب
$invoice_url = "http://" . $_SERVER['HTTP_HOST'] . "/invoice.php?order_id=" . $order_id;
$whatsapp_message = "🧾 *فاتورة رقم: " . ($order['invoice_number'] ?? '') . "*\n\n";
$whatsapp_message .= "👤 العميل: " . ($order['full_name'] ?? 'عميل') . "\n";
$whatsapp_message .= "📱 الهاتف: " . ($order['phone'] ?? '') . "\n";
$whatsapp_message .= "💰 المبلغ الإجمالي: " . number_format($order['total_amount'], 2) . " ر.س\n";
$whatsapp_message .= "💳 طريقة الدفع: " . $order['payment_method'] . "\n\n";

if ($order['status'] == 'reserved') {
    $whatsapp_message .= "⚠️ *تنبيه:* هذا الطلب محجوز لمدة 24 ساعة. يرجى إتمام الدفع لتأكيد الطلب.\n\n";
}

$whatsapp_message .= "🛍️ *المنتجات*:\n";
foreach ($order_items as $item) {
    $whatsapp_message .= "• " . $item['product_name'] . " (x" . $item['quantity'] . ") - " . number_format($item['total_price'], 2) . " ر.س\n";
}
$whatsapp_message .= "\n🔗 عرض الفاتورة: " . $invoice_url;

$whatsapp_link = "https://wa.me/" . $whatsapp . "?text=" . urlencode($whatsapp_message);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفاتورة | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .invoice-container { max-width: 800px; margin: 50px auto; }
        .invoice-header { background: linear-gradient(135deg, #ff3366, #a56cc1); color: white; padding: 30px; border-radius: 15px 15px 0 0; }
        .invoice-body { background: white; padding: 40px; border-radius: 0 0 15px 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); }
        .total-section { background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .payment-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .payment-cod { background-color: #e2e3e5; color: #383d41; }
        .payment-bank { background-color: #cfe2ff; color: #084298; }
        .payment-reserve { background-color: #fff3cd; color: #664d03; }
    </style>
</head>
<body>
    <div class="container invoice-container">
        <div class="invoice-header text-center">
            <h1 class="mb-2 fw-bold"><i class="fas fa-check-circle me-2"></i>تم استلام طلبك بنجاح</h1>
            <p class="mb-0 fs-5">رقم الفاتورة: <?php echo htmlspecialchars($order['invoice_number']); ?></p>
        </div>
        
        <div class="invoice-body">
            
            <?php if ($order['status'] == 'reserved'): ?>
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <h5 class="alert-heading"><i class="fas fa-clock me-2"></i>الطلب محجوز مؤقتاً</h5>
                <p class="mb-0">تم حجز المنتجات لك لمدة 24 ساعة. يرجى التواصل معنا عبر واتساب لتأكيد الدفع وإتمام الطلب.</p>
            </div>
            <?php endif; ?>

            <!-- معلومات الفاتورة -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <h5 class="text-primary fw-bold mb-3">معلومات العميل</h5>
                    <p class="mb-1"><strong>الاسم:</strong> <?php echo htmlspecialchars($order['full_name'] ?? 'غير محدد'); ?></p>
                    <p class="mb-1"><strong>الهاتف:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'غير محدد'); ?></p>
                    <p class="mb-1"><strong>المدينة:</strong> <?php echo htmlspecialchars($order['city'] ?? 'غير محددة'); ?></p>
                    <p class="mb-0"><strong>العنوان:</strong> <?php echo htmlspecialchars(($order['district'] ?? '') . ($order['district'] && $order['street'] ? ' - ' : '') . ($order['street'] ?? 'غير محدد')); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="text-primary fw-bold mb-3">تفاصيل الطلب</h5>
                    <p class="mb-1"><strong>تاريخ الطلب:</strong> <?php echo date('Y/m/d H:i', strtotime($order['order_date'])); ?></p>
                    <p class="mb-1">
                        <strong>طريقة الدفع:</strong> 
                        <?php 
                        $method_class = 'payment-cod';
                        $method_name = 'الدفع عند الاستلام';
                        
                        if ($order['payment_method'] == 'bank_transfer') {
                            $method_class = 'payment-bank';
                            $method_name = 'تحويل بنكي';
                        } elseif ($order['payment_method'] == 'reserve') {
                            $method_class = 'payment-reserve';
                            $method_name = 'حجز (ادفع لاحقاً)';
                        }
                        ?>
                        <span class="badge <?php echo $method_class; ?>"><?php echo $method_name; ?></span>
                    </p>
                    <p class="mb-0"><strong>حالة الطلب:</strong> <?php echo $order['status']; ?></p>

                    <?php if ($order['payment_proof']): ?>
                        <div class="mt-3">
                            <small class="d-block text-muted mb-1">إيصال التحويل:</small>
                            <a href="<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file-image me-1"></i> عرض الإيصال
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- تفاصيل المنتجات -->
            <div class="table-responsive mb-4">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-end">سعر الوحدة</th>
                            <th class="text-end">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end"><?php echo number_format($item['unit_price'], 2); ?> ر.س</td>
                            <td class="text-end fw-bold"><?php echo number_format($item['total_price'], 2); ?> ر.س</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- المجموع -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="total-section">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">المجموع الفرعي:</span>
                            <span><?php echo number_format($order['total_amount'] - $order['delivery_cost'], 2); ?> ر.س</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">تكاليف الشحن:</span>
                            <span><?php echo number_format($order['delivery_cost'], 2); ?> ر.س</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span class="text-primary">المجموع الكلي:</span>
                            <span><?php echo number_format($order['total_amount'], 2); ?> ر.س</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- أزرار -->
            <div class="text-center mt-5">
                <button onclick="window.print()" class="btn btn-secondary me-2">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="btn btn-success me-2">
                    <i class="fab fa-whatsapp me-1"></i> إرسال الفاتورة عبر واتساب
                </a>
                <a href="product.php" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag me-1"></i> متابعة التسوق
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>