<?php
// Tệp: config/database.php

// --- BƯỚC 1: ĐỒNG BỘ MÚI GIỜ CHO PHP ---
// Đặt múi giờ mặc định cho toàn bộ ứng dụng. 'Asia/Ho_Chi_Minh' là phù hợp nhất.
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Khai báo các hằng số kết nối
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'interior_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Hàm quản lý kết nối PDO (Singleton Pattern).
 * Đảm bảo chỉ có một kết nối duy nhất được tạo trong suốt quá trình chạy.
 *
 * @return PDO|null Trả về đối tượng PDO nếu thành công, null nếu thất bại.
 */
function get_pdo_connection() {
    // Biến tĩnh để lưu trữ đối tượng kết nối
    static $pdo = null;

    // Nếu kết nối chưa được tạo, hãy tạo nó
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // --- BƯỚC 2: ĐỒNG BỘ MÚI GIỜ CHO KẾT NỐI CSDL ---
            // Yêu cầu MySQL sử dụng cùng múi giờ với PHP cho phiên làm việc này.
            $pdo->exec("SET time_zone = '+07:00'");

        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    // Trả về đối tượng kết nối đã được tạo
    return $pdo;
}

// Tạo kết nối lần đầu để các tệp có thể sử dụng biến $pdo trực tiếp
$pdo = get_pdo_connection();
