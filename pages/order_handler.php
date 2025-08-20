<?php
// Tệp: pages/order_handler.php (Phiên bản cuối cùng, chống lặp đơn hàng)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// --- Bắt đầu xử lý ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ' . BASE_URL);
    exit;
}

// Lấy thông tin khách hàng từ form
$customer_name = trim(filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING));
$payment_method = $_POST['payment_method'] ?? 'cod';
$customer_id = $_SESSION['customer']['id'] ?? null;

// Validate thông tin cơ bản
if (empty($customer_name) || empty($email) || empty($phone) || empty($address)) {
    // Nếu thiếu thông tin, quay lại trang thanh toán
    header('Location: ' . BASE_URL . 'checkout');
    exit;
}

try {
    $pdo->beginTransaction();

    // Tính toán lại tổng tiền ở backend để đảm bảo an toàn
    $total_price = 0;
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt_products = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt_products->execute($product_ids);
    $products_in_cart = $stmt_products->fetchAll(PDO::FETCH_KEY_PAIR);

    $order_items_to_insert = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products_in_cart[$product_id])) {
            $price = $products_in_cart[$product_id];
            $total_price += $price * $quantity;
            $order_items_to_insert[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price
            ];
        }
    }
    
    // 1. Chèn vào bảng `orders` (chỉ một lần)
    $stmt_order = $pdo->prepare(
        "INSERT INTO orders (customer_id, customer_name, email, phone, address, total_price, payment_method, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt_order->execute([$customer_id, $customer_name, $email, $phone, $address, $total_price, $payment_method, 'pending']);
    $order_id = $pdo->lastInsertId();

    // 2. Chèn các sản phẩm của đơn hàng vào bảng `order_items`
    $stmt_items = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
    );
    foreach ($order_items_to_insert as $item) {
        $stmt_items->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }
    
    // Hoàn tất giao dịch
    $pdo->commit();

    // Xóa giỏ hàng và chuyển hướng
    unset($_SESSION['cart']);
    header('Location: ' . BASE_URL . 'order-success?id=' . $order_id);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    // Ghi lại lỗi để bạn có thể xem (quan trọng cho việc gỡ lỗi)
    error_log('Order failed: ' . $e->getMessage());
    // Chuyển hướng với thông báo lỗi chung
    header('Location: ' . BASE_URL . 'checkout?error=1');
    exit;
}

?>