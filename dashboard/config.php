<?php
// الاتصال بقاعدة البيانات

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "be_pretty";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// تعيين ترميز الأحرف
$conn->set_charset("utf8");
// دوال مساعدة
function getOrders($filters = []) {
    global $conn;
    
    $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
            FROM orders o 
            JOIN users u ON o.customer_id = u.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (o.invoice_number LIKE ? OR u.name LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    return $orders;
}
function updateOrderStatus($order_id, $status) {
    global $conn;
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
function getOrderDetails($order_id) {
    global $conn;
    
    $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
            FROM orders o 
            JOIN users u ON o.customer_id = u.id 
            WHERE o.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if ($order) {
        $sql_items = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        $order['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
        $stmt_items->close();
    }
    
    return $order;
}
function getPaymentMethodText($method) {
    $methods = [
        'credit_card' => 'بطاقة ائتمان',
        'bank_transfer' => 'تحويل بنكي',
        'cash_on_delivery' => 'الدفع عند الاستلام'
    ];
    return $methods[$method] ?? $method;
}
function getDeliveryMethodText($method) {
    $methods = [
        'fast_delivery' => 'توصيل سريع',
        'normal_delivery' => 'توصيل عادي'
    ];
    return $methods[$method] ?? $method;
}
function getStatusText($status) {
    $statuses = [
        'pending' => 'قيد المراجعة',
        'approved' => 'تمت الموافقة',
        'not_paid' => 'لم يتم الدفع',
        'in_delivery' => 'قيد التوصيل',
        'completed' => 'تم التسليم'
    ];
    return $statuses[$status] ?? $status;
}

function logUserActivity($user_id, $activity_type, $details = '', $status = 'success') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $browser_info = $_SERVER['HTTP_USER_AGENT'];
    $device_type = getDeviceType(); // دالة تحدد نوع الجهاز
    
    $sql = "INSERT INTO user_activities (user_id, activity_type, activity_details, ip_address, device_type, browser_info, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $activity_type, $details, $ip_address, $device_type, $browser_info, $status);
    return $stmt->execute();
}

// دالة لتحديد نوع الجهاز
function getDeviceType() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    if (preg_match('/mobile/i', $user_agent)) {
        return 'mobile';
    } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
        return 'tablet';
    } else {
        return 'desktop';
    }
}
// دالة جلب إحصائيات المستخدمين المحدثة
// function getUserStats() {
//     global $conn;
    
//     $stats = [];
    
//     // إجمالي المستخدمين
//     $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
//     $stmt->execute();
//     $stats['total_users'] = $stmt->get_result()->fetch_assoc()['total'];
    
//     // عدد المستخدمين العاديين
//     $stmt = $conn->prepare("SELECT COUNT(*) as regular_users FROM users WHERE role = 'user'");
//     $stmt->execute();
//     $stats['regular_users'] = $stmt->get_result()->fetch_assoc()['regular_users'];
    
//     // عدد فريق العمل
//     $stmt = $conn->prepare("SELECT COUNT(*) as staff FROM users WHERE role IN ('admin', 'manager', 'sales', 'support')");
//     $stmt->execute();
//     $stats['staff'] = $stmt->get_result()->fetch_assoc()['staff'];
    
//     // المستخدمين غير المفعلين
//     $stmt = $conn->prepare("SELECT COUNT(*) as not_verified FROM users WHERE email_verified = 0");
//     $stmt->execute();
//     $stats['not_verified_users'] = $stmt->get_result()->fetch_assoc()['not_verified'];
    
//     return $stats;
// }



// للاختبار فقط: إذا لم تكن الجلسة موجودة، أنشئ جلسة تجريبية
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'admin@example.com';
    $_SESSION['user_role'] = 'admin';
}

// دالة التحقق من المصادقة
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
        header('Location: login.php');
        exit();
    }
}




// // دالة إضافة مستخدم جديد
// function addUser($data) {
//     global $conn;
    
//     $name = $data['first_name'] . ($data['last_name'] ? ' ' . $data['last_name'] : '');
//     $user_type = $data['role'];
//     $email_verified = $data['status'] === 'active' ? 1 : 0;
    
//     $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type, email_verified) VALUES (?, ?, ?, ?, ?)");
//     $stmt->bind_param("ssssi", $name, $data['email'], $data['password'], $user_type, $email_verified);
    
//     return $stmt->execute();
// }
// دالة إضافة مستخدم جديد<?php
// اتصال قاعدة البيانات

// دالة جلب بيانات مستخدم محدد
function getUser($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT *, 
                            CASE 
                                WHEN email_verified = 1 THEN 'active'
                                ELSE 'pending' 
                            END as status
                           FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // إذا كان first_name فارغاً، نقوم بتقسيم الاسم
        if (empty($row['first_name']) && !empty($row['name'])) {
            $name_parts = explode(' ', $row['name'], 2);
            $row['first_name'] = $name_parts[0];
            $row['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
        } else {
            $row['last_name'] = $row['last_name'] ?? '';
        }
        
        // إضافة مفتاح 'role' لتتوافق مع الكود
        $row['role'] = $row['user_type'];
        
        return $row;
    }
    
    return null;
}

// دالة جلب جميع المستخدمين
function getAllUsers() {
    global $conn;
    
    $result = $conn->query("SELECT *, 
                           CASE 
                               WHEN email_verified = 1 THEN 'active'
                               ELSE 'pending' 
                           END as status
                           FROM users ORDER BY created_at DESC");
    $users = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // معالجة الاسم لاستخراج first_name و last_name إذا كانا فارغين
            if (empty($row['first_name']) && !empty($row['name'])) {
                $name_parts = explode(' ', $row['name'], 2);
                $row['first_name'] = $name_parts[0];
                $row['last_name'] = isset($name_parts[1]) ? $name_parts[1] : '';
            } else {
                $row['last_name'] = $row['last_name'] ?? '';
            }
            
            $row['role'] = $row['user_type'];
            $users[] = $row;
        }
    }
    
    return $users;
}

// دالة إضافة مستخدم جديد
function addUser($data) {
    global $conn;
    
    // التحقق من وجود البريد الإلكتروني أولاً
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $data['email']);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        return array('success' => false, 'message' => 'البريد الإلكتروني مسجل مسبقاً');
    }
    $checkStmt->close();
    
    // تحضير البيانات للإدخال
    $name = $data['first_name'] . ($data['last_name'] ? ' ' . $data['last_name'] : '');
    $first_name = $data['first_name'];
    $last_name = $data['last_name'] ?? null;
    $user_type = $data['role'];
    $email_verified = $data['status'] === 'active' ? 1 : 0;
    $status = $data['status'];
    
    // إضافة رقم الهاتف إذا كان موجوداً في البيانات
    $phone = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : null;
    
    // استخدام استعلام معدل
    if ($phone && $last_name) {
        $stmt = $conn->prepare("INSERT INTO users (name, first_name, last_name, email, phone, password, user_type, email_verified, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssis", $name, $first_name, $last_name, $data['email'], $phone, $data['password'], $user_type, $email_verified, $status);
    } elseif ($phone) {
        $stmt = $conn->prepare("INSERT INTO users (name, first_name, email, phone, password, user_type, email_verified, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $name, $first_name, $data['email'], $phone, $data['password'], $user_type, $email_verified, $status);
    } elseif ($last_name) {
        $stmt = $conn->prepare("INSERT INTO users (name, first_name, last_name, email, password, user_type, email_verified, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $name, $first_name, $last_name, $data['email'], $data['password'], $user_type, $email_verified, $status);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, first_name, email, password, user_type, email_verified, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $name, $first_name, $data['email'], $data['password'], $user_type, $email_verified, $status);
    }
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        return array('success' => true, 'message' => 'تم إضافة المستخدم بنجاح', 'user_id' => $user_id);
    } else {
        return array('success' => false, 'message' => 'حدث خطأ أثناء الإضافة: ' . $conn->error);
    }
}

// دالة تحديث المستخدم
function updateUser($id, $data) {
    global $conn;
    
    // التحقق من وجود البريد الإلكتروني (استثناء المستخدم الحالي)
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->bind_param("si", $data['email'], $id);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        return array('success' => false, 'message' => 'البريد الإلكتروني مسجل مسبقاً لمستخدم آخر');
    }
    $checkStmt->close();
    
    $name = $data['first_name'] . ($data['last_name'] ? ' ' . $data['last_name'] : '');
    $first_name = $data['first_name'];
    $last_name = isset($data['last_name']) && !empty($data['last_name']) ? $data['last_name'] : null;
    $user_type = $data['role'];
    $email_verified = $data['status'] === 'active' ? 1 : 0;
    $status = $data['status'];
    $phone = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : null;
    
    if (isset($data['password']) && !empty($data['password'])) {
        if ($phone && $last_name) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, last_name = ?, email = ?, phone = ?, password = ?, user_type = ?, email_verified = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssssisi", $name, $first_name, $last_name, $data['email'], $phone, $data['password'], $user_type, $email_verified, $status, $id);
        } elseif ($phone) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, email = ?, phone = ?, password = ?, user_type = ?, email_verified = ?, status = ?, last_name = NULL WHERE id = ?");
            $stmt->bind_param("ssssssisi", $name, $first_name, $data['email'], $phone, $data['password'], $user_type, $email_verified, $status, $id);
        } elseif ($last_name) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, last_name = ?, email = ?, password = ?, user_type = ?, email_verified = ?, status = ?, phone = NULL WHERE id = ?");
            $stmt->bind_param("ssssssisi", $name, $first_name, $last_name, $data['email'], $data['password'], $user_type, $email_verified, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, email = ?, password = ?, user_type = ?, email_verified = ?, status = ?, last_name = NULL, phone = NULL WHERE id = ?");
            $stmt->bind_param("ssssssisi", $name, $first_name, $data['email'], $data['password'], $user_type, $email_verified, $status, $id);
        }
    } else {
        if ($phone && $last_name) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, last_name = ?, email = ?, phone = ?, user_type = ?, email_verified = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssssssisi", $name, $first_name, $last_name, $data['email'], $phone, $user_type, $email_verified, $status, $id);
        } elseif ($phone) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, email = ?, phone = ?, user_type = ?, email_verified = ?, status = ?, last_name = NULL WHERE id = ?");
            $stmt->bind_param("sssssisi", $name, $first_name, $data['email'], $phone, $user_type, $email_verified, $status, $id);
        } elseif ($last_name) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, last_name = ?, email = ?, user_type = ?, email_verified = ?, status = ?, phone = NULL WHERE id = ?");
            $stmt->bind_param("sssssisi", $name, $first_name, $last_name, $data['email'], $user_type, $email_verified, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, first_name = ?, email = ?, user_type = ?, email_verified = ?, status = ?, last_name = NULL, phone = NULL WHERE id = ?");
            $stmt->bind_param("sssssisi", $name, $first_name, $data['email'], $user_type, $email_verified, $status, $id);
        }
    }
    
    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'تم تحديث المستخدم بنجاح');
    } else {
        return array('success' => false, 'message' => 'حدث خطأ أثناء التحديث: ' . $conn->error);
    }
}

// دالة حذف المستخدم
function deleteUser($id) {
    global $conn;
    
    // التحقق من وجود المستخدم
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows === 0) {
        $checkStmt->close();
        return array('success' => false, 'message' => 'المستخدم غير موجود');
    }
    $checkStmt->close();
    
    // حذف المستخدم
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return array('success' => true, 'message' => 'تم حذف المستخدم بنجاح');
    } else {
        return array('success' => false, 'message' => 'حدث خطأ أثناء الحذف: ' . $conn->error);
    }
}

// دالة إحصائيات المستخدمين
function getUserStats() {
    global $conn;
    
    $stats = [
        'total_users' => 0,
        'active_users' => 0,
        'pending_users' => 0,
        'inactive_users' => 0
    ];
    
    // إجمالي المستخدمين
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($result) {
        $stats['total_users'] = $result->fetch_assoc()['total'];
    }
    
    // المستخدمين النشطين
    $result = $conn->query("SELECT COUNT(*) as active FROM users WHERE status = 'active' AND email_verified = 1");
    if ($result) {
        $stats['active_users'] = $result->fetch_assoc()['active'];
    }
    
    // المستخدمين في انتظار التفعيل
    $result = $conn->query("SELECT COUNT(*) as pending FROM users WHERE status = 'pending' OR email_verified = 0");
    if ($result) {
        $stats['pending_users'] = $result->fetch_assoc()['pending'];
    }
    
    // المستخدمين المعطلين
    $result = $conn->query("SELECT COUNT(*) as inactive FROM users WHERE status = 'inactive'");
    if ($result) {
        $stats['inactive_users'] = $result->fetch_assoc()['inactive'];
    }
    
    return $stats;
}

?>