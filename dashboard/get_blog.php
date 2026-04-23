<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'غير مصرح']));
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'معرف المدونة مطلوب']));
}

$blog_id = (int)$_GET['id'];

// جلب بيانات المدونة
$sql = "SELECT * FROM blogs WHERE id = $blog_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $blog = $result->fetch_assoc();
    echo json_encode($blog);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'المدونة غير موجودة']);
}
?>