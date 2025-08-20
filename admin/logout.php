<?php
// Luôn bắt đầu session ở đầu file
session_start();

// Hủy tất cả các biến session
$_SESSION = [];

// Xóa session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy phiên làm việc
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập
header('Location: login.php');
exit;