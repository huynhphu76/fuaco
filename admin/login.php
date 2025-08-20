<?php
// Tệp: admin/login.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role_id, avatar FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role_id']) { 
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role_id' => $user['role_id'],
                    'avatar' => $user['avatar']
                ];
                
                session_write_close();
                header('Location: index.php');
                exit;
            } else {
                $error = 'Tài khoản của bạn chưa được cấp quyền. Vui lòng liên hệ quản trị viên.';
            }
        } else {
            $error = 'Email hoặc mật khẩu không chính xác.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập trang quản trị</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
        .login-card { width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="card login-card shadow-sm">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">Đăng Nhập Admin</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <!-- THÊM LIÊN KẾT TẠI ĐÂY -->
                        <a href="forgot-password.php" class="form-text small">Quên mật khẩu?</a>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
