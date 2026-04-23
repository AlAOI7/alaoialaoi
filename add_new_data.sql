-- سكربت إضافة بيانات جديدة بشكل آمن (لا يعتمد على IDs ثابتة)
-- Add New Data Safely Script

-- 1. إضافة فئة "العناية بالبشرة" الجديدة
INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) 
VALUES ('العناية بالبشرة الجديدة', NULL, 'product', 'active', 1, NOW());

SET @skin_care_id = LAST_INSERT_ID();

-- إضافة فئات فرعية للعناية بالبشرة
INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) VALUES 
('كريمات الوجه', @skin_care_id, 'product', 'active', 1, NOW());
SET @face_creams_id = LAST_INSERT_ID();

INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) VALUES 
('سيروم', @skin_care_id, 'product', 'active', 1, NOW());
SET @serums_id = LAST_INSERT_ID();

-- إضافة منتجات لفئة كريمات الوجه
INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('كريم مرطب للوجه - فيتامين C', 'كريم مرطب غني بفيتامين C لبشرة نضرة', @face_creams_id, 89.99, 120.00, 50, 50, 'active', 1, 1, 1, NOW());
SET @p1 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p1, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('غسول وجه منعش', 'غسول لطيف ينظف البشرة بعمق', @face_creams_id, 59.99, 79.00, 100, 100, 'active', 1, 0, 1, NOW());
SET @p2 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p2, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('ماسك الطين المغربي', 'ماسك طبيعي ينقي المسام', @face_creams_id, 69.99, 0, 45, 45, 'active', 1, 0, 0, NOW());
SET @p3 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p3, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('كريم ليلي مضاد للشيخوخة', 'كريم ليلي غني بالريتينول', @face_creams_id, 179.99, 220.00, 25, 25, 'active', 1, 1, 1, NOW());
SET @p4 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p4, 'img/default.jpg', 1, NOW());

-- إضافة منتجات لفئة السيروم
INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('سيروم هيالورونيك أسيد', 'سيروم مركز لترطيب عميق', @serums_id, 149.99, 199.00, 30, 30, 'active', 1, 1, 1, NOW());
SET @p5 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p5, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('تونر منعش للبشرة', 'تونر يوازن درجة حموضة البشرة', @serums_id, 49.99, 65.00, 80, 80, 'active', 1, 0, 0, NOW());
SET @p6 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p6, 'img/default.jpg', 1, NOW());


-- 2. إضافة فئة "المكياج" الجديدة
INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) 
VALUES ('المكياج الجديد', NULL, 'product', 'active', 1, NOW());

SET @makeup_id = LAST_INSERT_ID();

INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) VALUES 
('أحمر شفاه', @makeup_id, 'product', 'active', 1, NOW());
SET @lips_id = LAST_INSERT_ID();

INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) VALUES 
('عيون', @makeup_id, 'product', 'active', 1, NOW());
SET @eyes_id = LAST_INSERT_ID();

-- منتجات المكياج
INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('أحمر شفاه مات - أحمر', 'أحمر شفاه بتركيبة مخملية', @lips_id, 79.99, 99.00, 60, 60, 'active', 1, 1, 1, NOW());
SET @p7 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p7, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('ماسكارا فوليوم إكسبريس', 'ماسكارا كثافة وطول', @eyes_id, 89.99, 110.00, 70, 70, 'active', 1, 1, 1, NOW());
SET @p8 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p8, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('باليت ظلال عيون', 'باليت 12 لون نيود', @eyes_id, 129.99, 160.00, 40, 40, 'active', 1, 1, 1, NOW());
SET @p9 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p9, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('كونسيلر عالي التغطية', 'يخفي الهالات السوداء', @makeup_id, 69.99, 0, 55, 55, 'active', 1, 0, 1, NOW());
SET @p10 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p10, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('آيلاينر سائل - أسود', 'آيلاينر دقيق وثابت', @eyes_id, 49.99, 0, 90, 90, 'active', 1, 0, 1, NOW());
SET @p11 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p11, 'img/default.jpg', 1, NOW());


-- 3. إضافة فئة "العناية بالشعر"
INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) 
VALUES ('العناية بالشعر', NULL, 'product', 'active', 1, NOW());
SET @hair_id = LAST_INSERT_ID();

INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) VALUES 
('شامبو وبلسم', @hair_id, 'product', 'active', 1, NOW());
SET @shampoo_id = LAST_INSERT_ID();

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('شامبو بالأرجان المغربي', 'شامبو غني بزيت الأرجان', @shampoo_id, 79.99, 95.00, 75, 75, 'active', 1, 1, 1, NOW());
SET @p12 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p12, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('بلسم مرطب للشعر', 'بلسم عميق الترطيب', @shampoo_id, 69.99, 85.00, 70, 70, 'active', 1, 0, 1, NOW());
SET @p13 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p13, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('سيروم للشعر بجوز الهند', 'سيروم مغذي وملمع', @hair_id, 89.99, 0, 45, 45, 'active', 1, 1, 1, NOW());
SET @p14 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p14, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('ماسك الشعر بالكيراتين', 'ماسك علاجي للشعر التالف', @hair_id, 119.99, 150.00, 35, 35, 'active', 1, 1, 0, NOW());
SET @p15 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p15, 'img/default.jpg', 1, NOW());


-- 4. إضافة فئة "العطور"
INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) 
VALUES ('العطور الفاخرة', NULL, 'product', 'active', 1, NOW());
SET @perfume_id = LAST_INSERT_ID();

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('عطر زهري فاخر', 'عطر نسائي بنفحات زهرية', @perfume_id, 299.99, 350.00, 20, 20, 'active', 1, 1, 1, NOW());
SET @p16 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p16, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('عطر عودي شرقي', 'عطر فاخر بنفحات العود', @perfume_id, 499.99, 599.00, 15, 15, 'active', 1, 1, 1, NOW());
SET @p17 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p17, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('بخاخ معطر للجسم', 'بخاخ برائحة الفانيليا', @perfume_id, 79.99, 0, 60, 60, 'active', 1, 0, 1, NOW());
SET @p18 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p18, 'img/default.jpg', 1, NOW());


-- 5. إضافة فئة "الإكسسوارات"
INSERT INTO `categories` (`name`, `parent_id`, `type`, `status`, `is_active`, `created_at`) 
VALUES ('الإكسسوارات', NULL, 'product', 'active', 1, NOW());
SET @acc_id = LAST_INSERT_ID();

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('طقم فرش مكياج', 'طقم 10 فرش احترافية', @acc_id, 149.99, 180.00, 40, 40, 'active', 1, 1, 1, NOW());
SET @p19 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p19, 'img/default.jpg', 1, NOW());

INSERT INTO `products` (`name`, `description`, `category_id`, `selling_price`, `old_price`, `stock`, `quantity`, `status`, `is_active`, `featured`, `new_product`, `created_at`) VALUES
('حقيبة مكياج', 'حقيبة أنيقة لتنظيم المكياج', @acc_id, 89.99, 0, 50, 50, 'active', 1, 0, 1, NOW());
SET @p20 = LAST_INSERT_ID();
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`, `created_at`) VALUES (@p20, 'img/default.jpg', 1, NOW());
