<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name, image FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($categories as $cat) {
    echo "ID: " . $cat['id'] . " Name: " . $cat['name'] . " Image: " . $cat['image'] . " -> Exists? " . (file_exists(substr($cat['image'], 3)) ? 'Yes' : 'No') . "\n";
}
?>
