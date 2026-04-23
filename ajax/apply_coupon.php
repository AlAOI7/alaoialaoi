<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$code = isset($_POST['code']) ? trim($_POST['code']) : '';
$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'فضلاً أدخل كود الخصم']);
    exit;
}

// البحث عن القسيمة
$sql = "SELECT * FROM coupons WHERE code = ? AND is_active = 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // في حال عدم وجود الجدول، نرجع خطأ أو نستخدم قسيمة تجريبية
    // لكن سنفترض وجود الجدول بناء على التخطيط
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في النظام']);
    exit;
}

$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'كود الخصم غير صحيح أو منتهي الصلاحية']);
    exit;
}

$coupon = $result->fetch_assoc();
$now = date('Y-m-d H:i:s');

// التحقق من التاريخ
if (($coupon['start_date'] && $coupon['start_date'] > $now) || 
    ($coupon['end_date'] && $coupon['end_date'] < $now)) {
    echo json_encode(['success' => false, 'message' => 'كود الخصم منتهي الصلاحية']);
    exit;
}

// التحقق من حدود الاستخدام
if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
    echo json_encode(['success' => false, 'message' => 'تم استنفاد عدد مرات استخدام هذا الكود']);
    exit;
}

// التحقق من الحد الأدنى للطلب
if ($coupon['min_order_amount'] > 0 && $total < $coupon['min_order_amount']) {
    echo json_encode([
        'success' => false, 
        'message' => 'يجب أن يكون إجمالي الطلب ' . $coupon['min_order_amount'] . ' ر.س على الأقل لاستخدام هذا الكود'
    ]);
    exit;
}

// حساب الخصم
$discount_amount = 0;
if ($coupon['discount_type'] == 'percentage') {
    $discount_amount = ($total * $coupon['discount_value']) / 100;
    // التحقق من الحد الأقصى للخصم
    if ($coupon['max_discount_amount'] > 0 && $discount_amount > $coupon['max_discount_amount']) {
        $discount_amount = $coupon['max_discount_amount'];
    }
} else {
    $discount_amount = $coupon['discount_value'];
}

// التأكد أن الخصم لا يتجاوز الإجمالي
if ($discount_amount > $total) {
    $discount_amount = $total;
}

echo json_encode([
    'success' => true,
    'message' => 'تم تطبيق الخصم بنجاح',
    'coupon_code' => $coupon['code'],
    'discount_amount' => $discount_amount,
    'new_total' => $total - $discount_amount
]);
?>
