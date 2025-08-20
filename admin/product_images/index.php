<?php
// ======================================================
// BẢO MẬT VÀ KHAI BÁO (ĐÃ CẬP NHẬT)
// ======================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';

// BẢO MẬT: Yêu cầu quyền 'edit-products' để quản lý ảnh
if (!hasPermission('edit-products')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
// ======================================================

$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) { die("Lỗi: ID sản phẩm không hợp lệ."); }

// SỬA LỖI: Lấy tên sản phẩm (Tiếng Việt) từ bảng dịch
$language_code = 'vi';
$stmt = $pdo->prepare("
    SELECT name 
    FROM product_translations 
    WHERE product_id = ? AND language_code = ?
");
$stmt->execute([$product_id, $language_code]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { die("Lỗi: Sản phẩm không tồn tại hoặc chưa có bản dịch Tiếng Việt."); }

$pageTitle = "Thư viện ảnh cho sản phẩm: " . htmlspecialchars($product['name']);
$product_name_for_log = $product['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // XỬ LÝ UPLOAD ẢNH MỚI
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024;

        if (in_array(strtolower($_FILES['product_image']['type']), $allowed_types) && $_FILES['product_image']['size'] <= $max_size) {
            $filename = uniqid('prod_img_', true) . '.' . pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename)) {
                $db_path = 'uploads/products/' . $filename;
                $stmt_insert = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                $stmt_insert->execute([$product_id, $db_path]);
                logAction($pdo, $_SESSION['user_id'], "Tải ảnh '{$filename}' cho sản phẩm '" . htmlspecialchars($product_name_for_log) . "'");
            }
        }
    }

    // XỬ LÝ XÓA ẢNH
    if (isset($_POST['delete_image_id']) && isset($_POST['image_url'])) {
        $image_id = filter_var($_POST['delete_image_id'], FILTER_VALIDATE_INT);
        $image_url = $_POST['image_url'];
        
        logAction($pdo, $_SESSION['user_id'], "Xóa ảnh '{$image_url}' khỏi sản phẩm '" . htmlspecialchars($product_name_for_log) . "'");

        $stmt_delete = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        $stmt_delete->execute([$image_id, $product_id]);

        $physical_path = __DIR__ . '/../../uploads/' . $image_url; // Sửa lại đường dẫn vật lý
        if (file_exists($physical_path)) { @unlink($physical_path); }
    }
    
    header("Location: index.php?product_id=" . $product_id);
    exit;
}

$stmt_images = $pdo->prepare("SELECT id, image_url FROM product_images WHERE product_id = ?");
$stmt_images->execute([$product_id]);
$images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-images me-2"></i> <?= $pageTitle ?></h1>
    <a href="../products/index.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Quay lại danh sách sản phẩm</a>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tải lên ảnh mới</h6></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3"><input class="form-control" type="file" name="product_image" required></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Tải lên</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách ảnh hiện có (<?= count($images) ?> ảnh)</h6></div>
        <div class="card-body">
            <div class="row">
                <?php if (empty($images)): ?>
                    <div class="col-12"><p>Chưa có hình ảnh nào cho sản phẩm này.</p></div>
                <?php else: ?>
                    <?php foreach ($images as $image): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100">
                                <img src="/interior-website/<?= htmlspecialchars($image['image_url']) ?>" class="card-img-top" alt="Ảnh sản phẩm" style="height: 200px; object-fit: cover;">
                                <div class="card-body text-center p-2">
                                    <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ảnh này?');">
                                        <input type="hidden" name="delete_image_id" value="<?= $image['id'] ?>">
                                        <input type="hidden" name="image_url" value="<?= htmlspecialchars($image['image_url']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>