<?php
session_start();
require_once 'config/database.php';

$response = [
    'success' => false,
    'notifications' => 0,
    'favorites' => 0,
    'cart_items' => 0
];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    try {
        // Get unread notifications count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications 
                               WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $response['notifications'] = $stmt->fetch()['count'];
        
        // Get favorites count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites 
                               WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $response['favorites'] = $stmt->fetch()['count'];
        
        // Get cart items count
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items 
                               WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetch()['total'];
        $response['cart_items'] = $cart_count ?: 0;
        
        $response['success'] = true;
        
    } catch (PDOException $e) {
        error_log("Error getting notification counts: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>