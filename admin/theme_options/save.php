<?php
// Tệp: admin/theme_options/save.php (Tệp mới)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-theme')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm vào


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    verify_csrf_token(); // Thêm vào

    header('Location: index.php');
    exit;
}
// Thêm CSRF token nếu cần

try {
    $pdo->beginTransaction();

    // 1. LƯU CÁC CÀI ĐẶT KHÔNG DỊCH (VÀO BẢNG `theme_options`)
    if (isset($_POST['options']) && is_array($_POST['options'])) {
        $stmt_option = $pdo->prepare("INSERT INTO theme_options (option_key, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
        foreach ($_POST['options'] as $key => $value) {
            $stmt_option->execute([$key, $value]);
        }
    }

    // 2. LƯU CÁC CÀI ĐẶT CẦN DỊCH (VÀO BẢNG `theme_option_translations`)
    if (isset($_POST['translations']) && is_array($_POST['translations'])) {
        $stmt_trans = $pdo->prepare("INSERT INTO theme_option_translations (option_key, language_code, option_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
        foreach ($_POST['translations'] as $lang_code => $options) {
            foreach ($options as $key => $value) {
                $stmt_trans->execute([$key, $lang_code, $value]);
            }
        }
    }
    
    // 3. XỬ LÝ UPLOAD FILE ẢNH
    $upload_dir = __DIR__ . '/../../uploads/theme/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
    
$file_uploads = [
    'logo_light' => $_FILES['logo_light'] ?? null,
    'logo_dark' => $_FILES['logo_dark'] ?? null,
    'favicon' => $_FILES['favicon'] ?? null,
    'banner_image' => $_FILES['banner_image'] ?? null,
    'appointment_section_image' => $_FILES['appointment_section_image'] ?? null
];
    
    $stmt_file = $pdo->prepare("INSERT INTO theme_options (option_key, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
    
    foreach ($file_uploads as $key => $file) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            // Xóa file cũ nếu có
            $old_file_stmt = $pdo->prepare("SELECT option_value FROM theme_options WHERE option_key = ?");
            $old_file_stmt->execute([$key]);
            $old_file_path = $old_file_stmt->fetchColumn();
            if ($old_file_path && file_exists(__DIR__ . '/../../' . $old_file_path)) {
                @unlink(__DIR__ . '/../../' . $old_file_path);
            }

            // Upload file mới
            $filename = uniqid($key . '_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $db_path = 'uploads/theme/' . $filename;
                $stmt_file->execute([$key, $db_path]);
            }
        }
    }

    $pdo->commit();
    logAction($pdo, $_SESSION['user_id'] ?? null, "Cập nhật Tùy biến Giao diện.");

} catch (Exception $e) {
    $pdo->rollBack();
    die("Lỗi khi lưu cài đặt: " . $e->getMessage());
}

header('Location: index.php?success=1');
exit;
?>