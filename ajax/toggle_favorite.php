
<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
    exit();
}

$userId = $_SESSION['user_id'];

// جلب البيانات من الطلب (دعم كلا الطريقتين)
$productId = 0;
$action = 'toggle';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    // من POST مباشرة
    $productId = intval($_POST['product_id']);
    $action = isset($_POST['action']) ? $_POST['action'] : 'toggle';
} else {
    // من JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = isset($data['product_id']) ? intval($data['product_id']) : 0;
    $action = isset($data['action']) ? $data['action'] : 'toggle';
}

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صحيح']);
    exit();
}

// التحقق من وجود المنتج
$sql = "SELECT id, name FROM products WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
    exit();
}

$product = $result->fetch_assoc();

// التحقق مما إذا كان المنتج في المفضلة
$checkSql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $userId, $productId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

$isInWishlist = $checkResult->num_rows > 0;

if ($action == 'add' || (!$isInWishlist && $action == 'toggle')) {
    // إضافة إلى المفضلة
    if (!$isInWishlist) {
        $insertSql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $userId, $productId);
        
        if ($insertStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'تمت إضافة المنتج إلى المفضلة',
                'is_favorite' => true,
                'in_wishlist' => true,
                'action' => 'added'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الإضافة']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'المنتج موجود بالفعل في المفضلة']);
    }
} elseif ($action == 'remove' || ($isInWishlist && $action == 'toggle')) {
    // إزالة من المفضلة
    if ($isInWishlist) {
        $deleteSql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $userId, $productId);
        
        if ($deleteStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'تمت إزالة المنتج من المفضلة',
                'is_favorite' => false,
                'in_wishlist' => false,
                'action' => 'removed'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الإزالة']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'المنتج غير موجود في المفضلة']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
}

$conn->close();
?>