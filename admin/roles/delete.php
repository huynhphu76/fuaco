<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này


// BẢO MẬT: Yêu cầu quyền 'manage-roles'
if (!hasPermission('manage-roles')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
// ======================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        verify_csrf_token(); // Thêm dòng này

    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// BẢO MẬT: Không cho xóa vai trò Admin (ID=1)
if ($id === 1) {
    die("Không thể xóa vai trò Admin gốc.");
}

if ($id) {
    $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->execute([$id]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        $delete_stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $delete_stmt->execute([$id]);
        // Cập nhật log action để dùng session mới
        logAction($pdo, $_SESSION['user']['id'], "Xóa vai trò #{$id}: '" . htmlspecialchars($role['name']) . "'");
    }
}

header("Location: index.php");
exit;
?>