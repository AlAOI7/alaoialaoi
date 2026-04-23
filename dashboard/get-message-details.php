<?php
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit('غير مصرح');
}

if (isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    
    $query = "
        SELECT m.*, s.name as subject_name, u.username as user_name,
               a.username as responder_name
        FROM contact_messages m
        LEFT JOIN contact_subjects s ON m.subject_id = s.id
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN admin_users a ON m.response_by = a.id
        WHERE m.id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();
    $stmt->close();
    
    if ($message) {
        ?>
        <div class="message-details">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-user me-2"></i> المرسل</h6>
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($message['name']); ?></h5>
                            <p class="card-text mb-1">
                                <i class="fas fa-envelope me-1"></i>
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </a>
                            </p>
                            <?php if(!empty($message['phone'])): ?>
                            <p class="card-text mb-1">
                                <i class="fas fa-phone me-1"></i>
                                <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                    <?php echo htmlspecialchars($message['phone']); ?>
                                </a>
                            </p>
                            <?php endif; ?>
                            <?php if(!empty($message['user_name'])): ?>
                            <p class="card-text mb-0">
                                <i class="fas fa-user-tag me-1"></i>
                                مستخدم مسجل: <?php echo htmlspecialchars($message['user_name']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6><i class="fas fa-info-circle me-2"></i> معلومات الرسالة</h6>
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-2"><strong>الموضوع:</strong><br>
                                    <?php echo htmlspecialchars($message['subject_name'] ?? $message['subject_other'] ?? 'غير محدد'); ?></p>
                                    
                                    <p class="mb-2"><strong>التاريخ:</strong><br>
                                    <?php echo date('Y/m/d H:i', strtotime($message['created_at'])); ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-2"><strong>الحالة:</strong><br>
                                    <?php 
                                    $statusLabels = [
                                        'new' => '<span class="badge bg-info">جديد</span>',
                                        'read' => '<span class="badge bg-primary">مقروء</span>',
                                        'replied' => '<span class="badge bg-success">تم الرد</span>',
                                        'pending' => '<span class="badge bg-warning">قيد الانتظار</span>',
                                        'resolved' => '<span class="badge bg-secondary">تم الحل</span>'
                                    ];
                                    echo $statusLabels[$message['status']] ?? $message['status'];
                                    ?></p>
                                    
                                    <p class="mb-0"><strong>الأولوية:</strong><br>
                                    <?php 
                                    $priorityLabels = [
                                        'urgent' => '<span class="badge bg-danger">عاجل</span>',
                                        'high' => '<span class="badge bg-warning">عالي</span>',
                                        'normal' => '<span class="badge bg-primary">عادي</span>',
                                        'low' => '<span class="badge bg-secondary">منخفض</span>'
                                    ];
                                    echo $priorityLabels[$message['priority']] ?? '<span class="badge bg-primary">عادي</span>';
                                    ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <h6><i class="fas fa-comment me-2"></i> نص الرسالة</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if(!empty($message['response'])): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h6><i class="fas fa-reply me-2"></i> الرد</h6>
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">تم الرد بواسطة: <?php echo htmlspecialchars($message['responder_name'] ?? 'المشرف'); ?></h6>
                                <small><?php echo date('Y/m/d H:i', strtotime($message['response_at'])); ?></small>
                            </div>
                            <?php echo nl2br(htmlspecialchars($message['response'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row mt-4">
                <div class="col-12">
                    <h6><i class="fas fa-info me-2"></i> معلومات تقنية</h6>
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><small><strong>عنوان IP:</strong> <?php echo htmlspecialchars($message['ip_address']); ?></small></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><small><strong>المتصفح:</strong> <?php echo htmlspecialchars(substr($message['user_agent'], 0, 50)); ?></small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">الرسالة غير موجودة</div>';
    }
} else {
    echo '<div class="alert alert-warning">معرف الرسالة غير محدد</div>';
}
?>