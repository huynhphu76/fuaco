<?php
// Tệp: /pages/cart.php
global $pdo, $language_code;

// --- DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'title' => 'Giỏ Hàng Của Bạn',
        'empty_cart_message' => 'Giỏ hàng của bạn đang trống.',
        'continue_shopping' => 'Tiếp tục mua sắm',
        'product' => 'Sản phẩm',
        'price' => 'Giá',
        'quantity' => 'Số lượng',
        'subtotal' => 'Tạm tính',
        'cart_totals' => 'Tổng Giỏ Hàng',
        'total' => 'Tổng cộng',
        'proceed_to_checkout' => 'Tiến hành thanh toán',
        'remove_confirm' => 'Bạn có chắc muốn xóa sản phẩm này?'
    ],
    'en' => [
        'title' => 'Your Shopping Cart',
        'empty_cart_message' => 'Your cart is empty.',
        'continue_shopping' => 'Continue Shopping',
        'product' => 'Product',
        'price' => 'Price',
        'quantity' => 'Quantity',
        'subtotal' => 'Subtotal',
        'cart_totals' => 'Cart Totals',
        'total' => 'Total',
        'proceed_to_checkout' => 'Proceed to Checkout',
        'remove_confirm' => 'Are you sure you want to remove this product?'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC DỮ LIỆU SONG NGỮ ---

$cart_items_details = [];
$total_price = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

        $stmt = $pdo->prepare("
            SELECT p.id, p.price, p.main_image, p.quantity as stock_quantity, pt.name, pt.slug
            FROM products p
            JOIN product_translations pt ON p.id = pt.product_id
            WHERE p.id IN ($placeholders) AND pt.language_code = ?
        ");
        
        $params = array_merge($product_ids, [$language_code]);
        $stmt->execute($params);
        $products_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $products_in_cart = [];
        foreach ($products_from_db as $product) {
            $products_in_cart[$product['id']] = $product;
        }

        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            if (isset($products_in_cart[$product_id])) {
                $product = $products_in_cart[$product_id];
                $subtotal = $product['price'] * $quantity;
                $total_price += $subtotal;
                $cart_items_details[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'image' => $product['main_image'],
                    'quantity' => $quantity,
                    'stock_quantity' => $product['stock_quantity'],
                    'subtotal' => $subtotal
                ];
            }
        }
    }
}
?>
<style>
    .cart-table img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }
    .cart-summary { background-color: #f8f9fa; padding: 2rem; border-radius: var(--border-radius); position: sticky; top: 100px; }
    .quantity-input-cart { width: 70px; text-align: center; }
    .remove-item-btn { background: none; border: none; color: #dc3545; font-size: 1.2rem; }
</style>

<div class="container my-5">
    <h1 class="text-center mb-5"><?= $lang['title'] ?></h1>

    <?php if (empty($cart_items_details)): ?>
        <div class="text-center py-5">
            <p class="lead"><?= $lang['empty_cart_message'] ?></p>
            <a href="<?= BASE_URL ?>san-pham" class="btn btn-dark mt-3"><?= $lang['continue_shopping'] ?></a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8" id="cart-table-container">
                <table class="table align-middle cart-table">
                    <thead>
                        <tr>
                            <th colspan="2"><?= $lang['product'] ?></th>
                            <th><?= $lang['price'] ?></th>
                            <th class="text-center"><?= $lang['quantity'] ?></th>
                            <th class="text-end"><?= $lang['subtotal'] ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items_details as $item): ?>
                        <tr data-product-id="<?= $item['id'] ?>">
                            <td style="width: 120px;">
                                <a href="<?= BASE_URL ?>san-pham/<?= $item['slug'] ?>"><img src="<?= BASE_URL ?>uploads/products/<?= $item['image'] ?>" alt="<?= $item['name'] ?>"></a>
                            </td>
                            <td><a href="<?= BASE_URL ?>san-pham/<?= $item['slug'] ?>" class="text-dark text-decoration-none fw-bold"><?= htmlspecialchars($item['name']) ?></a></td>
                            <td><span class="price-per-item"><?= number_format($item['price']) ?></span>₫</td>
                            <td class="text-center">
                                <input type="number" class="form-control quantity-input-cart mx-auto" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" data-product-id="<?= $item['id'] ?>">
                            </td>
                            <td class="text-end"><strong class="subtotal"><?= number_format($item['subtotal']) ?></strong>₫</td>
                            <td class="text-center">
                                <button class="remove-item-btn" data-product-id="<?= $item['id'] ?>"><i class="fas fa-times-circle"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4 class="mb-4"><?= $lang['cart_totals'] ?></h4>
                    <div class="d-flex justify-content-between mb-3">
                        <span><?= $lang['subtotal'] ?></span>
                        <strong id="summary-subtotal"><?= number_format($total_price) ?>₫</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4 h5">
                        <span><?= $lang['total'] ?></span>
                        <strong id="summary-total"><?= number_format($total_price) ?>₫</strong>
                    </div>
                    <div class="d-grid">
                        <a href="<?= BASE_URL ?>checkout" class="btn btn-dark btn-lg"><?= $lang['proceed_to_checkout'] ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    function updateCartSummary() {
        let total = 0;
        document.querySelectorAll('tr[data-product-id]').forEach(row => {
            const priceText = row.querySelector('.price-per-item').textContent.replace(/,/g, '');
            const quantity = row.querySelector('.quantity-input-cart').value;
            const subtotal = parseFloat(priceText) * parseInt(quantity, 10);
            row.querySelector('.subtotal').textContent = subtotal.toLocaleString('vi-VN');
            total += subtotal;
        });

        const formattedTotal = total.toLocaleString('vi-VN');
        document.getElementById('summary-subtotal').textContent = formattedTotal + '₫';
        document.getElementById('summary-total').textContent = formattedTotal + '₫';
    }

    // Xử lý cập nhật số lượng
    document.querySelectorAll('.quantity-input-cart').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const quantity = this.value;

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('<?= BASE_URL ?>pages/cart_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                   updateCartSummary();
                } else {
                    alert('Lỗi: ' + data.message);
                    location.reload(); // Tải lại trang để hiển thị số lượng đúng
                }
            });
        });
    });

    // Xử lý xóa sản phẩm
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('<?= $lang['remove_confirm'] ?>')) return;
            
            const productId = this.dataset.productId;
            const row = this.closest('tr');
            
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);
            
            fetch('<?= BASE_URL ?>pages/cart_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.remove();
                    updateCartSummary();
                    document.getElementById('cart-item-count-badge').textContent = data.cart_item_count;
                    if (document.querySelectorAll('#cart-table-container tbody tr').length === 0) {
                        location.reload();
                    }
                } else {
                    alert('Lỗi: ' + data.message);
                }
            });
        });
    });
});
</script>