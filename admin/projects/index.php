<?php
// Tệp: admin/projects/index.php (HOÀN CHỈNH)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-projects')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

$language_code = 'vi';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$projects_per_page = 10;
$offset = ($page - 1) * $projects_per_page;
$search_term = $_GET['search'] ?? '';

// Xây dựng câu lệnh SQL có JOIN và tìm kiếm
$where_clause = "WHERE pt.language_code = :lang_code";
$params = [':lang_code' => $language_code];
if (!empty($search_term)) {
    $where_clause .= " AND pt.title LIKE :search_term";
    $params[':search_term'] = "%" . $search_term . "%";
}

$count_sql = "SELECT COUNT(p.id) FROM projects p JOIN project_translations pt ON p.id = pt.project_id $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_projects = $count_stmt->fetchColumn();
$total_pages = ceil($total_projects / $projects_per_page);

$sql = "
    SELECT p.id, p.thumbnail, p.completed_at, p.created_at, pt.title
    FROM projects p
    JOIN project_translations pt ON p.id = pt.project_id
    $where_clause
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => &$value) {
    $stmt->bindParam($key, $value);
}
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-project-diagram me-2"></i>Dự án thi công</h2>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Thêm dự án</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php">
                <div class="input-group"><input type="text" name="search" class="form-control" placeholder="Tìm theo tiêu đề dự án (Tiếng Việt)..." value="<?= htmlspecialchars($search_term) ?>"></div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle table-hover">
            <thead class="table-dark">
                <tr><th>ID</th><th>Hình đại diện</th><th>Tiêu đề (VI)</th><th>Ngày hoàn thành</th><th>Ngày tạo</th><th style="width: 170px;">Hành động</th></tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="6" class="text-center">Không tìm thấy dự án nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?= $project['id'] ?></td>
                            <td>
                                <?php if (!empty($project['thumbnail'])): ?>
                                    <img src="/interior-website/uploads/projects/<?= htmlspecialchars($project['thumbnail']) ?>" width="80" class="img-fluid rounded">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($project['title'] ?? '[Chưa có bản dịch]') ?></td>
                            <td><?= $project['completed_at'] ? date('d/m/Y', strtotime($project['completed_at'])) : 'N/A' ?></td>
                            <td><?= $project['created_at'] ? date('d/m/Y H:i', strtotime($project['created_at'])) : 'N/A' ?></td>
                            <td>
                                <a href="../project_images/index.php?project_id=<?= $project['id'] ?>" class="btn btn-info btn-sm" title="Thư viện ảnh"><i class="fas fa-images"></i> Ảnh</a>
                                <a href="edit.php?id=<?= $project['id'] ?>" class="btn btn-warning btn-sm" title="Sửa"><i class="fas fa-edit"></i></a>
                                <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dự án này?')"><input type="hidden" name="id" value="<?= $project['id'] ?>">    <?php csrf_field(); // Thêm dòng này ?>
<button type="submit" class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash-alt"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav><ul class="pagination justify-content-center"> ... (Phần phân trang giữ nguyên) ... </ul></nav>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý dự án';
include '../layout.php';
?>