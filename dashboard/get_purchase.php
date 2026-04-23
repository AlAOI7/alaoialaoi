<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM purchases WHERE id = $id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $purchase = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($purchase);
    } else {
        echo json_encode(['error' => 'Purchase not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>