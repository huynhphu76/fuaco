<?php
// Tệp: /pages/checkout.php
global $pdo, $language_code;
$customer_name_preset = $_SESSION['customer']['name'] ?? '';
$customer_email_preset = $_SESSION['customer']['email'] ?? '';
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ' . BASE_URL . 'cart');
    exit;
}

// --- BẮT ĐẦU BỔ SUNG: LẤY DỮ LIỆU CÀI ĐẶT ---
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
// --- KẾT THÚC BỔ SUNG ---

// --- DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'title' => 'Thanh Toán',
        'delivery_info' => 'Thông tin giao hàng',
        'full_name' => 'Họ và tên *',
        'email' => 'Email *',
        'phone' => 'Số điện thoại *',
        'address' => 'Địa chỉ *',
        'payment_method' => 'Phương thức thanh toán', // Mới
        'cod' => 'Thanh toán khi nhận hàng (COD)', // Mới
        'bank_transfer' => 'Chuyển khoản ngân hàng', // Mới
        'bank_info_instruct' => 'Vui lòng chuyển khoản vào tài khoản dưới đây:', // Mới
        'account_holder' => 'Chủ tài khoản', // Mới
        'account_number' => 'Số tài khoản', // Mới
        'bank_name' => 'Ngân hàng', // Mới
        'branch' => 'Chi nhánh', // Mới
        'complete_order' => 'Hoàn tất đơn hàng',
        'your_order' => 'Đơn hàng của bạn',
        'total' => 'Tổng cộng',
        'thank_you_note' => 'Cảm ơn bạn đã tin tưởng và mua sắm tại FUACO. Chúng tôi sẽ liên hệ với bạn sớm nhất để xác nhận đơn hàng.'
    ],
    'en' => [
        'title' => 'Checkout',
        'delivery_info' => 'Delivery Information',
        'full_name' => 'Full Name *',
        'email' => 'Email *',
        'phone' => 'Phone Number *',
        'address' => 'Address *',
        'payment_method' => 'Payment Method', // Mới
        'cod' => 'Cash on Delivery (COD)', // Mới
        'bank_transfer' => 'Bank Transfer', // Mới
        'bank_info_instruct' => 'Please transfer to the bank account below:', // Mới
        'account_holder' => 'Account Holder', // Mới
        'account_number' => 'Account Number', // Mới
        'bank_name' => 'Bank Name', // Mới
        'branch' => 'Branch', // Mới
        'complete_order' => 'Complete Order',
        'your_order' => 'Your Order',
        'total' => 'Total',
        'thank_you_note' => 'Thank you for trusting and shopping at FUACO. We will contact you shortly to confirm the order.'
    ]
];
$lang = $translations[$language_code];

// --- Lấy thông tin tài khoản ngân hàng ---
$bank_info = [
    'name' => $settings['bank_account_name'] ?? '',
    'number' => $settings['bank_account_number'] ?? '',
    'bank' => $settings['bank_name'] ?? '',
    'branch' => $settings['bank_branch'] ?? ''
];

// --- PHẦN LOGIC TÍNH TỔNG TIỀN ---
$total_price = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $price_lookup = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            if (isset($price_lookup[$product_id])) {
                $total_price += $price_lookup[$product_id] * $quantity;
            }
        }
    }
}
?>
<div class="container my-5">
    <h1 class="text-center mb-5"><?= $lang['title'] ?></h1>
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form action="<?= BASE_URL ?>pages/order_handler.php" method="POST">
                        <h4><?= $lang['delivery_info'] ?></h4>
<div class="mb-3"><label for="customer_name" class="form-label"><?= $lang['full_name'] ?></label><input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name_preset) ?>" required></div>
<div class="mb-3"><label for="email" class="form-label"><?= $lang['email'] ?></label><input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($customer_email_preset) ?>" required></div>                        <div class="mb-3"><label for="phone" class="form-label"><?= $lang['phone'] ?></label><input type="tel" class="form-control" id="phone" name="phone" required></div>
                        <div class="mb-3"><label for="address" class="form-label"><?= $lang['address'] ?></label><textarea class="form-control" id="address" name="address" rows="3" required></textarea></div>

                        <hr class="my-4">
                        <h4><?= $lang['payment_method'] ?></h4>
                        <div class="payment-methods">
                            <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked><label class="form-check-label" for="payment_cod"><?= $lang['cod'] ?></label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="bank_transfer"><label class="form-check-label" for="payment_bank"><?= $lang['bank_transfer'] ?></label></div>
                        </div>

                        <div id="bank-info" class="mt-3 p-3 border rounded bg-light" style="display: none;">
                            <p class="mb-2"><?= $lang['bank_info_instruct'] ?></p>
                            <ul class="list-unstyled mb-0">
                                <li><strong><?= $lang['account_holder'] ?>:</strong> <?= htmlspecialchars($bank_info['name']) ?></li>
                                <li><strong><?= $lang['account_number'] ?>:</strong> <?= htmlspecialchars($bank_info['number']) ?></li>
                                <li><strong><?= $lang['bank_name'] ?>:</strong> <?= htmlspecialchars($bank_info['bank']) ?></li>
                                <?php if (!empty($bank_info['branch'])): ?><li><strong><?= $lang['branch'] ?>:</strong> <?= htmlspecialchars($bank_info['branch']) ?></li><?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="d-grid mt-4"><button type="submit" class="btn btn-dark btn-lg"><?= $lang['complete_order'] ?></button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
             <h4><?= $lang['your_order'] ?></h4>
             <div class="cart-summary">
                 <div class="d-flex justify-content-between mb-4 h5"><span><?= $lang['total'] ?></span><strong><?= number_format($total_price) ?>₫</strong></div>
                 <p class="small text-muted"><?= $lang['thank_you_note'] ?></p>
             </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic cho việc hiển thị thông tin ngân hàng (giữ nguyên)
    const bankRadio = document.getElementById('payment_bank');
    const codRadio = document.getElementById('payment_cod');
    const bankInfoDiv = document.getElementById('bank-info');

    if (bankRadio && codRadio && bankInfoDiv) {
        bankRadio.addEventListener('change', function() {
            if (this.checked) {
                bankInfoDiv.style.display = 'block';
            }
        });

        codRadio.addEventListener('change', function() {
            if (this.checked) {
                bankInfoDiv.style.display = 'none';
            }
        });
    }

    // === PHẦN QUAN TRỌNG: VÔ HIỆU HÓA NÚT BẤM SAU KHI CLICK ===
    // Tìm đến form thanh toán
    const checkoutForm = document.querySelector('form[action*="order_handler.php"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Tìm nút submit bên trong form
            const submitButton = checkoutForm.querySelector('button[type="submit"]');
            if (submitButton) {
                // Vô hiệu hóa nút
                submitButton.disabled = true;
                // Thay đổi nội dung để người dùng biết
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
            }
        });
    }
});
</script>