<?php
// تضمين ملف الاتصال بقاعدة البيانات
require_once 'config/database.php';

// بدء الجلسة أولاً
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// الحصول على معرف المقالة من الرابط
$blogId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$blog = null;
$images = [];
$products = [];

if ($blogId > 0) {
    try {
        // التحقق من اتصال mysqli
        if (!$conn) {
            throw new Exception('فشل الاتصال بقاعدة البيانات');
        }
        
        // زيادة عدد المشاهدات
        $updateViews = $conn->prepare("UPDATE blogs SET views_count = views_count + 1 WHERE id = ?");
        $updateViews->bind_param("i", $blogId);
        $updateViews->execute();
        $updateViews->close();
        
        // جلب بيانات المقالة
        $stmt = $conn->prepare("
            SELECT 
                b.*,
                c.name as category_name
            FROM blogs b
            INNER JOIN categories c ON b.category_id = c.id
            WHERE b.id = ? AND b.status = 'published'
        ");
        $stmt->bind_param("i", $blogId);
        $stmt->execute();
        $result = $stmt->get_result();
        $blog = $result->fetch_assoc();
        $stmt->close();
        
        // إذا وجدت المقالة، جلب الصور والمنتجات
        if ($blog) {
            // جلب الصور الإضافية للمقالة
            $imagesStmt = $conn->prepare("
                SELECT * FROM blog_images 
                WHERE blog_id = ? 
                ORDER BY sort_order ASC
            ");
            $imagesStmt->bind_param("i", $blogId);
            $imagesStmt->execute();
            $imagesResult = $imagesStmt->get_result();
            while ($row = $imagesResult->fetch_assoc()) {
                $images[] = $row;
            }
            $imagesStmt->close();
            
            // جلب المنتجات المرتبطة بالمقالة
            $productsStmt = $conn->prepare("
                SELECT p.* FROM blog_products bp
                INNER JOIN products p ON bp.product_id = p.id
                WHERE bp.blog_id = ?
                ORDER BY bp.sort_order ASC
                LIMIT 6
            ");
            $productsStmt->bind_param("i", $blogId);
            $productsStmt->execute();
            $productsResult = $productsStmt->get_result();
            while ($row = $productsResult->fetch_assoc()) {
                $products[] = $row;
            }
            $productsStmt->close();
        }
        
    } catch(Exception $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
}

// التحقق من وجود المقالة
if (!$blog) {
    header("Location: blog.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> | المدونة</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #6c63ff;
            --accent-color: #ff6584;
            --light-color: #f8f9ff;
            --dark-color: #2d3748;
            --text-color: #4a5568;
            --border-radius-lg: 20px;
            --border-radius-md: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f4ff 100%);
            color: var(--text-color);
            line-height: 1.7;
            padding-bottom: 80px;
        }

        /* تحسين الصورة الرئيسية */
        .blog-hero-image {
            width: 100%;
            height: 50vh;
            min-height: 300px;
            max-height: 500px;
            object-fit: cover;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .blog-hero-image:hover {
            transform: scale(1.01);
        }

        @media (max-width: 768px) {
            .blog-hero-image {
                height: 35vh;
                min-height: 250px;
                border-radius: 0;
                margin: 0 -12px;
                width: calc(100% + 24px);
                box-shadow: none;
            }
        }

        /* تحسين العنوان */
        .blog-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-color);
            line-height: 1.3;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 15px;
        }

        .blog-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 4px;
            background: linear-gradient(to left, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        @media (max-width: 768px) {
            .blog-title {
                font-size: 2rem;
                margin-bottom: 1rem;
            }
        }

        /* تحسين معلومات المدونة */
        .blog-meta-wrapper {
            background: white;
            border-radius: var(--border-radius-md);
            padding: 1.5rem;
            margin: 2rem 0;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary-color);
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: var(--light-color);
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
        }

        .meta-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .meta-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .meta-text {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.85rem;
            color: var(--text-color);
            opacity: 0.7;
        }

        .meta-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .category-tag {
            background: linear-gradient(135deg, var(--accent-color), #ff4d7c);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(255, 101, 132, 0.3);
        }

        /* تحسين محتوى المقالة */
        .blog-content-container {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 3rem;
            margin: 2.5rem 0;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .blog-content-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to left, var(--primary-color), var(--accent-color));
        }

        .blog-content {
            font-size: 1.15rem;
            line-height: 1.9;
            text-align: justify;
        }

        .blog-content p {
            margin-bottom: 1.5rem;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius-md);
            margin: 2rem 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: var(--transition);
        }

        .blog-content img:hover {
            transform: scale(1.02);
        }

        .blog-content h2,
        .blog-content h3 {
            color: var(--secondary-color);
            margin: 2.5rem 0 1.5rem 0;
            font-weight: 700;
            position: relative;
            padding-right: 15px;
        }

        .blog-content h2::before,
        .blog-content h3::before {
            content: '▸';
            position: absolute;
            right: 0;
            color: var(--accent-color);
        }

        /* معرض الصور */
        .gallery-section {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            margin: 2.5rem 0;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-size: 1.75rem;
            color: var(--dark-color);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .gallery-card {
            position: relative;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: var(--transition);
            cursor: pointer;
            aspect-ratio: 4/3;
        }

        .gallery-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .gallery-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-card:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 1rem;
            transform: translateY(100%);
            transition: var(--transition);
        }

        .gallery-card:hover .gallery-overlay {
            transform: translateY(0);
        }

        /* المنتجات */
        .products-section {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            margin: 2.5rem 0;
            box-shadow: var(--shadow);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: white;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: var(--transition);
            border: 1px solid #eef2ff;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }

        .product-image {
            height: 180px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .product-info {
            padding: 1.25rem;
        }

        .product-name {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
            line-height: 1.4;
            height: 3em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-price {
            color: var(--accent-color);
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .product-button {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            width: 100%;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .product-button:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(74, 107, 255, 0.3);
        }

        /* أزرار المشاركة */
        .share-section {
            background: white;
            border-radius: var(--border-radius-md);
            padding: 2rem;
            margin: 2.5rem 0;
            box-shadow: var(--shadow);
            text-align: center;
            border: 2px dashed #e2e8ff;
        }

        .share-title {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .share-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.2);
            transform: translateY(100%);
            transition: var(--transition);
        }

        .share-btn:hover::before {
            transform: translateY(0);
        }

        .share-btn:hover {
            transform: translateY(-5px) scale(1.1);
        }

        .share-facebook { background: linear-gradient(135deg, #1877F2, #0d5cb6); }
        .share-twitter { background: linear-gradient(135deg, #1DA1F2, #0c85d0); }
        .share-whatsapp { background: linear-gradient(135deg, #25D366, #1da851); }
        .share-telegram { background: linear-gradient(135deg, #0088cc, #006699); }

        .share-tooltip {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .share-btn:hover .share-tooltip {
            opacity: 1;
            visibility: visible;
            bottom: -35px;
        }

        /* شريط التنقل السفلي */
        .bottom-navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 10px 0;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .nav-container {
            max-width: 500px;
            margin: 0 auto;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-color);
            transition: var(--transition);
            padding: 8px 5px;
            border-radius: var(--border-radius-md);
            position: relative;
        }

        .nav-item:hover {
            color: var(--primary-color);
            background: rgba(74, 107, 255, 0.05);
            transform: translateY(-5px);
        }

        .nav-item.active {
            color: var(--primary-color);
        }

        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -10px;
            width: 6px;
            height: 6px;
            background: var(--primary-color);
            border-radius: 50%;
        }

        .nav-icon {
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .nav-label {
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* أزرار التمرير */
        .scroll-top {
            position: fixed;
            bottom: 80px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            box-shadow: 0 5px 20px rgba(74, 107, 255, 0.4);
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-5px) scale(1.1);
        }

        /* تأثيرات الإحساس بالعمق */
        .depth-card {
            position: relative;
            background: white;
            border-radius: var(--border-radius-md);
            box-shadow: 
                0 2px 10px rgba(0,0,0,0.05),
                0 10px 30px rgba(0,0,0,0.1);
            transition: var(--transition);
        }

        .depth-card:hover {
            box-shadow: 
                0 5px 20px rgba(0,0,0,0.1),
                0 20px 50px rgba(0,0,0,0.15);
        }

        /* تحسينات للهواتف */
        @media (max-width: 768px) {
            .blog-content-container,
            .gallery-section,
            .products-section,
            .share-section {
                padding: 1.5rem;
                margin: 1.5rem 0;
                border-radius: var(--border-radius-md);
            }

            .blog-content {
                font-size: 1.1rem;
            }

            .gallery-grid,
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }

            .nav-container {
                padding: 0 1rem;
            }

            .meta-grid {
                grid-template-columns: 1fr;
            }
        }

        /* تأثيرات التحميل */
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* تحسين الفواصل */
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8ff, transparent);
            margin: 3rem 0;
            border: none;
        }
    </style>
</head>
<body>
    <!-- الهيدر -->
    <?php include 'header.php'; ?>

    <!-- زر العودة للأعلى -->
    <div class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-chevron-up"></i>
    </div>

    <!-- المحتوى الرئيسي -->
    <main class="container py-4 fade-in">
        <!-- الصورة الرئيسية -->
        <div class="hero-section mb-4">
            <img src="<?php echo !empty($blog['main_image']) ? htmlspecialchars($blog['main_image']) : 'img/1.jpg'; ?>" 
                 class="blog-hero-image" 
                 alt="<?php echo htmlspecialchars($blog['title']); ?>"
                 onerror="this.src='img/1.jpg'"
                 loading="lazy">
        </div>

        <!-- العنوان والمعلومات -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <!-- العنوان -->
                <h1 class="blog-title text-center text-md-start">
                    <?php echo htmlspecialchars($blog['title']); ?>
                </h1>

                <!-- معلومات المقالة -->
                <div class="blog-meta-wrapper">
                    <div class="meta-grid">
                        <?php if(!empty($blog['category_name'])): ?>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="meta-text">
                                <span class="meta-label">التصنيف</span>
                                <span class="meta-value"><?php echo htmlspecialchars($blog['category_name']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($blog['publish_date'])): ?>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="far fa-calendar"></i>
                            </div>
                            <div class="meta-text">
                                <span class="meta-label">تاريخ النشر</span>
                                <span class="meta-value"><?php echo date('Y-m-d', strtotime($blog['publish_date'])); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="far fa-eye"></i>
                            </div>
                            <div class="meta-text">
                                <span class="meta-label">المشاهدات</span>
                                <span class="meta-value"><?php echo number_format($blog['views_count'] ?? 0); ?></span>
                            </div>
                        </div>
                        
                        <?php if(!empty($blog['author'])): ?>
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="meta-text">
                                <span class="meta-label">الكاتب</span>
                                <span class="meta-value"><?php echo htmlspecialchars($blog['author']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <div class="meta-icon">
                                <i class="far fa-clock"></i>
                            </div>
                            <div class="meta-text">
                                <span class="meta-label">وقت القراءة</span>
                                <span class="meta-value">
                                    <?php 
                                    $wordCount = str_word_count(strip_tags($blog['content'] ?? ''));
                                    $readingTime = ceil($wordCount / 200);
                                    echo max(1, $readingTime) . ' دقيقة';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- محتوى المقالة -->
                <div class="blog-content-container depth-card">
                    <div class="blog-content">
                        <?php 
                        $content = $blog['content'] ?? '';
                        echo nl2br(htmlspecialchars($content)); 
                        ?>
                    </div>
                </div>

                <!-- معرض الصور -->
                <?php if(!empty($images) && count($images) > 0): ?>
                <div class="gallery-section depth-card">
                    <h3 class="section-title">
                        <i class="fas fa-images"></i>
                        معرض الصور
                    </h3>
                    <div class="gallery-grid">
                        <?php foreach($images as $image): ?>
                        <div class="gallery-card" 
                             onclick="openLightbox('<?php echo htmlspecialchars($image['image_path'] ?? 'img/1.jpg'); ?>')">
                            <img src="<?php echo htmlspecialchars($image['image_path'] ?? 'img/1.jpg'); ?>" 
                                 alt="صورة المقالة"
                                 loading="lazy"
                                 onerror="this.src='img/1.jpg'">
                            <div class="gallery-overlay">
                                <small>انقر للتكبير</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- أزرار المشاركة -->
                <div class="share-section">
                    <h4 class="share-title">شارك المقالة</h4>
                    <div class="share-buttons">
                        <a href="#" class="share-btn share-facebook" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f"></i>
                            <span class="share-tooltip">فيسبوك</span>
                        </a>
                        <a href="#" class="share-btn share-twitter" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i>
                            <span class="share-tooltip">تويتر</span>
                        </a>
                        <a href="#" class="share-btn share-whatsapp" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp"></i>
                            <span class="share-tooltip">واتساب</span>
                        </a>
                        <a href="#" class="share-btn share-telegram" onclick="shareOnTelegram()">
                            <i class="fab fa-telegram"></i>
                            <span class="share-tooltip">تلغرام</span>
                        </a>
                    </div>
                </div>

                <!-- المنتجات -->
                <?php if(!empty($products) && count($products) > 0): ?>
                <div class="products-section depth-card">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-bag"></i>
                        منتجات ذُكرت في المقالة
                    </h3>
                    <div class="products-grid">
                        <?php foreach($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'img/1.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name'] ?? 'منتج'); ?>"
                                     loading="lazy"
                                     onerror="this.src='img/1.jpg'">
                                <span class="product-badge">عرض خاص</span>
                            </div>
                            <div class="product-info">
                                <h5 class="product-name"><?php echo htmlspecialchars($product['name'] ?? 'منتج'); ?></h5>
                                <div class="product-price">
                                    <?php echo isset($product['price']) ? number_format($product['price'], 2) : '0.00'; ?> ر.س
                                </div>
                                <a href="product-details.php?id=<?php echo $product['id'] ?? ''; ?>" 
                                   class="product-button">
                                    <i class="fas fa-shopping-cart"></i>
                                    عرض المنتج
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    

    <!-- الفوتر -->
    <?php include 'footer.php'; ?>

    <!-- Lightbox للصور -->
    <div id="lightbox" class="lightbox" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 2000; align-items: center; justify-content: center;">
        <div style="position: relative; max-width: 90%; max-height: 90%;">
            <img id="lightbox-img" src="" alt="" style="max-width: 100%; max-height: 90vh; border-radius: 10px;">
            <button onclick="closeLightbox()" style="position: absolute; top: -40px; right: 0; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // دوال المشاركة
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('<?php echo addslashes($blog['title']); ?>');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${title}`, '_blank');
        }
        
        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?php echo addslashes($blog['title']); ?>');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
        }
        
        function shareOnWhatsApp() {
            const text = encodeURIComponent('<?php echo addslashes($blog['title']); ?> - ' + window.location.href);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }
        
        function shareOnTelegram() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?php echo addslashes($blog['title']); ?>');
            window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
        }
        
        // Lightbox للصور
        function openLightbox(imageSrc) {
            document.getElementById('lightbox-img').src = imageSrc;
            document.getElementById('lightbox').style.display = 'flex';
        }
        
        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
        }
        
        // إغلاق الـ Lightbox بالضغط خارج الصورة
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
        
        // زر العودة للأعلى
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('.scroll-top').addClass('show');
            } else {
                $('.scroll-top').removeClass('show');
            }
        });
        
        // إغلاق الـ Lightbox بمفتاح ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
        
        // تحسين تجربة الصور
        $(document).ready(function() {
            // إضافة تأثير تحميل للصور
            $('img').on('load', function() {
                $(this).addClass('loaded');
            }).on('error', function() {
                $(this).attr('src', 'img/1.jpg');
            });
            
            // تحسين الروابط
            $('a[href^="http"]').attr('target', '_blank');
            
            // تأثيرات تفاعلية
            $('.product-card, .gallery-card').hover(
                function() { $(this).addClass('hover'); },
                function() { $(this).removeClass('hover'); }
            );
            
            // حساب وقت القراءة
            function calculateReadingTime() {
                const content = $('.blog-content').text();
                const wordsPerMinute = 200;
                const wordCount = content.trim().split(/\s+/).length;
                return Math.max(1, Math.ceil(wordCount / wordsPerMinute));
            }
            
            // تحديث وقت القراءة
            const readingTime = calculateReadingTime();
            $('.reading-time').text(readingTime + ' دقيقة');
        });
    </script>
</body>
</html>

<?php
// إغلاق الاتصال بقاعدة البيانات
if (isset($conn)) {
    $conn->close();
}
?>