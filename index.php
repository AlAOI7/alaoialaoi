<?php
require_once 'config.php';
if (file_exists('functions.php')) require_once 'functions.php';

// جلب الإعدادات من قاعدة البيانات
$s = function_exists('getSettings') ? getSettings() : [];
$site_name        = $s['site_name']        ?? 'Be Pretty';
$site_logo        = $s['site_logo']        ?? 'img/logo.png';
$background_image = !empty($s['background_image']) ? $s['background_image'] : 'img/4.jpg';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_name) ?> - مرحباً</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Tajawal', sans-serif;
            color: #fff;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.7)), url('<?= htmlspecialchars($background_image) ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            overflow: hidden;
        }
        
        .top-bar {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            padding: 30px;
            z-index: 10;
        }
        
        .login-btn {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .login-btn:hover {
            background-color: #ff3366;
            border-color: #ff3366;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 51, 102, 0.4);
        }
        
        .center-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            text-align: center;
            padding: 40px;
            z-index: 10;
            animation: fadeInUP 1s ease-out forwards;
        }
        
        .logo-circle {
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), inset 0 0 20px rgba(255,255,255,0.2);
            overflow: hidden;
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .logo-circle:hover {
            transform: scale(1.05) rotate(5deg);
        }
        
        .logo-text {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .store-name {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            margin-top: 10px;
            margin-bottom: 15px;
            text-shadow: 0 4px 15px rgba(0,0,0,0.5);
            letter-spacing: 1px;
        }

        .store-desc {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            line-height: 1.6;
            margin-bottom: 40px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        
        .bottom-bar {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 40px;
            z-index: 10;
            animation: fadeInUP 1.2s ease-out forwards;
        }
        
        .enter-btn {
            background: linear-gradient(135deg, #ff3366 0%, #ff6699 100%);
            color: #fff;
            padding: 18px 60px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: bold;
            box-shadow: 0 10px 30px rgba(255, 51, 102, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .enter-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(255, 51, 102, 0.6);
            color: #fff;
        }

        .enter-btn i {
            transition: transform 0.3s ease;
        }

        .enter-btn:hover i {
            transform: translateX(-5px);
        }

        @keyframes fadeInUP {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Particles effect background */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div class="particles" id="particles-js"></div>
    
    <div class="top-bar">
        <a href="login.php" class="login-btn">
            <i class="fas fa-user-circle"></i> تسجيل دخول
        </a>
    </div>

    <div class="center-content">
        <div class="logo-circle">
            <?php if ($site_logo): ?>
                <img src="<?= htmlspecialchars($site_logo) ?>" alt="Logo" class="logo-img">
            <?php else: ?>
                <span class="logo-text"><?= htmlspecialchars($logo_text) ?></span>
            <?php endif; ?>
        </div>
        <h1 class="store-name"><?= htmlspecialchars($site_name) ?></h1>
        <p class="store-desc">الوجهة الأولى لتسوق أفضل وأرقى منتجات العناية والجمال. اكتشفي مجموعتنا الحصرية الآن!</p>
    </div>

    <div class="bottom-bar">
        <a href="home.php" class="enter-btn">
            تسوق الآن <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <script>
        // Simple particles generation
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('particles-js');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                createParticle(container);
            }
        });

        function createParticle(container) {
            const particle = document.createElement('div');
            
            const size = Math.random() * 5 + 2;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.background = 'rgba(255, 255, 255, 0.5)';
            particle.style.position = 'absolute';
            particle.style.borderRadius = '50%';
            
            const startX = Math.random() * window.innerWidth;
            const startY = Math.random() * window.innerHeight;
            particle.style.left = `${startX}px`;
            particle.style.top = `${startY}px`;
            
            const duration = Math.random() * 10 + 10;
            const delay = Math.random() * 5;
            
            particle.style.transition = `all ${duration}s linear ${delay}s`;
            
            container.appendChild(particle);
            
            setTimeout(() => {
                particle.style.top = `-10px`;
                particle.style.opacity = '0';
            }, 100);
            
            setTimeout(() => {
                particle.remove();
                createParticle(container);
            }, (duration + delay) * 1000);
        }
    </script>
</body>

</html>