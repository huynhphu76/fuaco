<?php
// Tệp: admin/project_images/index.php (HOÀN CHỈNH - ĐÃ SỬA LỖI)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-projects')) {
    header('HTTP/1.0 403 Forbidden');
    die('Bạn không có quyền truy cập chức năng này.');
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

// Bảo mật CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id) { die("Lỗi: ID dự án không hợp lệ."); }

// === SỬA LỖI SQL TẠI ĐÂY ===
// Lấy tiêu đề dự án từ bảng dịch (tiếng Việt)
$stmt = $pdo->prepare("
    SELECT pt.title 
    FROM projects p
    JOIN project_translations pt ON p.id = pt.project_id
    WHERE p.id = ? AND pt.language_code = 'vi'
");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) { die("Lỗi: Dự án không tồn tại."); }

$pageTitle = "Ảnh cho dự án: " . htmlspecialchars($project['title']);
$project_title_for_log = $project['title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Lỗi xác thực CSRF!');
    }

    // XỬ LÝ UPLOAD ẢNH MỚI
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/projects/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array(strtolower($_FILES['project_image']['type']), $allowed_types) && $_FILES['project_image']['size'] <= $max_size) {
            $file_extension = pathinfo($_FILES['project_image']['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid('project_' . $project_id . '_', true) . '.' . $file_extension;
            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $upload_dir . $unique_filename)) {
                // Chỉ lưu đường dẫn tương đối từ thư mục gốc của website
                $db_path = 'uploads/projects/' . $unique_filename;
                $stmt_insert = $pdo->prepare("INSERT INTO project_images (project_id, image_url, created_at) VALUES (?, ?, NOW())");
                $stmt_insert->execute([$project_id, $db_path]);
                logAction($pdo, $_SESSION['user_id'], "Tải ảnh '{$unique_filename}' lên cho dự án '" . htmlspecialchars($project_title_for_log) . "'");
            }
        }
    }

    // XỬ LÝ XÓA ẢNH
    if (isset($_POST['delete_image_id']) && isset($_POST['image_url'])) {
        $image_id_to_delete = filter_var($_POST['delete_image_id'], FILTER_VALIDATE_INT);
        $image_url_to_delete = $_POST['image_url'];
        
        $stmt_delete = $pdo->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?");
        $stmt_delete->execute([$image_id_to_delete, $project_id]);
        
        $physical_file_path = __DIR__ . '/../../' . $image_url_to_delete;
        if (file_exists($physical_file_path)) { @unlink($physical_file_path); }
        
        logAction($pdo, $_SESSION['user_id'], "Xóa ảnh '{$image_url_to_delete}' khỏi dự án '" . htmlspecialchars($project_title_for_log) . "'");
    }
    
    unset($_SESSION['csrf_token']); // Xóa token sau khi dùng
    header("Location: index.php?project_id=" . $project_id);
    exit;
}

// Lấy danh sách ảnh hiện có
$stmt_images = $pdo->prepare("SELECT id, image_url FROM project_images WHERE project_id = ? ORDER BY created_at DESC");
$stmt_images->execute([$project_id]);
$images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-images me-2"></i> <?= $pageTitle ?></h1>
    <a href="/interior-website/admin/projects/index.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Quay lại danh sách dự án</a>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tải lên ảnh mới</h6></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="mb-3"><input class="form-control" type="file" name="project_image" required></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Tải lên</button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Danh sách ảnh hiện có (<?= count($images) ?> ảnh)</h6></div>
        <div class="card-body">
            <div class="row">
                <?php if (empty($images)): ?>
                    <div class="col-12"><p>Chưa có hình ảnh nào cho dự án này.</p></div>
                <?php else: ?>
                    <?php foreach ($images as $image): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100">
                                <img src="/interior-website/<?= htmlspecialchars($image['image_url']) ?>" class="card-img-top" alt="Ảnh dự án" style="height: 200px; object-fit: cover;">
                                <div class="card-body text-center p-2">
                                    <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ảnh này?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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