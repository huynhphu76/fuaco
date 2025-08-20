<?php
// Tệp: admin/blogs/upload_handler.php (Phiên bản cuối cùng, đã sửa lỗi)

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các file cần thiết
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

// Chỉ người dùng đã đăng nhập và có quyền mới được upload
if (!hasPermission('manage-blogs')) {
    http_response_code(403);
    echo json_encode(['error' => ['message' => 'Bạn không có quyền thực hiện hành động này.']]);
    exit;
}

// Cấu hình an toàn
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5 MB
$upload_dir = __DIR__ . '/../../uploads/blogs/content/';

// Tạo thư mục nếu chưa tồn tại
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => ['message' => 'Không thể tạo thư mục tải lên.']]);
        exit;
    }
}

// Kiểm tra file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => 'Lỗi tải lên hoặc không có file nào được chọn.']]);
    exit;
}

$temp_name = $_FILES['file']['tmp_name'];

// KIỂM TRA AN TOÀN
if ($_FILES['file']['size'] > $max_size) {
    http_response_code(413);
    echo json_encode(['error' => ['message' => 'Kích thước file quá lớn. Tối đa 5MB.']]);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $temp_name);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    http_response_code(415);
    echo json_encode(['error' => ['message' => 'Loại file không được hỗ trợ. Chỉ chấp nhận JPG, PNG, GIF, WEBP.']]);
    exit;
}

// Xử lý tên file và di chuyển
$file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$file_name = uniqid('blog_content_', true) . '.' . $file_extension;
$file_path = $upload_dir . $file_name;

if (move_uploaded_file($temp_name, $file_path)) {
    // Trả về URL đầy đủ, bao gồm cả http://...
    $location = BASE_URL . 'uploads/blogs/content/' . $file_name;
    echo json_encode(['location' => $location]);
} else {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'Không thể di chuyển file đã tải lên.']]);
}