<?php
// Tệp: admin/media/delete.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('manage-blogs') && !hasPermission('manage-products')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // Lấy tên file để xóa khỏi thư mục
    $stmt = $pdo->prepare("SELECT file_name FROM media_library WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();

    if ($file) {
        $filePath = __DIR__ . '/../../uploads/library/' . $file['file_name'];
        // Xóa file vật lý nếu tồn tại
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        // Xóa bản ghi trong CSDL
        $delete_stmt = $pdo->prepare("DELETE FROM media_library WHERE id = ?");
        $delete_stmt->execute([$id]);
    }
}
header('Location: index.php');
exit;
