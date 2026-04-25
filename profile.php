<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
// 3. فحص إضافي: ماذا لو تم حذف المستخدم من القاعدة وهو لا يزال يملك Session؟
if (!$user) {
    session_destroy(); // تدمير الجلسة لأنها غير صالحة
    header('Location: login.php');
    exit;
}
// جلب الإحصائيات
$stats = [
    'orders' => 0,
    'favorites' => 0,
    'reviews' => 0,
    'points' => 0,
    'wallet' => 0
];

// عدد الطلبات - تصحيح: استخدام customer_id بدلاً من user_id
try {
    $orders_sql = "SELECT COUNT(*) as count FROM orders WHERE customer_id = ? AND status != 'cancelled'";
    $orders_stmt = $conn->prepare($orders_sql);
    $orders_stmt->bind_param("i", $user_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $stats['orders'] = $orders_result->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    error_log('خطأ في جلب عدد الطلبات: ' . $e->getMessage());
    $stats['orders'] = 0;
}

// عدد المفضلة
try {
    $favorites_sql = "SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ?";
    $favorites_stmt = $conn->prepare($favorites_sql);
    $favorites_stmt->bind_param("i", $user_id);
    $favorites_stmt->execute();
    $favorites_result = $favorites_stmt->get_result();
    $stats['favorites'] = $favorites_result->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    error_log('خطأ في جلب عدد المفضلة: ' . $e->getMessage());
    $stats['favorites'] = 0;
}

// عدد المراجعات - تصحيح: استخدام is_approved بدلاً من status
try {
    $reviews_sql = "SELECT COUNT(*) as count FROM product_reviews WHERE user_id = ? AND is_approved = 1";
    $reviews_stmt = $conn->prepare($reviews_sql);
    $reviews_stmt->bind_param("i", $user_id);
    $reviews_stmt->execute();
    $reviews_result = $reviews_stmt->get_result();
    $stats['reviews'] = $reviews_result->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    error_log('خطأ في جلب عدد المراجعات: ' . $e->getMessage());
    $stats['reviews'] = 0;
}

// النقاط والمحفظة
try {
    $wallet_sql = "SELECT balance, points FROM user_wallet WHERE user_id = ?";
    $wallet_stmt = $conn->prepare($wallet_sql);
    $wallet_stmt->bind_param("i", $user_id);
    $wallet_stmt->execute();
    $wallet_result = $wallet_stmt->get_result();
    if ($wallet_result->num_rows > 0) {
        $wallet = $wallet_result->fetch_assoc();
        $stats['wallet'] = $wallet['balance'] ?? 0;
        $stats['points'] = $wallet['points'] ?? 0;
    }
} catch (Exception $e) {
    error_log('خطأ في جلب بيانات المحفظة: ' . $e->getMessage());
}

// معالجة تحديث البيانات الشخصية
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'] ?? '';
        $birth_date = $_POST['birth_date'] ?? '';
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $currency = trim($_POST['currency'] ?? 'YER_NEW');
        
        $errors = [];
        
        // التحقق من البيانات
        if (empty($name)) $errors[] = 'الاسم مطلوب';
        if (empty($email)) $errors[] = 'البريد الإلكتروني مطلوب';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'البريد الإلكتروني غير صحيح';
        
        // التحقق من عدم تكرار البريد الإلكتروني
        if ($email != $user['email']) {
            $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $errors[] = 'البريد الإلكتروني مسجل مسبقاً';
            }
        }
        
        if (empty($errors)) {
            $update_sql = "UPDATE users SET 
                          name = ?, email = ?, phone = ?, gender = ?, 
                          birth_date = ?, city = ?, country = ?, currency = ?, updated_at = NOW() 
                          WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssssi", $name, $email, $phone, $gender, $birth_date, $city, $country, $currency, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['currency'] = $currency; // تحديث العملة في الجلسة
                $user['name'] = $name;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['gender'] = $gender;
                $user['birth_date'] = $birth_date;
                $user['city'] = $city;
                $user['country'] = $country;
                $user['currency'] = $currency;
                
                $success_message = 'تم تحديث بياناتك الشخصية بنجاح';
            } else {
                $errors[] = 'حدث خطأ أثناء تحديث البيانات';
            }
        }
    }
    
    elseif ($action == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
        }
        
        if ($new_password != $confirm_password) {
            $errors[] = 'كلمة المرور الجديدة غير متطابقة';
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $password_success = 'تم تغيير كلمة المرور بنجاح';
            } else {
                $errors[] = 'حدث خطأ أثناء تغيير كلمة المرور';
            }
        }
    }
    
    elseif ($action == 'update_image') {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $upload_dir = 'uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['profile_image']['name']);
            $target_file = $upload_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // التحقق من أن الملف صورة
            $check = getimagesize($_FILES['profile_image']['tmp_name']);
            if ($check === false) {
                $errors[] = 'الملف ليس صورة';
            }
            
            // التحقق من حجم الملف (5MB كحد أقصى)
            if ($_FILES['profile_image']['size'] > 5000000) {
                $errors[] = 'حجم الصورة كبير جداً (الحد الأقصى 5MB)';
            }
            
            // السماح بصيغ معينة فقط
            $allowed_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($imageFileType, $allowed_formats)) {
                $errors[] = 'الصيغ المسموح بها: JPG, JPEG, PNG, GIF, WEBP';
            }
            
            if (empty($errors)) {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    // حذف الصورة القديمة إذا كانت موجودة
                    if ($user['profile_image'] && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                    
                    // تحديث قاعدة البيانات
                    $update_sql = "UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $target_file, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $_SESSION['profile_image'] = $target_file;
                        $user['profile_image'] = $target_file;
                        $image_success = 'تم تحديث الصورة بنجاح';
                    } else {
                        $errors[] = 'حدث خطأ أثناء تحديث الصورة';
                    }
                } else {
                    $errors[] = 'حدث خطأ أثناء رفع الصورة';
                }
            }
        }
    }
}

// جلب العناوين
$addresses_sql = "SELECT * FROM user_addresses WHERE user_id = ? AND is_active = 1 ORDER BY is_default DESC";
$addresses_stmt = $conn->prepare($addresses_sql);
$addresses_stmt->bind_param("i", $user_id);
$addresses_stmt->execute();
$addresses_result = $addresses_stmt->get_result();
$addresses = [];
while ($row = $addresses_result->fetch_assoc()) {
    $addresses[] = $row;
}

// جلب طرق الدفع
// $payments_sql = "SELECT * FROM payment_methods WHERE user_id = ? AND is_active = 1 ORDER BY is_default DESC";
// $payments_stmt = $conn->prepare($payments_sql);
// $payments_stmt->bind_param("i", $user_id);
// $payments_stmt->execute();
// $payments_result = $payments_stmt->get_result();
// $payment_methods = [];
// while ($row = $payments_result->fetch_assoc()) {
//     $payment_methods[] = $row;
// }

// جلب آخر الطلبات - تصحيح: استخدام customer_id بدلاً من user_id
try {
    $recent_orders_sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 3";
    $recent_orders_stmt = $conn->prepare($recent_orders_sql);
    $recent_orders_stmt->bind_param("i", $user_id);
    $recent_orders_stmt->execute();
    $recent_orders_result = $recent_orders_stmt->get_result();
    $recent_orders = [];
    while ($row = $recent_orders_result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
} catch (Exception $e) {
    error_log('خطأ في جلب آخر الطلبات: ' . $e->getMessage());
    $recent_orders = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حسابي | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff3366;
            --secondary-color: #ff3366;
            --dark-color: #2c2c54;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f5f5f7;
            color: #1d1d1f;
            padding-bottom: 80px;
        }
        
        /* الهيدر */
        .main-header {
            background: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .header-btn {
            background: none;
            border: none;
            color: var(--dark-color);
            font-size: 1.1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .header-btn:hover {
            background-color: #f5f5f7;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* المحتوى الرئيسي */
        .main-content {
            margin-top: 80px;
            padding: 20px;
        }
        
        /* قسم معلومات المستخدم */
        .user-profile-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .profile-image-container {
            position: relative;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .change-image-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-color);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .change-image-btn:hover {
            background: #e02e5a;
            transform: scale(1.1);
        }
        
        .profile-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .profile-info p {
            color: #6e6e73;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .member-since {
            background: linear-gradient(135deg, var(--primary-color), #ff6b93);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        /* قسم الإحصائيات */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.3rem;
        }
        
        .stat-card:nth-child(1) .stat-icon { background: #e3f2fd; color: #1976d2; }
        .stat-card:nth-child(2) .stat-icon { background: #f3e5f5; color: #7b1fa2; }
        .stat-card:nth-child(3) .stat-icon { background: #e8f5e9; color: #388e3c; }
        .stat-card:nth-child(4) .stat-icon { background: #fff3e0; color: #f57c00; }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .stat-label {
            color: #6e6e73;
            font-size: 0.9rem;
        }
        
        /* القوائم السريعة */
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        @media (min-width: 768px) {
            .quick-links-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .quick-link {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }
        
        .quick-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            color: var(--primary-color);
        }
        
        .quick-link-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.3rem;
            background: linear-gradient(135deg, var(--primary-color), #ff6b93);
            color: white;
        }
        
        .quick-link-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        .quick-link-desc {
            color: #6e6e73;
            font-size: 0.85rem;
        }
        
        /* قسم الطلبات الأخيرة */
        .recent-orders {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .section-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .section-link:hover {
            text-decoration: underline;
        }
        
        .order-card {
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .order-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(255, 51, 102, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .order-id {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-date {
            color: #6e6e73;
            font-size: 0.9rem;
        }
        
        .order-total {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        /* قسم المعلومات الشخصية */
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6e6e73;
            font-size: 0.95rem;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .edit-btn {
            background: none;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .edit-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* أزرار الإجراءات */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #ff6b93);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 102, 0.3);
        }
        
        .btn-outline {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* الشريط السفلي */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 15px 0;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #8e8e93;
            font-size: 0.8rem;
            transition: all 0.3s;
            position: relative;
        }
        
        .nav-item.active {
            color: var(--primary-color);
        }
        
        .nav-item i {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        /* المودال */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid #f0f0f0;
            padding: 25px;
        }
        
        .modal-title {
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark-color);
            display: block;
        }
        
        .form-control {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 51, 102, 0.1);
        }
        
        .form-select {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        /* رسائل التنبيه */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        /* عناصر خاصة */
        .verification-badge {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 10px;
        }
        
        .premium-badge {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #8e8e93;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #e0e0e0;
        }
        
        /* تحسينات للهواتف */
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .user-profile-section,
            .recent-orders,
            .info-card {
                padding: 20px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-links-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <!-- الهيدر -->
    <header class="main-header">
        <div class="header-content">
            <a href="home.php" class="header-btn">
                <i class="fas fa-arrow-right"></i>
            </a>
            <h1 class="header-title">حسابي</h1>
            <div class="header-actions">
                <a href="notifications.php" class="header-btn position-relative">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </a>
                <a href="cart.php" class="header-btn position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="notification-badge"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <!-- المحتوى الرئيسي -->
    <main class="main-content">
        
        <!-- قسم الملف الشخصي -->
        <section class="user-profile-section">
            <div class="profile-header">
                <div class="profile-image-container">
                    <img src="<?php echo $user['profile_image'] ?: 'img/1.jpg'; ?>" 
                         alt="صورة الملف الشخصي" 
                         class="profile-image"
                         onerror="this.src='img/1.jpg'">
                    <button class="change-image-btn" onclick="openImageModal()">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if ($user['phone']): ?>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                    <?php if ($user['email_verified']): ?>
                        <span class="verification-badge">
                            <i class="fas fa-check-circle"></i> تم التحقق
                        </span>
                    <?php endif; ?>
                    <div class="member-since">
                        <i class="fas fa-user-plus"></i> عضو منذ <?php echo date('Y-m-d', strtotime($user['created_at'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button class="btn-primary" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> تعديل الملف الشخصي
                </button>
                <a href="logout.php" class="btn-outline">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
            </div>
        </section>

        <!-- الإحصائيات -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-number"><?php echo $stats['orders']; ?></div>
                <div class="stat-label">الطلبات</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-number"><?php echo $stats['favorites']; ?></div>
                <div class="stat-label">المفضلة</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number"><?php echo $stats['reviews']; ?></div>
                <div class="stat-label">التقييمات</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['wallet'], 2); ?> ر.س</div>
                <div class="stat-label">المحفظة</div>
            </div>
        </div>

        <!-- الروابط السريعة -->
        <div class="quick-links-grid">
            <a href="order.php" class="quick-link">
                <div class="quick-link-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="quick-link-title">طلباتي</div>
                <div class="quick-link-desc">تتبع وتصفح طلباتك</div>
            </a>
            
            <a href="favorites.php" class="quick-link">
                <div class="quick-link-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="quick-link-title">المفضلة</div>
                <div class="quick-link-desc">منتجاتك المفضلة</div>
            </a>
            
            <a href="addresses.php" class="quick-link">
                <div class="quick-link-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="quick-link-title">العناوين</div>
                <div class="quick-link-desc">إدارة عناوين الشحن</div>
            </a>
            
            <a href="payment-methods.php" class="quick-link">
                <div class="quick-link-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="quick-link-title">الدفع</div>
                <div class="quick-link-desc">طرق الدفع المحفوظة</div>
            </a>
        </div>

        <!-- الطلبات الأخيرة -->
        <section class="recent-orders">
            <div class="section-header">
                <h2 class="section-title">الطلبات الأخيرة</h2>
                <a href="order.php" class="section-link">عرض الكل</a>
            </div>
            
            <?php if (empty($recent_orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h4>لا توجد طلبات</h4>
                    <p>لم تقم بأي طلبات بعد</p>
                    <a href="products.php" class="btn-primary mt-3">تسوق الآن</a>
                </div>
            <?php else: ?>
                <?php foreach ($recent_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">طلب #<?php echo $order['invoice_number']; ?></span>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php 
                                    $status_text = [
                                        'pending' => 'قيد الانتظار',
                                        'processing' => 'قيد المعالجة',
                                        'shipped' => 'تم الشحن',
                                        'delivered' => 'تم التوصيل',
                                        'cancelled' => 'ملغى'
                                    ];
                                    echo $status_text[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </div>
                        <div class="order-details">
                            <span class="order-date">
                                <i class="fas fa-calendar"></i> <?php echo date('Y-m-d', strtotime($order['created_at'])); ?>
                            </span>
                            <span class="order-total"><?php echo number_format($order['total_amount'], 2); ?> ر.س</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- المعلومات الشخصية -->
        <section class="info-card">
            <h2 class="section-title mb-4">المعلومات الشخصية</h2>
            
            <div class="info-item">
                <span class="info-label">الاسم الكامل</span>
                <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">البريد الإلكتروني</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            
            <?php if ($user['phone']): ?>
            <div class="info-item">
                <span class="info-label">رقم الهاتف</span>
                <span class="info-value"><?php echo htmlspecialchars($user['phone']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($user['gender']): ?>
            <div class="info-item">
                <span class="info-label">الجنس</span>
                <span class="info-value"><?php echo $user['gender'] == 'male' ? 'ذكر' : 'أنثى'; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($user['birth_date']): ?>
            <div class="info-item">
                <span class="info-label">تاريخ الميلاد</span>
                <span class="info-value"><?php echo date('Y-m-d', strtotime($user['birth_date'])); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="info-item">
                <span class="info-label">العملة المفضلة</span>
                <span class="info-value">
                    <?php 
                        $cur = $user['currency'] ?? 'YER_NEW';
                        if ($cur == 'YER_NEW') echo 'ريال يمني (جديد)';
                        elseif ($cur == 'YER_OLD') echo 'ريال يمني (قديم)';
                        elseif ($cur == 'SAR') echo 'ريال سعودي';
                        elseif ($cur == 'USD') echo 'دولار أمريكي';
                        else echo $cur;
                    ?>
                </span>
            </div>
            
            <div class="info-item">
                <span class="info-label">تاريخ التسجيل</span>
                <span class="info-value"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
            </div>
            
            <div class="text-center mt-4">
                <button class="edit-btn" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> تعديل المعلومات
                </button>
                <button class="edit-btn" onclick="openPasswordModal()" style="margin-right: 10px;">
                    <i class="fas fa-key"></i> تغيير كلمة المرور
                </button>
            </div>
        </section>

        <!-- روابط إضافية -->
        <div class="quick-links-grid">
            <a href="settings.php" class="quick-link">
                <div class="quick-link-icon" style="background: linear-gradient(135deg, #6f42c1, #6610f2);">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="quick-link-title">الإعدادات</div>
                <div class="quick-link-desc">تخصيص تجربتك</div>
            </a>
            
            <a href="support.php" class="quick-link">
                <div class="quick-link-icon" style="background: linear-gradient(135deg, #20c997, #17a2b8);">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="quick-link-title">الدعم</div>
                <div class="quick-link-desc">مساعدة ودعم فني</div>
            </a>
            
            <a href="contact.php" class="quick-link">
                <div class="quick-link-icon" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="quick-link-title">تواصل معنا</div>
                <div class="quick-link-desc">أسئلة واقتراحات</div>
            </a>
            
            <a href="referral.php" class="quick-link">
                <div class="quick-link-icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="quick-link-title">دعوة أصدقاء</div>
                <div class="quick-link-desc">اكسب مكافآت</div>
            </a>
        </div>
    </main>

    <!-- الشريط السفلي -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>
        <a href="categories.php" class="nav-item">
            <i class="fas fa-th-large"></i>
            <span>الفئات</span>
        </a>
        <a href="cart.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>السلة</span>
        </a>
        <a href="order.php" class="nav-item">
            <i class="fas fa-list-alt"></i>
            <span>الطلبات</span>
        </a>
        <a href="profile.php" class="nav-item active">
            <i class="fas fa-user"></i>
            <span>حسابي</span>
        </a>
    </nav>

    <!-- مودال تعديل البيانات -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل البيانات الشخصية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label class="form-label">الاسم الكامل</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="tel" 
                                   class="form-control" 
                                   name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">الجنس</label>
                                    <select class="form-select" name="gender">
                                        <option value="">اختر الجنس</option>
                                        <option value="male" <?php echo ($user['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>ذكر</option>
                                        <option value="female" <?php echo ($user['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>أنثى</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">تاريخ الميلاد</label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="birth_date" 
                                           value="<?php echo $user['birth_date'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">المدينة</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="city" 
                                           value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">البلد</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="country" 
                                           value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">العملة المفضلة للتسوق</label>
                            <select class="form-select" name="currency">
                                <option value="YER_NEW" <?php echo ($user['currency'] ?? '') == 'YER_NEW' ? 'selected' : ''; ?>>ريال يمني (جديد)</option>
                                <option value="YER_OLD" <?php echo ($user['currency'] ?? '') == 'YER_OLD' ? 'selected' : ''; ?>>ريال يمني (قديم)</option>
                                <option value="SAR" <?php echo ($user['currency'] ?? '') == 'SAR' ? 'selected' : ''; ?>>ريال سعودي</option>
                                <option value="USD" <?php echo ($user['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>دولار أمريكي</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تغيير كلمة المرور -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تغيير كلمة المرور</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($password_success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $password_success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label class="form-label">كلمة المرور الحالية</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       name="current_password" 
                                       id="currentPassword"
                                       required>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="togglePassword('currentPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       name="new_password" 
                                       id="newPassword"
                                       required>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="togglePassword('newPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       name="confirm_password" 
                                       id="confirmPassword"
                                       required>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="togglePassword('confirmPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-key"></i> تغيير كلمة المرور
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تغيير الصورة -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تغيير صورة الملف الشخصي</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($image_success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $image_success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <img src="<?php echo $user['profile_image'] ?: 'img/default-profile.jpg'; ?>" 
                             alt="معاينة الصورة" 
                             class="profile-image mb-3"
                             id="imagePreview"
                             onerror="this.src='img/default-profile.jpg'">
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_image">
                        
                        <div class="form-group">
                            <label class="form-label">اختر صورة جديدة</label>
                            <input type="file" 
                                   class="form-control" 
                                   name="profile_image" 
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <small class="text-muted">الصيغ المسموحة: JPG, PNG, GIF, WEBP (الحد الأقصى 5MB)</small>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-upload"></i> رفع الصورة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // فتح مودال تعديل البيانات
        function openEditModal() {
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
        
        // فتح مودال تغيير كلمة المرور
        function openPasswordModal() {
            const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
            modal.show();
        }
        
        // فتح مودال تغيير الصورة
        function openImageModal() {
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }
        
        // تبديل عرض كلمة المرور
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // معاينة الصورة قبل الرفع
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        }
        
        // التحقق من صحة البيانات
        document.addEventListener('DOMContentLoaded', function() {
            // تحقق من رسائل التنبيه
            <?php if (isset($success_message) || isset($password_success) || isset($image_success)): ?>
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            <?php endif; ?>
            
            // تحسين تجربة المستخدم
            $('.nav-item').click(function(e) {
                $('.nav-item').removeClass('active');
                $(this).addClass('active');
            });
        });
        
        // تأثيرات الواجهة
        $(document).ready(function() {
            // إضافة تأثيرات للبطاقات
            $('.stat-card, .quick-link, .order-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
            
            // تحميل الصفحة بسلاسة
            $('main').hide().fadeIn(300);
        });
    </script>
</body>
</html>