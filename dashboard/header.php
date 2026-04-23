<?php
// التحقق من الجلسة في كل صفحة تستخدم الهيدر
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من أن المستخدم مسؤول
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: http://localhost/Storthory-main7/admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - لوحة تحكم المتجر الإلكتروني</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #200d0aff;
            --primary-light: #dedee4ff;
            --primary-dark: #102201ff;
            --secondary: #FF6584;
            --accent: #ffffffff;
            --success: #4ECDC4;
            --warning: #FF9A76;
            --info: #f8f8f8ff;
            --light: #F8F9FD;
            --dark: #2D3748;
            --gray: #718096;
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f2f8 100%);
            color: var(--dark);
            direction: rtl;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* التصميم الرئيسي */
        .dashboard {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* الشريط الجانبي */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            transition: var(--transition);
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .sidebar.collapsed {
            transform: translateX(100%);
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .logo h1 {
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .logo p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            border-right: 4px solid transparent;
            position: relative;
        }

        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-right-color: var(--accent);
        }

        .menu-item i {
            margin-left: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .menu-item span {
            font-size: 14px;
            font-weight: 500;
        }

        .submenu {
            background-color: rgba(0, 0, 0, 0.15);
            display: none;
        }

        .submenu-item {
            padding: 10px 20px 10px 45px;
            font-size: 13px;
            transition: var(--transition);
        }

        .submenu-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .menu-item.active .submenu {
            display: block;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-right: 0;
        }

        /* الهيدر */
        .header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 0 20px;
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--gray);
            margin-left: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .toggle-sidebar:hover {
            background-color: var(--light);
            color: var(--primary);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .header-icon {
            position: relative;
            margin-left: 15px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .header-icon:hover {
            background-color: var(--light);
        }

        .header-icon i {
            font-size: 18px;
            color: var(--gray);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: linear-gradient(135deg, var(--secondary), var(--warning));
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
            transition: var(--transition);
        }

        .user-profile:hover {
            background-color: var(--light);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            margin-left: 10px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 11px;
            color: var(--gray);
        }

        /* محتوى الصفحة */
        .page-content {
            flex: 1;
            padding: 20px;
        }

        .page-title {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 24px;
            color: var(--dark);
            font-weight: 700;
        }

        .page-title .date {
            color: var(--gray);
            font-size: 14px;
        }

        .page-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            background-color: white;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: var(--radius);
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 5px 15px rgba(21, 21, 22, 0.3);
        }

        .btn-secondary {
            background-color: white;
            color: var(--dark);
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: var(--light);
            border-color: var(--primary);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #2BBBAD);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #2BBBAD, var(--success));
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #FF8A65);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #FF8A65, var(--warning));
            box-shadow: 0 5px 15px rgba(255, 154, 118, 0.3);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 15px;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-info {
            flex: 1;
        }

        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 5px;
        }

        .stat-trend {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .trend-up {
            color: var(--success);
        }

        .trend-down {
            color: var(--secondary);
        }

        .card-1 .stat-icon {
            background: linear-gradient(135deg, #6C63FF, #8A84FF);
            color: white;
        }

        .card-2 .stat-icon {
            background: linear-gradient(135deg, #FF6584, #FF9A76);
            color: white;
        }

        .card-3 .stat-icon {
            background: linear-gradient(135deg, #36D1DC, #4ECDC4);
            color: white;
        }

        .card-4 .stat-icon {
            background: linear-gradient(135deg, #6A89CC, #82CCDD);
            color: white;
        }

        /* جدول المنتجات */
        .products-container {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
        }

        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .products-header h3 {
            font-size: 18px;
            color: var(--dark);
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th, .products-table td {
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #f0f0f0;
        }

        .products-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .products-table td {
            font-size: 13px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
        }

        .product-title {
            font-weight: 500;
            color: var(--dark);
        }

        .product-category {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            background-color: rgba(108, 99, 255, 0.15);
            color: var(--primary);
        }

        .product-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .product-status.active {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
        }

        .product-status.inactive {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .product-status.low-stock {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .product-price {
            font-weight: 600;
            color: var(--dark);
        }

        .product-price .old-price {
            text-decoration: line-through;
            color: var(--gray);
            font-size: 12px;
            margin-left: 5px;
        }

        .product-tags {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .product-tag {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .product-tag.featured {
            background-color: rgba(255, 154, 118, 0.15);
            color: var(--warning);
        }

        .product-tag.popular {
            background-color: rgba(255, 101, 132, 0.15);
            color: var(--secondary);
        }

        .product-tag.new {
            background-color: rgba(78, 205, 196, 0.15);
            color: var(--success);
        }

        .product-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #e2e8f0;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background-color: var(--light);
        }

        .action-btn.view:hover {
            color: var(--info);
            border-color: var(--info);
        }

        .action-btn.edit:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        .action-btn.delete:hover {
            color: var(--secondary);
            border-color: var(--secondary);
        }

        .action-btn.share:hover {
            color: var(--success);
            border-color: var(--success);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 8px;
        }

        .pagination button {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #e2e8f0;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
            font-size: 13px;
        }

        .pagination button.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination button:hover:not(.active) {
            background-color: var(--light);
            border-color: var(--primary);
        }

        /* النوافذ المنبثقة */
        .modal-overlay {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: var(--radius);
            width: 90%;
            max-width: 800px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            transition: var(--transition);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 18px;
            color: var(--dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--gray);
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
        }

        .close-modal:hover {
            background-color: var(--light);
            color: var(--dark);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-check input {
            margin-left: 10px;
        }

        .form-check label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .image-upload {
            border: 2px dashed #e2e8f0;
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .image-upload:hover {
            border-color: var(--primary);
        }

        .image-upload i {
            font-size: 30px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .image-upload p {
            font-size: 14px;
            color: var(--gray);
        }

        .image-upload span {
            color: var(--primary);
            font-weight: 500;
        }

        .image-preview {
            width: 100%;
            height: 150px;
            border-radius: var(--radius);
            object-fit: cover;
            display: none;
            margin-top: 15px;
        }

        .images-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .image-preview-item {
            position: relative;
            width: 100%;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            left: 5px;
            background-color: rgba(255, 101, 132, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 12px;
        }

        .sizes-colors-container {
            margin-top: 15px;
        }

        .size-color-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .size-color-item input {
            flex: 1;
        }

        .size-color-item .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .add-more-btn {
            background: none;
            border: 1px dashed #e2e8f0;
            padding: 10px 15px;
            border-radius: var(--radius);
            color: var(--primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            margin-top: 10px;
        }

        .add-more-btn:hover {
            background-color: rgba(108, 99, 255, 0.05);
            border-color: var(--primary);
        }

        .price-calculation {
            background-color: var(--light);
            border-radius: var(--radius);
            padding: 15px;
            margin-top: 10px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .price-row.total {
            font-weight: 600;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
        }

        .barcode-container {
            display: flex;
            gap: 10px;
        }

        .barcode-container input {
            flex: 1;
        }

        .barcode-container button {
            padding: 0 15px;
            background-color: var(--light);
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .barcode-container button:hover {
            background-color: #e2e8f0;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-control button {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--light);
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: var(--transition);
        }

        .quantity-control button:hover {
            background-color: #e2e8f0;
        }

        .quantity-control input {
            width: 60px;
            text-align: center;
        }

        .video-preview {
            width: 100%;
            height: 200px;
            border-radius: var(--radius);
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--gray);
            margin-top: 15px;
        }

        .social-share {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .social-share button {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .social-share button:hover {
            background-color: var(--light);
        }

        .social-share button.facebook {
            color: #1877F2;
        }

        .social-share button.twitter {
            color: #1DA1F2;
        }

        .social-share button.whatsapp {
            color: #25D366;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* عرض المنتج */
        .product-detail-modal {
            max-width: 1000px;
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .product-gallery {
            position: relative;
        }

        .product-main-image {
            width: 100%;
            height: 300px;
            border-radius: var(--radius);
            object-fit: cover;
            margin-bottom: 15px;
        }

        .product-thumbnails {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .product-thumbnail {
            width: 100%;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .product-thumbnail.active {
            border-color: var(--primary);
        }

        .product-thumbnail:hover {
            border-color: var(--primary-light);
        }

        .product-info h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .product-category-badge {
            display: inline-block;
            padding: 5px 12px;
            background-color: rgba(108, 99, 255, 0.1);
            color: var(--primary);
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .product-price-large {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .product-price-large .old-price {
            font-size: 18px;
            text-decoration: line-through;
            color: var(--gray);
            margin-right: 10px;
        }

        .product-description {
            margin: 20px 0;
            line-height: 1.6;
            color: var(--gray);
        }

        .product-specs {
            margin: 20px 0;
        }

        .product-specs h4 {
            margin-bottom: 10px;
            color: var(--dark);
        }

        .specs-list {
            list-style: none;
        }

        .specs-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
        }

        .specs-list li:last-child {
            border-bottom: none;
        }

        .specs-list .spec-name {
            font-weight: 500;
            width: 120px;
            color: var(--dark);
        }

        .specs-list .spec-value {
            flex: 1;
            color: var(--gray);
        }

        /* زر القائمة للشاشات الصغيرة */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
            z-index: 1001;
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
        }

        /* التجاوب */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .mobile-menu-btn {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .header {
                padding: 0 15px;
            }
            
            .user-info {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .page-content {
                padding: 15px;
            }
            
            .page-actions {
                flex-direction: column;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
            
            .header-right {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            
            .header-icon {
                margin-left: 10px;
            }
            
            .products-table {
                display: block;
                overflow-x: auto;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .product-detail {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 15px;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
                margin-left: 10px;
            }
            
            .stat-info h3 {
                font-size: 20px;
            }
            
            .modal {
                width: 95%;
            }
        }

        @media (max-width: 400px) {
            .header {
                padding: 0 10px;
            }
            
            .page-content {
                padding: 10px;
            }
            
            .products-container {
                padding: 15px;
            }
            
            .modal-body, .modal-header, .modal-footer {
                padding: 15px;
            }
        }
    </style>
    </head>
<body>
<!-- الهيدر -->
<div class="header">
    <div class="header-left">
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h2>لوحة التحكم</h2>
    </div>
    <div class="header-right">
        <div class="header-icon" id="quickAccessBtn">
            <i class="fas fa-th"></i>
            <span class="notification-badge">9</span>
            <div class="dropdown-menu" id="quickAccessMenu">
                <div class="dropdown-header">
                    <h3>الوصول السريع</h3>
                </div>
                <div class="quick-access-grid">
                    <a href="orders.php" class="quick-access-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>الطلبات</span>
                    </a>
                    <a href="payments.php" class="quick-access-item">
                        <i class="fas fa-credit-card"></i>
                        <span>الدفع</span>
                    </a>
                    <a href="products.php" class="quick-access-item">
                        <i class="fas fa-box"></i>
                        <span>المنتج</span>
                    </a>
                    <a href="categories.php" class="quick-access-item">
                        <i class="fas fa-tags"></i>
                        <span>الفئات</span>
                    </a>
                    <a href="sales.php" class="quick-access-item">
                        <i class="fas fa-chart-line"></i>
                        <span>المبيعات</span>
                    </a>
                    <a href="purchases.php" class="quick-access-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>المشتريات</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="header-icon" id="notificationBtn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">5</span>
            <div class="dropdown-menu" id="notificationMenu">
                <div class="dropdown-header">
                    <h3>الإشعارات</h3>
                    <span class="mark-all-read">تحديد الكل</span>
                </div>
                <a href="order_details.php?id=7842" class="dropdown-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>طلب جديد #7842</span>
                </a>
                <a href="inventory.php" class="dropdown-item">
                    <i class="fas fa-box"></i>
                    <span>منتج على وشك النفاد</span>
                </a>
                <a href="users.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>عميل جديد مسجل</span>
                </a>
                <a href="reports.php" class="dropdown-item">
                    <i class="fas fa-chart-line"></i>
                    <span>تقرير المبيعات جاهز</span>
                </a>
                <a href="products.php" class="dropdown-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>منتج يحتاج مراجعة</span>
                </a>
            </div>
        </div>
        
        <div class="user-profile" id="userProfileBtn">
            <div class="user-avatar"><?php echo mb_substr($_SESSION['admin_name'], 0, 1, 'UTF-8'); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['admin_name']; ?></div>
                <div class="user-role">مدير النظام</div>
            </div>
            <div class="dropdown-menu" id="userProfileMenu">
                <div class="dropdown-header">
                    <h3>الملف الشخصي</h3>
                </div>
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>الملف الشخصي</span>
                </a>
                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>الإعدادات</span>
                </a>
                <a href="activity_log.php" class="dropdown-item">
                    <i class="fas fa-history"></i>
                    <span>سجل النشاط</span>
                </a>
                <div class="dropdown-item" id="darkModeToggle">
                    <i class="fas fa-moon"></i>
                    <span>الوضع الليلي</span>
                </div>
                     <a href="logout.php" style="color: var(--danger); text-decoration: none;">
                                    <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                                </a>
          
            </div>
        </div>
    </div>
</div>

<script>
// دالة عامة للهيدر
document.addEventListener('DOMContentLoaded', function() {
    // تبديل السايدبار
    const toggleSidebar = document.getElementById('toggleSidebar');
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            // حفظ الحالة في localStorage
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
        });
    }

    // تحميل حالة السايدبار من localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    // إدارة القوائم المنسدلة
    const dropdownTriggers = document.querySelectorAll('.header-icon, .user-profile');
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // إغلاق جميع القوائم المنسدلة الأخرى
            dropdownTriggers.forEach(otherTrigger => {
                if (otherTrigger !== trigger) {
                    const otherMenu = otherTrigger.querySelector('.dropdown-menu');
                    if (otherMenu) {
                        otherMenu.style.opacity = '0';
                        otherMenu.style.visibility = 'hidden';
                        otherMenu.style.transform = 'translateY(10px)';
                    }
                }
            });

            // تبديل القائمة الحالية
            const menu = this.querySelector('.dropdown-menu');
            if (menu) {
                const isVisible = menu.style.visibility === 'visible';
                menu.style.opacity = isVisible ? '0' : '1';
                menu.style.visibility = isVisible ? 'hidden' : 'visible';
                menu.style.transform = isVisible ? 'translateY(10px)' : 'translateY(0)';
            }
        });
    });

    // إغلاق القوائم عند النقر خارجها
    document.addEventListener('click', function() {
        dropdownTriggers.forEach(trigger => {
            const menu = trigger.querySelector('.dropdown-menu');
            if (menu) {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                menu.style.transform = 'translateY(10px)';
            }
        });
    });

    // تحديد كل الإشعارات كمقروءة
    const markAllRead = document.querySelector('.mark-all-read');
    if (markAllRead) {
        markAllRead.addEventListener('click', function(e) {
            e.stopPropagation();
            const badge = document.querySelector('#notificationBtn .notification-badge');
            if (badge) {
                badge.textContent = '0';
                badge.style.display = 'none';
            }
        });
    }

    // الوضع الليلي
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
    }

    // تحميل الوضع الليلي من localStorage
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
