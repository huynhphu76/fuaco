<?php
// Tệp: admin/feedbacks/index.php (Đã cập nhật để hiển thị sao)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-feedbacks')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';

// Cập nhật câu lệnh SELECT để lấy thêm cột 'rating'
$stmt = $pdo->query("SELECT id, name, message, rating, created_at FROM feedbacks ORDER BY created_at DESC");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-comment-dots me-2"></i>Quản lý Phản hồi</h2>
        </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tên Khách hàng</th>
                <th>Nội dung</th>
                <th style="width: 120px;">Đánh giá</th> <th>Ngày gửi</th>
                <th style="width: 120px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($feedbacks)): ?>
                <tr><td colspan="6" class="text-center">Chưa có phản hồi nào.</td></tr>
            <?php else: ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <tr>
                        <td><?= $feedback['id'] ?></td>
                        <td><?= htmlspecialchars($feedback['name']) ?></td>
                        <td><?= nl2br(htmlspecialchars(substr($feedback['message'], 0, 100))) . '...' ?></td>
                        <td>
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= $feedback['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($feedback['created_at'])) ?></td>
                        <td>
                            <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa phản hồi này?')">
                                <input type="hidden" name="id" value="<?= $feedback['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Phản hồi';
include '../layout.php';
?>