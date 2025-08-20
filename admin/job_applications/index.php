<?php
// Tệp: admin/job_applications/index.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-recruitment')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';

$job_id = filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT);

// Lấy tên tất cả vị trí tuyển dụng để làm bộ lọc
$all_jobs_stmt = $pdo->query("SELECT j.id, jt.title FROM jobs j JOIN job_translations jt ON j.id = jt.job_id WHERE jt.language_code = 'vi' ORDER BY jt.title ASC");
$all_jobs = $all_jobs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách ứng viên
$where_clause = $job_id ? "WHERE a.job_id = :job_id" : "";
$params = $job_id ? [':job_id' => $job_id] : [];

$stmt = $pdo->prepare("SELECT a.*, jt.title as job_title FROM job_applications a JOIN job_translations jt ON a.job_id = jt.job_id AND jt.language_code = 'vi' $where_clause ORDER BY a.submitted_at DESC");
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4"><i class="fas fa-users me-2"></i>Hồ sơ Ứng tuyển</h2>
    <form method="GET" class="mb-4">
        <div class="input-group" style="max-width: 400px;">
            <select name="job_id" class="form-select">
                <option value="">-- Lọc theo vị trí --</option>
                <?php foreach ($all_jobs as $job): ?>
                    <option value="<?= $job['id'] ?>" <?= ($job_id == $job['id']) ? 'selected' : '' ?>><?= htmlspecialchars($job['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Lọc</button>
        </div>
    </form>
    
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Vị trí ứng tuyển</th>
                <th>Ứng viên</th>
                <th>Thông tin liên hệ</th>
                <th>CV</th>
                <th>Ngày nộp</th>
                <th style="width: 80px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
             <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?= htmlspecialchars($app['job_title']) ?></td>
                    <td><?= htmlspecialchars($app['applicant_name']) ?></td>
                    <td>
                        Email: <?= htmlspecialchars($app['applicant_email']) ?><br>
                        SĐT: <?= htmlspecialchars($app['applicant_phone']) ?>
                    </td>
                    <td><a href="/interior-website/<?= htmlspecialchars($app['cv_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info">Xem CV</a></td>
                    <td><?= date('d/m/Y H:i', strtotime($app['submitted_at'])) ?></td>
                    <td>
    <form action="delete.php" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa hồ sơ này?')">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <button type="submit" class="btn btn-sm btn-danger" title="Xóa hồ sơ">
            <i class="fas fa-trash-alt"></i>
        </button>
    </form>
</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Hồ sơ Ứng tuyển';
include '../layout.php';
?>