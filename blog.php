<?php
// تضمين ملف الاتصال بقاعدة البيانات
require_once 'config/database.php';

// بدء الجلسة إذا لزم الأمر
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// جلب الفئات النشطة من نوع المدونة
$categories_query = "
    SELECT id, name 
    FROM categories 
    WHERE type = 'blog' 
    AND status = 'active' 
    AND is_active = 1
    ORDER BY name
";

$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    $categories = $categories_result->fetch_all(MYSQLI_ASSOC);
}

// التحقق من وجود فئة محددة في الرابط
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// جلب المقالات المنشورة فقط (كلها أو حسب الفئة)
$blogs_query = "
    SELECT 
        b.id,
        b.title,
        b.summary,
        b.content,
        b.main_image,
        b.publish_date,
        b.views_count,
        b.shares_count,
        c.id as category_id,
        c.name as category_name
    FROM blogs b
    INNER JOIN categories c ON b.category_id = c.id
    WHERE b.status = 'published'
";

// إضافة شرط الفئة إذا تم اختيار فئة محددة
if ($selected_category > 0) {
    $blogs_query .= " AND b.category_id = $selected_category";
}

$blogs_query .= " ORDER BY b.publish_date DESC";

$blogs_result = $conn->query($blogs_query);
$blogs = [];
if ($blogs_result && $blogs_result->num_rows > 0) {
    $blogs = $blogs_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المدونة | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e83e8c; /* اللون الوردي الموحد */
            --secondary-color: #d83a7c;
            --accent-color: #ff6b9d;
            --light-pink: #fff0f5;
            --dark-pink: #c5377a;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff9fc;
            min-height: 100vh;
            color: #333;
            padding-bottom: 70px;
        }
        
        /* الهيدر الموحد */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 15px rgba(232, 62, 140, 0.3);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }
        
        .logo h2 {
            color: white;
            font-weight: 700;
            margin: 0;
            font-size: 1.8rem;
        }
        
        .icon-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .icon-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .header-icons {
            display: flex;
            gap: 10px;
        }
        
        .main-content {
            margin-top: 100px;
            margin-bottom: 20px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* قسم الفلتر الموحد */
        .filter-section {
            background: white;
            border-radius: 50px;
            padding: 15px 25px;
            margin: 20px 15px 30px;
            box-shadow: 0 5px 20px rgba(232, 62, 140, 0.1);
            border: 1px solid rgba(232, 62, 140, 0.1);
        }
        
        /* عنوان الصفحة الموحد */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50px;
            padding: 50px 40px;
            margin: 20px 15px 30px;
            box-shadow: 0 10px 30px rgba(232, 62, 140, 0.3);
            text-align: center;
            color: white;
        }
        
        .page-header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }
        
        .page-header i {
            color: white;
        }
        
        /* أزرار الفئات الموحدة */
        .blog-category-btn {
            transition: all 0.3s;
            white-space: nowrap;
            min-width: 100px;
            text-align: center;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: white;
            border-radius: 30px !important;
            padding: 10px 20px;
            font-weight: 600;
        }
        
        .blog-category-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(232, 62, 140, 0.4);
        }
        
        .blog-category-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(232, 62, 140, 0.4);
        }
        
        /* بطاقات المقالات الموحدة */
        .blog-post-card {
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .blog-post-card:hover {
            transform: translateY(-10px);
        }
        
        .blog-post-card .card {
            border: none;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(232, 62, 140, 0.1);
            height: 100%;
            transition: all 0.3s;
            border: 1px solid rgba(232, 62, 140, 0.1);
        }
        
        .blog-post-card:hover .card {
            box-shadow: 0 15px 40px rgba(232, 62, 140, 0.2);
        }
        
        .blog-post-card .card-img-top {
            height: 220px;
            object-fit: cover;
            transition: all 0.5s;
        }
        
        .blog-post-card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .blog-post-card .card-body {
            padding: 25px;
        }
        
        .blog-post-card .badge {
            font-size: 0.8rem;
            padding: 8px 15px;
            border-radius: 25px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            color: white;
            font-weight: 500;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .blog-post-card .card-title {
            font-size: 1.2rem;
            line-height: 1.5;
            margin-bottom: 15px;
            color: #333;
            font-weight: 700;
            height: 55px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .blog-post-card .card-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #666;
            height: 70px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            margin-bottom: 20px;
        }
        
        .read-more-link {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .read-more-link:hover {
            color: var(--secondary-color);
            transform: translateX(-5px);
        }
        
        /* شريط التبويب السفلي الموحد */
        .bottom-tab-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -5px 20px rgba(232, 62, 140, 0.1);
            z-index: 1000;
            border-top: 1px solid rgba(232, 62, 140, 0.1);
        }
        
        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #999;
            font-size: 0.8rem;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-item i {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item.active::after {
            content: '';
            position: absolute;
            bottom: -12px;
            width: 30px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
        }
        
        /* الفوتر الموحد */
        footer {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 50px 0 30px;
            margin-top: 50px;
        }
        
        footer h5, footer h6 {
            color: white;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        footer ul {
            padding: 0;
            list-style: none;
        }
        
        footer ul li {
            margin-bottom: 10px;
        }
        
        footer a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        footer a:hover {
            color: white;
            transform: translateX(-5px);
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-left: 10px;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: white;
            color: var(--primary-color) !important;
            transform: translateY(-3px);
        }
        
        /* حالة عدم وجود مقالات */
        .empty-blog {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 30px;
            margin: 20px;
            box-shadow: 0 5px 20px rgba(232, 62, 140, 0.1);
            border: 1px solid rgba(232, 62, 140, 0.1);
        }
        
        .empty-blog-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-blog h3 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .empty-blog p {
            color: #666;
            max-width: 500px;
            margin: 0 auto 25px;
        }
        
        .btn-outline-pink {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline-pink:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(232, 62, 140, 0.3);
        }
        
        /* شريط التمرير الأفقي للفئات */
        .categories-scroll {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 10px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .categories-scroll::-webkit-scrollbar {
            display: none;
        }
        
        /* معلومات المقال */
        .post-meta {
            display: flex;
            gap: 15px;
            color: #999;
            font-size: 0.85rem;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(232, 62, 140, 0.1);
        }
        
        .post-meta i {
            color: var(--primary-color);
            margin-left: 5px;
        }
        
        /* تحسينات للصور */
        .img-placeholder {
            background: linear-gradient(45deg, #fff0f5, #ffe4ec);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 3rem;
            height: 220px;
        }
        
        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .blog-post-card .card-img-top,
            .img-placeholder {
                height: 180px;
            }
            
            .blog-category-btn {
                min-width: 90px;
                font-size: 0.85rem;
                padding: 8px 15px;
            }
            
            .filter-section {
                padding: 12px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .page-header {
                padding: 40px 20px;
            }
            
            .blog-category-btn {
                min-width: 80px;
                font-size: 0.8rem;
                padding: 6px 12px;
            }
        }
        
        /* زر العودة للكل */
        .show-all-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(232, 62, 140, 0.3);
        }
        
        .show-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232, 62, 140, 0.4);
        }
    </style>
</head>
<body>

    <!-- هيدر موحد -->
    <header class="main-header">
        <div class="container-fluid">
            <div class="header-top">
                <div class="logo">
                    <h2><i class="fas fa-heart me-2"></i>Be Pretty</h2>
                </div>
                <div class="header-icons">
                    <button class="icon-btn" id="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="icon-btn" onclick="window.location.href='cart.php'">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
   <!-- \include 'header.php'; ?> -->
    <!-- المحتوى الرئيسي -->
    <div class="main-content container-fluid py-4">
        <!-- عنوان الصفحة -->
        <div class="page-header">
            <h1><i class="fas fa-newspaper me-2"></i>المدونة</h1>
            <p>اكتشف أحدث المقالات والنصائح في عالم الجمال والعناية</p>
        </div>

        <!-- تصفية مقالات المدونة -->
        <div class="filter-section">
            <div class="categories-scroll">
                <div class="d-flex justify-content-start gap-2">
                    <a href="?category=0" class="btn rounded-pill fw-bold blog-category-btn <?php echo $selected_category == 0 ? 'active' : ''; ?>" data-category="0">
                        <i class="fas fa-th-large me-1"></i> الكل
                    </a>
                    <?php if(!empty($categories)): ?>
                        <?php foreach($categories as $category): ?>
                            <a href="?category=<?php echo $category['id']; ?>" 
                               class="btn rounded-pill fw-bold blog-category-btn <?php echo $selected_category == $category['id'] ? 'active' : ''; ?>"
                               data-category="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- عرض فئات افتراضية إذا لم توجد فئات -->
                        <a href="?category=1" class="btn rounded-pill fw-bold blog-category-btn <?php echo $selected_category == 1 ? 'active' : ''; ?>" data-category="1">
                            <i class="fas fa-spa me-1"></i> عناية بالبشرة
                        </a>
                        <a href="?category=2" class="btn rounded-pill fw-bold blog-category-btn <?php echo $selected_category == 2 ? 'active' : ''; ?>" data-category="2">
                            <i class="fas fa-fan me-1"></i> عطور
                        </a>
                        <a href="?category=3" class="btn rounded-pill fw-bold blog-category-btn <?php echo $selected_category == 3 ? 'active' : ''; ?>" data-category="3">
                            <i class="fas fa-paint-brush me-1"></i> مكياج
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- قسم المقالات -->
        <section class="blog-posts-section">
            <?php if(empty($blogs)): ?>
                <!-- حالة عدم وجود مقالات -->
                <div class="empty-blog">
                    <div class="empty-blog-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>لا توجد مقالات في هذه الفئة</h3>
                    <p>لم يتم العثور على مقالات تطابق اختيارك. يمكنك تصفح جميع المقالات أو العودة لاحقاً.</p>
                    <a href="?category=0" class="btn show-all-btn">
                        <i class="fas fa-eye me-2"></i>عرض جميع المقالات
                    </a>
                </div>
            <?php else: ?>
                <!-- عرض المقالات -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 px-3">
                    <?php foreach($blogs as $blog): ?>
                        <?php 
                        // حساب وقت القراءة التقريبي
                        $wordCount = str_word_count(strip_tags($blog['content']));
                        $readingTime = max(1, ceil($wordCount / 200));
                        
                        // معالجة الصورة
                        $imageSrc = !empty($blog['main_image']) ? htmlspecialchars($blog['main_image']) : '';
                        $imageAlt = htmlspecialchars($blog['title']);
                        ?>
                        
                        <div class="col blog-post-card">
                            <a href="blog-details.php?id=<?php echo $blog['id']; ?>" class="text-decoration-none">
                                <div class="card">
                                    <?php if(!empty($imageSrc)): ?>
                                        <img src="<?php echo $imageSrc; ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo $imageAlt; ?>"
                                             onerror="this.src='https://via.placeholder.com/400x300/e83e8c/ffffff?text=<?php echo urlencode(substr($blog['title'], 0, 20)); ?>'">
                                    <?php else: ?>
                                        <div class="img-placeholder">
                                            <i class="fas fa-newspaper"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <span class="badge">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($blog['category_name']); ?>
                                        </span>
                                        
                                        <h5 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h5>
                                        
                                        <p class="card-text">
                                            <?php 
                                            if(!empty($blog['summary'])) {
                                                echo htmlspecialchars($blog['summary']);
                                            } else {
                                                $summary = strip_tags($blog['content']);
                                                echo htmlspecialchars(mb_substr($summary, 0, 100)) . '...';
                                            }
                                            ?>
                                        </p>
                                        
                                        <div class="read-more-link">
                                            اقرأ المزيد 
                                            <i class="fas fa-arrow-left"></i>
                                        </div>
                                        
                                        <div class="post-meta">
                                            <span>
                                                <i class="fas fa-clock"></i> 
                                                <?php echo $readingTime; ?> دقائق
                                            </span>
                                            <span>
                                                <i class="fas fa-eye"></i> 
                                                <?php echo number_format($blog['views_count']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-share"></i> 
                                                <?php echo number_format($blog['shares_count']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- فوتر موحد -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-heart me-2"></i>Be Pretty</h5>
                    <p>نقدم لك أحدث المنتجات والنصائح في عالم الجمال والعناية</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-snapchat"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>روابط سريعة</h6>
                    <ul>
                        <li><a href="home.php">الرئيسية</a></li>
                        <li><a href="products.php">المنتجات</a></li>
                        <li><a href="blog.php">المدونة</a></li>
                        <li><a href="about.php">عن الموقع</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>معلومات الاتصال</h6>
                    <ul>
                        <li><i class="fas fa-phone ms-2"></i>+966 123 456 789</li>
                        <li><i class="fas fa-envelope ms-2"></i>info@bepretty.com</li>
                        <li><i class="fas fa-map-marker-alt ms-2"></i>الرياض، المملكة العربية السعودية</li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>ساعات العمل</h6>
                    <ul>
                        <li>السبت - الخميس: ٩ص - ١٠م</li>
                        <li>الجمعة: مغلق</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-white opacity-25">
            <div class="text-center">
                <p class="mb-0">جميع الحقوق محفوظة &copy; 2024 Be Pretty</p>
            </div>
        </div>
    </footer>

    <!-- شريط التبويب السفلي -->
    <div class="bottom-tab-bar">
        <a href="home.php" class="tab-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="categories.php" class="tab-item">
            <i class="fas fa-th-large"></i>
            <span>الأقسام</span>
        </a>
        <a href="favorites.php" class="tab-item">
            <i class="fas fa-heart"></i>
            <span>المفضلة</span>
        </a>
        <a href="blog.php" class="tab-item active">
            <i class="fas fa-newspaper"></i>
            <span>المدونة</span>
        </a>
        <a href="profile.php" class="tab-item">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // زر البحث
            $('#search-btn').on('click', function() {
                alert('ميزة البحث قريباً...');
            });
            
            // تأثير hover للبطاقات
            $('.blog-post-card').hover(
                function() {
                    $(this).find('.card').css('box-shadow', '0 20px 40px rgba(232, 62, 140, 0.2)');
                },
                function() {
                    $(this).find('.card').css('box-shadow', '0 5px 20px rgba(232, 62, 140, 0.1)');
                }
            );
            
            // تفعيل التمرير السلس للفئات
            $('.blog-category-btn').on('click', function() {
                // إزالة التنشيط من جميع الأزرار
                $('.blog-category-btn').removeClass('active');
                $(this).addClass('active');
            });
            
            // إضافة تأثير حركة للمقالات عند التحميل
            $('.blog-post-card').each(function(index) {
                $(this).css({
                    'animation': `fadeInUp 0.5s ease-out ${index * 0.1}s both`
                });
            });
        });
        
        // إضافة animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>