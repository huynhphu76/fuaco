<?php
// Tệp: helpers/permission_check.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nạp tệp cấu hình CSDL để có thể sử dụng hàm get_pdo_connection()
require_once __DIR__ . '/../config/database.php';

/**
 * Hàm kiểm tra quyền hạn chính, tự quản lý kết nối CSDL.
 * @param string $required_permission Tên quyền cần kiểm tra.
 * @return bool
 */
function hasPermission($required_permission) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để tiếp tục.";
        header("Location: /interior-website/admin/login.php");
        exit();
    }
    
    try {
        // Tự lấy kết nối CSDL một cách đáng tin cậy
        $pdo = get_pdo_connection();
        $user_id = $_SESSION['user']['id'];

        $sql = "SELECT COUNT(*)
                FROM role_permissions rp
                JOIN users u ON u.role_id = rp.role_id
                JOIN permissions p ON p.id = rp.permission_id
                WHERE u.id = :user_id AND p.name = :permission_name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':permission_name' => $required_permission
        ]);
        
        return $stmt->fetchColumn() > 0;

    } catch (PDOException $e) {
        // Gặp lỗi CSDL, từ chối quyền để đảm bảo an toàn
        return false;
    }
}