<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول - السلة متاحة فقط للمسجلين
$is_logged_in = isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0;

if (!$is_logged_in) {
    // إعادة توجيه غير المسجلين لصفحة تسجيل الدخول
    header('Location: login.php?redirect=cart.php&msg=login_required');
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب منتجات السلة من قاعدة البيانات للمستخدم المسجل فقط
$cart_items = [];
$cart_sql = "SELECT c.*, p.name, p.selling_price, p.old_price, 
                    COALESCE(p.stock, p.quantity) as stock,
                    COALESCE(pi.image_path, 'img/default.jpg') as image
             FROM cart c
             JOIN products p ON c.product_id = p.id
             LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC";

$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cart_items[] = [
        'cart_id' => $row['id'],
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'selling_price' => $row['selling_price'],
        'old_price' => $row['old_price'] ?? 0,
        'quantity' => $row['quantity'],
        'stock' => $row['stock'],
        'image' => $row['image']
    ];
}

// معالجة طلبات AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                    $result = addToCart($_POST['product_id'], $_POST['quantity']);
                    echo json_encode($result);
                }
                break;
            case 'update':
                if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                    $result = updateCart($_POST['product_id'], $_POST['quantity']);
                    echo json_encode($result);
                }
                break;
            case 'remove':
                if (isset($_POST['product_id'])) {
                    $result = removeFromCart($_POST['product_id']);
                    echo json_encode($result);
                }
                break;
            case 'clear':
                $result = clearCart();
                echo json_encode($result);
                break;
        }
        exit;
    }
}

// دالة لإضافة منتج للسلة
function addToCart($product_id, $quantity) {
    global $conn;
    
    $product = getProductById($product_id);
    if (!$product) return ['success' => false, 'message' => 'المنتج غير موجود'];
    
    if ($quantity > $product['quantity']) {
        return ['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة في المخزون'];
    }
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $check_stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $res = $check_stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $new_qty = $row['quantity'] + $quantity;
            $upd_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $upd_stmt->bind_param("iii", $new_qty, $user_id, $product_id);
            $upd_stmt->execute();
        } else {
            $ins_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $ins_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $ins_stmt->execute();
        }
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'selling_price' => floatval($product['selling_price']),
                'quantity' => intval($quantity),
                'stock' => intval($product['quantity'])
            ];
        }
    }
    return ['success' => true];
}

// دالة لتحديث كمية منتج
function updateCart($product_id, $quantity) {
    global $conn;
    $product = getProductById($product_id);
    if (!$product) return ['success' => false, 'message' => 'المنتج غير موجود'];
    
    if ($quantity > $product['quantity']) {
        return ['success' => false, 'message' => 'الكمية غير متوفرة'];
    }
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        if ($quantity <= 0) {
            $del_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $del_stmt->bind_param("ii", $user_id, $product_id);
            $del_stmt->execute();
        } else {
            $upd_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $upd_stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $upd_stmt->execute();
        }
        return ['success' => true];
    } else {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = intval($quantity);
        }
        return ['success' => true];
    }
}

// دالة لحذف منتج من السلة
function removeFromCart($product_id) {
    global $conn;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $del_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $del_stmt->bind_param("ii", $user_id, $product_id);
        $del_stmt->execute();
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    return ['success' => true];
}

// دالة لتفريغ السلة
function clearCart() {
    global $conn;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $del_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $del_stmt->bind_param("i", $user_id);
        $del_stmt->execute();
    } else {
        $_SESSION['cart'] = [];
    }
    return ['success' => true];
}

// دالة للحصول على منتج بواسطة ID
function getProductById($id) {
    global $conn;
    
    $sql = "SELECT p.*, 
                   pi.image_path as main_image
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            WHERE p.id = ? AND p.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// 1. للمستخدمين المسجلين: جلب السلة من قاعدة البيانات ودمجها/تحديث الجلسة
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $_SESSION['cart'] = []; // إعادة تعيين الجلسة لضمان التزامن
    
    $cart_sql = "SELECT c.quantity, p.*, pi.image_path as main_image 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $_SESSION['cart'][$row['id']] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'selling_price' => floatval($row['selling_price']),
            'old_price' => $row['old_price'] ? floatval($row['old_price']) : null,
            'quantity' => intval($row['quantity']),
            'stock' => intval($row['stock']),
            'image' => $row['main_image'] ?: 'img/default-product.jpg'
        ];
    }
}

// تهيئة المتغيرات
$cart_items = [];
$subtotal = 0;
$discount_total = 0;
$shipping_cost = 0;

// 2. تكرار العناصر (الآن الجلسة محدثة سواء للزائر أو المسجل)
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        // التحقق من صحة بيانات العنصر
        if (!isset($item['selling_price']) || !isset($item['quantity'])) {
            continue; // تخطي العناصر غير الصالحة
        }
        
        $product = getProductById($product_id);
        if ($product) {
            // تحديث بيانات السلة من قاعدة البيانات (في حال تغير السعر)
            $item['name'] = $product['name'];
            $item['selling_price'] = floatval($product['selling_price']);
            $item['old_price'] = $product['old_price'] ? floatval($product['old_price']) : null;
            $item['stock'] = intval($product['quantity']);
            $item['image'] = $product['main_image'] ?: 'img/default-product.jpg';
            
            $_SESSION['cart'][$product_id] = $item; // تحديث الجلسة
            
            // حساب المجموع الفرعي
            $item_total = $item['selling_price'] * $item['quantity'];
            $subtotal += $item_total;
            
            // حساب الخصم إذا كان هناك سعر قديم
            if ($item['old_price'] && $item['old_price'] > $item['selling_price']) {
                $item_discount = ($item['old_price'] - $item['selling_price']) * $item['quantity'];
                $discount_total += $item_discount;
            }
            
            $cart_items[] = $item;
        } else {
            // المنتج غير موجود في قاعدة البيانات، إزالته من السلة
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

// حساب تكلفة الشحن والإجمالي
if (!empty($cart_items)) {
    $shipping_cost = 20;
}

$total = $subtotal + $shipping_cost - $discount_total;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق - متجر Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* نفس الأنماط السابقة... */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
        }
        
        .icon-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .icon-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .cart-item {
            background: white;
            border-radius: 15px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .cart-item-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .price-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        
        .current-price {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
        }
        
        .old-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }
        
        .stock-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .low-stock {
            color: #ff6b6b;
            font-weight: bold;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 25px;
            width: fit-content;
        }
        
        .quantity-btn {
            background: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .quantity-btn:hover {
            background: #f8f9fa;
            transform: scale(1.1);
        }
        
        .quantity-input {
            width: 40px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: bold;
            font-size: 16px;
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .remove-btn:hover {
            transform: scale(1.2);
            color: #c82333;
        }
        
        .share-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .share-title {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-weight: bold;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .share-whatsapp { background: #25D366; }
        .share-twitter { background: #1DA1F2; }
        .share-facebook { background: #1877F2; }
        .share-snapchat { background: #FFFC00; color: #000; }
        .share-instagram { background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D); }
        
        .totals-section {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .checkout-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .continue-shopping-btn {
            display: block;
            padding: 12px;
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 15px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .continue-shopping-btn:hover {
            background: #667eea;
            color: white;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-cart-icon {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
        <?php include 'header.php'; ?>
    <!-- <header class="main-header">
        <div class="header-top">
            <a href="home.php" class="icon-btn"><i class="fas fa-arrow-right"></i></a> -->
            <h5 class="mb-0 fw-bold">السلة <span id="cart-count" class="badge bg-danger"><?php echo count($cart_items); ?></span></h5>
            <!-- <div class="header-icons">
                <button class="icon-btn"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </header> -->

    <div class="main-content container py-4">
        <?php if (empty($cart_items)): ?>
            <!-- حالة السلة الفارغة -->
            <div id="empty-cart" class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="text-muted">سلة التسوق فارغة</h3>
                <p class="text-muted mb-4">لم تقم بإضافة أي منتجات إلى السلة بعد</p>
                <a href="home.php" class="btn btn-danger rounded-pill px-4">ابدأ التسوق</a>
            </div>
        <?php else: ?>
            <!-- حالة السلة تحتوي على منتجات -->
            <div id="cart-with-items">
                <section class="cart-items-section mb-4">
                    <div id="cart-list" class="d-flex flex-column">
                        <?php foreach ($cart_items as $item): 
                            $discount_percentage = 0;
                            if (isset($item['old_price']) && $item['old_price'] && $item['old_price'] > $item['selling_price']) {
                                $discount_percentage = round((($item['old_price'] - $item['selling_price']) / $item['old_price']) * 100);
                            }
                        ?>
                            <div class="cart-item d-flex align-items-center p-3" 
                                 data-price="<?php echo isset($item['selling_price']) ? $item['selling_price'] : 0; ?>" 
                                 data-old-price="<?php echo isset($item['old_price']) && $item['old_price'] ? $item['old_price'] : 0; ?>" 
                                 data-id="<?php echo isset($item['id']) ? $item['id'] : 0; ?>" 
                                 data-stock="<?php echo isset($item['stock']) ? $item['stock'] : 0; ?>">
                                
                                <img src="<?php echo htmlspecialchars($item['image'] ?? 'img/1.jpg'); ?>" 
                                     class="cart-item-img me-3" 
                                     alt="<?php echo htmlspecialchars($item['name'] ?? 'صورة المنتج'); ?>"
                                     onerror="this.src='img/1.jpg'">
                                
                                <div class="cart-item-info flex-grow-1">
                                    <h5 class="cart-item-title"><?php echo htmlspecialchars($item['name'] ?? 'منتج غير معروف'); ?></h5>
                                    <div class="price-container">
                                        <span class="current-price"><?php echo number_format($item['selling_price'] ?? 0, 2); ?> ر.س</span>
                                        <?php if (isset($item['old_price']) && $item['old_price'] && $item['old_price'] > $item['selling_price']): ?>
                                            <span class="old-price"><?php echo number_format($item['old_price'], 2); ?> ر.س</span>
                                            <span class="discount-badge"><?php echo $discount_percentage; ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="stock-info">
                                        <span class="<?php echo (isset($item['stock']) && $item['stock'] <= 5) ? 'low-stock' : ''; ?>">
                                            بقي <?php echo isset($item['stock']) ? $item['stock'] : 0; ?> قطع فقط
                                        </span>
                                    </div>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" data-action="minus"><i class="fas fa-minus"></i></button>
                                        <input type="text" class="quantity-input" value="<?php echo isset($item['quantity']) ? $item['quantity'] : 1; ?>" readonly>
                                        <button class="quantity-btn" data-action="plus"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <button class="remove-btn"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="share-section">
                    <h6 class="share-title">شارك السلة مع الأصدقاء</h6>
                    <div class="share-buttons">
                        <?php
                        // إنشاء رابط لمشاركة السلة
                        $cart_items_text = [];
                        foreach ($cart_items as $item) {
                            $cart_items_text[] = ($item['name'] ?? 'منتج') . " (" . ($item['quantity'] ?? 1) . "x)";
                        }
                        $share_text = "ألقي نظرة على منتجاتي في سلة التسوق من Be Pretty!\n";
                        $share_text .= implode("\n", $cart_items_text);
                        $share_text .= "\nالمجموع: " . number_format($total, 2) . " ر.س";
                        $share_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $encoded_text = urlencode($share_text);
                        ?>
                        <a href="https://wa.me/?text=<?php echo $encoded_text; ?>" 
                           class="share-btn share-whatsapp" 
                           target="_blank" 
                           data-platform="whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo $encoded_text; ?>" 
                           class="share-btn share-twitter" 
                           target="_blank" 
                           data-platform="twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($share_url); ?>&quote=<?php echo $encoded_text; ?>" 
                           class="share-btn share-facebook" 
                           target="_blank" 
                           data-platform="facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" 
                           class="share-btn share-snapchat" 
                           onclick="shareToSnapchat()" 
                           data-platform="snapchat">
                            <i class="fab fa-snapchat-ghost"></i>
                        </a>
                        <a href="https://www.instagram.com/" 
                           class="share-btn share-instagram" 
                           target="_blank" 
                           data-platform="instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </section>

                <section class="totals-section">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">المجموع الفرعي</span>
                        <span class="fw-bold fs-5 text-dark"><span id="subtotal"><?php echo number_format($subtotal, 2); ?></span> ر.س</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">تكاليف الشحن</span>
                        <span class="fw-bold fs-5 text-dark"><span id="shipping"><?php echo number_format($shipping_cost, 2); ?></span> ر.س</span>
                    </div>
                    <?php if ($discount_total > 0): ?>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">الخصم</span>
                        <span class="fw-bold fs-5 text-success">-<span id="discount"><?php echo number_format($discount_total, 2); ?></span> ر.س</span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-4 text-primary">المجموع الكلي</span>
                        <span class="fw-bold fs-4 text-danger"><span id="total-price"><?php echo number_format($total, 2); ?></span> ر.س</span>
                    </div>
                    <div class="d-grid gap-2">
                        <button id="checkout-btn" class="checkout-btn">الدفع الآن</button>
                        <a href="home.php" class="continue-shopping-btn text-center">متابعة التسوق</a>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </div>
  <?php include 'footer.php'; ?>
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // إدارة أزرار زيادة ونقصان الكمية
            $(document).on('click', '.quantity-btn', function() {
                const action = $(this).data('action');
                const input = $(this).siblings('.quantity-input');
                let value = parseInt(input.val());
                const cartItem = $(this).closest('.cart-item');
                const productId = cartItem.data('id');
                const stock = parseInt(cartItem.data('stock'));
                
                if (action === 'plus' && value < stock) {
                    value++;
                    updateCartItem(productId, value);
                } else if (action === 'minus' && value > 1) {
                    value--;
                    updateCartItem(productId, value);
                }
                
                input.val(value);
                updateTotals();
            });
            
            // إزالة عنصر من السلة
            $(document).on('click', '.remove-btn', function() {
                const cartItem = $(this).closest('.cart-item');
                const itemId = cartItem.data('id');
                
                if (confirm('هل تريد إزالة هذا المنتج من السلة؟')) {
                    removeCartItem(itemId);
                    cartItem.fadeOut(300, function() {
                        $(this).remove();
                        updateCartBadge();
                        checkEmptyCart();
                        updateTotals();
                    });
                }
            });
            
            // زر الدفع
            $('#checkout-btn').click(function() {
                const totalPrice = $('#total-price').text();
                alert(`جارٍ توجيهك إلى صفحة الدفع بمبلغ ${totalPrice} ر.س`);
                window.location.href = 'checkout.php?total=' + totalPrice;
            });
            
            // تحديث المجاميع
            function updateTotals() {
                let subtotal = 0;
                let discount = 0;
                
                $('.cart-item').each(function() {
                    const price = parseFloat($(this).data('price')) || 0;
                    const oldPrice = parseFloat($(this).data('old-price')) || 0;
                    const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
                    subtotal += price * quantity;
                    
                    if (oldPrice > price) {
                        discount += (oldPrice - price) * quantity;
                    }
                });
                
                const shipping = parseFloat($('#shipping').text()) || 0;
                const total = subtotal + shipping - discount;
                
                $('#subtotal').text(subtotal.toFixed(2));
                $('#discount').text(discount.toFixed(2));
                $('#total-price').text(total.toFixed(2));
            }
            
            // تحديث عدد العناصر في السلة
            function updateCartBadge() {
                const itemCount = $('.cart-item').length;
                $('#cart-count').text(itemCount);
            }
            
            // التحقق إذا كانت السلة فارغة
            function checkEmptyCart() {
                if ($('.cart-item').length === 0) {
                    $('#cart-with-items').hide();
                    $('#empty-cart').removeClass('d-none');
                } else {
                    $('#cart-with-items').show();
                    $('#empty-cart').addClass('d-none');
                }
            }
            
            // تحديث كمية المنتج عبر AJAX
            function updateCartItem(productId, quantity) {
                $.ajax({
                    url: 'cart.php',
                    method: 'POST',
                    data: {
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateCartBadge();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            }
            
            // إزالة المنتج عبر AJAX
            function removeCartItem(productId) {
                $.ajax({
                    url: 'cart.php',
                    method: 'POST',
                    data: {
                        action: 'remove',
                        product_id: productId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateCartBadge();
                        }
                    }
                });
            }
            
            // تحديث المجاميع عند التحميل
            updateTotals();
        });
        
        // مشاركة عبر Snapchat
        function shareToSnapchat() {
            const items = [];
            $('.cart-item-title').each(function() {
                items.push($(this).text());
            });
            
            const shareText = "سلة التسوق من Be Pretty تحتوي على:\n" + items.join("\n");
            const shareUrl = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: 'سلة التسوق من Be Pretty',
                    text: shareText,
                    url: shareUrl
                });
            } else {
                alert('يمكنك نسخ رابط السلة:\n' + shareUrl + '\n\n' + shareText);
                navigator.clipboard.writeText(shareText + '\n' + shareUrl);
            }
        }
    </script>
</body>
</html>