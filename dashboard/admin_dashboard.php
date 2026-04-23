<?php
session_start();
require_once '../config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}
    require_once '../config.php';

// التحقق من تسجيل دخول المسؤول
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- زر القائمة للشاشات الصغيرة -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
 
    <!-- لوحة التحكم -->
    <div class="dashboard">
         <?php include 'sidebar.php'; ?>
        <!-- المحتوى الرئيسي -->
        <div class="main-content" id="mainContent">
                     <!-- الهيدر -->
                    <?php include 'header.php'; ?>



            <!-- محتوى الصفحة -->
            <div class="page-content">
                <div class="page-title">
                    <h2>نظرة عامة</h2>
                    <div class="date">الأحد، 15 أكتوبر 2023</div>
                </div>

                <div class="stats-cards">
                    <div class="stat-card card-1">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>1,248</h3>
                            <p>إجمالي الطلبات</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>12.5% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-2">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>5,362</h3>
                            <p>إجمالي العملاء</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>8.3% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-3">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$24,850</h3>
                            <p>إجمالي الإيرادات</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>15.2% زيادة</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card card-4">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3>856</h3>
                            <p>المنتجات</p>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                <span>5.7% زيادة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الشريط الجانبي -->

                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>مبيعات الشهر</h3>
                            <select>
                                <option>آخر 7 أيام</option>
                                <option>آخر 30 يوم</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>مصادر الزيارات</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="trafficChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="mini-charts">
                    <div class="mini-chart-card">
                        <div class="mini-chart-header">
                            <h4>معدل التحويل</h4>
                            <span>4.2%</span>
                        </div>
                        <div class="mini-chart-container">
                            <canvas id="conversionChart"></canvas>
                        </div>
                    </div>
                    <div class="mini-chart-card">
                        <div class="mini-chart-header">
                            <h4>معدل التصفح والشراء</h4>
                            <span>68%</span>
                        </div>
                        <div class="mini-chart-container">
                            <canvas id="browseBuyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="recent-orders">
                    <div class="chart-header">
                        <h3>أحدث الطلبات</h3>
                        <a href="#">عرض الكل</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#ORD-7842</td>
                                <td>أحمد محمد</td>
                                <td>15 أكتوبر 2023</td>
                                <td>$245.99</td>
                                <td><span class="status completed">مكتمل</span></td>
                            </tr>
                            <tr>
                                <td>#ORD-7841</td>
                                <td>سارة عبدالله</td>
                                <td>14 أكتوبر 2023</td>
                                <td>$128.50</td>
                                <td><span class="status pending">قيد الانتظار</span></td>
                            </tr>
                            <tr>
                                <td>#ORD-7840</td>
                                <td>خالد أحمد</td>
                                <td>14 أكتوبر 2023</td>
                                <td>$79.99</td>
                                <td><span class="status completed">مكتمل</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عناصر DOM
            const toggleSidebar = document.getElementById('toggleSidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const menuItems = document.querySelectorAll('.menu-item');
            const userProfileBtn = document.getElementById('userProfileBtn');
            const userProfileMenu = document.getElementById('userProfileMenu');
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationMenu = document.getElementById('notificationMenu');
            const quickAccessBtn = document.getElementById('quickAccessBtn');
            const quickAccessMenu = document.getElementById('quickAccessMenu');
            
            // تبديل الشريط الجانبي - زر الهيدر
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // تبديل الشريط الجانبي - زر الجوال
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // إغلاق القائمة عند النقر خارجها (للشاشات الصغيرة)
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target) && !toggleSidebar.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
            
            // إدارة القوائم الجانبية
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // منع إغلاق القائمة عند النقر على عنصر فرعي
                    if (e.target.classList.contains('submenu-item')) {
                        // إغلاق القائمة على الشاشات الصغيرة بعد النقر
                        if (window.innerWidth <= 992) {
                            setTimeout(() => {
                                sidebar.classList.remove('active');
                            }, 300);
                        }
                        return;
                    }
                    
                    // إذا كانت القائمة تحتوي على قائمة فرعية
                    if (this.querySelector('.submenu')) {
                        // إغلاق جميع القوائم
                        menuItems.forEach(i => {
                            if (i !== this) i.classList.remove('active');
                        });
                        
                        // فتح/إغلاق القائمة الحالية
                        this.classList.toggle('active');
                    } else {
                        // إذا لم تكن تحتوي على قائمة فرعية، إغلاق جميع القوائم
                        menuItems.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                        
                        // إغلاق القائمة على الشاشات الصغيرة بعد النقر
                        if (window.innerWidth <= 992) {
                            setTimeout(() => {
                                sidebar.classList.remove('active');
                            }, 300);
                        }
                    }
                });
            });
            
            // إدارة القوائم المنبثقة
            userProfileBtn.addEventListener('click', function() {
                userProfileMenu.classList.toggle('active');
                notificationMenu.classList.remove('active');
                quickAccessMenu.classList.remove('active');
            });
            
            notificationBtn.addEventListener('click', function() {
                notificationMenu.classList.toggle('active');
                userProfileMenu.classList.remove('active');
                quickAccessMenu.classList.remove('active');
            });
            
            quickAccessBtn.addEventListener('click', function() {
                quickAccessMenu.classList.toggle('active');
                userProfileMenu.classList.remove('active');
                notificationMenu.classList.remove('active');
            });
            
            // إغلاق القوائم المنبثقة عند النقر خارجها
            document.addEventListener('click', function(e) {
                if (!userProfileBtn.contains(e.target) && !userProfileMenu.contains(e.target)) {
                    userProfileMenu.classList.remove('active');
                }
                
                if (!notificationBtn.contains(e.target) && !notificationMenu.contains(e.target)) {
                    notificationMenu.classList.remove('active');
                }
                
                if (!quickAccessBtn.contains(e.target) && !quickAccessMenu.contains(e.target)) {
                    quickAccessMenu.classList.remove('active');
                }
            });

            // إنشاء المخططات الإحصائية
            createCharts();
            
            function createCharts() {
                // مخطط المبيعات
                const salesCtx = document.getElementById('salesChart').getContext('2d');
                const salesChart = new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: ['1 أكتوبر', '5 أكتوبر', '10 أكتوبر', '15 أكتوبر', '20 أكتوبر', '25 أكتوبر', '30 أكتوبر'],
                        datasets: [{
                            label: 'المبيعات',
                            data: [1200, 1900, 1500, 2200, 1800, 2500, 3000],
                            borderColor: '#6C63FF',
                            backgroundColor: 'rgba(108, 99, 255, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // مخطط مصادر الزيارات
                const trafficCtx = document.getElementById('trafficChart').getContext('2d');
                const trafficChart = new Chart(trafficCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['وسائل التواصل', 'البريد الإلكتروني', 'محركات البحث', 'الإحالات', 'مباشر'],
                        datasets: [{
                            data: [30, 20, 25, 15, 10],
                            backgroundColor: [
                                '#6C63FF',
                                '#FF6584',
                                '#36D1DC',
                                '#4ECDC4',
                                '#6A89CC'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true
                            }
                        }
                    }
                });

                // مخطط معدل التحويل
                const conversionCtx = document.getElementById('conversionChart').getContext('2d');
                const conversionChart = new Chart(conversionCtx, {
                    type: 'bar',
                    data: {
                        labels: ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                        datasets: [{
                            label: 'معدل التحويل',
                            data: [3.2, 4.1, 3.8, 4.5, 4.2, 3.9, 5.1],
                            backgroundColor: '#6C63FF',
                            borderWidth: 0,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // مخطط معدل التصفح والشراء
                const browseBuyCtx = document.getElementById('browseBuyChart').getContext('2d');
                const browseBuyChart = new Chart(browseBuyCtx, {
                    type: 'line',
                    data: {
                        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر'],
                        datasets: [{
                            label: 'معدل التصفح',
                            data: [45, 52, 48, 55, 58, 62, 65, 63, 68, 72],
                            borderColor: '#36D1DC',
                            backgroundColor: 'transparent',
                            borderWidth: 3,
                            tension: 0.4
                        }, {
                            label: 'معدل الشراء',
                            data: [30, 35, 38, 42, 45, 48, 52, 55, 58, 62],
                            borderColor: '#FF6584',
                            backgroundColor: 'transparent',
                            borderWidth: 3,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>