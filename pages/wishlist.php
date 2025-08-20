<?php
// Tệp: pages/wishlist.php (Đã thêm song ngữ)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['customer'])) {
    header('Location: ' . BASE_URL . 'dang-nhap');
    exit;
}
global $pdo, $language_code;

$translations = [
    'vi' => ['title' => 'Danh sách yêu thích', 'no_items' => 'Bạn chưa có sản phẩm yêu thích nào.', 'remove_button' => 'Xóa'],
    'en' => ['title' => 'Wishlist', 'no_items' => 'You have no items in your wishlist.', 'remove_button' => 'Remove']
];
$lang = $translations[$language_code];

$customer_id = $_SESSION['customer']['id'];

$stmt = $pdo->prepare("
    SELECT p.id, p.price, p.main_image, pt.name, pt.slug
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    JOIN product_translations pt ON p.id = pt.product_id
    WHERE w.customer_id = ? AND pt.language_code = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$customer_id, $language_code]);
$wishlist_items = $stmt->fetchAll();
?>
<div class="container my-5">
    <h1><?= $lang['title'] ?></h1>
    <?php if (empty($wishlist_items)): ?>
        <p><?= $lang['no_items'] ?></p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="product-card h-100">
                        <a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($item['slug']) ?>">
                            <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($item['main_image']) ?>" class="card-img-top">
                        </a>
                        <div class="card-body text-center">
                            <h5 class="product-title"><a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($item['slug']) ?>"><?= htmlspecialchars($item['name']) ?></a></h5>
                            <p class="product-price mt-2"><?= number_format($item['price']) ?>₫</p>
                            <form action="<?= BASE_URL ?>pages/wishlist_handler.php" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><?= $lang['remove_button'] ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>