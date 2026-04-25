<?php
// ajax/update_product_prices.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$code = strtoupper(trim($_POST['currency_code'] ?? ''));
if (!$code) {
    echo json_encode(['success' => false, 'message' => 'كود العملة مطلوب']);
    exit;
}

// جلب سعر الصرف
$curr_stmt = $conn->prepare("SELECT exchange_rate FROM currencies WHERE code = ? AND status = 'active'");
$curr_stmt->bind_param("s", $code);
$curr_stmt->execute();
$curr = $curr_stmt->get_result()->fetch_assoc();

if (!$curr) {
    echo json_encode(['success' => false, 'message' => 'العملة غير موجودة']);
    exit;
}

$rate = $curr['exchange_rate'];

// خريطة الأعمدة
$col_map = [
    'SAR'     => 'price_sar',
    'USD'     => 'price_usd',
    'YER'     => 'price_yer_new',
    'YER_OLD' => 'price_yer_old',
];

if (!isset($col_map[$code])) {
    echo json_encode(['success' => false, 'message' => 'لا يوجد عمود مخصص لهذه العملة في جدول المنتجات']);
    exit;
}

$col = $col_map[$code];
$result = $conn->query("UPDATE products SET $col = ROUND(selling_price * $rate, 2) WHERE selling_price > 0");

echo json_encode([
    'success' => true,
    'count'   => $conn->affected_rows,
    'message' => "تم تحديث الأسعار بسعر صرف {$rate}"
]);
?>
