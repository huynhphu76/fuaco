<?php
// Tệp: admin/projects/delete.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-projects')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token(); // Thêm dòng này

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        // Lấy thông tin TRƯỚC KHI xóa để ghi log
        $stmt = $pdo->prepare("
            SELECT pt.title, p.thumbnail 
            FROM projects p
            LEFT JOIN project_translations pt ON p.id = pt.project_id AND pt.language_code = 'vi'
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            // Xóa file hình thumbnail nếu có
            if ($project['thumbnail']) {
                $imagePath = '../../uploads/projects/' . $project['thumbnail'];
                if (file_exists($imagePath)) { @unlink($imagePath); }
            }
            
            // Xóa dự án (bản dịch, ảnh con, sản phẩm liên quan sẽ tự xóa nhờ CSDL)
            $delete_stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $delete_stmt->execute([$id]);
            
            logAction($pdo, $_SESSION['user_id'], "Xóa dự án #{$id}: '" . htmlspecialchars($project['title']) . "'");
        }
    }
}

header("Location: index.php");
exit;
?>