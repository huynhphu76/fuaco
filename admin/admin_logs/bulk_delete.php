<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Kiểm tra quyền 'view-logs'
if (!hasPermission('view-logs')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_ids'])) {
    $ids_to_delete = array_filter($_POST['selected_ids'], 'is_numeric');

    if (!empty($ids_to_delete)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM admin_logs WHERE id = ?");
            foreach ($ids_to_delete as $id) {
                $stmt->execute([$id]);
            }
            $pdo->commit();

            $deleted_ids_str = implode(', ', $ids_to_delete);
            logAction($pdo, $_SESSION['user_id'], "Xóa hàng loạt nhật ký có ID: {$deleted_ids_str}");
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}

header("Location: index.php");
exit;
?>