<?php
// dashboard/functions.php

session_start();

// دالة التحقق من المصادقة
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
        header('Location: login.php');
        exit();
    }
}

// دالة جلب بيانات المستخدم
function getUser($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// دالة جلب جميع المستخدمين
function getAllUsers() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = [];
    
    while ($row = $result->fetch_assoc()) {
        // تحويل الهيكل ليتناسب مع الجدول الجديد
        $users[] = [
            'id' => $row['id'],
            'first_name' => $row['name'], // استخدام name ك first_name
            'last_name' => '', // غير موجود في الجدول الحالي
            'email' => $row['email'],
            'phone' => '', // غير موجود في الجدول الحالي
            'role' => $row['user_type'], // تحويل user_type إلى role
            'status' => $row['email_verified'] ? 'active' : 'pending',
            'created_at' => $row['created_at'],
            'last_activity' => null // غير موجود في الجدول الحالي
        ];
    }
    
    return $users;
}

// دالة إضافة مستخدم جديد
function addUser($data) {
    global $conn;
    
    // تحويل البيانات لتتناسب مع الجدول الحالي
    $name = $data['first_name'] . ($data['last_name'] ? ' ' . $data['last_name'] : '');
    $user_type = $data['role'];
    $email_verified = $data['status'] === 'active' ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type, email_verified) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $data['email'], $data['password'], $user_type, $email_verified);
    
    return $stmt->execute();
}

// دالة تحديث المستخدم
function updateUser($id, $data) {
    global $conn;
    
    // تحويل البيانات لتتناسب مع الجدول الحالي
    $name = $data['first_name'] . ($data['last_name'] ? ' ' . $data['last_name'] : '');
    $user_type = $data['role'];
    $email_verified = $data['status'] === 'active' ? 1 : 0;
    
    if (isset($data['password'])) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, user_type = ?, email_verified = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $name, $data['email'], $data['password'], $user_type, $email_verified, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, user_type = ?, email_verified = ? WHERE id = ?");
        $stmt->bind_param("sssii", $name, $data['email'], $user_type, $email_verified, $id);
    }
    
    return $stmt->execute();
}

// دالة حذف المستخدم
function deleteUser($id) {
    global $conn;
    
    // منع حذف المستخدم الحالي
    if ($id == $_SESSION['user_id']) {
        return false;
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
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
    $stats['total_users'] = $result->fetch_assoc()['total'];
    
    // المستخدمين النشطين (مفعلين بالبريد)
    $result = $conn->query("SELECT COUNT(*) as active FROM users WHERE email_verified = 1");
    $stats['active_users'] = $result->fetch_assoc()['active'];
    
    // في انتظار التفعيل
    $result = $conn->query("SELECT COUNT(*) as pending FROM users WHERE email_verified = 0");
    $stats['pending_users'] = $result->fetch_assoc()['pending'];
    
    // المعطلين (غير موجود في الجدول الحالي، نستخدم pending كنفس الشيء)
    $stats['inactive_users'] = 0;
    
    return $stats;
}
?>