<?php
// Tệp: /pages/about.php
global $pdo, $language_code;

$page_slug = '/ve-chung-toi'; // Slug cố định cho trang "Về chúng tôi"

// Lấy nội dung trang từ CSDL
$stmt = $pdo->prepare("
    SELECT pt.title, pt.content
    FROM pages p
    JOIN page_translations pt ON p.id = pt.page_id
    WHERE p.slug = ? AND pt.language_code = ? AND p.is_published = 1
");
$stmt->execute([$page_slug, $language_code]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// Mặc định nếu chưa có nội dung trong CSDL
$page_title = $page['title'] ?? ($language_code == 'vi' ? 'Về Chúng Tôi' : 'About Us');
$page_content = $page['content'] ?? ($language_code == 'vi' ? '<p>Nội dung đang được cập nhật. Vui lòng tạo trang có slug <strong>/ve-chung-toi</strong> trong khu vực quản trị.</p>' : '<p>Content is being updated. Please create a page with the slug <strong>/ve-chung-toi</strong> in the admin panel.</p>');

// --- DỮ LIỆU SONG NGỮ CHO CÁC MỤC TĨNH ---
$static_translations = [
    'vi' => [
        'mission_title' => 'Sứ Mệnh',
        'mission_text' => 'Kiến tạo không gian sống ngoài trời đẳng cấp, bền vững và hài hòa với thiên nhiên.',
        'vision_title' => 'Tầm Nhìn',
        'vision_text' => 'Trở thành thương hiệu nội thất ngoài trời hàng đầu, được tin chọn bởi chất lượng và thiết kế vượt trội.',
        'values_title' => 'Giá Trị Cốt Lõi',
        'values_text' => 'Sáng tạo - Tận tâm - Chất lượng - Bền vững.'
    ],
    'en' => [
        'mission_title' => 'Our Mission',
        'mission_text' => 'To create classy, sustainable outdoor living spaces in harmony with nature.',
        'vision_title' => 'Our Vision',
        'vision_text' => 'To become the leading outdoor furniture brand, trusted for superior quality and design.',
        'values_title' => 'Core Values',
        'values_text' => 'Creativity - Dedication - Quality - Sustainability.'
    ]
];
$static_lang = $static_translations[$language_code];
// --- KẾT THÚC ---
?>

<div class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($page_title) ?></h1>
    </div>
</div>

<div class="container my-5 content-section">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?= $page_content ?>
        </div>
    </div>

    <div class="row text-center mt-5 pt-5 border-top">
        <div class="col-md-4">
            <h4><i class="fas fa-bullseye text-secondary mb-3"></i></h4>
            <h4 class="h5"><?= $static_lang['mission_title'] ?></h4>
            <p class="text-muted"><?= $static_lang['mission_text'] ?></p>
        </div>
        <div class="col-md-4">
            <h4><i class="fas fa-eye text-secondary mb-3"></i></h4>
            <h4 class="h5"><?= $static_lang['vision_title'] ?></h4>
            <p class="text-muted"><?= $static_lang['vision_text'] ?></p>
        </div>
        <div class="col-md-4">
            <h4><i class="fas fa-star text-secondary mb-3"></i></h4>
            <h4 class="h5"><?= $static_lang['values_title'] ?></h4>
            <p class="text-muted"><?= $static_lang['values_text'] ?></p>
        </div>
    </div>
</div>