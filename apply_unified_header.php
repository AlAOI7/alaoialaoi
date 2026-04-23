<?php
// apply_unified_header.php - سكريبت لتطبيق الهيدر الموحد على صفحة واحدة

// قائمة الصفحات التي سيتم تطبيق الهيدر عليها تلقائياً
$pages = [
    'home.php',
    'categories.php',
    'category-details.php',
    'cart.php',
    'favorites.php',
    'checkout.php',
    'profile.php'
];

echo "<h2>تطبيق الهيدر الموحد على الصفحات</h2>";
echo "<p>الصفحات المستهدفة:</p>";
echo "<ul>";

foreach ($pages as $page) {
    $filepath = __DIR__ . '/' . $page;
    
    if (!file_exists($filepath)) {
        echo "<li><strong>$page</strong>: <span style='color: orange;'>الملف غير موجود</span></li>";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // التحقق مما إذا كان الهيدر موجوداً بالفعل
    if (strpos($content, 'header_unified.php') !== false) {
        echo "<li><strong>$page</strong>: <span style='color: green;'>✓ الهيدر الموحد مطبق بالفعل</span></li>";
        continue;
    }
    
    // استبدال header.php بـ header_unified.php
    $updated = false;
    if (strpos($content, "include 'header.php'") !== false) {
        $content = str_replace("include 'header.php'", "include 'header_unified.php'", $content);
        $updated = true;
    } elseif (strpos($content, 'include "header.php"') !== false) {
        $content = str_replace('include "header.php"', 'include "header_unified.php"', $content);
        $updated = true;
    } elseif (strpos($content, "require 'header.php'") !== false) {
        $content = str_replace("require 'header.php'", "require 'header_unified.php'", $content);
        $updated = true;
    } elseif (strpos($content, 'require "header.php"') !== false) {
        $content = str_replace('require "header.php"', 'require "header_unified.php"', $content);
        $updated = true;
    }
    
    if ($updated) {
        // حفظ التغييرات
        file_put_contents($filepath, $content);
        echo "<li><strong>$page</strong>: <span style='color: green;'>✓ تم التحديث بنجاح</span></li>";
    } else {
        echo "<li><strong>$page</strong>: <span style='color: red;'>✗ لم يتم العثور على header.php للاستبدال</span></li>";
    }
}

echo "</ul>";
echo "<p><strong>الانتهاء!</strong> يمكنك الآن حذف هذا الملف.</p>";
?>
