<?php
// Tệp: admin/media/media_picker.php
// Tệp này chỉ trả về HTML, không phải là một trang hoàn chỉnh.

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

// Chỉ những ai có quyền quản lý nội dung mới có thể sử dụng chức năng này.
if (!hasPermission('manage-blogs') && !hasPermission('manage-products')) {
    echo '<p class="text-danger">Bạn không có quyền truy cập.</p>';
    exit;
}

$media_files = $pdo->query("SELECT file_name FROM media_library ORDER BY uploaded_at DESC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="media-picker-grid">
    <?php if (empty($media_files)): ?>
        <p class="text-center col-12">Thư viện của bạn chưa có tệp nào. Hãy tải lên ở trang Thư viện Media.</p>
    <?php else: ?>
        <?php foreach ($media_files as $file_name): ?>
            <div class="media-picker-item" onclick="selectMedia('<?= htmlspecialchars($file_name) ?>')">
                <img src="/interior-website/uploads/library/<?= htmlspecialchars($file_name) ?>" loading="lazy">
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .media-picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; max-height: 60vh; overflow-y: auto; }
    .media-picker-item { cursor: pointer; border: 2px solid transparent; border-radius: 5px; overflow: hidden; }
    .media-picker-item:hover { border-color: #0d6efd; }
    .media-picker-item img { width: 100%; height: 120px; object-fit: cover; }
</style>
