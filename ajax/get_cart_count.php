<?php
require_once '../config/database.php';
session_start();

$count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
}

echo json_encode(['count' => $count]);
?>