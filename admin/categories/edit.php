<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-categories')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    try {
        $pdo->beginTransaction();
        foreach (['vi', 'en'] as $lang) {
            $name = $_POST['name'][$lang] ?? '';
            $description = $_POST['description'][$lang] ?? '';

            // Nếu có tên, thêm hoặc cập nhật
            if (!empty($name)) {
                $stmt = $pdo->prepare("
                    INSERT INTO category_translations (category_id, language_code, name, description) VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)
                ");
                $stmt->execute([$id, $lang, $name, $description]);
            } 
            // Nếu không có tên, xóa bản dịch cũ (nếu có)
            else {
                $stmt = $pdo->prepare("DELETE FROM category_translations WHERE category_id = ? AND language_code = ?");
                $stmt->execute([$id, $lang]);
            }
        }
        $pdo->commit();
        logAction($pdo, $_SESSION['user_id'], "Cập nhật danh mục #{$id}");
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi cập nhật: " . $e->getMessage());
    }
}
$trans_stmt = $pdo->prepare("SELECT language_code, name, slug, description FROM category_translations WHERE category_id = ?");
$trans_stmt->execute([$id]);
$translations = $trans_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4">Sửa danh mục đa ngôn ngữ</h2>
    <form method="POST">
        <?php csrf_field(); ?>
        <ul class="nav nav-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vi-tab-pane" type="button">Tiếng Việt</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#en-tab-pane" type="button">English</button></li></ul>
        <div class="tab-content card" id="myTabContent" style="border-top-left-radius: 0;">
            <div class="tab-pane fade show active" id="vi-tab-pane"><div class="card-body"><div class="mb-3"><label class="form-label">Tên danh mục (Tiếng Việt)</label><input type="text" name="name[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['name'] ?? '') ?>" required></div><div class="mb-3"><label class="form-label">Mô tả (Tiếng Việt)</label><textarea name="description[vi]" class="form-control" rows="3"><?= htmlspecialchars($translations['vi']['description'] ?? '') ?></textarea></div></div></div>
            <div class="tab-pane fade" id="en-tab-pane"><div class="card-body"><div class="mb-3"><label class="form-label">Category Name (English)</label><input type="text" name="name[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['name'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Description (English)</label><textarea name="description[en]" class="form-control" rows="3"><?= htmlspecialchars($translations['en']['description'] ?? '') ?></textarea></div></div></div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-primary">Lưu thay đổi</button><a href="index.php" class="btn btn-secondary">Huỷ</a></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Sửa danh mục';
include '../layout.php';
?>