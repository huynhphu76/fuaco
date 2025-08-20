<?php
// Tệp: admin/forgot-password.php

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    die("Vui lòng cài đặt PHPMailer qua Composer. Chạy 'composer require phpmailer/phpmailer' ở thư mục gốc.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Vui lòng nhập một địa chỉ email hợp lệ.';
        $message_type = 'danger';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update_stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
            $update_stmt->execute([$token, $expires_at, $user['id']]);

            $mail = new PHPMailer(true);
            try {
                // =================================================================
                // CẤU HÌNH SERVER (SMTP) - ĐÃ CẬP NHẬT EMAIL CỦA BẠN
                // =================================================================
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'huynhphu06143@gmail.com'; // ĐÃ CẬP NHẬT
                $mail->Password   = 'ifey soxp zwlv drqr'; // THAY THẾ BẰNG MẬT KHẨU MỚI
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // Người gửi và người nhận
                $mail->setFrom('huynhphu06143@gmail.com', 'FUACO'); // ĐÃ CẬP NHẬT
                $mail->addAddress($user['email'], $user['name']);

                // Nội dung Email
                $reset_link = "http://{$_SERVER['HTTP_HOST']}/interior-website/admin/reset-password.php?token=" . $token;
                $mail->isHTML(true);
                $mail->Subject = 'Yêu cầu khôi phục mật khẩu';
                $mail->Body    = "Xin chào " . htmlspecialchars($user['name']) . ",<br><br>" .
                                 "Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.<br>" .
                                 "Vui lòng nhấp vào liên kết dưới đây để tạo mật khẩu mới. Liên kết này sẽ hết hạn sau 1 giờ.<br><br>" .
                                 "<a href='{$reset_link}' style='padding:10px 15px; background-color:#0d6efd; color:white; text-decoration:none; border-radius:5px;'>Đặt lại mật khẩu</a><br><br>" .
                                 "Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.<br><br>" .
                                 "Trân trọng,<br>Đội ngũ hỗ trợ.";
                $mail->AltBody = 'Để đặt lại mật khẩu, vui lòng truy cập liên kết sau: ' . $reset_link;

                $mail->send();
                $message = 'Yêu cầu thành công! Vui lòng kiểm tra hộp thư email của bạn để nhận liên kết khôi phục.';
                $message_type = 'success';

            } catch (Exception $e) {
                // error_log("Mailer Error: {$mail->ErrorInfo}");
                $message = 'Không thể gửi email khôi phục. Vui lòng kiểm tra lại cấu hình mật khẩu ứng dụng.';
                $message_type = 'danger';
            }

        } else {
            $message = 'Email không tồn tại trong hệ thống. Vui lòng thử lại.';
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
        .auth-card { width: 100%; max-width: 450px; }
    </style>
</head>
<body>
    <div class="card auth-card shadow-sm">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">Khôi phục mật khẩu</h3>
            <p class="text-muted text-center small">Nhập email của bạn để nhận liên kết đặt lại mật khẩu.</p>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" action="forgot-password.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Gửi liên kết khôi phục</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="small">Quay lại trang Đăng nhập</a>
            </div>
        </div>
    </div>
</body>
</html>
