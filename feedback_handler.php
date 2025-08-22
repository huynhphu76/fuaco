<?php
// Tệp: pages/feedback_handler.php (Phiên bản cuối cùng - Đã sửa lỗi)

// BẮT BUỘC: Nạp file index.php ở thư mục gốc để có BASE_URL, $pdo và các cài đặt khác
// Thao tác này sẽ làm cho file handler hoạt động như một phần của toàn bộ website
require_once __DIR__ . '/../index.php';

// Lưu ý: không cần session_start() hay require database/app nữa vì index.php đã làm hết rồi.

// Kiểm tra phương thức POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'], $_POST['message'], $_POST['rating'])) {
    
    // Lấy và làm sạch dữ liệu
    $name = trim($_POST['name']);
    $message = trim($_POST['message']);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 5]
    ]);

    // Kiểm tra dữ liệu hợp lệ
    if (!empty($name) && !empty($message) && $rating !== false) {
        try {
            // Biến $pdo đã có sẵn do nạp từ index.php
            $stmt = $pdo->prepare("INSERT INTO feedbacks (name, message, rating, is_approved) VALUES (?, ?, ?, ?)");
            // Thêm is_approved = 0 (chờ duyệt) khi thêm mới
            $stmt->execute([$name, $message, $rating, 0]);

            // Thiết lập thông báo thành công và chuyển hướng
            $_SESSION['feedback_status'] = 'success';
            $_SESSION['feedback_message'] = 'Cảm ơn bạn đã gửi phản hồi! Chúng tôi rất trân trọng ý kiến của bạn.';
            
            // Hằng số BASE_URL bây giờ đã tồn tại
            header('Location: ' . BASE_URL . 'lien-he?feedback=success#feedback-form');
            exit();

        } catch (PDOException $e) {
            // Xử lý lỗi CSDL và thiết lập thông báo lỗi
            $_SESSION['feedback_status'] = 'error';
            $_SESSION['feedback_message'] = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
            header('Location: ' . BASE_URL . 'lien-he?feedback=error#feedback-form');
            exit();
        }
    } else {
        // Dữ liệu không hợp lệ
        $_SESSION['feedback_status'] = 'error';
        $_SESSION['feedback_message'] = 'Vui lòng điền đầy đủ thông tin và chọn số sao đánh giá.';
        header('Location: ' . BASE_URL . 'lien-he?feedback=invalid#feedback-form');
        exit();
    }
} else {
    // Nếu không phải phương thức POST, chuyển hướng về trang chủ
    header('Location: ' . BASE_URL);
    exit();
}