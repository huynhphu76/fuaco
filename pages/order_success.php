<?php
// Tệp: /pages/order_success.php
global $language_code;
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// --- DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'title' => 'Đặt Hàng Thành Công!',
        'thank_you' => 'Cảm ơn bạn đã mua sắm tại FUACO.',
        'order_id_is' => 'Mã đơn hàng của bạn là:',
        'we_will_contact' => 'Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng.',
        'continue_shopping' => 'Tiếp tục mua sắm'
    ],
    'en' => [
        'title' => 'Order Placed Successfully!',
        'thank_you' => 'Thank you for shopping at FUACO.',
        'order_id_is' => 'Your order ID is:',
        'we_will_contact' => 'We will contact you shortly to confirm the order.',
        'continue_shopping' => 'Continue Shopping'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC DỮ LIỆU SONG NGỮ ---
?>
<div class="container my-5 text-center">
    <div class="py-5">
        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
        <h1 class="mt-4"><?= $lang['title'] ?></h1>
        <p class="lead"><?= $lang['thank_you'] ?></p>
        <?php if ($order_id): ?>
            <p><?= $lang['order_id_is'] ?> <strong>#<?= htmlspecialchars($order_id) ?></strong></p>
        <?php endif; ?>
        <p><?= $lang['we_will_contact'] ?></p>
        <a href="<?= BASE_URL ?>san-pham" class="btn btn-dark mt-3"><?= $lang['continue_shopping'] ?></a>
    </div>
</div>