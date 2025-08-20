<?php
// Tệp: admin/pages/delete.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-pages')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này


if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
    verify_csrf_token(); // Thêm dòng này


$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // Lấy tiêu đề tiếng Việt để ghi log
    $stmt = $pdo->prepare("SELECT title FROM page_translations WHERE page_id = ? AND language_code = 'vi'");
    $stmt->execute([$id]);
    $page_title = $stmt->fetchColumn();

    // Xóa trang (bản dịch sẽ tự xóa theo)
    $delete_stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $delete_stmt->execute([$id]);
    
    logAction($pdo, $_SESSION['user_id'], "Xóa trang #{$id}: '" . htmlspecialchars($page_title ?? 'Không có tiêu đề') . "'");
}
header("Location: index.php");
exit;
?>