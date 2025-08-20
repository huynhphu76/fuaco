<?php
// Tệp: pages/login.php (Đã thêm song ngữ cho link Quên mật khẩu)
global $language_code;

// --- BẮT ĐẦU CẬP NHẬT TẠI ĐÂY ---
$translations = [
    'vi' => [
        'title' => 'Đăng Nhập',
        'email_label' => 'Email',
        'password_label' => 'Mật khẩu',
        'forgot_password' => 'Quên mật khẩu?', // Thêm mới
        'login_button' => 'Đăng Nhập',
        'no_account' => 'Chưa có tài khoản?',
        'register_now' => 'Đăng ký ngay'
    ],
    'en' => [
        'title' => 'Login',
        'email_label' => 'Email',
        'password_label' => 'Password',
        'forgot_password' => 'Forgot password?', // Thêm mới
        'login_button' => 'Login',
        'no_account' => 'Don\'t have an account?',
        'register_now' => 'Register now'
    ]
];
// --- KẾT THÚC CẬP NHẬT ---
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
        <input type="hidden" name="action" value="login">
        <div class="mb-3">
            <label for="email" class="form-label"><?= $lang['email_label'] ?></label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label"><?= $lang['password_label'] ?></label>
                <a href="<?= BASE_URL ?>quen-mat-khau" class="form-text"><?= $lang['forgot_password'] ?></a>
            </div>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-dark w-100"><?= $lang['login_button'] ?></button>
        <div class="text-center mt-3">
            <p><?= $lang['no_account'] ?> <a href="<?= BASE_URL ?>dang-ky"><?= $lang['register_now'] ?></a></p>
        </div>
    </form>
</div>