<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
// --- DỮ LIỆU SONG NGỮ CHO THÔNG BÁO ---
$language_code = $_SESSION['lang'] ?? 'vi';
$translations = [
    'vi' => [
        'fill_all_fields' => 'Vui lòng điền đầy đủ các trường bắt buộc.',
        'review_submitted' => 'Cảm ơn bạn! Đánh giá của bạn đã được gửi và đang chờ duyệt.',
        'generic_error' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'
    ],
    'en' => [
        'fill_all_fields' => 'Please fill in all required fields.',
        'review_submitted' => 'Thank you! Your review has been submitted and is awaiting approval.',
        'generic_error' => 'An error occurred. Please try again.'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

// Lấy và làm sạch dữ liệu
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING));
$product_slug = trim(filter_input(INPUT_POST, 'product_slug', FILTER_SANITIZE_STRING));

$redirect_url = BASE_URL . 'san-pham/' . $product_slug;

if (empty($product_id) || empty($rating) || empty($name) || empty($email) || empty($content)) {
    $_SESSION['review_message'] = ['type' => 'error', 'text' => $lang['fill_all_fields']];
    header('Location: ' . $redirect_url . '#review-form');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO product_reviews (product_id, author_name, author_email, content, rating, status) 
         VALUES (?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([$product_id, $name, $email, $content, $rating]);
    
    $_SESSION['review_message'] = ['type' => 'success', 'text' => $lang['review_submitted']];
    header('Location: ' . $redirect_url . '#reviews-tab-pane');
    exit;

} catch (PDOException $e) {
   $_SESSION['review_message'] = ['type' => 'error', 'text' => $lang['generic_error']];
    header('Location: ' . $redirect_url . '#review-form');
    exit;
}