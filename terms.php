<?php
// terms.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شروط الاستخدام | Be Pretty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .terms-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e0e0e0;
        }
        .term-item {
            border-right: 3px solid #dc3545;
            padding-right: 15px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        .term-item:hover {
            border-right-color: #ff6b6b;
            background-color: #fff5f5;
            padding-right: 20px;
        }
        .last-update {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            border: 1px dashed #dee2e6;
        }
        .note-badge {
            background-color: #e7f3ff;
            color: #0d6efd;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    
    <?php include 'header.php'; ?>

    <main class="container-fluid py-4" style="margin-top: 60px; margin-bottom: 70px;">
        <div class="terms-section bg-white p-4 rounded-3 shadow-sm">
            <h2 class="fw-bold text-center text-danger mb-4">
                <i class="fas fa-file-contract me-2"></i>الشروط والأحكام
            </h2>
            <p class="text-muted text-center lead">
                يرجى قراءة هذه الشروط بعناية قبل استخدام موقعنا.
            </p>
            
            <?php
            // استرجاع الشروط من قاعدة البيانات
            try {
                $stmt = $pdo->query("
                    SELECT title, content, note 
                    FROM terms 
                    WHERE is_active = 1 
                    ORDER BY display_order ASC
                ");
                $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($terms) > 0) {
                    $counter = 1;
                    foreach ($terms as $term) {
                        ?>
                        <div class="term-item">
                            <h5 class="fw-bold text-dark">
                                <span class="badge bg-danger me-2"><?php echo $counter++; ?></span>
                                <?php echo htmlspecialchars($term['title']); ?>
                            </h5>
                            <p class="text-muted mb-2">
                                <?php echo nl2br(htmlspecialchars($term['content'])); ?>
                            </p>
                            <?php if (!empty($term['note'])): ?>
                                <div class="d-flex align-items-center mt-2">
                                    <span class="badge note-badge me-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        ملاحظة
                                    </span>
                                    <small class="text-muted"><?php echo htmlspecialchars($term['note']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        لا توجد شروط مضافة حالياً
                    </div>
                    <?php
                }
                
                // استرجاع تاريخ آخر تحديث
                $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'terms_last_update'");
                $lastUpdate = $stmt->fetchColumn();
                if (!$lastUpdate) {
                    $lastUpdate = '25 سبتمبر 2025';
                }
                
            } catch(PDOException $e) {
                echo '<div class="alert alert-danger">خطأ في تحميل الشروط: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <div class="last-update text-center mt-5">
                <p class="text-muted mb-0">
                    <i class="fas fa-history me-2"></i>
                    <strong>آخر تحديث:</strong> <?php echo htmlspecialchars($lastUpdate); ?>
                </p>
            </div>
        </div>
    </main>

  <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>