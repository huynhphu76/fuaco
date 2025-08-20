<?php
// Tệp: /pages/contact_handler.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- DỮ LIỆU SONG NGỮ CHO THÔNG BÁO ---
$language_code = $_SESSION['lang'] ?? 'vi';
$translations = [
    'vi' => [
        'missing_fields' => 'Vui lòng điền đầy đủ các trường bắt buộc.',
        'message_sent' => 'Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công.',
        'mailer_error' => 'Không thể gửi tin nhắn. Lỗi: '
    ],
    'en' => [
        'missing_fields' => 'Please fill in all required fields.',
        'message_sent' => 'Thank you! Your message has been sent successfully.',
        'mailer_error' => 'Message could not be sent. Mailer Error: '
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'lien-he');
    exit;
}

// Lấy dữ liệu form
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

// Lấy email của admin từ CSDL
$admin_email = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'")->fetchColumn();
if (!$admin_email) {
    $admin_email = 'huynhphu06143@gmail.com'; // Email dự phòng
}

// Validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    $_SESSION['contact_form_message'] = ['type' => 'danger', 'text' => $lang['missing_fields']];
    header('Location: ' . BASE_URL . 'lien-he');
    exit;
}

$mail = new PHPMailer(true);

try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'huynhphu06143@gmail.com'; // Email gửi thư
    $mail->Password   = 'ifey soxp zwlv drqr';     // Mật khẩu ứng dụng
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Người gửi và người nhận
    $mail->setFrom($email, $name);
    $mail->addAddress($admin_email, 'Admin FUACO');
    $mail->addReplyTo($email, $name);

    // Nội dung Email
    $mail->isHTML(true);
    $mail->Subject = "[LIÊN HỆ] - " . $subject;
    $mail->Body    = "Bạn có một tin nhắn mới từ form liên hệ:<br><br>" .
                     "<strong>Họ và tên:</strong> " . htmlspecialchars($name) . "<br>" .
                     "<strong>Email:</strong> " . htmlspecialchars($email) . "<br>" .
                     "<strong>Chủ đề:</strong> " . htmlspecialchars($subject) . "<br><br>" .
                     "<strong>Nội dung:</strong><br>" . nl2br(htmlspecialchars($message));
    
    $mail->send();
    $_SESSION['contact_form_message'] = ['type' => 'success', 'text' => $lang['message_sent']];

} catch (Exception $e) {
    $_SESSION['contact_form_message'] = ['type' => 'danger', 'text' => $lang['mailer_error'] . $mail->ErrorInfo];
}

header('Location: ' . BASE_URL . 'lien-he');
exit;