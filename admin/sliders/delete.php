<?php
// Tệp: admin/sliders/delete.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-sliders')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này


// Bảo mật CSRF (Nên được thêm vào form xóa trong index.php)
// if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
// if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
//     die('Lỗi xác thực CSRF!');
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token(); // Thêm dòng này

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            // 1. Lấy thông tin TRƯỚC KHI xóa để ghi log và xóa file
            $stmt_info = $pdo->prepare("
                SELECT s.image_url, st.title 
                FROM sliders s
                LEFT JOIN slider_translations st ON s.id = st.slider_id AND st.language_code = 'vi'
                WHERE s.id = ?
            ");
            $stmt_info->execute([$id]);
            $slider = $stmt_info->fetch(PDO::FETCH_ASSOC);

            if ($slider) {
                // 2. Xóa file ảnh vật lý trên server
                if (!empty($slider['image_url'])) {
                    $imagePath = __DIR__ . '/../../uploads/sliders/' . $slider['image_url'];
                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }

                // 3. Xóa slider khỏi CSDL (bản dịch sẽ tự xóa theo nhờ ON DELETE CASCADE)
                $stmt_delete = $pdo->prepare("DELETE FROM sliders WHERE id = ?");
                $stmt_delete->execute([$id]);

                // 4. Ghi lại hành động
                $log_message = "Xóa slider #{$id}: '" . htmlspecialchars($slider['title'] ?? 'Không có tiêu đề') . "'";
                logAction($pdo, $_SESSION['user_id'] ?? null, $log_message);
            }
        } catch (Exception $e) {
            // Xử lý nếu có lỗi
            die("Lỗi khi xóa slider: " . $e->getMessage());
        }
    }
}

// unset($_SESSION['csrf_token']);
header("Location: index.php");
exit;
?>