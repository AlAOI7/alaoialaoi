<?php
// clear_cart.php
session_start();

if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    // يمكنك إضافة منطق إضافي هنا إذا أردت
}

// تفريغ السلة
unset($_SESSION['cart']);

echo json_encode(['success' => true]);
?>