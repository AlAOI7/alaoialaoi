<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'لم يتم تحديد ID']);
    exit;
}

$purchase_id = (int)$_GET['id'];

try {
    // جلب بيانات الفاتورة
    $sql = "SELECT p.*, s.name as supplier_name 
            FROM purchases p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $purchase_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'فاتورة غير موجودة']);
        exit;
    }
    
    $purchase = $result->fetch_assoc();
    
    // جلب المنتجات
    $details_sql = "SELECT pd.*, pr.name as product_name 
                   FROM purchase_details pd 
                   LEFT JOIN products pr ON pd.product_id = pr.id 
                   WHERE pd.purchase_id = ?";
    
    $details_stmt = $conn->prepare($details_sql);
    $details_stmt->bind_param("i", $purchase_id);
    $details_stmt->execute();
    $details_result = $details_stmt->get_result();
    
    $products = [];
    $items_count = 0;
    
    while ($detail = $details_result->fetch_assoc()) {
        $products[] = $detail;
        $items_count += $detail['quantity'];
    }
    
    // إضافة البيانات
    $purchase['products'] = $products;
    $purchase['items_count'] = $items_count;
    
    // تحديد الحالة
    switch($purchase['status']) {
        case 'in-stock':
            $status_class = 'in-stock';
            $status_text = 'في المخزن';
            break;
        case 'with-supplier':
            $status_class = 'with-supplier';
            $status_text = 'مع المورد';
            break;
        case 'out-of-stock':
            $status_class = 'out-of-stock';
            $status_text = 'نفذت';
            break;
        default:
            $status_class = 'in-stock';
            $status_text = 'في المخزن';
    }
    
    $purchase['status_class'] = $status_class;
    $purchase['status_text'] = $status_text;
    
    echo json_encode([
        'success' => true,
        'data' => $purchase
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>