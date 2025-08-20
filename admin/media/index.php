<?php
// Tệp: admin/media/index.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/permission_check.php';

// Bất kỳ ai có quyền quản lý nội dung đều có thể truy cập thư viện
if (!hasPermission('manage-blogs') && !hasPermission('manage-products')) {
    die('Bạn không có quyền truy cập chức năng này.');
}

$uploadDir = __DIR__ . '/../../uploads/library/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

$message = '';
$message_type = '';

// Xử lý upload file mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid('media_', true) . '_' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $stmt = $pdo->prepare("INSERT INTO media_library (file_name, file_type, file_size) VALUES (?, ?, ?)");
            $stmt->execute([$filename, $file['type'], $file['size']]);
            $message = 'Tải tệp lên thành công!';
            $message_type = 'success';
        }
    } else {
        $message = 'Đã có lỗi xảy ra khi tải tệp lên.';
        $message_type = 'danger';
    }
}

// Lấy danh sách media từ CSDL
$media_files = $pdo->query("SELECT * FROM media_library ORDER BY uploaded_at DESC")->fetchAll();

ob_start();
?>
<style>
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
    .media-item { position: relative; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
    .media-item img { width: 100%; height: 150px; object-fit: cover; }
    .media-item .overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); color: white; opacity: 0; transition: opacity 0.3s; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 5px; }
    .media-item:hover .overlay { opacity: 1; }
    .media-item .file-name { font-size: 0.8rem; word-break: break-all; }
    .media-item .btn-delete { position: absolute; top: 5px; right: 5px; }
</style>

<div class="dashboard container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-photo-video me-2"></i>Thư viện Media</h1>

    <div class="card shadow mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Tải tệp mới</h6></div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="file" class="form-control" name="media_file" required>
                    <button class="btn btn-primary" type="submit">Tải lên</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Tất cả tệp media</h6></div>
        <div class="card-body">
            <?php if (empty($media_files)): ?>
                <p class="text-center">Thư viện của bạn chưa có tệp nào.</p>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($media_files as $file): ?>
                        <div class="media-item">
                            <img src="/interior-website/uploads/library/<?= htmlspecialchars($file['file_name']) ?>" alt="<?= htmlspecialchars($file['file_name']) ?>">
                            <div class="overlay">
                                <a href="delete.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa tệp này?')"><i class="fas fa-trash"></i></a>
                                <div class="text-center">
                                    <small class="file-name"><?= htmlspecialchars($file['file_name']) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Thư viện Media';
include '../layout.php';
?>
