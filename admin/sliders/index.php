<?php
// Tệp: admin/sliders/index.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-sliders')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

$language_code = 'vi'; // Hiển thị tiêu đề tiếng Việt trong admin

$stmt = $pdo->prepare("
    SELECT s.id, s.image_url, s.display_order, s.is_active, st.title
    FROM sliders s
    LEFT JOIN slider_translations st ON s.id = st.slider_id AND st.language_code = ?
    ORDER BY s.display_order ASC, s.id DESC
");
$stmt->execute([$language_code]);
$sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-images me-2"></i>Quản lý Trình chiếu (Sliders)</h2>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Slider mới</a>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr><th>Thứ tự</th><th>Ảnh</th><th>Tiêu đề (VI)</th><th>Trạng thái</th><th style="width: 120px;">Hành động</th></tr>
        </thead>
        <tbody>
            <?php foreach ($sliders as $slider): ?>
                <tr>
                    <td><?= $slider['display_order'] ?></td>
                    <td><img src="/interior-website/uploads/sliders/<?= htmlspecialchars($slider['image_url']) ?>" height="50" class="img-thumbnail"></td>
                    <td><?= htmlspecialchars($slider['title'] ?? '[Chưa có bản dịch]') ?></td>
                    <td>
                        <span class="badge <?= $slider['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= $slider['is_active'] ? 'Hoạt động' : 'Ẩn' ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $slider['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa slider này?')">
                            <input type="hidden" name="id" value="<?= $slider['id'] ?>">
                                <?php csrf_field(); // Thêm dòng này ?>

                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Sliders';
include '../layout.php';
?>