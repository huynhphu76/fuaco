<?php
// Tệp: pages/account.php (Đã thêm song ngữ)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['customer'])) {
    header('Location: ' . BASE_URL . 'dang-nhap');
    exit;
}
global $pdo, $language_code;

$translations = [
    'vi' => [
        'title' => 'Tài Khoản Của Tôi',
        'welcome' => 'Chào mừng trở lại,',
        'menu_overview' => 'Đơn Hàng Gần Đây',
        'recent_orders_title' => 'Đơn Hàng Gần Đây',
        'no_orders' => 'Bạn chưa có đơn hàng nào.',
        'col_order_id' => 'Mã ĐH',
        'col_date' => 'Ngày Đặt',
        'col_total' => 'Tổng Tiền',
        'col_status' => 'Trạng thái',
         'menu_wishlist' => 'Danh sách yêu thích', // Thêm mới

    ],
    'en' => [
        'title' => 'My Account',
        'welcome' => 'Welcome back,',
        'menu_overview' => 'Recent Orders',
        'recent_orders_title' => 'Recent Orders',
        'no_orders' => 'You have not placed any orders yet.',
        'col_order_id' => 'Order ID',
        'col_date' => 'Date',
        'col_total' => 'Total',
        'col_status' => 'Status',
                'menu_wishlist' => 'Wishlist', // Thêm mới

    ]
];
$lang = $translations[$language_code];

$customer_id = $_SESSION['customer']['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$customer_id]);
$recent_orders = $stmt->fetchAll();
?>
<div class="container my-5">
    <h1><?= $lang['title'] ?></h1>
    <p class="lead"><?= $lang['welcome'] ?> <?= htmlspecialchars($_SESSION['customer']['name']) ?>!</p>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="list-group">
                <a href="<?= BASE_URL ?>tai-khoan" class="list-group-item list-group-item-action active"><?= $lang['menu_overview'] ?></a>
                <a href="<?= BASE_URL ?>tai-khoan/yeu-thich" class="list-group-item list-group-item-action"><?= $lang['menu_wishlist'] ?></a>

            </div>
        </div>
        <div class="col-md-9">
            <h4><?= $lang['recent_orders_title'] ?></h4>
            <?php if (empty($recent_orders)): ?>
                <p><?= $lang['no_orders'] ?></p>
            <?php else: ?>
                <table class="table table-bordered">
                    <thead><tr><th><?= $lang['col_order_id'] ?></th><th><?= $lang['col_date'] ?></th><th><?= $lang['col_total'] ?></th><th><?= $lang['col_status'] ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                            <td><?= number_format($order['total_price']) ?>₫</td>
                            <td><span class="badge bg-primary text-uppercase"><?= htmlspecialchars($order['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>