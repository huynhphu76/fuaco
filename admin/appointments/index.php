<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Kiểm tra quyền 'manage-appointments'
if (!hasPermission('manage-appointments')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
// ======================================================

$stmt = $pdo->query("SELECT * FROM appointments ORDER BY date_time DESC");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-check me-2"></i>Quản lý lịch hẹn</h2>
        <a href="create.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Thêm lịch hẹn
        </a>
    </div>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tên khách</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Ngày & Giờ</th>
                <th>Ghi chú</th>
                <th>Trạng thái</th>
                <th style="width: 100px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="8" class="text-center">Chưa có lịch hẹn nào.</td></tr>
            <?php else: ?>
                <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?= $appt['id'] ?></td>
                        <td><?= htmlspecialchars($appt['name']) ?></td>
                        <td><?= htmlspecialchars($appt['phone']) ?></td>
                        <td><?= htmlspecialchars($appt['email']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($appt['date_time'])) ?></td>
                        <td><?= htmlspecialchars($appt['note']) ?></td>
                        <td>
                            <?php
                            $statusClass = match ($appt['status']) {
                                'pending' => 'secondary', 'confirmed' => 'success', 'cancelled' => 'danger', default => 'light'
                            };
                            ?>
                            <span class="badge text-bg-<?= $statusClass ?> text-uppercase"><?= $appt['status'] ?></span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa lịch hẹn này?')">
                                <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
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
$pageTitle = 'Lịch hẹn';
include '../layout.php';
?>