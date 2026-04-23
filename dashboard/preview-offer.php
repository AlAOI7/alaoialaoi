<?php
// admin/preview-offer.php
session_start();
require_once '../config/database.php';

// التحقق من وجود معرف العرض
if (!isset($_GET['id'])) {
    header('Location: offers.php');
    exit();
}

$offer_id = intval($_GET['id']);

// جلب بيانات العرض
// جلب بيانات العرض (تم حذف الربط بالأقسام لأنه غير موجود في جدول العروض)
$sql = "SELECT o.* FROM offers o
        WHERE o.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $offer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    header('Location: offers.php');
    exit();
}

// جلب المنتجات المرتبطة بالعرض
$products_sql = "SELECT p.id, p.name, p.selling_price, p.description, 
                        pi.image_path as image
                 FROM products p
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 INNER JOIN offer_products op ON p.id = op.product_id
                 WHERE op.offer_id = ?
                 ORDER BY p.name ASC";
$products_stmt = mysqli_prepare($conn, $products_sql);
mysqli_stmt_bind_param($products_stmt, "i", $offer_id);
mysqli_stmt_execute($products_stmt);
$products_result = mysqli_stmt_get_result($products_stmt);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// حساب معلومات العرض
$total_products = count($products);
$offer_status = 'منتهي';
$status_color = 'danger';

$start_date = strtotime($offer['start_date']);
$end_date = strtotime($offer['end_date']);
$current_time = time();

if ($current_time < $start_date) {
    $offer_status = 'قريباً';
    $status_color = 'warning';
} elseif ($current_time >= $start_date && $current_time <= $end_date) {
    $offer_status = 'نشط';
    $status_color = 'success';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة العرض: <?php echo htmlspecialchars($offer['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .offer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .offer-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 1%);
            background-size: 20px 20px;
            opacity: 0.3;
            animation: pattern 20s linear infinite;
        }
        
        @keyframes pattern {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .offer-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .offer-description {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .offer-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 25px;
        }
        
        .meta-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .offer-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border: 5px solid white;
        }
        
        .offer-image-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .offer-image-placeholder i {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .products-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .section-title {
            color: #2d3748;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
        }
        
        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #e9ecef;
        }
        
        .product-image-placeholder {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .product-image-placeholder i {
            font-size: 40px;
            opacity: 0.8;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .product-description {
            font-size: 0.9rem;
            color: #718096;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .offer-details {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .detail-item {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 15px;
            border-right: 4px solid #667eea;
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 1.1rem;
            color: #2d3748;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #cbd5e0;
        }
        
        .timer {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px 25px;
            border-radius: 15px;
            margin-top: 20px;
            display: inline-block;
        }
        
        .timer-item {
            display: inline-block;
            margin: 0 10px;
            text-align: center;
        }
        
        .timer-value {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }
        
        .timer-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .preview-container {
                padding: 10px;
            }
            
            .offer-header {
                padding: 20px;
                border-radius: 15px;
            }
            
            .offer-title {
                font-size: 1.8rem;
            }
            
            .offer-description {
                font-size: 1rem;
            }
            
            .offer-image, .offer-image-placeholder {
                height: 250px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <!-- شريط التحكم -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">
                <i class="fas fa-eye text-primary me-2"></i>
                معاينة العرض
            </h1>
            <div class="action-buttons">
                <a href="edit-offer.php?id=<?php echo $offer['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    تعديل العرض
                </a>
                <a href="offers.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-2"></i>
                    العودة للقائمة
                </a>
                <button onclick="window.print()" class="btn-print">
                    <i class="fas fa-print me-2"></i>
                    طباعة المعاينة
                </button>
            </div>
        </div>
        
        <!-- رأس العرض -->
        <div class="offer-header">
            <span class="status-badge bg-<?php echo $status_color; ?>">
                <?php echo $offer_status; ?>
            </span>
            
            <h1 class="offer-title"><?php echo htmlspecialchars($offer['title']); ?></h1>
            
            <p class="offer-description"><?php echo nl2br(htmlspecialchars($offer['description'])); ?></p>
            
            <?php if ($current_time >= $start_date && $current_time <= $end_date): ?>
            <div class="timer">
                <div class="timer-item">
                    <span class="timer-value" id="days">00</span>
                    <span class="timer-label">يوم</span>
                </div>
                <div class="timer-item">
                    <span class="timer-value" id="hours">00</span>
                    <span class="timer-label">ساعة</span>
                </div>
                <div class="timer-item">
                    <span class="timer-value" id="minutes">00</span>
                    <span class="timer-label">دقيقة</span>
                </div>
                <div class="timer-item">
                    <span class="timer-value" id="seconds">00</span>
                    <span class="timer-label">ثانية</span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="offer-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <strong>البدء:</strong> <?php echo date('Y-m-d H:i', $start_date); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-times me-2"></i>
                    <strong>الانتهاء:</strong> <?php echo date('Y-m-d H:i', $end_date); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-boxes me-2"></i>
                    <strong>عدد المنتجات:</strong> <?php echo $total_products; ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-sort-numeric-up me-2"></i>
                    <strong>الترتيب:</strong> <?php echo $offer['display_order']; ?>
                </div>
            </div>
        </div>
        
        <!-- صورة العرض -->
        <?php if (!empty($offer['image'])): ?>
            <img src="../<?php echo htmlspecialchars($offer['image']); ?>" 
                 class="offer-image" 
                 alt="<?php echo htmlspecialchars($offer['title']); ?>"
                 onerror="this.style.display='none'; document.getElementById('placeholderImage').style.display='flex';">
        <?php endif; ?>
        
        <div id="placeholderImage" class="offer-image-placeholder" 
             style="<?php echo empty($offer['image']) ? 'display:flex' : 'display:none'; ?>">
            <i class="fas fa-bullhorn"></i>
            <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
            <p>عرض خاص</p>
        </div>
        
        <!-- المنتجات -->
        <div class="products-section">
            <h2 class="section-title">
                <i class="fas fa-boxes"></i>
                المنتجات في العرض
                <span class="badge bg-primary ms-2"><?php echo $total_products; ?></span>
            </h2>
            
            <?php if ($total_products > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            
                            <div class="product-image-placeholder" 
                                 style="<?php echo empty($product['image']) ? 'display:flex' : 'display:none'; ?>">
                                <i class="fas fa-box"></i>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price">
                                    <?php echo number_format($product['selling_price'], 2); ?> ر.س
                                </div>
                                <?php if (!empty($product['description'])): ?>
                                    <p class="product-description"><?php echo htmlspecialchars(mb_substr($product['description'], 0, 100)); ?>...</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h4>لا توجد منتجات مرتبطة بهذا العرض</h4>
                    <p class="text-muted">يمكنك إضافة منتجات من خلال صفحة تعديل العرض</p>
                    <a href="edit-offer.php?id=<?php echo $offer['id']; ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>
                        إضافة منتجات
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- تفاصيل العرض -->
        <div class="offer-details">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                تفاصيل العرض
            </h2>
            
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">الحالة</div>
                    <div class="detail-value">
                        <span class="badge bg-<?php echo $status_color; ?>">
                            <?php echo $offer_status; ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">نص الزر</div>
                    <div class="detail-value"><?php echo !empty($offer['button_text']) ? htmlspecialchars($offer['button_text']) : 'اكتشف العروض'; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">الترتيب في القائمة</div>
                    <div class="detail-value"><?php echo $offer['display_order']; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">تاريخ الإنشاء</div>
                    <div class="detail-value"><?php echo date('Y-m-d H:i', strtotime($offer['created_at'])); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">آخر تحديث</div>
                    <div class="detail-value"><?php echo date('Y-m-d H:i', strtotime($offer['updated_at'])); ?></div>
                </div>
                
                <?php if (!empty($offer['link'])): ?>
                <div class="detail-item">
                    <div class="detail-label">الرابط الإضافي</div>
                    <div class="detail-value">
                        <a href="<?php echo htmlspecialchars($offer['link']); ?>" target="_blank" class="text-primary">
                            <i class="fas fa-external-link-alt me-2"></i>
                            زيارة الرابط
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-item">
                    <div class="detail-label">المدة المتبقية</div>
                    <div class="detail-value">
                        <?php if ($current_time < $start_date): ?>
                            يبدأ بعد: <span id="countdownStart"></span>
                        <?php elseif ($current_time > $end_date): ?>
                            انتهى منذ: <span id="timeSinceEnd"></span>
                        <?php else: ?>
                            تنتهي بعد: <span id="countdownEnd"></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- أزرار التحرير -->
        <div class="text-center mt-5 mb-5">
            <div class="btn-group" role="group">
                <a href="edit-offer.php?id=<?php echo $offer['id']; ?>" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-edit me-2"></i>
                    تعديل العرض
                </a>
                <a href="offers.php" class="btn btn-outline-secondary btn-lg px-5">
                    <i class="fas fa-list me-2"></i>
                    عرض جميع العروض
                </a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // دالة حساب الوقت المتبقي
        function updateCountdown() {
            const startDate = new Date('<?php echo $offer['start_date']; ?>').getTime();
            const endDate = new Date('<?php echo $offer['end_date']; ?>').getTime();
            const now = new Date().getTime();
            
            // حساب الوقت المتبقي للبدء
            if (now < startDate) {
                const distance = startDate - now;
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                $('#countdownStart').text(`${days} يوم ${hours} ساعة ${minutes} دقيقة ${seconds} ثانية`);
            }
            
            // حساب الوقت المتبقي للنهاية
            if (now >= startDate && now <= endDate) {
                const distance = endDate - now;
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // تحديث التايمر في الرأس
                $('#days').text(days.toString().padStart(2, '0'));
                $('#hours').text(hours.toString().padStart(2, '0'));
                $('#minutes').text(minutes.toString().padStart(2, '0'));
                $('#seconds').text(seconds.toString().padStart(2, '0'));
                
                $('#countdownEnd').text(`${days} يوم ${hours} ساعة ${minutes} دقيقة ${seconds} ثانية`);
            }
            
            // حساب الوقت المنقضي منذ النهاية
            if (now > endDate) {
                const distance = now - endDate;
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                $('#timeSinceEnd').text(`${days} يوم ${hours} ساعة ${minutes} دقيقة ${seconds} ثانية`);
            }
        }
        
        // تحديث العد التنازلي كل ثانية
        $(document).ready(function() {
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // طباعة المعاينة
            $('.btn-print').click(function(e) {
                e.preventDefault();
                window.print();
            });
            
            // إضافة تأثيرات للصور
            $('.product-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-10px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
        
        // إخفاء أزرار التحكم عند الطباعة
        window.onbeforeprint = function() {
            $('.action-buttons, .btn-group').hide();
        };
        
        window.onafterprint = function() {
            $('.action-buttons, .btn-group').show();
        };
    </script>
</body>
</html>