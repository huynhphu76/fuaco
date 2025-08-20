<?php
// Tệp: admin/menus/delete.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';

// Bảo mật: Yêu cầu quyền 'manage-menus'
if (!hasPermission('manage-menus')) {
    die('Bạn không có quyền thực hiện chức năng này.');
}

// Chỉ chấp nhận yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Kiểm tra CSRF token
verify_csrf_token();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        // Lấy tên menu (tiếng Việt) TRƯỚC KHI xóa để ghi log
        $stmt_name = $pdo->prepare("SELECT name FROM menu_translations WHERE menu_id = ? AND language_code = 'vi'");
        $stmt_name->execute([$id]);
        $menu_name = $stmt_name->fetchColumn();

        // Xóa menu, các mục con và bản dịch sẽ tự động bị xóa theo (do ràng buộc CSDL)
        $delete_stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $delete_stmt->execute([$id]);
        
        logAction($pdo, $_SESSION['user']['id'], "Xóa menu #{$id}: '" . htmlspecialchars($menu_name) . "'");

    } catch (PDOException $e) {
        // Ghi lại lỗi nếu có
        error_log("Lỗi khi xóa menu: " . $e->getMessage());
    }
}

header("Location: index.php");
exit;
?>