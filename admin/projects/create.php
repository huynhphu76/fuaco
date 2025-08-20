<?php
// Tệp: admin/projects/create.php (HOÀN CHỈNH - ĐÃ SỬA LỖI & ĐẦY ĐỦ)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-projects')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Thêm dòng này

get_csrf_token();

// Bảo mật CSRF
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

// Sửa lỗi: Lấy tên sản phẩm từ bảng dịch để hiển thị
$products_stmt = $pdo->prepare("SELECT p.id, pt.name FROM products p JOIN product_translations pt ON p.id = pt.product_id WHERE pt.language_code = 'vi' ORDER BY pt.name ASC");
$products_stmt->execute();
$products = $products_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Xác thực CSRF
    verify_csrf_token(); 

    if (!empty($_POST['translations']['vi']['title'])) {
        // Khởi tạo HTML Purifier
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        try {
            $pdo->beginTransaction();
            
            // 2. Xử lý ảnh đại diện
            $thumbnail = null;
            $uploadDir = __DIR__ . '/../../uploads/projects/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            
            if (!empty($_POST['thumbnail_from_library'])) {
                $image_filename = basename($_POST['thumbnail_from_library']);
                $source_path = __DIR__ . '/../../uploads/library/' . $image_filename;
                if (file_exists($source_path)) {
                    copy($source_path, $uploadDir . $image_filename);
                    $thumbnail = $image_filename;
                }
            } 
            elseif (isset($_FILES['thumbnail_upload']) && $_FILES['thumbnail_upload']['error'] === UPLOAD_ERR_OK) {
                $filename = uniqid('project_thumb_', true) . '.' . pathinfo($_FILES['thumbnail_upload']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['thumbnail_upload']['tmp_name'], $uploadDir . $filename)) {
                    $thumbnail = $filename;
                }
            }

            // 3. Chèn vào bảng `projects`
            $stmt = $pdo->prepare("INSERT INTO projects (completed_at, thumbnail) VALUES (?, ?)");
            $stmt->execute([!empty($_POST['completed_at']) ? $_POST['completed_at'] : null, $thumbnail]);
            $project_id = $pdo->lastInsertId();

            // 4. Chèn vào bảng `project_translations`
            $stmt_trans = $pdo->prepare(
                "INSERT INTO project_translations (project_id, language_code, title, slug, description, meta_title, meta_description, meta_keywords) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            foreach (['vi', 'en'] as $lang) {
                $title = $_POST['translations'][$lang]['title'] ?? '';
                if (!empty($title)) {
                    // Gán các giá trị vào biến rõ ràng
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                    $description = $purifier->purify($_POST['translations'][$lang]['description'] ?? '');
                    $meta_title = $_POST['translations'][$lang]['meta_title'] ?? '';
                    $meta_description = $_POST['translations'][$lang]['meta_description'] ?? '';
                    $meta_keywords = $_POST['translations'][$lang]['meta_keywords'] ?? '';
                    
                    // Thực thi câu lệnh với 8 tham số
                    $stmt_trans->execute([
                        $project_id, 
                        $lang, 
                        $title, 
                        $slug, 
                        $description,
                        $meta_title,
                        $meta_description,
                        $meta_keywords
                    ]);
                }
            }

            // 5. Chèn vào bảng `project_products`
            if ($project_id && !empty($_POST['products'])) {
                $stmt_link = $pdo->prepare("INSERT INTO project_products (project_id, product_id) VALUES (?, ?)");
                foreach ($_POST['products'] as $product_id) { 
                    $stmt_link->execute([$project_id, $product_id]); 
                }
            }
            
            $pdo->commit();
            logAction($pdo, $_SESSION['user']['id'], "Tạo dự án mới: '" . htmlspecialchars($_POST['translations']['vi']['title']) . "'");
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi khi tạo dự án: " . $e->getMessage());
        }
    }
}

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-plus me-2"></i>Thêm dự án mới</h2>
    <form id="project-form" method="post" enctype="multipart/form-data" class="mt-4">
        <?php csrf_field(); ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
                    <div class="card-body tab-content">
                        <div class="tab-pane active" id="vi">
                            <div class="mb-3"><label class="form-label">Tiêu đề dự án (VI)</label><input type="text" name="translations[vi][title]" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Mô tả chi tiết (VI)</label><textarea name="translations[vi][description]" class="tinymce-editor"></textarea></div>
                            <hr><h5 class="mb-3">SEO (VI)</h5>
                            <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="translations[vi][meta_title]" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="translations[vi][meta_description]" rows="3" class="form-control"></textarea></div>
                            <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="translations[vi][meta_keywords]" class="form-control"></div>
                        </div>
                        <div class="tab-pane" id="en">
                             <div class="mb-3"><label class="form-label">Project Title (EN)</label><input type="text" name="translations[en][title]" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Description (EN)</label><textarea name="translations[en][description]" class="tinymce-editor"></textarea></div>
                            <hr><h5 class="mb-3">SEO (EN)</h5>
                            <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="translations[en][meta_title]" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="translations[en][meta_description]" rows="3" class="form-control"></textarea></div>
                            <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="translations[en][meta_keywords]" class="form-control"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h5 class="mb-0">Thông tin bổ sung</h5></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Ngày hoàn thành</label><input type="date" name="completed_at" class="form-control"></div>
                        <div class="mb-3">
                            <label class="form-label">Ảnh đại diện</label>
                            <img id="image-preview" src="/interior-website/assets/images/default-placeholder.png" class="img-thumbnail mb-2" style="max-height: 150px;">
                            <input type="hidden" name="thumbnail_from_library" id="thumbnail_from_library">
                            <div class="input-group">
                                <input type="file" name="thumbnail_upload" class="form-control form-control-sm" id="thumbnail_upload">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#mediaModal">Thư viện</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Sản phẩm liên quan</h5></div>
                    <div class="card-body">
                        <select name="products[]" class="form-select" multiple size="10">
                            <?php foreach($products as $product): ?><option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option><?php endforeach; ?>
                        </select>
                        <div class="form-text">Giữ phím Ctrl (hoặc Command) để chọn nhiều sản phẩm.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-success">Tạo mới</button><a href="index.php" class="btn btn-secondary">Hủy</a></div>
    </form>
</div>
<div class="modal fade" id="mediaModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Chọn ảnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mediaModalBody"></div></div></div></div>

<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // KHỞI TẠO TINYMCE
    tinymce.init({
        selector: '.tinymce-editor',
        plugins: 'lists link image code table paste wordcount',
        height: 300,
        branding: false,
        paste_data_images: true,
        images_upload_url: 'upload_handler.php',
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false
    });

    // KÍCH HOẠT LƯU TRƯỚC KHI SUBMIT FORM
    document.getElementById('project-form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
    
    // LOGIC CHO MEDIA LIBRARY
    const mediaModalEl = document.getElementById('mediaModal');
    const imagePreview = document.getElementById('image-preview');
    const imageFromLibraryInput = document.getElementById('thumbnail_from_library');
    const imageUploadInput = document.getElementById('thumbnail_upload');
    const mediaModalBody = document.getElementById('mediaModalBody');
    const mediaModal = new bootstrap.Modal(mediaModalEl);

    mediaModalEl.addEventListener('show.bs.modal', function(){
        fetch('/interior-website/admin/media/media_picker.php')
            .then(r => r.text())
            .then(h => mediaModalBody.innerHTML = h);
    });

    window.selectMedia = function(fileName) {
        imageFromLibraryInput.value = fileName;
        imagePreview.src = '/interior-website/uploads/library/' + fileName;
        imageUploadInput.value = '';
        mediaModal.hide();
    }

    imageUploadInput.addEventListener('change', function(e){
        if(e.target.files && e.target.files[0]){
            const r = new FileReader();
            r.onload = function(e){
                imagePreview.src = e.target.result;
                imageFromLibraryInput.value = '';
            }
            r.readAsDataURL(e.target.files[0]);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Thêm dự án';
include '../layout.php';
?>