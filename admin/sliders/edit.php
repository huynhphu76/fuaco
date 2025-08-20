<?php
// Tệp: admin/sliders/edit.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-sliders')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
verify_csrf_token();    
    $translations = $_POST['translations'];
    $title_vi = $translations['vi']['title'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        $stmt_slider = $pdo->prepare("SELECT image_url FROM sliders WHERE id = ?");
        $stmt_slider->execute([$id]);
        $image_url = $stmt_slider->fetchColumn();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/sliders/';
            if ($image_url && file_exists($uploadDir . $image_url)) { @unlink($uploadDir . $image_url); }
            $filename = uniqid('slider_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image_url = $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE sliders SET image_url=?, button_link=?, display_order=?, is_active=? WHERE id=?");
        $stmt->execute([$image_url, $_POST['button_link'] ?? '', $_POST['display_order'] ?? 0, isset($_POST['is_active']) ? 1 : 0, $id]);
        
        $stmt_trans = $pdo->prepare("INSERT INTO slider_translations (slider_id, language_code, title, subtitle, button_text) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), subtitle=VALUES(subtitle), button_text=VALUES(button_text)");
        foreach (['vi', 'en'] as $lang) {
            if (!empty($translations[$lang]['title'])) {
                $stmt_trans->execute([
                    $id, $lang, $translations[$lang]['title'],
                    $translations[$lang]['subtitle'] ?? '', $translations[$lang]['button_text'] ?? ''
                ]);
            } else {
                $pdo->prepare("DELETE FROM slider_translations WHERE slider_id = ? AND language_code = ?")->execute([$id, $lang]);
            }
        }
        
        $pdo->commit();
        unset($_SESSION['csrf_token']);
        logAction($pdo, $_SESSION['user_id'], "Cập nhật slider #{$id}: '" . htmlspecialchars($title_vi) . "'");
        header("Location: index.php");
        exit;
    } catch(Exception $e) {
        $pdo->rollBack();
        die("Lỗi: " . $e->getMessage());
    }
}

// LẤY DỮ LIỆU HIỆN TẠI
$stmt = $pdo->prepare("SELECT * FROM sliders WHERE id = ?");
$stmt->execute([$id]);
$slider = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$slider) { die("Không tìm thấy slider"); }

$trans_stmt = $pdo->prepare("SELECT language_code, title, subtitle, button_text FROM slider_translations WHERE slider_id = ?");
$trans_stmt->execute([$id]);
$translations = $trans_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-edit me-2"></i>Sửa Slider</h2>
    <form method="POST" enctype="multipart/form-data" class="mt-4">
<?php csrf_field(); ?>
        <div class="card shadow-sm">
            <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
            <div class="card-body tab-content">
                <div class="tab-pane active" id="vi">
                    <div class="mb-3"><label class="form-label">Tiêu đề chính (VI)</label><input type="text" name="translations[vi][title]" class="form-control" value="<?= htmlspecialchars($translations['vi']['title'] ?? '') ?>" required></div>
                    <div class="mb-3"><label class="form-label">Tiêu đề phụ (VI)</label><input type="text" name="translations[vi][subtitle]" class="form-control" value="<?= htmlspecialchars($translations['vi']['subtitle'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Chữ trên nút (VI)</label><input type="text" name="translations[vi][button_text]" class="form-control" value="<?= htmlspecialchars($translations['vi']['button_text'] ?? '') ?>"></div>
                </div>
                <div class="tab-pane" id="en">
                    <div class="mb-3"><label class="form-label">Main Title (EN)</label><input type="text" name="translations[en][title]" class="form-control" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Subtitle (EN)</label><input type="text" name="translations[en][subtitle]" class="form-control" value="<?= htmlspecialchars($translations['en']['subtitle'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Button Text (EN)</label><input type="text" name="translations[en][button_text]" class="form-control" value="<?= htmlspecialchars($translations['en']['button_text'] ?? '') ?>"></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header"><h5 class="mb-0">Cấu hình chung</h5></div>
            <div class="card-body row g-3">
                <div class="col-12"><label class="form-label">Ảnh Slider (chọn để thay đổi)</label><br><img src="/interior-website/uploads/sliders/<?= htmlspecialchars($slider['image_url']) ?>" height="80" class="img-thumbnail mb-2"><input type="file" name="image" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Đường dẫn của nút</label><input type="text" name="button_link" class="form-control" value="<?= htmlspecialchars($slider['button_link']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Thứ tự hiển thị</label><input type="number" name="display_order" class="form-control" value="<?= $slider['display_order'] ?>"></div>
                <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= $slider['is_active'] ? 'checked' : '' ?>><label class="form-check-label" for="is_active">Hiển thị</label></div></div>
            </div>
        </div>

        <div class="col-12 mt-4"><button type="submit" class="btn btn-primary">Lưu thay đổi</button><a href="index.php" class="btn btn-secondary">Quay lại</a></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Sửa Slider';
include '../layout.php';
?>