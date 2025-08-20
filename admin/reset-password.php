<?php
// Tệp: admin/reset-password.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/database.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$show_form = false;

if (empty($token)) {
    $message = 'Mã khôi phục không hợp lệ hoặc bị thiếu.';
    $message_type = 'danger';
} else {
    // Tìm người dùng với token hợp lệ và CHƯA HẾT HẠN
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Token hợp lệ, cho phép hiển thị form
        $show_form = true;
    } else {
        $message = 'Liên kết khôi phục không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.';
        $message_type = 'danger';
    }
}

// Xử lý khi người dùng gửi form mật khẩu mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $token_from_form = $_POST['token'] ?? '';

    // Lấy lại thông tin user để đảm bảo an toàn
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token_from_form]);
    $user = $stmt->fetch();

    if (!$user) {
         $message = 'Đã có lỗi xảy ra hoặc token đã hết hạn trong quá trình bạn thao tác. Vui lòng thử lại từ đầu.';
         $message_type = 'danger';
         $show_form = false;
    } elseif ($password !== $password_confirm) {
        $message = 'Mật khẩu xác nhận không khớp.';
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Mật khẩu phải có ít nhất 6 ký tự.';
        $message_type = 'danger';
    } else {
        // Cập nhật mật khẩu mới và vô hiệu hóa token
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update_stmt->execute([$passwordHash, $user['id']]);

        $message = 'Mật khẩu của bạn đã được cập nhật thành công! Bạn có thể đăng nhập ngay bây giờ.';
        $message_type = 'success';
        $show_form = false; // Ẩn form sau khi thành công
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
        .auth-card { width: 100%; max-width: 450px; }
    </style>
</head>
<body>
    <div class="card auth-card shadow-sm">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">Tạo mật khẩu mới</h3>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($show_form): ?>
                <form method="POST" action="reset-password.php?token=<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                     <div class="mb-3">
                        <label for="password_confirm" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đặt lại mật khẩu</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (!$show_form): ?>
            <div class="text-center mt-3">
                <a href="login.php" class="btn btn-success">Quay lại trang Đăng nhập</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
