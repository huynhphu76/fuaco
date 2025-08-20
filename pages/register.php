<?php
// Tệp: pages/register.php (Đã thêm song ngữ)
global $language_code;

$translations = [
    'vi' => [
        'title' => 'Đăng Ký Tài Khoản',
        'name_label' => 'Họ và tên',
        'email_label' => 'Email',
        'password_label' => 'Mật khẩu (ít nhất 6 ký tự)',
        'register_button' => 'Đăng Ký',
        'has_account' => 'Đã có tài khoản?',
        'login_now' => 'Đăng nhập'
    ],
    'en' => [
        'title' => 'Create Account',
        'name_label' => 'Full Name',
        'email_label' => 'Email',
        'password_label' => 'Password (at least 6 characters)',
        'register_button' => 'Register',
        'has_account' => 'Already have an account?',
        'login_now' => 'Login'
    ]
];
$lang = $translations[$language_code];

$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>
<div class="container my-5" style="max-width: 500px;">
    <h1 class="text-center mb-4"><?= $lang['title'] ?></h1>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form action="<?= BASE_URL ?>pages/auth_handler.php" method="POST">
        <input type="hidden" name="action" value="register">
        <div class="mb-3">
            <label for="name" class="form-label"><?= $lang['name_label'] ?></label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label"><?= $lang['email_label'] ?></label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label"><?= $lang['password_label'] ?></label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-dark w-100"><?= $lang['register_button'] ?></button>
         <div class="text-center mt-3">
            <p><?= $lang['has_account'] ?> <a href="<?= BASE_URL ?>dang-nhap"><?= $lang['login_now'] ?></a></p>
        </div>
    </form>
</div>