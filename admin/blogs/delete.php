<?php
// Tệp: admin/blogs/delete.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

// Bảo mật CSRF: Thêm dòng này để kiểm tra token
// if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
//     die('Lỗi xác thực CSRF!');
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        verify_csrf_token(); // Thêm dòng này

    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    try {
        // SỬA LỖI: Lấy thông tin từ cả 2 bảng để ghi log và xóa ảnh
        $stmt = $pdo->prepare("
            SELECT bt.title, b.thumbnail 
            FROM blogs b
            LEFT JOIN blog_translations bt ON b.id = bt.blog_id AND bt.language_code = 'vi'
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        $blog = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($blog) {
            // 1. Xóa file ảnh thumbnail liên quan (nếu có)
            if (!empty($blog['thumbnail'])) {
                $imagePath = __DIR__ . '/../../uploads/blogs/' . $blog['thumbnail'];
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            
            // 2. Xóa bản ghi trong bảng blogs (bản dịch sẽ tự xóa nhờ ON DELETE CASCADE)
            $delete_stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
            $delete_stmt->execute([$id]);

            // 3. Ghi log hành động
            logAction($pdo, $_SESSION['user_id'] ?? null, "Xóa bài viết #{$id}: '" . htmlspecialchars($blog['title'] ?? 'Không có tiêu đề') . "'");
        }
    } catch (PDOException $e) {
        // Xử lý nếu có lỗi CSDL
        die("Lỗi khi xóa bài viết: " . $e->getMessage());
    }
}

// Xóa token sau khi dùng
// unset($_SESSION['csrf_token']);

header('Location: index.php');
exit;
?>