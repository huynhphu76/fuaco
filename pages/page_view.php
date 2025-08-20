<?php
// Tệp: /pages/page_view.php (Giao diện chung cho mọi trang tĩnh - Đã nâng cấp)
global $pdo, $language_code, $params;

$page_slug = '/' . ($params['slug'] ?? '');

try {
    // BƯỚC 1: Dùng slug để tìm ra ID của trang, bất kể ngôn ngữ
    $stmt_page_id = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND is_published = 1");
    $stmt_page_id->execute([$page_slug]);
    $page_id = $stmt_page_id->fetchColumn();

    $page = null;
    if ($page_id) {
        // BƯỚC 2: Dùng ID để lấy bản dịch theo ngôn ngữ đang chọn
        $stmt_content = $pdo->prepare("SELECT title, content FROM page_translations WHERE page_id = ? AND language_code = ?");
        $stmt_content->execute([$page_id, $language_code]);
        $page = $stmt_content->fetch(PDO::FETCH_ASSOC);

        // BƯỚC 3 (NÂNG CẤP): Nếu không có bản dịch, tự động lấy tiếng Việt làm dự phòng
        if (!$page) {
            $stmt_content->execute([$page_id, 'vi']); // Lấy bản dịch tiếng Việt
            $page = $stmt_content->fetch(PDO::FETCH_ASSOC);
        }
    }

    // Nếu sau tất cả các bước vẫn không tìm thấy trang, hiển thị lỗi 404
    if (!$page) {
        http_response_code(404);
        echo "<div class='container my-5 text-center'><h1>404</h1><p>Không tìm thấy trang yêu cầu.</p></div>";
        return;
    }

    $page_title = $page['title'];
    $page_content = $page['content'];

} catch (PDOException $e) {
    die("Lỗi cơ sở dữ liệu: " . $e->getMessage());
}
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
</div>