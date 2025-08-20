<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-categories')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này


if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
        verify_csrf_token(); // Thêm dòng này

    header('Location: index.php'); exit; }

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // Xóa trong bảng categories, các bản dịch sẽ tự xóa theo (ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    logAction($pdo, $_SESSION['user_id'], "Xóa danh mục #{$id}");
}
header("Location: index.php");
exit;
?>