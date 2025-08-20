<?php
// Tệp: admin/profile.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/permission_check.php';
require_once __DIR__ . '/../helpers/log_action.php';

// Bất kỳ ai đã đăng nhập đều có thể xem hồ sơ của mình
if (!hasPermission('view-dashboard')) {
    die('Bạn không có quyền truy cập.');
}

$user_id = $_SESSION['user']['id'];
$success_message = '';
$error_message = '';

// Lấy thông tin người dùng hiện tại, bao gồm cả avatar
$stmt = $pdo->prepare("SELECT name, email, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý khi người dùng gửi form cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? null;

    // Kiểm tra email trùng lặp
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $error_message = 'Email này đã được sử dụng bởi một tài khoản khác.';
    } else {
        $new_avatar_filename = $user['avatar']; // Giữ lại avatar cũ làm mặc định

        // Xử lý upload ảnh đại diện mới
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['avatar']['type'], $allowed_types) && $_FILES['avatar']['size'] <= 2097152) { // 2MB
                // Xóa avatar cũ nếu có
                if ($user['avatar'] && file_exists($uploadDir . $user['avatar'])) {
                    unlink($uploadDir . $user['avatar']);
                }
                // Tạo tên file mới và di chuyển file
                $new_avatar_filename = uniqid('avatar_', true) . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $new_avatar_filename);
            } else {
                $error_message = 'Tệp ảnh không hợp lệ. Vui lòng chọn ảnh JPG, PNG, GIF và dung lượng dưới 2MB.';
            }
        }

        // Chỉ cập nhật CSDL nếu không có lỗi upload
        if (empty($error_message)) {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                     $error_message = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $passwordHash, $new_avatar_filename, $user_id]);
                    $success_message = 'Cập nhật hồ sơ và mật khẩu thành công!';
                }
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$name, $email, $new_avatar_filename, $user_id]);
                $success_message = 'Cập nhật hồ sơ thành công!';
            }

            if (empty($error_message)) {
                logAction($pdo, $user_id, "Tự cập nhật thông tin hồ sơ.");
                // Cập nhật lại session để hiển thị thông tin mới ngay lập tức
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['avatar'] = $new_avatar_filename;
                // Tải lại thông tin user để hiển thị avatar mới
                $user['avatar'] = $new_avatar_filename;
            }
        }
    }
}

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4"><i class="fas fa-user-circle me-2"></i>Hồ sơ của tôi</h2>

    <?php if ($success_message): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-danger"><?= $error_message ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="mt-3">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="/interior-website/uploads/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>" 
                             onerror="this.onerror=null;this.src='/interior-website/assets/images/default-avatar.png';"
                             class="img-thumbnail rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover;" 
                             alt="Avatar">
                        <div class="mb-3">
                             <label for="avatar" class="form-label">Thay đổi ảnh đại diện</label>
                             <input class="form-control form-control-sm" type="file" id="avatar" name="avatar">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Họ tên</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control" placeholder="Bỏ trống nếu không muốn thay đổi">
                            <div class="form-text">Nhập mật khẩu mới nếu bạn muốn thay đổi. Yêu cầu tối thiểu 6 ký tự.</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end border-top pt-3 mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Cập nhật thông tin</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Hồ sơ của tôi';
include 'layout.php';
?>
