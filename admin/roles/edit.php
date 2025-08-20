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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }
// ======================================================


// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $permissions = $_POST['permissions'] ?? [];

    // Cập nhật tên và mô tả của vai trò
    $stmt = $pdo->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);

    // Xóa tất cả các quyền cũ của vai trò này
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$id]);

    // Thêm các quyền mới được chọn
    if (!empty($permissions)) {
        $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissions as $permission_id) {
            $stmt->execute([$id, $permission_id]);
        }
    }
    
    // Cập nhật log action để dùng session mới
    logAction($pdo, $_SESSION['user']['id'], "Cập nhật quyền cho vai trò '" . htmlspecialchars($name) . "'");
    header("Location: index.php");
    exit;
}

// Lấy thông tin vai trò
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$id]);
$role = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$role) { die('Không tìm thấy vai trò'); }

// Lấy tất cả các quyền có thể có
$all_permissions = $pdo->query("SELECT * FROM permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy các quyền mà vai trò này đang có
$stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$stmt->execute([$id]);
$role_has_permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4">Sửa vai trò: <?= htmlspecialchars($role['name']) ?></h2>
    <form method="POST">
        <?php csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Tên vai trò</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($role['name']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description"><?= htmlspecialchars($role['description']) ?></textarea></div>
        
        <h5 class="mt-4">Gán quyền hạn</h5>
        <div class="row">
            <?php foreach ($all_permissions as $permission): ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permission['id'] ?>" id="perm_<?= $permission['id'] ?>"
                            <?php if (in_array($permission['id'], $role_has_permissions)) echo 'checked'; ?>>
                        <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                            <?= htmlspecialchars($permission['description']) ?> (<?= htmlspecialchars($permission['name']) ?>)
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="index.php" class="btn btn-secondary">Huỷ</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Sửa vai trò';
include '../layout.php';
?>