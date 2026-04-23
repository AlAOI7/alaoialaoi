<?php
require_once 'config/database.php';

// التحقق من الاتصال بقاعدة البيانات
echo "<h3>فحص اتصال قاعدة البيانات</h3>";
if ($conn) {
    echo "✓ تم الاتصال بنجاح<br>";
} else {
    echo "✗ فشل الاتصال: " . mysqli_connect_error() . "<br>";
}

// عرض جميع الفئات
echo "<h3>الفئات المتاحة:</h3>";
$categories_query = "SELECT id, name, status, is_active FROM categories WHERE status = 'active' AND is_active = 1";
$categories_result = mysqli_query($conn, $categories_query);

if (mysqli_num_rows($categories_result) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>الاسم</th><th>الحالة</th><th>مفعل</th></tr>";
    while($cat = mysqli_fetch_assoc($categories_result)) {
        echo "<tr>";
        echo "<td>" . $cat['id'] . "</td>";
        echo "<td>" . $cat['name'] . "</td>";
        echo "<td>" . $cat['status'] . "</td>";
        echo "<td>" . $cat['is_active'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "لا توجد فئات مفعلة<br>";
}

// عرض عدد المنتجات في كل فئة
echo "<h3>عدد المنتجات في كل فئة:</h3>";
$products_by_category_query = "SELECT 
    c.id, 
    c.name as category_name,
    COUNT(p.id) as product_count
FROM categories c
LEFT JOIN products p ON c.id = p.category_id 
    AND p.status = 'active' 
    AND p.is_active = 1
WHERE c.status = 'active' 
    AND c.is_active = 1
GROUP BY c.id, c.name";

$result = mysqli_query($conn, $products_by_category_query);
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID الفئة</th><th>اسم الفئة</th><th>عدد المنتجات</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['category_name'] . "</td>";
        echo "<td>" . $row['product_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// اختبار استعلام المنتجات
echo "<h3>اختبار استعلام المنتجات للفئة 1:</h3>";
$test_query = "SELECT p.*, c.name as category_name 
               FROM products p 
               JOIN categories c ON p.category_id = c.id 
               WHERE p.category_id = 1 
               AND p.status = 'active' 
               AND p.is_active = 1
               LIMIT 5";

$test_result = mysqli_query($conn, $test_query);
if ($test_result && mysqli_num_rows($test_result) > 0) {
    echo "✓ يوجد " . mysqli_num_rows($test_result) . " منتجات في الفئة 1<br>";
    echo "<pre>";
    while($row = mysqli_fetch_assoc($test_result)) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "✗ لا توجد منتجات في الفئة 1<br>";
}

mysqli_close($conn);
?>