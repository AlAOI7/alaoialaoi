<?php
// order_details.php - إعادة توجيه لصفحة الفاتورة
header('Location: invoice.php?order_id=' . ($_GET['id'] ?? ''));
exit;
?>
