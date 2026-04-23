-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 05 أكتوبر 2025 الساعة 12:12
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shopingala`
--

-- --------------------------------------------------------

--
-- بنية الجدول `about_us`
--

CREATE TABLE `about_us` (
  `about_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `values` text DEFAULT NULL,
  `core_values` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `about_us`
--

INSERT INTO `about_us` (`about_id`, `title`, `content`, `mission`, `vision`, `values`, `core_values`, `image_url`, `is_active`, `updated_by`, `updated_at`) VALUES
(1, 'من نحن', 'شركة رائدة في التجارة الإلكترونية...', 'تقديم أفضل تجربة للعملاء', 'أن نكون الخيار الأول في السوق', 'الشفافية، الجودة، الالتزام', NULL, 'about_us.jpg', 1, 1, '2025-10-05 13:09:38');

-- --------------------------------------------------------

--
-- بنية الجدول `advertisements`
--

CREATE TABLE `advertisements` (
  `ad_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `ad_position` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `click_count` int(11) DEFAULT 0,
  `impression_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `advertisements`
--

INSERT INTO `advertisements` (`ad_id`, `title`, `description`, `image_url`, `target_url`, `ad_position`, `display_order`, `start_date`, `end_date`, `is_active`, `click_count`, `impression_count`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'خصومات الهواتف', 'خصومات تصل إلى 20% على الهواتف الذكية', 'ads/phones.jpg', 'https://example.com/sale', 'homepage_top', 1, '2025-10-01 00:00:00', '2025-10-31 23:59:59', 1, 0, 0, 1, '2025-10-05 12:50:54', '2025-10-05 12:50:54'),
(2, 'منتجات جديدة', 'اكتشف أحدث المنتجات لدينا', 'ads/new_products.jpg', 'https://example.com/new', 'homepage_sidebar', 2, '2025-10-05 00:00:00', '2025-11-05 23:59:59', 1, 0, 0, 2, '2025-10-05 12:50:54', '2025-10-05 12:50:54');

-- --------------------------------------------------------

--
-- بنية الجدول `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action_type`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'update', 'orders', 'ORD-20251005-00001', '{\"status\":\"pending\"}', '{\"status\":\"confirmed\"}', '192.168.1.10', 'Mozilla/5.0', '2025-10-05 13:05:40'),
(2, 2, 'insert', 'products', '3', NULL, '{\"product_name\":\"ملحقات الكمبيوتر\",\"regular_price\":3000}', '192.168.1.15', 'Mozilla/5.0', '2025-10-05 13:05:40'),
(3, NULL, 'system', 'inventory_logs', '4', NULL, '{\"change_quantity\":5,\"new_quantity\":5}', '127.0.0.1', 'system', '2025-10-05 13:05:40');

-- --------------------------------------------------------

--
-- بنية الجدول `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `account_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'SAR',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bank_accounts`
--

INSERT INTO `bank_accounts` (`account_id`, `bank_name`, `account_name`, `account_number`, `iban`, `branch_name`, `currency`, `is_active`, `created_at`) VALUES
(1, 'Al Ahli Bank', 'Company Main Account', '111222333', 'SA1234567890', 'Sana\'a Branch', 'SAR', 1, '2025-10-05 12:43:46'),
(2, 'Riyadh Bank', 'Company Backup Account', '444555666', 'SA0987654321', 'Aden Branch', 'SAR', 1, '2025-10-05 12:43:46'),
(3, 'Al Ahli Bank', 'Company Main Account', '111222333', 'SA1234567890', 'Sana\'a Branch', 'SAR', 1, '2025-10-05 12:44:11'),
(4, 'Riyadh Bank', 'Company Backup Account', '444555666', 'SA0987654321', 'Aden Branch', 'SAR', 1, '2025-10-05 12:44:11');

-- --------------------------------------------------------

--
-- بنية الجدول `bank_transfers`
--

CREATE TABLE `bank_transfers` (
  `transfer_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `transfer_amount` decimal(15,2) NOT NULL,
  `transfer_date` date DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bank_transfers`
--

INSERT INTO `bank_transfers` (`transfer_id`, `order_id`, `user_id`, `bank_name`, `account_name`, `account_number`, `transfer_amount`, `transfer_date`, `receipt_image`, `reference_number`, `admin_notes`, `status`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20251005-00002', 2, 'Al Ahli Bank', 'Staff One', '123456789', 999.99, '2025-10-05', NULL, 'REF001', NULL, 'pending', NULL, NULL, '2025-10-05 12:43:46', '2025-10-05 12:43:46'),
(2, 'ORD-20251005-00002', 2, 'Al Ahli Bank', 'Staff One', '123456789', 999.99, '2025-10-05', NULL, 'REF001', NULL, 'pending', NULL, NULL, '2025-10-05 12:44:11', '2025-10-05 12:44:11');

-- --------------------------------------------------------

--
-- بنية الجدول `blog_categories`
--

CREATE TABLE `blog_categories` (
  `blog_category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_slug` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `blog_categories`
--

INSERT INTO `blog_categories` (`blog_category_id`, `category_name`, `category_slug`, `description`, `parent_category_id`, `is_active`, `created_at`) VALUES
(1, 'تكنولوجيا', 'technology', 'أحدث الأخبار والمقالات التقنية', NULL, 1, '2025-10-05 12:51:22'),
(2, 'هواتف ذكية', 'smartphones', 'مقالات عن الهواتف الذكية', 1, 1, '2025-10-05 12:51:22'),
(3, 'أجهزة لوحية', 'tablets', 'مراجعات الأجهزة اللوحية', 1, 1, '2025-10-05 12:51:22'),
(4, 'مقالات عامة', 'general-articles', 'مقالات متنوعة غير تقنية', NULL, 1, '2025-10-05 12:51:22');

-- --------------------------------------------------------

--
-- بنية الجدول `blog_posts`
--

CREATE TABLE `blog_posts` (
  `post_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `meta_title` varchar(60) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `blog_posts`
--

INSERT INTO `blog_posts` (`post_id`, `title`, `slug`, `content`, `excerpt`, `author_id`, `category_id`, `featured_image`, `status`, `published_at`, `meta_title`, `meta_description`, `view_count`, `created_at`, `updated_at`) VALUES
(1, 'أفضل الهواتف الذكية لعام 2025', 'best-smartphones-2025', 'مقال شامل عن أفضل الهواتف الذكية...', 'أفضل الهواتف الذكية لعام 2025', 1, 1, 'smartphones2025.jpg', 'published', '2025-10-01 09:00:00', 'أفضل الهواتف 2025', 'مقال عن أفضل الهواتف الذكية المتوفرة في الأسواق لعام 2025', 0, '2025-10-05 12:47:25', '2025-10-05 12:47:25'),
(2, 'كيفية اختيار هاتف مناسب', 'how-to-choose-phone', 'دليل لاختيار الهاتف المناسب...', 'دليل لاختيار الهاتف المناسب', 2, 1, 'choose_phone.jpg', 'published', '2025-10-02 10:00:00', 'اختيار الهاتف المناسب', 'نصائح لاختيار هاتفك الجديد', 0, '2025-10-05 12:47:25', '2025-10-05 12:47:25');

-- --------------------------------------------------------

--
-- بنية الجدول `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `brand_slug` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_slug`, `description`, `logo_url`, `website_url`, `is_active`, `created_at`) VALUES
(1, 'Apple', 'apple', 'شركة أبل للتكنولوجيا', 'apple_logo.png', 'https://www.apple.com', 1, '2025-10-05 12:26:42'),
(2, 'Samsung', 'samsung', 'شركة سامسونج', 'samsung_logo.png', 'https://www.samsung.com', 1, '2025-10-05 12:26:42'),
(3, 'Sony', 'sony', 'شركة سوني', 'sony_logo.png', 'https://www.sony.com', 1, '2025-10-05 12:26:42');

-- --------------------------------------------------------

--
-- بنية الجدول `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `color_id` int(11) DEFAULT NULL,
  `size_id` int(11) DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `color_id`, `size_id`, `added_at`, `updated_at`) VALUES
(1, 3, 1, 1, 1, 1, '2025-10-05 12:35:28', '2025-10-05 12:35:28'),
(2, 3, 2, 2, 3, 3, '2025-10-05 12:35:28', '2025-10-05 12:35:28'),
(3, 2, 1, 1, 2, 2, '2025-10-05 12:35:28', '2025-10-05 12:35:28');

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_slug` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title` varchar(60) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_slug`, `description`, `parent_category_id`, `image_url`, `display_order`, `is_active`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'electronics', 'أجهزة إلكترونية متنوعة', NULL, NULL, 1, 1, NULL, NULL, '2025-10-05 12:26:27', '2025-10-05 12:26:27'),
(2, 'Smartphones', 'smartphones', 'هواتف ذكية', 1, NULL, 2, 1, NULL, NULL, '2025-10-05 12:26:27', '2025-10-05 12:26:27'),
(3, 'Laptops', 'laptops', 'أجهزة الكمبيوتر المحمولة', 1, NULL, 3, 1, NULL, NULL, '2025-10-05 12:26:27', '2025-10-05 12:26:27'),
(4, 'Home Appliances', 'home-appliances', 'أجهزة منزلية', NULL, NULL, 4, 1, NULL, NULL, '2025-10-05 12:26:27', '2025-10-05 12:26:27');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_info`
--

CREATE TABLE `contact_info` (
  `contact_id` int(11) NOT NULL,
  `contact_type` enum('phone','email','address','social') NOT NULL,
  `contact_value` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_info`
--

INSERT INTO `contact_info` (`contact_id`, `contact_type`, `contact_value`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'phone', '+966500000001', 1, 1, '2025-10-05 13:06:20'),
(2, 'email', 'info@example.com', 2, 1, '2025-10-05 13:06:20'),
(3, 'address', 'الرياض، المملكة العربية السعودية', 3, 1, '2025-10-05 13:06:20'),
(4, 'social', 'https://www.facebook.com/example', 4, 1, '2025-10-05 13:06:20');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_requests`
--

CREATE TABLE `contact_requests` (
  `request_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','in_progress','resolved') DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_requests`
--

INSERT INTO `contact_requests` (`request_id`, `full_name`, `email`, `phone`, `subject`, `message`, `status`, `assigned_to`, `response`, `responded_at`, `created_at`) VALUES
(1, 'أحمد علي', 'ahmed@example.com', '+966501234567', 'استفسار عن الطلب', 'أريد معرفة حالة طلبي رقم ORD-20251005-00001', 'new', 2, NULL, NULL, '2025-10-05 13:06:31'),
(2, 'سارة محمد', 'sara@example.com', '+966502345678', 'شكوى', 'تم استلام المنتج تالفاً', 'in_progress', 1, 'تم التواصل مع العميل', '2025-10-06 14:00:00', '2025-10-05 13:06:31');

-- --------------------------------------------------------

--
-- بنية الجدول `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `coupon_type` enum('percentage','fixed','free_shipping') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_amount` decimal(15,2) DEFAULT 0.00,
  `maximum_discount` decimal(15,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `coupon_code`, `coupon_type`, `discount_value`, `minimum_amount`, `maximum_discount`, `usage_limit`, `used_count`, `valid_from`, `valid_until`, `is_active`, `created_by`, `created_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 100.00, 50.00, 100, 0, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1, '2025-10-05 12:35:28'),
(2, 'FLAT50', 'fixed', 50.00, 200.00, 50.00, 50, 0, '2025-01-01 00:00:00', '2025-06-30 23:59:59', 1, 1, '2025-10-05 12:35:28'),
(3, 'FREESHIP', 'free_shipping', 0.00, 0.00, NULL, 100, 0, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, 1, '2025-10-05 12:35:28');

-- --------------------------------------------------------

--
-- بنية الجدول `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 3, 1, '2025-10-05 12:35:28'),
(2, 3, 2, '2025-10-05 12:35:28'),
(3, 2, 1, '2025-10-05 12:35:28');

-- --------------------------------------------------------

--
-- بنية الجدول `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `log_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `change_type` enum('purchase','sale','return','adjustment','damage','transfer') NOT NULL,
  `change_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `reference_type` enum('order','purchase','adjustment','return') NOT NULL,
  `reference_id` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `inventory_logs`
--

INSERT INTO `inventory_logs` (`log_id`, `product_id`, `change_type`, `change_quantity`, `new_quantity`, `reference_type`, `reference_id`, `notes`, `created_by`, `created_at`) VALUES
(13, 1, 'purchase', 50, 50, 'purchase', 'PUR-20251005-0001', 'إضافة أول دفعة من الهواتف الذكية', 2, '2025-10-05 12:57:08'),
(14, 2, 'purchase', 10, 10, 'purchase', 'PUR-20251005-0001', 'إضافة بعض الملحقات', 2, '2025-10-05 12:57:08'),
(15, 3, 'purchase', 20, 20, 'purchase', 'PUR-20251005-0002', 'استلام الملحقات بالكامل', 2, '2025-10-05 12:57:08'),
(16, 4, 'purchase', 5, 5, 'purchase', 'PUR-20251005-0003', 'أجهزة لوحية جديدة', 1, '2025-10-05 12:57:08');

-- --------------------------------------------------------

--
-- بنية الجدول `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `order_id`, `invoice_date`, `due_date`, `total_amount`, `tax_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(5, 'INV-20251005-00001', 'ORD-20251005-00001', '2025-10-05', '2025-10-20', 1999.98, 100.00, 'sent', 'فاتورة الطلب الأول', '2025-10-05 13:03:14', '2025-10-05 13:03:14'),
(6, 'INV-20251005-00002', 'ORD-20251005-00002', '2025-10-05', '2025-10-21', 999.99, 0.00, 'draft', 'فاتورة الطلب الثاني', '2025-10-05 13:03:14', '2025-10-05 13:03:14');

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('email','sms','push','whatsapp','in_app') DEFAULT 'in_app',
  `related_entity_type` enum('order','product','ticket','payment','system') DEFAULT 'system',
  `related_entity_id` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_status` enum('pending','sent','failed') DEFAULT 'pending',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `notification_type`, `related_entity_type`, `related_entity_id`, `is_read`, `sent_status`, `scheduled_at`, `sent_at`, `read_at`, `created_at`) VALUES
(1, 3, 'طلبك تم تأكيده', 'تم تأكيد طلبك ORD-20251005-00001', 'in_app', 'order', 'ORD-20251005-00001', 0, 'sent', '2025-10-05 12:05:00', NULL, NULL, '2025-10-05 12:51:10'),
(2, 3, 'تذكير بالفاتورة', 'لم تقم بدفع فاتورتك ORD-20251005-00002 بعد', 'email', 'order', 'ORD-20251005-00002', 0, 'pending', '2025-10-06 09:00:00', NULL, NULL, '2025-10-05 12:51:10'),
(3, 2, 'رسالة دعم جديدة', 'لديك رسالة جديدة من المستخدم في التذكرة TCK-20251005-0001', 'in_app', 'ticket', 'TCK-20251005-0001', 0, 'pending', '2025-10-05 14:00:00', NULL, NULL, '2025-10-05 12:51:10');

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded','partially_refunded') DEFAULT 'pending',
  `shipping_status` enum('pending','packed','shipped','delivered') DEFAULT 'pending',
  `total_amount` decimal(15,2) NOT NULL,
  `subtotal_amount` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `shipping_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `coupon_id` int(11) DEFAULT NULL,
  `shipping_address_id` int(11) NOT NULL,
  `billing_address_id` int(11) NOT NULL,
  `order_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_status`, `payment_status`, `shipping_status`, `total_amount`, `subtotal_amount`, `tax_amount`, `shipping_amount`, `discount_amount`, `coupon_id`, `shipping_address_id`, `billing_address_id`, `order_notes`, `created_at`, `updated_at`) VALUES
('ORD-20251005-00001', 3, 'confirmed', 'paid', 'shipped', 1999.98, 1899.98, 100.00, 0.00, 50.00, 1, 3, 3, 'الطلب مخصص لتجربة النظام', '2025-10-05 12:35:28', '2025-10-05 12:35:28'),
('ORD-20251005-00002', 2, 'processing', 'pending', 'packed', 999.99, 999.99, 0.00, 0.00, 0.00, NULL, 2, 2, 'طلب الموظف لتجربة النظام', '2025-10-05 12:35:28', '2025-10-05 12:35:28');

-- --------------------------------------------------------

--
-- بنية الجدول `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(15,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `color`, `size`, `subtotal`) VALUES
(1, 'ORD-20251005-00001', 1, 'iPhone 14', 949.99, 1, 'Black', '128GB', 949.99),
(2, 'ORD-20251005-00001', 2, 'Samsung Galaxy S23', 849.99, 2, 'Phantom Black', '128GB', 1699.98),
(3, 'ORD-20251005-00002', 1, 'iPhone 14', 999.99, 1, 'White', '256GB', 999.99),
(4, 'ORD-20251005-00001', 1, 'iPhone 14', 949.99, 1, 'Black', '128GB', 949.99),
(5, 'ORD-20251005-00001', 2, 'Samsung Galaxy S23', 849.99, 2, 'Phantom Black', '128GB', 1699.98),
(6, 'ORD-20251005-00002', 1, 'iPhone 14', 999.99, 1, 'White', '256GB', 999.99);

-- --------------------------------------------------------

--
-- بنية الجدول `order_tracking`
--

CREATE TABLE `order_tracking` (
  `tracking_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `carrier` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_transit','out_for_delivery','delivered','exception') DEFAULT 'pending',
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_tracking`
--

INSERT INTO `order_tracking` (`tracking_id`, `order_id`, `tracking_number`, `carrier`, `status`, `estimated_delivery`, `actual_delivery`, `tracking_url`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20251005-00001', 'TRK123456', 'DHL', 'in_transit', '2025-10-10', NULL, 'http://dhl.com/track/TRK123456', 'تم شحن الطلب من المخزن الرئيسي', '2025-10-05 12:43:46', '2025-10-05 12:43:46'),
(2, 'ORD-20251005-00002', 'TRK654321', 'Aramex', 'pending', '2025-10-12', NULL, 'http://aramex.com/track/TRK654321', 'الطلب قيد التجهيز', '2025-10-05 12:43:46', '2025-10-05 12:43:46'),
(3, 'ORD-20251005-00001', 'TRK123456', 'DHL', 'in_transit', '2025-10-10', NULL, 'http://dhl.com/track/TRK123456', 'تم شحن الطلب من المخزن الرئيسي', '2025-10-05 12:44:11', '2025-10-05 12:44:11'),
(4, 'ORD-20251005-00002', 'TRK654321', 'Aramex', 'pending', '2025-10-12', NULL, 'http://aramex.com/track/TRK654321', 'الطلب قيد التجهيز', '2025-10-05 12:44:11', '2025-10-05 12:44:11');

-- --------------------------------------------------------

--
-- بنية الجدول `order_tracking_events`
--

CREATE TABLE `order_tracking_events` (
  `event_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `status` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_date` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_tracking_events`
--

INSERT INTO `order_tracking_events` (`event_id`, `order_id`, `status`, `description`, `location`, `event_date`, `created_by`) VALUES
(1, 'ORD-20251005-00001', 'Shipped', 'تم شحن الطلب', 'Sana\'a Warehouse', '2025-10-05 12:43:46', 1),
(2, 'ORD-20251005-00001', 'In Transit', 'الطلب في الطريق', 'Aden', '2025-10-05 12:43:46', 1),
(3, 'ORD-20251005-00002', 'Pending', 'الطلب تحت التجهيز', 'Sana\'a Warehouse', '2025-10-05 12:43:46', 2),
(4, 'ORD-20251005-00001', 'Shipped', 'تم شحن الطلب', 'Sana\'a Warehouse', '2025-10-05 12:44:11', 1),
(5, 'ORD-20251005-00001', 'In Transit', 'الطلب في الطريق', 'Aden', '2025-10-05 12:44:11', 1),
(6, 'ORD-20251005-00002', 'Pending', 'الطلب تحت التجهيز', 'Sana\'a Warehouse', '2025-10-05 12:44:11', 2);

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'SAR',
  `status` enum('pending','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_gateway`, `transaction_id`, `amount`, `currency`, `status`, `gateway_response`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20251005-00001', 'credit_card', 'Visa', 'TXN123456', 1999.98, 'SAR', 'completed', NULL, '2025-10-05 12:00:00', '2025-10-05 12:43:46', '2025-10-05 12:43:46'),
(2, 'ORD-20251005-00002', 'bank_transfer', 'Bank', 'TXN654321', 999.99, 'SAR', 'pending', NULL, NULL, '2025-10-05 12:43:46', '2025-10-05 12:43:46'),
(3, 'ORD-20251005-00001', 'credit_card', 'Visa', 'TXN123456', 1999.98, 'SAR', 'completed', NULL, '2025-10-05 12:00:00', '2025-10-05 12:44:11', '2025-10-05 12:44:11'),
(4, 'ORD-20251005-00002', 'bank_transfer', 'Bank', 'TXN654321', 999.99, 'SAR', 'pending', NULL, NULL, '2025-10-05 12:44:11', '2025-10-05 12:44:11');

-- --------------------------------------------------------

--
-- بنية الجدول `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method_type` enum('credit_card','debit_card','bank_transfer','digital_wallet') NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `user_id`, `method_type`, `provider`, `account_number`, `expiry_date`, `is_default`, `is_active`, `created_at`) VALUES
(1, 1, 'credit_card', 'Visa', '4111111111111111', '2026-12-31', 1, 1, '2025-10-05 12:26:27'),
(2, 2, 'debit_card', 'MasterCard', '5500000000000004', '2025-06-30', 1, 1, '2025-10-05 12:26:27'),
(3, 3, 'digital_wallet', 'PayPal', 'customer1@paypal.com', NULL, 1, 1, '2025-10-05 12:26:27');

-- --------------------------------------------------------

--
-- بنية الجدول `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_name`, `description`, `module`) VALUES
(1, 'view_users', 'عرض قائمة المستخدمين', 'users'),
(2, 'edit_users', 'تعديل بيانات المستخدمين', 'users'),
(3, 'delete_users', 'حذف المستخدمين', 'users'),
(4, 'manage_orders', 'إدارة الطلبات', 'orders'),
(5, 'view_products', 'عرض المنتجات', 'products'),
(6, 'add_products', 'إضافة منتجات جديدة', 'products'),
(7, 'manage_inventory', 'إدارة المخزون', 'inventory'),
(8, 'view_reports', 'عرض التقارير', 'reports');

-- --------------------------------------------------------

--
-- بنية الجدول `privacy_policies`
--

CREATE TABLE `privacy_policies` (
  `policy_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `version` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `effective_from` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `privacy_policies`
--

INSERT INTO `privacy_policies` (`policy_id`, `title`, `content`, `version`, `is_active`, `created_by`, `created_at`, `effective_from`) VALUES
(1, 'سياسة الخصوصية العامة', 'نحن نحمي بيانات عملائنا وفقًا لهذه السياسة...', '1.0', 1, 1, '2025-10-05 13:01:07', '2025-10-05 00:00:00'),
(2, 'سياسة ملفات تعريف الارتباط', 'نستخدم الكوكيز لتحسين تجربة المستخدم...', '1.0', 1, 2, '2025-10-05 13:01:07', '2025-10-05 00:00:00'),
(3, 'سياسة الخصوصية العامة', 'نحن نحمي بيانات عملائنا وفقًا لهذه السياسة...', '1.0', 1, 1, '2025-10-05 13:01:30', '2025-10-05 00:00:00'),
(4, 'سياسة ملفات تعريف الارتباط', 'نستخدم الكوكيز لتحسين تجربة المستخدم...', '1.0', 1, 2, '2025-10-05 13:01:30', '2025-10-05 00:00:00');

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_slug` varchar(300) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `regular_price` decimal(15,2) NOT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `main_image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_best_seller` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `tax_status` enum('taxable','shipping','none') DEFAULT 'taxable',
  `tax_class` varchar(50) DEFAULT NULL,
  `meta_title` varchar(60) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `product_slug`, `description`, `short_description`, `sku`, `barcode`, `category_id`, `brand_id`, `supplier_id`, `regular_price`, `sale_price`, `cost_price`, `stock_quantity`, `low_stock_threshold`, `weight`, `dimensions`, `main_image_url`, `is_featured`, `is_new`, `is_best_seller`, `is_active`, `tax_status`, `tax_class`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 14', 'iphone-14', 'هاتف أيفون 14 بمواصفات رائعة', 'أحدث هواتف أبل', 'SKU001', '123456789012', 2, 1, 1, 999.99, 949.99, 700.00, 50, 5, 0.17, '14x7x0.7', 'iphone14_main.jpg', 1, 1, 1, 1, 'taxable', 'standard', 'iPhone 14', 'أفضل هاتف أبل لعام 2024', '2025-10-05 12:31:19', '2025-10-05 12:31:19'),
(2, 'Samsung Galaxy S23', 'samsung-galaxy-s23', 'هاتف سامسونج جلاكسي S23', 'أحدث هواتف سامسونج', 'SKU002', '987654321098', 2, 2, 2, 899.99, 849.99, 650.00, 40, 5, 0.17, '14x7x0.8', 'galaxy_s23_main.jpg', 1, 1, 0, 1, 'taxable', 'standard', 'Galaxy S23', 'هاتف سامسونج الرائد', '2025-10-05 12:31:19', '2025-10-05 12:31:19'),
(3, 'هاتف ذكي A', 'smartphone-a', 'وصف الهاتف A', NULL, 'SKU101', NULL, 1, NULL, NULL, 4000.00, NULL, NULL, 50, 5, NULL, NULL, NULL, 0, 0, 0, 1, 'taxable', NULL, NULL, NULL, '2025-10-05 12:55:40', '2025-10-05 12:56:25'),
(4, 'هاتف ذكي B', 'smartphone-b', 'وصف الهاتف B', NULL, 'SKU102', NULL, 1, NULL, NULL, 1000.00, NULL, NULL, 10, 5, NULL, NULL, NULL, 0, 0, 0, 1, 'taxable', NULL, NULL, NULL, '2025-10-05 12:55:40', '2025-10-05 12:56:34'),
(5, 'ملحقات الكمبيوتر', 'computer-accessories', 'وصف الملحقات', NULL, 'SKU103', NULL, 2, NULL, NULL, 3000.00, NULL, NULL, 20, 5, NULL, NULL, NULL, 0, 0, 0, 1, 'taxable', NULL, NULL, NULL, '2025-10-05 12:55:40', '2025-10-05 12:56:44'),
(6, 'جهاز لوحي X', 'tablet-x', 'وصف الجهاز اللوحي X', NULL, 'SKU104', NULL, 3, NULL, NULL, 1500.00, NULL, NULL, 5, 5, NULL, NULL, NULL, 0, 0, 0, 1, 'taxable', NULL, NULL, NULL, '2025-10-05 12:55:40', '2025-10-05 12:56:52');

-- --------------------------------------------------------

--
-- بنية الجدول `product_colors`
--

CREATE TABLE `product_colors` (
  `color_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(7) DEFAULT NULL,
  `additional_price` decimal(10,2) DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_colors`
--

INSERT INTO `product_colors` (`color_id`, `product_id`, `color_name`, `color_code`, `additional_price`, `stock_quantity`, `is_active`) VALUES
(1, 1, 'Black', '#000000', 0.00, 20, 1),
(2, 1, 'White', '#FFFFFF', 0.00, 15, 1),
(3, 2, 'Phantom Black', '#111111', 0.00, 25, 1),
(4, 2, 'Green', '#00FF00', 0.00, 10, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `product_discounts`
--

CREATE TABLE `product_discounts` (
  `discount_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `min_quantity` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_discounts`
--

INSERT INTO `product_discounts` (`discount_id`, `product_id`, `discount_type`, `discount_value`, `start_date`, `end_date`, `min_quantity`, `is_active`, `created_by`, `created_at`) VALUES
(4, 1, 'percentage', 10.00, '2025-10-05 00:00:00', '2025-10-31 23:59:59', 1, 1, 2, '2025-10-05 12:57:08'),
(5, 2, 'fixed', 50.00, '2025-10-01 00:00:00', '2025-10-20 23:59:59', 1, 1, 2, '2025-10-05 12:57:08'),
(6, 3, 'percentage', 15.00, '2025-10-05 00:00:00', '2025-11-05 23:59:59', 5, 1, 1, '2025-10-05 12:57:08');

-- --------------------------------------------------------

--
-- بنية الجدول `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `alt_text`, `display_order`, `is_primary`, `created_at`) VALUES
(1, 1, 'iphone14_1.jpg', 'iPhone 14 front view', 1, 1, '2025-10-05 12:31:20'),
(2, 1, 'iphone14_2.jpg', 'iPhone 14 back view', 2, 0, '2025-10-05 12:31:20'),
(3, 2, 'galaxy_s23_1.jpg', 'Samsung Galaxy S23 front', 1, 1, '2025-10-05 12:31:20'),
(4, 2, 'galaxy_s23_2.jpg', 'Samsung Galaxy S23 back', 2, 0, '2025-10-05 12:31:20');

-- --------------------------------------------------------

--
-- بنية الجدول `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_reviews`
--

INSERT INTO `product_reviews` (`review_id`, `product_id`, `user_id`, `rating`, `title`, `comment`, `is_approved`, `helpful_count`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 5, 'مذهل', 'أفضل هاتف استخدمته على الإطلاق', 1, 10, '2025-10-05 12:31:20', '2025-10-05 12:31:20'),
(2, 2, 2, 4, 'جيد', 'هاتف ممتاز لكن السعر مرتفع قليلاً', 1, 5, '2025-10-05 12:31:20', '2025-10-05 12:31:20');

-- --------------------------------------------------------

--
-- بنية الجدول `product_sizes`
--

CREATE TABLE `product_sizes` (
  `size_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_name` varchar(50) NOT NULL,
  `size_description` varchar(100) DEFAULT NULL,
  `additional_price` decimal(10,2) DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `weight` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_sizes`
--

INSERT INTO `product_sizes` (`size_id`, `product_id`, `size_name`, `size_description`, `additional_price`, `stock_quantity`, `weight`, `is_active`) VALUES
(1, 1, '128GB', 'سعة 128 جيجابايت', 0.00, 30, 0.17, 1),
(2, 1, '256GB', 'سعة 256 جيجابايت', 100.00, 20, 0.17, 1),
(3, 2, '128GB', 'سعة 128 جيجابايت', 0.00, 25, 0.17, 1),
(4, 2, '256GB', 'سعة 256 جيجابايت', 80.00, 15, 0.17, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','ordered','received','cancelled','partial') DEFAULT 'pending',
  `payment_status` enum('pending','paid','partial') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `purchase_number`, `supplier_id`, `purchase_date`, `expected_delivery_date`, `actual_delivery_date`, `total_amount`, `status`, `payment_status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PUR-20251005-0001', 1, '2025-10-01', '2025-10-10', NULL, 5000.00, 'pending', 'pending', 'شراء أول دفعة من الهواتف الذكية', 2, '2025-10-05 12:52:09', '2025-10-05 12:52:09'),
(2, 'PUR-20251005-0002', 2, '2025-10-03', '2025-10-12', '2025-10-11', 3000.00, 'received', 'paid', 'شراء ملحقات للكمبيوتر', 2, '2025-10-05 12:52:09', '2025-10-05 12:52:09'),
(3, 'PUR-20251005-0003', 3, '2025-10-04', '2025-10-15', NULL, 1500.00, 'ordered', 'partial', 'شراء أجهزة لوحية جديدة', 1, '2025-10-05 12:52:09', '2025-10-05 12:52:09');

-- --------------------------------------------------------

--
-- بنية الجدول `purchase_items`
--

CREATE TABLE `purchase_items` (
  `purchase_item_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `total_cost` decimal(15,2) NOT NULL,
  `received_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `purchase_items`
--

INSERT INTO `purchase_items` (`purchase_item_id`, `purchase_id`, `product_id`, `quantity`, `unit_cost`, `total_cost`, `received_quantity`) VALUES
(17, 1, 1, 50, 80.00, 4000.00, 0),
(18, 1, 2, 10, 100.00, 1000.00, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `reports`
--

INSERT INTO `reports` (`report_id`, `report_type`, `report_name`, `report_data`, `date_from`, `date_to`, `generated_by`, `generated_at`) VALUES
(1, 'sales', 'تقرير مبيعات أكتوبر', '{\"total_sales\":50000,\"orders\":25}', '2025-10-01', '2025-10-31', 1, '2025-10-05 13:05:54'),
(2, 'returns', 'تقرير المرتجعات', '{\"total_returns\":5,\"total_refunded\":2000}', '2025-10-01', '2025-10-31', 2, '2025-10-05 13:05:54');

-- --------------------------------------------------------

--
-- بنية الجدول `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_reason` text NOT NULL,
  `return_status` enum('requested','approved','rejected','received','refunded') DEFAULT 'requested',
  `refund_amount` decimal(15,2) DEFAULT NULL,
  `refund_method` varchar(50) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `returns`
--

INSERT INTO `returns` (`return_id`, `return_number`, `order_id`, `user_id`, `return_reason`, `return_status`, `refund_amount`, `refund_method`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(3, 'RET-20251005-0001', 'ORD-20251005-00001', 1, 'منتج معيب', 'approved', 400.00, 'bank_transfer', 2, '2025-10-06 10:00:00', '2025-10-05 13:03:43', '2025-10-05 13:03:43'),
(4, 'RET-20251005-0002', 'ORD-20251005-00002', 2, 'استلام منتج خاطئ', 'requested', NULL, NULL, NULL, NULL, '2025-10-05 13:03:43', '2025-10-05 13:03:43');

-- --------------------------------------------------------

--
-- بنية الجدول `return_items`
--

CREATE TABLE `return_items` (
  `return_item_id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `item_condition` enum('new','opened','damaged','used') DEFAULT 'new',
  `restocking_fee` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `return_items`
--

INSERT INTO `return_items` (`return_item_id`, `return_id`, `order_item_id`, `quantity`, `reason`, `item_condition`, `restocking_fee`) VALUES
(4, 3, 1, 1, 'منتج معيب عند الاستلام', 'damaged', 50.00),
(5, 3, 2, 2, 'تم فتح المنتج بدون استخدام', 'opened', 20.00),
(6, 4, 3, 1, 'استلام منتج خاطئ', 'new', 0.00);

-- --------------------------------------------------------

--
-- بنية الجدول `review_likes`
--

CREATE TABLE `review_likes` (
  `like_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `review_likes`
--

INSERT INTO `review_likes` (`like_id`, `review_id`, `user_id`, `created_at`) VALUES
(1, 1, 2, '2025-10-05 12:31:20'),
(2, 1, 1, '2025-10-05 12:31:20'),
(3, 2, 1, '2025-10-05 12:31:20'),
(4, 2, 3, '2025-10-05 12:31:20');

-- --------------------------------------------------------

--
-- بنية الجدول `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_by`, `created_at`) VALUES
(1, 'Administrator', 'الدور الإداري الكامل', 1, '2025-10-05 12:22:11'),
(2, 'Staff', 'دور الموظف العادي', 2, '2025-10-05 12:22:11'),
(3, 'Customer', 'دور العميل العادي', 3, '2025-10-05 12:22:11');

-- --------------------------------------------------------

--
-- بنية الجدول `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_at` datetime DEFAULT current_timestamp(),
  `granted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role_id`, `permission_id`, `granted_at`, `granted_by`) VALUES
(1, 1, 1, '2025-10-05 12:22:23', 1),
(2, 1, 2, '2025-10-05 12:22:23', 1),
(3, 1, 3, '2025-10-05 12:22:23', 1),
(4, 1, 4, '2025-10-05 12:22:23', 1),
(5, 1, 5, '2025-10-05 12:22:23', 1),
(6, 2, 1, '2025-10-05 12:22:23', 2),
(7, 2, 2, '2025-10-05 12:22:23', 2);

-- --------------------------------------------------------

--
-- بنية الجدول `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json','text') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `site_settings`
--

INSERT INTO `site_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'متجرك', 'string', 'اسم الموقع', NULL, '2025-10-05 10:47:45'),
(2, 'site_description', 'وصف متجرك', 'string', 'وصف الموقع', NULL, '2025-10-05 10:47:45'),
(3, 'site_currency', 'SAR', 'string', 'العملة الافتراضية', NULL, '2025-10-05 10:47:45'),
(4, 'site_language', 'ar', 'string', 'لغة الموقع', NULL, '2025-10-05 10:47:45'),
(5, 'admin_email', 'admin@yourstore.com', 'string', 'البريد الإلكتروني للإدارة', NULL, '2025-10-05 10:47:45'),
(6, 'items_per_page', '20', 'string', 'عدد العناصر في كل صفحة', NULL, '2025-10-05 10:47:45');

-- --------------------------------------------------------

--
-- بنية الجدول `social_media`
--

CREATE TABLE `social_media` (
  `social_id` int(11) NOT NULL,
  `platform_name` varchar(50) NOT NULL,
  `profile_url` varchar(255) NOT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `social_media`
--

INSERT INTO `social_media` (`social_id`, `platform_name`, `profile_url`, `icon_class`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Facebook', 'https://www.facebook.com/example', 'fab fa-facebook-f', 1, 1, '2025-10-05 13:06:41'),
(2, 'Twitter', 'https://twitter.com/example', 'fab fa-twitter', 2, 1, '2025-10-05 13:06:41'),
(3, 'Instagram', 'https://www.instagram.com/example', 'fab fa-instagram', 3, 1, '2025-10-05 13:06:41'),
(4, 'LinkedIn', 'https://www.linkedin.com/company/example', 'fab fa-linkedin-in', 4, 1, '2025-10-05 13:06:41');

-- --------------------------------------------------------

--
-- بنية الجدول `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `email`, `phone`, `address`, `tax_number`, `current_balance`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Tech Distributors', 'Ali Ahmed', 'techdist@example.com', '+967770010001', 'Sana\'a, Yemen', 'TAX001', 10000.00, 1, '2025-10-05 12:26:42', '2025-10-05 12:26:42'),
(2, 'Gadget Supplies', 'Fatima Hassan', 'gadgets@example.com', '+967770010002', 'Aden, Yemen', 'TAX002', 5000.00, 1, '2025-10-05 12:26:42', '2025-10-05 12:26:42'),
(3, 'Home Goods Co.', 'Mohammed Khalid', 'homegoods@example.com', '+967770010003', 'Taiz, Yemen', 'TAX003', 8000.00, 1, '2025-10-05 12:26:42', '2025-10-05 12:26:42');

-- --------------------------------------------------------

--
-- بنية الجدول `support_tickets`
--

CREATE TABLE `support_tickets` (
  `ticket_id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department` enum('sales','technical','billing','general') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','waiting_customer','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `support_tickets`
--

INSERT INTO `support_tickets` (`ticket_id`, `ticket_number`, `user_id`, `subject`, `description`, `department`, `priority`, `status`, `assigned_to`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 'TCK-20251005-0001', 3, 'مشاكل تسجيل الدخول', 'لا أستطيع تسجيل الدخول إلى حسابي', 'technical', 'high', 'open', 2, '2025-10-05 12:50:28', '2025-10-05 12:50:28', NULL),
(2, 'TCK-20251005-0002', 3, 'استفسار عن الفاتورة', 'أريد نسخة من فاتورتي الأخيرة', 'billing', 'medium', 'in_progress', 2, '2025-10-05 12:50:28', '2025-10-05 12:50:28', NULL),
(3, 'TCK-20251005-0003', 2, 'اقتراح تحسين الموقع', 'أقترح إضافة فلتر جديد للمنتجات', 'general', 'low', 'open', NULL, '2025-10-05 12:50:28', '2025-10-05 12:50:28', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `terms_conditions`
--

CREATE TABLE `terms_conditions` (
  `term_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `version` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `effective_from` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `terms_conditions`
--

INSERT INTO `terms_conditions` (`term_id`, `title`, `content`, `version`, `is_active`, `created_by`, `created_at`, `effective_from`) VALUES
(1, 'الشروط والأحكام العامة', 'هذه هي الشروط والأحكام العامة لمتجري...', '1.0', 1, 1, '2025-10-05 13:01:07', '2025-10-05 00:00:00'),
(2, 'شروط الاستخدام للخدمات', 'شروط استخدام الخدمات المقدمة...', '1.1', 1, 2, '2025-10-05 13:01:07', '2025-10-05 00:00:00'),
(3, 'الشروط والأحكام العامة', 'هذه هي الشروط والأحكام العامة لمتجري...', '1.0', 1, 1, '2025-10-05 13:01:30', '2025-10-05 00:00:00'),
(4, 'شروط الاستخدام للخدمات', 'شروط استخدام الخدمات المقدمة...', '1.1', 1, 2, '2025-10-05 13:01:30', '2025-10-05 00:00:00');

-- --------------------------------------------------------

--
-- بنية الجدول `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `message_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_type` enum('text','image','file','system') DEFAULT 'text',
  `message_text` text DEFAULT NULL,
  `attachment_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `ticket_messages`
--

INSERT INTO `ticket_messages` (`message_id`, `ticket_id`, `sender_id`, `message_type`, `message_text`, `attachment_url`, `is_read`, `read_at`, `created_at`) VALUES
(1, 1, 3, 'text', 'مرحباً، لدي مشكلة في تسجيل الدخول.', NULL, 0, NULL, '2025-10-05 12:50:42'),
(2, 1, 2, 'text', 'شكراً لتواصلك. سنقوم بمراجعة المشكلة فوراً.', NULL, 0, NULL, '2025-10-05 12:50:42'),
(3, 2, 3, 'text', 'أحتاج نسخة من الفاتورة رقم 12345.', NULL, 0, NULL, '2025-10-05 12:50:42'),
(4, 2, 2, 'text', 'تم إرسال الفاتورة إلى بريدك الإلكتروني.', NULL, 0, NULL, '2025-10-05 12:50:42');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('customer','admin','staff') DEFAULT 'customer',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `user_type`, `first_name`, `last_name`, `phone`, `date_of_birth`, `registration_date`, `last_login`, `status`, `email_verified`, `phone_verified`) VALUES
(1, 'ahmed.saleh', 'hashed_pass_ahmed1', 'ahmed.saleh@email.com', 'customer', 'أحمد', 'صالح', '+966501234567', '1990-05-15', '2025-10-05 12:03:17', '2024-01-10 14:30:00', 'active', 1, 0),
(2, 'fatima.ali', 'hashed_pass_fatima1', 'fatima.ali@email.com', 'customer', 'فاطمة', 'علي', '+966502345678', '1992-08-22', '2025-10-05 12:03:17', '2024-01-09 10:15:00', 'active', 1, 1),
(3, 'admin.root', 'hashed_pass_admin1', 'admin@store.com', 'admin', 'إداري', 'رئيسي', '+966503456789', NULL, '2025-10-05 12:03:17', '2024-01-11 09:00:00', 'active', 1, 1),
(4, 'staff.manager', 'hashed_pass_staff1', 'manager@store.com', 'staff', 'مدير', 'فريق', '+966504567890', '1985-03-10', '2025-10-05 12:03:17', '2024-01-08 16:45:00', 'active', 1, 0),
(5, 'khalid.omar', 'hashed_pass_khalid1', 'khalid.omar@email.com', 'customer', 'خالد', 'عمر', '+966505678901', '1988-11-30', '2025-10-05 12:03:17', '2024-01-07 12:20:00', 'inactive', 0, 1),
(6, 'noura.hassan', 'hashed_pass_noura1', 'noura.hassan@email.com', 'customer', 'نورة', 'حسن', '+966506789012', '1995-02-14', '2025-10-05 12:03:17', '2024-01-06 18:10:00', 'suspended', 1, 1),
(7, 'mohammed.yousuf', 'hashed_pass_mohammed1', 'mohammed.yousuf@email.com', 'customer', 'محمد', 'يوسف', '+966507890123', '1991-07-25', '2025-10-05 12:03:17', '2024-01-05 11:55:00', 'active', 1, 0),
(8, 'sara.khan', 'hashed_pass_sara1', 'sara.khan@email.com', 'customer', 'سارة', 'خان', '+966508901234', '1993-12-05', '2025-10-05 12:03:17', '2024-01-04 15:40:00', 'active', 0, 1),
(9, 'tech.support', 'hashed_pass_tech1', 'support@store.com', 'staff', 'دعم', 'تقني', '+966509012345', NULL, '2025-10-05 12:03:17', '2024-01-03 13:25:00', 'active', 1, 1),
(10, 'buyer.test', 'hashed_pass_test1', 'test.buyer@email.com', 'customer', 'مختبر', 'مشتري', '+966510123456', '1980-01-01', '2025-10-05 12:03:17', NULL, 'active', 0, 0),
(11, 'admin1', 'pass1', 'admin1@example.com', 'admin', 'Admin', 'One', '+967770000001', '1980-01-01', '2025-10-05 12:14:08', NULL, 'active', 1, 1),
(12, 'staff1', 'pass2', 'staff1@example.com', 'staff', 'Staff', 'One', '+967770000002', '1990-02-15', '2025-10-05 12:14:08', NULL, 'active', 1, 0),
(13, 'customer1', 'pass3', 'customer1@example.com', 'customer', 'Customer', 'One', '+967770000003', '1995-05-20', '2025-10-05 12:14:08', NULL, 'active', 0, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('home','work','billing','shipping') DEFAULT 'home',
  `recipient_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `street_address` text DEFAULT NULL,
  `building_number` varchar(20) DEFAULT NULL,
  `apartment_number` varchar(20) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `user_id`, `address_type`, `recipient_name`, `phone`, `country`, `city`, `district`, `street_address`, `building_number`, `apartment_number`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'home', 'Admin One', '+967770000001', 'Yemen', 'Sana\'a', 'Al-Mansoura', 'Street 1', '12', '101', '10001', 1, '2025-10-05 12:26:12', '2025-10-05 12:26:12'),
(2, 2, 'work', 'Staff One', '+967770000002', 'Yemen', 'Aden', 'Crater', 'Street 5', '20', '202', '20002', 1, '2025-10-05 12:26:12', '2025-10-05 12:26:12'),
(3, 3, 'billing', 'Customer One', '+967770000003', 'Yemen', 'Taiz', 'Al-Qahera', 'Street 10', '5', '301', '30003', 1, '2025-10-05 12:26:12', '2025-10-05 12:26:12');

-- --------------------------------------------------------

--
-- بنية الجدول `user_roles`
--

CREATE TABLE `user_roles` (
  `user_role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_roles`
--

INSERT INTO `user_roles` (`user_role_id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`) VALUES
(1, 1, 1, '2025-10-05 12:24:23', 1),
(2, 2, 2, '2025-10-05 12:24:23', 1),
(3, 3, 3, '2025-10-05 12:24:23', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`about_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `advertisements`
--
ALTER TABLE `advertisements`
  ADD PRIMARY KEY (`ad_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `bank_transfers`
--
ALTER TABLE `bank_transfers`
  ADD PRIMARY KEY (`transfer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`blog_category_id`),
  ADD UNIQUE KEY `category_slug` (`category_slug`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`),
  ADD UNIQUE KEY `brand_slug` (`brand_slug`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `color_id` (`color_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_slug` (`category_slug`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_user_favorite` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`),
  ADD KEY `billing_address_id` (`billing_address_id`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_tracking_events`
--
ALTER TABLE `order_tracking_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `privacy_policies`
--
ALTER TABLE `privacy_policies`
  ADD PRIMARY KEY (`policy_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_slug` (`product_slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_active` (`is_active`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`color_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_discounts`
--
ALTER TABLE `product_discounts`
  ADD PRIMARY KEY (`discount_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_product_user_review` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`size_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD UNIQUE KEY `purchase_number` (`purchase_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`purchase_item_id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`return_item_id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_review_like` (`review_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `granted_by` (`granted_by`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `social_media`
--
ALTER TABLE `social_media`
  ADD PRIMARY KEY (`social_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_tickets_status` (`status`);

--
-- Indexes for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  ADD PRIMARY KEY (`term_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_role_id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us`
--
ALTER TABLE `about_us`
  MODIFY `about_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `advertisements`
--
ALTER TABLE `advertisements`
  MODIFY `ad_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bank_transfers`
--
ALTER TABLE `bank_transfers`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `blog_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_tracking_events`
--
ALTER TABLE `order_tracking_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `privacy_policies`
--
ALTER TABLE `privacy_policies`
  MODIFY `policy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_discounts`
--
ALTER TABLE `product_discounts`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `return_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `review_likes`
--
ALTER TABLE `review_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `social_media`
--
ALTER TABLE `social_media`
  MODIFY `social_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `terms_conditions`
--
ALTER TABLE `terms_conditions`
  MODIFY `term_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `user_role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `about_us`
--
ALTER TABLE `about_us`
  ADD CONSTRAINT `about_us_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `advertisements`
--
ALTER TABLE `advertisements`
  ADD CONSTRAINT `advertisements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- قيود الجداول `bank_transfers`
--
ALTER TABLE `bank_transfers`
  ADD CONSTRAINT `bank_transfers_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `bank_transfers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bank_transfers_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD CONSTRAINT `blog_categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `blog_categories` (`blog_category_id`);

--
-- قيود الجداول `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `blog_posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- قيود الجداول `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`color_id`) REFERENCES `product_colors` (`color_id`),
  ADD CONSTRAINT `cart_ibfk_4` FOREIGN KEY (`size_id`) REFERENCES `product_sizes` (`size_id`);

--
-- قيود الجداول `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`category_id`);

--
-- قيود الجداول `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD CONSTRAINT `contact_requests_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- قيود الجداول `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- قيود الجداول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_address_id`) REFERENCES `user_addresses` (`address_id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`billing_address_id`) REFERENCES `user_addresses` (`address_id`);

--
-- قيود الجداول `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- قيود الجداول `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- قيود الجداول `order_tracking_events`
--
ALTER TABLE `order_tracking_events`
  ADD CONSTRAINT `order_tracking_events_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- قيود الجداول `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- قيود الجداول `privacy_policies`
--
ALTER TABLE `privacy_policies`
  ADD CONSTRAINT `privacy_policies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- قيود الجداول `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- قيود الجداول `product_discounts`
--
ALTER TABLE `product_discounts`
  ADD CONSTRAINT `product_discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_discounts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- قيود الجداول `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- قيود الجداول `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- قيود الجداول `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- قيود الجداول `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`return_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`);

--
-- قيود الجداول `review_likes`
--
ALTER TABLE `review_likes`
  ADD CONSTRAINT `review_likes_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`review_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- قيود الجداول `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `site_settings`
--
ALTER TABLE `site_settings`
  ADD CONSTRAINT `site_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `terms_conditions`
--
ALTER TABLE `terms_conditions`
  ADD CONSTRAINT `terms_conditions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- قيود الجداول `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
