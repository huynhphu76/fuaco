<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Kiểm tra quyền 'manage-feedbacks'
if (!hasPermission('manage-feedbacks')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
// ======================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = $pdo->prepare("SELECT name FROM feedbacks WHERE id = ?");
    $stmt->execute([$id]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        $delete_stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
        $delete_stmt->execute([$id]);
        logAction($pdo, $_SESSION['user_id'], "Xóa phản hồi #{$id} từ khách hàng '" . htmlspecialchars($feedback['name']) . "'");
    }
}

header("Location: index.php");
exit;
?>