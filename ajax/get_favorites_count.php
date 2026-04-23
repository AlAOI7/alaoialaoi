
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

$userId = getCurrentUserId();

$query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$count = $row['count'] ?? 0;

echo json_encode([
    'success' => true,
    'count' => $count
]);
?>