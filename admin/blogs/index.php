<?php
// Tệp: admin/blogs/index.php (HOÀN CHỈNH - ĐÃ SỬA LỖI)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token(); // Đảm bảo token được tạo cho form

$language_code = 'vi'; // Ngôn ngữ hiển thị mặc định trong admin

// SỬA LỖI: Sử dụng hai tên tham số khác nhau (:lang_bt và :lang_bct)
$sql = "
    SELECT 
        b.id, b.thumbnail, b.created_at, b.status,
        bt.title, 
        bct.name as category_name 
    FROM 
        blogs b
    LEFT JOIN 
        blog_translations bt ON b.id = bt.blog_id AND bt.language_code = :lang_bt
    LEFT JOIN 
        blog_categories bc ON b.category_id = bc.id
    LEFT JOIN 
        blog_category_translations bct ON bc.id = bct.blog_category_id AND bct.language_code = :lang_bct
    ORDER BY 
        b.created_at DESC
";
$stmt = $pdo->prepare($sql);
// SỬA LỖI: Truyền cả hai tham số vào mảng execute
$stmt->execute([
    ':lang_bt' => $language_code,
    ':lang_bct' => $language_code
]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>Quản lý Bài viết</h2>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm bài viết mới</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Ảnh</th>
                    <th>Tiêu đề (<?= strtoupper($language_code) ?>)</th>
                    <th>Chuyên mục</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width: 120px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <?php if ($post['thumbnail']): ?>
                                <img src="/interior-website/uploads/blogs/<?= htmlspecialchars($post['thumbnail']) ?>" style="width: 80px; height: 60px; object-fit: cover;" class="img-thumbnail">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($post['title'] ?? '[Chưa có bản dịch]') ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($post['category_name'] ?? 'Chưa phân loại') ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $post['status'] === 'published' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $post['status'] === 'published' ? 'Xuất bản' : 'Bản nháp' ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                            <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                    <?php csrf_field(); // Thêm dòng này ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Bài viết';
include '../layout.php';
?>