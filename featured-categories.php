
<!-- الفئات المميزه -->

<section class="featured-categories mb-5">
    <h2 class="section-title">الفئات المميزة</h2>
    <div class="featured-categories-container" id="featured-categories-container">
        <?php 
        // إعادة تعيين المؤشر إذا لزم الأمر
        mysqli_data_seek($featured_categories_result, 0);
        
        while($category = mysqli_fetch_assoc($featured_categories_result)): 
            // تحضير الصورة أو الأيقونة المناسبة
            $category_image = !empty($category['image']) ? $category['image'] : '';
            $has_image = !empty($category_image) && file_exists($category_image);
            $default_image = 'img/1.jpg'; // الصورة الافتراضية
        ?>
            <a href="category-details.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                <div class="featured-category">
                    <?php if($has_image): ?>
                        <img src="<?php echo $category_image; ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                             class="featured-category-img"
                             onerror="this.onerror=null; this.src='<?php echo $default_image; ?>';">
                    <?php else: ?>
                        <!-- استخدام الصورة الافتراضية -->
                        <div class="featured-category-image">
                            <img src="<?php echo $default_image; ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 class="featured-category-default-img">
                            <!-- أو يمكنك استخدام أيقونة فوق الصورة -->
                            <div class="category-overlay">
                                <i class="fas fa-tag"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                    <p class="text-dark fw-bold mt-2"><?php echo htmlspecialchars($category['name']); ?></p>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<style>
    .featured-categories {
        padding: 30px 0;
        background: linear-gradient(to bottom, #fff 0%, #f9f9f9 100%);
    }
    
    .section-title {
        text-align: center;
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 30px;
        position: relative;
        padding-bottom: 15px;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: linear-gradient(135deg, #ff3366, #ff6699);
        border-radius: 2px;
    }
    
    .featured-categories-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 20px;
        padding: 0 15px;
    }
    
    .featured-category {
        background: white;
        border-radius: 15px;
        padding: 20px 12px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #f0f0f0;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .featured-category:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(255, 51, 102, 0.1);
        border-color: #ff3366;
    }
    
    .featured-category-img,
    .featured-category-default-img {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        will-change: transform;
    }
    
    .featured-category:hover .featured-category-img,
    .featured-category:hover .featured-category-default-img {
        transform: scale(1.15);
        box-shadow: 0 8px 25px rgba(255, 51, 102, 0.25);
    }
    
    .featured-category-image {
        position: relative;
        width: 90px;
        height: 90px;
        margin-bottom: 15px;
    }
    
    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 51, 102, 0.7);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .featured-category:hover .category-overlay {
        opacity: 1;
    }
    
    .featured-category p {
        font-size: 1rem;
        margin-top: 15px;
        color: #333;
        transition: color 0.3s ease;
        text-align: center;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        max-height: 2.8em;
    }
    
    .featured-category:hover p {
        color: #ff3366;
    }
    
    /* تصميم متجاوب */
    @media (max-width: 992px) {
        .featured-categories-container {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 18px;
        }
        
        .featured-category-img,
        .featured-category-default-img {
            width: 80px;
            height: 80px;
        }
        
        .featured-category-image {
            width: 80px;
            height: 80px;
        }
    }
    
    @media (max-width: 768px) {
        .featured-categories-container {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        
        .featured-category {
            padding: 15px 10px;
        }
        
        .featured-category-img,
        .featured-category-default-img {
            width: 70px;
            height: 70px;
        }
        
        .featured-category-image {
            width: 70px;
            height: 70px;
        }
    }
    
    @media (max-width: 480px) {
        .featured-categories-container {
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 12px;
        }
        
        .featured-category-img,
        .featured-category-default-img {
            width: 65px;
            height: 65px;
        }
        
        .featured-category-image {
            width: 65px;
            height: 65px;
        }
        
        .featured-category p {
            font-size: 0.85rem;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .featured-category-img,
        .featured-category-default-img {
            transition: none;
        }
    }
</style>

<!-- عرض الايقونات بدال الصور -->
<!-- <section class="featured-categories mb-5">
    <h2 class="section-title">تصفح الفئات</h2>
    <div class="featured-categories-container" id="featured-categories-container">
        <?php 
        mysqli_data_seek($featured_categories_result, 0);
        
        while($category = mysqli_fetch_assoc($featured_categories_result)): 
            // أيقونة حسب نوع الفئة
            $category_name = strtolower($category['name']);
            $icon_class = 'fas fa-tag'; // أيقونة افتراضية
            
            if (strpos($category_name, 'مكياج') !== false) $icon_class = 'fas fa-paint-brush';
            elseif (strpos($category_name, 'عطور') !== false) $icon_class = 'fas fa-spray-can';
            elseif (strpos($category_name, 'بشرة') !== false) $icon_class = 'fas fa-spa';
            elseif (strpos($category_name, 'شعر') !== false) $icon_class = 'fas fa-cut';
            elseif (strpos($category_name, 'عناية') !== false) $icon_class = 'fas fa-heart';
            elseif (strpos($category_name, 'رجال') !== false) $icon_class = 'fas fa-male';
            elseif (strpos($category_name, 'نساء') !== false) $icon_class = 'fas fa-female';
        ?>
            <a href="category-details.php?id=<?php echo $category['id']; ?>" class="category-card">
                <div class="category-icon">
                    <i class="<?php echo $icon_class; ?>"></i>
                </div>
                <div class="category-name">
                    <?php echo htmlspecialchars($category['name']); ?>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</section> -->

<!-- <style>
    .featured-categories {
        padding: 40px 0;
        background: #fff;
    }
    
    .section-title {
        text-align: center;
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 40px;
        font-weight: 700;
    }
    
    .featured-categories-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 25px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .category-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 140px;
        padding: 20px 15px;
        background: #fff;
        border-radius: 15px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(255, 51, 102, 0.15);
        border-color: #ff3366;
    }
    
    .category-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #ff3366, #ff6699);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        color: white;
        font-size: 1.8rem;
        transition: all 0.3s ease;
    }
    
    .category-card:hover .category-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .category-name {
        text-align: center;
        color: #333;
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.4;
        transition: color 0.3s ease;
    }
    
    .category-card:hover .category-name {
        color: #ff3366;
    }
    
    @media (max-width: 768px) {
        .featured-categories-container {
            gap: 15px;
        }
        
        .category-card {
            width: 120px;
            padding: 15px 10px;
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .category-name {
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 480px) {
        .featured-categories-container {
            gap: 12px;
        }
        
        .category-card {
            width: 110px;
            padding: 12px 8px;
        }
        
        .category-icon {
            width: 55px;
            height: 55px;
            font-size: 1.3rem;
        }
    }
</style> -->
