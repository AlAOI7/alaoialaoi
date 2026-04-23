<?php
// session_start();

// // تحويل جميع المستخدمين مباشرة إلى home.php
// // بغض النظر عن حالة تسجيل الدخول
// header('Location: home.php');
// exit();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Be Pretty - مرحباً</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            color: #333;
            /* New Background Styles */
            background-image: url('img/4.jpg');
            /* Replace with your image path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            /* Optional: keeps image static while scrolling */
        }
        
        .top-bar {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            padding: 20px;
        }
        
        .login-btn {
            background-color: #fff;
            color: #ff3366;
            border: 1px solid #ff3366;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .login-btn:hover {
            background-color: #ff3366;
            color: #fff;
        }
        
        .center-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            text-align: center;
            /* Add a semi-transparent background for better readability */
            padding: 30px;
            border-radius: 20px;
        }
        
        .logo-circle {
            width: 150px;
            height: 150px;
            background-color: #fff;
            border-radius: 50%;
            border: 5px solid #ff3366;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo-text {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff3366;
            margin: 0;
        }
        
        .store-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            margin-top: 10px;
        }
        
        .bottom-bar {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        
        .enter-btn {
            background-color: #ff3366;
            color: #fff;
            padding: 15px 50px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .enter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <a href="login.php" class="login-btn">تسجيل دخول</a>
    </div>

    <div class="center-content">
        <div class="logo-circle">
            <span class="logo-text">BP</span>
        </div>
        <h1 class="store-name">Be Pretty</h1>
    </div>

    <div class="bottom-bar">
        <a href="home.php" class="enter-btn">ابدأ الآن</a>
    </div>
</body>

</html>