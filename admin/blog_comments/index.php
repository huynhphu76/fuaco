<?php
// Tệp: admin/blog_comments/index.php (HOÀN CHỈNH - ĐÃ SỬA LỖI)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('manage-blogs')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

// Lọc bình luận theo trạng thái
$filter = $_GET['filter'] ?? 'all';
$where_clause = '';
$params = [];
if (in_array($filter, ['pending', 'approved'])) {
    $where_clause = "WHERE c.status = :status";
    $params[':status'] = $filter;
}

$language_code = 'vi'; // Lấy tiêu đề bài viết theo tiếng Việt

// === SỬA LỖI SQL TẠI ĐÂY ===
// Thêm LEFT JOIN với bảng blog_translations để lấy tiêu đề
$sql = "
    SELECT c.*, bt.title as post_title 
    FROM blog_comments c
    JOIN blogs b ON c.post_id = b.id
    LEFT JOIN blog_translations bt ON b.id = bt.blog_id AND bt.language_code = :lang_code
    $where_clause
    ORDER BY c.created_at DESC
";
$stmt = $pdo->prepare($sql);
// Thêm tham số cho language_code
$params[':lang_code'] = $language_code;
$stmt->execute($params);
$comments = $stmt->fetchAll();

ob_start();
?>
<style>
    /* CSS tùy chỉnh cho giao diện bình luận */
    .comment-card { border-left-width: 4px; }
    .comment-card.status-pending { border-left-color: #f6c23e; }
    .comment-card.status-approved { border-left-color: #1cc88a; }
    .comment-content {
        max-height: 75px;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .comment-content.expanded { max-height: 1000px; }
    .read-more { cursor: pointer; color: #4e73df; font-weight: bold; }
</style>

<div class="dashboard container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-comments me-2"></i>Quản lý Bình luận</h1>
    </div>

    <div class="mb-3">
        <a href="index.php?filter=all" class="btn btn-sm <?= $filter == 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">Tất cả</a>
        <a href="index.php?filter=pending" class="btn btn-sm <?= $filter == 'pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">Chờ duyệt</a>
        <a href="index.php?filter=approved" class="btn btn-sm <?= $filter == 'approved' ? 'btn-success' : 'btn-outline-secondary' ?>">Đã duyệt</a>
    </div>

    <?php if (empty($comments)): ?>
        <div class="alert alert-info">Không có bình luận nào phù hợp.</div>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="card shadow-sm mb-3 comment-card status-<?= $comment['status'] ?>">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-user fa-2x text-gray-400"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">
                                <?= htmlspecialchars($comment['author_name']) ?>
                                <small class="text-muted fw-normal">- <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                            </h5>
                            <p class="mb-2 small">Bình luận về bài viết: 
                                <a href="../blogs/edit.php?id=<?= $comment['post_id'] ?>" target="_blank">
                                    "<?= htmlspecialchars($comment['post_title'] ?? '[Bài viết không có tiêu đề]') ?>"
                                </a>
                            </p>
                            
                            <div class="comment-content mb-2" id="comment-<?= $comment['id'] ?>">
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </div>
                            <?php if (strlen($comment['content']) > 200): // Hiển thị nút nếu nội dung dài ?>
                                <span class="read-more small" onclick="toggleComment(this, <?= $comment['id'] ?>)">Xem thêm</span>
                            <?php endif; ?>

                            <div class="mt-2">
                                <?php if ($comment['status'] == 'pending'): ?>
                                    <a href="update_status.php?id=<?= $comment['id'] ?>&status=approved" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Duyệt</a>
                                <?php else: ?>
                                    <a href="update_status.php?id=<?= $comment['id'] ?>&status=pending" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Bỏ duyệt</a>
                                <?php endif; ?>
                                <a href="delete.php?id=<?= $comment['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bình luận này?')"><i class="fas fa-trash"></i> Xóa</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleComment(button, commentId) {
    const content = document.getElementById('comment-' + commentId);
    content.classList.toggle('expanded');
    if (content.classList.contains('expanded')) {
        button.textContent = 'Rút gọn';
    } else {
        button.textContent = 'Xem thêm';
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Bình luận';
include '../layout.php';
?>