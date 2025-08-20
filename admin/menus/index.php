<?php
// Tệp: admin/menus/index.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-menus')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

$available_locations = [
    'main_nav' => 'Menu chính (Đầu trang)',
    'footer_menu' => 'Menu cuối trang (Cột 1)',
    'footer_links' => 'Menu liên kết hữu ích (Cuối trang)',
];

$language_code = 'vi'; // Hiển thị tên menu tiếng Việt trong admin

$stmt = $pdo->prepare("
    SELECT m.id, m.location, mt.name
    FROM menus m
    LEFT JOIN menu_translations mt ON m.id = mt.menu_id AND mt.language_code = ?
    ORDER BY m.id ASC
");
$stmt->execute([$language_code]);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bars me-2"></i>Quản lý Menu</h2>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Menu mới</a>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr><th>Tên Menu (VI)</th><th>Vị trí hiển thị</th><th style="width: 180px;">Hành động</th></tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $menu): ?>
                <tr>
                    <td><?= htmlspecialchars($menu['name'] ?? '[Chưa có tên]') ?></td>
                    <td><?= htmlspecialchars($available_locations[$menu['location']] ?? $menu['location']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $menu['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Quản lý mục</a>
                        <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa menu này?')">
                            <input type="hidden" name="id" value="<?= $menu['id'] ?>">
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
$pageTitle = 'Quản lý Menu';
include '../layout.php';
?>