<?php
// export_products.php
require_once 'config.php';

$type = $_GET['type'] ?? 'all';
$format = $_GET['format'] ?? 'excel';
$category_id = $_GET['category'] ?? null;
$brand_id = $_GET['brand'] ?? null;

// بناء استعلام SQL
$query = "
    SELECT p.*, 
           c.name as category_name,
           b.name as brand_name,
           cur.code as currency_code,
           GROUP_CONCAT(DISTINCT pi.image_path SEPARATOR ',') as images,
           GROUP_CONCAT(DISTINCT CONCAT(pc.color_name, ':', pc.color_code) SEPARATOR ';') as colors,
           GROUP_CONCAT(DISTINCT CONCAT(ps.size, ',', ps.length, ',', ps.width) SEPARATOR ';') as sizes
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN currencies cur ON p.currency_id = cur.id
    LEFT JOIN product_images pi ON p.id = pi.product_id
    LEFT JOIN product_colors pc ON p.id = pc.product_id
    LEFT JOIN product_sizes ps ON p.id = ps.product_id
    WHERE 1=1
";

if ($type === 'active') {
    $query .= " AND p.status = 'active'";
} elseif ($type === 'inactive') {
    $query .= " AND p.status = 'inactive'";
}

if ($category_id) {
    $query .= " AND p.category_id = :category_id";
}

if ($brand_id) {
    $query .= " AND p.brand_id = :brand_id";
}

$query .= " GROUP BY p.id ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);

if ($category_id) {
    $stmt->bindParam(':category_id', $category_id);
}

if ($brand_id) {
    $stmt->bindParam(':brand_id', $brand_id);
}

$stmt->execute();
$products = $stmt->fetchAll();

// تصدير حسب التنسيق المطلوب
switch ($format) {
    case 'excel':
        exportToExcel($products);
        break;
    case 'csv':
        exportToCSV($products);
        break;
    case 'json':
        exportToJSON($products);
        break;
}

function exportToExcel($products) {
    require_once 'vendor/autoload.php';
    
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // العناوين
    $headers = [
        'ID', 'اسم المنتج', 'الوصف', 'الفئة', 'العلامة التجارية',
        'السعر الأساسي', 'سعر البيع', 'السعر القديم', 'الكمية',
        'الباركود', 'تاريخ الانتهاء', 'الحالة', 'الصور', 'الألوان', 'المقاسات'
    ];
    
    foreach ($headers as $index => $header) {
        $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
    }
    
    // البيانات
    $row = 2;
    foreach ($products as $product) {
        $sheet->setCellValue('A' . $row, $product['id']);
        $sheet->setCellValue('B' . $row, $product['name']);
        $sheet->setCellValue('C' . $row, $product['description']);
        $sheet->setCellValue('D' . $row, $product['category_name']);
        $sheet->setCellValue('E' . $row, $product['brand_name']);
        $sheet->setCellValue('F' . $row, $product['base_price']);
        $sheet->setCellValue('G' . $row, $product['selling_price']);
        $sheet->setCellValue('H' . $row, $product['old_price']);
        $sheet->setCellValue('I' . $row, $product['quantity']);
        $sheet->setCellValue('J' . $row, $product['barcode']);
        $sheet->setCellValue('K' . $row, $product['expiry_date']);
        $sheet->setCellValue('L' . $row, $product['status']);
        $sheet->setCellValue('M' . $row, $product['images']);
        $sheet->setCellValue('N' . $row, $product['colors']);
        $sheet->setCellValue('O' . $row, $product['sizes']);
        $row++;
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="products_export.xlsx"');
    
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
}

function exportToCSV($products) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="products_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'ID', 'اسم المنتج', 'الفئة', 'العلامة التجارية',
        'السعر الأساسي', 'سعر البيع', 'الكمية', 'الحالة'
    ]);
    
    foreach ($products as $product) {
        fputcsv($output, [
            $product['id'],
            $product['name'],
            $product['category_name'],
            $product['brand_name'],
            $product['base_price'],
            $product['selling_price'],
            $product['quantity'],
            $product['status']
        ]);
    }
    
    fclose($output);
}

function exportToJSON($products) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="products_export.json"');
    
    echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>