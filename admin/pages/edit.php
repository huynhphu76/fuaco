<?php
// Tệp: admin/pages/edit.php (Phiên bản hoàn hảo)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Nạp các file cần thiết theo đúng thứ tự
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-pages')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';

// Luôn gọi hàm này ở đầu để đảm bảo token được tạo ra
get_csrf_token();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

// Xử lý khi form được gửi đi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    try {
        $pdo->beginTransaction();
        
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        $slug = $_POST['slug'];

        $stmt = $pdo->prepare("UPDATE pages SET slug = ?, is_published = ? WHERE id = ?");
        $stmt->execute([$slug, $is_published, $id]);

        // LOGIC MỚI: Cập nhật hoặc chèn bản dịch một cách rõ ràng
        foreach (['vi', 'en'] as $lang) {
            $title = $_POST['translations'][$lang]['title'] ?? '';
            
            if (!empty($title)) {
                $content = $purifier->purify($_POST['translations'][$lang]['content'] ?? ''); // Làm sạch HTML

                // Kiểm tra xem bản dịch đã tồn tại chưa
                $check_stmt = $pdo->prepare("SELECT id FROM page_translations WHERE page_id = ? AND language_code = ?");
                $check_stmt->execute([$id, $lang]);
                
                if ($check_stmt->fetch()) {
                    // Nếu có rồi -> Cập nhật (UPDATE)
                    $update_trans_stmt = $pdo->prepare("UPDATE page_translations SET title=?, content=? WHERE page_id = ? AND language_code = ?");
                    $update_trans_stmt->execute([$title, $content, $id, $lang]);
                } else {
                    // Nếu chưa có -> Chèn mới (INSERT)
                    $insert_trans_stmt = $pdo->prepare("INSERT INTO page_translations (page_id, language_code, title, content) VALUES (?, ?, ?, ?)");
                    $insert_trans_stmt->execute([$id, $lang, $title, $content]);
                }
            } 
            else {
                // Nếu không có tiêu đề, xóa bản dịch (nếu có)
                $pdo->prepare("DELETE FROM page_translations WHERE page_id = ? AND language_code = ?")->execute([$id, $lang]);
            }
        }
        
        $pdo->commit();
        logAction($pdo, $_SESSION['user']['id'], "Cập nhật trang #{$id}");
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi cập nhật trang: " . $e->getMessage());
    }
}

// Lấy dữ liệu hiện tại để hiển thị
$stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();
if (!$page) { die('Không tìm thấy trang.'); }

$trans_stmt = $pdo->prepare("SELECT language_code, title, content FROM page_translations WHERE page_id = ?");
$trans_stmt->execute([$id]);
$translations = $trans_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

ob_start();
?>
<div class="container mt-4">
    <h2><i class="fas fa-edit me-2"></i>Chỉnh sửa trang</h2>
    <form id="page-form" method="post" class="mt-4">
        <?php csrf_field(); ?>
        <div class="row">
            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
                    <div class="card-body tab-content">
                        <div class="tab-pane active" id="vi">
                            <div class="mb-3"><label class="form-label">Tiêu đề (VI)</label><input type="text" name="translations[vi][title]" value="<?= htmlspecialchars($translations['vi']['title'] ?? '') ?>" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Nội dung (VI)</label><textarea name="translations[vi][content]" class="content-editor"><?= $translations['vi']['content'] ?? '' ?></textarea></div>
                        </div>
                        <div class="tab-pane" id="en">
                            <div class="mb-3"><label class="form-label">Title (EN)</label><input type="text" name="translations[en][title]" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Content (EN)</label><textarea name="translations[en][content]" class="content-editor"><?= $translations['en']['content'] ?? '' ?></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-header"><h5>Cài đặt</h5></div>
                    <div class="card-body">
                        <div class="mb-3"><label for="slug" class="form-label">Slug (đường dẫn)</label><input type="text" name="slug" id="slug" class="form-control" value="<?= htmlspecialchars($page['slug']) ?>" required></div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" <?= $page['is_published'] ? 'checked' : '' ?>><label class="form-check-label" for="is_published">Xuất bản</label></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="index.php" class="btn btn-secondary">Hủy</a>
        </div>
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
$pageTitle = 'Chỉnh sửa trang';
include '../layout.php';
?>