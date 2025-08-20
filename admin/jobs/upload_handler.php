<?php
// Tệp: admin/jobs/upload_handler.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('manage-recruitment')) {
    http_response_code(403);
    echo json_encode(['error' => ['message' => 'Bạn không có quyền thực hiện hành động này.']]);
    exit;
}

$upload_dir = __DIR__ . '/../../uploads/jobs/content/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $temp_name = $_FILES['file']['tmp_name'];
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $temp_name);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        http_response_code(415);
        echo json_encode(['error' => ['message' => 'Loại file không được hỗ trợ.']]);
        exit;
    }

    $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('job_content_', true) . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($temp_name, $file_path)) {
        $location = BASE_URL . 'uploads/jobs/content/' . $file_name;
        echo json_encode(['location' => $location]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => ['message' => 'Không thể di chuyển file đã tải lên.']]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => ['message' => 'Lỗi tải lên hoặc không có file nào được chọn.']]);
}
?>