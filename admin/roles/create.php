<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();
// BẢO MẬT: Yêu cầu quyền 'manage-roles'
if (!hasPermission('manage-roles')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        // Cập nhật log action để dùng session mới
        logAction($pdo, $_SESSION['user']['id'], "Tạo vai trò mới: '" . htmlspecialchars($name) . "'");
        header("Location: index.php");
        exit;
    }
}

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4">Thêm vai trò mới</h2>
    <form method="POST">
        <?php csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Tên vai trò</label><input type="text" class="form-control" name="name" required></div>
        <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description"></textarea></div>
        <button type="submit" class="btn btn-success">Thêm mới</button>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Thêm vai trò';
include '../layout.php';
?>