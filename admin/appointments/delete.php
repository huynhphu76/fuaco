<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Kiểm tra quyền 'manage-appointments'
if (!hasPermission('manage-appointments')) {
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
    $stmt = $pdo->prepare("SELECT name FROM appointments WHERE id = ?");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        $delete_stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $delete_stmt->execute([$id]);
        logAction($pdo, $_SESSION['user_id'], "Xóa lịch hẹn #{$id} của khách '{$appointment['name']}'");
    }
}

header('Location: index.php');
exit;
?>