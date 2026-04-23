<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// إنشاء مجلد لصور الشعارات إذا لم يكن موجوداً
if (!file_exists('brands_logos')) {
    mkdir('brands_logos', 0777, true);
}

// إنشاء المجلدات اللازمة
$folders = ['blog_images', 'blog_additional_images', 'import_files'];
foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
}

// معالجة العمليات
$message = "";
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// حفظ بيانات النموذج في حالة وجود أخطاء
$formData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_blog']) || isset($_POST['update_blog'])) {
        // إضافة أو تعديل مدونة
        $title = $conn->real_escape_string($_POST['title']);
        $summary = $conn->real_escape_string($_POST['summary']);
        $content = $conn->real_escape_string($_POST['content']);
        $category_id = (int)$_POST['category_id'];
        $publish_date = $_POST['publish_date'];
        $status = $conn->real_escape_string($_POST['status']);
        
        // حفظ بيانات النموذج في حالة وجود أخطاء
        $formData = [
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'category_id' => $category_id,
            'publish_date' => $publish_date,
            'status' => $status
        ];
        
        // معالجة رفع الصورة الرئيسية
        $main_image = NULL;
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['main_image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $main_image = 'blog_images/' . time() . '_' . basename($_FILES['main_image']['name']);
                move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image);
            }
        }
        
        if (isset($_POST['add_blog'])) {
            // إضافة مدونة جديدة
            $sql = "INSERT INTO blogs (title, summary, content, category_id, main_image, publish_date, status) 
                    VALUES ('$title', '$summary', '$content', $category_id, " . 
                    ($main_image ? "'$main_image'" : "NULL") . ", '$publish_date', '$status')";
            
            if ($conn->query($sql)) {
                $blog_id = $conn->insert_id;
                $message = "تم إضافة المدونة بنجاح!";
                
                // معالجة الصور الإضافية
                processBlogImages($blog_id, $conn);
                
                // معالجة المنتجات المرتبطة
                processBlogProducts($blog_id, $conn);
                
                // إعادة توجيه لمنع إعادة إرسال النموذج
                header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
                exit();
                
            } else {
                $message = "خطأ في إضافة المدونة: " . $conn->error;
            }
        } elseif (isset($_POST['update_blog'])) {
            // تعديل مدونة موجودة
            $blog_id = (int)$_POST['blog_id'];
            
            // جلب الصورة القديمة
            $old_image_result = $conn->query("SELECT main_image FROM blogs WHERE id=$blog_id");
            $old_image = $old_image_result ? $old_image_result->fetch_assoc()['main_image'] : null;
            
            // معالجة تحديث الصورة الرئيسية
            $main_image_sql = "";
            if ($main_image) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }
                $main_image_sql = ", main_image='$main_image'";
            }
            
            $sql = "UPDATE blogs SET 
                    title='$title', 
                    summary='$summary', 
                    content='$content', 
                    category_id=$category_id, 
                    publish_date='$publish_date', 
                    status='$status' 
                    $main_image_sql 
                    WHERE id=$blog_id";
            
            if ($conn->query($sql)) {
                $message = "تم تعديل المدونة بنجاح!";
                
                // معالجة الصور الإضافية
                processBlogImages($blog_id, $conn);
                
                // معالجة المنتجات المرتبطة
                processBlogProducts($blog_id, $conn);
                
                // إعادة توجيه لمنع إعادة إرسال النموذج
                header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
                exit();
                
            } else {
                $message = "خطأ في تعديل المدونة: " . $conn->error;
            }
        }
    } 
    elseif (isset($_POST['delete_blog'])) {
        // حذف مدونة
        $blog_id = (int)$_POST['blog_id'];
        
        // جلب الصورة الرئيسية
        $main_image_result = $conn->query("SELECT main_image FROM blogs WHERE id=$blog_id");
        if ($main_image_result) {
            $main_image = $main_image_result->fetch_assoc()['main_image'];
            if ($main_image && file_exists($main_image)) {
                unlink($main_image);
            }
        }
        
        // حذف الصور الإضافية
        $additional_images = $conn->query("SELECT image_path FROM blog_images WHERE blog_id=$blog_id");
        if ($additional_images) {
            while($image = $additional_images->fetch_assoc()) {
                if ($image['image_path'] && file_exists($image['image_path'])) {
                    unlink($image['image_path']);
                }
            }
        }
        
        // حذف السجلات المرتبطة
        $conn->query("DELETE FROM blog_images WHERE blog_id=$blog_id");
        $conn->query("DELETE FROM blog_products WHERE blog_id=$blog_id");
        
        // حذف المدونة
        $sql = "DELETE FROM blogs WHERE id=$blog_id";
        if ($conn->query($sql)) {
            $message = "تم حذف المدونة بنجاح!";
            header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
            exit();
        } else {
            $message = "خطأ في حذف المدونة: " . $conn->error;
        }
    }
    elseif (isset($_POST['import_blogs'])) {
        // استيراد المدونات من ملف Excel
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
            $import_dir = 'import_files';
            if (!file_exists($import_dir)) {
                mkdir($import_dir, 0777, true);
            }
            
            $file_name = $import_dir . '/' . time() . '_' . basename($_FILES['import_file']['name']);
            if (move_uploaded_file($_FILES['import_file']['tmp_name'], $file_name)) {
                $message = "تم رفع ملف الاستيراد بنجاح! سيتم معالجة البيانات قريباً.";
            } else {
                $message = "خطأ في رفع الملف!";
            }
        } else {
            $message = "يرجى اختيار ملف صالح للاستيراد!";
        }
    }
    elseif (isset($_POST['export_blogs'])) {
        // تصدير المدونات إلى ملف Excel
        exportBlogsToExcel($conn);
    }
}

// دوال مساعدة
function processBlogImages($blog_id, $conn) {
    if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['tmp_name'])) {
        // حذف الصور القديمة أولاً
        $old_images = $conn->query("SELECT image_path FROM blog_images WHERE blog_id=$blog_id");
        if ($old_images) {
            while($image = $old_images->fetch_assoc()) {
                if ($image['image_path'] && file_exists($image['image_path'])) {
                    unlink($image['image_path']);
                }
            }
        }
        $conn->query("DELETE FROM blog_images WHERE blog_id=$blog_id");
        
        foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['additional_images']['error'][$key] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['additional_images']['type'][$key];
                
                if (in_array($file_type, $allowed_types)) {
                    $image_name = 'blog_additional_images/' . time() . '_' . $key . '_' . basename($_FILES['additional_images']['name'][$key]);
                    if (move_uploaded_file($tmp_name, $image_name)) {
                        $sql = "INSERT INTO blog_images (blog_id, image_path, sort_order) 
                                VALUES ($blog_id, '$image_name', $key)";
                        $conn->query($sql);
                    }
                }
            }
        }
    }
}

function processBlogProducts($blog_id, $conn) {
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        // حذف المنتجات القديمة أولاً
        $conn->query("DELETE FROM blog_products WHERE blog_id=$blog_id");
        
        // حساب السعر الإجمالي
        $total_price = 0;
        
        foreach ($_POST['products'] as $key => $product_id) {
            $product_id = (int)$product_id;
            
            // جلب سعر المنتج
            $product_result = $conn->query("SELECT selling_price FROM products WHERE id=$product_id");
            if ($product_result && $product_result->num_rows > 0) {
                $product_data = $product_result->fetch_assoc();
                $product_price = $product_data['selling_price'] ?? 0;
                $total_price += $product_price;
            }
            
            $sql = "INSERT INTO blog_products (blog_id, product_id, sort_order) 
                    VALUES ($blog_id, $product_id, $key)";
            $conn->query($sql);
        }
        
        // تحديث السعر الإجمالي في المدونة
        $conn->query("UPDATE blogs SET total_price=$total_price WHERE id=$blog_id");
    }
}

function exportBlogsToExcel($conn) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="blogs_export_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $blogs = $conn->query("
        SELECT b.*, c.name as category_name,
               (SELECT COUNT(*) FROM blog_products WHERE blog_id = b.id) as products_count
        FROM blogs b 
        LEFT JOIN categories c ON b.category_id = c.id 
        ORDER BY b.created_at DESC
    ");
    
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>العنوان</th>";
    echo "<th>الملخص</th>";
    echo "<th>الفئة</th>";
    echo "<th>تاريخ النشر</th>";
    echo "<th>عدد المنتجات</th>";
    echo "<th>السعر الإجمالي</th>";
    echo "<th>الحالة</th>";
    echo "<th>عدد المشاهدات</th>";
    echo "<th>عدد المشاركات</th>";
    echo "<th>المبيعات</th>";
    echo "<th>تاريخ الإنشاء</th>";
    echo "</tr>";
    
    if ($blogs && $blogs->num_rows > 0) {
        while($blog = $blogs->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $blog['id'] . "</td>";
            echo "<td>" . htmlspecialchars($blog['title']) . "</td>";
            echo "<td>" . htmlspecialchars($blog['summary']) . "</td>";
            echo "<td>" . htmlspecialchars($blog['category_name']) . "</td>";
            echo "<td>" . $blog['publish_date'] . "</td>";
            echo "<td>" . $blog['products_count'] . "</td>";
            echo "<td>" . $blog['total_price'] . "</td>";
            echo "<td>" . $blog['status'] . "</td>";
            echo "<td>" . $blog['views_count'] . "</td>";
            echo "<td>" . $blog['shares_count'] . "</td>";
            echo "<td>" . $blog['sales_from_blog'] . "</td>";
            echo "<td>" . $blog['created_at'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='12'>لا توجد بيانات</td></tr>";
    }
    echo "</table>";
    exit;
}

// جلب البيانات للعرض
// إحصائيات المدونات
$stats_result = $conn->query("SELECT COUNT(*) as total FROM blogs");
$total_blogs = $stats_result ? $stats_result->fetch_assoc()['total'] : 0;

$stats_result = $conn->query("SELECT SUM(views_count) as total FROM blogs");
$total_views = $stats_result ? ($stats_result->fetch_assoc()['total'] ?: 0) : 0;

$stats_result = $conn->query("SELECT SUM(shares_count) as total FROM blogs");
$total_shares = $stats_result ? ($stats_result->fetch_assoc()['total'] ?: 0) : 0;

$stats_result = $conn->query("SELECT SUM(sales_from_blog) as total FROM blogs");
$total_sales = $stats_result ? ($stats_result->fetch_assoc()['total'] ?: 0) : 0;

// جلب المدونات للعرض في الجدول
$where_clause = "";
if ($search) {
    $where_clause = " WHERE b.title LIKE '%$search%' OR b.content LIKE '%$search%' OR c.name LIKE '%$search%'";
}

$blogs_sql = "
    SELECT b.*, c.name as category_name,
           (SELECT COUNT(*) FROM blog_products WHERE blog_id = b.id) as products_count
    FROM blogs b 
    LEFT JOIN categories c ON b.category_id = c.id 
    $where_clause
    ORDER BY b.publish_date DESC 
    LIMIT 10
";

$blogs_result = $conn->query($blogs_sql);

// جلب البيانات للقوائم المنسدلة
$categories_query = $conn->query("SELECT * FROM categories WHERE type = 'blog' OR type = 'product' ORDER BY name");
$categories = [];
if ($categories_query) {
    while($row = $categories_query->fetch_assoc()) {
        $categories[] = $row;
    }
}

$products_query = $conn->query("SELECT id, name, selling_price FROM products WHERE status = 'active' ORDER BY name");
$products = [];
if ($products_query) {
    while($row = $products_query->fetch_assoc()) {
        $products[] = $row;
    }
}

// جلب رسالة من URL إذا وجدت
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المدونات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
      

        <!-- المحتوى الرئيسي -->
           <!-- <div class="dashboard">
         include 'sidebar.php'; ?>

   
        <div class="main-content">
              include 'header.php'; ?> -->
   
    <div class="container">
        <div class="page-content">
            <div class="page-title">
                <a href="admin_dashboard.php"><h2>الصفحه الرئيسيه </h2></a>
                <h2>إدارة المدونات</h2>
                <div class="date"><?php echo date('l، j F Y'); ?></div>
            </div>

            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- إحصائيات سريعة -->
            <div class="stats-cards">
                <div class="stat-card card-1">
                    <div class="stat-icon">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $total_blogs ?></h3>
                        <p>إجمالي المدونات</p>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>5 جديد</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card card-2">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($total_views) ?></h3>
                        <p>مشاهدات المدونات</p>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>12% زيادة</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card card-3">
                    <div class="stat-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($total_shares) ?></h3>
                        <p>مشاركات المدونات</p>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>8% زيادة</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card card-4">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($total_sales) ?></h3>
                        <p>مبيعات من المدونات</p>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>5% زيادة</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- شريط البحث والإجراءات -->
            <div class="page-actions">
                <div class="search-box">
                    <form method="GET" style="display: flex; align-items: center; width: 100%;">
                        <input type="text" name="search" placeholder="ابحث عن مدونة..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" style="display:none"></button>
                        <i class="fas fa-search"></i>
                    </form>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-warning" id="importBlogsBtn">
                        <i class="fas fa-download"></i>
                        استيراد
                    </button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="export_blogs" class="btn btn-success">
                            <i class="fas fa-upload"></i>
                            تصدير
                        </button>
                    </form>
                    <button class="btn btn-secondary" id="filterBtn">
                        <i class="fas fa-filter"></i>
                        تصفية
                    </button>
                    <button class="btn btn-primary" id="addBlogBtn">
                        <i class="fas fa-plus"></i>
                        إضافة مدونة
                    </button>
                </div>
            </div>

            <!-- جدول المدونات -->
            <div class="blogs-container">
                <div class="blogs-header">
                    <h3>قائمة المدونات</h3>
                    <div class="view-toggle">
                        <span>عرض 1-<?= min(10, $blogs_result ? $blogs_result->num_rows : 0) ?> من <?= $total_blogs ?></span>
                    </div>
                </div>

                <table class="blogs-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>العنوان</th>
                            <th>الفئة</th>
                            <th>التاريخ</th>
                            <th>المنتجات</th>
                            <th>السعر الإجمالي</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($blogs_result && $blogs_result->num_rows > 0): ?>
                            <?php while($blog = $blogs_result->fetch_assoc()): ?>
                                <?php
                                // جلب المنتجات المرتبطة بهذه المدونة
                                $blog_products_result = $conn->query("
                                    SELECT p.name 
                                    FROM blog_products bp 
                                    JOIN products p ON bp.product_id = p.id 
                                    WHERE bp.blog_id = " . $blog['id'] . " 
                                    LIMIT 3
                                ");
                                ?>
                                <tr>
                                    <td>
                                        <div class="blog-image">
                                            <?php if ($blog['main_image']): ?>
                                                <img src="<?= htmlspecialchars($blog['main_image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                                            <?php else: ?>
                                                <i class="fas fa-image"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="blog-title"><?= htmlspecialchars($blog['title']) ?></div>
                                    </td>
                                    <td>
                                        <span class="blog-category"><?= htmlspecialchars($blog['category_name']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($blog['publish_date']) ?></td>
                                    <td>
                                        <div class="blog-products">
                                            <?php if ($blog_products_result && $blog_products_result->num_rows > 0): ?>
                                                <?php while($product = $blog_products_result->fetch_assoc()): ?>
                                                    <span class="blog-product"><?= htmlspecialchars($product['name']) ?></span>
                                                <?php endwhile; ?>
                                                <?php if ($blog['products_count'] > 3): ?>
                                                    <span class="blog-product">+<?= $blog['products_count'] - 3 ?> أكثر</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span style="color: #6c757d; font-size: 0.9em;">لا توجد منتجات</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="blog-total-price"><?= number_format($blog['total_price'], 2) ?> ر.س</div>
                                    </td>
                                    <td>
                                        <span class="blog-status status-<?= $blog['status'] ?>">
                                            <?= $blog['status'] == 'published' ? 'منشور' : ($blog['status'] == 'draft' ? 'مسودة' : 'مجدول') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="blog-actions">
                                            <button class="action-btn view" 
                                                    data-blog="<?= $blog['id'] ?>"
                                                    data-title="<?= htmlspecialchars($blog['title']) ?>"
                                                    data-category="<?= htmlspecialchars($blog['category_name']) ?>"
                                                    data-date="<?= $blog['publish_date'] ?>"
                                                    data-content="<?= htmlspecialchars($blog['content']) ?>"
                                                    data-summary="<?= htmlspecialchars($blog['summary']) ?>"
                                                    data-image="<?= htmlspecialchars($blog['main_image']) ?>"
                                                    data-total-price="<?= $blog['total_price'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit" 
                                                    data-blog="<?= $blog['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="action-btn share" 
                                                    data-blog="<?= $blog['id'] ?>"
                                                    data-title="<?= htmlspecialchars($blog['title']) ?>">
                                                <i class="fas fa-share-alt"></i>
                                            </button>
                                            <form method="POST" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف المدونة؟')">
                                                <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                                                <button type="submit" name="delete_blog" class="action-btn delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #6c757d;">
                                    <?= $search ? 'لم يتم العثور على مدونات تطابق بحثك' : 'لا توجد مدونات مضافة بعد' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <button class="active">1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>4</button>
                    <button>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- 
        </div>
           </div> -->
   
    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- نافذة منبثقة لإضافة/تعديل مدونة -->
    <div class="modal-overlay" id="blogModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="blogModalTitle">إضافة مدونة جديدة</h3>
                <button type="button" class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="blogForm">
                    <input type="hidden" name="blog_id" id="blogId">
                    
                    <div class="form-group">
                        <label for="blogTitle">عنوان المدونة *</label>
                        <input type="text" class="form-control" id="blogTitle" name="title" 
                               value="<?= isset($formData['title']) ? htmlspecialchars($formData['title']) : '' ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="blogCategory">فئة المدونة *</label>
                            <select class="form-select" id="blogCategory" name="category_id" required>
                                <option value="">اختر فئة المدونة</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                            <?= (isset($formData['category_id']) && $formData['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="blogDate">تاريخ النشر *</label>
                            <input type="date" class="form-control" id="blogDate" name="publish_date" 
                                   value="<?= isset($formData['publish_date']) ? $formData['publish_date'] : date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="blogSummary">ملخص المدونة</label>
                        <textarea class="form-control" id="blogSummary" name="summary" rows="2"><?= isset($formData['summary']) ? htmlspecialchars($formData['summary']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="blogContent">محتوى المدونة *</label>
                        <textarea class="form-control" id="blogContent" name="content" rows="6" required><?= isset($formData['content']) ? htmlspecialchars($formData['content']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>صورة المدونة الرئيسية</label>
                        <div class="image-upload" id="blogImageUpload" onclick="document.getElementById('mainImage').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>انقر لاختيار صورة</p>
                        </div>
                        <input type="file" id="mainImage" name="main_image" accept="image/*" style="display: none;" onchange="previewMainImage(this)">
                        <div id="imagePreviewContainer"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>صور إضافية للمدونة</label>
                        <div class="image-upload" id="blogImagesUpload" onclick="document.getElementById('additionalImages').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>انقر لاختيار صور إضافية</p>
                        </div>
                        <input type="file" id="additionalImages" name="additional_images[]" multiple accept="image/*" style="display: none;" onchange="previewAdditionalImages(this)">
                        <div class="images-preview" id="blogImagesPreview"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>المنتجات المرتبطة</label>
                        <div class="products-selection">
                            <button type="button" class="btn btn-secondary" id="addProductsBtn">
                                <i class="fas fa-plus"></i> اختيار المنتجات
                            </button>
                        </div>
                        
                        <div class="selected-products" id="selectedProducts">
                            <!-- سيتم إضافة المنتجات المختارة هنا ديناميكياً -->
                        </div>
                        
                        <div class="total-price-calculation">
                            <div class="price-row">
                                <span>السعر الإجمالي للمنتجات:</span>
                                <span id="displayTotalPrice">0 ر.س</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="blogStatus">حالة المدونة *</label>
                        <select class="form-select" id="blogStatus" name="status" required>
                            <option value="draft" <?= (isset($formData['status']) && $formData['status'] == 'draft') ? 'selected' : '' ?>>مسودة</option>
                            <option value="published" <?= (isset($formData['status']) && $formData['status'] == 'published') ? 'selected' : '' ?>>منشور</option>
                            <option value="scheduled" <?= (isset($formData['status']) && $formData['status'] == 'scheduled') ? 'selected' : '' ?>>مجدول</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelBlog">إلغاء</button>
                <button type="submit" class="btn btn-primary" id="saveBlogBtn">حفظ المدونة</button>
            </div>
        </div>
    </div>

    <!-- نافذة اختيار المنتجات -->
    <div class="modal-overlay" id="productsModal">
        <div class="modal products-modal">
            <div class="modal-header">
                <h3>اختيار المنتجات المرتبطة</h3>
                <button type="button" class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="products-selection-header">
                    <div class="search-box">
                        <input type="text" id="productsSearch" placeholder="ابحث عن منتج...">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-primary" id="confirmProducts">
                            تأكيد الاختيار
                        </button>
                    </div>
                </div>
                
                <div class="products-categories" id="productsCategories">
                    <button class="category-btn active" data-category="">الكل</button>
                    <!-- سيتم إضافة الفئات هنا ديناميكياً -->
                </div>
                
                <div class="products-grid" id="productsGrid">
                    <!-- سيتم إضافة المنتجات هنا ديناميكياً -->
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة عرض المدونة -->
    <div class="modal-overlay" id="viewBlogModal">
        <div class="modal blog-detail-modal">
            <div class="modal-header">
                <h3>تفاصيل المدونة</h3>
                <button type="button" class="close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="blog-detail">
                    <div class="blog-gallery">
                        <img src="" alt="المدونة" class="blog-main-image" id="viewMainImage">
                        <div class="blog-thumbnails" id="viewThumbnails"></div>
                    </div>
                    <div class="blog-info">
                        <h2 id="viewBlogTitle">-</h2>
                        <div class="blog-category-badge" id="viewBlogCategory">-</div>
                        <div class="blog-date" id="viewBlogDate">-</div>
                        <div class="blog-content" id="viewBlogContent">لا يوجد محتوى</div>
                        
                        <div class="blog-products-section" id="viewBlogProducts"></div>
                        
                        <div class="social-share">
                            <button type="button" class="facebook" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i>
                                مشاركة على فيسبوك
                            </button>
                            <button type="button" class="twitter" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i>
                                مشاركة على تويتر
                            </button>
                            <button type="button" class="whatsapp" onclick="shareOnWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                                مشاركة على واتساب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeViewBlog">إغلاق</button>
                <button type="button" class="btn btn-primary" id="editViewBlog">
                    <i class="fas fa-edit"></i>
                    تعديل المدونة
                </button>
            </div>
        </div>
    </div>
   
    <script>
        let selectedProducts = [];
        let allProducts = [];

        // فتح نافذة إضافة مدونة
        document.getElementById('addBlogBtn').addEventListener('click', function() {
            document.getElementById('blogModalTitle').textContent = 'إضافة مدونة جديدة';
            document.getElementById('blogForm').reset();
            document.getElementById('blogId').value = '';
            document.getElementById('blogDate').valueAsDate = new Date();
            document.getElementById('imagePreviewContainer').innerHTML = '';
            document.getElementById('blogImagesPreview').innerHTML = '';
            document.getElementById('selectedProducts').innerHTML = '';
            document.getElementById('displayTotalPrice').textContent = '0 ر.س';
            selectedProducts = [];
            document.getElementById('blogModal').style.display = 'flex';
        });

        // فتح نافذة اختيار المنتجات
        document.getElementById('addProductsBtn').addEventListener('click', function() {
            loadProducts();
            document.getElementById('productsModal').style.display = 'flex';
        });

        // فتح نافذة عرض المدونة
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const blogTitle = this.getAttribute('data-title');
                const blogCategory = this.getAttribute('data-category');
                const blogDate = this.getAttribute('data-date');
                const blogContent = this.getAttribute('data-content');
                const blogSummary = this.getAttribute('data-summary');
                const blogImage = this.getAttribute('data-image');
                const totalPrice = this.getAttribute('data-total-price');
                const blogId = this.getAttribute('data-blog');
                
                document.getElementById('viewBlogTitle').textContent = blogTitle;
                document.getElementById('viewBlogCategory').textContent = blogCategory;
                document.getElementById('viewBlogDate').textContent = 'نشر في: ' + formatDate(blogDate);
                document.getElementById('viewBlogContent').innerHTML = blogContent ? '<p>' + blogContent.replace(/\n/g, '</p><p>') + '</p>' : '<p>لا يوجد محتوى</p>';
                
                if (blogImage) {
                    document.getElementById('viewMainImage').src = blogImage;
                } else {
                    document.getElementById('viewMainImage').src = 'https://via.placeholder.com/500x400?text=لا+توجد+صورة';
                }
                
                // جلب المنتجات المرتبطة
                loadBlogProducts(blogId);
                
                document.getElementById('viewBlogModal').style.display = 'flex';
            });
        });

        // فتح نافذة تعديل المدونة
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', async function() {
                const blogId = this.getAttribute('data-blog');
                
                try {
                    const response = await fetch(`get_blog_data.php?id=${blogId}`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const blog = await response.json();
                    
                    if (blog) {
                        document.getElementById('blogModalTitle').textContent = 'تعديل المدونة';
                        document.getElementById('blogId').value = blog.id;
                        document.getElementById('blogTitle').value = blog.title;
                        document.getElementById('blogCategory').value = blog.category_id;
                        document.getElementById('blogDate').value = blog.publish_date;
                        document.getElementById('blogSummary').value = blog.summary;
                        document.getElementById('blogContent').value = blog.content;
                        document.getElementById('blogStatus').value = blog.status;
                        
                        // عرض الصورة الرئيسية إن وجدت
                        if (blog.main_image) {
                            document.getElementById('imagePreviewContainer').innerHTML = `
                                <div style="margin-top: 10px;">
                                    <img src="${blog.main_image}" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                </div>
                            `;
                        }
                        
                        // جلب المنتجات المرتبطة
                        const productsResponse = await fetch(`get_blog_products.php?blog_id=${blogId}`);
                        if (productsResponse.ok) {
                            const products = await productsResponse.json();
                            selectedProducts = products;
                            updateSelectedProductsList();
                            calculateTotalPrice();
                        }
                        
                        document.getElementById('blogModal').style.display = 'flex';
                    }
                } catch (error) {
                    console.error('Error loading blog:', error);
                    alert('حدث خطأ في تحميل بيانات المدونة');
                }
            });
        });

        // إغلاق النوافذ المنبثقة
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.modal-overlay').style.display = 'none';
            });
        });

        document.getElementById('cancelBlog').addEventListener('click', function() {
            document.getElementById('blogModal').style.display = 'none';
        });

        document.getElementById('closeViewBlog').addEventListener('click', function() {
            document.getElementById('viewBlogModal').style.display = 'none';
        });

        // إغلاق النوافذ عند النقر خارجها
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });

        // معاينة الصورة الرئيسية
        function previewMainImage(input) {
            const container = document.getElementById('imagePreviewContainer');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    container.innerHTML = `
                        <div style="margin-top: 10px;">
                            <img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                        </div>
                    `;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // معاينة الصور الإضافية
        function previewAdditionalImages(input) {
            const preview = document.getElementById('blogImagesPreview');
            
            if (input.files) {
                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imagePreview = document.createElement('div');
                        imagePreview.className = 'image-preview-item';
                        imagePreview.innerHTML = `
                            <img src="${e.target.result}" alt="معاينة الصورة">
                            <button type="button" class="remove-image" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        preview.appendChild(imagePreview);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        // تحميل المنتجات للاختيار
        async function loadProducts() {
            try {
                const response = await fetch('get_products.php');
                if (!response.ok) throw new Error('Network response was not ok');
                const products = await response.json();
                
                allProducts = products;
                displayProducts(products);
                updateCategories(products);
            } catch (error) {
                console.error('Error loading products:', error);
                // استخدام بيانات تجريبية في حالة الخطأ
                const sampleProducts = [
                    { id: 1, name: 'هاتف سامسونج جالاكسي S23', selling_price: 3499, category: 'هواتف ذكية' },
                    { id: 2, name: 'سماعات أبل AirPods Pro', selling_price: 899, category: 'إكسسوارات' },
                    { id: 3, name: 'ساعة سامسونج جالاكسي واتش', selling_price: 1299, category: 'إكسسوارات' },
                    { id: 4, name: 'لابتوب ديل XPS 13', selling_price: 5299, category: 'لابتوبات' }
                ];
                
                allProducts = sampleProducts;
                displayProducts(sampleProducts);
                updateCategories(sampleProducts);
            }
        }

        function displayProducts(products) {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = '';
            
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-select-card';
                productCard.innerHTML = `
                    <div class="product-select-image">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="product-select-name">${product.name}</div>
                    <div class="product-select-price">${product.selling_price || product.price} ر.س</div>
                `;
                
                productCard.addEventListener('click', function() {
                    this.classList.toggle('selected');
                    
                    const productId = product.id;
                    if (this.classList.contains('selected')) {
                        if (!selectedProducts.find(p => p.id === productId)) {
                            selectedProducts.push(product);
                        }
                    } else {
                        selectedProducts = selectedProducts.filter(p => p.id !== productId);
                    }
                });
                
                grid.appendChild(productCard);
            });
        }

        function updateCategories(products) {
            const categoriesContainer = document.getElementById('productsCategories');
            const categories = [...new Set(products.map(p => p.category).filter(Boolean))];
            
            categories.forEach(category => {
                const button = document.createElement('button');
                button.className = 'category-btn';
                button.textContent = category;
                button.setAttribute('data-category', category);
                
                button.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const category = this.getAttribute('data-category');
                    const filteredProducts = category ? products.filter(p => p.category === category) : products;
                    displayProducts(filteredProducts);
                });
                
                categoriesContainer.appendChild(button);
            });
        }

        // تأكيد اختيار المنتجات
        document.getElementById('confirmProducts').addEventListener('click', function() {
            updateSelectedProductsList();
            calculateTotalPrice();
            document.getElementById('productsModal').style.display = 'none';
        });

        function updateSelectedProductsList() {
            const container = document.getElementById('selectedProducts');
            container.innerHTML = '';
            
            selectedProducts.forEach((product, index) => {
                const productElement = document.createElement('div');
                productElement.className = 'selected-product';
                productElement.innerHTML = `
                    <div class="selected-product-info">
                        <span>${product.name}</span>
                        <span class="selected-product-price">${product.selling_price || product.price} ر.س</span>
                    </div>
                    <button type="button" class="remove-product" onclick="removeSelectedProduct(${product.id})">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="products[${index}]" value="${product.id}">
                `;
                container.appendChild(productElement);
            });
        }

        function removeSelectedProduct(productId) {
            selectedProducts = selectedProducts.filter(p => p.id !== productId);
            updateSelectedProductsList();
            calculateTotalPrice();
        }

        function calculateTotalPrice() {
            const total = selectedProducts.reduce((sum, product) => sum + (product.selling_price || product.price), 0);
            document.getElementById('displayTotalPrice').textContent = total + ' ر.س';
        }

        // تحميل منتجات المدونة للعرض
        async function loadBlogProducts(blogId) {
            try {
                const response = await fetch(`get_blog_products.php?blog_id=${blogId}`);
                if (response.ok) {
                    const products = await response.json();
                    
                    const container = document.getElementById('viewBlogProducts');
                    if (products.length > 0) {
                        let totalPrice = 0;
                        let productsHTML = `
                            <h4>المنتجات المرتبطة بهذه المدونة</h4>
                            <div class="blog-products-grid" id="viewBlogProductsGrid"></div>
                        `;
                        
                        container.innerHTML = productsHTML;
                        const grid = document.getElementById('viewBlogProductsGrid');
                        
                        products.forEach(product => {
                            totalPrice += parseFloat(product.selling_price) || 0;
                            
                            const productCard = document.createElement('div');
                            productCard.className = 'blog-product-card';
                            productCard.innerHTML = `
                                <div class="blog-product-image">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="blog-product-info">
                                    <h5>${product.name}</h5>
                                    <div class="blog-product-price">${product.selling_price} ر.س</div>
                                </div>
                            `;
                            grid.appendChild(productCard);
                        });
                        
                        container.innerHTML += `
                            <div class="blog-total-price-large">
                                السعر الإجمالي: ${totalPrice.toFixed(2)} ر.س
                            </div>
                        `;
                    } else {
                        container.innerHTML = '<p style="color: #6c757d;">لا توجد منتجات مرتبطة</p>';
                    }
                }
            } catch (error) {
                console.error('Error loading blog products:', error);
                document.getElementById('viewBlogProducts').innerHTML = '<p style="color: #6c757d;">حدث خطأ في تحميل المنتجات</p>';
            }
        }

        // مشاركة المدونة
        function shareOnFacebook() {
            const blogTitle = document.getElementById('viewBlogTitle').textContent;
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('اقرأ هذه المدونة: ' + blogTitle);
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + url + '&quote=' + text, '_blank');
        }

        function shareOnTwitter() {
            const blogTitle = document.getElementById('viewBlogTitle').textContent;
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('اقرأ هذه المدونة: ' + blogTitle);
            window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + url, '_blank');
        }

        function shareOnWhatsApp() {
            const blogTitle = document.getElementById('viewBlogTitle').textContent;
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('اقرأ هذه المدونة: ' + blogTitle + ' ' + url);
            window.open('https://api.whatsapp.com/send?text=' + text, '_blank');
        }

        // تنسيق التاريخ
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ar-SA');
        }

        // البحث في المنتجات
        document.getElementById('productsSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredProducts = allProducts.filter(product => 
                product.name.toLowerCase().includes(searchTerm)
            );
            displayProducts(filteredProducts);
        });

        // حفظ المدونة
        document.getElementById('saveBlogBtn').addEventListener('click', function() {
            const blogForm = document.getElementById('blogForm');
            const blogId = document.getElementById('blogId').value;
            
            // التحقق من صحة النموذج
            const requiredFields = blogForm.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#28a745';
                }
            });
            
            if (isValid) {
                // إضافة زر التعديل أو الإضافة بناءً على الحالة
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = blogId ? 'update_blog' : 'add_blog';
                hiddenInput.value = '1';
                
                blogForm.appendChild(hiddenInput);
                blogForm.submit();
            } else {
                alert('يرجى ملء جميع الحقول المطلوبة');
            }
        });

        // تعيين تاريخ اليوم كافتراضي
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const blogDateInput = document.getElementById('blogDate');
            if (blogDateInput && !blogDateInput.value) {
                blogDateInput.value = today;
            }
        });
    </script>
</body>
</html>