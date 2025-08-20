<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php'; // Đã thêm
require_once __DIR__ . '/../../helpers/log_action.php'; // Đã thêm

// Đã thêm khối kiểm tra quyền
if (!hasPermission('manage-product-reviews')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status = in_array($_GET['status'], ['approved', 'pending']) ? $_GET['status'] : 'pending';

if ($id) {
    $stmt = $pdo->prepare("UPDATE product_reviews SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // Đã thêm phần ghi log
    $action_text = ($status == 'approved') ? 'Duyệt' : 'Bỏ duyệt';
    logAction($pdo, $_SESSION['user']['id'], "{$action_text} đánh giá sản phẩm ID #{$id}");
}

header('Location: index.php');
exit;