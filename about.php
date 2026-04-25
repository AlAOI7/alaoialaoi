<?php
require_once 'config/database.php';

// جلب بيانات "من نحن"
$aboutQuery = "SELECT * FROM about WHERE is_active = 1 LIMIT 1";
$aboutResult = $conn->query($aboutQuery);
$about = $aboutResult->fetch_assoc();

// تحويل البيانات من JSON إلى arrays
$values = !empty($about['values']) ? json_decode($about['values'], true) : [];
$features = !empty($about['features']) ? json_decode($about['features'], true) : [];

// إذا لم تكن هناك بيانات، استخدم القيم الافتراضية
if (!$about) {
    $about = [
        'company_name' => 'Be Pretty',
        'story' => 'نؤمن بأن الجمال ينبع من الداخل، ونحن هنا لمساعدتك على إظهار جمالك الخارجي بأفضل المنتجات.',
        'vision' => 'أن نكون وجهتك الأولى لكل ما يتعلق بالجمال والعناية الشخصية.',
        'mission' => 'تقديم منتجات جمالية وعناية شخصية عالية الجودة.'
    ];
}
?>
<?php require_once 'header.php'; ?>
    <style>
        :root {
            --primary-color: #e91e63;
            --secondary-color: #9c27b0;
            --accent-color: #ff4081;
            --light-pink: #fce4ec;
            --text-dark: #333;
            --text-light: #666;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #fce4ec 100%);
            min-height: 100vh;
        }
        
        /* الهيدر */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        /* قسم البطل */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('<?php echo $about['hero_image'] ?? 'img/about-hero.jpg'; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
            position: relative;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .company-logo {
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* البطاقات */
        .about-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        
        .about-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }
        
        .card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 28px;
        }
        
        .value-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-color);
        }
        
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .feature-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            margin: 5px;
            font-size: 0.9rem;
        }
        
        /* معلومات الاتصال */
        .contact-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin: 40px 0;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .contact-item:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            font-size: 20px;
        }
        
        /* شريط التنقل السفلي */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-around;
            padding: 15px 0;
            z-index: 1000;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-item.active {
            color: var(--primary-color);
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            top: -10px;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .nav-item i {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .nav-item span {
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* تأثيرات النص */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        
        /* متجاوب */
        @media (max-width: 768px) {
            .hero-section {
                padding: 50px 0;
            }
            
            .company-logo {
                width: 100px;
                height: 100px;
            }
            
            .contact-info {
                padding: 25px;
            }
            
            .about-card {
                padding: 20px;
            }
</style>

    <!-- قسم البطل -->
    <section class="hero-section">
        <div class="container text-center">
            <?php if(!empty($about['logo'])): ?>
            <img src="<?php echo htmlspecialchars($about['logo']); ?>" 
                 alt="<?php echo htmlspecialchars($about['company_name']); ?>" 
                 class="company-logo animate__animated animate__fadeIn"
                 onerror="this.src='img/default-logo.png'">
            <?php endif; ?>
            
            <h1 class="display-5 fw-bold mb-3 animate__animated animate__fadeInUp">
                <?php echo htmlspecialchars($about['company_name']); ?>
            </h1>
            <p class="lead mb-0 animate__animated animate__fadeInUp animate__delay-1s">
                وجهتك الأولى للجمال والعناية الشخصية
            </p>
        </div>
    </section>

    <main class="container py-4" style="padding-bottom: 100px;">
        <!-- قصة الشركة -->
        <div class="about-card animate__animated animate__fadeIn">
            <div class="card-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="text-center gradient-text mb-4">قصتنا</h3>
            <p class="text-center lead text-muted">
                <?php echo nl2br(htmlspecialchars($about['story'] ?? 'بدأت رحلتنا برؤية واضحة: جعل الجمال في متناول الجميع.')); ?>
            </p>
        </div>

        <!-- الرؤية والرسالة -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="about-card h-100 animate__animated animate__fadeInLeft">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4 class="text-center gradient-text mb-3">رؤيتنا</h4>
                    <p class="text-center text-muted">
                        <?php echo nl2br(htmlspecialchars($about['vision'] ?? 'أن نكون وجهتك الأولى لكل ما يتعلق بالجمال والعناية الشخصية.')); ?>
                    </p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="about-card h-100 animate__animated animate__fadeInRight">
                    <div class="card-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="text-center gradient-text mb-3">رسالتنا</h4>
                    <p class="text-center text-muted">
                        <?php echo nl2br(htmlspecialchars($about['mission'] ?? 'تقديم منتجات جمالية وعناية شخصية عالية الجودة.')); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- قيمنا -->
        <?php if(!empty($values)): ?>
        <div class="mb-5">
            <h3 class="section-title gradient-text">قيمنا</h3>
            <div class="row g-4">
                <?php foreach($values as $key => $value): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="value-card animate__animated animate__fadeInUp">
                        <div class="mb-3">
                            <i class="fas fa-star fa-2x" style="color: var(--primary-color);"></i>
                        </div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($key); ?></h5>
                        <p class="text-muted small"><?php echo htmlspecialchars($value); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- مميزاتنا -->
        <?php if(!empty($features)): ?>
        <div class="mb-5">
            <h3 class="section-title gradient-text">مميزاتنا</h3>
            <div class="text-center">
                <?php foreach($features as $key => $feature): ?>
                <span class="feature-badge animate__animated animate__fadeIn">
                    <?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($feature); ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- سياسات الشركة -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="about-card h-100">
                    <h5 class="gradient-text mb-3"><i class="fas fa-shipping-fast me-2"></i> الشحن والتوصيل</h5>
                    <p class="text-muted small">
                        <?php echo nl2br(htmlspecialchars($about['shipping_info'] ?? 'شحن مجاني للطلبات فوق 200 ريال. مدة التوصيل 2-5 أيام عمل.')); ?>
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="about-card h-100">
                    <h5 class="gradient-text mb-3"><i class="fas fa-exchange-alt me-2"></i> سياسة الإرجاع</h5>
                    <p class="text-muted small">
                        <?php echo nl2br(htmlspecialchars($about['return_policy'] ?? 'يمكنك إرجاع المنتج خلال 14 يومًا من تاريخ الاستلام بشرط أن يكون بحالته الأصلية.')); ?>
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="about-card h-100">
                    <h5 class="gradient-text mb-3"><i class="fas fa-clock me-2"></i> ساعات العمل</h5>
                    <p class="text-muted small">
                        <?php echo nl2br(htmlspecialchars($about['working_hours'] ?? 'من الأحد إلى الخميس: 9 صباحاً - 11 مساءً')); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- معلومات الاتصال -->
        <div class="contact-info animate__animated animate__fadeIn">
            <h3 class="text-center mb-4">تواصل معنا</h3>
            <div class="row">
                <?php if(!empty($about['address'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">العنوان</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($about['address']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($about['phone'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">الهاتف</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($about['phone']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($about['email'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">البريد الإلكتروني</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($about['email']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($about['whatsapp'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">واتساب</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($about['whatsapp']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- وسائل التواصل الاجتماعي -->
            <?php if(!empty($about['facebook']) || !empty($about['instagram']) || !empty($about['twitter'])): ?>
            <div class="text-center mt-4">
                <h5 class="mb-3">تابعنا على</h5>
                <div class="d-flex justify-content-center gap-3">
                    <?php if(!empty($about['facebook'])): ?>
                    <a href="<?php echo htmlspecialchars($about['facebook']); ?>" 
                       class="btn btn-light btn-circle" 
                       target="_blank">
                        <i class="fab fa-facebook-f text-primary"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($about['instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($about['instagram']); ?>" 
                       class="btn btn-light btn-circle" 
                       target="_blank">
                        <i class="fab fa-instagram text-danger"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if(!empty($about['twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($about['twitter']); ?>" 
                       class="btn btn-light btn-circle" 
                       target="_blank">
                        <i class="fab fa-twitter text-info"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تأثيرات عند التمرير
        $(window).scroll(function() {
            $('.animate__animated').each(function() {
                const elementTop = $(this).offset().top;
                const elementVisible = 150;
                const windowHeight = $(window).height();
                
                if (elementTop < $(window).scrollTop() + windowHeight - elementVisible) {
                    $(this).addClass('animate__fadeInUp');
                }
            });
        });
        
        // تفعيل تأثيرات عند التحميل
        $(document).ready(function() {
            $('.animate__animated').addClass('animate__fadeInUp');
        });
        
        // تأثيرات الأزرار
        $('.btn-circle').hover(
            function() { $(this).css('transform', 'scale(1.1)'); },
            function() { $(this).css('transform', 'scale(1)'); }
        );
    </script>
<?php require_once 'footer.php'; ?>
<?php
if (isset($conn)) {
    $conn->close();
}
?>