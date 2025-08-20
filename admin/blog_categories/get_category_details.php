<?php
// Tệp: admin/blog_categories/get_category_details.php (TỆP MỚI)
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) {
    echo json_encode(['error' => 'Không có quyền truy cập.']);
    exit;
}
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode(null);
    exit;
}

// Lấy thông tin chính
$stmt_main = $pdo->prepare("SELECT * FROM blog_categories WHERE id = ?");
$stmt_main->execute([$id]);
$category = $stmt_main->fetch(PDO::FETCH_ASSOC);

if ($category) {
    // Lấy tất cả các bản dịch liên quan
$stmt_trans = $pdo->prepare("SELECT language_code, name, slug, description FROM blog_category_translations WHERE blog_category_id = ?");    $stmt_trans->execute([$id]);
    $translations = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);
    $category['translations'] = $translations;
}

echo json_encode($category);
?>