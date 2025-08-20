<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-categories')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// Lấy danh sách danh mục Tiếng Việt để hiển thị
$language_code = 'vi';
$stmt = $pdo->prepare("
    SELECT c.id, ct.name, ct.description 
    FROM categories c 
    LEFT JOIN category_translations ct ON c.id = ct.category_id AND ct.language_code = ?
    ORDER BY c.id DESC
");
$stmt->execute([$language_code]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list me-2"></i>Quản lý danh mục</h2>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm danh mục</a>
    </div>
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr><th>ID</th><th>Tên danh mục (Tiếng Việt)</th><th>Mô tả</th><th style="width: 120px;">Hành động</th></tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['name'] ?? '[Chưa có bản dịch]') ?></td>
                    <td><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                    <td>
                        <a href="edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Sửa</a>
                        <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
$pageTitle = 'Danh sách danh mục';
include '../layout.php';
?>