-- =============================================
-- SQL SETUP - Be Pretty Store
-- Run this in phpMyAdmin or via mysql CLI
-- =============================================

USE `be_pretty`;

-- 1. جدول المفضلة
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. جدول صور المنتجات المتعددة
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. جدول العملات
CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `exchange_rate` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `is_default` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- بيانات العملات الافتراضية (متوافقة مع الجدول الموجود)
INSERT IGNORE INTO `currencies` (`code`, `name`, `symbol`, `exchange_rate`, `status`) VALUES
('SAR', 'ريال سعودي', 'ر.س', 1.0000, 'active'),
('USD', 'دولار أمريكي', '$', 0.2667, 'active'),
('YER', 'ريال يمني (جديد)', 'ر.ي ج', 66.7500, 'active'),
('YER_OLD', 'ريال يمني (قديم)', 'ر.ي', 667.5000, 'active');

-- 4. جدول أسعار الصرف
CREATE TABLE IF NOT EXISTS `exchange_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `from_currency` varchar(10) NOT NULL,
  `to_currency` varchar(10) NOT NULL,
  `rate` decimal(10,4) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. جدول الكوبونات
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- كوبون تجريبي
INSERT IGNORE INTO `coupons` (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `is_active`) VALUES
('WELCOME10', 'خصم 10% للعملاء الجدد', 'percentage', 10.00, 50.00, 1),
('SAVE20', 'خصم 20 ريال على الطلبات فوق 100', 'fixed', 20.00, 100.00, 1);

-- 6. جدول عناوين التوصيل
CREATE TABLE IF NOT EXISTS `delivery_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. إضافة صورة للفئات إن لم تكن موجودة
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) NULL AFTER `name`;

-- 8. إضافة حقول أسعار متعددة للمنتجات
ALTER TABLE `products` 
  ADD COLUMN IF NOT EXISTS `price_sar` decimal(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `price_usd` decimal(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `price_yer_new` decimal(10,2) DEFAULT NULL COMMENT 'سعر العمله اليمنية الجديدة',
  ADD COLUMN IF NOT EXISTS `price_yer_old` decimal(10,2) DEFAULT NULL COMMENT 'سعر العمله اليمنية القديمة';

-- 9. جدول حوالات التحويل البنكي
CREATE TABLE IF NOT EXISTS `bank_transfer_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `sender_name` varchar(150) DEFAULT NULL,
  `transfer_amount` decimal(10,2) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. جدول حجوزات الطلبات
CREATE TABLE IF NOT EXISTS `order_reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `status` enum('active','expired','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. جدول إعدادات الموقع
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- القيم الافتراضية للإعدادات
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Be Pretty'),
('site_logo', 'img/logo.png'),
('site_favicon', 'img/favicon.ico'),
('background_image', ''),
('footer_text', 'جميع الحقوق محفوظة © Be Pretty 2024'),
('currency', 'SAR'),
('contact_email', 'info@bepretty.com'),
('contact_phone', '+966500000000'),
('whatsapp', '966500000000'),
('address', 'الرياض، المملكة العربية السعودية'),
('facebook', 'https://facebook.com/'),
('instagram', 'https://instagram.com/'),
('twitter', 'https://twitter.com/'),
('snapchat', ''),
('tiktok', ''),
('shipping_cost', '15'),
('free_shipping_min', '200'),
('tax_rate', '0'),
('about_text', 'متجر Be Pretty متخصص في منتجات العناية بالجمال');

-- =============================================
-- تأكد من تشغيل هذا الملف مرة واحدة فقط
-- =============================================
