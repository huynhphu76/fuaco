<?php
// Tệp: admin/feedbacks/handler.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../index.php';


// Kiểm tra quyền và phương thức POST
if (!hasPermission('manage-feedbacks') || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Truy cập bị từ chối.');
}

// Xử lý hành động XÓA
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['toast_message'] = "Đã xóa phản hồi thành công.";
    } catch (PDOException $e) {
        $_SESSION['toast_error'] = "Lỗi khi xóa phản hồi: " . $e->getMessage();
    }
}

// Xử lý hành động DUYỆT/ẨN
if (isset($_POST['action']) && $_POST['action'] === 'toggle_approval' && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        // Lấy trạng thái hiện tại
        $stmt = $pdo->prepare("SELECT is_approved FROM feedbacks WHERE id = ?");
        $stmt->execute([$id]);
        $current_status = $stmt->fetchColumn();

        // Đảo ngược trạng thái
        $new_status = !$current_status;

        // Cập nhật trạng thái mới
        $update_stmt = $pdo->prepare("UPDATE feedbacks SET is_approved = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $id]);
        $_SESSION['toast_message'] = "Đã cập nhật trạng thái phản hồi.";

    } catch (PDOException $e) {
        $_SESSION['toast_error'] = "Lỗi khi cập nhật trạng thái: " . $e->getMessage();
    }
}

// Chuyển hướng về trang quản lý
header('Location: ' . BASE_URL . 'admin/feedbacks');
exit();