<?php
// Tệp: admin/admin_logs/index.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

// Bảo mật: Yêu cầu quyền 'view-logs'
if (!hasPermission('view-logs')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

// Xử lý xóa các log đã chọn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $log_ids_to_delete = $_POST['log_ids'] ?? [];
    if (!empty($log_ids_to_delete)) {
        $placeholders = implode(',', array_fill(0, count($log_ids_to_delete), '?'));
        $stmt = $pdo->prepare("DELETE FROM admin_logs WHERE id IN ($placeholders)");
        $stmt->execute($log_ids_to_delete);
        // Không ghi log khi xóa log
    }
    header("Location: index.php");
    exit;
}

// Logic tìm kiếm
$search_term = $_GET['search'] ?? '';
$where_clause = '';
$params = [];
if (!empty($search_term)) {
    $where_clause = "WHERE u.name LIKE :search_name OR a.action LIKE :search_action";
    $params[':search_name'] = "%" . $search_term . "%";
    $params[':search_action'] = "%" . $search_term . "%";
}

$sql = "SELECT a.id, a.action, a.timestamp, u.name as user_name, u.id as user_id
        FROM admin_logs a
        LEFT JOIN users u ON a.user_id = u.id
        $where_clause
        ORDER BY a.timestamp DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-history me-2"></i>Nhật ký hoạt động của Admin</h2>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên người dùng hoặc hành động..." value="<?= htmlspecialchars($search_term) ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa các mục đã chọn?')">
        <button type="submit" name="delete_selected" class="btn btn-danger mb-3"><i class="fas fa-trash-alt"></i> Xóa mục đã chọn</button>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;" class="text-center"><input type="checkbox" id="select-all"></th>
                        <th>Người thực hiện</th>
                        <th>Hành động</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" name="log_ids[]" value="<?= $log['id'] ?>" class="log-checkbox"></td>
                            <td>
                                <?php if ($log['user_name']): ?>
                                    <a href="../users/edit.php?id=<?= $log['user_id'] ?>"><?= htmlspecialchars($log['user_name']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Người dùng đã bị xóa (ID: <?= $log['user_id'] ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['timestamp'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function(e) {
    document.querySelectorAll('.log-checkbox').forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Nhật ký hoạt động';
include '../layout.php';
?>