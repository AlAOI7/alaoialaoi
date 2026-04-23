
<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $order = getOrderDetails($order_id);
    
    if ($order) {
        echo json_encode($order);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
} else {
    echo json_encode(['error' => 'Order ID is required']);
}