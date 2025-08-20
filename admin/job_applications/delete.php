<?php
// Tệp: admin/job_applications/delete.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-recruitment')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    if ($id) {
        // Lấy thông tin hồ sơ TRƯỚC KHI xóa để ghi log và xóa file
        $stmt_select = $pdo->prepare("SELECT applicant_name, cv_path FROM job_applications WHERE id = ?");
        $stmt_select->execute([$id]);
        $application = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($application) {
            // Xóa hồ sơ khỏi cơ sở dữ liệu
            $stmt_delete = $pdo->prepare("DELETE FROM job_applications WHERE id = ?");
            $stmt_delete->execute([$id]);

            // Xóa file CV khỏi server để tiết kiệm dung lượng
            $cv_full_path = __DIR__ . '/../../../' . $application['cv_path'];
            if (file_exists($cv_full_path)) {
                @unlink($cv_full_path);
            }

            // Ghi lại hành động
            logAction($pdo, $_SESSION['user']['id'], "Xóa hồ sơ ứng tuyển #{$id} của '" . htmlspecialchars($application['applicant_name']) . "'");
        }
    }
}

header("Location: index.php");
exit;
?>