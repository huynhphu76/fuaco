<?php
// Tệp: helpers/csrf_helper.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Tạo hoặc lấy CSRF token hiện tại từ session.
 * @return string CSRF token.
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Hiển thị thẻ input hidden chứa CSRF token.
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . get_csrf_token() . '">';
}

/**
 * Kiểm tra CSRF token được gửi từ form.
 * Nếu không hợp lệ, sẽ dừng chương trình và báo lỗi.
 */
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Lỗi xác thực CSRF! Yêu cầu không hợp lệ.');
    }
    // Xóa token sau khi đã sử dụng để tăng cường bảo mật
    unset($_SESSION['csrf_token']);
}
?>