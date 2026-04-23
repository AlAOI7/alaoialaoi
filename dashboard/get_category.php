<?php
require_once 'C:/xampp/htdocs/Storthory-main7/config.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT id, parent_id FROM categories WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
?>
