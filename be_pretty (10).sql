-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 16 فبراير 2026 الساعة 23:06
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
-- بنية الجدول `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL DEFAULT 'Be Pretty',
  `logo` varchar(500) DEFAULT NULL,
  `hero_image` varchar(500) DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `story` text DEFAULT NULL,
  `values` text DEFAULT NULL COMMENT 'قيم الشركة كـ JSON',
  `features` text DEFAULT NULL COMMENT 'مميزات الشركة كـ JSON',
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `facebook` varchar(200) DEFAULT NULL,
  `instagram` varchar(200) DEFAULT NULL,
  `twitter` varchar(200) DEFAULT NULL,
  `working_hours` varchar(100) DEFAULT NULL,
  `shipping_info` text DEFAULT NULL,
  `return_policy` text DEFAULT NULL,
  `privacy_policy` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `about`
--

INSERT INTO `about` (`id`, `company_name`, `logo`, `hero_image`, `vision`, `mission`, `story`, `values`, `features`, `address`, `phone`, `email`, `whatsapp`, `facebook`, `instagram`, `twitter`, `working_hours`, `shipping_info`, `return_policy`, `privacy_policy`, `terms_conditions`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Be Pretty', NULL, NULL, 'أن نكون وجهتك الأولى لكل ما يتعلق بالجمال والعناية الشخصية في الشرق الأوسط.', 'تقديم منتجات جمالية وعناية شخصية عالية الجودة، مع توفير تجربة تسوق استثنائية تعزز الثقة والجمال الداخلي والخارجي.', 'بدأت رحلتنا في عام 2023 برؤية واضحة: جعل الجمال في متناول الجميع. اليوم، نحن فخورون بكوننا منصة موثوقة للمنتجات الجمالية الأصلية والعالية الجودة.', '{\"الجودة\": \"نوفر فقط المنتجات الأصلية عالية الجودة\", \"الثقة\": \"نحرص على بناء علاقة ثقة مع عملائنا\", \"الشفافية\": \"نقدم معلومات واضحة وصادقة\", \"الابتكار\": \"نبحث دائمًا عن أحدث صيحات الجمال\", \"التميز\": \"نسعى لتقديم الأفضل في كل شيء نفعله\"}', '{\"ضمان الجودة\": \"جميع منتجاتنا أصلية ومرخصة\", \"شحن سريع\": \"توصيل سريع في جميع أنحاء المملكة\", \"دعم فني\": \"فريق دعم متاح على مدار الساعة\", \"سهولة الإرجاع\": \"سياسة إرجاع مرنة وسهلة\", \"عروض حصرية\": \"خصومات وعروض خاصة للمشتركين\"}', 'المملكة العربية السعودية - الرياض', '+966500000000', 'info@bepretty.com', '+966500000000', NULL, NULL, NULL, 'من الأحد إلى الخميس: 9 صباحاً - 11 مساءً', 'شحن مجاني للطلبات فوق 200 ريال. مدة التوصيل 2-5 أيام عمل.', 'يمكنك إرجاع المنتج خلال 14 يومًا من تاريخ الاستلام بشرط أن يكون بحالته الأصلية.', 'نحترم خصوصيتك ونحمي بياناتك الشخصية وفقًا لأفضل الممارسات الأمنية.', NULL, 'من نحن | Be Pretty - وجهتك الأولى للجمال والعناية', 'اكتشف قصة Be Pretty، رؤيتنا، رسالتنا، وقيمنا. نحن نقدم منتجات جمالية وعناية شخصية عالية الجودة.', NULL, 1, '2026-01-12 04:12:10', '2026-01-12 04:12:10');

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
(2, 'علاء فيصل علي الحاج عبدالله', 'banks_logos/1765378524_693989dc58885.jfif', 'علاء فيصل علي الحاج عبدالله', 'https://github.com/AlAOI7/Real-Estate', 'active', '2025-12-10 14:55:24', '2025-12-10 14:55:24'),
(3, 'البنك الأهلي السعودي', 'alahli.png', NULL, NULL, 'active', '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(4, 'بنك الرياض', 'riyad.png', NULL, NULL, 'active', '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(5, 'البنك السعودي الفرنسي', 'sf.png', NULL, NULL, 'active', '2026-01-26 18:55:18', '2026-01-26 18:55:18');

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
(8, 1, '32453254', 'cxzvx', 'YER', '3453', 'xcvcz43', 'f', 343.00, 'active', 0, 'sdf', '2025-12-10 16:06:34', '2025-12-10 16:06:34'),
(9, 1, 'SA1234567890123456789012', 'شركة Be Pretty', 'SAR', 'SA1234567890123456789012', NULL, NULL, 0.00, 'active', 1, NULL, '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(10, 2, 'SA9876543210987654321098', 'شركة Be Pretty', 'SAR', 'SA9876543210987654321098', NULL, NULL, 0.00, 'active', 1, NULL, '2026-01-26 18:55:18', '2026-01-26 18:55:18');

-- --------------------------------------------------------

--
-- بنية الجدول `bank_transfer_receipts`
--

CREATE TABLE `bank_transfer_receipts` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `transfer_amount` decimal(10,2) DEFAULT NULL,
  `receipt_image` varchar(500) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `reading_time` int(11) DEFAULT 3,
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

INSERT INTO `blogs` (`id`, `title`, `summary`, `content`, `reading_time`, `category_id`, `main_image`, `publish_date`, `status`, `total_price`, `views_count`, `shares_count`, `sales_from_blog`, `created_at`, `updated_at`) VALUES
(1, 'روتين العناية بالبشرة اليومي', 'دليل شامل للعناية بالبشرة يومياً للحصول على بشرة صحية ومشرقة', '<h2>روتين العناية بالبشرة اليومي</h2>\r\n<p>تعتبر العناية بالبشرة من أهم الأمور للحفاظ على صحة وجمال البشرة. إليك روتين يومي مقترح:</p>\r\n<h3>الصباح:</h3>\r\n<ol>\r\n<li>غسل الوجه بغسول لطيف</li>\r\n<li>استخدام تونر مناسب</li>\r\n<li>ترطيب البشرة</li>\r\n<li>وضع واقي الشمس</li>\r\n</ol>\r\n<h3>المساء:</h3>\r\n<ol>\r\n<li>إزالة المكياج</li>\r\n<li>غسول البشرة</li>\r\n<li>استخدام السيروم</li>\r\n<li>كريم ليلي</li>\r\n</ol>', 3, 1, 'blog_images/skin_routine.jpg', '2024-01-20', 'published', 57.00, 225, 44, 6, '2026-01-12 18:36:15', '2026-02-14 19:07:23'),
(2, 'كيف تختار العطر المثالي لك', 'دليل شامل لاختيار العطر المناسب لكل مناسبة وشخصية', '<h2>اختيار العطر المثالي</h2>\r\n<p>اختيار العطر المناسب يعتمد على عدة عوامل:</p>\r\n<ul>\r\n<li>نوع البشرة</li>\r\n<li>فصل السنة</li>\r\n<li>المناسبة</li>\r\n<li>الوقت من اليوم</li>\r\n</ul>\r\n<p>عطور الصباح خفيفة بينما عطور المساء تكون أقوى.</p>', 3, 2, 'blog_images/perfumes.jpg', '2024-01-18', 'published', 135.00, 109, 27, 3, '2026-01-12 18:36:15', '2026-02-05 20:12:57'),
(3, 'فوائد قناع الطين للبشرة', 'اكتشف فوائد قناع الطين في تنظيف وتنقية البشرة', '<h2>فوائد قناع الطين</h2>\r\n<p>قناع الطين له فوائد عديدة للبشرة:</p>\r\n<ul>\r\n<li>تنظيف المسام العميق</li>\r\n<li>التخلص من الزيوت الزائدة</li>\r\n<li>تقشير خفيف للبشرة</li>\r\n<li>تحسين الدورة الدموية</li>\r\n</ul>', 3, 3, 'blog_images/clay_mask.jpg', '2024-01-15', 'published', 35.00, 204, 23, 3, '2026-01-12 18:36:15', '2026-01-12 19:14:04'),
(4, 'أساسيات المكياج للمبتدئات', 'تعلمي أساسيات المكياج خطوة بخطوة', '<h2>أساسيات المكياج</h2>\r\n<p>ابدئي رحلة المكياج مع هذه الأساسيات:</p>\r\n<ol>\r\n<li>كريم الأساس</li>\r\n<li>كونسيلر</li>\r\n<li>بودرة</li>\r\n<li>أحمر الخدود</li>\r\n<li>ماسكارا</li>\r\n</ol>', 3, 3, 'blog_images/makeup_basics.jpg', '2024-01-10', 'draft', 22.00, 0, 0, 0, '2026-01-12 18:36:15', '2026-01-12 18:37:07'),
(5, 'أفضل المنتجات للشعر الجاف', 'مجموعة مختارة من أفضل المنتجات للعناية بالشعر الجاف', '<h2>العناية بالشعر الجاف</h2>\r\n<p>الشعر الجاف يحتاج إلى عناية خاصة:</p>\r\n<ul>\r\n<li>شامبو مرطب</li>\r\n<li>بلسم عميق</li>\r\n<li>زيوت مغذية</li>\r\n<li>ماسكات طبيعية</li>\r\n</ul>', 3, 3, 'blog_images/dry_hair.jpg', '2024-01-25', 'scheduled', 22.00, 0, 0, 0, '2026-01-12 18:36:15', '2026-01-12 18:37:07');

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
(1, 1, 'blog_additional_images/skin_routine_1.jpg', 1, '2026-01-12 18:36:15'),
(2, 1, 'blog_additional_images/skin_routine_2.jpg', 2, '2026-01-12 18:36:15'),
(3, 1, 'blog_additional_images/skin_routine_3.jpg', 3, '2026-01-12 18:36:15'),
(4, 2, 'blog_additional_images/perfumes_1.jpg', 1, '2026-01-12 18:36:15'),
(5, 2, 'blog_additional_images/perfumes_2.jpg', 2, '2026-01-12 18:36:15'),
(6, 3, 'blog_additional_images/clay_mask_1.jpg', 1, '2026-01-12 18:36:15'),
(7, 3, 'blog_additional_images/clay_mask_2.jpg', 2, '2026-01-12 18:36:15'),
(8, 4, 'blog_additional_images/makeup_1.jpg', 1, '2026-01-12 18:36:15'),
(9, 5, 'blog_additional_images/hair_1.jpg', 1, '2026-01-12 18:36:15'),
(10, 5, 'blog_additional_images/hair_2.jpg', 2, '2026-01-12 18:36:15');

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
(1, 1, 7, 1, '2026-01-12 18:36:15'),
(2, 1, 9, 2, '2026-01-12 18:36:15'),
(3, 2, 8, 1, '2026-01-12 18:36:15'),
(4, 3, 9, 1, '2026-01-12 18:36:15'),
(5, 4, 7, 1, '2026-01-12 18:36:15'),
(6, 5, 7, 1, '2026-01-12 18:36:15');

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
-- بنية الجدول `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size_id` int(11) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `size_id`, `color_id`, `created_at`, `updated_at`) VALUES
(1, 3253925, 10, 1, NULL, NULL, '2026-01-12 05:22:35', '2026-01-12 05:22:35'),
(2, 25, 3, 1, NULL, NULL, '2026-01-12 20:05:55', '2026-01-12 20:05:55'),
(3, 25, 8, 1, NULL, NULL, '2026-01-12 20:06:03', '2026-01-12 20:06:03'),
(4, 0, 1, 1, NULL, NULL, '2026-01-16 17:27:59', '2026-01-16 17:27:59'),
(5, 0, 3, 1, NULL, NULL, '2026-01-16 18:17:10', '2026-01-16 18:17:10'),
(6, 0, 8, 6, NULL, NULL, '2026-01-16 19:17:53', '2026-01-16 19:17:53'),
(7, 0, 2, 5, NULL, NULL, '2026-01-23 14:40:30', '2026-02-03 14:24:53'),
(10, 27, 2, 4, NULL, NULL, '2026-01-27 20:12:32', '2026-01-27 20:13:00'),
(15, 1, 2, 6, NULL, NULL, '2026-02-04 06:58:32', '2026-02-04 07:05:19'),
(21, 29, 2, 3, NULL, NULL, '2026-02-05 18:53:22', '2026-02-05 19:39:31');

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
(1, 'العناية بالبشرة', NULL, 'skincare.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'blog'),
(2, 'العطور', NULL, 'perfumes.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'blog'),
(3, 'صحة وجمال', NULL, 'health-beauty.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'blog'),
(4, 'ملابس', NULL, 'clothes.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'product'),
(5, 'أحذية', NULL, 'shoes.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'product'),
(6, 'إلكترونيات', NULL, 'electronics.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'product'),
(7, 'ملابس رجالية', 4, 'men-clothes.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'product'),
(8, 'ملابس نسائية', 4, 'women-clothes.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'product'),
(9, 'مستلزمات المكتب', NULL, 'office.jpg', 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 'product'),
(10, 'alaoi alaoi', NULL, 'uploads/1768587998_شعار مدارس الرسالة.jpg', 'active', '2026-01-16 18:26:38', '2026-01-16 18:26:38', 1, 'product'),
(11, 'العناية بالبشرة الجديدة', NULL, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(12, 'كريمات الوجه', 11, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(13, 'سيروم', 11, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(14, 'المكياج الجديد', NULL, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(15, 'أحمر شفاه', 14, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(16, 'عيون', 14, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(17, 'العناية بالشعر', NULL, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(18, 'شامبو وبلسم', 17, NULL, 'active', '2026-02-05 20:21:16', '2026-02-05 20:21:16', 1, 'product'),
(19, 'العناية بالبشرة الجديدة', NULL, NULL, 'active', '2026-02-05 20:22:31', '2026-02-05 20:22:31', 1, 'product'),
(20, 'كريمات الوجه', NULL, NULL, 'active', '2026-02-05 20:22:57', '2026-02-05 20:22:57', 1, 'product'),
(21, 'سيروم', NULL, NULL, 'active', '2026-02-05 20:22:57', '2026-02-05 20:22:57', 1, 'product'),
(22, 'المكياج الجديد', NULL, NULL, 'active', '2026-02-05 20:23:38', '2026-02-05 20:23:38', 1, 'product'),
(23, 'أحمر شفاه', NULL, NULL, 'active', '2026-02-05 20:23:48', '2026-02-05 20:23:48', 1, 'product'),
(24, 'الإكسسوارات', NULL, NULL, 'active', '2026-02-05 20:26:11', '2026-02-05 20:26:11', 1, 'product');

-- --------------------------------------------------------

--
-- بنية الجدول `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country` varchar(100) DEFAULT 'السعودية',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `cities`
--

INSERT INTO `cities` (`id`, `name`, `country`, `is_active`) VALUES
(1, 'الرياض', 'السعودية', 1),
(2, 'جدة', 'السعودية', 1),
(3, 'مكة', 'السعودية', 1),
(4, 'المدينة المنورة', 'السعودية', 1),
(5, 'الدمام', 'السعودية', 1),
(6, 'الخبر', 'السعودية', 1),
(7, 'الطائف', 'السعودية', 1),
(8, 'تبوك', 'السعودية', 1),
(9, 'أبها', 'السعودية', 1),
(10, 'حائل', 'السعودية', 1),
(11, 'نجران', 'السعودية', 1),
(12, 'جازان', 'السعودية', 1),
(13, 'الباحة', 'السعودية', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `subject_other` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('new','read','replied','pending','resolved') DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `attachments` text DEFAULT NULL COMMENT 'مسارات الملفات المرفقة كـ JSON',
  `response` text DEFAULT NULL,
  `response_by` int(11) DEFAULT NULL,
  `response_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `phone`, `subject_id`, `subject_other`, `message`, `ip_address`, `user_agent`, `status`, `assigned_to`, `priority`, `attachments`, `response`, `response_by`, `response_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'alaoi alaoi', 'alaoi@gmail.com', '774252137', 4, NULL, 'اتىةعاتلاغالاى هعنت', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'new', NULL, 'normal', NULL, NULL, NULL, NULL, '2026-01-25 18:44:55', '2026-01-25 18:44:55');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_methods`
--

CREATE TABLE `contact_methods` (
  `id` int(11) NOT NULL,
  `type` enum('phone','email','whatsapp','instagram','snapchat','tiktok','facebook','twitter','telegram','location','sms') NOT NULL,
  `title` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_methods`
--

INSERT INTO `contact_methods` (`id`, `type`, `title`, `value`, `icon`, `color`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'phone', 'الاتصال الهاتفي', '+966500000000', 'fas fa-phone-alt', '#dc3545', 1, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(2, 'email', 'البريد الإلكتروني', 'info@bepretty.com', 'fas fa-envelope', '#28a745', 2, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(3, 'whatsapp', 'واتساب', '+966500000000', 'fab fa-whatsapp', '#25d366', 3, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(4, 'instagram', 'انستجرام', 'https://instagram.com/bepretty', 'fab fa-instagram', '#e4405f', 4, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(5, 'snapchat', 'سناب شات', 'https://snapchat.com/add/bepretty', 'fab fa-snapchat', '#fffc00', 5, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(6, 'tiktok', 'تيك توك', 'https://tiktok.com/@bepretty', 'fab fa-tiktok', '#000000', 6, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(7, 'location', 'العنوان', 'المملكة العربية السعودية - الرياض', 'fas fa-map-marker-alt', '#007bff', 7, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_settings`
--

CREATE TABLE `contact_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','email','url','textarea','json','boolean') DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_settings`
--

INSERT INTO `contact_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'contact_page_title', 'تواصل معنا | Be Pretty', 'text', 'general', 'عنوان صفحة التواصل', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(2, 'contact_page_description', 'نحن في خدمتك على مدار الساعة! تواصل معنا عبر وسائل التواصل المختلفة.', 'textarea', 'general', 'وصف صفحة التواصل', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(3, 'contact_form_title', 'أرسل لنا رسالة', 'text', 'form', 'عنوان نموذج التواصل', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(4, 'contact_form_success_message', 'شكراً لك! تم استلام رسالتك بنجاح وسنقوم بالرد في أقرب وقت ممكن.', 'textarea', 'form', 'رسالة النجاح بعد إرسال النموذج', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(5, 'contact_email_notifications', '1', 'boolean', 'email', 'تفعيل إشعارات البريد الإلكتروني للرسائل الجديدة', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(6, 'contact_admin_email', 'admin@bepretty.com', 'email', 'email', 'بريد المشرف للإشعارات', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(7, 'contact_auto_reply', '1', 'boolean', 'email', 'تفعيل الرد الآلي على الرسائل', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(8, 'contact_auto_reply_subject', 'تم استلام رسالتك - Be Pretty', 'text', 'email', 'عنوان الرد الآلي', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(9, 'contact_auto_reply_message', 'شكراً لتواصلك مع Be Pretty! لقد استلمنا رسالتك وسنقوم بالرد في غضون 24 ساعة عمل.', 'textarea', 'email', 'نص الرد الآلي', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(10, 'contact_working_hours', 'من الأحد إلى الخميس: 9 صباحاً - 11 مساءً', 'text', 'general', 'ساعات العمل', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(11, 'contact_response_time', '24', 'number', 'general', 'وقت الرد المتوقع بالساعات', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(12, 'contact_google_map_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3620.120638478258!2d46.67233791500067!3d24.854280784058215!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2ee5d6d8e7c4a9%3A0x9c5c6c7d8e7c4a9!2sRiyadh!5e0!3m2!1sen!2ssa!4v1647832345678!5m2!1sen!2ssa', 'url', 'map', 'رابط خريطة جوجل', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(13, 'contact_privacy_note', 'نحن نحترم خصوصيتك ولن نشارك معلوماتك مع أي طرف ثالث.', 'textarea', 'privacy', 'ملاحظة الخصوصية', '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(14, 'contact_required_fields', 'name,email,subject,message', 'text', 'form', 'الحقول المطلوبة في النموذج', '2026-01-12 04:32:33', '2026-01-12 04:32:33');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_subjects`
--

CREATE TABLE `contact_subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `email_recipient` varchar(255) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_subjects`
--

INSERT INTO `contact_subjects` (`id`, `name`, `description`, `email_recipient`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'استفسار عام', 'استفسارات عامة عن المتجر والخدمات', 'info@bepretty.com', 1, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(2, 'استفسار عن منتج', 'استفسارات عن منتج معين', 'sales@bepretty.com', 2, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(3, 'استفسار عن طلب', 'استفسارات عن حالة الطلب', 'orders@bepretty.com', 3, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(4, 'شكوى', 'شكاوى عن منتج أو خدمة', 'support@bepretty.com', 4, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(5, 'اقتراح', 'اقتراحات لتطوير المتجر', 'suggestions@bepretty.com', 5, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33'),
(6, 'شراكة', 'طلبات الشراكة والتعاون', 'partnership@bepretty.com', 6, 1, '2026-01-12 04:32:33', '2026-01-12 04:32:33');

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
-- بنية الجدول `delivery_addresses`
--

CREATE TABLE `delivery_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL COMMENT 'المنزل، العمل، إلخ',
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `secondary_phone` varchar(20) DEFAULT NULL COMMENT 'رقم هاتف آخر للتواصل',
  `country` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL COMMENT 'المنطقة أو المحافظة',
  `district` varchar(100) NOT NULL COMMENT 'الحي أو المديرية',
  `street` varchar(255) NOT NULL COMMENT 'الشارع',
  `building` varchar(100) DEFAULT NULL COMMENT 'رقم المبنى',
  `floor` varchar(50) DEFAULT NULL COMMENT 'الطابق',
  `apartment` varchar(50) DEFAULT NULL COMMENT 'رقم الشقة',
  `nearest_landmark` text DEFAULT NULL COMMENT 'أقرب مكان معروف',
  `postal_code` varchar(20) DEFAULT NULL,
  `address_type` enum('home','work','other') DEFAULT 'home',
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `delivery_addresses`
--

INSERT INTO `delivery_addresses` (`id`, `user_id`, `title`, `full_name`, `phone`, `secondary_phone`, `country`, `city`, `region`, `district`, `street`, `building`, `floor`, `apartment`, `nearest_landmark`, `postal_code`, `address_type`, `is_default`, `is_active`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 3, '', '', '', NULL, '', 'سيب', NULL, 'ي', 'يسليسل52', NULL, NULL, NULL, NULL, NULL, 'home', 0, 1, NULL, NULL, '2026-02-04 19:26:51', '2026-02-04 19:26:51'),
(2, 29, '', '', '', NULL, '', 'سيب', NULL, 'ي', 'يسليسل52', NULL, NULL, NULL, NULL, NULL, 'home', 0, 1, NULL, NULL, '2026-02-04 19:30:07', '2026-02-04 19:30:07');

-- --------------------------------------------------------

--
-- بنية الجدول `delivery_options`
--

CREATE TABLE `delivery_options` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `free_threshold` decimal(10,2) DEFAULT NULL,
  `delivery_time_min` int(11) DEFAULT 1,
  `delivery_time_max` int(11) DEFAULT 5,
  `delivery_time_unit` enum('hours','days','weeks') DEFAULT 'days',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `icon` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `delivery_options`
--

INSERT INTO `delivery_options` (`id`, `name`, `description`, `cost`, `free_threshold`, `delivery_time_min`, `delivery_time_max`, `delivery_time_unit`, `is_active`, `sort_order`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'التوصيل العادي', 'التوصيل خلال 3-5 أيام عمل', 15.00, 200.00, 3, 5, 'days', 1, 1, 'fas fa-truck', '2026-01-24 18:03:00', '2026-01-24 18:03:00'),
(2, 'التوصيل السريع', 'التوصيل خلال 1-2 أيام عمل', 30.00, 500.00, 1, 2, 'days', 1, 2, 'fas fa-shipping-fast', '2026-01-24 18:03:00', '2026-01-24 18:03:00'),
(3, 'الاستلام من المتجر', 'استلام الطلب من أقرب فرع', 0.00, NULL, 0, 1, 'hours', 1, 3, 'fas fa-store', '2026-01-24 18:03:00', '2026-01-24 18:03:00'),
(4, 'التوصيل المجاني', 'توصيل مجاني للطلبات فوق 200 ريال', 0.00, 200.00, 4, 7, 'days', 1, 4, 'fas fa-gift', '2026-01-24 18:03:00', '2026-01-24 18:03:00'),
(5, 'التوصيل العادي', 'التوصيل خلال 3-5 أيام عمل', 15.00, NULL, 3, 5, 'days', 1, 0, 'fas fa-truck', '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(6, 'التوصيل السريع', 'التوصيل خلال 24-48 ساعة', 30.00, NULL, 1, 2, 'days', 1, 0, 'fas fa-bolt', '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(7, 'الاستلام من المتجر', 'استلام الطلب من أقرب فرع', 0.00, NULL, 1, 2, 'hours', 1, 0, 'fas fa-store', '2026-01-26 18:55:18', '2026-01-26 18:55:18');

-- --------------------------------------------------------

--
-- بنية الجدول `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `helpful` int(11) DEFAULT 0,
  `not_helpful` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `button_text` varchar(50) DEFAULT 'اكتشف العروض',
  `offer_type_id` int(11) DEFAULT NULL,
  `discount_type` enum('percentage','fixed','none') DEFAULT 'none',
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `offers`
--

INSERT INTO `offers` (`id`, `title`, `description`, `image`, `link`, `start_date`, `end_date`, `is_active`, `display_order`, `button_text`, `offer_type_id`, `discount_type`, `discount_value`, `created_at`, `updated_at`) VALUES
(1, 'تخفيضات الصيف الكبرى', 'خصومات حصرية تصل إلى 50% على جميع منتجات المكياج والعناية بالبشرة. استمتعي بأفضل العروض هذا الصيف واحصلي على بشرة مشرقة وجميلة.', 'uploads/offers/offer_1768589462_9584.jpg', '', '2025-06-01 00:00:00', '2026-10-30 23:59:00', 1, 1, 'تسوق العروض', 5, 'percentage', 50.00, '2026-01-12 04:33:53', '2026-01-16 18:56:40'),
(2, 'عروض خاصة على العناية بالشعر', 'احصلي على شعر صحي ولامع مع عروضنا الخاصة على منتجات العناية بالشعر. مجموعة متكاملة بخصم 40%.', 'uploads/offers/offer_1768589524_9132.jpg', '', '2025-01-01 00:00:00', '2026-12-31 23:59:00', 1, 2, 'عناية بالشعر', 4, 'percentage', 40.00, '2026-01-12 04:33:53', '2026-01-16 18:52:04'),
(3, 'شحن مجاني على جميع الطلبات', 'استمتعي بشحن مجاني على جميع طلباتك دون حد أدنى للشراء. العرض ساري لمدة محدودة.', 'uploads/offers/offer_1771098259_1724.jpg', 'http://localhost/altorya/dash', '2026-02-14 00:00:00', '2026-03-13 23:59:00', 1, 3, 'اطلب الآن', 3, 'none', 0.00, '2026-01-12 04:33:53', '2026-02-14 19:44:19'),
(4, 'عروض حصرية على العطور الفرنسية', 'خصم 30% على أجود العطور الفرنسية الأصيلة. رائحة تدوم طويلاً وتناسب جميع المناسبات.', 'uploads/offers/offer_1771098296_5436.png', 'http://localhost/altorya/dash', '2025-02-01 00:00:00', '2026-07-01 23:59:00', 1, 4, 'اكتشف العطور', 8, 'percentage', 30.00, '2026-01-12 04:33:53', '2026-02-14 19:44:56'),
(5, 'خصم 25% على المنتجات الجديدة', 'كني أول من يجرب منتجاتنا الجديدة بخصم 25%. تشكيلة متنوعة من مستحضرات التجميل والعناية بالبشرة.', 'img/offers/new.jpg', 'products.php?filter=new', '2025-01-15 00:00:00', '2025-02-15 23:59:59', 1, 5, 'تسوق الجديد', 7, 'percentage', 25.00, '2026-01-12 04:33:53', '2026-01-12 04:35:23'),
(6, 'باقة العناية الكاملة', 'باقة متكاملة للعناية اليومية تشمل 5 منتجات أساسية بخصم 35%. وفرِ المال واحصلي على كل ما تحتاجينه.', 'img/offers/bundle.jpg', 'products.php?bundle=1', '2025-03-01 00:00:00', '2025-03-31 23:59:59', 1, 6, 'اشتري الباقة', 4, 'percentage', 35.00, '2026-01-12 04:33:53', '2026-01-12 04:35:23'),
(7, 'تخفيضات نهاية الموسم', 'تخفيضات كبيرة على منتجات الموسم الماضي. فرصة ذهبية لشراء منتجات أصلية بأسعار لا تقبل المنافسة.', 'img/offers/clearance.jpg', 'products.php?clearance=1', '2025-12-01 00:00:00', '2025-12-31 23:59:59', 1, 7, 'تسوق التخفيضات', 6, 'percentage', 60.00, '2026-01-12 04:33:53', '2026-01-12 04:35:24');

-- --------------------------------------------------------

--
-- بنية الجدول `offer_products`
--

CREATE TABLE `offer_products` (
  `id` int(11) NOT NULL,
  `offer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `offer_products`
--

INSERT INTO `offer_products` (`id`, `offer_id`, `product_id`, `display_order`, `created_at`) VALUES
(24, 1, 10, 0, '2026-01-16 18:56:40'),
(25, 1, 6, 0, '2026-01-16 18:56:40'),
(26, 1, 2, 0, '2026-01-16 18:56:40'),
(27, 1, 4, 0, '2026-01-16 18:58:28'),
(28, 2, 5, 0, '2026-01-16 18:58:46'),
(30, 3, 10, 0, '2026-02-14 19:44:19'),
(31, 3, 4, 0, '2026-02-14 19:44:19'),
(32, 4, 4, 0, '2026-02-14 19:44:56'),
(33, 4, 3, 0, '2026-02-14 19:44:56'),
(34, 4, 6, 0, '2026-02-14 19:46:36');

-- --------------------------------------------------------

--
-- بنية الجدول `offer_types`
--

CREATE TABLE `offer_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `offer_types`
--

INSERT INTO `offer_types` (`id`, `name`, `description`, `icon`, `is_active`) VALUES
(1, 'خصم خاص', 'عروض خصم على منتجات محددة', 'fa-percentage', 1),
(2, 'عرض مجاني', 'إهداء مجاني مع الشراء', 'fa-gift', 1),
(3, 'شحن مجاني', 'توصيل مجاني للطلبات', 'fa-shipping-fast', 1),
(4, 'مجموعة', 'عروض الباقات والتجميعات', 'fa-boxes', 1),
(5, 'موسمي', 'عروض موسمية', 'fa-calendar-alt', 1),
(6, 'تخفيضات', 'تخفيضات عامة', 'fa-tag', 1),
(7, 'عرض جديد', 'عروض للمنتجات الجديدة', 'fa-star', 1),
(8, 'عرض حصري', 'عروض حصرية', 'fa-crown', 1),
(9, 'خصم خاص', 'عروض خصم على منتجات محددة', 'fa-percentage', 1),
(10, 'عرض مجاني', 'إهداء مجاني مع الشراء', 'fa-gift', 1),
(11, 'شحن مجاني', 'توصيل مجاني للطلبات', 'fa-shipping-fast', 1),
(12, 'مجموعة', 'عروض الباقات والتجميعات', 'fa-boxes', 1),
(13, 'موسمي', 'عروض موسمية', 'fa-calendar-alt', 1),
(14, 'تخفيضات', 'تخفيضات عامة', 'fa-tag', 1),
(15, 'عرض جديد', 'عروض للمنتجات الجديدة', 'fa-star', 1),
(16, 'عرض حصري', 'عروض حصرية', 'fa-crown', 1);

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
  `subtotal` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('credit_card','bank_transfer','cash_on_delivery') NOT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `delivery_method` enum('fast_delivery','normal_delivery') NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `reservation_expires_at` datetime DEFAULT NULL,
  `bank_receipt` varchar(500) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_option_id` int(11) DEFAULT NULL,
  `delivery_address_id` int(11) DEFAULT NULL,
  `delivery_cost` decimal(10,2) DEFAULT 0.00,
  `delivery_date` date DEFAULT NULL,
  `delivery_time_slot` varchar(50) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `delivery_status` enum('pending','preparing','shipped','out_for_delivery','delivered','failed') DEFAULT 'pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `delivery_person_id` int(11) DEFAULT NULL,
  `delivery_person_name` varchar(255) DEFAULT NULL,
  `delivery_person_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `invoice_number`, `customer_id`, `order_date`, `total_amount`, `subtotal`, `payment_method`, `payment_status`, `payment_proof`, `delivery_method`, `status`, `reservation_expires_at`, `bank_receipt`, `tracking_number`, `estimated_delivery`, `created_at`, `delivery_option_id`, `delivery_address_id`, `delivery_cost`, `delivery_date`, `delivery_time_slot`, `delivery_notes`, `delivery_status`, `delivered_at`, `delivery_person_id`, `delivery_person_name`, `delivery_person_phone`) VALUES
(1, 'INV-2024-001', 1, '2024-01-15', 250.75, NULL, 'credit_card', 'pending', NULL, 'fast_delivery', 'completed', NULL, NULL, 'TRK123456789', '2024-01-18', '2025-11-24 16:59:12', NULL, NULL, 0.00, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL),
(2, 'INV-2024-002', 2, '2024-01-16', 150.50, NULL, 'bank_transfer', 'pending', NULL, 'normal_delivery', 'in_delivery', NULL, NULL, 'TRK987654321', '2024-01-20', '2025-11-24 16:59:12', NULL, NULL, 0.00, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL),
(3, 'INV-2024-003', 1, '2024-01-17', 75.25, NULL, 'cash_on_delivery', 'pending', NULL, 'normal_delivery', 'approved', NULL, NULL, NULL, '2024-01-22', '2025-11-24 16:59:12', NULL, NULL, 0.00, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL),
(4, 'INV-2024-004', 3, '2024-01-18', 320.00, NULL, 'credit_card', 'pending', NULL, 'fast_delivery', 'pending', NULL, NULL, NULL, NULL, '2025-11-24 16:59:12', NULL, NULL, 0.00, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL),
(5, 'INV-2024-005', 2, '2024-01-19', 180.00, NULL, 'bank_transfer', 'pending', NULL, 'normal_delivery', 'approved', NULL, 'receipt_456789.jpg', 'TRK555444333', '2024-01-25', '2025-11-24 16:59:43', NULL, NULL, 0.00, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL),
(6, 'INV-2024-006', 3, '2024-01-20', 95.00, NULL, 'bank_transfer', 'pending', NULL, 'normal_delivery', 'approved', NULL, NULL, NULL, NULL, '2025-11-24 16:59:56', NULL, NULL, 0.00, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL),
(13, 'INV-69839D7BBEC45', 3, '2026-02-04', 19465.00, 19450.00, 'cash_on_delivery', 'pending', NULL, 'normal_delivery', 'approved', NULL, NULL, NULL, NULL, '2026-02-04 19:26:51', NULL, 1, 15.00, NULL, NULL, '', 'pending', NULL, NULL, NULL, NULL),
(14, 'INV-69839E3FED676', 29, '2026-02-04', 22515.00, 22500.00, 'cash_on_delivery', 'pending', NULL, 'normal_delivery', 'pending', NULL, NULL, NULL, NULL, '2026-02-04 19:30:07', NULL, 2, 15.00, NULL, NULL, '', 'pending', NULL, NULL, NULL, NULL),
(15, 'INV-6983A25AB0390', 29, '2026-02-04', 1265.00, 1250.00, 'cash_on_delivery', 'pending', NULL, 'normal_delivery', 'pending', NULL, NULL, NULL, NULL, '2026-02-04 19:47:38', NULL, NULL, 15.00, NULL, NULL, '', 'pending', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `order_bank_transfers`
--

CREATE TABLE `order_bank_transfers` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `transferee_name` varchar(255) NOT NULL COMMENT 'اسم المحول',
  `transfer_date` date NOT NULL,
  `transfer_amount` decimal(10,2) NOT NULL,
  `receipt_image` varchar(500) DEFAULT NULL,
  `receipt_verified` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `order_coupons`
--

CREATE TABLE `order_coupons` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `original_total` decimal(10,2) NOT NULL,
  `final_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `status`, `notes`, `created_at`) VALUES
(1, 13, 'approved', 'تم إنشاء الطلب - طريقة الدفع: cod', '2026-02-04 19:26:51'),
(2, 14, 'pending', 'تم إنشاء الطلب - طريقة الدفع: reserve', '2026-02-04 19:30:07'),
(3, 15, 'pending', 'تم إنشاء الطلب - طريقة الدفع: reserve', '2026-02-04 19:47:38');

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
  `total_price` decimal(10,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `size`, `color`, `quantity`, `unit_price`, `total_price`, `product_id`, `image`) VALUES
(1, 1, 'قميص رجالي', 'L', 'أزرق', 2, 75.50, 151.00, NULL, NULL),
(2, 1, 'بنطال جينز', '32', 'أزرق غامق', 1, 99.75, 99.75, NULL, NULL),
(3, 2, 'فستان سهرة', 'M', 'أسود', 1, 150.50, 150.50, NULL, NULL),
(4, 3, 'تيشيرت', 'XL', 'أبيض', 3, 25.00, 75.00, NULL, NULL),
(5, 4, 'جاكيت شتوي', 'L', 'بني', 1, 200.00, 200.00, NULL, NULL),
(6, 4, 'قبعة', 'ONE SIZE', 'رمادي', 2, 60.00, 120.00, NULL, NULL),
(7, 5, 'حذاء رياضي', '42', 'أبيض', 1, 120.00, 120.00, NULL, NULL),
(8, 5, 'جوارب', 'M', 'أسود', 3, 20.00, 60.00, NULL, NULL),
(9, 6, 'حقيبة يد', 'ONE SIZE', 'أسود', 1, 95.00, 95.00, NULL, NULL),
(10, 13, 'قناع طين طبيعي', NULL, NULL, 150, 35.00, 5250.00, 9, 'img/default-product.jpg'),
(11, 13, 'حذاء رياضي', NULL, NULL, 50, 110.00, 5500.00, 2, 'img/default-product.jpg'),
(12, 13, 'قميص قطني رجالي', NULL, NULL, 100, 45.00, 4500.00, 1, 'img/default-product.jpg'),
(13, 13, 'طابعة ليزر', NULL, NULL, 15, 280.00, 4200.00, 6, 'img/default-product.jpg'),
(14, 14, 'حذاء رياضي', NULL, NULL, 0, 110.00, 0.00, 2, 'img/default-product.jpg'),
(15, 14, 'هاتف ذكي', NULL, NULL, 30, 750.00, 22500.00, 3, 'img/default-product.jpg'),
(16, 15, 'حذاء رياضي', NULL, NULL, 2, 110.00, 220.00, 2, 'img/default.jpg'),
(17, 15, 'هاتف ذكي', NULL, NULL, 1, 750.00, 750.00, 3, 'img/default.jpg'),
(18, 15, 'طابعة ليزر', NULL, NULL, 1, 280.00, 280.00, 6, 'img/default.jpg');

-- --------------------------------------------------------

--
-- بنية الجدول `order_reservations`
--

CREATE TABLE `order_reservations` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','expired','confirmed','cancelled') DEFAULT 'active',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `payment_cards`
--

CREATE TABLE `payment_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_type` enum('visa','mastercard','mada') NOT NULL,
  `last_four` varchar(4) DEFAULT NULL,
  `expiry_month` varchar(2) DEFAULT NULL,
  `expiry_year` varchar(4) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(13, 'ala', 'نقدي', 'cash', 'لا يوجد', 1, 1, 'fas fa-money-bill-wave', 'لا يوةجد', '2025-12-26 16:43:47', '2025-12-26 16:43:47'),
(14, 'الدفع عند الاستلام', 'ادفع عند استلام طلبك', 'cash', NULL, 1, 0, 'fas fa-money-bill-wave', NULL, '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(15, 'التحويل البنكي', 'تحويل بنكي إلى حسابنا', 'bank', NULL, 1, 0, 'fas fa-university', NULL, '2026-01-26 18:55:18', '2026-01-26 18:55:18'),
(16, 'بطاقة ائتمان', 'الدفع ببطاقة الائتمان', '', NULL, 1, 0, 'fas fa-credit-card', NULL, '2026-01-26 18:55:18', '2026-01-26 18:55:18');

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
(1, 'قميص قطني رجالي', 'قميص قطني عالي الجودة للرجال', 7, 1, 50.00, 60.00, 45.00, 5.00, 10.00, 1, 0, 'PROD001', NULL, 1, 1, 0, 'active', '2026-01-12 18:36:15', '2026-02-04 19:26:51', 1, 100),
(2, 'حذاء رياضي', 'حذاء رياضي مريح للمشي والجري', 5, 2, 120.00, 150.00, 110.00, 5.00, 8.33, 1, -2, 'PROD002', NULL, 1, 0, 1, 'active', '2026-01-12 18:36:15', '2026-02-04 19:47:38', 1, 50),
(3, 'هاتف ذكي', 'هاتف ذكي بشاشة 6.5 بوصة وذاكرة 128GB', 6, 3, 800.00, 900.00, 750.00, 15.00, 6.25, 1, -1, 'PROD003', NULL, 0, 1, 1, 'active', '2026-01-12 18:36:15', '2026-02-04 19:47:38', 1, 30),
(4, 'تنورة نسائية صيفية', 'تنورة نسائية صيفية قطنية', 8, 1, 40.00, NULL, 35.00, 5.00, 0.00, 1, 80, 'PROD004', NULL, 0, 0, 1, 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 80),
(5, 'حقيبة يد نسائية', 'حقيبة يد جلدية للنساء', 8, 4, 200.00, 250.00, 180.00, 10.00, 10.00, 1, 25, 'PROD005', NULL, 0, 1, 0, 'low_stock', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 5),
(6, 'طابعة ليزر', 'طابعة ليزر ملونة للمكتب', 9, 5, 300.00, 350.00, 280.00, 15.00, 5.71, 1, -1, 'PROD006', '2025-12-31', 1, 0, 1, 'active', '2026-01-12 18:36:15', '2026-02-04 19:47:38', 1, 15),
(7, 'غسول البشرة', 'غسول لطيف للبشرة الحساسة', 1, 6, 25.00, 30.00, 22.00, 5.00, 8.33, 1, 200, 'PROD007', '2026-06-30', 1, 1, 1, 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 200),
(8, 'عطر فلورال', 'عطر نسائي برائحة الأزهار', 2, 7, 150.00, 180.00, 135.00, 10.00, 10.00, 1, 100, 'PROD008', NULL, 1, 1, 0, 'active', '2026-01-12 18:36:15', '2026-01-12 18:36:15', 1, 100),
(9, 'قناع طين طبيعي', 'قناع طين لتنظيف البشرة', 1, 6, 40.00, 50.00, 35.00, 5.00, 12.50, 1, 0, 'PROD009', '2026-12-31', 1, 1, 1, 'active', '2026-01-12 18:36:15', '2026-02-04 19:26:51', 1, 150),
(10, 'alaoi alaoi', 'hj', 10, 5, 200.00, 190.00, 210.00, 5.00, 0.00, 1, 50, 'PROD-88280771-719', '2026-01-30', 0, 0, 1, '', '2026-01-16 18:31:41', '2026-01-16 18:31:41', 1, 0);

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
(1, 1, 'أبيض', '#FFFFFF', '2026-01-12 18:36:15'),
(2, 1, 'أسود', '#000000', '2026-01-12 18:36:15'),
(3, 1, 'أزرق', '#0000FF', '2026-01-12 18:36:15'),
(4, 2, 'أبيض', '#FFFFFF', '2026-01-12 18:36:15'),
(5, 2, 'رمادي', '#808080', '2026-01-12 18:36:15'),
(6, 2, 'أسود', '#000000', '2026-01-12 18:36:15'),
(7, 3, 'أسود', '#000000', '2026-01-12 18:36:15'),
(8, 3, 'أزرق', '#0000FF', '2026-01-12 18:36:15'),
(9, 3, 'فضي', '#C0C0C0', '2026-01-12 18:36:15'),
(10, 4, 'أحمر', '#FF0000', '2026-01-12 18:36:15'),
(11, 4, 'أخضر', '#008000', '2026-01-12 18:36:15'),
(12, 4, 'أصفر', '#FFFF00', '2026-01-12 18:36:15'),
(13, 5, 'أسود', '#000000', '2026-01-12 18:36:15'),
(14, 5, 'بني', '#A52A2A', '2026-01-12 18:36:15'),
(15, 6, 'أسود', '#000000', '2026-01-12 18:36:15'),
(16, 6, 'أبيض', '#FFFFFF', '2026-01-12 18:36:15'),
(17, 7, 'أبيض', '#FFFFFF', '2026-01-12 18:36:15'),
(18, 7, 'أخضر', '#008000', '2026-01-12 18:36:15'),
(19, 8, 'زهري', '#FFC0CB', '2026-01-12 18:36:15'),
(20, 8, 'ذهبي', '#FFD700', '2026-01-12 18:36:15'),
(21, 9, 'بني', '#8B4513', '2026-01-12 18:36:15'),
(22, 9, 'أخضر', '#006400', '2026-01-12 18:36:15'),
(23, 10, 'أرجواني', '#6c63ff', '2026-01-16 18:31:41');

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
(72, 10, 'product_images/1768588301_0_7108da49b691858a26b00bcc9d68daa3_1722665758599_tapUniverse.jpg', 1, 0, '2026-01-16 18:31:41');

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
(1, 1, 'M', '70', '50', '2026-01-12 18:36:15'),
(2, 1, 'L', '74', '54', '2026-01-12 18:36:15'),
(3, 1, 'XL', '78', '58', '2026-01-12 18:36:15'),
(4, 1, 'XXL', '82', '62', '2026-01-12 18:36:15'),
(5, 2, '40', '26', NULL, '2026-01-12 18:36:15'),
(6, 2, '41', '27', NULL, '2026-01-12 18:36:15'),
(7, 2, '42', '27.5', NULL, '2026-01-12 18:36:15'),
(8, 2, '43', '28', NULL, '2026-01-12 18:36:15'),
(9, 4, 'S', '65', '45', '2026-01-12 18:36:15'),
(10, 4, 'M', '70', '50', '2026-01-12 18:36:15'),
(11, 4, 'L', '74', '54', '2026-01-12 18:36:15'),
(12, 5, 'وسط', '30', '15', '2026-01-12 18:36:15'),
(13, 5, 'كبير', '35', '20', '2026-01-12 18:36:15'),
(14, 7, '150 مل', NULL, NULL, '2026-01-12 18:36:15'),
(15, 7, '300 مل', NULL, NULL, '2026-01-12 18:36:15'),
(16, 8, '50 مل', NULL, NULL, '2026-01-12 18:36:15'),
(17, 8, '100 مل', NULL, NULL, '2026-01-12 18:36:15'),
(18, 9, '100 جرام', NULL, NULL, '2026-01-12 18:36:15'),
(19, 9, '200 جرام', NULL, NULL, '2026-01-12 18:36:15'),
(20, 10, '1', '2', '2', '2026-01-16 18:31:41');

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
(1, 'PUR-2024-001', 1, 2450.00, '2024-01-15', 'شراء منتجات العناية بالبشرة', 'in-stock', '2026-01-12 18:36:15', '2026-01-12 18:36:15'),
(2, 'PUR-2024-002', 2, 4135.00, '2024-01-20', 'شراء أجهزة إلكترونية وعطور', 'in-stock', '2026-01-12 18:36:15', '2026-01-12 18:36:15'),
(3, 'PUR-2024-003', 3, 875.00, '2024-02-01', 'إعادة تخزين الملابس', 'in-stock', '2026-01-12 18:36:15', '2026-01-12 18:36:15'),
(4, 'PUR-2024-004', 1, 515.00, '2024-02-10', 'طلب خاص من العميل', 'with-supplier', '2026-01-12 18:36:15', '2026-01-12 18:36:15'),
(5, 'PUR-2024-005', 4, 1500.00, '2024-02-15', 'شراء منتجات تجميل', 'in-stock', '2026-01-12 18:36:15', '2026-01-12 18:36:15');

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
(1, 1, 7, 50, 20.00, 1000.00, '2026-01-12 18:36:15'),
(2, 1, 9, 30, 35.00, 1050.00, '2026-01-12 18:36:15'),
(3, 1, 8, 10, 40.00, 400.00, '2026-01-12 18:36:15'),
(4, 2, 3, 3, 750.00, 2250.00, '2026-01-12 18:36:15'),
(5, 2, 6, 2, 300.00, 600.00, '2026-01-12 18:36:15'),
(6, 2, 8, 5, 130.00, 650.00, '2026-01-12 18:36:15'),
(7, 2, 1, 5, 45.00, 225.00, '2026-01-12 18:36:15'),
(8, 2, 4, 2, 32.50, 65.00, '2026-01-12 18:36:15'),
(9, 3, 4, 15, 35.00, 525.00, '2026-01-12 18:36:15'),
(10, 3, 5, 5, 70.00, 350.00, '2026-01-12 18:36:15'),
(11, 4, 2, 5, 55.00, 275.00, '2026-01-12 18:36:15'),
(12, 4, 1, 3, 45.00, 135.00, '2026-01-12 18:36:15'),
(13, 4, 4, 1, 35.00, 35.00, '2026-01-12 18:36:15'),
(14, 4, 5, 1, 70.00, 70.00, '2026-01-12 18:36:15'),
(15, 5, 7, 40, 22.00, 880.00, '2026-01-12 18:36:15'),
(16, 5, 8, 5, 125.00, 625.00, '2026-01-12 18:36:15');

-- --------------------------------------------------------

--
-- بنية الجدول `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referral_code` varchar(50) NOT NULL,
  `friend_name` varchar(100) DEFAULT NULL,
  `friend_email` varchar(100) DEFAULT NULL,
  `friend_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','registered','completed') DEFAULT 'pending',
  `reward_credited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `referral_rewards`
--

CREATE TABLE `referral_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `reward_type` enum('credit','discount','cash') DEFAULT 'credit',
  `reward_amount` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('pending','credited','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(7, 'RET-2024-001', 1, 1, NULL, '', NULL, NULL, 1, 0.00, 'defective', 'approved', 75.50, 'المنتج به عيب في الخياطة', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(8, 'RET-2024-002', 2, 2, NULL, '', NULL, NULL, 1, 0.00, 'wrong-item', 'completed', 150.50, 'تم استلام المنتج الخطأ', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(9, 'RET-2024-003', 3, 1, NULL, '', NULL, NULL, 2, 0.00, 'damaged', 'pending', 50.00, 'المنتج وصل تالف أثناء الشحن', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(10, 'RET-2024-004', 4, 3, NULL, '', NULL, NULL, 1, 0.00, 'not-needed', 'approved', 200.00, 'لم يعد العميل بحاجة للمنتج', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(11, 'RET-2024-005', 1, 1, NULL, '', NULL, NULL, 1, 0.00, 'defective', 'rejected', 99.75, 'العميل لم يقدم إثباتات كافية', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30'),
(12, 'RET-2024-006', 5, 2, NULL, '', NULL, NULL, 1, 0.00, 'other', 'pending', 120.00, 'المقاس غير مناسب', 1, '2025-11-24 17:27:30', '2025-11-24 17:27:30');

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
-- بنية الجدول `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `user_type` enum('guest','registered') DEFAULT 'guest',
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `user_type`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, '27', 'guest', 2, 4, 'ا', '2026-01-25 18:23:39');

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
-- بنية الجدول `support_centers`
--

CREATE TABLE `support_centers` (
  `id` int(11) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `working_hours` text DEFAULT NULL,
  `map_link` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_number` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `category` enum('technical','billing','account','order','product','general') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','waiting','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `last_reply_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `ticket_number`, `subject`, `category`, `priority`, `status`, `assigned_to`, `last_reply_at`, `created_at`, `updated_at`) VALUES
(1, 27, 'TICKET-20260125-8FFF', 'ت', 'technical', 'urgent', 'open', NULL, NULL, '2026-01-25 19:16:39', '2026-01-25 19:16:39'),
(2, 27, 'TICKET-20260125-7838', 'ت', 'technical', 'urgent', 'open', NULL, NULL, '2026-01-25 19:16:50', '2026-01-25 19:16:50'),
(3, 27, 'TICKET-20260125-84E8', 'ت', 'technical', 'urgent', 'open', NULL, NULL, '2026-01-25 19:17:22', '2026-01-25 19:17:22');

-- --------------------------------------------------------

--
-- بنية الجدول `terms`
--

CREATE TABLE `terms` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `note` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `terms`
--

INSERT INTO `terms` (`id`, `title`, `content`, `note`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'الموافقة على الشروط', 'باستخدامك لموقعنا، فإنك توافق على الالتزام بشروط الاستخدام هذه. إذا لم توافق على هذه الشروط، يرجى عدم استخدام الموقع.', 'يجب على كل مستقر قبول الشروط قبل استخدام الخدمة', 1, 1, '2026-01-26 18:44:36', '2026-01-26 18:44:36'),
(2, 'حقوق الملكية الفكرية', 'جميع المحتويات الموجودة على الموقع، بما في ذلك النصوص، الصور، والشعارات، هي ملكية لمتجر Be Pretty أو لمورديه، وهي محمية بقوانين حقوق النشر.', 'يمنع نسخ أو إعادة نشر أي محتوى دون إذن', 2, 1, '2026-01-26 18:44:36', '2026-01-26 18:44:36'),
(3, 'سياسة الخصوصية', 'نحن نلتزم بحماية بياناتك الشخصية. يرجى مراجعة سياسة الخصوصية الخاصة بنا لفهم كيفية جمعنا واستخدامنا وحمايتنا لبياناتك.', 'سيتم تحديث سياسة الخصوصية دورياً', 3, 1, '2026-01-26 18:44:36', '2026-01-26 18:44:36'),
(4, 'حدود المسؤولية', 'متجر Be Pretty غير مسؤول عن أي أضرار مباشرة أو غير مباشرة ناتجة عن استخدام الموقع أو المنتجات المباعة عليه.', 'يجب قراءة هذه النقطة بعناية', 4, 1, '2026-01-26 18:44:36', '2026-01-26 18:44:36'),
(5, 'التغييرات في الشروط', 'يحق لمتجر Be Pretty تعديل هذه الشروط في أي وقت. سيتم نشر التغييرات على هذه الصفحة وتصبح سارية فوراً.', 'سيتم إعلام المستخدمين بالتحديثات', 5, 1, '2026-01-26 18:44:36', '2026-01-26 18:44:36'),
(6, 'استخدام الموقع', 'يجب أن يكون استخدامك للموقع لأغراض مشروعة فقط. يحظر استخدام الموقع لأي أغراض غير قانونية أو مخالفة للأنظمة المعمول بها.', 'يجب الالتزام بالأنظمة والقوانين المحلية والدولية', 6, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(7, 'حسابات المستخدمين', 'أنت مسؤول عن الحفاظ على سرية حسابك وكلمة المرور الخاصة بك. يجب إبلاغنا فوراً عن أي استخدام غير مصرح لحسابك.', 'حماية الحساب مسؤولية المستخدم', 7, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(8, 'المنتجات والخدمات', 'نحن نحتفظ بالحق في تعديل أو سحب أي منتج أو خدمة في أي وقت دون سابق إنذار. جميع المنتجات تخضع للتوفر.', 'الأسعار والعروض قابلة للتغيير', 8, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(9, 'طرق الدفع', 'نحن نقبل وسائل الدفع المتاحة على الموقع. يجب أن تكون معلومات الدفع دقيقة وصحيحة. نحن لسنا مسؤولين عن أي أخطاء في الدفع من قبل المستخدم.', 'تأكد من صحة معلومات الدفع', 9, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(10, 'التسليم والشحن', 'نحن نسعى لتسليم الطلبات في الوقت المحدد. قد تتأخر أوقات التسليم بسبب ظروف خارجة عن إرادتنا مثل الظروف الجوية أو أحداث القوة القاهرة.', 'أوقات التسليم تقديرية', 10, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(11, 'المرتجعات والاستبدال', 'يجب مراجعة سياسة الإرجاع والاستبدال المنشورة على الموقع. بعض المنتجات قد لا تكون قابلة للإرجاع أو الاستبدال حسب طبيعتها.', 'اقرأ سياسة الإرجاع بعناية', 11, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(12, 'المعلومات الشخصية', 'نلتزم بحماية خصوصيتك. بياناتك الشخصية تستخدم فقط للأغراض المعلنة ولا تباع أو تشارك مع أطراف ثالثة دون موافقتك.', 'راجع سياسة الخصوصية للتفاصيل', 12, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(13, 'التواصل معنا', 'يمكنك التواصل معنا عبر القنوات المعلنة على الموقع. نحن نسعى للرد على جميع الاستفسارات في غضون 48 ساعة عمل.', 'أوقات الدعم من 9 صباحاً حتى 5 مساءً', 13, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(14, 'القوانين المطبقة', 'تخضع هذه الشروط والأحكام لقوانين المملكة العربية السعودية. أي نزاعات تنشأ عن هذه الشروط يتم حلها في المحاكم المختصة.', 'يجب احترام القوانين المحلية', 14, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(15, 'إخلاء المسؤولية', 'نحن لا نضمن أن الموقع سيكون خالياً من الأخطاء أو الفيروسات. استخدام الموقع على مسؤوليتك الخاصة.', 'تأكد من أمان جهازك', 15, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(16, 'صلاحية الشروط', 'إذا تم اعتبار أي بند من هذه الشروط باطلاً أو غير قابل للتطبيق، تبقى بقية البنود سارية المفعول.', 'تعتبر كل فقرة مستقلة', 16, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(17, 'التحديثات والتعديلات', 'نحتفظ بالحق في تحديث وتعديل هذه الشروط في أي وقت. سيتم إشعار المستخدمين بالتغييرات عبر البريد الإلكتروني أو إشعار على الموقع.', 'راجع الشروط دورياً', 17, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(18, 'اللغات', 'في حالة وجود أي تعارض بين النسخة العربية والنسخة الإنجليزية من هذه الشروط، تكون النسخة العربية هي المعتمدة.', 'اللغة العربية هي الأساس', 18, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(19, 'فترة السماح', 'عدم قيامنا بتنفيذ أي حق من حقوقنا لا يعتبر تنازلاً عن هذا الحق. يمكننا تنفيذ حقوقنا في أي وقت نراه مناسباً.', 'نحتفظ بحقوقنا القانونية', 19, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48'),
(20, 'التسعير', 'جميع الأسعار معروضة بالريال السعودي وتشمل الضريبة المضافة. نحتفظ بالحق في تغيير الأسعار دون سابق إنذار.', 'الأسعار قابلة للتغيير', 20, 1, '2026-01-26 18:46:48', '2026-01-26 18:46:48');

-- --------------------------------------------------------

--
-- بنية الجدول `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachments` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `user_id`, `message`, `attachments`, `is_read`, `created_at`) VALUES
(1, 1, 27, 'ىة', NULL, 0, '2026-01-25 19:16:39'),
(2, 2, 27, 'ىة', NULL, 0, '2026-01-25 19:16:50'),
(3, 3, 27, 'ىة', NULL, 0, '2026-01-25 19:17:22');

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
  `username` varchar(50) DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('user','admin','manager','sales','support') DEFAULT 'user',
  `verification_code` varchar(100) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `display_name`, `first_name`, `last_name`, `email`, `phone`, `profile_image`, `password`, `user_type`, `verification_code`, `email_verified`, `created_at`, `last_activity`, `status`, `updated_at`, `address`, `city`, `country`, `gender`, `birth_date`) VALUES
(1, 'ala fisal ali', NULL, NULL, 'ala fisal ali', NULL, 'admin@gmail.com', NULL, NULL, '$2y$10$akwBc540/Wkvnf8N1Fsu2.4fN6zA/PQ9jVierCALonUJRZrrYwjxa', 'admin', '240993', 0, '2025-11-17 11:19:59', '2026-02-04 07:05:13', 'pending', '2026-02-04 07:05:13', NULL, NULL, NULL, NULL, NULL),
(2, 'المسؤول', NULL, NULL, 'المسؤول', NULL, 'alaoi77alsoh@gmail.com', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-11-17 11:21:39', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(3, 'المسؤول الرئيسي', NULL, NULL, 'المسؤول الرئيسي', NULL, 'admin@storthory.com', NULL, NULL, '$2y$10$Eqgbf89ldAMLClkNmSA2RO1ZYXztEAFFuRTDGr72lclsOQeneN5kq', 'admin', NULL, 1, '2025-11-20 14:28:49', '2026-02-16 22:01:44', 'active', '2026-02-16 22:01:44', NULL, NULL, NULL, NULL, NULL),
(4, 'أحمد محمد', NULL, NULL, 'أحمد', 'محمد', 'ahmed@example.com', '0501234567', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(5, 'سارة عبدالله', NULL, NULL, 'سارة', 'عبدالله', 'sara@example.com', '0507654321', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(6, 'خالد علي', NULL, NULL, 'خالد', 'علي', 'khaled@example.com', '0551112233', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(7, 'فاطمة ناصر', NULL, NULL, 'فاطمة', 'ناصر', 'fatima@example.com', '0554445566', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(8, 'ماجد راشد', NULL, NULL, 'ماجد', 'راشد', 'majed@example.com', '0567778889', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 0, '2025-12-02 15:18:36', NULL, 'pending', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(9, 'نورة سعيد', NULL, NULL, 'نورة', 'سعيد', 'noura@example.com', '0569990001', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(10, 'مدير النظام', NULL, NULL, 'مدير', 'النظام', 'admin1@example.com', '0500000001', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(11, 'مدير الدعم', NULL, NULL, 'مدير', 'الدعم', 'support@example.com', '0500000002', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(12, 'المشرف العام', NULL, NULL, 'المشرف', 'العام', 'supervisor@example.com', '0500000003', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-02 15:18:36', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(13, 'علاء عبدالله', NULL, NULL, NULL, NULL, 'alaoi77alosh@gmail.com', NULL, NULL, '$2y$10$lDW5tLrVuHLIrEsgu3NgNO6kJbSjdPneFFBq.i6R7hxRHv1AxIe2y', 'user', NULL, 1, '2025-12-10 13:16:26', NULL, 'pending', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(17, 'علاء عبدالله', NULL, NULL, NULL, NULL, 'alaoi77@gmail.com', '0774252137', NULL, '$2y$10$DPVAjmctAtv3uZPYlf6v3eklSRtyXlJRb5oLux2zuElp.cj6i8vDq', 'admin', NULL, 1, '2025-12-10 13:20:35', NULL, 'pending', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(18, 'علاءيي يي', NULL, NULL, 'علاءيي', 'يي', 'ala@gmail.com', '077425213744', NULL, '$2y$10$eiwMDtuagi57C3gQEDJKHurUahyoCF1Yv3J7KPPqUzOKfr.6ALk82', 'user', NULL, 1, '2025-12-10 14:42:07', NULL, 'active', '2026-01-10 17:38:00', NULL, NULL, NULL, NULL, NULL),
(19, 'Alaoi', NULL, NULL, NULL, NULL, 'alaoi77alosiih@gmail.com', NULL, NULL, '$2y$10$lWrcUDdbi8Y.aNzCqEgFzOYyFJcuAoY/IN6VL74PihxRvBg4L18P6', 'admin', '514150', 0, '2026-01-12 05:10:46', NULL, 'pending', '2026-01-12 05:10:46', NULL, NULL, NULL, NULL, NULL),
(20, 'Alaoi', NULL, NULL, NULL, NULL, 'alaoiii77alosh@gmail.com', NULL, NULL, '$2y$10$iIJyYVxwMbSLX1Tx00nvHuGedjk5Z.Rdjj854WMFSVM5WJ12SD9xS', 'user', '594402', 0, '2026-01-12 05:11:34', NULL, 'pending', '2026-01-12 05:11:34', NULL, NULL, NULL, NULL, NULL),
(21, '233', NULL, NULL, NULL, NULL, 'admin22@storthory.com', NULL, NULL, '$2y$10$a.qIGZxuA.FaYz2Cj3zt8e9nQ1WwrZe3XM9XJS4nXTWsUGZRbVT.K', 'user', '439989', 0, '2026-01-12 05:44:29', NULL, 'pending', '2026-01-12 05:44:29', NULL, NULL, NULL, NULL, NULL),
(22, 'samptick', NULL, NULL, NULL, NULL, 'samptick@storthory.com', '774252137', NULL, '$2y$10$n8dVvIRX.u8RidcQatHEIukx9V4XRhp.YsM4iYs3zgnbrWX247/0O', 'user', '799701', 0, '2026-01-12 05:59:22', NULL, 'pending', '2026-01-12 05:59:22', NULL, NULL, NULL, NULL, NULL),
(23, 'samptick', NULL, NULL, NULL, NULL, 'samptick2@storthory.com', '774252137', NULL, '$2y$10$B91OWJ35wgAERow8/qT29.gqJai9x6KJ8u/tcUyUbsP1v2IOU1ZFC', 'user', '770425', 0, '2026-01-12 06:09:38', NULL, 'pending', '2026-01-12 06:09:38', NULL, NULL, NULL, NULL, NULL),
(24, 'alaoi7', NULL, NULL, NULL, NULL, 'alaoi@gmail.com', '774252137', NULL, '$2y$10$EqiC3IJW.Zw6ygxQ7rpone8kWaygrdgL53Y96zdQqk84Uwx/aT4A.', 'user', '813670', 1, '2026-01-12 18:32:09', NULL, 'pending', '2026-01-12 18:38:25', NULL, NULL, NULL, NULL, NULL),
(25, 'ala', NULL, NULL, NULL, NULL, 'alaoi774252@gmail.com', '774252137', NULL, '$2y$10$dW3.p8YB7bosFTkqVE.Mo.Zcl2zxx4k4zG1XKzrSJE4OaI5GOdfuu', 'user', '908806', 1, '2026-01-12 18:40:52', '2026-01-12 20:06:14', 'active', '2026-01-12 20:06:14', NULL, NULL, NULL, NULL, NULL),
(26, 'Alaoi Fisal', 'alaoi77', 'Alaoi Fisal', 'Alaoi', 'Fisal', 'alaoi.alshoga77@gmail.com', '0774252137', NULL, '$2y$10$VlFJwQtcoVP8x9sDKIHihOWD.rWfq.r5VJ5bgUr4ySGwtWgZ5MnQ6', 'user', '639356', 1, '2026-01-12 19:10:44', '2026-01-12 19:54:19', 'active', '2026-01-12 19:54:19', NULL, NULL, NULL, NULL, NULL),
(27, 'alaoialoai', NULL, NULL, '125', '125', 'admin@admin.com', '774252137', 'uploads/profiles/1769363904_IMG-20240730-WA0013 (2).jpg', '$2y$10$j396T7o/qBhc3aIiCfWNsexGvIo/QzOUaYkbCoTpkbbuHMTWHcY6W', 'user', NULL, 1, '2026-01-23 15:38:59', '2026-01-27 20:24:31', 'active', '2026-01-27 20:24:31', NULL, 'تعز', 'اليمن', 'male', '0000-00-00'),
(28, 'علاء فيصل الشجاع', 'ala2', 'علاء فيصل الشجاع', 'علاء فيصل', 'الشجاع', 'alaoi.alshoga757@gmail.com', '774252137', 'uploads/profiles/1769455969_IMG_20260121_124905.jpg', '$2y$10$A4Pf0gsqOmOARifEHNUgFe/Q5H4Ze8VGF0BRRtNHJ3KRGYbAHN2r2', 'user', '160500', 1, '2026-01-26 19:32:01', '2026-01-26 19:40:02', 'active', '2026-01-26 19:40:02', NULL, NULL, NULL, NULL, NULL),
(29, 'alaa ala', 'ala1', 'alaa ala', 'ala', 'ala', 'ala@storthory.com', '774252137', 'uploads/profiles/1770318942_IMG-20240529-WA0024.jpg', '$2y$10$Coe8PkhuXwGpGlNOynRO3O7.4HsvXDLS25oNNJ8edA8o14./ITkNm', 'user', '669185', 1, '2026-02-04 19:29:10', '2026-02-05 20:35:39', 'active', '2026-02-05 20:35:39', NULL, 'تعز', 'اليمن', 'male', '0000-00-00');

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
(2, 1, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'success', '2025-12-30 18:23:32'),
(4, 1, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', '2026-01-12 05:39:55'),
(5, 1, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', '2026-01-23 15:36:43'),
(7, 3, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', '2026-02-03 14:23:19'),
(8, 3, 'logout', 'تسجيل خروج من النظام', '::1', 'desktop', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'success', '2026-02-14 19:52:20');

-- --------------------------------------------------------

--
-- بنية الجدول `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `title`, `address`, `city`, `country`, `postal_code`, `phone`, `is_default`, `is_active`, `created_at`) VALUES
(1, 27, 'الهاتف', 'الهاتف', 'الخبر', 'السعودية', '45', '774252137', 1, 1, '2026-01-25 18:19:03'),
(2, 28, 'G', 'V', 'الخبر', 'السعودية', '5', '774252137', 1, 1, '2026-01-26 19:37:07');

-- --------------------------------------------------------

--
-- بنية الجدول `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- بنية الجدول `user_wallet`
--

CREATE TABLE `user_wallet` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `points` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `total_earned` decimal(10,2) DEFAULT 0.00,
  `total_spent` decimal(10,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `total_earned`, `total_spent`, `last_updated`) VALUES
(1, 27, 100.00, 150.00, 0.00, '2026-01-25 19:06:55'),
(2, 28, 0.00, 0.00, 0.00, '2026-01-26 19:38:12');

-- --------------------------------------------------------

--
-- بنية الجدول `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal','purchase','refund','reward','referral','bonus') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `balance_before` decimal(10,2) DEFAULT NULL,
  `balance_after` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `transaction_type`, `amount`, `description`, `reference_id`, `status`, `balance_before`, `balance_after`, `created_at`) VALUES
(1, 27, 'deposit', 50.00, 'إيداع عبر mada', NULL, 'completed', 0.00, 50.00, '2026-01-25 19:05:50'),
(2, 27, 'withdrawal', 50.00, 'طلب سحب عبر bank_transfer', NULL, 'pending', 50.00, 0.00, '2026-01-25 19:06:11'),
(3, 27, 'deposit', 100.00, 'إيداع عبر mada', NULL, 'completed', 0.00, 100.00, '2026-01-25 19:06:55');

-- --------------------------------------------------------

--
-- بنية الجدول `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 3253925, 7, '2026-01-10 18:49:28'),
(2, 0, 2, '2026-01-16 18:19:47'),
(5, 3, 2, '2026-02-03 15:17:43');

-- --------------------------------------------------------

--
-- بنية الجدول `withdrawal_requests`
--

CREATE TABLE `withdrawal_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `withdrawal_method` enum('bank_transfer','stc_pay','mada','paypal','vodafone_cash') NOT NULL,
  `account_details` text DEFAULT NULL,
  `status` enum('pending','approved','processing','completed','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `withdrawal_requests`
--

INSERT INTO `withdrawal_requests` (`id`, `user_id`, `amount`, `withdrawal_method`, `account_details`, `status`, `admin_notes`, `processed_at`, `created_at`) VALUES
(1, 27, 50.00, 'bank_transfer', '774252137', 'pending', NULL, NULL, '2026-01-25 19:06:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `bank_transfer_receipts`
--
ALTER TABLE `bank_transfer_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `verification_status` (`verification_status`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_category_status` (`category_id`,`status`),
  ADD KEY `idx_publish_date` (`publish_date`);

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
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_type_status` (`type`,`status`,`is_active`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_subject` (`subject_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `contact_methods`
--
ALTER TABLE `contact_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_settings`
--
ALTER TABLE `contact_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `contact_subjects`
--
ALTER TABLE `contact_subjects`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `delivery_options`
--
ALTER TABLE `delivery_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

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
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `fk_offers_offer_types` (`offer_type_id`);

--
-- Indexes for table `offer_products`
--
ALTER TABLE `offer_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_offer_product` (`offer_id`,`product_id`),
  ADD KEY `fk_offer_products_offer` (`offer_id`),
  ADD KEY `fk_offer_products_product` (`product_id`);

--
-- Indexes for table `offer_types`
--
ALTER TABLE `offer_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `delivery_option_id` (`delivery_option_id`),
  ADD KEY `delivery_address_id` (`delivery_address_id`);

--
-- Indexes for table `order_bank_transfers`
--
ALTER TABLE `order_bank_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `bank_account_id` (`bank_account_id`);

--
-- Indexes for table `order_coupons`
--
ALTER TABLE `order_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_reservations`
--
ALTER TABLE `order_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `status_expires` (`status`,`expires_at`);

--
-- Indexes for table `payment_cards`
--
ALTER TABLE `payment_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_cards` (`user_id`);

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
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_referrer` (`referrer_id`),
  ADD KEY `idx_code` (`referral_code`);

--
-- Indexes for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

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
-- Indexes for table `support_centers`
--
ALTER TABLE `support_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket` (`ticket_id`),
  ADD KEY `idx_user_msg` (`user_id`);

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
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `activity_type` (`activity_type`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_wallet`
--
ALTER TABLE `user_wallet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_transactions` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_withdrawal` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bank_transfer_receipts`
--
ALTER TABLE `bank_transfer_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blog_images`
--
ALTER TABLE `blog_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `blog_products`
--
ALTER TABLE `blog_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_methods`
--
ALTER TABLE `contact_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_settings`
--
ALTER TABLE `contact_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contact_subjects`
--
ALTER TABLE `contact_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_options`
--
ALTER TABLE `delivery_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `offer_products`
--
ALTER TABLE `offer_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `offer_types`
--
ALTER TABLE `offer_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_bank_transfers`
--
ALTER TABLE `order_bank_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_coupons`
--
ALTER TABLE `order_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_reservations`
--
ALTER TABLE `order_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_cards`
--
ALTER TABLE `payment_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `product_imports`
--
ALTER TABLE `product_imports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `support_centers`
--
ALTER TABLE `support_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `user_wallet`
--
ALTER TABLE `user_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- قيود الجداول `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD CONSTRAINT `delivery_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_offers_offer_types` FOREIGN KEY (`offer_type_id`) REFERENCES `offer_types` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `offer_products`
--
ALTER TABLE `offer_products`
  ADD CONSTRAINT `fk_offer_products_offer` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offer_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`delivery_option_id`) REFERENCES `delivery_options` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`delivery_address_id`) REFERENCES `delivery_addresses` (`id`);

--
-- قيود الجداول `order_bank_transfers`
--
ALTER TABLE `order_bank_transfers`
  ADD CONSTRAINT `order_bank_transfers_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_bank_transfers_ibfk_2` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `order_coupons`
--
ALTER TABLE `order_coupons`
  ADD CONSTRAINT `order_coupons_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_coupons_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

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
-- قيود الجداول `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

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
-- قيود الجداول `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- قيود الجداول `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `user_wallet`
--
ALTER TABLE `user_wallet`
  ADD CONSTRAINT `user_wallet_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
