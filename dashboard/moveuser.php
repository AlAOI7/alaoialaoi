<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل حركات المستخدمين - المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <!-- الشريط الجانبي -->
        <div class="sidebar">
            <div class="logo">
                <h1>المتجر الإلكتروني</h1>
                <p>سجل حركات المستخدمين</p>
            </div>
            <div class="sidebar-menu">
                <div class="menu-item" id="admin-menu">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>لوحة التحكم</span>
                </div>
                <div class="menu-item" id="supplier-menu">
                    <i class="fas fa-user-tie"></i>
                    <span>إدارة الموردين</span>
                </div>
                <div class="menu-item" id="returns-menu">
                    <i class="fas fa-undo-alt"></i>
                    <span>إدارة المرتجعات</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-boxes"></i>
                    <span>إدارة المنتجات</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>العلامات التجارية</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>الفواتير والمبيعات</span>
                </div>
                <div class="menu-item active" id="user-activity-menu">
                    <i class="fas fa-user-clock"></i>
                    <span>سجل المستخدمين</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>الإعدادات</span>
                </div>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <!-- الهيدر -->
            <div class="header">
                <div class="header-left">
                    <button class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-title">
                        <h2 id="page-title">سجل حركات المستخدمين</h2>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">7</span>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">إد</div>
                        <div class="user-info">
                            <div class="user-name">إدارة المتجر</div>
                            <div class="user-role">مسؤول النظام</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- شاشة حركات المستخدمين -->
                <div id="user-activity-view" class="user-activity-view">
                    <div class="page-title">
                        <h2>سجل حركات المستخدمين</h2>
                        <div class="date">الثلاثاء، 15 نوفمبر 2023</div>
                    </div>

                    <!-- بطاقات الإحصائيات -->
                    <div class="stats-cards">
                        <div class="stat-card card-1">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3>156</h3>
                                <p>إجمالي المستخدمين</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>5.2% زيادة</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-2">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3>42</h3>
                                <p>المستخدمين النشطين اليوم</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>12.7% زيادة</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-3">
                            <div class="stat-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="stat-info">
                                <h3>89</h3>
                                <p>الدخول من أجهزة سطح المكتب</p>
                                <div class="stat-trend trend-down">
                                    <i class="fas fa-arrow-down"></i>
                                    <span>3.1% انخفاض</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card card-4">
                            <div class="stat-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3>67</h3>
                                <p>الدخول من الأجهزة المحمولة</p>
                                <div class="stat-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>8.4% زيادة</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إدارة حركات المستخدمين -->
                    <div class="user-activity-management">
                        <div class="user-stats">
                            <h3>إحصائيات سريعة</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-label">إجمالي المستخدمين</span>
                                    <span class="stat-value">156</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">المستخدمين النشطين</span>
                                    <span class="stat-value">42</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الدخول اليوم</span>
                                    <span class="stat-value">124</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">الأجهزة المستخدمة</span>
                                    <span class="stat-value">3 أنواع</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">متوسط النشاط/مستخدم</span>
                                    <span class="stat-value">8.7</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="width: 100%; margin-top: 20px;" id="generate-report-btn">
                                <i class="fas fa-file-export"></i> إنشاء تقرير
                            </button>
                        </div>

                        <div class="user-activity-content">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3>سجل حركات المستخدمين</h3>
                                <div>
                                    <input type="text" class="form-control" placeholder="بحث في السجل..." style="width: 200px;">
                                </div>
                            </div>

                            <!-- الفلاتر -->
                            <div class="filters">
                                <div class="filter-group">
                                    <label for="user-filter">المستخدم</label>
                                    <select id="user-filter" class="filter-control">
                                        <option value="">جميع المستخدمين</option>
                                        <option value="1">أحمد محمد</option>
                                        <option value="2">سارة عبدالله</option>
                                        <option value="3">محمد علي</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="activity-filter">نوع النشاط</label>
                                    <select id="activity-filter" class="filter-control">
                                        <option value="">جميع الأنشطة</option>
                                        <option value="login">تسجيل الدخول</option>
                                        <option value="logout">تسجيل الخروج</option>
                                        <option value="add">إضافة</option>
                                        <option value="edit">تعديل</option>
                                        <option value="delete">حذف</option>
                                        <option value="view">عرض</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="device-filter">نوع الجهاز</label>
                                    <select id="device-filter" class="filter-control">
                                        <option value="">جميع الأجهزة</option>
                                        <option value="desktop">جهاز كمبيوتر</option>
                                        <option value="mobile">هاتف محمول</option>
                                        <option value="tablet">جهاز لوحي</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="date-filter">التاريخ</label>
                                    <input type="date" id="date-filter" class="filter-control">
                                </div>
                                <div class="filter-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary" style="height: 38px;">
                                        <i class="fas fa-filter"></i> تطبيق
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>المستخدم</th>
                                            <th>النشاط</th>
                                            <th>التفاصيل</th>
                                            <th>التاريخ والوقت</th>
                                            <th>الجهاز</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>أحمد محمد</td>
                                            <td><span class="activity-type login">تسجيل دخول</span></td>
                                            <td>تسجيل دخول ناجح إلى النظام</td>
                                            <td>15/11/2023 09:15 ص</td>
                                            <td>
                                                <i class="fas fa-laptop device-icon device-desktop"></i>
                                                كمبيوتر
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="1">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>سارة عبدالله</td>
                                            <td><span class="activity-type add">إضافة</span></td>
                                            <td>إضافة منتج جديد: هاتف ذكي - موديل X1</td>
                                            <td>15/11/2023 10:30 ص</td>
                                            <td>
                                                <i class="fas fa-mobile-alt device-icon device-mobile"></i>
                                                هاتف محمول
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="2">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>محمد علي</td>
                                            <td><span class="activity-type edit">تعديل</span></td>
                                            <td>تعديل بيانات المورد: شركة الأجهزة الحديثة</td>
                                            <td>15/11/2023 11:45 ص</td>
                                            <td>
                                                <i class="fas fa-laptop device-icon device-desktop"></i>
                                                كمبيوتر
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="3">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>فاطمة أحمد</td>
                                            <td><span class="activity-type view">عرض</span></td>
                                            <td>عرض تقرير المبيعات الشهري</td>
                                            <td>15/11/2023 01:20 م</td>
                                            <td>
                                                <i class="fas fa-tablet-alt device-icon device-tablet"></i>
                                                جهاز لوحي
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="4">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>خالد سعيد</td>
                                            <td><span class="activity-type delete">حذف</span></td>
                                            <td>حذف مرتجع: RTN-2023-005</td>
                                            <td>15/11/2023 03:10 م</td>
                                            <td>
                                                <i class="fas fa-laptop device-icon device-desktop"></i>
                                                كمبيوتر
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="5">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>نورة عبدالرحمن</td>
                                            <td><span class="activity-type logout">تسجيل خروج</span></td>
                                            <td>تسجيل خروج من النظام</td>
                                            <td>15/11/2023 04:45 م</td>
                                            <td>
                                                <i class="fas fa-mobile-alt device-icon device-mobile"></i>
                                                هاتف محمول
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" data-id="6">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة عرض التفاصيل المنبثقة -->
    <div class="modal" id="view-activity-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تفاصيل النشاط</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="user-details">
                    <div class="user-info-card">
                        <h4>معلومات المستخدم</h4>
                        <div class="info-item">
                            <span class="info-label">اسم المستخدم:</span>
                            <span class="info-value">أحمد محمد</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">البريد الإلكتروني:</span>
                            <span class="info-value">ahmed@example.com</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">الدور:</span>
                            <span class="info-value">مدير النظام</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">القسم:</span>
                            <span class="info-value">الإدارة</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">آخر نشاط:</span>
                            <span class="info-value">15/11/2023 09:15 ص</span>
                        </div>
                    </div>
                    <div class="user-device-info">
                        <h4>معلومات الجهاز</h4>
                        <div class="info-item">
                            <span class="info-label">نوع الجهاز:</span>
                            <span class="info-value">كمبيوتر</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">نظام التشغيل:</span>
                            <span class="info-value">Windows 11</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">المتصفح:</span>
                            <span class="info-value">Chrome 118</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">عنوان IP:</span>
                            <span class="info-value">192.168.1.105</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">الموقع:</span>
                            <span class="info-value">الرياض، السعودية</span>
                        </div>
                    </div>
                </div>

                <div class="user-info-card" style="margin-top: 20px;">
                    <h4>تفاصيل النشاط</h4>
                    <div class="info-item">
                        <span class="info-label">نوع النشاط:</span>
                        <span class="info-value"><span class="activity-type login">تسجيل دخول</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التاريخ والوقت:</span>
                        <span class="info-value">15/11/2023 09:15 ص</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الحالة:</span>
                        <span class="info-value" style="color: var(--success);">ناجح</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التفاصيل:</span>
                        <span class="info-value">تسجيل دخول ناجح إلى النظام من خلال صفحة تسجيل الدخول الرئيسية</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">المدة:</span>
                        <span class="info-value">4 ساعات و 30 دقيقة</span>
                    </div>
                </div>

                <div class="user-info-card" style="margin-top: 20px;">
                    <h4>النشاطات المرتبطة</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>النشاط</th>
                                    <th>التفاصيل</th>
                                    <th>التاريخ والوقت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="activity-type view">عرض</span></td>
                                    <td>عرض لوحة التحكم الرئيسية</td>
                                    <td>15/11/2023 09:16 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type view">عرض</span></td>
                                    <td>عرض تقرير المبيعات</td>
                                    <td>15/11/2023 09:30 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type edit">تعديل</span></td>
                                    <td>تعديل إعدادات النظام</td>
                                    <td>15/11/2023 10:15 ص</td>
                                </tr>
                                <tr>
                                    <td><span class="activity-type view">عرض</span></td>
                                    <td>عرض قائمة المستخدمين</td>
                                    <td>15/11/2023 11:20 ص</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="print-activity-details">
                    <i class="fas fa-print"></i> طباعة التقرير
                </button>
                <button class="btn btn-danger close-modal">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        // التحكم في الشريط الجانبي
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // التحكم في القوائم
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                // إزالة النشاط من جميع العناصر
                document.querySelectorAll('.menu-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // إضافة النشاط للعنصر المحدد
                this.classList.add('active');
                
                // تحديث عنوان الصفحة
                if (this.id === 'user-activity-menu') {
                    document.getElementById('page-title').textContent = 'سجل حركات المستخدمين';
                }
            });
        });

        // التحكم في النافذة المنبثقة
        const viewActivityModal = document.getElementById('view-activity-modal');

        // فتح نافذة عرض التفاصيل
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                viewActivityModal.classList.add('active');
            });
        });

        // إغلاق النافذة المنبثقة
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                viewActivityModal.classList.remove('active');
            });
        });

        // إنشاء تقرير
        document.getElementById('generate-report-btn').addEventListener('click', function() {
            alert('جاري إنشاء التقرير... سيتم تحميله خلال لحظات.');
            // في التطبيق الحقيقي، سيتم هنا استدعاء دالة إنشاء التقرير
        });

        // طباعة تفاصيل النشاط
        document.getElementById('print-activity-details').addEventListener('click', function() {
            alert('جاري تحضير التقرير للطباعة...');
            // في التطبيق الحقيقي، سيتم هنا استدعاء دالة الطباعة
        });

        // تطبيق الفلاتر
        document.querySelector('.filters .btn-primary').addEventListener('click', function() {
            alert('تم تطبيق الفلاتر بنجاح!');
            // في التطبيق الحقيقي، سيتم هنا تطبيق الفلاتر على البيانات
        });
    </script>
</body>
</html>