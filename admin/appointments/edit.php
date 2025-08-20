<?php
// ======================================================
// KHỐI 1: XỬ LÝ LOGIC VÀ BẢO MẬT (ĐÃ CẬP NHẬT)
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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

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
        $stmt = $pdo->prepare("UPDATE appointments SET name=?, phone=?, email=?, date_time=?, note=?, status=? WHERE id=?");
        $stmt->execute([$name, $phone, $email, $date_time, $note, $status, $id]);
        logAction($pdo, $_SESSION['user_id'], "Cập nhật lịch hẹn #{$id} cho khách '{$name}'");
        header('Location: index.php');
        exit;
    }
}

// ======================================================
// KHỐI 2: LẤY DỮ LIỆU VÀ HIỂN THỊ HTML
// ======================================================
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$appointment) { die("Không tìm thấy lịch hẹn."); }

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Sửa lịch hẹn</h2>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= $err ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6"><label class="form-label">Tên khách hàng</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $appointment['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? $appointment['phone']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $appointment['email']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Ngày & Giờ hẹn</label><input type="datetime-local" name="date_time" class="form-control" value="<?= htmlspecialchars($_POST['date_time'] ?? date('Y-m-d\TH:i', strtotime($appointment['date_time']))) ?>" required></div>
        <div class="col-12"><label class="form-label">Ghi chú</label><textarea name="note" class="form-control"><?= htmlspecialchars($_POST['note'] ?? $appointment['note']) ?></textarea></div>
        <div class="col-md-6"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><?php $statuses = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy']; $current_status = $_POST['status'] ?? $appointment['status']; foreach ($statuses as $key => $label) { $selected_attr = ($current_status == $key) ? 'selected' : ''; echo "<option value=\"{$key}\" {$selected_attr}>" . htmlspecialchars($label) . "</option>"; } ?></select></div>
        <div class="col-12"><button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Cập nhật</button></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Sửa lịch hẹn';
include '../layout.php';
?>