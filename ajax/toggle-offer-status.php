<?php
// admin/ajax/toggle-offer-status.php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$offer_id = intval($_POST['offer_id']);
$current_status = intval($_POST['current_status']);
$new_status = $current_status == 1 ? 0 : 1;

$sql = "UPDATE offers SET is_active = ?, updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_status, $offer_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'new_status' => $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}