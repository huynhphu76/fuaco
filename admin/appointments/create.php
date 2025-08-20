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
require_once __DIR__ . '/../../helpers/log_action.php';
// ======================================================

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date_time = trim($_POST['date_time'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $status = $_POST['status'] ?? 'pending';

    if (!$name) $errors['name'] = 'Vui lòng nhập tên khách hàng';
    if (!$phone) $errors['phone'] = 'Vui lòng nhập số điện thoại';
    if (!$date_time) $errors['date_time'] = 'Vui lòng chọn ngày giờ hẹn';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO appointments (name, phone, email, date_time, note, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $date_time, $note, $status]);
        
        $appointment_id = $pdo->lastInsertId();
        logAction($pdo, $_SESSION['user_id'], "Tạo lịch hẹn mới #{$appointment_id} cho khách '{$name}'");
        
        header('Location: index.php');
        exit;
    }
}

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus me-2"></i>Thêm lịch hẹn mới</h2>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= $err ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6"><label for="name" class="form-label">Tên khách hàng</label><input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></div>
        <div class="col-md-6"><label for="phone" class="form-label">Số điện thoại</label><input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required></div>
        <div class="col-md-6"><label for="email" class="form-label">Email (tuỳ chọn)</label><input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
        <div class="col-md-6"><label for="date_time" class="form-label">Ngày & Giờ hẹn</label><input type="datetime-local" class="form-control" id="date_time" name="date_time" value="<?= htmlspecialchars($_POST['date_time'] ?? '') ?>" required></div>
        <div class="col-12"><label for="note" class="form-label">Ghi chú</label><textarea class="form-control" id="note" name="note" rows="3"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea></div>
        <div class="col-md-6"><label for="status" class="form-label">Trạng thái</label><select class="form-select" name="status" id="status"><option value="pending" selected>Chờ xác nhận</option><option value="confirmed">Đã xác nhận</option><option value="cancelled">Đã hủy</option></select></div>
        <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu</button></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Thêm lịch hẹn mới';
include '../layout.php';
?>