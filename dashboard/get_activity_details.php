
<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    die('غير مصرح');
}

if (!isset($_GET['id'])) {
    die('معرف النشاط مطلوب');
}

$activity_id = intval($_GET['id']);

$sql = "SELECT 
            u.name,
            u.email,
            u.user_type,
            u.phone,
            a.activity_type,
            a.activity_details,
            a.ip_address,
            a.device_type,
            a.browser_info,
            a.created_at,
            a.status
        FROM user_activities a
        JOIN users u ON a.user_id = u.id
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $activity_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $activity = $result->fetch_assoc();
    
    $activity_types = [
        'login' => 'تسجيل دخول',
        'logout' => 'تسجيل خروج',
        'add' => 'إضافة',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'view' => 'عرض'
    ];
    
    $user_types = [
        'user' => 'مستخدم',
        'admin' => 'مدير',
        'manager' => 'مدير فرع',
        'sales' => 'مبيعات',
        'support' => 'دعم'
    ];
?>

<div class="user-details">
    <div class="user-info-card">
        <h4>معلومات المستخدم</h4>
        <div class="info-item">
            <span class="info-label">اسم المستخدم:</span>
            <span class="info-value"><?php echo htmlspecialchars($activity['name']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">البريد الإلكتروني:</span>
            <span class="info-value"><?php echo htmlspecialchars($activity['email']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">الدور:</span>
            <span class="info-value"><?php echo $user_types[$activity['user_type']] ?? $activity['user_type']; ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">الهاتف:</span>
            <span class="info-value"><?php echo htmlspecialchars($activity['phone'] ?? 'غير متوفر'); ?></span>
        </div>
    </div>
    
    <div class="user-device-info">
        <h4>معلومات الجهاز</h4>
        <div class="info-item">
            <span class="info-label">نوع الجهاز:</span>
            <span class="info-value">
                <?php 
                    if ($activity['device_type'] == 'desktop') echo 'كمبيوتر';
                    elseif ($activity['device_type'] == 'mobile') echo 'هاتف محمول';
                    elseif ($activity['device_type'] == 'tablet') echo 'جهاز لوحي';
                    else echo 'غير معروف';
                ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">المتصفح:</span>
            <span class="info-value"><?php echo htmlspecialchars($activity['browser_info'] ?? 'غير معروف'); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">عنوان IP:</span>
            <span class="info-value"><?php echo htmlspecialchars($activity['ip_address']); ?></span>
        </div>
    </div>
</div>

<div class="user-info-card" style="margin-top: 20px;">
    <h4>تفاصيل النشاط</h4>
    <div class="info-item">
        <span class="info-label">نوع النشاط:</span>
        <span class="info-value">
            <span class="activity-type <?php echo $activity['activity_type']; ?>">
                <?php echo $activity_types[$activity['activity_type']] ?? $activity['activity_type']; ?>
            </span>
        </span>
    </div>
    <div class="info-item">
        <span class="info-label">التاريخ والوقت:</span>
        <span class="info-value">
            <?php 
                $date = new DateTime($activity['created_at']);
                echo $date->format('d/m/Y H:i:s');
            ?>
        </span>
    </div>
    <div class="info-item">
        <span class="info-label">الحالة:</span>
        <span class="info-value" style="color: <?php echo ($activity['status'] == 'success') ? 'green' : 'red'; ?>;">
            <?php echo ($activity['status'] == 'success') ? 'ناجح' : 'فشل'; ?>
        </span>
    </div>
    <div class="info-item">
        <span class="info-label">التفاصيل:</span>
        <span class="info-value"><?php echo htmlspecialchars($activity['activity_details']); ?></span>
    </div>
</div>

<?php
} else {
    echo '<div class="alert alert-danger">النشاط غير موجود</div>';
}
?>