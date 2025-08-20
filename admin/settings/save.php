<?php
// Tệp: admin/settings/save.php (HOÀN CHỈNH - ĐÃ SỬA LỖI)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // Thêm vào

require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-settings')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    verify_csrf_token(); // Thêm vào

    header('Location: index.php');
    exit;
}
// Thêm CSRF token nếu cần

try {
    $pdo->beginTransaction();

    // 1. LƯU CÁC CÀI ĐẶT KHÔNG DỊCH (VÀO BẢNG `settings`)
    if (isset($_POST['settings']) && is_array($_POST['settings'])) {
        $stmt_setting = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($_POST['settings'] as $key => $value) {
            $stmt_setting->execute([$key, $value]);
        }
    }

    // 2. LƯU CÁC CÀI ĐẶT CẦN DỊCH (VÀO BẢNG `setting_translations`)
    if (isset($_POST['translations']) && is_array($_POST['translations'])) {
        $stmt_trans = $pdo->prepare("INSERT INTO setting_translations (setting_key, language_code, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($_POST['translations'] as $lang_code => $settings) {
            // Đảm bảo chỉ lưu các trường hợp lệ: site_name và address
            $allowed_trans_keys = ['site_name', 'address'];
            foreach ($settings as $key => $value) {
                if (in_array($key, $allowed_trans_keys)) {
                    $stmt_trans->execute([$key, $lang_code, $value]);
                }
            }
        }
    }
    
    $pdo->commit();
    logAction($pdo, $_SESSION['user_id'] ?? null, "Cập nhật cài đặt trang web.");

} catch (Exception $e) {
    $pdo->rollBack();
    die("Lỗi khi lưu cài đặt: " . $e->getMessage());
}

header('Location: index.php?success=1');
exit;
?>