<?php
// Tệp: admin/products/get_attributes.php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

if (!hasPermission('view-products')) {
    echo json_encode(['error' => 'Không có quyền truy cập.']);
    exit;
}

$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id) {
    echo json_encode(['error' => 'ID sản phẩm không hợp lệ.']);
    exit;
}

// SỬA LỖI: Lấy tên sản phẩm (Tiếng Việt) từ bảng dịch
$language_code = 'vi';
$stmt_name = $pdo->prepare("
    SELECT name 
    FROM product_translations 
    WHERE product_id = ? AND language_code = ?
");
$stmt_name->execute([$product_id, $language_code]);
$product_name = $stmt_name->fetchColumn();

// Lấy danh sách thuộc tính (đoạn này đã đúng)
$stmt_attr = $pdo->prepare("SELECT attribute_name, attribute_value FROM product_attributes WHERE product_id = ? ORDER BY attribute_name ASC");
$stmt_attr->execute([$product_id]);
$attributes = $stmt_attr->fetchAll(PDO::FETCH_ASSOC);

// Trả về dữ liệu JSON
echo json_encode([
    'product_name' => $product_name ?: 'Sản phẩm chưa có tên',
    'attributes' => $attributes
]);