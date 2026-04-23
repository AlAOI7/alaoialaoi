<?php
// admin/save-offer.php
session_start();
require_once '../config/database.php';
// require_once 'admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: offers.php');
    exit;
}

// جمع البيانات
$offer_id = isset($_POST['offer_id']) ? intval($_POST['offer_id']) : 0;
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$button_text = $_POST['button_text'] ?? 'اكتشف العروض';
$display_order = intval($_POST['display_order'] ?? 0);
$is_active = isset($_POST['is_active']) ? 1 : 0;
$link = $_POST['link'] ?? null;

// معالجة الصورة
$image_path = $_POST['current_image'] ?? '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/offers/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = 'offer_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($file_extension), $allowed_extensions)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($image_path && file_exists('../' . $image_path) && $image_path !== 'img/default-offer.jpg') {
                unlink('../' . $image_path);
            }
            $image_path = 'uploads/offers/' . $file_name;
        }
    }
}

// التحقق من البيانات
if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
    $_SESSION['error'] = 'جميع الحقول المطلوبة يجب ملؤها';
    header('Location: ' . ($offer_id ? 'edit-offer.php?id=' . $offer_id : 'add-offer.php'));
    exit;
}

// بدء المعاملة
$conn->begin_transaction();

try {
    if ($offer_id > 0) {
        // تحديث العرض الموجود
        $sql = "UPDATE offers SET 
                title = ?, 
                description = ?, 
                image = ?, 
                link = ?, 
                start_date = ?, 
                end_date = ?, 
                is_active = ?, 
                display_order = ?, 
                button_text = ?, 
                updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiisi", 
            $title, $description, $image_path, $link, 
            $start_date, $end_date, $is_active, $display_order, 
            $button_text, $offer_id
        );
    } else {
        // إضافة عرض جديد
        $sql = "INSERT INTO offers (title, description, image, link, start_date, end_date, is_active, display_order, button_text) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiis", 
            $title, $description, $image_path, $link, 
            $start_date, $end_date, $is_active, $display_order, $button_text
        );
    }
    
    $stmt->execute();
    
    if ($offer_id === 0) {
        $offer_id = $stmt->insert_id;
    }
    
    // حذف المنتجات المرتبطة القديمة
    $delete_sql = "DELETE FROM offer_products WHERE offer_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $offer_id);
    $delete_stmt->execute();
    
    // إضافة المنتجات الجديدة
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        $insert_sql = "INSERT INTO offer_products (offer_id, product_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        foreach ($_POST['products'] as $index => $product_id) {
            $product_id = intval($product_id);
            if ($product_id > 0) {
                $insert_stmt->bind_param("ii", $offer_id, $product_id);
                $insert_stmt->execute();
            }
        }
    }
    
    $conn->commit();
    
    $_SESSION['success'] = $offer_id > 0 ? 'تم تحديث العرض بنجاح' : 'تم إضافة العرض بنجاح';
    header('Location: offers.php');
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'حدث خطأ: ' . $e->getMessage();
    header('Location: ' . ($offer_id ? 'edit-offer.php?id=' . $offer_id : 'add-offer.php'));
}
exit;