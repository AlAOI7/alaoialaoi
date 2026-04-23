<?php
// admin/offers.php
session_start();
require_once '../config/database.php';
// require_once 'admin_auth.php';

// جلب جميع العروض
$sql = "SELECT o.*, COUNT(op.product_id) as products_count 
        FROM offers o 
        LEFT JOIN offer_products op ON o.id = op.offer_id 
        GROUP BY o.id 
        ORDER BY o.display_order ASC, o.created_at DESC";
$result = $conn->query($sql);
$offers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العروض</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .offer-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .active-badge {
            background: #28a745;
            color: white;
        }
        
        .inactive-badge {
            background: #dc3545;
            color: white;
        }
        
        .expired-badge {
            background: #ffc107;
            color: #000;
        }
        
        .btn-action {
            padding: 5px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
         
        <?php include 'sidebar.php'; ?>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
             <?php include 'header.php'; ?>
        <!-- الهيدر -->
        <div class="d-flex justify-content-between align-items-center py-3 mb-4 border-bottom">
            <h2 class="h4 mb-0">
                <i class="fas fa-bullhorn text-primary me-2"></i>
                إدارة العروض الإعلانية
            </h2>
            <a href="add-offer.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>إضافة عرض جديد
            </a>
        </div>
        
        <!-- إحصائيات -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي العروض</h5>
                        <p class="display-6 fw-bold text-primary"><?php echo count($offers); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title">العروض النشطة</h5>
                        <p class="display-6 fw-bold text-success">
                            <?php echo count(array_filter($offers, fn($o) => $o['is_active'] == 1)); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="card-title">العروض المنتهية</h5>
                        <p class="display-6 fw-bold text-warning">
                            <?php echo count(array_filter($offers, fn($o) => strtotime($o['end_date']) < time())); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h5 class="card-title">متوسط المنتجات</h5>
                        <p class="display-6 fw-bold text-info">
                            <?php echo count($offers) > 0 ? round(array_sum(array_column($offers, 'products_count')) / count($offers), 1) : 0; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- جدول العروض -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">قائمة العروض</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الصورة</th>
                                <th>العنوان</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
                                <th>المنتجات</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offers as $index => $offer): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <img src="../<?php echo $offer['image']; ?>" 
                                         alt="صورة العرض" 
                                         style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;"
                                         onerror="this.src='../img/default-offer.jpg'">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($offer['title']); ?></strong><br>
                                    <small class="text-muted"><?php echo substr($offer['description'], 0, 50); ?>...</small>
                                </td>
                                <td><?php echo date('Y/m/d', strtotime($offer['start_date'])); ?></td>
                                <td><?php echo date('Y/m/d', strtotime($offer['end_date'])); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $offer['products_count']; ?> منتج</span>
                                </td>
                                <td>
                                    <?php 
                                    $badge_class = 'inactive-badge';
                                    $badge_text = 'غير نشط';
                                    
                                    if ($offer['is_active'] == 1) {
                                        if (strtotime($offer['end_date']) < time()) {
                                            $badge_class = 'expired-badge';
                                            $badge_text = 'منتهي';
                                        } else {
                                            $badge_class = 'active-badge';
                                            $badge_text = 'نشط';
                                        }
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           value="<?php echo $offer['display_order']; ?>" 
                                           style="width: 70px;"
                                           onchange="updateOrder(<?php echo $offer['id']; ?>, this.value)">
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit-offer.php?id=<?php echo $offer['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="offer-products.php?id=<?php echo $offer['id']; ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-box"></i>
                                        </a>
                                        <button onclick="toggleStatus(<?php echo $offer['id']; ?>, <?php echo $offer['is_active']; ?>)" 
                                                class="btn btn-sm <?php echo $offer['is_active'] == 1 ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                            <i class="fas fa-<?php echo $offer['is_active'] == 1 ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                        <button onclick="deleteOffer(<?php echo $offer['id']; ?>)" 
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleStatus(offerId, currentStatus) {
            $.ajax({
                url: 'ajax/toggle-offer-status.php',
                method: 'POST',
                data: { 
                    offer_id: offerId,
                    current_status: currentStatus 
                },
                success: function() {
                    location.reload();
                }
            });
        }
        
        function updateOrder(offerId, order) {
            $.ajax({
                url: 'ajax/update-offer-order.php',
                method: 'POST',
                data: { 
                    offer_id: offerId,
                    order: order 
                }
            });
        }
        
        function deleteOffer(offerId) {
            if (confirm('هل أنت متأكد من حذف هذا العرض؟')) {
                $.ajax({
                    url: 'ajax/delete-offer.php',
                    method: 'POST',
                    data: { offer_id: offerId },
                    success: function() {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>