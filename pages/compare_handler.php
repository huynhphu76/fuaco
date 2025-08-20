<?php
// Tệp: pages/compare_handler.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/constants.php';

// Khởi tạo danh sách so sánh trong session nếu chưa có
if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}

$action = $_POST['action'] ?? null;
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$MAX_ITEMS = 3;

if ($action === 'add' && $product_id) {
    if (count($_SESSION['compare_list']) < $MAX_ITEMS) {
        // Thêm sản phẩm nếu chưa có
        if (!in_array($product_id, $_SESSION['compare_list'])) {
            $_SESSION['compare_list'][] = $product_id;
        }
    } else {
        // Có thể thêm thông báo lỗi ở đây nếu muốn
    }
}

if ($action === 'remove' && $product_id) {
    $_SESSION['compare_list'] = array_filter($_SESSION['compare_list'], function($id) use ($product_id) {
        return $id != $product_id;
    });
}

if ($action === 'clear') {
    $_SESSION['compare_list'] = [];
}

// Chuyển hướng người dùng về trang trước đó
$redirect_url = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
header('Location: ' . $redirect_url);
exit;
?>