<?php
/**
 * Schema Setup for Enhanced Order System
 * Run this file once to create/update the necessary database structure
 */

require_once 'config/database.php';

echo "<h2>Setting up Order System Schema...</h2>";

// 1. Update orders table
echo "<p>Updating orders table...</p>";

$orders_updates = [
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'pending' AFTER payment_method",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS reservation_expires_at DATETIME NULL AFTER status",
    "ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'"
];

foreach ($orders_updates as $sql) {
    // For MySQL compatibility, we'll check and add columns individually
    $result = $conn->query($sql);
    if ($result) {
        echo "✓ ";
    } else {
        // Try alternative syntax for MySQL
        $sql_alt = str_replace("ADD COLUMN IF NOT EXISTS", "ADD COLUMN", $sql);
        if ($conn->query($sql_alt)) {
            echo "✓ ";
        } else {
            echo "- (column may already exist) ";
        }
    }
}
echo "<br>";

// 2. Create bank_transfer_receipts table
echo "<p>Creating bank_transfer_receipts table...</p>";
$bank_transfer_sql = "
CREATE TABLE IF NOT EXISTS bank_transfer_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sender_name VARCHAR(255),
    transfer_amount DECIMAL(10,2),
    receipt_image VARCHAR(255),
    verification_status VARCHAR(50) DEFAULT 'pending',
    verified_by INT NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_verification_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($bank_transfer_sql)) {
    echo "✓ bank_transfer_receipts table created/verified<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// 3. Create order_reservations table
echo "<p>Creating order_reservations table...</p>";
$reservations_sql = "
CREATE TABLE IF NOT EXISTS order_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    cancelled_at DATETIME NULL,
    converted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($reservations_sql)) {
    echo "✓ order_reservations table created/verified<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// 4. Check orders table structure
echo "<p>Verifying orders table structure...</p>";
$result = $conn->query("SHOW COLUMNS FROM orders");
if ($result) {
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    echo "Orders table columns: " . implode(", ", $columns) . "<br>";
}

echo "<h3 style='color: green;'>✓ Schema setup completed!</h3>";
echo "<p><a href='checkout.php'>Go to Checkout</a></p>";
?>
