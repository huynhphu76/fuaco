<?php
// Tệp: /pages/blog_comment_handler.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'bai-viet');
    exit;
}

// Dữ liệu song ngữ cho thông báo
$language_code = $_SESSION['lang'] ?? 'vi';
$translations = [
    'vi' => [
        'missing_fields' => 'Vui lòng điền đầy đủ tên, email và nội dung bình luận.',
        'comment_submitted' => 'Cảm ơn bạn! Bình luận của bạn đã được gửi và đang chờ duyệt.',
        'generic_error' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'
    ],
    'en' => [
        'missing_fields' => 'Please fill in your name, email, and comment.',
        'comment_submitted' => 'Thank you! Your comment has been submitted and is awaiting approval.',
        'generic_error' => 'An error occurred. Please try again.'
    ]
];
$lang = $translations[$language_code];

// Lấy dữ liệu form
$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$slug = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING);
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING));

$redirect_url = BASE_URL . 'bai-viet/' . $slug;

// Validation
if (empty($post_id) || empty($slug) || empty($name) || empty($email) || empty($content)) {
    $_SESSION['comment_message'] = ['type' => 'danger', 'text' => $lang['missing_fields']];
    header('Location: ' . $redirect_url . '#comment-form');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO blog_comments (post_id, author_name, author_email, content, status) 
         VALUES (?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([$post_id, $name, $email, $content]);
    
    $_SESSION['comment_message'] = ['type' => 'success', 'text' => $lang['comment_submitted']];

} catch (PDOException $e) {
    // error_log('Blog comment error: ' . $e->getMessage());
    $_SESSION['comment_message'] = ['type' => 'danger', 'text' => $lang['generic_error']];
}

header('Location: ' . $redirect_url . '#comment-section');
exit;