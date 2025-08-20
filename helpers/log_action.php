<?php
// Tệp: helpers/log_action.php

/**
 * Ghi lại một hành động của quản trị viên vào cơ sở dữ liệu.
 *
 * QUAN TRỌNG: Hàm này đã được sửa lỗi để LUÔN LUÔN lấy ID người dùng
 * trực tiếp từ session, đảm bảo tính chính xác và nhất quán.
 *
 * @param PDO $pdo Đối tượng kết nối PDO.
 * @param mixed $user_id (Tham số này được giữ lại để tương thích, nhưng không còn được sử dụng).
 * @param string $action Mô tả về hành động đã thực hiện.
 * @return void
 */
function logAction($pdo, $user_id, $action) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Luôn lấy user_id từ session hiện tại để đảm bảo chính xác tuyệt đối.
    $correct_user_id = $_SESSION['user']['id'] ?? null;

    // Chỉ ghi log nếu lấy được ID người dùng hợp lệ từ session.
    if ($correct_user_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO admin_logs (user_id, action) VALUES (?, ?)");
            $stmt->execute([$correct_user_id, $action]);
        } catch (PDOException $e) {
            // Lỗi không ghi được log thì cũng không nên làm sập trang.
            // Có thể ghi lỗi ra file log riêng để debug nếu cần.
            // error_log('Failed to log action: ' . $e->getMessage());
        }
    }
}