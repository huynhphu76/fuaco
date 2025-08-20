<?php
// Tệp: admin/users/index.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// Bảo mật: Yêu cầu quyền 'view-users'
if (!hasPermission('view-users')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

// Lấy danh sách người dùng và vai trò của họ
$stmt = $pdo->query("SELECT u.id, u.name, u.email, r.name as role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     ORDER BY u.id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users-cog me-2"></i>Quản lý người dùng</h2>
        <?php if (hasPermission('create-users')): ?>
            <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm người dùng</a>
        <?php endif; ?>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th style="width: 150px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge bg-info text-dark"><?= htmlspecialchars($user['role_name'] ?? 'Chưa gán') ?></span>
                    </td>
                    <td>
                        <?php if (hasPermission('edit-users')): ?>
                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Sửa</a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('delete-users') && $user['id'] != $_SESSION['user']['id']): // Không cho tự xóa mình ?>
                            <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <?php csrf_field(); // Thêm dòng này ?>

                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý người dùng';
include '../layout.php';
?>