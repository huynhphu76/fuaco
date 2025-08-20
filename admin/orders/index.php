<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Kiểm tra quyền 'view-orders'
if (!hasPermission('view-orders')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
// ======================================================

$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt me-2"></i>Danh sách đơn hàng</h2>
    </div>

    <table class="table table-bordered table-striped align-middle table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Email / SĐT</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th style="width: 120px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td>
                        <?= htmlspecialchars($order['email']) ?><br>
                        <?= htmlspecialchars($order['phone']) ?>
                    </td>
                    <td><?= htmlspecialchars($order['address']) ?></td>
                    <td>
                        <span class="badge text-bg-info text-uppercase"><?= $order['status'] ?></span>
                    </td>
                    <td><?= number_format($order['total_price'] ?? 0, 0, ',', '.') ?>₫</td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td>
                        <a href="details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                        
                        <?php if (hasPermission('edit-orders')): ?>
                            <a href="edit.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-warning" title="Sửa trạng thái"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>

                        <?php if (hasPermission('delete-orders')): ?>
                            <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý đơn hàng';
include '../layout.php';
?>