<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// BẢO MẬT: Yêu cầu quyền 'manage-roles'
if (!hasPermission('manage-roles')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
// ======================================================

$stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-tag me-2"></i>Quản lý vai trò</h2>
        <?php if (hasPermission('manage-roles')): // Chỉ người có quyền mới thấy nút này ?>
            <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm vai trò</a>
        <?php endif; ?>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tên vai trò</th>
                <th>Mô tả</th>
                <th style="width: 120px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= $role['id'] ?></td>
                    <td><?= htmlspecialchars($role['name']) ?></td>
                    <td><?= htmlspecialchars($role['description']) ?></td>
                    <td>
                        <?php if (hasPermission('manage-roles')): // Chỉ người có quyền mới thấy các nút này ?>
                            <a href="edit.php?id=<?= $role['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Sửa</a>
                            <?php if ($role['id'] != 1): // Giữ nguyên logic không cho xóa vai trò Admin ?>
                                <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa vai trò này?')">
                                    <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                        <?php csrf_field(); // Thêm dòng này ?>

                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý vai trò';
include '../layout.php';
?>