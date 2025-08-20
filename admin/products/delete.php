<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm dòng này
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

// Bảo mật: Yêu cầu quyền 'delete-products'
if (!hasPermission('delete-products')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
// ======================================================

// Chỉ chấp nhận yêu cầu POST để xóa
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        verify_csrf_token(); // Thêm dòng này để kiểm tra token

    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // Lấy thông tin sản phẩm TRƯỚC KHI xóa để ghi log và xóa ảnh
    $stmt = $pdo->prepare("
        SELECT p.main_image, pt.name 
        FROM products p
        LEFT JOIN product_translations pt ON p.id = pt.product_id AND pt.language_code = 'vi'
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // 1. Xóa file ảnh đại diện nếu có
        if ($product['main_image']) {
            $imagePath = __DIR__ . '/../../uploads/products/' . $product['main_image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        // 2. Xóa sản phẩm khỏi CSDL
        // Nhờ có ON DELETE CASCADE, các bản dịch, ảnh con, và thuộc tính liên quan cũng sẽ bị xóa theo.
        $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->execute([$id]);

        // 3. Ghi lại hành động
        logAction($pdo, $_SESSION['user_id'], "Xóa sản phẩm #{$id}: '" . htmlspecialchars($product['name']) . "'");
    }
}

header("Location: index.php");
exit;
?>