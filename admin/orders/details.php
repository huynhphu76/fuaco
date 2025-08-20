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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) { die("Không tìm thấy đơn hàng."); }

$stmtItems = $pdo->prepare("
    SELECT oi.*, pt.name
    FROM order_items oi
    JOIN product_translations pt ON oi.product_id = pt.product_id
    WHERE oi.order_id = ? AND pt.language_code = 'vi'
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-3">Chi tiết đơn hàng #<?= $order['id'] ?></h2>
    <div class="card">
        <div class="card-header">Thông tin khách hàng</div>
        <div class="card-body">
            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Phương thức thanh toán:</strong> <span class="text-primary fw-bold"><?= htmlspecialchars($order['payment_method'] == 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản') ?></span></p>
            <p><strong>Trạng thái:</strong> <span class="badge text-bg-primary text-uppercase"><?= $order['status'] ?></span></p>
            <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
            <p class="fs-5"><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?= number_format($order['total_price']) ?>₫</span></p>
        </div>
    </div>
    <h4 class="mt-4">Sản phẩm trong đơn hàng</h4>
    <?php if ($items && count($items) > 0): ?>
        <table class="table table-bordered mt-3">
            <thead><tr><th>Tên sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price']) ?>₫</td>
                        <td><?= number_format($item['quantity'] * $item['price']) ?>₫</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Không có sản phẩm trong đơn hàng.</p>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-3">Quay lại danh sách</a>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Chi tiết đơn hàng';
include '../layout.php';
?>