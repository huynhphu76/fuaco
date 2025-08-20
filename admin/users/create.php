<?php
// Tệp: admin/users/create.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// Bảo mật: Yêu cầu quyền 'create-users'
if (!hasPermission('create-users')) {
    die('Bạn không có quyền thực hiện chức năng này.');
}

// Lấy danh sách vai trò từ database để hiển thị trong dropdown
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? null;

    // --- VALIDATION ---
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email này đã được sử dụng.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }
    if (empty($role_id)) {
        $errors[] = 'Vui lòng chọn vai trò cho người dùng.';
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $passwordHash, $role_id]);
        
        // SỬA LỖI: Truyền đủ 3 tham số vào hàm logAction
        logAction($pdo, $_SESSION['user']['id'], "Tạo tài khoản mới: '" . htmlspecialchars($email) . "'");

        header('Location: index.php');
        exit;
    }
}

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-user-plus me-2"></i>Thêm người dùng mới</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mt-3">
            <?php foreach ($errors as $error): ?><p class="mb-0"><?= $error ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST" class="mt-4">
        <?php csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Họ tên</label><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label">Mật khẩu</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role_id" class="form-select" required>
                <option value="">-- Chọn vai trò --</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= (($role_id ?? '') == $role['id']) ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($role['name'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu người dùng</button>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Thêm người dùng';
include '../layout.php';
?>
