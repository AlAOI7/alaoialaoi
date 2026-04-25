<?php
// dashboard/pricing.php - لوحة التحكم: إدارة العملات وأسعار الصرف
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

$message = '';
$error = '';

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // إضافة / تعديل عملة
    if (isset($_POST['save_currency'])) {
        $id     = (int)($_POST['id'] ?? 0);
        $code   = strtoupper(trim($_POST['code']));
        $name   = trim($_POST['name']);
        $symbol = trim($_POST['symbol']);
        $rate   = floatval($_POST['exchange_rate']);
        $is_def = isset($_POST['is_default']) ? 1 : 0;
        $status = $_POST['status'];

        if ($is_def) {
            $conn->query("UPDATE currencies SET is_default = 0");
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE currencies SET code=?, name=?, symbol=?, exchange_rate=?, is_default=?, status=? WHERE id=?");
            $stmt->bind_param("sssdssi", $code, $name, $symbol, $rate, $is_def, $status, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO currencies (code, name, symbol, exchange_rate, is_default, status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("sssdis", $code, $name, $symbol, $rate, $is_def, $status);
        }

        if ($stmt->execute()) {
            $message = '✅ تم حفظ العملة بنجاح';
            // تحديث أسعار المنتجات بناءً على السعر الأساسي
            updateProductPrices($conn, $code, $rate);
        } else {
            $error = 'خطأ: ' . $stmt->error;
        }
    }

    // حذف عملة
    if (isset($_POST['delete_currency'])) {
        $id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM currencies WHERE id = $id AND is_default = 0");
        $message = 'تم الحذف';
    }
}

// دالة تحديث أسعار المنتجات
function updateProductPrices($conn, $currency_code, $exchange_rate) {
    // جلب العملة الافتراضية (SAR)
    $default = $conn->query("SELECT exchange_rate FROM currencies WHERE is_default = 1 LIMIT 1")->fetch_assoc();
    if (!$default) return;

    // تحديث سعر كل منتج بالعملة المحددة
    $col_map = [
        'USD'     => 'price_usd',
        'SAR'     => 'price_sar',
        'YER'     => 'price_yer_new',
        'YER_OLD' => 'price_yer_old',
    ];

    if (isset($col_map[$currency_code])) {
        $col = $col_map[$currency_code];
        $conn->query("UPDATE products SET $col = ROUND(selling_price * $exchange_rate, 2) WHERE selling_price > 0");
    }
}

// جلب العملات
$currencies = $conn->query("SELECT * FROM currencies ORDER BY is_default DESC, code")->fetch_all(MYSQLI_ASSOC);

// عملة محددة للتعديل
$edit_currency = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM currencies WHERE id = $eid");
    $edit_currency = $res ? $res->fetch_assoc() : null;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العملات | لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .rate-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .default-badge {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .currency-symbol {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="content-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="main-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave me-2 text-primary"></i>إدارة العملات وأسعار الصرف</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#currencyModal">
                <i class="fas fa-plus me-2"></i>إضافة عملة
            </button>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <!-- بطاقات العملات -->
        <div class="row g-4 mb-4">
            <?php foreach ($currencies as $curr): ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="currency-symbol mb-2"><?= htmlspecialchars($curr['symbol']) ?></div>
                        <h5 class="fw-bold"><?= htmlspecialchars($curr['code']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($curr['name']) ?></p>
                        <span class="rate-badge">× <?= number_format($curr['exchange_rate'], 4) ?></span>
                        <?php if ($curr['is_default']): ?>
                            <div class="mt-2"><span class="default-badge">العملة الافتراضية</span></div>
                        <?php endif; ?>
                        <div class="mt-3 d-flex gap-2 justify-content-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="editCurrency(<?= htmlspecialchars(json_encode($curr)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if (!$curr['is_default']): ?>
                            <form method="POST" onsubmit="return confirm('حذف العملة?')">
                                <input type="hidden" name="delete_id" value="<?= $curr['id'] ?>">
                                <button type="submit" name="delete_currency" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-center small">
                        <span class="badge bg-<?= $curr['status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= $curr['status'] === 'active' ? 'نشطة' : 'معطلة' ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- جدول العملات -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-table me-2"></i>جدول العملات الكامل
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الرمز</th>
                            <th>سعر الصرف (مقابل الريال السعودي)</th>
                            <th>الحالة</th>
                            <th>آخر تحديث</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currencies as $curr): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($curr['code']) ?></strong>
                                <?php if ($curr['is_default']): ?><span class="ms-1 badge bg-success">افتراضية</span><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($curr['name']) ?></td>
                            <td class="text-primary fw-bold"><?= htmlspecialchars($curr['symbol']) ?></td>
                            <td><span class="rate-badge">1 SAR = <?= number_format($curr['exchange_rate'], 4) ?> <?= $curr['code'] ?></span></td>
                            <td>
                                <span class="badge bg-<?= $curr['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $curr['status'] === 'active' ? '✅ نشطة' : '❌ معطلة' ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('Y/m/d', strtotime($curr['updated_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editCurrency(<?= htmlspecialchars(json_encode($curr)) ?>)">
                                    <i class="fas fa-edit"></i> تعديل
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="updateAllPrices('<?= $curr['code'] ?>')">
                                    <i class="fas fa-sync"></i> تحديث الأسعار
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- تعليمات -->
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-info-circle me-2"></i>كيفية عمل نظام العملات:</h6>
            <ul class="mb-0">
                <li>الأسعار في قاعدة البيانات محفوظة بالريال السعودي (العملة الأساسية)</li>
                <li>عند اختيار عملة أخرى، يُضرب السعر × سعر الصرف تلقائياً</li>
                <li>يمكن للمستخدم اختيار العملة من أيقونة 💰 في الهيدر</li>
                <li>عند الضغط على "تحديث الأسعار" يتم حفظ الأسعار المحسوبة في جدول المنتجات</li>
            </ul>
        </div>
    </div>
</div>

<!-- مودال إضافة/تعديل عملة -->
<div class="modal fade" id="currencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">إضافة عملة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="curr_id" value="0">
                    <div class="mb-3">
                        <label class="form-label">كود العملة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" id="curr_code" placeholder="مثال: SAR, USD, YER" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم العملة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="curr_name" placeholder="مثال: ريال سعودي" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رمز العملة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="symbol" id="curr_symbol" placeholder="مثال: ر.س" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سعر الصرف (مقابل 1 ريال سعودي) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="exchange_rate" id="curr_rate" step="0.0001" min="0.0001" placeholder="مثال: 1 للريال، 0.2667 للدولار" required>
                        <div class="form-text">إذا كان 1 ريال = 66.75 ريال يمني جديد، اكتب 66.75</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" name="status" id="curr_status">
                            <option value="active">نشطة</option>
                            <option value="inactive">معطلة</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" id="curr_default">
                        <label class="form-check-label" for="curr_default">جعلها العملة الافتراضية</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" name="save_currency" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editCurrency(curr) {
    document.getElementById('modalTitle').textContent = 'تعديل عملة: ' + curr.code;
    document.getElementById('curr_id').value = curr.id;
    document.getElementById('curr_code').value = curr.code;
    document.getElementById('curr_name').value = curr.name;
    document.getElementById('curr_symbol').value = curr.symbol;
    document.getElementById('curr_rate').value = curr.exchange_rate;
    document.getElementById('curr_status').value = curr.status;
    document.getElementById('curr_default').checked = curr.is_default == 1;
    new bootstrap.Modal(document.getElementById('currencyModal')).show();
}

function updateAllPrices(code) {
    if (!confirm(`تحديث أسعار جميع المنتجات بعملة ${code}?`)) return;
    
    fetch('ajax/update_product_prices.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'currency_code=' + code
    })
    .then(r => r.json())
    .then(data => {
        alert(data.success ? `✅ تم تحديث أسعار ${data.count} منتج` : '❌ ' + data.message);
    });
}
</script>
</body>
</html>
