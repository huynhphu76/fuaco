<?php
// Tệp: admin/blog_comments/update_status.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('manage-blogs')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status = $_GET['status'] ?? 'pending';

// Đảm bảo trạng thái chỉ có thể là 'approved' hoặc 'pending'
if (!in_array($status, ['approved', 'pending'])) {
    $status = 'pending';
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE blog_comments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header('Location: index.php');
exit;
