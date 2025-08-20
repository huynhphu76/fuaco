<?php
// Tệp: pages/compare.php (Phiên bản cuối cùng - Hỗ trợ song ngữ)
global $pdo, $language_code;

// --- BƯỚC 1: THÊM MẢNG DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'page_title' => 'So Sánh Sản Phẩm',
        'feature_column' => 'Tính năng',
        'price_feature' => 'Giá',
        'no_products' => 'Vui lòng chọn ít nhất 2 sản phẩm để so sánh.',
        'back_to_shop' => 'Quay lại trang sản phẩm'
    ],
    'en' => [
        'page_title' => 'Product Comparison',
        'feature_column' => 'Feature',
        'price_feature' => 'Price',
        'no_products' => 'Please select at least 2 products to compare.',
        'back_to_shop' => 'Back to Shop'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC BƯỚC 1 ---

$product_ids = $_SESSION['compare_list'] ?? [];
$products_by_id = [];
$all_attribute_names = [];

if (count($product_ids) >= 2) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt_main = $pdo->prepare("
        SELECT p.id, p.price, p.main_image, pt.name
        FROM products p
        JOIN product_translations pt ON p.id = pt.product_id
        WHERE p.id IN ($placeholders) AND pt.language_code = ?
    ");
    $stmt_main->execute(array_merge($product_ids, [$language_code]));
    $products_assoc = $stmt_main->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products_assoc as $product) {
        $products_by_id[$product['id']] = $product;
    }

    if (!empty($products_by_id)) {
        $stmt_attrs = $pdo->prepare("
            SELECT product_id, attribute_name, attribute_value
            FROM product_attributes
            WHERE product_id IN ($placeholders)
        ");
        $stmt_attrs->execute($product_ids);
        $all_attributes = $stmt_attrs->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all_attributes as $attr) {
            if (isset($products_by_id[$attr['product_id']])) {
                $products_by_id[$attr['product_id']]['attributes'][$attr['attribute_name']] = $attr['attribute_value'];
                $all_attribute_names[$attr['attribute_name']] = true;
            }
        }
        $all_attribute_names = array_keys($all_attribute_names);
        sort($all_attribute_names);
    }
}
?>
<style>
/* CSS cho bảng so sánh (giữ nguyên) */
.compare-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
.compare-table th, .compare-table td { padding: 1.5rem; text-align: center; vertical-align: top; }
.compare-table thead th { font-family: var(--font-heading); font-size: 1.5rem; }
.compare-table tbody tr:first-child td { border-top: 1px solid #eee; padding-top: 2rem; }
.compare-table tbody td { border-bottom: 1px solid #eee; }
.compare-table .feature-name { text-align: left; font-weight: 600; color: var(--color-primary); }
.compare-table .product-image { max-width: 200px; }
.compare-table .product-name { font-weight: 700; font-size: 1.2rem; }
.compare-table .product-price { color: var(--color-secondary); font-weight: 700; font-size: 1.5rem; }
</style>
<div class="container my-5">
    <h1 class="text-center mb-5"><?= $lang['page_title'] ?></h1>
    <?php if (count($products_by_id) < 2): ?>
        <p class="text-center"><?= $lang['no_products'] ?></p>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>san-pham" class="btn btn-primary"><?= $lang['back_to_shop'] ?></a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="compare-table">
                <thead>
                    <tr>
                        <th><?= $lang['feature_column'] ?></th>
                        <?php foreach ($products_by_id as $product): ?>
                            <th>
                                <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>" class="product-image img-fluid mb-3">
                                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="feature-name"><?= $lang['price_feature'] ?></td>
                        <?php foreach ($products_by_id as $product): ?>
                            <td><div class="product-price"><?= number_format($product['price']) ?>₫</div></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($all_attribute_names as $attr_name): ?>
                        <tr>
                            <td class="feature-name"><?= htmlspecialchars($attr_name) ?></td>
                            <?php foreach ($products_by_id as $product): ?>
                                <td><?= htmlspecialchars($product['attributes'][$attr_name] ?? 'N/A') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>