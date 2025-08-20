<?php
// Tệp: admin/sliders/create.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-sliders')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $translations = $_POST['translations'];
    $title_vi = $translations['vi']['title'] ?? '';
    $image_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/sliders/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        $filename = uniqid('slider_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
            $image_url = $filename;
        }
    }

    if ($title_vi && $image_url) {
        try {
            $pdo->beginTransaction();
            // 1. Thêm vào bảng `sliders`
            $stmt_slider = $pdo->prepare("INSERT INTO sliders (image_url, button_link, display_order, is_active) VALUES (?, ?, ?, ?)");
            $stmt_slider->execute([
                $image_url, $_POST['button_link'] ?? '',
                $_POST['display_order'] ?? 0, isset($_POST['is_active']) ? 1 : 0
            ]);
            $slider_id = $pdo->lastInsertId();

            // 2. Thêm vào bảng dịch
            $stmt_trans = $pdo->prepare("INSERT INTO slider_translations (slider_id, language_code, title, subtitle, button_text) VALUES (?, ?, ?, ?, ?)");
            foreach(['vi', 'en'] as $lang) {
                if (!empty($translations[$lang]['title'])) {
                    $stmt_trans->execute([
                        $slider_id, $lang, $translations[$lang]['title'],
                        $translations[$lang]['subtitle'] ?? '', $translations[$lang]['button_text'] ?? ''
                    ]);
                }
            }
            $pdo->commit();

            unset($_SESSION['csrf_token']);
            logAction($pdo, $_SESSION['user_id'], "Tạo slider mới: '" . htmlspecialchars($title_vi) . "'");
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi: " . $e->getMessage());
        }
    }
}
ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-plus me-2"></i>Thêm Slider Mới</h2>
    <form method="POST" enctype="multipart/form-data" class="mt-4">
<?php csrf_field(); ?>        
        <div class="card shadow-sm">
            <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
            <div class="card-body tab-content">
                <div class="tab-pane active" id="vi">
                    <div class="mb-3"><label class="form-label">Tiêu đề chính (VI)</label><input type="text" name="translations[vi][title]" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Tiêu đề phụ (VI)</label><input type="text" name="translations[vi][subtitle]" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Chữ trên nút (VI)</label><input type="text" name="translations[vi][button_text]" class="form-control" placeholder="Ví dụ: Xem Ngay"></div>
                </div>
                <div class="tab-pane" id="en">
                    <div class="mb-3"><label class="form-label">Main Title (EN)</label><input type="text" name="translations[en][title]" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Subtitle (EN)</label><input type="text" name="translations[en][subtitle]" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Button Text (EN)</label><input type="text" name="translations[en][button_text]" class="form-control" placeholder="Example: View More"></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header"><h5 class="mb-0">Cấu hình chung</h5></div>
            <div class="card-body row g-3">
                <div class="col-12"><label class="form-label">Ảnh Slider</label><input type="file" name="image" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Đường dẫn của nút</label><input type="text" name="button_link" class="form-control" placeholder="Ví dụ: /products"></div>
                <div class="col-md-3"><label class="form-label">Thứ tự hiển thị</label><input type="number" name="display_order" class="form-control" value="0"></div>
                <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked><label class="form-check-label" for="is_active">Hiển thị</label></div></div>
            </div>
        </div>

        <div class="col-12 mt-4"><button type="submit" class="btn btn-success">Lưu Slider</button><a href="index.php" class="btn btn-secondary">Quay lại</a></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Thêm Slider';
include '../layout.php';
?>