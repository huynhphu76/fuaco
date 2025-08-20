<?php
// Tệp: /index.php (Phiên bản cuối cùng đã nâng cấp router)

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nạp các file cấu hình và helper cần thiết
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

// Thiết lập ngôn ngữ
if (isset($_GET['lang']) && in_array($_GET['lang'], ['vi', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$language_code = $_SESSION['lang'] ?? 'vi';


// --- HỆ THỐNG ĐIỀU HƯỚNG (ROUTER) NÂNG CẤP ---
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url_parts = explode('/', $url);

$page_file = '';
$params = [];

// Định nghĩa các tuyến đường CỐ ĐỊNH (luôn được ưu tiên kiểm tra trước)
// ĐÃ XÓA /ve-chung-toi ra khỏi đây để router tự động tìm
$routes = [
    ''              => 'home.php',
    'san-pham'      => 'product_list.php',
    'cart'          => 'cart.php',
    'checkout'      => 'checkout.php',
    'order-success' => 'order_success.php',
    'lien-he'       => 'contact.php',
    'bai-viet'      => 'blog_list.php',
    'du-an'         => 'project_list.php',
    'tuyen-dung'    => 'recruitment_list.php',
        'so-sanh'       => 'compare.php',
         'dang-nhap'         => 'login.php',       // <-- THÊM MỚI
    'dang-ky'           => 'register.php',    // <-- THÊM MỚI
    'dang-xuat'         => 'logout.php',      // <-- THÊM MỚI
    'tai-khoan'         => 'account.php',     // <-- THÊM MỚI
    'quen-mat-khau'     => 'forgot_password.php',
    'dat-lai-mat-khau'  => 'reset_password.php',
        'tai-khoan/yeu-thich' => 'wishlist.php', // <-- THÊM MỚI


];

// 1. Kiểm tra các tuyến đường cố định
if (isset($routes[$url])) {
    $page_file = __DIR__ . '/pages/' . $routes[$url];
} 

// 2. Xử lý các tuyến đường động (chi tiết sản phẩm, blog, dự án)
else if (isset($url_parts[0]) && $url_parts[0] == 'san-pham' && isset($url_parts[1])) {
    $page_file = __DIR__ . '/pages/product_detail.php';
    $params['slug'] = $url_parts[1];
} 
else if (isset($url_parts[0]) && $url_parts[0] == 'bai-viet' && isset($url_parts[1])) {
    $page_file = __DIR__ . '/pages/blog_detail.php';
    $params['slug'] = $url_parts[1];
}
else if (isset($url_parts[0]) && $url_parts[0] == 'du-an' && isset($url_parts[1])) {
    $page_file = __DIR__ . '/pages/project_detail.php';
    $params['slug'] = $url_parts[1];
}
else if (isset($url_parts[0]) && $url_parts[0] == 'tuyen-dung' && isset($url_parts[1])) {
    $page_file = __DIR__ . '/pages/recruitment_detail.php';
    $params['slug'] = $url_parts[1];
}
// 3. TỰ ĐỘNG TÌM KIẾM CÁC TRANG TĨNH KHÁC TRONG DATABASE
else {
    // Thêm dấu / vào đầu slug để khớp với dữ liệu trong CSDL
    $slug_to_check = '/' . $url; 
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND is_published = 1");
    $stmt->execute([$slug_to_check]);
    if ($stmt->fetch()) {
        // Nếu tìm thấy, sử dụng giao diện chung 'page_view.php'
        $page_file = __DIR__ . '/pages/page_view.php'; 
        $params['slug'] = $url; // Truyền slug vào cho trang page_view
    } else {
        // 4. Nếu không tìm thấy ở đâu cả, trả về 404
        $page_file = __DIR__ . '/pages/404.php';
        http_response_code(404);
    }
}
if ($url === 'dang-xuat') {
    include __DIR__ . '/pages/logout.php';
    exit; // Dừng lại ngay lập tức, không nạp layout
}

// Kiểm tra file tồn tại lần cuối
if (!file_exists($page_file)) {
    $page_file = __DIR__ . '/pages/404.php';
    http_response_code(404);
}

// Nạp file layout chính
include __DIR__ . '/templates/layout.php';