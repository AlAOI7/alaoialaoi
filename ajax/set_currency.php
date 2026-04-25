<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Validate currency code
    $stmt = $conn->prepare("SELECT code FROM currencies WHERE code = ? AND status = 'active'");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['currency'] = $code;
    }
}

// Redirect back to previous page
header("Location: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../home.php'));
exit;
?>
