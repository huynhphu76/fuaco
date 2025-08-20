<?php
// Tệp: /templates/footer.php
global $pdo, $language_code;
$footer_translations = [
    'vi' => [
        'copyright' => '© ' . date('Y') . ' FUACO. Thiết kế và phát triển bởi Huỳnh Phú.',
        'privacy_policy' => 'Chính sách bảo mật',
        'terms_of_service' => 'Điều khoản dịch vụ',
          'explore_title' => 'Khám Phá',
        'support_title' => 'Hỗ Trợ',
        'contact_title' => 'Liên Hệ',
    ],
    'en' => [
        'copyright' => '© ' . date('Y') . ' FUACO. Designed and developed by Huynh Phu.',
        'privacy_policy' => 'Privacy Policy',
        'terms_of_service' => 'Terms of Service',
        'explore_title' => 'Explore',
        'support_title' => 'Support',
        'contact_title' => 'Contact',
    ]
];
$footer_lang = $footer_translations[$language_code];
// --- KẾT THÚC ---
// Lấy thông tin từ settings & translations
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$settings_trans_stmt = $pdo->prepare("SELECT setting_key, setting_value FROM setting_translations WHERE language_code = ?");
$settings_trans_stmt->execute([$language_code]);
$settings_trans = $settings_trans_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Lấy thông tin từ theme_options & translations
$options_stmt = $pdo->query("SELECT option_key, option_value FROM theme_options");
$options = $options_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$options_trans_stmt = $pdo->prepare("SELECT option_key, option_value FROM theme_option_translations WHERE language_code = ?");
$options_trans_stmt->execute([$language_code]);
$options_trans = $options_trans_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

function get_footer_menu_items($pdo, $menu_id, $language_code) {
    if (!$menu_id) return [];
    $stmt = $pdo->prepare("SELECT mi.url, mit.title FROM menu_items mi LEFT JOIN menu_item_translations mit ON mi.id = mit.menu_item_id AND mit.language_code = ? WHERE mi.menu_id = ? ORDER BY mi.display_order ASC");
    $stmt->execute([$language_code, $menu_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$explore_items = get_footer_menu_items($pdo, $options['footer_explore_menu_id'] ?? null, $language_code);
$connect_items = get_footer_menu_items($pdo, $options['footer_connect_menu_id'] ?? null, $language_code);
$logo_light_file = $options['logo_light'] ?? '';
?>



<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                <?php if($logo_light_file): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($logo_light_file) ?>" class="footer-logo" alt="FUACO Logo">
                <?php endif; ?>
                <p class="small mt-3"><?= htmlspecialchars($options_trans['footer_about'] ?? 'Thương hiệu hàng đầu trong lĩnh vực thiết kế và thi công nội thất cao cấp.') ?></p>
            </div>

            <div class="col-lg-2 col-6 mb-4 mb-lg-0">
             <h5 class="text-uppercase"><?= $footer_lang['explore_title'] ?></h5>
                <ul class="list-unstyled">
                    <?php foreach($explore_items as $item): ?>
                    <li class="mb-2"><a href="<?= BASE_URL . ltrim(htmlspecialchars($item['url']), '/') ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-2 col-6 mb-4 mb-lg-0">
          <h5 class="text-uppercase"><?= $footer_lang['support_title'] ?></h5>
                <ul class="list-unstyled">
                     <?php foreach($connect_items as $item): ?>
                    <li class="mb-2"><a href="<?= BASE_URL . ltrim(htmlspecialchars($item['url']), '/') ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
           <h5 class="text-uppercase"><?= $footer_lang['contact_title'] ?></h5>
                 <ul class="list-unstyled small contact-info">
                    <li class="mb-3 d-flex"><i class="fas fa-map-marker-alt fa-fw mt-1"></i><span><?= htmlspecialchars($settings_trans['address'] ?? '') ?></span></li>
                    <li class="mb-3 d-flex"><i class="fas fa-phone-alt fa-fw mt-1"></i><span><?= htmlspecialchars($settings['hotline'] ?? '') ?></span></li>
                    <li class="mb-3 d-flex"><i class="fas fa-envelope fa-fw mt-1"></i><span><?= htmlspecialchars($settings['contact_email'] ?? '') ?></span></li>
                 </ul>
                 <div class="d-flex gap-3">
                    <?php if (!empty($options['social_facebook'])): ?><a href="<?= htmlspecialchars($options['social_facebook']) ?>" target="_blank" class="fs-5"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if (!empty($options['social_instagram'])): ?><a href="<?= htmlspecialchars($options['social_instagram']) ?>" target="_blank" class="fs-5"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if (!empty($options['social_youtube'])): ?><a href="<?= htmlspecialchars($options['social_youtube']) ?>" target="_blank" class="fs-5"><i class="fab fa-youtube"></i></a><?php endif; ?>
                    <?php if (!empty($options['social_tiktok'])): ?><a href="<?= htmlspecialchars($options['social_tiktok']) ?>" target="_blank" class="fs-5"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                 </div>
            </div>
        </div>

       <div class="footer-bottom d-flex justify-content-between flex-wrap align-items-center">
    <span><?= $footer_lang['copyright'] ?></span>
    <span>
        <a href="<?= BASE_URL ?>chinh-sach-bao-mat" class="me-3"><?= $footer_lang['privacy_policy'] ?></a>
        <a href="<?= BASE_URL ?>dieu-khoan-dich-vu"><?= $footer_lang['terms_of_service'] ?></a>
    </span>
</div>
    </div>
</footer>
<div class="floating-contact-buttons">
    <?php
        $facebook_link = $options['social_facebook'] ?? '#';
        $instagram_link = $options['social_instagram'] ?? '#';
        $youtube_link = $options['social_youtube'] ?? '#';
        $tiktok_link = $options['social_tiktok'] ?? '#';
    ?>

    <?php if ($facebook_link !== '#'): ?>
    <a href="<?= htmlspecialchars($facebook_link) ?>" target="_blank" class="contact-button cb-facebook" title="Facebook">
        <i class="fab fa-facebook-f"></i>
        <span>Facebook</span>
    </a>
    <?php endif; ?>

    <?php if ($instagram_link !== '#'): ?>
    <a href="<?= htmlspecialchars($instagram_link) ?>" target="_blank" class="contact-button cb-instagram" title="Instagram">
        <i class="fab fa-instagram"></i>
        <span>Instagram</span>
    </a>
    <?php endif; ?>

    <?php if ($youtube_link !== '#'): ?>
    <a href="<?= htmlspecialchars($youtube_link) ?>" target="_blank" class="contact-button cb-youtube" title="YouTube">
        <i class="fab fa-youtube"></i>
        <span>YouTube</span>
    </a>
    <?php endif; ?>

    <?php if ($tiktok_link !== '#'): ?>
    <a href="<?= htmlspecialchars($tiktok_link) ?>" target="_blank" class="contact-button cb-tiktok" title="TikTok">
        <i class="fab fa-tiktok"></i>
        <span>TikTok</span>
    </a>
    <?php endif; ?>
</div>
<div id="search-overlay">
    <button id="search-close-btn"><i class="fas fa-times"></i></button>
    <div class="search-form-container">
        <form action="<?= BASE_URL ?>san-pham" method="GET">
            <input type="search" name="search" placeholder="Tìm kiếm sản phẩm..." autocomplete="off" required>
            <button type="submit" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>


