<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل دخول العميل
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// معالجة البحث والتصفية
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// بناء استعلام SQL
$sql = "SELECT r.*, o.invoice_number, 
               CONCAT(u.first_name, ' ', u.last_name) as customer_name,
               u.email, u.phone
        FROM returns r
        LEFT JOIN orders o ON r.order_id = o.id
        LEFT JOIN users u ON r.customer_id = u.id
        WHERE r.customer_id = ?";

$params = [$customer_id];
$types = "i";

if (!empty($search)) {
    $sql .= " AND (r.return_number LIKE ? OR r.product_name LIKE ? OR o.invoice_number LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

if (!empty($status_filter)) {
    $sql .= " AND r.return_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(r.created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(r.created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY r.created_at DESC";

// تنفيذ الاستعلام
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$returns = $result->fetch_all(MYSQLI_ASSOC);

// الحصول على إحصائيات المرتجعات
$stats_sql = "SELECT 
    COUNT(*) as total_returns,
    SUM(CASE WHEN return_status = 'completed' THEN return_amount ELSE 0 END) as total_refunded,
    SUM(CASE WHEN return_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN return_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN return_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM returns WHERE customer_id = ?";
    
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $customer_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات الإرجاع - لوحة العميل</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-completed { background-color: #cce5ff; color: #004085; }
        
        .return-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .return-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            border-radius: 10px;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card.total { background: linear-gradient(45deg, #4361ee, #3a56d4); }
        .stat-card.refunded { background: linear-gradient(45deg, #2ecc71, #27ae60); }
        .stat-card.pending { background: linear-gradient(45deg, #f39c12, #e67e22); }
        .stat-card.approved { background: linear-gradient(45deg, #3498db, #2980b9); }
        
        .filter-section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .return-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .badge-reason {
            background-color: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 15px;
            }
            
            .card-header h5 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold">
                    <i class="fas fa-exchange-alt me-2"></i>طلبات الإرجاع الخاصة بي
                </h2>
                <p class="text-muted">عرض وتتبع جميع طلبات الإرجاع الخاصة بك</p>
            </div>
        </div>
        
        <!-- إحصائيات سريعة -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card total">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo $stats['total_returns'] ?? 0; ?></h5>
                            <small>إجمالي الطلبات</small>
                        </div>
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card refunded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($stats['total_refunded'] ?? 0, 2); ?> ر.س</h5>
                            <small>المبلغ المسترد</small>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card pending">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo $stats['pending_count'] ?? 0; ?></h5>
                            <small>قيد المراجعة</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stat-card approved">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo $stats['approved_count'] ?? 0; ?></h5>
                            <small>تم الموافقة</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- فلترة وبحث -->
        <div class="filter-section">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" 
                           placeholder="ابحث برقم الإرجاع أو المنتج..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>قيد المراجعة</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>تم الموافقة</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>مرفوض</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>"
                           placeholder="من تاريخ">
                </div>
                
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>"
                           placeholder="إلى تاريخ">
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>بحث
                    </button>
                    <a href="customer_returns.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
        
        <!-- قائمة طلبات الإرجاع -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">طلبات الإرجاع (<?php echo count($returns); ?>)</h5>
                <a href="new_return.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i>طلب إرجاع جديد
                </a>
            </div>
            
            <div class="card-body">
                <?php if (empty($returns)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">لا توجد طلبات إرجاع</h5>
                        <p class="text-muted">لم تقم بإنشاء أي طلبات إرجاع بعد</p>
                        <a href="new_return.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>إنشاء طلب إرجاع جديد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الإرجاع</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>المبلغ</th>
                                    <th>سبب الإرجاع</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returns as $return): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($return['return_number']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($return['invoice_number']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($return['product_name']); ?>
                                            <?php if (!empty($return['size'])): ?>
                                                <br><small class="text-muted">المقاس: <?php echo htmlspecialchars($return['size']); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($return['color'])): ?>
                                                <br><small class="text-muted">اللون: <?php echo htmlspecialchars($return['color']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($return['quantity']); ?></td>
                                        <td>
                                            <strong><?php echo number_format($return['return_amount'], 2); ?> ر.س</strong>
                                        </td>
                                        <td>
                                            <?php
                                            $reason_labels = [
                                                'defective' => 'منتج معيب',
                                                'wrong-item' => 'منتج خاطئ',
                                                'damaged' => 'منتج تالف',
                                                'not-needed' => 'غير محتاج',
                                                'other' => 'أسباب أخرى'
                                            ];
                                            echo '<span class="badge-reason">' . 
                                                 ($reason_labels[$return['return_reason']] ?? $return['return_reason']) . 
                                                 '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_labels = [
                                                'pending' => 'قيد المراجعة',
                                                'approved' => 'تم الموافقة',
                                                'rejected' => 'مرفوض',
                                                'completed' => 'مكتمل'
                                            ];
                                            $status_class = 'status-' . $return['return_status'];
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo $status_labels[$return['return_status']] ?? $return['return_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('Y/m/d', strtotime($return['created_at'])); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('h:i A', strtotime($return['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailsModal<?php echo $return['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($return['return_status'] == 'pending'): ?>
                                                <a href="edit_return.php?id=<?php echo $return['id']; ?>" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal for Details -->
                                    <div class="modal fade" id="detailsModal<?php echo $return['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تفاصيل طلب الإرجاع</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>معلومات الطلب:</h6>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th>رقم الإرجاع:</th>
                                                                    <td><?php echo htmlspecialchars($return['return_number']); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>رقم الفاتورة:</th>
                                                                    <td><?php echo htmlspecialchars($return['invoice_number']); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>تاريخ الطلب:</th>
                                                                    <td><?php echo date('Y/m/d h:i A', strtotime($return['created_at'])); ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <h6>معلومات المنتج:</h6>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th>اسم المنتج:</th>
                                                                    <td><?php echo htmlspecialchars($return['product_name']); ?></td>
                                                                </tr>
                                                                <?php if (!empty($return['size'])): ?>
                                                                <tr>
                                                                    <th>المقاس:</th>
                                                                    <td><?php echo htmlspecialchars($return['size']); ?></td>
                                                                </tr>
                                                                <?php endif; ?>
                                                                <?php if (!empty($return['color'])): ?>
                                                                <tr>
                                                                    <th>اللون:</th>
                                                                    <td><?php echo htmlspecialchars($return['color']); ?></td>
                                                                </tr>
                                                                <?php endif; ?>
                                                                <tr>
                                                                    <th>الكمية:</th>
                                                                    <td><?php echo htmlspecialchars($return['quantity']); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>سعر الوحدة:</th>
                                                                    <td><?php echo number_format($return['unit_price'], 2); ?> ر.س</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>تفاصيل الإرجاع:</h6>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th>سبب الإرجاع:</th>
                                                                    <td><?php echo $reason_labels[$return['return_reason']] ?? $return['return_reason']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>حالة الإرجاع:</th>
                                                                    <td>
                                                                        <span class="status-badge <?php echo $status_class; ?>">
                                                                            <?php echo $status_labels[$return['return_status']] ?? $return['return_status']; ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>المبلغ المسترد:</th>
                                                                    <td><strong><?php echo number_format($return['return_amount'], 2); ?> ر.س</strong></td>
                                                                </tr>
                                                                <?php if (!empty($return['return_notes'])): ?>
                                                                <tr>
                                                                    <th>ملاحظات:</th>
                                                                    <td><?php echo nl2br(htmlspecialchars($return['return_notes'])); ?></td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- سجل النشاطات -->
                                                    <?php
                                                    // جلب سجل النشاطات لهذا الإرجاع
                                                    $log_sql = "SELECT rl.*, u.display_name 
                                                               FROM return_logs rl
                                                               LEFT JOIN users u ON rl.created_by = u.id
                                                               WHERE rl.return_id = ?
                                                               ORDER BY rl.created_at DESC";
                                                    $log_stmt = $conn->prepare($log_sql);
                                                    $log_stmt->bind_param("i", $return['id']);
                                                    $log_stmt->execute();
                                                    $logs_result = $log_stmt->get_result();
                                                    $logs = $logs_result->fetch_all(MYSQLI_ASSOC);
                                                    
                                                    if (!empty($logs)): ?>
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>سجل النشاطات:</h6>
                                                            <div class="timeline">
                                                                <?php foreach ($logs as $log): ?>
                                                                <div class="d-flex mb-2">
                                                                    <div class="flex-shrink-0">
                                                                        <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                                                    </div>
                                                                    <div class="flex-grow-1 ms-3">
                                                                        <p class="mb-0">
                                                                            <strong><?php echo htmlspecialchars($log['action']); ?></strong>
                                                                            <?php if (!empty($log['description'])): ?>
                                                                                <br><small><?php echo htmlspecialchars($log['description']); ?></small>
                                                                            <?php endif; ?>
                                                                        </p>
                                                                        <small class="text-muted">
                                                                            <?php echo date('Y/m/d h:i A', strtotime($log['created_at'])); ?>
                                                                            <?php if (!empty($log['display_name'])): ?>
                                                                                • بواسطة: <?php echo htmlspecialchars($log['display_name']); ?>
                                                                            <?php endif; ?>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                    <?php if ($return['return_status'] == 'pending'): ?>
                                                        <a href="edit_return.php?id=<?php echo $return['id']; ?>" class="btn btn-warning">
                                                            <i class="fas fa-edit me-1"></i>تعديل الطلب
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
     <?php include 'footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // تحديث وقت الصفحة كل 60 ثانية لتحديث الحالات
        setTimeout(function() {
            window.location.reload();
        }, 60000);
        
        // طباعة طلب الإرجاع
        function printReturn(returnId) {
            window.open('print_return.php?id=' + returnId, '_blank');
        }
    </script>
</body>
</html>