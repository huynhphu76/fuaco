<?php
// Tệp: admin/pages/upload_handler.php (Phiên bản cuối cùng)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('manage-pages')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$upload_dir = __DIR__ . '/../../uploads/pages/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $temp_name = $_FILES['file']['tmp_name'];
    $file_name = uniqid('page_img_', true) . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($temp_name, $file_path)) {
        $location = BASE_URL . 'uploads/pages/' . $file_name;
        echo json_encode(['location' => $location]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Could not move uploaded file.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error.']);
}