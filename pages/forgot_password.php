<?php
// Tệp: pages/forgot_password.php (Đã thêm song ngữ)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

global $language_code;
$translations = [
    'vi' => [
        'title' => 'Quên Mật Khẩu',
        'subtitle' => 'Nhập email của bạn để nhận liên kết đặt lại mật khẩu.',
        'email_label' => 'Email',
        'submit_button' => 'Gửi Liên Kết Khôi Phục',
        'back_to_login' => 'Quay lại trang Đăng nhập',
        'invalid_email' => 'Vui lòng nhập một địa chỉ email hợp lệ.',
        'email_sent' => 'Yêu cầu thành công! Vui lòng kiểm tra email để nhận liên kết khôi phục.',
        'email_fail' => 'Không thể gửi email khôi phục. Vui lòng liên hệ hỗ trợ.',
        'email_not_found' => 'Email không tồn tại trong hệ thống.',
        'email_subject' => 'Yêu cầu khôi phục mật khẩu tài khoản FUACO'
    ],
    'en' => [
        'title' => 'Forgot Password',
        'subtitle' => 'Enter your email to receive a password reset link.',
        'email_label' => 'Email',
        'submit_button' => 'Send Reset Link',
        'back_to_login' => 'Back to Login',
        'invalid_email' => 'Please enter a valid email address.',
        'email_sent' => 'Request successful! Please check your email for the reset link.',
        'email_fail' => 'Could not send reset email. Please contact support.',
        'email_not_found' => 'Email does not exist in the system.',
        'email_subject' => 'FUACO Account Password Reset Request'
    ]
];
$lang = $translations[$language_code];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = $lang['invalid_email'];
        $message_type = 'danger';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update_stmt = $pdo->prepare("UPDATE customers SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
            $update_stmt->execute([$token, $expires_at, $customer['id']]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'huynhphu06143@gmail.com';
                $mail->Password   = 'ifey soxp zwlv drqr';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('huynhphu06143@gmail.com', 'FUACO');
                $mail->addAddress($customer['email'], $customer['name']);

                $reset_link = BASE_URL . "dat-lai-mat-khau?token=" . $token;
                $mail->isHTML(true);
                $mail->Subject = $lang['email_subject'];
                $mail->Body    = "Xin chào " . htmlspecialchars($customer['name']) . ",<br><br>" .
                                 "Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại FUACO.<br>" .
                                 "Vui lòng nhấp vào liên kết dưới đây để tạo mật khẩu mới. Liên kết này sẽ hết hạn sau 1 giờ.<br><br>" .
                                 "<a href='{$reset_link}' style='padding:10px 15px; background-color:#c5a47e; color:white; text-decoration:none; border-radius:5px;'>Đặt Lại Mật Khẩu</a><br><br>" .
                                 "Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.<br><br>" .
                                 "Trân trọng,<br>Đội ngũ FUACO.";
                
                $mail->send();
                $message = $lang['email_sent'];
                $message_type = 'success';

            } catch (Exception $e) {
                $message = $lang['email_fail'];
                $message_type = 'danger';
            }
        } else {
            $message = $lang['email_not_found'];
            $message_type = 'danger';
        }
    }
}
?>
<div class="container my-5" style="max-width: 500px;">
    <h1 class="text-center mb-4"><?= $lang['title'] ?></h1>
    <p class="text-center text-muted"><?= $lang['subtitle'] ?></p>
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label"><?= $lang['email_label'] ?></label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-dark w-100"><?= $lang['submit_button'] ?></button>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>dang-nhap"><?= $lang['back_to_login'] ?></a>
        </div>
    </form>
</div>