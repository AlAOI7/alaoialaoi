-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 06 يناير 2026 الساعة 19:59
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `be_pretty`
--

-- --------------------------------------------------------

--
-- بنية الجدول `banks`
--

CREATE TABLE `banks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `banks`
--

INSERT INTO `banks` (`id`, `name`, `logo`, `description`, `website`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ala', 'banks_logos/1765378342_6939892647388.png', 'يب', 'https://github.com/AlAOI7/Real-Estate', 'active', '2025-12-10 14:52:22', '2025-12-10 14:52:22'),
(2, 'علاء فيصل علي الحاج عبدالله', 'banks_logos/1765378524_693989dc58885.jfif', 'علاء فيصل علي الحاج عبدالله', 'https://github.com/AlAOI7/Real-Estate', 'active', '2025-12-10 14:55:24', '2025-12-10 14:55:24');

-- --------------------------------------------------------

--
-- بنية الجدول `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `account_holder` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'SAR',
  `iban` varchar(50) DEFAULT NULL,
  `swift_code` varchar(20) DEFAULT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','pending','inactive') DEFAULT 'pending',
  `is_primary` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `bank_id`, `account_number`, `account_holder`, `currency`, `iban`, `swift_code`, `branch_name`, `balance`, `status`, `is_primary`, `notes`, `created_at`, `updated_at`) VALUES
(7, 1, '123213413', 'عل', 'SAR', '3242432543245', 'fdsf32', 'fg', 33.00, 'active', 1, 'dsfsd', '2025-12-10 16:06:34', '2025-12-10 16:06:34'),
(8, 1, '32453254', 'cxzvx', 'YER', '3453', 'xcvcz43', 'f', 343.00, 'active', 0, 'sdf', '2025-12-10 16:06:34', '2025-12-10 16:06:34');

-- --------------------------------------------------------

--
-- بنية الجدول `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `category_id` int(11) NOT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `publish_date` date NOT NULL,
  `status` enum('published','draft','scheduled') DEFAULT 'draft',
  `total_price` decimal(12,2) DEFAULT 0.00,
  `views_count` int(11) DEFAULT 0,
  `shares_count` int(11) DEFAULT 0,
  `sales_from_blog` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `summary`, `content`, `category_id`, `main_image`, `publish_date`, `status`, `total_price`, `views_count`, `shares_count`, `sales_from_blog`, `created_at`, `updated_at`) VALUES
(1, 'روتين العناية بالبشرة', 'أفضل النصائح للعناية بالبشرة', 'محتوى طويل حول العناية بالبشرة...', 1, 'blog_images/skin_routine.jpg', '2025-11-23', 'published', 0.00, 100, 10, 5, '2025-11-23 08:10:38', '2025-11-23 08:10:38'),
(2, 'عطور فاخرة لكل مناسبة', 'اختيار العطر المناسب', 'محتوى عن العطور...', 2, 'blog_images/perfumes.jpg', '2025-11-22', 'published', 0.00, 50, 5, 2, '2025-11-23 08:10:38', '2025-11-23 08:10:38'),
(3, 'قناع الطين وفوائده', 'فوائد قناع الطين للبشرة', 'محتوى مفصل عن القناع...', 1, 'blog_images/clay_mask.jpg', '2025-11-21', 'draft', 0.00, 30, 2, 0, '2025-11-23 08:10:38', '2025-11-23 08:10:38'),
(4, 'سشب', 'شب', 'شبصث', 6, 'blog_images/1765382263_تنزيل.jfif', '2025-12-10', 'published', 58.50, 0, 0, 0, '2025-12-10 15:57:43', '2025-12-10 15:57:44');

-- --------------------------------------------------------

--
-- بنية الجدول `blog_images`
--

CREATE TABLE `blog_images` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `blog_images`
--

INSERT INTO `blog_images` (`id`, `blog_id`, `image_path`, `sort_order`, `created_at`) VALUES
(1, 1, 'blog_additional_images/skin_routine_1.jpg', 1, '2025-11-23 08:12:53'),
(2, 1, 'blog_additional_images/skin_routine_2.jpg', 2, '2025-11-23 08:12:53'),
(3, 2, 'blog_additional_images/perfumes_1.jpg', 1, '2025-11-23 08:12:53'),
(4, 3, 'blog_additional_images/clay_mask_1.jpg', 1, '2025-11-23 08:12:53'),
(5, 4, 'blog_additional_images/1765382263_0_تنزيل (1).jfif', 0, '2025-12-10 15:57:44');

-- --------------------------------------------------------

--
-- بنية الجدول `blog_products`
--

CREATE TABLE `blog_products` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `blog_products`
--

INSERT INTO `blog_products` (`id`, `blog_id`, `product_id`, `sort_order`, `created_at`) VALUES
(1, 4, 9, 0, '2025-12-10 15:57:44');

-- --------------------------------------------------------

--
-- بنية الجدول `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `products_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo`, `country`, `website`, `status`, `description`, `products_count`, `created_at`, `updated_at`) VALUES
(1, 'بيوتي كير', '/images/brands/beauty-care-logo.png', 'فرنسا', 'https://beauty-care.com', 'active', 'علامة تجارية رائدة في مستحضرات التجميل والعناية بالبشرة', 2, '2025-11-22 19:31:31', '2025-11-22 19:31:31'),
(2, 'باريس بريفيوم', '/images/brands/paris-prefume-logo.png', 'فرنسا', 'https://paris-prefume.com', 'active', 'عطور فاخرة من باريس برائحة فرنسية أصيلة', 1, '2025-11-22 19:31:31', '2025-11-22 19:31:31'),
(3, 'ناتشرال واي', '/images/brands/natural-way-logo.png', 'المملكة المتحدة', 'https://natural-way.com', 'active', 'منتجات طبيعية وعضوية للعناية بالبشرة', 1, '2025-11-22 19:31:31', '2025-11-22 19:31:31'),
(4, 'أروماثيرابي', '/images/brands/aromatherapy-logo.png', 'أستراليا', 'https://aromatherapy.com', 'active', 'زيوت عطرية نقية للعلاج والاسترخاء', 1, '2025-11-22 19:31:31', '2025-11-22 19:31:31'),
(5, 'ala', '', 'CN', 'https://github.com/AlAOI7/Real-Estate', 'active', 'alaoi', 0, '2025-12-10 13:44:24', '2025-12-10 13:44:24'),
(6, 'Default', NULL, 'JP', 'https://github.com/AlAOI7/Real-Estate', 'active', 'س', 0, '2025-12-10 14:30:07', '2025-12-10 14:30:07'),
(7, 'alaaa', NULL, 'SA', 'https://sss', 'active', 'ss', 0, '2025-12-30 18:37:57', '2025-12-30 18:37:57');

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `type` enum('product','blog') DEFAULT 'product'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`, `image`, `status`, `created_at`, `updated_at`, `is_active`, `type`) VALUES
(1, 'العناية بالبشرة 4', NULL, 'uploads/1765372000_تنزيل (1).jfif', 'active', '2025-11-22 19:31:30', '2025-12-10 13:06:58', 1, 'product'),
(2, 'العطور', NULL, '/images/categories/perfumes.jpg', 'active', '2025-11-22 19:31:30', '2025-11-22 19:31:30', 1, 'product'),
(3, 'الزيوت العطرية', NULL, '/images/categories/essential-oils.jpg', 'active', '2025-11-22 19:31:30', '2025-11-22 19:31:30', 1, 'product'),
(4, 'العناية بالشعر', NULL, '/images/categories/hair-care.jpg', 'active', '2025-11-22 19:31:30', '2025-11-23 08:10:10', 1, 'blog'),
(6, 'al', 1, 'uploads/1765371986_تنزيل.jfif', 'active', '2025-12-10 13:06:26', '2025-12-10 13:06:26', 1, 'product'),
(7, 'Default', 2, 'uploads/1765372052_تنزيل (2).jfif', 'active', '2025-12-10 13:07:32', '2025-12-10 13:07:32', 1, 'product'),
(8, 'Default', 2, 'uploads/1765372139_تنزيل (2).jfif', 'active', '2025-12-10 13:08:59', '2025-12-10 13:08:59', 1, 'product'),
(9, 'Default', 2, 'uploads/1765372167_تنزيل (2).jfif', 'active', '2025-12-10 13:09:27', '2025-12-10 13:09:27', 1, 'product'),
(10, 'Default', 2, 'uploads/1765372236_تنزيل (2).jfif', 'active', '2025-12-10 13:10:36', '2025-12-10 13:10:36', 1, 'product'),
(11, 'Default', 2, 'uploads/1765372278_تنزيل (2).jfif', 'active', '2025-12-10 13:11:18', '2025-12-10 13:11:18', 1, 'product'),
(12, 'Default', 2, 'uploads/1765372291_تنزيل (2).jfif', 'active', '2025-12-10 13:11:31', '2025-12-10 13:11:31', 1, 'product'),
(13, 'Default', 2, 'uploads/1765374900_تنزيل (2).jfif', 'active', '2025-12-10 13:55:00', '2025-12-10 13:55:00', 1, 'product'),
(14, 'Default', 2, 'uploads/1765375065_تنزيل (2).jfif', 'active', '2025-12-10 13:57:45', '2025-12-10 13:57:45', 1, 'product'),
(15, 'Default', 2, 'uploads/1765375118_تنزيل (2).jfif', 'active', '2025-12-10 13:58:38', '2025-12-10 13:58:38', 1, 'product'),
(16, 'Default', 2, 'uploads/1765375131_تنزيل (2).jfif', 'active', '2025-12-10 13:58:51', '2025-12-10 13:58:51', 1, 'product');

-- --------------------------------------------------------

--
-- بنية الجدول `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `status` enum('active','pending','closed') DEFAULT 'pending',
  `unread_count` int(11) DEFAULT 0,
  `last_message` text DEFAULT NULL,
  `last_message_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `conversations`
--

INSERT INTO `conversations` (`id`, `user_id`, `admin_id`, `title`, `status`, `unread_count`, `last_message`, `last_message_time`, `created_at`) VALUES
(6, 4, 10, 'استفسار عن حالة الطلب #12345', 'active', 0, 'شكراً على مساعدتك في حل المشكلة', '2025-12-02 15:30:20', '2025-12-02 15:19:11'),
(7, 5, 11, 'مشكلة في عملية الدفع', 'active', 0, 'أحتاج مساعدة في عملية الدفع', '2025-12-02 20:30:16', '2025-12-02 15:19:11'),
(8, 6, 10, 'تأخير في استلام المنتج', 'active', 0, 'المنتج وصل بتأخير، أريد استفسار عن السبب', '2024-01-23 11:45:00', '2025-12-02 15:19:11'),
(9, 7, 11, 'مشكلة في تفعيل الحساب', 'closed', 0, 'شكراً لكم، المشكلة تم حلها', '2024-01-22 08:30:00', '2025-12-02 15:19:11'),
(10, 8, 12, 'مساعدة فنية في استخدام النظام', 'active', 0, 'نموذج تجريبيب', '2025-12-02 20:30:49', '2025-12-02 15:19:11'),
(11, 9, 10, 'استفسار عن المنتجات الجديدة', 'closed', 0, 'هل لديكم منتجات جديدة في المجموعة؟', '2025-12-02 20:31:06', '2025-12-02 15:19:11'),
(12, 4, 11, 'شكوى في الخدمة', 'pending', 0, 'لدي شكوى في الخدمة المقدمة', '2024-01-19 13:30:00', '2025-12-02 15:19:11'),
(13, 6, 12, 'طلب استرجاع منتج', 'closed', 0, 'أرغب في استرجاع منتج اشتريته', '2025-12-02 20:48:36', '2025-12-02 15:19:11'),
(14, 4, 1, 'محادثة مع أحمد محمد', 'active', 0, NULL, '2025-12-02 20:48:01', '2025-12-02 20:48:01'),
(15, 2, 1, 'محادثة مع المسؤول', 'closed', 0, NULL, '2025-12-29 19:01:22', '2025-12-29 19:01:10');

-- --------------------------------------------------------

--
-- بنية الجدول `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('general','category','product') NOT NULL DEFAULT 'general',
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `categories` text DEFAULT NULL,
  `products` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `name`, `description`, `type`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `categories`, `products`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SUMMER2024', 'خصم الصيف علاء', 'خصم خاص على جميع المنتجات خلال فصل الصيف', 'product', 'percentage', 12.00, 50.00, 20.00, 100, 0, '2024-06-01', '2025-12-31', NULL, '9,7', 1, '2025-11-22 19:03:32', '2025-12-10 14:22:47'),
(2, 'WELCOME10', 'خصم ترحيبي', 'خصم ترحيبي للعملاء الجدد', 'general', 'percentage', 10.00, 50.00, NULL, NULL, 0, '2024-01-01', '2024-12-31', NULL, NULL, 1, '2025-11-22 19:03:32', '2025-11-22 19:03:32'),
(3, 'ELECTRO25', 'خصم الإلكترونيات', 'خصم خاص على فئة الإلكترونيات', 'category', 'fixed', 25.00, 200.00, NULL, 50, 0, '2024-03-01', '2024-03-31', '1,2,3', NULL, 1, '2025-11-22 19:03:32', '2025-11-22 19:03:32'),
(4, 'PHONE50', 'خصم الهواتف', 'خصم على الهواتف المحددة', 'product', 'percentage', 20.00, 300.00, 100.00, 30, 0, '2024-04-01', '2024-04-30', NULL, '5,10,15', 1, '2025-11-22 19:03:32', '2025-11-22 19:03:32');

-- --------------------------------------------------------

--
-- بنية الجدول `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `flag` varchar(255) DEFAULT NULL,
  `exchange_rate` decimal(15,6) NOT NULL,
  `change_rate` decimal(8,4) DEFAULT 0.0000,
  `status` enum('active','inactive') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `currencies`
--

INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `country`, `flag`, `exchange_rate`, `change_rate`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'الريال السعودي', 'SAR', 'ر.س', 'المملكة العربية السعودية', '/images/flags/saudi-flag.png', 1.000000, 0.0000, 'active', 'العملة الرسمية للمملكة العربية السعودية', '2025-11-22 19:31:31', '2025-11-22 19:31:31'),
(2, 'al', 'ALA', 's', 'it', '', 900.000000, 0.1960, 'active', '', '2025-12-10 14:26:55', '2025-12-10 14:26:55');

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `message_type` enum('text','image','file') DEFAULT 'text',
  `file_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message_text`, `message_type`, `file_url`, `is_read`, `created_at`) VALUES
(1, 10, 1, 'نموذج تجريبيب', 'text', NULL, 0, '2025-12-02 20:30:44'),
(2, 10, 1, 'نموذج تجريبيب', 'text', NULL, 0, '2025-12-02 20:30:46'),
(3, 10, 1, 'نموذج تجريبيب', 'text', NULL, 0, '2025-12-02 20:30:46');

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(12, 10, 'لديك 2 رسالة غير مقروءة من أحمد محمد', 0, '2024-01-25 07:50:00'),
(13, 11, 'لديك محادثة جديدة من سارة عبدالله', 0, '2024-01-24 06:30:00'),
(14, 12, 'ماجد راشد يحتاج إلى مساعدة فنية', 0, '2024-01-21 10:25:00'),
(15, 10, 'خالد علي قام بإرسال رسالة جديدة', 1, '2024-01-23 11:50:00'),
(16, 11, 'تم إغلاق محادثة مع فاطمة ناصر', 1, '2024-01-22 08:35:00'),
(17, 4, 'تم الرد على استفسارك من فريق الدعم', 1, '2024-01-25 07:55:00'),
(18, 5, 'تم استلام استفسارك حول عملية الدفع', 0, '2024-01-24 06:35:00'),
(19, 6, 'تم الرد على استفسارك حول تأخير المنتج', 0, '2024-01-23 11:55:00'),
(20, 7, 'تم تفعيل حسابك بنجاح', 1, '2024-01-22 08:40:00'),
(21, 8, 'تم الرد على استفسارك الفني', 0, '2024-01-21 10:30:00'),
(22, 9, 'تم الرد على استفسارك عن المنتجات الجديدة', 0, '2024-01-20 12:15:00');

-- --------------------------------------------------------

--
-- بنية الجدول `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('general','product') NOT NULL DEFAULT 'general',
  `product_id` int(11) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `offers`
--

INSERT INTO `offers` (`id`, `name`, `description`, `type`, `product_id`, `discount_type`, `discount_value`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(12, 'عرض الصيف', 'خصم خاص على منتجات الصيف', 'general', NULL, 'percentage', 15.00, '2024-06-01 00:00:00', '2024-08-31 23:59:59', 'active', '2025-11-23 07:20:58', '2025-11-23 07:20:58'),
(13, 'خصم على كريم الوجه', 'عرض محدود على كريم الوجه الفاخر', 'product', 8, 'percentage', 20.00, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 'active', '2025-11-23 07:20:58', '2025-11-23 07:20:58'),
(14, 'عرض التخفيض الكبير', 'خصم بقيمة ثابتة على جميع المنتجات', 'general', NULL, 'fixed', 25.00, '2024-03-01 00:00:00', '2024-03-31 23:59:59', 'inactive', '2025-11-23 07:20:58', '2025-11-23 07:20:58'),
(15, 'عرض العطر الفرنسي', 'عرض خاص على العطر الفرنسي', 'product', 6, 'fixed', 50.00, '2024-02-01 00:00:00', '2024-02-29 23:59:59', 'expired', '2025-11-23 07:20:58', '2025-11-23 07:20:58'),
(16, 'ala', 'سش', 'product', 6, 'fixed', 33.00, '2025-12-01 00:00:00', '2025-12-10 00:00:00', 'active', '2025-12-10 14:12:47', '2025-12-10 14:12:47');

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','bank_transfer','cash_on_delivery') NOT NULL,
  `delivery_method` enum('fast_delivery','normal_delivery') NOT NULL,
  `status` enum('pending','approved','not_paid','in_delivery','completed') DEFAULT 'pending',
  `bank_receipt` varchar(500) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `invoice_number`, `customer_id`, `order_date`, `total_amount`, `payment_method`, `delivery_method`, `status`, `bank_receipt`, `tracking_number`, `estimated_delivery`, `created_at`) VALUES
(1, 'INV-2024-001', 1, '2024-01-15', 250.75, 'credit_card', 'fast_delivery', 'completed', NULL, 'TRK123456789', '2024-01-18', '2025-11-24 16:59:12'),
(2, 'INV-2024-002', 2, '2024-01-16', 150.50, 'bank_transfer', 'normal_delivery', 'in_delivery', NULL, 'TRK987654321', '2024-01-20', '2025-11-24 16:59:12'),
(3, 'INV-2024-003', 1, '2024-01-17', 75.25, 'cash_on_delivery', 'normal_delivery', 'approved', NULL, NULL, '2024-01-22', '2025-11-24 16:59:12'),
(4, 'INV-2024-004', 3, '2024-01-18', 320.00, 'credit_card', 'fast_delivery', 'pending', NULL, NULL, NULL, '2025-11-24 16:59:12'),
(5, 'INV-2024-005', 2, '2024-01-19', 180.00, 'bank_transfer', 'normal_delivery', 'approved', 'receipt_456789.jpg', 'TRK555444333', '2024-01-25', '2025-11-24 16:59:43'),
(6, 'INV-2024-006', 3, '2024-01-20', 95.00, 'bank_transfer', 'normal_delivery', 'approved', NULL, NULL, NULL, '2025-11-24 16:59:56');

-- --------------------------------------------------------

--
-- بنية الجدول `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `size`, `color`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 'قميص رجالي', 'L', 'أزرق', 2, 75.50, 151.00),
(2, 1, 'بنطال جينز', '32', 'أزرق غامق', 1, 99.75, 99.75),
(3, 2, 'فستان سهرة', 'M', 'أسود', 1, 150.50, 150.50),
(4, 3, 'تيشيرت', 'XL', 'أبيض', 3, 25.00, 75.00),
(5, 4, 'جاكيت شتوي', 'L', 'بني', 1, 200.00, 200.00),
(6, 4, 'قبعة', 'ONE SIZE', 'رمادي', 2, 60.00, 120.00),
(7, 5, 'حذاء رياضي', '42', 'أبيض', 1, 120.00, 120.00),
(8, 5, 'جوارب', 'M', 'أسود', 3, 20.00, 60.00),
(9, 6, 'حقيبة يد', 'ONE SIZE', 'أسود', 1, 95.00, 95.00);

-- --------------------------------------------------------

--
-- بنية الجدول `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('bank','card','digital','cash') NOT NULL,
  `credentials` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `icon` varchar(255) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `type`, `credentials`, `is_active`, `sort_order`, `icon`, `additional_info`, `created_at`, `updated_at`) VALUES
(1, 'التحويل البنكي', 'الدفع عن طريق التحويل البنكي المباشر', 'bank', NULL, 1, 1, 'fas fa-university', 'يرجى إرسال صورة الإيصال بعد التحويل', '2025-11-22 18:52:41', '2025-11-22 18:52:41'),
(2, 'بطاقة الائتمان', 'الدفع باستخدام بطاقات Visa أو Mastercard', 'card', NULL, 1, 2, 'fas fa-credit-card', 'مدفوعات آمنة عبر بوابات الدفع الإلكتروني', '2025-11-22 18:52:41', '2025-11-22 18:52:41'),
(3, 'الدفع عند الاستلام', 'الدفع نقداً عند استلام الطلب', 'cash', NULL, 1, 3, 'fas fa-money-bill-wave', 'خدمة الدفع عند الاستلام متاحة في جميع المناطق', '2025-11-22 18:52:41', '2025-11-22 18:52:41'),
(4, 'حساب Paypal', 'الدفع باستخدام حساب Paypal', 'digital', NULL, 1, 4, 'fab fa-paypal', 'مدفوعات دولية آمنة وسريعة', '2025-11-22 18:52:41', '2025-11-22 18:52:41'),
(5, 'Apple Pay', 'الدفع باستخدام Apple Pay', 'digital', NULL, 0, 5, 'fab fa-apple-pay', 'متوفر لأجهزة Apple', '2025-11-22 18:52:41', '2025-11-22 18:52:41'),
(6, 'STC Pay', 'الدفع باستخدام STC Pay', 'digital', NULL, 1, 6, 'fas fa-mobile-alt', 'خدمة الدفع عبر الجوال', '2025-11-22 18:52:41', '2025-11-22 18:52:41'),
(7, 'التحويل البنكي', 'الدفع عن طريق التحويل البنكي المباشر', 'bank', NULL, 1, 1, 'fas fa-university', 'يرجى إرسال صورة الإيصال بعد التحويل', '2025-11-22 18:52:43', '2025-11-22 18:52:43'),
(8, 'بطاقة الائتمان', 'الدفع باستخدام بطاقات Visa أو Mastercard', 'card', NULL, 1, 2, 'fas fa-credit-card', 'مدفوعات آمنة عبر بوابات الدفع الإلكتروني', '2025-11-22 18:52:43', '2025-11-22 18:52:43'),
(9, 'الدفع عند الاستلام', 'الدفع نقداً عند استلام الطلب', 'cash', NULL, 1, 3, 'fas fa-money-bill-wave', 'خدمة الدفع عند الاستلام متاحة في جميع المناطق', '2025-11-22 18:52:43', '2025-11-22 18:52:43'),
(10, 'حساب Paypal', 'الدفع باستخدام حساب Paypal', 'digital', NULL, 1, 4, 'fab fa-paypal', 'مدفوعات دولية آمنة وسريعة', '2025-11-22 18:52:43', '2025-11-22 18:52:43'),
(11, 'Apple Pay', 'الدفع باستخدام Apple Pay', 'digital', NULL, 0, 5, 'fab fa-apple-pay', 'متوفر لأجهزة Apple', '2025-11-22 18:52:43', '2025-11-22 18:52:43'),
(12, 'STC Pay', 'الدفع باستخدام STC Pay', 'digital', NULL, 1, 6, 'fas fa-mobile-alt', 'خدمة الدفع عبر الجوال', '2025-11-22 18:52:43', '2025-11-22 18:52:43'),
(13, 'ala', 'نقدي', 'cash', 'لا يوجد', 1, 1, 'fas fa-money-bill-wave', 'لا يوةجد', '2025-12-26 16:43:47', '2025-12-26 16:43:47');

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `base_price` decimal(12,2) NOT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `selling_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `discount` decimal(5,2) DEFAULT 0.00,
  `currency_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `barcode` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `popular` tinyint(1) DEFAULT 0,
  `new_product` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive','low_stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category_id`, `brand_id`, `base_price`, `old_price`, `selling_price`, `tax_rate`, `discount`, `currency_id`, `quantity`, `barcode`, `expiry_date`, `featured`, `popular`, `new_product`, `status`, `created_at`, `updated_at`, `is_active`, `stock`) VALUES
(6, 'كريم الوجه الفاخر4444', 'كريم ترطيب عميق للوجه بمكونات طبيعية، يناسب جميع أنواع البشرة ويوفر حماية من أشعة الشمس. يحتوي على فيتامين E وزيت الأرجان المغذي.', 7, 1, 3333.00, NULL, 3832.95, 15.00, 0.00, 2, 1, 'PROD-05919554-111', NULL, 0, 0, 1, '', '2025-11-22 19:31:31', '2025-12-24 19:52:01', 1, 0),
(7, 'عطر فرنسي نقي', 'عطر فاخر برائحة الفانيليا والزهور البيضاء، يدوم طويلاً ويناسب المناسبات الخاصة. تصنيعه يدوي في فرنسا.', 2, 2, 250.00, 300.00, 225.00, 15.00, 10.00, 1, 25, 'BARCODE002', '2026-06-30', 1, 0, 1, 'active', '2025-11-22 19:31:31', '2025-11-22 19:31:31', 1, 0),
(8, 'ماسك الطين المغربي', 'ماسك طبيعي لتنقية البشرة وإزالة الشوائب، مصنوع من طين المغرب الأصلي. يساعد في تقليل المسام وتنعيم البشرة.', 1, 3, 80.00, NULL, 72.00, 15.00, 10.00, 1, 100, 'BARCODE003', '2025-09-15', 0, 1, 1, 'active', '2025-11-22 19:31:31', '2025-12-26 17:11:59', 1, 1),
(9, 'زيت اللافندر العطري', 'زيت عطري نقي من اللافندر للاسترخاء وتخفيف التوتر، مناسب للتدليك والعلاج العطري. 100% طبيعي وخالي من الإضافات.', 3, 4, 65.00, 80.00, 58.50, 15.00, 10.00, 1, 75, 'BARCODE004', '2026-03-20', 0, 0, 0, 'active', '2025-11-22 19:31:31', '2025-11-22 19:31:31', 1, 0),
(10, 'مجموعة العناية بالشعر', 'مجموعة متكاملة للعناية بالشعر تشمل شامبو، بلسم، وسيروم للحماية من الحرارة. مناسبة للشعر الجاف والمجعد.', 4, 1, 180.00, 220.00, 162.00, 15.00, 10.00, 1, 30, 'BARCODE005', '2025-11-30', 1, 1, 1, 'active', '2025-11-22 19:31:31', '2025-12-26 17:23:21', 1, 1),
(12, 'ala', 'ثص', 6, 5, 34.00, 44.00, 39.10, 15.00, 0.00, 2, 2, 'PROD-35645231-535', '2025-12-22', 1, 0, 1, '', '2025-12-14 18:07:31', '2025-12-26 17:23:21', 1, 8),
(13, 'ملابس نسائسيه', 'ملابس تماذج تجريبيه', 4, 4, 900000.00, 4000000.00, 918000.00, 5.00, 3.00, 1, 400, 'PROD-04508201-959', '2026-01-08', 1, 0, 1, '', '2025-12-24 19:28:35', '2025-12-24 19:28:35', 1, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `color_name`, `color_code`, `created_at`) VALUES
(3, 7, 'ذهبي فاخر', '#FFD700', '2025-11-23 07:18:50'),
(4, 8, 'شفاف كريستال', '#F5F5F5', '2025-11-23 07:18:50'),
(5, 9, 'أخضر طيني', '#8B7355', '2025-11-23 07:18:50'),
(6, 9, 'بنفسجي غامق', '#800080', '2025-11-23 07:18:50'),
(7, 8, 'أزرق ملكي', '#0000FF', '2025-11-23 07:18:50'),
(8, 10, 'وردي ناعم', '#FFC0CB', '2025-11-23 07:18:50'),
(9, 12, 'أرجواني', '#6c63ff', '2025-12-14 18:07:31'),
(11, 13, 'أرجواني', '#070359', '2025-12-24 19:28:36'),
(23, 6, 'أرجواني', '#6c63ff', '2025-12-24 19:52:02');

-- --------------------------------------------------------

--
-- بنية الجدول `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_main`, `sort_order`, `created_at`) VALUES
(60, 6, '/images/products/face-cream-1.jpg', 1, 1, '2025-11-23 07:15:24'),
(61, 6, '/images/products/face-cream-2.jpg', 0, 2, '2025-11-23 07:15:24'),
(62, 6, '/images/products/face-cream-3.jpg', 0, 3, '2025-11-23 07:15:24'),
(63, 7, '/images/products/perfume-1.jpg', 1, 1, '2025-11-23 07:15:24'),
(64, 7, '/images/products/perfume-2.jpg', 0, 2, '2025-11-23 07:15:24'),
(65, 8, '/images/products/perfume-3.jpg', 0, 3, '2025-11-23 07:15:24'),
(66, 8, '/images/products/clay-mask-1.jpg', 1, 1, '2025-11-23 07:15:24'),
(67, 9, '/images/products/clay-mask-2.jpg', 0, 2, '2025-11-23 07:15:24'),
(68, 9, '/images/products/lavender-oil-1.jpg', 1, 1, '2025-11-23 07:15:24'),
(69, 10, '/images/products/lavender-oil-2.jpg', 0, 2, '2025-11-23 07:15:24'),
(70, 12, 'product_images/1765735651_0_تنزيل (1).jfif', 1, 0, '2025-12-14 18:07:31'),
(71, 13, 'product_images/1766604515_0_تنزيل (2).jfif', 1, 0, '2025-12-24 19:28:35');

-- --------------------------------------------------------

--
-- بنية الجدول `product_imports`
--

CREATE TABLE `product_imports` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `total_records` int(11) DEFAULT 0,
  `imported_records` int(11) DEFAULT 0,
  `failed_records` int(11) DEFAULT 0,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `import_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `length` varchar(20) DEFAULT NULL,
  `width` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`, `length`, `width`, `created_at`) VALUES
(13, 7, '30 مل', '8 سم', '3 سم', '2025-11-23 07:18:03'),
(14, 7, '50 مل', '10 سم', '4 سم', '2025-11-23 07:18:03'),
(15, 7, '100 مل', '12 سم', '5 سم', '2025-11-23 07:18:03'),
(16, 8, '200 جرام', '10 سم', '10 سم', '2025-11-23 07:18:03'),
(17, 8, '500 جرام', '15 سم', '15 سم', '2025-11-23 07:18:03'),
(18, 9, '10 مل', '5 سم', '2 سم', '2025-11-23 07:18:03'),
(19, 9, '30 مل', '8 سم', '3 سم', '2025-11-23 07:18:03'),
(20, 10, 'مجموعة كاملة', '25 سم', '15 سم', '2025-11-23 07:18:03'),
(23, 7, '30 مل', '8 سم', '3 سم', '2025-11-23 07:18:27'),
(24, 7, '50 مل', '10 سم', '4 سم', '2025-11-23 07:18:27'),
(25, 7, '100 مل', '12 سم', '5 سم', '2025-11-23 07:18:27'),
(26, 8, '200 جرام', '10 سم', '10 سم', '2025-11-23 07:18:27'),
(27, 8, '500 جرام', '15 سم', '15 سم', '2025-11-23 07:18:27'),
(28, 9, '10 مل', '5 سم', '2 سم', '2025-11-23 07:18:27'),
(29, 9, '30 مل', '8 سم', '3 سم', '2025-11-23 07:18:27'),
(30, 10, 'مجموعة كاملة', '25 سم', '15 سم', '2025-11-23 07:18:27'),
(31, 12, '4', '4', '4', '2025-12-14 18:07:31'),
(33, 13, '4', '4', '3', '2025-12-24 19:28:36'),
(45, 6, '33', '33', '33', '2025-12-24 19:52:02');

-- --------------------------------------------------------

--
-- بنية الجدول `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `purchase_date` date NOT NULL DEFAULT curdate(),
  `notes` text DEFAULT NULL,
  `status` enum('in-stock','with-supplier','out-of-stock') DEFAULT 'in-stock',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `purchases`
--

INSERT INTO `purchases` (`id`, `purchase_number`, `supplier_id`, `total`, `purchase_date`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PUR-2025-12-0001', 1, 111.10, '2025-12-26', '', 'with-supplier', '2025-12-26 17:11:59', '2025-12-26 17:11:59');

-- --------------------------------------------------------

--
-- بنية الجدول `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `unit_price` decimal(10,2) NOT NULL CHECK (`unit_price` >= 0),
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `purchase_details`
--

INSERT INTO `purchase_details` (`id`, `purchase_id`, `product_id`, `quantity`, `unit_price`, `total`, `created_at`) VALUES
(1, 1, 8, 1, 72.00, 72.00, '2025-12-26 17:11:59'),
(2, 1, 12, 1, 39.10, 39.10, '2025-12-26 17:11:59');

-- --------------------------------------------------------

--
-- بنية الجدول `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `return_reason` enum('defective','wrong-item','damaged','not-needed','other') NOT NULL,
  `return_status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `return_amount` decimal(10,2) NOT NULL,
  `return_notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `returns`
--

INSERT INTO `returns` (`id`, `return_number`, `order_id`, `customer_id`, `product_id`, `product_name`, `size`, `color`, `quantity`, `unit_price`, `return_reason`, `return_status`, `return_amount`, `return_notes`, `created_by`, `created_at`, `updated_at`) VALUES
(7, 'RET-2024-001', 1, 1, 6, '', NULL, NULL, 1, 0.00, 'defective', 'approved', 75.50, 'المنتج به عيب في الخياطة', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(8, 'RET-2024-002', 2, 2, 7, '', NULL, NULL, 1, 0.00, 'wrong-item', 'completed', 150.50, 'تم استلام المنتج الخطأ', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(9, 'RET-2024-003', 3, 1, 8, '', NULL, NULL, 2, 0.00, 'damaged', 'pending', 50.00, 'المنتج وصل تالف أثناء الشحن', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(10, 'RET-2024-004', 4, 3, 9, '', NULL, NULL, 1, 0.00, 'not-needed', 'approved', 200.00, 'لم يعد العميل بحاجة للمنتج', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(11, 'RET-2024-005', 1, 1, 10, '', NULL, NULL, 1, 0.00, 'defective', 'rejected', 99.75, 'العميل لم يقدم إثباتات كافية', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(12, 'RET-2024-006', 5, 2, 6, '', NULL, NULL, 1, 0.00, 'other', 'pending', 120.00, 'المقاس غير مناسب', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30');

-- --------------------------------------------------------

--
-- بنية الجدول `return_logs`
--

CREATE TABLE `return_logs` (
  `id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','image','file','boolean') DEFAULT 'text',
  `setting_group` varchar(100) DEFAULT 'general',
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `display_name`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'متجرك الإلكتروني', 'text', 'general', 'اسم الموقع', 'الاسم الذي يظهر في أعلى الموقع وفي عنوان المتصفح', 1, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(2, 'site_description', 'أفضل متجر إلكتروني', 'text', 'general', 'وصف الموقع', 'وصف مختصر عن المتجر يظهر في محركات البحث', 2, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(3, 'site_address', 'العنوان: مدينة، شارع، مبنى', 'textarea', 'general', 'عنوان الموقع', 'عنوان المتجر الفعلي', 3, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(4, 'site_email', 'info@example.com', 'text', 'general', 'البريد الإلكتروني', 'البريد الإلكتروني الرئيسي للتواصل', 4, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(5, 'site_phone', '+1234567890', 'text', 'general', 'هاتف الموقع', 'رقم الهاتف الرئيسي للتواصل', 5, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(6, 'site_logo', '', 'image', 'general', 'شعار الموقع', 'الشعار الرئيسي للموقع (يفضل أن يكون بصيغة PNG بخلفية شفافة)', 6, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(7, 'site_icon', '', 'image', 'general', 'أيقونة الموقع', 'الأيقونة التي تظهر في علامة تبويب المتصفح (يفضل 32x32 بكسل)', 7, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(8, 'homepage_background', '', 'image', 'general', 'خلفية الصفحة الرئيسية', 'صورة الخلفية للصفحة الرئيسية', 8, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(9, 'terms_conditions', '<p>شروط وأحكام استخدام الموقع...</p>', 'textarea', 'content', 'شروط وأحكام', 'شروط وأحكام استخدام المتجر الإلكتروني', 1, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(10, 'privacy_policy', '<p>سياسة الخصوصية...</p>', 'textarea', 'content', 'سياسة الخصوصية', 'سياسة الخصوصية وحماية البيانات', 2, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(11, 'about_us', '<p>من نحن...</p>', 'textarea', 'content', 'من نحن', 'معلومات عن المتجر ورسالته', 3, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(12, 'shipping_policy', '<p>سياسة الشحن...</p>', 'textarea', 'content', 'سياسة الشحن', 'تفاصيل سياسة الشحن والتوصيل', 4, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(13, 'return_policy', '<p>سياسة الإرجاع...</p>', 'textarea', 'content', 'سياسة الإرجاع', 'تفاصيل سياسة إرجاع المنتجات', 5, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(14, 'maintenance_mode', '0', 'boolean', 'system', 'وضع الصيانة', 'تفعيل وضع الصيانة لإيقاف الموقع عن الزوار', 1, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(15, 'max_login_attempts', '5', 'text', 'system', 'أقصى عدد لمحاولات تسجيل الدخول', 'عدد المحاولات المسموح بها قبل حظر المستخدم', 2, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(16, 'session_timeout', '30', 'text', 'system', 'مدة انتهاء الجلسة (بالدقائق)', 'المدة التي تنتهي بعدها جلسة المستخدم تلقائياً', 3, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(17, 'items_per_page', '12', 'text', 'system', 'عدد العناصر في الصفحة', 'عدد المنتجات المعروضة في كل صفحة', 4, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(18, 'currency', 'SAR', 'text', 'system', 'العملة', 'العملة الافتراضية للمتجر', 5, '2025-11-22 19:11:01', '2025-11-22 19:11:01'),
(19, 'timezone', 'Asia/Riyadh', 'text', 'system', 'المنطقة الزمنية', 'المنطقة الزمنية للمتجر', 6, '2025-11-22 19:11:01', '2025-11-22 19:11:01');

-- --------------------------------------------------------

--
-- بنية الجدول `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `balance` decimal(12,2) DEFAULT 0.00,
  `total_products` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `address`, `account_number`, `type`, `notes`, `status`, `balance`, `total_products`, `created_at`, `updated_at`) VALUES
(1, 'علاء', '77425137', 'alaoi@gmail.com', 'aka', '2323', '23sa', 'sad', 'active', 1088.00, 71, '2025-12-02 20:43:13', '2025-12-10 15:07:09'),
(2, 'علاء فيصل علي الحاج عبدالله', '0774252137', 'alaoi77alosh@gmail.com', 'تعز صبر الموادم', '828472', 'office', 'سش', 'active', 0.00, 0, '2025-12-10 15:09:00', '2025-12-10 15:09:00');

-- --------------------------------------------------------

--
-- بنية الجدول `supplier_balance_updates`
--

CREATE TABLE `supplier_balance_updates` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `update_type` enum('debt','credit','payment') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `supplier_balance_updates`
--

INSERT INTO `supplier_balance_updates` (`id`, `supplier_id`, `update_type`, `amount`, `notes`, `created_at`) VALUES
(1, 1, 'payment', 222.00, '23', '2025-12-10 15:07:09');

-- --------------------------------------------------------

--
-- بنية الجدول `supplier_products`
--

CREATE TABLE `supplier_products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `store_stock` int(11) DEFAULT 0,
  `supplier_stock` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `supplier_products`
--

INSERT INTO `supplier_products` (`id`, `supplier_id`, `product_name`, `price`, `category`, `store_stock`, `supplier_stock`, `status`, `created_at`) VALUES
(1, 1, 'سس', 3.00, 'electronics', 2, 2, 'active', '2025-12-10 15:06:24');

-- --------------------------------------------------------

--
-- بنية الجدول `supplier_transactions`
--

CREATE TABLE `supplier_transactions` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `type` enum('purchase','return','payment','receipt') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `supplier_transactions`
--

INSERT INTO `supplier_transactions` (`id`, `supplier_id`, `type`, `amount`, `product_id`, `quantity`, `notes`, `transaction_date`, `created_at`) VALUES
(2, 1, 'purchase', 22.00, 1, 1, '', '2025-12-10', '2025-12-10 15:06:47');

-- --------------------------------------------------------

--
-- بنية الجدول `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,  
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('user','admin','manager','sales','support') DEFAULT 'user',
  `verification_code` varchar(100) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `first_name`, `last_name`, `email`, `phone`, `password`, `user_type`, `verification_code`, `email_verified`, `created_at`, `last_activity`, `status`) VALUES
(1, 'ala fisal ali', 'ala fisal ali', NULL, 'admin@gmail.com', NULL, '$2y$10$akwBc540/Wkvnf8N1Fsu2.4fN6zA/PQ9jVierCALonUJRZrrYwjxa', 'admin', '240993', 0, '2025-11-17 11:19:59', NULL, 'pending'),
(2, 'المسؤول', 'المسؤول', NULL, 'alaoi77alsoh@gmail.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-11-17 11:21:39', NULL, 'active'),
(3, 'المسؤول الرئيسي', 'المسؤول الرئيسي', NULL, 'admin@storthory.com', NULL, '$2y$10$Eqgbf89ldAMLClkNmSA2RO1ZYXztEAFFuRTDGr72lclsOQeneN5kq', 'admin', NULL, 1, '2025-11-20 14:28:49', NULL, 'active'),
(4, 'أحمد محمد', 'أحمد', 'محمد', 'ahmed@example.com', '0501234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(5, 'سارة عبدالله', 'سارة', 'عبدالله', 'sara@example.com', '0507654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(6, 'خالد علي', 'خالد', 'علي', 'khaled@example.com', '0551112233', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(7, 'فاطمة ناصر', 'فاطمة', 'ناصر', 'fatima@example.com', '0554445566', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(8, 'ماجد راشد', 'ماجد', 'راشد', 'majed@example.com', '0567778889', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 0, '2025-12-02 15:18:36', NULL, 'pending'),
(9, 'نورة سعيد', 'نورة', 'سعيد', 'noura@example.com', '0569990001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(10, 'مدير النظام', 'مدير', 'النظام', 'admin1@example.com', '0500000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(11, 'مدير الدعم', 'مدير', 'الدعم', 'support@example.com', '0500000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(12, 'المشرف العام', 'المشرف', 'العام', 'supervisor@example.com', '0500000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-02 15:18:36', NULL, 'active'),
(13, 'علاء عبدالله', NULL, NULL, 'alaoi77alosh@gmail.com', NULL, '$2y$10$lDW5tLrVuHLIrEsgu3NgNO6kJbSjdPneFFBq.i6R7hxRHv1AxIe2y', 'user', NULL, 1, '2025-12-10 13:16:26', NULL, 'pending'),
(17, 'علاء عبدالله', NULL, NULL, 'alaoi77@gmail.com', '0774252137', '$2y$10$DPVAjmctAtv3uZPYlf6v3eklSRtyXlJRb5oLux2zuElp.cj6i8vDq', 'admin', NULL, 1, '2025-12-10 13:20:35', NULL, 'pending'),
(18, 'علاءيي يي', 'علاءيي', 'يي', 'ala@gmail.com', '077425213744', '$2y$10$eiwMDtuagi57C3gQEDJKHurUahyoCF1Yv3J7KPPqUzOKfr.6ALk82', 'user', NULL, 1, '2025-12-10 14:42:07', NULL, 'active');

-- --------------------------------------------------------

--
-- بنية الجدول `user_activities`
--

CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('login','logout','add','edit','delete','view','download','upload','print','export') NOT NULL,
  `activity_details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','other') DEFAULT 'desktop',
  `browser_info` varchar(255) DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_activities`
--

INSERT INTO `user_activities` (`id`, `user_id`, `activity_type`, `activity_details`, `ip_address`, `device_type`, `browser_info`, `status`, `created_at`) VALUES
(1, 1, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'success', '2025-12-30 18:19:29'),
(2, 1, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'success', '2025-12-30 18:23:32');

-- --------------------------------------------------------

--
-- بنية الجدول `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission`, `created_at`) VALUES
(1, 1, 'view_profile', '2025-12-10 13:54:33'),
(2, 4, 'view_profile', '2025-12-10 13:54:33'),
(3, 5, 'view_profile', '2025-12-10 13:54:33'),
(4, 6, 'view_profile', '2025-12-10 13:54:33'),
(5, 7, 'view_profile', '2025-12-10 13:54:33'),
(6, 8, 'view_profile', '2025-12-10 13:54:33'),
(7, 9, 'view_profile', '2025-12-10 13:54:33'),
(8, 2, 'view_users', '2025-12-10 13:54:33'),
(9, 3, 'view_users', '2025-12-10 13:54:33'),
(10, 10, 'view_users', '2025-12-10 13:54:33'),
(11, 11, 'view_users', '2025-12-10 13:54:33'),
(12, 12, 'view_users', '2025-12-10 13:54:33'),
(13, 13, 'view_users', '2025-12-10 13:54:33'),
(14, 17, 'view_users', '2025-12-10 13:54:33'),
(15, 2, 'add_users', '2025-12-10 13:54:34'),
(16, 3, 'add_users', '2025-12-10 13:54:34'),
(17, 10, 'add_users', '2025-12-10 13:54:34'),
(18, 11, 'add_users', '2025-12-10 13:54:34'),
(19, 12, 'add_users', '2025-12-10 13:54:34'),
(20, 13, 'add_users', '2025-12-10 13:54:34'),
(21, 17, 'add_users', '2025-12-10 13:54:34'),
(22, 2, 'edit_users', '2025-12-10 13:54:34'),
(23, 3, 'edit_users', '2025-12-10 13:54:34'),
(24, 10, 'edit_users', '2025-12-10 13:54:34'),
(25, 11, 'edit_users', '2025-12-10 13:54:34'),
(26, 12, 'edit_users', '2025-12-10 13:54:34'),
(27, 13, 'edit_users', '2025-12-10 13:54:34'),
(28, 17, 'edit_users', '2025-12-10 13:54:34'),
(29, 2, 'delete_users', '2025-12-10 13:54:34'),
(30, 3, 'delete_users', '2025-12-10 13:54:34'),
(31, 10, 'delete_users', '2025-12-10 13:54:34'),
(32, 11, 'delete_users', '2025-12-10 13:54:34'),
(33, 12, 'delete_users', '2025-12-10 13:54:34'),
(34, 13, 'delete_users', '2025-12-10 13:54:34'),
(35, 17, 'delete_users', '2025-12-10 13:54:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `blog_images`
--
ALTER TABLE `blog_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_id` (`blog_id`);

--
-- Indexes for table `blog_products`
--
ALTER TABLE `blog_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_blog_product` (`blog_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `currency_id` (`currency_id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_imports`
--
ALTER TABLE `product_imports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_number` (`purchase_number`),
  ADD KEY `idx_purchase_number` (`purchase_number`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_purchase_date` (`purchase_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_purchase_product` (`purchase_id`,`product_id`),
  ADD KEY `idx_purchase_id` (`purchase_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `return_logs`
--
ALTER TABLE `return_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_balance_updates`
--
ALTER TABLE `supplier_balance_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_number` (`transaction_number`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `activity_type` (`activity_type`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog_images`
--
ALTER TABLE `blog_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blog_products`
--
ALTER TABLE `blog_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `product_imports`
--
ALTER TABLE `product_imports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `return_logs`
--
ALTER TABLE `return_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_balance_updates`
--
ALTER TABLE `supplier_balance_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_products`
--
ALTER TABLE `supplier_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `blogs`
--
ALTER TABLE `blogs`
  ADD CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- قيود الجداول `blog_images`
--
ALTER TABLE `blog_images`
  ADD CONSTRAINT `blog_images_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `blog_products`
--
ALTER TABLE `blog_products`
  ADD CONSTRAINT `blog_products_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- قيود الجداول `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`);

--
-- قيود الجداول `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- قيود الجداول `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `returns_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `return_logs`
--
ALTER TABLE `return_logs`
  ADD CONSTRAINT `return_logs_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_logs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `supplier_balance_updates`
--
ALTER TABLE `supplier_balance_updates`
  ADD CONSTRAINT `supplier_balance_updates_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD CONSTRAINT `supplier_products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  ADD CONSTRAINT `supplier_transactions_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_transactions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `supplier_products` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

--
-- قيود الجداول `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
