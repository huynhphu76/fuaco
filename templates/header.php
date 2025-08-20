<?php
// Tệp: /templates/header.php (Phiên bản cuối cùng, tích hợp đầy đủ và chính xác)
global $pdo, $language_code, $params;

// --- Mảng dịch cho header ---
$header_translations = [
    'vi' => ['my_account' => 'Tài khoản của tôi', 'logout' => 'Đăng xuất', 'login_register' => 'Đăng nhập / Đăng ký',        'wishlist' => 'Danh sách yêu thích', // Thêm mới
],
    'en' => ['my_account' => 'My Account', 'logout' => 'Logout', 'login_register' => 'Login / Register' ,'wishlist' => 'Wishlist',]
];
$header_lang = $header_translations[$language_code];

// --- Mã PHP gốc của bạn để lấy dữ liệu ---
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$theme_options_stmt = $pdo->query("SELECT option_key, option_value FROM theme_options");
$theme_options = $theme_options_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$logo_light_file = $theme_options['logo_light'] ?? '';
$stmt_menu = $pdo->prepare("
    SELECT mi.url, mit.title
    FROM menu_items mi
    JOIN menus m ON mi.menu_id = m.id
    LEFT JOIN menu_item_translations mit ON mi.id = mit.menu_item_id AND mit.language_code = ?
    WHERE m.location = 'main_nav'
    ORDER BY mi.display_order ASC
");
$stmt_menu->execute([$language_code]);
$main_menu_items = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);
$cart_item_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<style>
    #customer-account-nav { position: relative; }
    #customer-account-nav .customer-nav-link { display: flex; align-items: center; cursor: pointer; padding: 8px; }
    #customer-account-nav .customer-dropdown-menu {
        position: absolute; top: 100%; right: 0; z-index: 1050; display: none;
        min-width: 10rem; padding: 0.5rem 0; margin-top: 0.5rem; font-size: 1rem;
        color: #212529; text-align: left; list-style: none; background-color: #fff;
        background-clip: padding-box; border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
    }
    #customer-account-nav .customer-dropdown-menu.show { display: block; }
    #customer-account-nav .customer-dropdown-menu .dropdown-item {
        display: block; width: 100%; padding: 0.25rem 1rem; clear: both;
        font-weight: 400; color: #212529; text-decoration: none;
        white-space: nowrap; background-color: transparent; border: 0;
    }
    #customer-account-nav .customer-dropdown-menu .dropdown-item:hover { background-color: #f8f9fa; }
</style>

<div class="top-bar d-none d-lg-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <?php if (!empty($settings['hotline'])): ?>
            <a href="tel:<?= htmlspecialchars($settings['hotline']) ?>" class="me-3"><i class="fas fa-phone-alt me-2"></i><span>Hotline: <?= htmlspecialchars($settings['hotline']) ?></span></a>
            <?php endif; ?>
            <?php if (!empty($settings['contact_email'])): ?>
            <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>"><i class="fas fa-envelope me-2"></i><span><?= htmlspecialchars($settings['contact_email']) ?></span></a>
            <?php endif; ?>
        </div>
        <div>
            <?php if (!empty($theme_options['social_facebook'])): ?><a href="<?= htmlspecialchars($theme_options['social_facebook']) ?>" target="_blank" class="me-3"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
            <?php if (!empty($theme_options['social_instagram'])): ?><a href="<?= htmlspecialchars($theme_options['social_instagram']) ?>" target="_blank" class="me-3"><i class="fab fa-instagram"></i></a><?php endif; ?>
            <?php if (!empty($theme_options['social_youtube'])): ?><a href="<?= htmlspecialchars($theme_options['social_youtube']) ?>" target="_blank" class="me-3"><i class="fab fa-youtube"></i></a><?php endif; ?>
            
            <span class="ms-2 lang-switcher">
            <?php
                $current_url = $_SERVER['REQUEST_URI'];
                $query_params = $_GET;
                $slug_for_lang_switcher = $params['slug'] ?? null;
                $query_params['lang'] = 'vi';
                if ($slug_for_lang_switcher) { $query_params['slug'] = $slug_for_lang_switcher; }
                $vi_link = strtok($current_url, '?') . '?' . http_build_query($query_params);
                $query_params['lang'] = 'en';
                if ($slug_for_lang_switcher) { $query_params['slug'] = $slug_for_lang_switcher; }
                $en_link = strtok($current_url, '?') . '?' . http_build_query($query_params);
            ?>
            <a href="<?= $vi_link ?>" title="Tiếng Việt" class="lang-switcher-link <?= $language_code == 'vi' ? 'active' : '' ?>">
                <svg class="lang-switcher-flag" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 600"><path fill="#da251d" d="M0 0h900v600H0z"/><path fill="#ff0" d="m450 115.4 81.3 249.1-212.7-153.9h262.8L368.7 364.5z"/></svg>
            </a>
            <a href="<?= $en_link ?>" title="English" class="lang-switcher-link <?= $language_code == 'en' ? 'active' : '' ?>">
                <svg class="lang-switcher-flag" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 30"><clipPath id="a"><path d="M0 0v30h60V0z"/></clipPath><clipPath id="b"><path d="M30 15h30v15zm0 0v15H0zm0 0H0V0zm0 0V0h30z"/></clipPath><g clip-path="url(#a)"><path d="M0 0v30h60V0z" fill="#012169"/><path d="M0 0L60 30m0-30L0 30" stroke="#fff" stroke-width="6"/><path d="M0 0L60 30m0-30L0 30" clip-path="url(#b)" stroke="#C8102E" stroke-width="4"/><path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10"/><path d="M30 0v30M0 15h60" stroke="#C8102E" stroke-width="6"/></g></svg>
            </a>
            </span>
        </div>
    </div>
</div>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL . htmlspecialchars($logo_light_file) ?>" id="navbar-logo" alt="FUACO Logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <?php foreach($main_menu_items as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL . ltrim(htmlspecialchars($item['url']), '/') ?>"><?= htmlspecialchars($item['title']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><a href="#" id="search-toggle-btn" class="nav-link"><i class="fas fa-search fs-5"></i></a></li>
                
                <?php if (isset($_SESSION['customer'])): ?>
                    <li class="nav-item" id="customer-account-nav">
                        <a class="nav-link customer-nav-link" id="customerAccountLink">
                            <i class="fas fa-user me-2"></i>
                            <?= htmlspecialchars($_SESSION['customer']['name']) ?>
                        </a>
                        <ul class="customer-dropdown-menu" id="customerDropdownMenu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>tai-khoan"><?= $header_lang['my_account'] ?></a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>tai-khoan/yeu-thich"><?= $header_lang['wishlist'] ?></a></li>

                            <li><hr class="dropdown-divider"></li>
                            
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>dang-xuat"><?= $header_lang['logout'] ?></a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>dang-nhap" class="nav-link" title="<?= $header_lang['login_register'] ?>">
                            <i class="fas fa-user fs-5"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>cart" class="nav-link position-relative">
                        <i class="fas fa-shopping-cart fs-5"></i>
                        <span id="cart-item-count-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;"><?= $cart_item_count ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountNav = document.getElementById('customer-account-nav');
    if (accountNav) {
        const accountLink = document.getElementById('customerAccountLink');
        const dropdownMenu = document.getElementById('customerDropdownMenu');
        accountLink.addEventListener('click', function(event) {
            event.preventDefault();
            dropdownMenu.classList.toggle('show');
        });
        document.addEventListener('click', function(event) {
            if (!accountNav.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>