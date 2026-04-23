<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الاختصارات | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e83e8c;
            --secondary-color: #d83a7c;
            --accent-color: #ff6b9d;
            --light-pink: #fff0f5;
            --dark-pink: #c5377a;
            --gradient: linear-gradient(135deg, #e83e8c, #d83a7c);
        }
        
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fef6f9;
            color: #333;
            padding-bottom: 70px;
        }
        
        /* الهيدر الموحد */
        .main-header {
            background: var(--gradient);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(232, 62, 140, 0.3);
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
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }
        
        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1) rotate(5deg);
        }
        
        .header-icons {
            display: flex;
            gap: 10px;
        }
        
        /* عنوان الصفحة */
        .page-title-section {
            background: linear-gradient(135deg, #fff0f5, #ffe4ec);
            padding: 30px 20px;
            margin-top: 80px;
            text-align: center;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .page-title-section h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }
        
        .page-title-section p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        /* تصميم بطاقات الاختصارات الجديدة */
        .shortcuts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .shortcut-item {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .shortcut-card {
            background: white;
            border-radius: 25px;
            padding: 25px 15px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(232, 62, 140, 0.1);
            border: 2px solid transparent;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .shortcut-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }
        
        .shortcut-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-color);
            box-shadow: 0 20px 40px rgba(232, 62, 140, 0.2);
        }
        
        .shortcut-card:hover::before {
            transform: translateX(0);
        }
        
        .shortcut-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #fff0f5, #ffe4ec);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            transition: all 0.4s ease;
        }
        
        .shortcut-card:hover .shortcut-icon {
            background: var(--gradient);
            transform: rotateY(360deg);
            border-radius: 40% 60% 30% 70% / 50% 40% 60% 50%;
        }
        
        .shortcut-icon i {
            font-size: 2.5rem;
            color: var(--primary-color);
            transition: all 0.4s ease;
        }
        
        .shortcut-card:hover .shortcut-icon i {
            color: white;
            transform: scale(1.1);
        }
        
        .shortcut-card h5 {
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
            font-size: 1.1rem;
        }
        
        .shortcut-card p {
            color: #999;
            font-size: 0.8rem;
            margin: 0;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .shortcut-card:hover p {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* بطاقات مميزة */
        .shortcut-card.featured {
            background: linear-gradient(135deg, #fff0f5, white);
            border: 2px solid var(--primary-color);
            grid-column: span 2;
        }
        
        .shortcut-card.featured .shortcut-icon {
            background: var(--gradient);
        }
        
        .shortcut-card.featured .shortcut-icon i {
            color: white;
        }
        
        /* قسم الفئات السريعة */
        .quick-categories {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 40px;
            box-shadow: 0 15px 35px rgba(232, 62, 140, 0.1);
        }
        
        .quick-categories h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
        }
        
        .quick-categories h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }
        
        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }
        
        .category-chip {
            padding: 12px 25px;
            background: #f8f9fa;
            border-radius: 50px;
            text-decoration: none;
            color: #666;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .category-chip:hover {
            background: var(--gradient);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(232, 62, 140, 0.3);
        }
        
        .category-chip i {
            margin-left: 8px;
        }
        
        /* شريط التبويب السفلي */
        .bottom-tab-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -5px 25px rgba(232, 62, 140, 0.15);
            z-index: 1000;
            border-top: 2px solid var(--primary-color);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
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
            padding: 5px 0;
        }
        
        .tab-item i {
            font-size: 1.3rem;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item.active i {
            transform: translateY(-3px);
        }
        
        .tab-item.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            width: 25px;
            height: 3px;
            background: var(--gradient);
            border-radius: 3px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                width: 0;
                opacity: 0;
            }
            to {
                width: 25px;
                opacity: 1;
            }
        }
        
        .tab-item:hover {
            color: var(--primary-color);
        }
        
        .tab-item:hover i {
            transform: translateY(-3px);
        }
        
        /* الفوتر */
        footer {
            background: linear-gradient(135deg, #2c2c54, #1a1a3a);
            color: white;
            padding: 50px 0 30px;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient);
        }
        
        footer h5,
        footer h6 {
            color: white;
            margin-bottom: 20px;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        footer h5::after,
        footer h6::after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 0;
            width: 40px;
            height: 3px;
            background: var(--gradient);
            border-radius: 2px;
        }
        
        footer ul {
            padding: 0;
            list-style: none;
        }
        
        footer ul li {
            margin-bottom: 12px;
        }
        
        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        footer a:hover {
            color: white;
            transform: translateX(-5px);
        }
        
        .social-links {
            margin-top: 20px;
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-left: 8px;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--gradient);
            transform: translateY(-5px) rotate(360deg);
        }
        
        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .shortcuts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .shortcut-card.featured {
                grid-column: span 1;
            }
            
            .page-title-section h1 {
                font-size: 1.8rem;
            }
            
            .quick-categories {
                padding: 20px;
            }
            
            .category-chip {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .shortcut-icon {
                width: 60px;
                height: 60px;
            }
            
            .shortcut-icon i {
                font-size: 2rem;
            }
            
            .shortcut-card h5 {
                font-size: 1rem;
            }
            
            .shortcut-card p {
                font-size: 0.7rem;
            }
        }
        
        /* تأثيرات إضافية */
        .ripple-effect {
            position: relative;
            overflow: hidden;
        }
        
        .ripple-effect::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(100, 100);
                opacity: 0;
            }
        }
        
        .ripple-effect:focus:not(:active)::after {
            animation: ripple 0.6s ease-out;
        }
        
        /* شريط التمرير المخصص */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gradient);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
    <!-- إضافة خط Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- هيدر محسن -->
    <header class="main-header">
        <div class="container-fluid">
            <div class="header-top">
                <div class="logo">
                    <h2><i class="fas fa-heart me-2"></i>Be Pretty</h2>
                </div>
                <div class="header-icons">
                    <button class="icon-btn" id="search-btn" title="بحث">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="icon-btn" onclick="window.location.href='cart.php'" title="السلة">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- عنوان الصفحة الجديد -->
    <div class="page-title-section">
        <h1>
            <i class="fas fa-rocket me-2"></i>
            الاختصارات
        </h1>
        <p>وصول سريع إلى جميع أقسام الموقع والصفحات المهمة</p>
    </div>

    <div class="main-content">
        <!-- شبكة الاختصارات الجديدة -->
        <div class="shortcuts-grid">
            <!-- طلباتي - مميز -->
            <a href="order.php" class="shortcut-item">
                <div class="shortcut-card featured">
                    <div class="shortcut-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h5>طلباتي</h5>
                    <p>تتبع طلباتك السابقة</p>
                </div>
            </a>
            
            <!-- المفضلة -->
            <a href="favorites.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5>المفضلة</h5>
                    <p>منتجاتك المفضلة</p>
                </div>
            </a>
            
            <!-- بياناتي -->
            <a href="profile.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5>بياناتي</h5>
                    <p>المعلومات الشخصية</p>
                </div>
            </a>
            
            <!-- الإشعارات -->
            <a href="notifications.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h5>الإشعارات</h5>
                    <p>آخر التحديثات</p>
                </div>
            </a>
            
            <!-- الفئات -->
            <a href="categories.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <h5>الفئات</h5>
                    <p>جميع الأقسام</p>
                </div>
            </a>
            
            <!-- المدونة -->
            <a href="blog.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-blog"></i>
                    </div>
                    <h5>المدونة</h5>
                    <p>أحدث المقالات</p>
                </div>
            </a>
            
            <!-- من نحن -->
            <a href="about.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>من نحن</h5>
                    <p>تعرف علينا</p>
                </div>
            </a>
            
            <!-- تواصل معنا -->
            <a href="contact.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>تواصل معنا</h5>
                    <p>نحن هنا لمساعدتك</p>
                </div>
            </a>
            
            <!-- الشحن والتوصيل -->
            <a href="shipping.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h5>الشحن</h5>
                    <p>معلومات التوصيل</p>
                </div>
            </a>
            
            <!-- الأسئلة الشائعة -->
            <a href="faq.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h5>الأسئلة</h5>
                    <p>استفسارات متكررة</p>
                </div>
            </a>
            
            <!-- الشروط والأحكام -->
            <a href="terms.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h5>الشروط</h5>
                    <p>الأحكام والقوانين</p>
                </div>
            </a>
            
            <!-- الدعم الفني -->
            <a href="support.php" class="shortcut-item">
                <div class="shortcut-card">
                    <div class="shortcut-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h5>الدعم الفني</h5>
                    <p>مساعدة فورية</p>
                </div>
            </a>
        </div>
        
        <!-- قسم الفئات السريعة -->
        <div class="quick-categories">
            <h3>
                <i class="fas fa-compass me-2"></i>
                تصفح سريع
            </h3>
            <div class="category-chips">
                <a href="products.php?category=1" class="category-chip">
                    <i class="fas fa-spa"></i>
                    العناية بالبشرة
                </a>
                <a href="products.php?category=2" class="category-chip">
                    <i class="fas fa-fan"></i>
                    العطور
                </a>
                <a href="products.php?category=3" class="category-chip">
                    <i class="fas fa-paint-brush"></i>
                    المكياج
                </a>
                <a href="products.php?category=4" class="category-chip">
                    <i class="fas fa-cut"></i>
                    العناية بالشعر
                </a>
                <a href="products.php?category=5" class="category-chip">
                    <i class="fas fa-hand-holding-heart"></i>
                    العروض
                </a>
                <a href="products.php?category=6" class="category-chip">
                    <i class="fas fa-gift"></i>
                    الهدايا
                </a>
            </div>
        </div>
    </div>

    <!-- فوتر محسن -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-heart me-2"></i>Be Pretty</h5>
                    <p>نقدم لك أحدث المنتجات والنصائح في عالم الجمال والعناية مع تجربة تسوق فريدة ومميزة.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-snapchat"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h6>روابط سريعة</h6>
                    <ul>
                        <li><a href="home.php"><i class="fas fa-chevron-left ms-2 fa-sm"></i>الرئيسية</a></li>
                        <li><a href="products.php"><i class="fas fa-chevron-left ms-2 fa-sm"></i>المنتجات</a></li>
                        <li><a href="blog.php"><i class="fas fa-chevron-left ms-2 fa-sm"></i>المدونة</a></li>
                        <li><a href="about.php"><i class="fas fa-chevron-left ms-2 fa-sm"></i>عن الموقع</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>معلومات الاتصال</h6>
                    <ul>
                        <li><i class="fas fa-phone ms-2"></i>+966 123 456 789</li>
                        <li><i class="fas fa-envelope ms-2"></i>info@bepretty.com</li>
                        <li><i class="fas fa-map-marker-alt ms-2"></i>الرياض، المملكة العربية السعودية</li>
                        <li><i class="fas fa-clock ms-2"></i>السبت - الخميس: ٩ص - ١٠م</li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6>النشرة البريدية</h6>
                    <p>اشترك لتصلك أحدث العروض والتحديثات</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="بريدك الإلكتروني">
                        <button class="btn" style="background: var(--gradient); color: white;" type="button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr class="bg-white opacity-25">
            <div class="text-center">
                <p class="mb-0">جميع الحقوق محفوظة &copy; 2024 Be Pretty | تصميم وتطوير <i class="fas fa-heart text-danger"></i></p>
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
        <a href="shortcuts.php" class="tab-item active">
            <i class="fas fa-rocket"></i>
            <span>اختصارات</span>
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
            
            // تأثيرات متقدمة للبطاقات
            $('.shortcut-card').each(function(index) {
                $(this).css({
                    'animation': `cardAppear 0.5s ease-out ${index * 0.05}s both`
                });
            });
            
            // تأثير النقر
            $('.shortcut-item').on('click', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                
                $(this).find('.shortcut-card').css({
                    'transform': 'scale(0.95)',
                    'transition': 'transform 0.2s'
                });
                
                setTimeout(() => {
                    $(this).find('.shortcut-card').css('transform', 'scale(1)');
                }, 200);
                
                setTimeout(() => {
                    window.location.href = href;
                }, 250);
            });
            
            // تأثير التحويم المتقدم
            $('.shortcut-card').hover(
                function() {
                    $(this).find('.shortcut-icon i').css({
                        'animation': 'shake 0.5s ease'
                    });
                },
                function() {
                    $(this).find('.shortcut-icon i').css('animation', 'none');
                }
            );
        });
        
        // إضافة animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes cardAppear {
                from {
                    opacity: 0;
                    transform: translateY(30px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            @keyframes shake {
                0%, 100% { transform: rotate(0deg); }
                25% { transform: rotate(-10deg); }
                75% { transform: rotate(10deg); }
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-5px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>