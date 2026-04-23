<?php
require_once 'config.php';

function generatePurchaseNumber() {
    global $conn;
    $year = date('Y');
    $month = date('m');
    
    $query = "SELECT purchase_number FROM purchases 
              WHERE purchase_number LIKE 'PUR-$year-$month-%' 
              ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $last_purchase = $result->fetch_assoc();
        $last_number = explode('-', $last_purchase['purchase_number']);
        $new_seq = intval(end($last_number)) + 1;
    } else {
        $new_seq = 1;
    }
    
    return sprintf("PUR-%s-%s-%04d", $year, $month, $new_seq);
}

echo generatePurchaseNumber();
?>