<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_GET['barcode'])) {
    $barcode = trim($_GET['barcode']);
    
    $stmt = $conn->prepare("SELECT id, name FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $stmt->store_result();
    
    echo json_encode([
        'exists' => $stmt->num_rows > 0
    ]);
    
    $stmt->close();
}
?>