<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php'; // Đã thêm
require_once __DIR__ . '/../../helpers/log_action.php'; // Đã thêm

// Đã thêm khối kiểm tra quyền
if (!hasPermission('manage-product-reviews')) {
    die('Bạn không có quyền truyupp cập chức năng này.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM product_reviews WHERE id = ?");
        $stmt->execute([$id]);

        // Đã thêm phần ghi log
        logAction($pdo, $_SESSION['user']['id'], "Xóa đánh giá sản phẩm ID #{$id}");
    }
}

header('Location: index.php');
exit;