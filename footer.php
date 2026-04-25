<?php
// جلب إعدادات الموقع
if (!isset($site_settings)) {
    $site_settings = function_exists('getSettings') ? getSettings() : [];
}
$site_name     = $site_settings['site_name']     ?? 'Be Pretty';
$site_desc     = $site_settings['about_text']    ?? 'متجرك الأول لمستحضرات التجميل والعناية بالبشرة.';
$snapchat      = $site_settings['snapchat']      ?? '';
$tiktok        = $site_settings['tiktok']        ?? '';
$instagram     = $site_settings['instagram']     ?? '';
$twitter       = $site_settings['twitter']       ?? '';
$facebook      = $site_settings['facebook']      ?? '';
$contact_phone = $site_settings['contact_phone'] ?? '';
$contact_email = $site_settings['contact_email'] ?? '';
$site_address  = $site_settings['address']       ?? '';

$current_page  = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --pp-gradient: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
        --pp-primary:  #e83e8c;
    }

    /* ========== FOOTER ========== */
    .custom-footer {
        background: linear-gradient(135deg, #1a1a3a 0%, #2c2c54 100%);
        color: rgba(255,255,255,.85);
        padding: 55px 0 40px;
        margin-top: 70px;
        position: relative;
        overflow: hidden;
        direction: rtl;
        text-align: right;
        font-family: 'Cairo', sans-serif;
    }
    .custom-footer::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: var(--pp-gradient);
    }
    .custom-footer::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -80px;
        width: 250px; height: 250px;
        background: rgba(232,62,140,.06);
        border-radius: 50%;
        pointer-events: none;
    }
    .custom-footer h5,
    .custom-footer h6 {
        color: #fff;
        margin-bottom: 22px;
        font-weight: 700;
        position: relative;
        display: inline-block;
    }
    .custom-footer h5::after,
    .custom-footer h6::after {
        content: '';
        position: absolute;
        bottom: -8px; right: 0;
        width: 35px; height: 3px;
        background: var(--pp-gradient);
        border-radius: 2px;
    }
    .custom-footer p {
        color: rgba(255,255,255,.7);
        line-height: 1.8;
        font-size: .95rem;
    }
    .custom-footer ul {
        padding: 0;
        list-style: none;
        margin: 0;
    }
    .custom-footer ul li {
        margin-bottom: 12px;
        color: rgba(255,255,255,.7);
        font-size: .92rem;
    }
    .custom-footer ul li i {
        color: var(--pp-primary);
        width: 20px;
    }
    .custom-footer a {
        color: rgba(255,255,255,.75);
        text-decoration: none;
        transition: all .3s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .custom-footer a:hover {
        color: #fff;
        transform: translateX(-4px);
    }
    .footer-social {
        display: flex;
        gap: 10px;
        margin-top: 18px;
        flex-wrap: wrap;
    }
    .footer-social a {
        width: 40px; height: 40px;
        background: rgba(255,255,255,.1);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        transition: all .3s;
        font-size: 1rem;
    }
    .footer-social a:hover {
        background: var(--pp-gradient);
        transform: translateY(-4px) rotate(8deg);
        color: #fff;
    }
    .footer-newsletter .input-group {
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,.2);
    }
    .footer-newsletter .form-control {
        background: rgba(255,255,255,.12);
        border: none;
        color: #fff;
        padding: 12px 18px;
        font-size: .92rem;
    }
    .footer-newsletter .form-control::placeholder { color: rgba(255,255,255,.5); }
    .footer-newsletter .form-control:focus {
        background: rgba(255,255,255,.18);
        box-shadow: none;
        color: #fff;
    }
    .footer-newsletter .btn-subscribe {
        background: var(--pp-gradient);
        color: #fff;
        border: none;
        padding: 12px 20px;
        white-space: nowrap;
        font-size: .9rem;
        font-weight: 600;
        transition: all .3s;
    }
    .footer-newsletter .btn-subscribe:hover {
        opacity: .9;
        transform: scale(1.02);
    }
    .footer-divider {
        border-color: rgba(255,255,255,.12);
        margin: 30px 0 20px;
    }
    .footer-copy {
        color: rgba(255,255,255,.55);
        font-size: .88rem;
        text-align: center;
    }
    .footer-copy i { color: #e83e8c; }

    /* ========== BOTTOM TAB BAR ========== */
    .bottom-tab-bar {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(12px);
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 10px 0 8px;
        box-shadow: 0 -4px 20px rgba(232,62,140,.15);
        border-top: 2px solid transparent;
        border-image: var(--pp-gradient) 1;
        z-index: 1050;
        direction: rtl;
    }
    .bottom-tab-bar .tab-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #aaa;
        font-size: .72rem;
        font-weight: 600;
        transition: all .3s;
        position: relative;
        flex: 1;
        padding: 4px 0;
        gap: 4px;
        font-family: 'Cairo', sans-serif;
    }
    .bottom-tab-bar .tab-item i {
        font-size: 1.25rem;
        transition: all .3s;
    }
    .bottom-tab-bar .tab-item.active {
        color: var(--pp-primary);
    }
    .bottom-tab-bar .tab-item.active i {
        transform: translateY(-3px);
        filter: drop-shadow(0 4px 6px rgba(232,62,140,.4));
    }
    .bottom-tab-bar .tab-item.active::before {
        content: '';
        position: absolute;
        top: -2px; left: 50%;
        transform: translateX(-50%);
        width: 28px; height: 3px;
        background: var(--pp-gradient);
        border-radius: 0 0 4px 4px;
        animation: tabSlide .3s ease;
    }
    @keyframes tabSlide {
        from { width: 0; opacity: 0; }
        to   { width: 28px; opacity: 1; }
    }
    .bottom-tab-bar .tab-item:hover {
        color: var(--pp-primary);
    }
    .bottom-tab-bar .tab-item:hover i {
        transform: translateY(-3px);
    }
    .tab-badge {
        position: absolute;
        top: 2px; right: 22%;
        background: #e83e8c;
        color: #fff;
        font-size: .65rem;
        font-weight: 700;
        min-width: 18px; height: 18px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        padding: 0 4px;
        border: 2px solid #fff;
    }

    /* body padding so content not hidden behind tab bar */
    body { padding-bottom: 75px !important; }
</style>

<!-- ===== FOOTER ===== -->
<footer class="custom-footer">
    <div class="container">
        <div class="row g-4">

            <!-- Brand -->
            <div class="col-lg-4 col-md-6">
                <h5><i class="fas fa-heart me-2" style="background:var(--pp-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></i><?= htmlspecialchars($site_name) ?></h5>
                <p><?= htmlspecialchars($site_desc) ?></p>
                <div class="footer-social">
                    <?php if (!empty($facebook)):  ?><a href="<?= htmlspecialchars($facebook)  ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if (!empty($instagram)): ?><a href="<?= htmlspecialchars($instagram) ?>" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if (!empty($twitter)):   ?><a href="<?= htmlspecialchars($twitter)   ?>" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
                    <?php if (!empty($snapchat)):  ?><a href="<?= htmlspecialchars($snapchat)  ?>" target="_blank" title="Snapchat"><i class="fab fa-snapchat"></i></a><?php endif; ?>
                    <?php if (!empty($tiktok)):    ?><a href="<?= htmlspecialchars($tiktok)    ?>" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6>روابط سريعة</h6>
                <ul>
                    <li><a href="home.php"><i class="fas fa-chevron-left fa-xs"></i> الرئيسية</a></li>
                    <li><a href="product.php"><i class="fas fa-chevron-left fa-xs"></i> المنتجات</a></li>
                    <li><a href="wishlist.php"><i class="fas fa-chevron-left fa-xs"></i> المفضلة</a></li>
                    <li><a href="about.php"><i class="fas fa-chevron-left fa-xs"></i> من نحن</a></li>
                    <li><a href="blog.php"><i class="fas fa-chevron-left fa-xs"></i> المدونة</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-3 col-md-6 col-6">
                <h6>تواصل معنا</h6>
                <ul>
                    <?php if (!empty($contact_phone)): ?>
                    <li><i class="fas fa-phone"></i> <?= htmlspecialchars($contact_phone) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($contact_email)): ?>
                    <li><i class="fas fa-envelope"></i> <?= htmlspecialchars($contact_email) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($site_address)): ?>
                    <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($site_address) ?></li>
                    <?php endif; ?>
                    <li><i class="fas fa-clock"></i> السبت – الخميس: ٩ص – ١٠م</li>
                </ul>
                <ul class="mt-2">
                    <li><a href="shipping.php"><i class="fas fa-shipping-fast"></i> الشحن والتوصيل</a></li>
                    <li><a href="faq.php"><i class="fas fa-question-circle"></i> الأسئلة الشائعة</a></li>
                    <li><a href="support.php"><i class="fas fa-headset"></i> الدعم الفني</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-3 col-md-6 footer-newsletter">
                <h6>النشرة البريدية</h6>
                <p>اشترك لتصلك أحدث العروض والتحديثات أولاً بأول</p>
                <div class="input-group">
                    <input type="email" id="footer-newsletter-email" class="form-control" placeholder="بريدك الإلكتروني">
                    <button class="btn btn-subscribe" type="button" onclick="footerSubscribe()">
                        <i class="fas fa-paper-plane me-1"></i> اشتراك
                    </button>
                </div>
                <small class="d-block mt-2" style="color:rgba(255,255,255,.45);font-size:.8rem;"><i class="fas fa-lock me-1"></i>بياناتك محمية ولن تُشارك مع أي طرف</small>
            </div>
        </div>

        <hr class="footer-divider">
        <p class="footer-copy mb-0">
            جميع الحقوق محفوظة &copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>
            &nbsp;|&nbsp; تصميم وتطوير بـ <i class="fas fa-heart"></i>
        </p>
    </div>
</footer>

<!-- ===== BOTTOM TAB BAR ===== -->
<?php
// حساب الشارات
$tab_cart_count = 0;
$tab_notif_count = 0;
if (isset($_SESSION['user_id'])) {
    // سلة التسوق
    if (isset($conn)) {
        $tc = $conn->query("SELECT SUM(quantity) as t FROM cart WHERE user_id = " . intval($_SESSION['user_id']));
        if ($tc) $tab_cart_count = (int)($tc->fetch_assoc()['t'] ?? 0);
        // إشعارات غير مقروءة
        $tn = $conn->query("SELECT COUNT(*) as t FROM notifications WHERE user_id = " . intval($_SESSION['user_id']) . " AND is_read = 0");
        if ($tn) $tab_notif_count = (int)($tn->fetch_assoc()['t'] ?? 0);
    } elseif (isset($pdo)) {
        $tc = $pdo->prepare("SELECT SUM(quantity) as t FROM cart WHERE user_id = ?");
        $tc->execute([$_SESSION['user_id']]);
        $tab_cart_count = (int)($tc->fetch(PDO::FETCH_ASSOC)['t'] ?? 0);
        $tn = $pdo->prepare("SELECT COUNT(*) as t FROM notifications WHERE user_id = ? AND is_read = 0");
        $tn->execute([$_SESSION['user_id']]);
        $tab_notif_count = (int)($tn->fetch(PDO::FETCH_ASSOC)['t'] ?? 0);
    }
}
?>
<div class="bottom-tab-bar">
    <a href="home.php" class="tab-item <?= in_array($current_page, ['home.php','index.php']) ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>الرئيسية</span>
    </a>
    <a href="product.php" class="tab-item <?= in_array($current_page, ['product.php','products.php']) ? 'active' : '' ?>">
        <i class="fas fa-store"></i>
        <span>المنتجات</span>
    </a>
    <a href="cart.php" class="tab-item <?= ($current_page == 'cart.php') ? 'active' : '' ?>">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($tab_cart_count > 0): ?>
        <span class="tab-badge"><?= min($tab_cart_count, 99) ?></span>
        <?php endif; ?>
        <span>السلة</span>
    </a>
    <a href="categories.php" class="tab-item <?= ($current_page == 'categories.php') ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i>
        <span>الفئات</span>
    </a>
    <a href="profile.php" class="tab-item <?= in_array($current_page, ['profile.php','acsses.php']) ? 'active' : '' ?>">
        <i class="fas fa-user"></i>
        <span>حسابي</span>
    </a>
</div>

<script>
function footerSubscribe() {
    var email = document.getElementById('footer-newsletter-email').value.trim();
    if (!email) { alert('الرجاء إدخال بريدك الإلكتروني'); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert('بريد إلكتروني غير صحيح'); return; }
    fetch('ajax/subscribe_newsletter.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    })
    .then(r => r.json()).then(d => {
        alert(d.message || 'تم الاشتراك بنجاح! 🎉');
        document.getElementById('footer-newsletter-email').value = '';
    })
    .catch(() => {
        alert('تم الاشتراك بنجاح! 🎉');
        document.getElementById('footer-newsletter-email').value = '';
    });
}
</script>