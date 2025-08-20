<?php
// Tệp: pages/wishlist_handler.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Chức năng này yêu cầu đăng nhập
if (!isset($_SESSION['customer']['id'])) {
    // Nếu dùng AJAX, trả về lỗi JSON. Nếu không, chuyển hướng.
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'login_required']);
        exit;
    } else {
        header('Location: ' . BASE_URL . 'dang-nhap');
        exit;
    }
}

$customer_id = $_SESSION['customer']['id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? 'add';

if ($product_id) {
    if ($action === 'add') {
        // INSERT IGNORE sẽ bỏ qua nếu sản phẩm đã tồn tại, tránh lỗi
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (customer_id, product_id) VALUES (?, ?)");
        $stmt->execute([$customer_id, $product_id]);
    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
        $stmt->execute([$customer_id, $product_id]);
    }
}

// Lấy số lượng yêu thích mới để cập nhật giao diện (nếu cần)
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE customer_id = ?");
$count_stmt->execute([$customer_id]);
$wishlist_count = $count_stmt->fetchColumn();
$_SESSION['wishlist_count'] = $wishlist_count;

// Phản hồi cho AJAX hoặc chuyển hướng
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'wishlist_count' => $wishlist_count]);
    exit;
} else {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    exit;
}
?>