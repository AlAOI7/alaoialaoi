
<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب نوع العملية ومعرف المنتج
$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صحيح']);
    exit();
}

// التحقق من وجود المنتج
$product_sql = "SELECT id, name FROM products WHERE id = ? AND status = 'active'";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
    exit();
}

$product = $product_result->fetch_assoc();

if ($action == 'add') {
    // التحقق إذا كان المنتج موجودًا بالفعل في المفضلة
    $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'المنتج موجود بالفعل في المفضلة',
            'in_wishlist' => true
        ]);
        exit();
    }
    
    // إضافة المنتج إلى المفضلة
    $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $product_id);
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'تمت إضافة المنتج إلى المفضلة',
            'in_wishlist' => true
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الإضافة']);
    }
    
} elseif ($action == 'remove') {
    // إزالة المنتج من المفضلة
    $delete_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $product_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'تمت إزالة المنتج من المفضلة',
            'in_wishlist' => false
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الإزالة']);
    }
    
} elseif ($action == 'toggle') {
    // تبديل حالة المنتج (إضافة/إزالة)
    $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // المنتج موجود - إزالته
        $delete_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $user_id, $product_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'تمت إزالة المنتج من المفضلة',
                'in_wishlist' => false,
                'action' => 'removed'
            ]);
        }
    } else {
        // المنتج غير موجود - إضافته
        $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $product_id);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'تمت إضافة المنتج إلى المفضلة',
                'in_wishlist' => true,
                'action' => 'added'
            ]);
        }
    }
    
} elseif ($action == 'check') {
    // التحقق من حالة المنتج
    $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    echo json_encode([
        'success' => true, 
        'in_wishlist' => $check_result->num_rows > 0
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
}

$conn->close();
?>