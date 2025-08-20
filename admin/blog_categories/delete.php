<?php
// Tệp: admin/blog_categories/delete.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

// Bảo mật CSRF
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Lỗi xác thực CSRF!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            // Lấy tên tiếng Việt để ghi log
            $stmt_info = $pdo->prepare("SELECT name FROM blog_category_translations WHERE blog_category_id = ? AND language_code = 'vi'");
            $stmt_info->execute([$id]);
            $category_name = $stmt_info->fetchColumn();

            // Xóa chuyên mục, các bản dịch sẽ tự xóa theo
            $stmt_delete = $pdo->prepare("DELETE FROM blog_categories WHERE id = ?");
            $stmt_delete->execute([$id]);
            
            $log_message = "Xóa chuyên mục bài viết #{$id}: '" . htmlspecialchars($category_name ?? 'Không có tên') . "'";
            logAction($pdo, $_SESSION['user_id'] ?? null, $log_message);

        } catch (Exception $e) {
            // Có thể có lỗi nếu chuyên mục đang được sử dụng (nếu CSDL có ràng buộc)
        }
    }
}

unset($_SESSION['csrf_token']);
header("Location: index.php?success=1");
exit;
?>