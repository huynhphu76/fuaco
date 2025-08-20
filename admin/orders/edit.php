<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Kiểm tra quyền 'edit-orders'
if (!hasPermission('edit-orders')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
// ======================================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'pending';
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    logAction($pdo, $_SESSION['user_id'], "Cập nhật trạng thái đơn hàng #{$id} thành '{$status}'");
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { die("<div class='alert alert-danger'>Không tìm thấy đơn hàng.</div>"); }

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Cập nhật trạng thái đơn hàng #<?= $order['id'] ?></h2>
    </div>
    <form method="POST">
        <div class="mb-3"><label class="form-label fw-bold">Khách hàng</label><p class="form-control-plaintext"><?= htmlspecialchars($order['customer_name']) ?></p></div>
        <div class="mb-3"><label class="form-label fw-bold">Tổng tiền</label><p class="form-control-plaintext"><?= number_format($order['total_price'], 0, ',', '.') ?> đ</p></div>
        <div class="mb-3">
            <label class="form-label fw-bold">Trạng thái</label>
            <select name="status" class="form-select" required>
                <?php $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']; foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Cập nhật</button>
        <a href="index.php" class="btn btn-secondary ms-2">Quay lại</a>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Chỉnh sửa đơn hàng';
include '../layout.php';
?>