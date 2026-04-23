<?php
session_start();
require_once 'config/database.php';
require_once 'functions.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب المنتجات المفضلة
$sql = "SELECT p.*, pi.image_path as main_image,
               (SELECT COUNT(*) FROM favorites WHERE product_id = p.id AND user_id = ?) as is_favorite
        FROM favorites f
        JOIN products p ON f.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE f.user_id = ? AND (p.is_active = 1 OR p.status = 'active')
        ORDER BY f.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المفضلة | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 0 20px;
            max-width: 1400px;
            margin: 0 auto 40px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="fw-bold mb-2"><i class="fas fa-heart text-danger"></i> المفضلة</h1>
            <p class="mb-0 text-white-50">المنتجات التي أحببتها</p>
        </div>
    </div>

    <div class="container mb-5">
        <?php if (empty($favorites)): ?>
            <div class="empty-state">
                <i class="far fa-heart"></i>
                <h3>لا توجد منتجات في المفضلة</h3>
                <p class="text-muted">تصفح المنتجات واضغط على القلب لحفظ ما يعجبك هنا</p>
                <a href="products.php" class="btn btn-primary rounded-pill px-4 mt-3">تصفح المنتجات</a>
            </div>
        <?php else: ?>
            <div class="favorites-grid">
                <?php foreach ($favorites as $product): ?>
                    <?php echo generateProductCard($product); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // دوال JavaScript
        function addToCart(productId, quantity = 1) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('ajax/add_to_cart.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    showNotification('تمت الإضافة للسلة', 'success');
                } else {
                    showNotification(d.message, 'error');
                }
            });
        }

        function toggleFavorite(productId, button) {
            const formData = new FormData();
            formData.append('product_id', productId);
            
            fetch('ajax/toggle_favorite.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    // إزالة البطاقة إذا كنا في صفحة المفضلة
                    if (!d.is_favorite) {
                        const card = button.closest('.product-card-wrapper') || button.closest('.col');
                        if (card) {
                            card.style.transition = 'all 0.3s';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                card.remove();
                                if (document.querySelectorAll('.product-card-wrapper').length === 0) {
                                    location.reload(); // لإظهار الحالة الفارغة
                                }
                            }, 300);
                        }
                    }
                    showNotification(d.message, 'success');
                }
            });
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            notification.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999;';
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    </script>
</body>
</html>
