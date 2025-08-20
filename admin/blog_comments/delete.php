<?php
// Tệp: admin/blog_comments/delete.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('manage-blogs')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;
