<?php
// offer-details.php
session_start();
require_once 'config/database.php';

// التحقق من وجود معرف العرض
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$offer_id = intval($_GET['id']);

// جلب بيانات العرض
$sql = "SELECT o.*, 
               COUNT(op.product_id) as products_count
        FROM offers o
        LEFT JOIN offer_products op ON o.id = op.offer_id
        WHERE o.id = ? 
          AND o.is_active = 1 
          AND o.start_date <= NOW() 
          AND o.end_date >= NOW()
        GROUP BY o.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $offer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$offer = $result->fetch_assoc();

// جلب المنتجات المرتبطة بالعرض
$products_sql = "SELECT p.*, 
                        pi.image_path as main_image,
                        (p.old_price - p.selling_price) as discount_amount,
                        CASE 
                            WHEN p.old_price > 0 THEN ROUND(((p.old_price - p.selling_price) / p.old_price) * 100)
                            ELSE 0
                        END as discount_percentage,
                        COALESCE(p.stock, p.quantity) as available_quantity
                 FROM products p
                 INNER JOIN offer_products op ON p.id = op.product_id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 WHERE op.offer_id = ? 
                   AND (p.is_active = 1 OR p.status = 'active')
                 ORDER BY op.display_order ASC, p.created_at DESC";

$products_stmt = $conn->prepare($products_sql);
$products_stmt->bind_param("i", $offer_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $available_qty = $row['available_quantity'] ?? 0;
    $row['stock_status'] = $available_qty > 0 ? 'in_stock' : 'out_of_stock';
    $row['low_stock'] = $available_qty > 0 && $available_qty <= 5;
    
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($offer['title']); ?> | عروض</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff6699;
            --accent-color: #ff9a8b;
            --dark-color: #2c2c54;
            --light-color: #f8f9fa;
            --border-radius: 16px;
            --box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        body {
            font-family: 'Segoe UI', 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }
        
        .offer-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0 60px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .offer-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('<?php echo $offer['image']; ?>') center/cover no-repeat;
            opacity: 0.2;
            z-index: 1;
        }
        
        .offer-header-content {
            position: relative;
            z-index: 2;
        }
        
        .offer-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .offer-description {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 30px;
            max-width: 800px;
        }
        
        .offer-meta {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
        }
        
        .meta-item i {
            font-size: 1.2rem;
        }
        
        .timer {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 15px;
            display: inline-flex;
            gap: 20px;
            backdrop-filter: blur(10px);
        }
        
        .timer-item {
            text-align: center;
            min-width: 70px;
        }
        
        .timer-number {
            font-size: 2rem;
            font-weight: 800;
            display: block;
            line-height: 1;
        }
        
        .timer-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .main-content {
            padding: 0 20px 40px;
        }
        
        .products-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-top: -80px;
            position: relative;
            z-index: 10;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
        }
        
        .offer-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 3px 10px rgba(255, 51, 102, 0.3);
        }
        
        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 52px;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .current-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary-color);
        }
        
        .old-price {
            font-size: 1rem;
            color: #999;
            text-decoration: line-through;
        }
        
        .discount-percentage {
            background: #ffebee;
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        
        .btn-add-to-cart {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .btn-add-to-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 51, 102, 0.3);
        }
        
        @media (max-width: 768px) {
            .offer-title {
                font-size: 2rem;
            }
            
            .offer-meta {
                flex-direction: column;
                gap: 15px;
            }
            
            .products-section {
                padding: 20px;
                margin-top: -40px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- هيدر العرض -->
    <header class="offer-header">
        <div class="container offer-header-content">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="offer-title"><?php echo htmlspecialchars($offer['title']); ?></h1>
                    <p class="offer-description"><?php echo htmlspecialchars($offer['description']); ?></p>
                    
                    <div class="offer-meta">
                        <div class="meta-item">
                            <i class="fas fa-box"></i>
                            <span><?php echo $offer['products_count']; ?> منتج</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>ينتهي في: <?php echo date('Y/m/d', strtotime($offer['end_date'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- عداد الوقت -->
                    <div class="timer" id="offerTimer">
                        <div class="timer-item">
                            <span class="timer-number" id="days">00</span>
                            <span class="timer-label">أيام</span>
                        </div>
                        <div class="timer-item">
                            <span class="timer-number" id="hours">00</span>
                            <span class="timer-label">ساعات</span>
                        </div>
                        <div class="timer-item">
                            <span class="timer-number" id="minutes">00</span>
                            <span class="timer-label">دقائق</span>
                        </div>
                        <div class="timer-item">
                            <span class="timer-number" id="seconds">00</span>
                            <span class="timer-label">ثواني</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- المنتجات -->
    <main class="main-content">
        <div class="container">
            <section class="products-section">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>
                        المنتجات في هذا العرض
                    </h2>
                    <span class="badge bg-primary fs-6">
                        <?php echo count($products); ?> منتج
                    </span>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>لا توجد منتجات في هذا العرض</h4>
                        <p class="text-muted">لم يتم ربط أي منتجات بهذا العرض بعد</p>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-badge">
                                    <span class="offer-badge">
                                        <i class="fas fa-tag me-1"></i>
                                        عرض خاص
                                    </span>
                                </div>
                                
                                <a href="product-details.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo $product['main_image'] ?: 'img/default-product.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image"
                                         onerror="this.src='img/default-product.jpg'">
                                </a>
                                
                                <div class="product-info">
                                    <h3 class="product-name">
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="product-price">
                                        <span class="current-price">
                                            <?php echo number_format($product['selling_price'], 2); ?> ر.س
                                        </span>
                                        
                                        <?php if ($product['old_price'] && $product['old_price'] > $product['selling_price']): ?>
                                            <span class="old-price">
                                                <?php echo number_format($product['old_price'], 2); ?> ر.س
                                            </span>
                                            
                                            <?php if ($product['discount_percentage'] > 0): ?>
                                                <span class="discount-percentage">
                                                    <?php echo $product['discount_percentage']; ?>% خصم
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button class="btn-add-to-cart" 
                                            onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus me-2"></i>
                                        أضف إلى السلة
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // عداد الوقت التنازلي
        function updateTimer() {
            const endDate = new Date('<?php echo $offer['end_date']; ?>').getTime();
            const now = new Date().getTime();
            const distance = endDate - now;
            
            if (distance < 0) {
                document.getElementById('offerTimer').innerHTML = '<span class="text-danger">انتهى العرض</span>';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').innerText = days.toString().padStart(2, '0');
            document.getElementById('hours').innerText = hours.toString().padStart(2, '0');
            document.getElementById('minutes').innerText = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').innerText = seconds.toString().padStart(2, '0');
        }
        
        setInterval(updateTimer, 1000);
        updateTimer(); // تشغيل فوري
        
        // إضافة منتج إلى السلة
        function addToCart(productId, quantity = 1) {
            $.ajax({
                url: 'add-to-cart-ajax.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('تم إضافة المنتج إلى السلة بنجاح', 'success');
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function() {
                    showToast('حدث خطأ في الاتصال بالخادم', 'error');
                }
            });
        }
        
        // دالة لعرض الرسائل
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="toast-close">&times;</button>
            `;
            
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-width: 300px;
                max-width: 90%;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                z-index: 9999;
                animation: slideDown 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
            
            toast.querySelector('.toast-close').onclick = () => toast.remove();
        }
    </script>
</body>
</html>