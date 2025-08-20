<?php
// Tệp: admin/pages/create.php (Phiên bản cuối cùng)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-pages')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
require_once __DIR__ . '/../../vendor/autoload.php';
get_csrf_token();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
    try {
        $pdo->beginTransaction();
        
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $slug = $_POST['slug'];

        $stmt = $pdo->prepare("INSERT INTO pages (slug, is_published) VALUES (?, ?)");
        $stmt->execute([$slug, $is_published]);
        $page_id = $pdo->lastInsertId();

        $stmt_trans = $pdo->prepare("INSERT INTO page_translations (page_id, language_code, title, content) VALUES (?, ?, ?, ?)");
        foreach (['vi', 'en'] as $lang) {
            if (!empty($_POST['translations'][$lang]['title'])) {
$stmt_trans->execute([$page_id, $lang, $_POST['translations'][$lang]['title'], $_POST['translations'][$lang]['content'] ?? '']);

            }
        }
        
        $pdo->commit();
        unset($_SESSION['csrf_token']);
        logAction($pdo, $_SESSION['user']['id'], "Tạo trang mới: '" . htmlspecialchars($_POST['translations']['vi']['title']) . "'");
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi tạo trang: " . $e->getMessage());
    }
}

ob_start();
?>
<div class="container mt-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Tạo trang mới</h2>
    <form id="page-form" method="post" class="mt-4">
<?php csrf_field(); ?>
    <div class="row">
            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
                    <div class="card-body tab-content">
                        <div class="tab-pane active" id="vi"><div class="mb-3"><label class="form-label">Tiêu đề (VI)</label><input type="text" name="translations[vi][title]" class="form-control" required></div><div class="mb-3"><label class="form-label">Nội dung (VI)</label><textarea name="translations[vi][content]" class="content-editor"></textarea></div></div>
                        <div class="tab-pane" id="en"><div class="mb-3"><label class="form-label">Title (EN)</label><input type="text" name="translations[en][title]" class="form-control"></div><div class="mb-3"><label class="form-label">Content (EN)</label><textarea name="translations[en][content]" class="content-editor"></textarea></div></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card shadow-sm"><div class="card-header"><h5>Cài đặt</h5></div><div class="card-body"><div class="mb-3"><label for="slug" class="form-label">Slug (đường dẫn)</label><input type="text" name="slug" id="slug" class="form-control" required placeholder="/vi-du-slug"></div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" checked><label class="form-check-label" for="is_published">Xuất bản</label></div></div></div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-success">Lưu trang</button><a href="index.php" class="btn btn-secondary">Hủy</a></div>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    tinymce.init({
        selector: '.content-editor',
        height: 500,
        branding: false,
        plugins: 'lists link image code table paste wordcount',
        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image table code',
        paste_data_images: true,
        images_upload_url: '/interior-website/admin/pages/upload_handler.php',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false
    });
    document.getElementById('page-form').addEventListener('submit', function(e) { tinymce.triggerSave(); });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Tạo trang mới';
include '../layout.php';
?>