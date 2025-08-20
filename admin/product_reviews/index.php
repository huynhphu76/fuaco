<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php'; // Đã thêm

// Đã thêm khối kiểm tra quyền
if (!hasPermission('manage-product-reviews')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

$stmt = $pdo->query("
    SELECT pr.*, pt.name as product_name
    FROM product_reviews pr
    JOIN product_translations pt ON pr.product_id = pt.product_id
    WHERE pt.language_code = 'vi'
    ORDER BY pr.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Quản lý Đánh giá Sản phẩm</h1>
</div>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Sản phẩm</th>
            <th>Người gửi</th>
            <th>Đánh giá</th>
            <th>Nội dung</th>
            <th>Ngày gửi</th>
            <th>Trạng thái</th>
            <th style="width: 150px;">Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reviews as $review): ?>
            <tr>
                <td><?= htmlspecialchars($review['product_name']) ?></td>
                <td><?= htmlspecialchars($review['author_name']) ?><br><small><?= htmlspecialchars($review['author_email']) ?></small></td>
                <td class="text-warning">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <i class="<?= $i < $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                    <?php endfor; ?>
                </td>
                <td><?= htmlspecialchars($review['content']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                <td>
                    <?php if ($review['status'] == 'approved'): ?>
                        <span class="badge bg-success">Đã duyệt</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Chờ duyệt</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($review['status'] == 'pending'): ?>
                        <a href="update_status.php?id=<?= $review['id'] ?>&status=approved" class="btn btn-sm btn-success">Duyệt</a>
                    <?php else: ?>
                        <a href="update_status.php?id=<?= $review['id'] ?>&status=pending" class="btn btn-sm btn-secondary">Bỏ duyệt</a>
                    <?php endif; ?>
                    <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');">
                        <input type="hidden" name="id" value="<?= $review['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Đánh giá Sản phẩm';
include '../layout.php';
?>