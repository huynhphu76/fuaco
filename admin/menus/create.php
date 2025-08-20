<?php
// Tệp: admin/menus/create.php (GIỮ NGUYÊN)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-menus')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

$available_locations = [
    'main_nav' => 'Menu chính (Đầu trang)',
    'footer_menu' => 'Menu cuối trang (Cột 1)',
    'footer_links' => 'Menu liên kết hữu ích (Cuối trang)',
];

$stmt_used = $pdo->query("SELECT location FROM menus");
$used_locations = $stmt_used->fetchAll(PDO::FETCH_COLUMN);
$locations_to_show = array_diff_key($available_locations, array_flip($used_locations));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name']); // Tên này sẽ là tên tiếng Việt mặc định
    $location = trim($_POST['location']);
    
    if ($name && $location) {
        if (in_array($location, $used_locations)) {
            $error = "Lỗi: Vị trí '{$available_locations[$location]}' đã được sử dụng.";
        } else {
            try {
                $pdo->beginTransaction();
                // 1. Tạo menu chính
                $stmt = $pdo->prepare("INSERT INTO menus (location) VALUES (?)");
                $stmt->execute([$location]);
                $menu_id = $pdo->lastInsertId();

                // 2. Thêm bản dịch tiếng Việt mặc định
                $stmt_trans = $pdo->prepare("INSERT INTO menu_translations (menu_id, language_code, name) VALUES (?, 'vi', ?)");
                $stmt_trans->execute([$menu_id, $name]);

                $pdo->commit();
                logAction($pdo, $_SESSION['user_id'], "Tạo menu mới: '" . htmlspecialchars($name) . "'");
                // Chuyển hướng đến trang Sửa để thêm các mục con
                header("Location: edit.php?id=" . $menu_id);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Lỗi CSDL: " . $e->getMessage();
            }
        }
    }
}
ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4">Tạo Menu Mới</h2>
    <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
    <form method="POST">
        <?php csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Tên Menu (Tiếng Việt)</label>
            <input type="text" class="form-control" name="name" placeholder="Ví dụ: Menu Chính" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Vị trí hiển thị trên trang web</label>
            <select name="location" class="form-select" required>
                <option value="">-- Chọn vị trí --</option>
                <?php foreach ($locations_to_show as $key => $value): ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($locations_to_show)): ?>
                <div class="form-text text-warning">Tất cả các vị trí đã được sử dụng.</div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success" <?= empty($locations_to_show) ? 'disabled' : '' ?>>Tạo Menu và Tiếp tục</button>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Tạo Menu';
include '../layout.php';
?>