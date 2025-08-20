<?php
// Tệp: admin/users/delete.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này


// Bảo mật: Yêu cầu quyền 'delete-users'
if (!hasPermission('delete-users')) {
    die('Bạn không có quyền thực hiện chức năng này.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        verify_csrf_token(); // Thêm dòng này

    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Bảo mật: Không cho admin tự xóa tài khoản của chính mình
if ($id && $id == $_SESSION['user']['id']) {
    die('Lỗi: Bạn không thể tự xóa tài khoản của chính mình.');
}

if ($id) {
    // Lấy thông tin TRƯỚC KHI xóa để ghi log
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->execute([$id]);
        
        // SỬA LỖI: Truyền đủ 3 tham số vào hàm logAction
        logAction($pdo, $_SESSION['user']['id'], "Xóa tài khoản #{$id}: '" . htmlspecialchars($user['email']) . "'");
    }
}

header("Location: index.php");
exit;
?>
