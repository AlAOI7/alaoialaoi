<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الفئة | Be Pretty</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff3366;
            --accent-color: #ff3366;
            --dark-color: #2c2c54;
            --light-color: #f7f7f7;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding-bottom: 70px;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
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
            margin-top: 80px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            position: relative;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .product-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 12px;
        }
        
        .product-info h3 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .product-price {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .product-category {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 5px;
        }
        
        .rating {
            color: #ffc107;
            font-size: 0.8rem;
        }
        
        .add-to-cart-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }
        
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.8);
            color: #ccc;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .favorite-btn.active {
            color: var(--primary-color);
        }
        
        .favorite-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        
        .quick-view-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(255,255,255,0.8);
            color: var(--dark-color);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .quick-view-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        
        .stock-badge {
            position: absolute;
            top: 50px;
            left: 10px;
            background: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
        
        .tab-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #777;
            font-size: 0.8rem;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-item.active {
            color: var(--primary-color);
        }
        
        .tab-item i {
            font-size: 1.2rem;
            margin-bottom: 3px;
        }
        
        .bottom-tab-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: 5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-bar-container {
            position: relative;
            margin: 15px 15px 0;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: none;
            border-radius: 25px;
            background: rgba(255,255,255,0.9);
            font-size: 14px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }
        
        .view-toggle-btn {
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
        
        .view-toggle-btn.active {
            background: rgba(255,255,255,0.3);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .products-list {
            display: none;
        }
        
        .list-item {
            display: flex;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .list-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        
        .list-item-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }
        
        .list-item-info {
            padding: 15px;
            flex-grow: 1;
        }
        
        .list-item-actions {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 15px;
            gap: 10px;
        }
        
        .product-detail-img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .product-detail-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .product-detail-old-price {
            font-size: 1rem;
            color: #777;
            text-decoration: line-through;
            margin-left: 10px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-input {
            width: 40px;
            text-align: center;
            border: none;
            margin: 0 5px;
        }
        
        footer {
            background: var(--dark-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 40px;
        }
        
        footer h5, footer h6 {
            color: white;
            margin-bottom: 15px;
        }
        
        footer ul {
            padding: 0;
            list-style: none;
        }
        
        footer ul li {
            margin-bottom: 8px;
        }
        
        footer a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        footer a:hover {
            color: var(--primary-color);
        }
        
        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-left: 10px;
        }
        
        .social-links a:hover {
            background: var(--primary-color);
        }
        
        @media (min-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .product-img {
                height: 200px;
            }
        }
        
        @media (min-width: 992px) {
            .products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .product-img {
                height: 220px;
            }
        }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-top">
            <a href="categories.php" class="icon-btn"><i class="fas fa-arrow-right"></i></a>
            <h5 class="mb-0 fw-bold" id="category-title">مكياج</h5>
            <div class="header-icons">
                <button id="view-toggle" class="view-toggle-btn active" data-view="grid">
                    <i class="fas fa-th-large"></i>
                </button>
                <button id="view-toggle-list" class="view-toggle-btn" data-view="list">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
        <div class="search-bar-container">
            <input type="text" placeholder="ابحث في المكياج..." class="search-input">
            <i class="fas fa-search search-icon"></i>
        </div>
    </header>
<br><br><br>
    <div class="main-content container py-4">
        <!-- عرض الشبكي -->
        <div id="grid-view" class="products-grid">
            <!-- المنتج 1 -->
            <div class="product-card">
                <a href="product-details.php" class="product-link">
                    <img src="img/1.jpg" alt="كريم الأساس الفاخر" class="product-img">
                </a>
                <div class="product-info">
                    <a href="product-details.php" class="text-decoration-none text-dark">
                        <h3>كريم الأساس الفاخر</h3>
                    </a>
                    <p class="product-category">مكياج</p>
                    <p class="product-price">150 ر.س <small class="text-muted text-decoration-line-through">180 ر.س</small></p>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                </div>
                <span class="stock-badge">15 متبقي</span>
                <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                <button class="favorite-btn"><i class="fas fa-heart"></i></button>
                <button class="quick-view-btn" data-product-id="1"><i class="fas fa-eye"></i></button>
            </div>

            <!-- المنتج 2 -->
            <div class="product-card">
                <a href="product-details.php" class="product-link">
                    <img src="img/2.jpg" alt="مسكارا طويلة الرموش" class="product-img">
                </a>
                <div class="product-info">
                    <a href="product-details.php" class="text-decoration-none text-dark">
                        <h3>مسكارا طويلة الرموش</h3>
                    </a>
                    <p class="product-category">مكياج</p>
                    <p class="product-price">85 ر.س</p>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                </div>
                <span class="stock-badge">22 متبقي</span>
                <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                <button class="favorite-btn"><i class="fas fa-heart"></i></button>
                <button class="quick-view-btn" data-product-id="2"><i class="fas fa-eye"></i></button>
            </div>

            <!-- المنتج 3 -->
            <div class="product-card">
                <a href="product-details.php" class="product-link">
                    <img src="img/3.jpg" alt="أحمر شفاه سائل" class="product-img">
                </a>
                <div class="product-info">
                    <a href="product-details.php" class="text-decoration-none text-dark">
                        <h3>أحمر شفاه سائل</h3>
                    </a>
                    <p class="product-category">مكياج</p>
                    <p class="product-price">65 ر.س <small class="text-muted text-decoration-line-through">80 ر.س</small></p>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                </div>
                <span class="stock-badge">30 متبقي</span>
                <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                <button class="favorite-btn"><i class="fas fa-heart"></i></button>
                <button class="quick-view-btn" data-product-id="3"><i class="fas fa-eye"></i></button>
            </div>

            <!-- المنتج 4 -->
            <div class="product-card">
                <a href="product-details.php" class="product-link">
                    <img src="img/4.jpg" alt="كونسيلر عالي التغطية" class="product-img">
                </a>
                <div class="product-info">
                    <a href="product-details.php" class="text-decoration-none text-dark">
                        <h3>كونسيلر عالي التغطية</h3>
                    </a>
                    <p class="product-category">مكياج</p>
                    <p class="product-price">55 ر.س</p>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                </div>
                <span class="stock-badge">18 متبقي</span>
                <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                <button class="favorite-btn"><i class="fas fa-heart"></i></button>
                <button class="quick-view-btn" data-product-id="4"><i class="fas fa-eye"></i></button>
            </div>
        </div>

        <!-- عرض القائمة -->
        <div id="list-view" class="products-list">
            <!-- المنتج 1 -->
            <div class="list-item">
                <a href="product-details.php">
                    <img src="img/1.jpg" alt="كريم الأساس الفاخر" class="list-item-img">
                </a>
                <div class="list-item-info">
                    <a href="product-details.php" class="text-decoration-none text-dark">
                        <h4>كريم الأساس الفاخر</h4>
                    </a>
                    <p class="text-muted mb-2">مكياج</p>
                    <p class="product-price mb-2">150 ر.س <small class="text-muted text-decoration-line-through">180 ر.س</small></p>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <span class="badge bg-primary mt-2">15 متبقي</span>
                </div>
                <div class="list-item-actions">
                    <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                    <button class="favorite-btn"><i class="fas fa-heart"></i></button>
                    <button class="quick-view-btn" data-product-id="1"><i class="fas fa-eye"></i></button>
                </div>
            </div>

            <!-- المنتج 2 -->
            <div class="list-item">
                <a href="product-details.php">
                    <img src="img/2.jpg" alt="مسكارا طويلة الرموش" class="list-item-img">
                </a>
                <div class="list-item-info">
                    <a href="product-details.php" class="text-decoration-none text-dark">
                        <h4>مسكارا طويلة الرموش</h4>
                    </a>
                    <p class="text-muted mb-2">مكياج</p>
                    <p class="product-price mb-2">85 ر.س</p>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <span class="badge bg-primary mt-2">22 متبقي</span>
                </div>
                <div class="list-item-actions">
                    <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i></button>
                    <button class="favorite-btn"><i class="fas fa-heart"></i></button>
                    <button class="quick-view-btn" data-product-id="2"><i class="fas fa-eye"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة منبثقة لعرض المنتج -->
    <div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailModalLabel">تفاصيل المنتج</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="" alt="منتج" class="product-detail-img" id="product-detail-img">
                        </div>
                        <div class="col-md-6">
                            <h3 id="product-detail-name">اسم المنتج</h3>
                            <p class="product-category" id="product-detail-category">الفئة: مكياج</p>
                            <div class="mb-3">
                                <span class="product-detail-price" id="product-detail-price">150 ر.س</span>
                                <span class="product-detail-old-price" id="product-detail-old-price">180 ر.س</span>
                            </div>
                            <div class="rating mb-3" id="product-detail-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <p id="product-detail-description">وصف المنتج يظهر هنا. هذا المنتج رائع ومميز ويحتوي على مكونات طبيعية.</p>
                            <div class="mb-3">
                                <strong>الكمية المتاحة:</strong> <span id="product-detail-stock">15</span> قطعة
                            </div>
                            <div class="quantity-controls mb-4">
                                <label class="me-2"><strong>الكمية:</strong></label>
                                <button class="quantity-btn">-</button>
                                <input type="text" class="quantity-input" value="1" readonly id="product-detail-quantity">
                                <button class="quantity-btn">+</button>
                            </div>
                            <div class="d-grid gap-2 d-md-flex">
                                <button class="btn btn-danger flex-fill" id="add-to-cart-detail">
                                    <i class="fas fa-shopping-cart me-2"></i>أضف إلى السلة
                                </button>
                                <button class="btn btn-outline-danger flex-fill" id="add-to-favorites-detail">
                                    <i class="fas fa-heart me-2"></i>أضف إلى المفضلة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light pt-5 pb-3">
        <div class="container">
            <div class="row">
                <!-- معلومات المتجر -->
                <div class="col-md-4 mb-4">
                    <h5>Be Pretty</h5>
                    <p>متجرك الأول لمستحضرات التجميل والعناية بالبشرة.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-snapchat"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <!-- روابط سريعة -->
                <div class="col-md-2 mb-4">
                    <h6>روابط سريعة</h6>
                    <ul class="list-unstyled">
                        <li><a href="about.php">من نحن</a></li>
                        <li><a href="contact.php">اتصل بنا</a></li>
                        <li><a href="terms.php">الشروط والأحكام</a></li>
                        <li><a href="blog.php">المدونة</a></li>
                    </ul>
                </div>
                
                <!-- خدمة العملاء -->
                <div class="col-md-3 mb-4">
                    <h6>خدمة العملاء</h6>
                    <ul class="list-unstyled">
                        <li><a href="shipping.php">الشحن والتوصيل</a></li>
                        <li><a href="returns.php">سياسة الإرجاع</a></li>
                        <li><a href="faq.php">الأسئلة الشائعة</a></li>
                        <li><a href="support.php">الدعم الفني</a></li>
                    </ul>
                </div>
                
                <!-- الاشتراك في النشرة البريدية -->
                <div class="col-md-3 mb-4">
                    <h6>اشترك في نشرتنا البريدية</h6>
                    <div class="input-group mb-2">
                        <input type="email" class="form-control" placeholder="بريدك الإلكتروني">
                        <button class="btn btn-danger" type="button">اشتراك</button>
                    </div>
                    <small>احصلي على آخر العروض والتخفيضات</small>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- حقوق النشر -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2023 Be Pretty. جميع الحقوق محفوظة.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="https://via.placeholder.com/200x30?text=طرق+الدفع+المتاحة" alt="طرق الدفع" class="img-fluid">
                </div>
            </div>
        </div>
    </footer>

    <nav class="bottom-tab-bar">
        <a href="home.php" class="tab-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="categories.php" class="tab-item active">
            <i class="fas fa-th-large"></i>
            <span>الفئات</span>
        </a>
        <a href="cart.php" class="tab-item">
            <i class="fas fa-shopping-cart"></i>
            <span>السلة</span>
            <span class="notification-badge">3</span>
        </a>
        <a href="order.php" class="tab-item">
            <i class="fas fa-list-alt"></i>
            <span>الطلبات</span>
        </a>
        <a href="profile.php" class="tab-item">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // بيانات المنتجات
            const products = {
                1: {
                    name: "كريم الأساس الفاخر",
                    category: "مكياج",
                    price: "150 ر.س",
                    oldPrice: "180 ر.س",
                    image: "img/1.jpg",
                    rating: 4,
                    stock: 15,
                    description: "كريم أساس عالي الجودة يمنحك مظهرًا طبيعيًا وناعمًا طوال اليوم. مناسب لجميع أنواع البشرة، ويحتوي على عامل حماية من الشمس SPF 15. خالي من الزيوت وغير كوميدوغينيك (لا يسد المسام)."
                },
                2: {
                    name: "مسكارا طويلة الرموش",
                    category: "مكياج",
                    price: "85 ر.س",
                    oldPrice: null,
                    image: "img/2.jpg",
                    rating: 4,
                    stock: 22,
                    description: "مسكارا طويلة الرموش تمنحك رموشًا طويلة وكثيفة دون تكتل. مقاومة للماء ولا تبهت طوال اليوم. مناسبة للاستخدام اليومي والمناسبات الخاصة."
                },
                3: {
                    name: "أحمر شفاه سائل",
                    category: "مكياج",
                    price: "65 ر.س",
                    oldPrice: "80 ر.س",
                    image: "img/3.jpg",
                    rating: 3,
                    stock: 30,
                    description: "أحمر شفاه سائل طويل الأمد بتقنية مات. لا ينتقل ولا يبهت طوال اليوم. يحتوي على فيتامين E لترطيب الشفاه. متوفر بعدة ألوان تناسب جميع الإطلالات."
                },
                4: {
                    name: "كونسيلر عالي التغطية",
                    category: "مكياج",
                    price: "55 ر.س",
                    oldPrice: null,
                    image: "img/4.jpg",
                    rating: 4,
                    stock: 18,
                    description: "كونسيلر عالي التغطية يخفي الهالات السوداء وعيوب البشرة. سهل التطبيق وطويل الأمد. مناسب لجميع أنواع البشرة وخالي من الزيوت."
                }
            };

            // التبديل بين العرض الشبكي والعرض القائمة
            $('.view-toggle-btn').click(function() {
                const viewType = $(this).data('view');
                
                // تحديث حالة الأزرار
                $('.view-toggle-btn').removeClass('active');
                $(this).addClass('active');
                
                // تبديل العرض
                if (viewType === 'grid') {
                    $('#grid-view').show();
                    $('#list-view').hide();
                } else {
                    $('#grid-view').hide();
                    $('#list-view').show();
                }
            });
            
            // إضافة منتج إلى السلة
            $('.add-to-cart-btn').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // تحديث عدد العناصر في السلة
                const currentCount = parseInt($('.notification-badge').text());
                $('.notification-badge').text(currentCount + 1);
                
                // عرض رسالة نجاح
                alert('تمت إضافة المنتج إلى السلة بنجاح!');
            });
            
            // إضافة منتج إلى المفضلة
            $('.favorite-btn').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                $(this).toggleClass('active');
                
                if ($(this).hasClass('active')) {
                    alert('تمت إضافة المنتج إلى المفضلة!');
                } else {
                    alert('تمت إزالة المنتج من المفضلة!');
                }
            });
            
            // البحث في المنتجات
            $('.search-input').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.product-card, .list-item').each(function() {
                    const productName = $(this).find('h3, h4').text().toLowerCase();
                    
                    if (productName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            
            // إدارة أزرار زيادة ونقصان الكمية
            $('.quantity-btn').click(function() {
                const input = $(this).siblings('.quantity-input');
                let value = parseInt(input.val());
                
                if ($(this).text() === '+') {
                    value++;
                } else if ($(this).text() === '-' && value > 1) {
                    value--;
                }
                
                input.val(value);
            });
            
            // عرض تفاصيل المنتج في النافذة المنبثقة
            $('.quick-view-btn').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = $(this).data('product-id');
                const product = products[productId];
                
                if (product) {
                    // تعبئة البيانات في النافذة المنبثقة
                    $('#product-detail-img').attr('src', product.image);
                    $('#product-detail-name').text(product.name);
                    $('#product-detail-category').text(`الفئة: ${product.category}`);
                    $('#product-detail-price').text(product.price);
                    
                    if (product.oldPrice) {
                        $('#product-detail-old-price').text(product.oldPrice).show();
                    } else {
                        $('#product-detail-old-price').hide();
                    }
                    
                    // تحديث النجوم
                    const ratingStars = $('#product-detail-rating');
                    ratingStars.empty();
                    for (let i = 1; i <= 5; i++) {
                        if (i <= product.rating) {
                            ratingStars.append('<i class="fas fa-star"></i>');
                        } else {
                            ratingStars.append('<i class="far fa-star"></i>');
                        }
                    }
                    
                    $('#product-detail-description').text(product.description);
                    $('#product-detail-stock').text(product.stock);
                    $('#product-detail-quantity').val(1);
                    
                    // إعداد أزرار الإضافة
                    $('#add-to-cart-detail').data('product-id', productId);
                    $('#add-to-favorites-detail').data('product-id', productId);
                    
                    // فتح النافذة المنبثقة
                    $('#productDetailModal').modal('show');
                }
            });
            
            // إضافة منتج إلى السلة من نافذة التفاصيل
            $('#add-to-cart-detail').click(function() {
                const productId = $(this).data('product-id');
                const product = products[productId];
                const quantity = parseInt($('#product-detail-quantity').val());
                
                // تحديث عدد العناصر في السلة
                const currentCount = parseInt($('.notification-badge').text());
                $('.notification-badge').text(currentCount + quantity);
                
                // إغلاق النافذة المنبثقة وعرض رسالة نجاح
                $('#productDetailModal').modal('hide');
                alert(`تمت إضافة ${quantity} من ${product.name} إلى السلة بنجاح!`);
            });
            
            // إضافة منتج إلى المفضلة من نافذة التفاصيل
            $('#add-to-favorites-detail').click(function() {
                const productId = $(this).data('product-id');
                const product = products[productId];
                
                // تحديث حالة زر المفضلة في البطاقة
                $(`.favorite-btn[data-product-id="${productId}"]`).addClass('active');
                
                // إغلاق النافذة المنبثقة وعرض رسالة نجاح
                $('#productDetailModal').modal('hide');
                alert(`تمت إضافة ${product.name} إلى المفضلة!`);
            });
        });
    </script>
</body>
</html>