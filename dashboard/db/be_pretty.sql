-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 19 نوفمبر 2025 الساعة 09:21
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
-- Database: `be_pretty`
--

-- --------------------------------------------------------

--
-- بنية الجدول `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `products_count` int(11) DEFAULT 0,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `brands`
--

INSERT INTO `brands` (`id`, `name`, `country`, `website`, `status`, `description`, `products_count`, `logo`) VALUES
(1, 'سامسونج', 'كوريا الجنوبية', 'https://samsung.com', 'active', 'شركة سامسونج هي شركة كورية جنوبية متعددة الجنسيات مقرها في سيول.', 24, ''),
(2, 'أبل', 'الولايات المتحدة', 'https://apple.com', 'active', 'شركة أبل هي شركة تكنولوجيا أمريكية متعددة الجنسيات متخصصة في الإلكترونيات الاستهلاكية.', 18, ''),
(3, 'هواوي', 'الصين', 'https://huawei.com', 'inactive', 'هواوي هي شركة تكنولوجيا صينية متعددة الجنسيات ومزود لحلول الاتصالات.', 12, ''),
(4, 'شاومي', 'الصين', 'https://mi.com', 'active', 'شاومي هي شركة إلكترونيات صينية متعددة الجنسيات مقرها في بكين.', 15, '');

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `parent_id`, `created_at`, `updated_at`, `status`, `sort_order`) VALUES
(1, 'الهواتف والأجهزة', 'جميع أنواع الهواتف والأجهزة الذكية', NULL, NULL, '2025-11-19 06:14:14', '2025-11-19 06:14:14', 'active', 0),
(2, 'أجهزة الكمبيوتر', 'أجهزة الكمبيوتر والمحمول', NULL, NULL, '2025-11-19 06:14:14', '2025-11-19 06:14:14', 'active', 0),
(3, 'الملابس', 'ملابس رجالية ونسائية', NULL, NULL, '2025-11-19 06:14:14', '2025-11-19 06:14:14', 'active', 0),
(6, 'سامسونج', 'هواتف سامسونج', NULL, 1, '2025-11-19 06:14:14', '2025-11-19 06:14:14', 'active', 0),
(7, 'لابتوب', 'أجهزة لابتوب', NULL, 2, '2025-11-19 06:14:14', '2025-11-19 06:14:14', 'active', 0),
(8, 'كمبيوتر مكتبي', 'أجهزة كمبيوتر مكتبية', NULL, 2, '2025-11-19 06:14:14', '2025-11-19 06:14:14', 'active', 0);

-- --------------------------------------------------------

--
-- بنية الجدول `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `rate` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `currencies`
--

INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `country`, `rate`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'الدولار الأمريكي', 'USD', '$', 'الولايات المتحدة', 1.0000, 'active', 'العملة الرسمية للولايات المتحدة الأمريكية وأكثر العملات تداولاً في العالم.', '2025-11-19 07:25:54', '2025-11-19 07:25:54'),
(2, 'اليورو', 'EUR', '€', 'الاتحاد الأوروبي', 0.9200, 'active', 'العملة الرسمية للاتحاد الأوروبي وتستخدم في 19 دولة أوروبية.', '2025-11-19 07:25:54', '2025-11-19 07:25:54'),
(3, 'الريال السعودي', 'SAR', 'ر.س', 'المملكة العربية السعودية', 3.7500, 'active', 'العملة الرسمية للمملكة العربية السعودية.', '2025-11-19 07:25:54', '2025-11-19 07:25:54'),
(4, 'الدرهم الإماراتي', 'AED', 'د.إ', 'الإمارات العربية المتحدة', 3.6725, 'active', 'العملة الرسمية لدولة الإمارات العربية المتحدة.', '2025-11-19 07:25:54', '2025-11-19 07:25:54'),
(5, 'الجنيه الإسترليني', 'GBP', '£', 'المملكة المتحدة', 0.7900, 'active', 'العملة الرسمية للمملكة المتحدة.', '2025-11-19 07:25:54', '2025-11-19 07:25:54');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `user_type` enum('user','admin') DEFAULT 'user',
  `verification_code` varchar(100) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `verification_code`, `email_verified`, `created_at`) VALUES
(1, 'ala fisal ali', 'admin@gmail.com', '$2y$10$akwBc540/Wkvnf8N1Fsu2.4fN6zA/PQ9jVierCALonUJRZrrYwjxa', 'user', '240993', 0, '2025-11-17 14:19:59'),
(2, 'المسؤول', 'alaoi77alsoh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-11-17 14:21:39'),
(3, 'مستخدم جوجل', 'user@gmail.com', '', 'user', NULL, 1, '2025-11-18 05:56:23'),
(4, 'المسؤول الرئيسي', 'admin@storthory.com', '$2y$10$0ztZ164srxDyiWkdLPwRh.2WqJ.fDifzoCx/XxEvAQS2Zq2eZXA3W', 'admin', NULL, 1, '2025-11-18 07:54:31');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
