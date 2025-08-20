<?php
// Tệp: pages/logout.php (Phiên bản đúng)

// Luôn bắt đầu session ở đầu file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xóa session của khách hàng
unset($_SESSION['customer']);

// Chuyển hướng về trang chủ
// File này không include bất kỳ file HTML nào, nên sẽ không có lỗi "headers already sent"
header('Location: /interior-website/');
exit;

?>