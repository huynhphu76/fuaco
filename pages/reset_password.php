<?php
// Tệp: pages/reset_password.php (Đã thêm song ngữ)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

global $language_code;
$translations = [
    'vi' => [
        'title' => 'Tạo Mật Khẩu Mới',
        'new_password_label' => 'Mật khẩu mới',
        'confirm_password_label' => 'Xác nhận mật khẩu mới',
        'submit_button' => 'Đặt Lại Mật Khẩu',
        'back_to_login' => 'Quay lại trang Đăng nhập',
        'invalid_token' => 'Mã khôi phục không hợp lệ.',
        'expired_token' => 'Liên kết khôi phục không hợp lệ hoặc đã hết hạn.',
        'token_expired_during_process' => 'Token đã hết hạn trong lúc bạn thao tác. Vui lòng thử lại.',
        'password_mismatch' => 'Mật khẩu xác nhận không khớp.',
        'password_too_short' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'update_success' => 'Mật khẩu của bạn đã được cập nhật thành công!'
    ],
    'en' => [
        'title' => 'Create New Password',
        'new_password_label' => 'New Password',
        'confirm_password_label' => 'Confirm New Password',
        'submit_button' => 'Reset Password',
        'back_to_login' => 'Back to Login',
        'invalid_token' => 'Invalid recovery token.',
        'expired_token' => 'The recovery link is invalid or has expired.',
        'token_expired_during_process' => 'The token expired while you were working. Please try again.',
        'password_mismatch' => 'The password confirmation does not match.',
        'password_too_short' => 'Password must be at least 6 characters long.',
        'update_success' => 'Your password has been updated successfully!'
    ]
];
$lang = $translations[$language_code];

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$show_form = false;

if (empty($token)) {
    $message = $lang['invalid_token'];
    $message_type = 'danger';
} else {
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $customer = $stmt->fetch();

    if ($customer) {
        $show_form = true;
    } else {
        $message = $lang['expired_token'];
        $message_type = 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $token_from_form = $_POST['token'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token_from_form]);
    $customer = $stmt->fetch();

    if (!$customer) {
         $message = $lang['token_expired_during_process'];
         $message_type = 'danger';
         $show_form = false;
    } elseif ($password !== $password_confirm) {
        $message = $lang['password_mismatch'];
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = $lang['password_too_short'];
        $message_type = 'danger';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update_stmt->execute([$passwordHash, $customer['id']]);

        $message = $lang['update_success'];
        $message_type = 'success';
        $show_form = false;
    }
}
?>
<div class="container my-5" style="max-width: 500px;">
    <h1 class="text-center mb-4"><?= $lang['title'] ?></h1>
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <form method="POST" action="">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="mb-3">
                <label for="password" class="form-label"><?= $lang['new_password_label'] ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
             <div class="mb-3">
                <label for="password_confirm" class="form-label"><?= $lang['confirm_password_label'] ?></label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-dark w-100"><?= $lang['submit_button'] ?></button>
        </form>
    <?php endif; ?>

    <?php if (!$show_form): ?>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>dang-nhap" class="btn btn-primary"><?= $lang['back_to_login'] ?></a>
        </div>
    <?php endif; ?>
</div>