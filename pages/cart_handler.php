<?php
// Tệp: /pages/cart_handler.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
// --- DỮ LIỆU SONG NGỮ CHO THÔNG BÁO ---
$language_code = $_SESSION['lang'] ?? 'vi';
$translations = [
    'vi' => [
        'added_to_cart' => 'Đã thêm sản phẩm vào giỏ hàng!',
        'out_of_stock' => 'Số lượng tồn kho không đủ!',
        'invalid_data' => 'Dữ liệu không hợp lệ.'
    ],
    'en' => [
        'added_to_cart' => 'Product added to cart!',
        'out_of_stock' => 'Not enough stock available!',
        'invalid_data' => 'Invalid data.'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---
header('Content-Type: application/json');

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$product_id = (int)($_POST['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

try {
    switch ($action) {
        case 'add':
            if ($product_id > 0 && $quantity > 0) {
                // Kiểm tra số lượng tồn kho
                $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $stock = $stmt->fetchColumn();

                $current_cart_qty = $_SESSION['cart'][$product_id] ?? 0;
                
                if ($stock !== false && ($current_cart_qty + $quantity) <= $stock) {
                    $_SESSION['cart'][$product_id] = $current_cart_qty + $quantity;
                  echo json_encode(['success' => true, 'message' => $lang['added_to_cart'], 'cart_item_count' => count($_SESSION['cart'])]);
                } else {
                   echo json_encode(['success' => false, 'message' => $lang['out_of_stock']]);
                }
            } else {
                 echo json_encode(['success' => false, 'message' => $lang['invalid_data']]);
            }
            break;

        case 'update':
            if ($product_id > 0 && array_key_exists($product_id, $_SESSION['cart'])) {
                 if ($quantity > 0) {
                    $_SESSION['cart'][$product_id] = $quantity;
                 } else {
                    unset($_SESSION['cart'][$product_id]); // Xóa nếu số lượng là 0
                 }
                 echo json_encode(['success' => true, 'message' => 'Cập nhật giỏ hàng thành công.']);
            }
            break;

        case 'remove':
             if ($product_id > 0 && array_key_exists($product_id, $_SESSION['cart'])) {
                unset($_SESSION['cart'][$product_id]);
                echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm.', 'cart_item_count' => count($_SESSION['cart'])]);
            }
            break;
            
        case 'get_count':
            echo json_encode(['cart_item_count' => count($_SESSION['cart'])]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không được hỗ trợ.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()]);
}