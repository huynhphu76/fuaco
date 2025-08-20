<?php
// Tệp: admin/pages/index.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-pages')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

$language_code = 'vi'; // Hiển thị tiêu đề tiếng Việt trong admin

$stmt = $pdo->prepare("
    SELECT p.id, p.slug, p.is_published, p.updated_at, pt.title
    FROM pages p
    LEFT JOIN page_translations pt ON p.id = pt.page_id AND pt.language_code = ?
    ORDER BY p.id DESC
");
$stmt->execute([$language_code]);
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Quản lý Trang tĩnh</h2>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Trang mới</a>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr><th>ID</th><th>Tiêu đề (VI)</th><th>Đường dẫn (Slug)</th><th>Trạng thái</th><th>Cập nhật lần cuối</th><th style="width: 120px;">Hành động</th></tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $page): ?>
                <tr>
                    <td><?= $page['id'] ?></td>
                    <td><?= htmlspecialchars($page['title'] ?? '[Chưa có bản dịch]') ?></td>
                    <td>/<?= htmlspecialchars($page['slug']) ?></td>
                    <td>
                        <span class="badge <?= $page['is_published'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= $page['is_published'] ? 'Đã xuất bản' : 'Bản nháp' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($page['updated_at'])) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $page['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa trang này?')">
                            <input type="hidden" name="id" value="<?= $page['id'] ?>">
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
$pageTitle = 'Quản lý Trang tĩnh';
include '../layout.php';
?>