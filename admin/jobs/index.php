<?php
// Tệp: admin/jobs/index.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-recruitment')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token(); // Đảm bảo token được tạo cho form    

$language_code = 'vi'; // Luôn lấy bản dịch tiếng Việt để hiển thị trong admin
$stmt = $pdo->prepare("
    SELECT 
        j.id, 
        j.is_active, 
        j.created_at, 
        jt.title, 
        j.location, 
        j.salary,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as application_count
    FROM jobs j
    LEFT JOIN job_translations jt ON j.id = jt.job_id AND jt.language_code = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$language_code]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-briefcase me-2"></i>Quản lý Vị trí Tuyển dụng</h2>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Đăng tin mới</a>
    </div>
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Chức danh</th>
                <th>Địa điểm</th>
                <th>Mức lương</th>
                <th>Ứng viên</th>
                <th>Trạng thái</th>
                <th>Ngày đăng</th>
                <th style="width: 150px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($job['title'] ?? '[Chưa có tên]') ?></strong></td>
                    <td><?= htmlspecialchars($job['location']) ?></td>
                    <td><?= htmlspecialchars($job['salary']) ?></td>
                    <td><span class="badge text-bg-info"><?= $job['application_count'] ?></span></td>
                    <td>
                        <span class="badge <?= $job['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= $job['is_active'] ? 'Đang tuyển' : 'Ngừng tuyển' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($job['created_at'])) ?></td>
                    <td>
                        <a href="../job_applications/index.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-info" title="Xem ứng viên"><i class="fas fa-users"></i></a>
                        <a href="edit.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                       <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin tuyển dụng này?');">
    <input type="hidden" name="id" value="<?= $job['id'] ?>">
    <?php csrf_field(); // Thêm token vào form ?>
    <button type="submit" class="btn btn-sm btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
</form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Tuyển dụng';
include '../layout.php';
?>