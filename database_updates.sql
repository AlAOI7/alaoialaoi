-- =====================================================
-- تحديثات قاعدة البيانات - نظام الطلبات المحسّن
-- =====================================================
-- تشغيل هذا الملف مرة واحدة لتحديث قاعدة البيانات

-- 1. تحديث جدول orders
-- =====================================================

-- إضافة عمود payment_status
ALTER TABLE `orders` 
ADD COLUMN `payment_status` VARCHAR(50) DEFAULT 'pending' AFTER `payment_method`;

-- إضافة عمود reservation_expires_at
ALTER TABLE `orders` 
ADD COLUMN `reservation_expires_at` DATETIME NULL AFTER `status`;

-- تعديل عمود status ليكون أكثر مرونة
ALTER TABLE `orders` 
MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'pending';


-- 2. إنشاء جدول bank_transfer_receipts
-- =====================================================
CREATE TABLE IF NOT EXISTS `bank_transfer_receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `sender_name` VARCHAR(255) DEFAULT NULL,
    `transfer_amount` DECIMAL(10,2) DEFAULT NULL,
    `receipt_image` VARCHAR(255) DEFAULT NULL,
    `verification_status` VARCHAR(50) DEFAULT 'pending',
    `verified_by` INT NULL,
    `verified_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. إنشاء جدول order_reservations
-- =====================================================
CREATE TABLE IF NOT EXISTS `order_reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `status` VARCHAR(50) DEFAULT 'active',
    `cancelled_at` DATETIME NULL,
    `converted_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- انتهى التحديث
-- =====================================================
