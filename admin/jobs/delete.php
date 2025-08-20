<?php
// Tệp: admin/jobs/delete.php (Phiên bản cuối cùng, đã bảo mật)

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các tệp cần thiết
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';

// Bảo mật: Yêu cầu quyền
if (!hasPermission('manage-recruitment')) {
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
        // Lấy thông tin TRƯỚC KHI xóa để ghi log
        $stmt_info = $pdo->prepare("SELECT title FROM job_translations WHERE job_id = ? AND language_code = 'vi'");
        $stmt_info->execute([$id]);
        $job_title = $stmt_info->fetchColumn();

        // Xóa tin tuyển dụng (các bản dịch và hồ sơ ứng tuyển sẽ tự xóa theo ràng buộc CSDL)
        $delete_stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $delete_stmt->execute([$id]);
        
        logAction($pdo, $_SESSION['user']['id'], "Xóa tin tuyển dụng #{$id}: '" . htmlspecialchars($job_title) . "'");

    } catch (PDOException $e) {
        // Ghi lại lỗi nếu có
        error_log("Lỗi khi xóa tin tuyển dụng: " . $e->getMessage());
    }
}

header("Location: index.php");
exit;
?>