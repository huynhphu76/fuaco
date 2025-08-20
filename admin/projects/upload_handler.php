<?php
// Tệp: admin/projects/upload_handler.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Giả định rằng bạn đã có file constants.php để lấy BASE_URL
require_once __DIR__ . '/../../config/constants.php'; 
// Kiểm tra quyền (quan trọng)
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-projects')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Thư mục để lưu ảnh chèn vào nội dung dự án
// Tạo một thư mục con 'content' để phân biệt với ảnh đại diện
$upload_dir = __DIR__ . '/../../uploads/projects/content/';
if (!is_dir($upload_dir)) {
    // @mkdir để tránh lỗi nếu thư mục đã tồn tại
    @mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $temp_name = $_FILES['file']['tmp_name'];
    
    // Tạo tên file ngẫu nhiên, duy nhất để tránh trùng lặp
    $file_name = uniqid('project_content_', true) . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $file_path = $upload_dir . $file_name;

    // Di chuyển file đã tải lên vào thư mục chỉ định
    if (move_uploaded_file($temp_name, $file_path)) {
        // TinyMCE cần nhận về một JSON chứa đường dẫn đầy đủ của ảnh
        $location = BASE_URL . 'uploads/projects/content/' . $file_name;
        echo json_encode(['location' => $location]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Could not move uploaded file.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or an upload error occurred.']);
}
?>