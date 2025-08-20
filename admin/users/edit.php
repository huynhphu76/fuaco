<?php
// Tệp: admin/users/edit.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// Bảo mật: Yêu cầu quyền 'edit-users'
if (!hasPermission('edit-users')) {
    die('Bạn không có quyền thực hiện chức năng này.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? null;
    $role_id = $_POST['role_id'] ?? null;

    // Chống tự hạ quyền của chính mình
    if ($id == $_SESSION['user']['id']) {
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'Admin' LIMIT 1");
        $stmt->execute();
        $admin_role_id = $stmt->fetchColumn();
        if ($role_id != $admin_role_id) {
            die('Lỗi: Bạn không thể tự thay đổi vai trò Admin của chính mình.');
        }
    }

    if ($password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role_id = ? WHERE id = ?");
        $stmt->execute([$name, $email, $passwordHash, $role_id, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role_id = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role_id, $id]);
    }

    // SỬA LỖI: Truyền đủ 3 tham số vào hàm logAction
    logAction($pdo, $_SESSION['user']['id'], "Cập nhật tài khoản #{$id} - " . htmlspecialchars($email));
    
    header('Location: index.php');
    exit;
}

// Lấy dữ liệu để hiển thị
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { die("Tài khoản không tồn tại."); }

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-user-edit"></i> Chỉnh sửa tài khoản</h2>
    <form method="POST" class="mt-4">
        <?php csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Mật khẩu mới (bỏ qua nếu không đổi)</label><input type="password" name="password" class="form-control"></div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role_id" class="form-select" required>
                <option value="">-- Chọn vai trò --</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= ($user['role_id'] == $role['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($role['name'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Chỉnh sửa tài khoản';
include '../layout.php';
?>
