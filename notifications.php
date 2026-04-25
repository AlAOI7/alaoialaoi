<?php
session_start();
require_once 'config/database.php';
require_once 'functions.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// إنشاء جدول الإشعارات إذا لم يكن موجوداً
$create_table = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_table);

// تحديث الإشعارات كـ "مقروءة" إذا تم النقر على إشعار معين
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notif_id = intval($_GET['read']);
    $update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("ii", $notif_id, $user_id);
    $update_stmt->execute();
    
    // إعادة التوجيه للرابط إذا وجد
    $link_stmt = $conn->prepare("SELECT link FROM notifications WHERE id = ?");
    $link_stmt->bind_param("i", $notif_id);
    $link_stmt->execute();
    $link_res = $link_stmt->get_result();
    if ($link_row = $link_res->fetch_assoc()) {
        if (!empty($link_row['link'])) {
            header('Location: ' . $link_row['link']);
            exit();
        }
    }
    header('Location: notifications.php');
    exit();
}

// تحديد الكل كمقروء
if (isset($_POST['mark_all_read'])) {
    $update_all = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $update_all->bind_param("i", $user_id);
    $update_all->execute();
    header('Location: notifications.php');
    exit();
}

// حذف الإشعارات المقروءة
if (isset($_POST['clear_read'])) {
    $del_stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ? AND is_read = 1");
    $del_stmt->bind_param("i", $user_id);
    $del_stmt->execute();
    header('Location: notifications.php');
    exit();
}

// جلب الإشعارات
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$notif_query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($notif_query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// حساب إجمالي الإشعارات للترقيم
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_notifications = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $limit);

$page_title = "إشعاراتي";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | المتجر</title>
    <!-- تضمين أنماط Bootstrap وغيرها -->
</head>
<body style="background-color: #f8f9fa;">

<?php include 'header.php'; ?>

<div class="container py-4 my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-bell text-primary me-2"></i> الإشعارات</h2>
        <div class="d-flex gap-2">
            <form method="POST">
                <button type="submit" name="mark_all_read" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-double me-1"></i> تحديد الكل كمقروء
                </button>
            </form>
            <form method="POST">
                <button type="submit" name="clear_read" class="btn btn-outline-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف الإشعارات المقروءة؟')">
                    <i class="fas fa-trash-alt me-1"></i> مسح المقروء
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if (empty($notifications)): ?>
                <div class="text-center bg-white p-5 rounded-4 shadow-sm">
                    <div class="mb-3">
                        <i class="fas fa-bell-slash fa-4x text-muted" style="opacity: 0.5;"></i>
                    </div>
                    <h4 class="text-muted">لا توجد إشعارات حالياً</h4>
                    <p class="text-muted mb-0">سنقوم بإبلاغك هنا بكل ما هو جديد</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-4 shadow-sm overflow-hidden">
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notif): ?>
                            <?php 
                                $is_read = $notif['is_read'] == 1;
                                $bg_class = $is_read ? 'bg-white' : 'bg-light';
                                $icon = 'fa-bell';
                                $icon_color = 'text-primary';
                                
                                switch($notif['type']) {
                                    case 'order': $icon = 'fa-shopping-bag'; $icon_color = 'text-success'; break;
                                    case 'promo': $icon = 'fa-tag'; $icon_color = 'text-danger'; break;
                                    case 'system': $icon = 'fa-info-circle'; $icon_color = 'text-info'; break;
                                }
                                
                                $link = !empty($notif['link']) ? "?read={$notif['id']}" : "#";
                            ?>
                            <a href="<?= $link ?>" class="list-group-item list-group-item-action p-4 <?= $bg_class ?> border-bottom" style="transition: background 0.3s;">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle p-3 me-3" style="background-color: rgba(0,0,0,0.03);">
                                            <i class="fas <?= $icon ?> <?= $icon_color ?> fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold <?= $is_read ? 'text-dark' : 'text-primary' ?>">
                                                <?= htmlspecialchars($notif['title']) ?>
                                                <?php if (!$is_read): ?>
                                                    <span class="badge bg-danger ms-2 rounded-pill" style="font-size: 0.7em;">جديد</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-1 text-muted"><?= nl2br(htmlspecialchars($notif['message'])) ?></p>
                                            <small class="text-secondary">
                                                <i class="far fa-clock me-1"></i> 
                                                <span dir="ltr"><?= date('Y-m-d H:i', strtotime($notif['created_at'])) ?></span>
                                            </small>
                                        </div>
                                    </div>
                                    <?php if (!$is_read && empty($notif['link'])): ?>
                                        <form method="GET" style="display:inline;" onclick="event.stopPropagation();">
                                            <button type="submit" name="read" value="<?= $notif['id'] ?>" class="btn btn-sm btn-link text-decoration-none" title="تحديد كمقروء">
                                                <i class="fas fa-circle text-primary" style="font-size: 10px;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">السابق</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">التالي</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
