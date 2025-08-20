<?php
// Tệp: /pages/appointment_handler.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

// Lấy và làm sạch dữ liệu
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$note = trim(filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING));

// Xác thực dữ liệu cơ bản
if (empty($name) || empty($phone) || empty($date) || empty($time)) {
    // Nếu thiếu thông tin cần thiết, chuyển hướng với thông báo lỗi
    header('Location: ' . BASE_URL . '?appointment=error&reason=missing_fields');
    exit;
}

// Kết hợp ngày và giờ thành một chuỗi datetime chuẩn của MySQL
$date_time_str = $date . ' ' . $time . ':00';
try {
    $date_time_obj = new DateTime($date_time_str);
    $formatted_date_time = $date_time_obj->format('Y-m-d H:i:s');
} catch (Exception $e) {
    // Nếu ngày giờ không hợp lệ
    header('Location: ' . BASE_URL . '?appointment=error&reason=invalid_date');
    exit;
}

// Chèn dữ liệu vào CSDL
try {
    $stmt = $pdo->prepare(
        "INSERT INTO appointments (name, phone, email, date_time, note, status) 
         VALUES (?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([$name, $phone, $email, $formatted_date_time, $note]);
    
    // Chèn thành công, chuyển hướng với thông báo thành công
    header('Location: ' . BASE_URL . '?appointment=success#appointment-form');
    exit;
} catch (PDOException $e) {
    // Lỗi CSDL
    // error_log('Appointment form error: ' . $e->getMessage()); // Ghi log lỗi để debug
    header('Location: ' . BASE_URL . '?appointment=error&reason=db_error');
    exit;
}
?>