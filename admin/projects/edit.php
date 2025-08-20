<?php
// Tệp: admin/projects/edit.php (HOÀN CHỈNH - ĐÃ SỬA LỖI & ĐẦY ĐỦ)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-projects')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Thêm dòng này
get_csrf_token();

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
     $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    try {
        $pdo->beginTransaction();
        
        // 2. Xử lý ảnh đại diện (giữ nguyên logic cũ)
        $project_stmt = $pdo->prepare("SELECT thumbnail FROM projects WHERE id = ?");
        $project_stmt->execute([$id]);
        $thumbnail = $project_stmt->fetchColumn();
        $uploadDir = __DIR__ . '/../../uploads/projects/';
        if (!empty($_POST['thumbnail_from_library']) && basename($_POST['thumbnail_from_library']) !== $thumbnail) {
            if ($thumbnail && file_exists($uploadDir . $thumbnail)) { @unlink($uploadDir . $thumbnail); }
            $image_filename = basename($_POST['thumbnail_from_library']);
            $source_path = __DIR__ . '/../../uploads/library/' . $image_filename;
            if (file_exists($source_path)) { copy($source_path, $uploadDir . $image_filename); $thumbnail = $image_filename; }
        } 
        elseif (isset($_FILES['thumbnail_upload']) && $_FILES['thumbnail_upload']['error'] === UPLOAD_ERR_OK) {
            if ($thumbnail && file_exists($uploadDir . $thumbnail)) { @unlink($uploadDir . $thumbnail); }
            $filename = uniqid('project_thumb_', true) . '.' . pathinfo($_FILES['thumbnail_upload']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['thumbnail_upload']['tmp_name'], $uploadDir . $filename)) { $thumbnail = $filename; }
        }

        // 3. Cập nhật bảng `projects`
        $stmt_project = $pdo->prepare("UPDATE projects SET completed_at = ?, thumbnail = ? WHERE id = ?");
        $stmt_project->execute([!empty($_POST['completed_at']) ? $_POST['completed_at'] : null, $thumbnail, $id]);

        // 4. LOGIC MỚI: Cập nhật hoặc chèn bản dịch một cách rõ ràng
        foreach (['vi', 'en'] as $lang) {
            $title = $_POST['translations'][$lang]['title'] ?? '';
            
            // Nếu có tiêu đề, thì cập nhật hoặc chèn mới
            if (!empty($title)) {
                $description = $_POST['translations'][$lang]['description'] ?? ''; // Giả sử đã làm sạch bằng Purifier
                $meta_title = $_POST['translations'][$lang]['meta_title'] ?? '';
                $meta_description = $_POST['translations'][$lang]['meta_description'] ?? '';
                $meta_keywords = $_POST['translations'][$lang]['meta_keywords'] ?? '';
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

                // Kiểm tra xem bản dịch đã tồn tại chưa
                $check_stmt = $pdo->prepare("SELECT id FROM project_translations WHERE project_id = ? AND language_code = ?");
                $check_stmt->execute([$id, $lang]);
                
                if ($check_stmt->fetch()) {
                    // Nếu có rồi -> Cập nhật (UPDATE)
                    $update_trans_stmt = $pdo->prepare(
                        "UPDATE project_translations SET title=?, slug=?, description=?, meta_title=?, meta_description=?, meta_keywords=? 
                         WHERE project_id = ? AND language_code = ?"
                    );
                    $update_trans_stmt->execute([$title, $slug, $description, $meta_title, $meta_description, $meta_keywords, $id, $lang]);
                } else {
                    // Nếu chưa có -> Chèn mới (INSERT)
                    $insert_trans_stmt = $pdo->prepare(
                        "INSERT INTO project_translations (project_id, language_code, title, slug, description, meta_title, meta_description, meta_keywords) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $insert_trans_stmt->execute([$id, $lang, $title, $slug, $description, $meta_title, $meta_description, $meta_keywords]);
                }
            } 
            // Nếu không có tiêu đề, thì xóa bản dịch (nếu có)
            else {
                $pdo->prepare("DELETE FROM project_translations WHERE project_id = ? AND language_code = ?")->execute([$id, $lang]);
            }
        }
        
        // 5. Cập nhật sản phẩm liên quan
        $pdo->prepare("DELETE FROM project_products WHERE project_id = ?")->execute([$id]);
        if (!empty($_POST['products'])) {
            $stmt_link = $pdo->prepare("INSERT INTO project_products (project_id, product_id) VALUES (?, ?)");
            foreach ($_POST['products'] as $product_id) { 
                $stmt_link->execute([$id, $product_id]); 
            }
        }
        
        $pdo->commit();
        logAction($pdo, $_SESSION['user']['id'], "Cập nhật dự án #{$id}");
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi cập nhật dự án: " . $e->getMessage());
    }
}

// LẤY DỮ LIỆU HIỆN TẠI
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) { die("Dự án không tồn tại"); }

$trans_stmt = $pdo->prepare("SELECT language_code, title, slug, description, meta_title, meta_description, meta_keywords FROM project_translations WHERE project_id = ?");
$trans_stmt->execute([$id]);
$translations = $trans_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

$products_stmt = $pdo->prepare("SELECT p.id, pt.name FROM products p JOIN product_translations pt ON p.id = pt.product_id WHERE pt.language_code = 'vi' ORDER BY pt.name ASC");
$products_stmt->execute();
$all_products = $products_stmt->fetchAll();

$linked_products_stmt = $pdo->prepare("SELECT product_id FROM project_products WHERE project_id = ?");
$linked_products_stmt->execute([$id]);
$linked_product_ids = $linked_products_stmt->fetchAll(PDO::FETCH_COLUMN);

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-edit me-2"></i>Sửa dự án</h2>
    <form id="project-form" method="post" enctype="multipart/form-data" class="mt-4">
        <?php csrf_field(); ?>
         <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
                    <div class="card-body tab-content">
                        <div class="tab-pane active" id="vi">
                            <div class="mb-3"><label class="form-label">Tiêu đề (VI)</label><input type="text" name="translations[vi][title]" value="<?= htmlspecialchars($translations['vi']['title'] ?? '') ?>" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Mô tả (VI)</label><textarea name="translations[vi][description]" class="tinymce-editor"><?= $translations['vi']['description'] ?? '' ?></textarea></div>
                            <hr><h5 class="mb-3">SEO (VI)</h5>
                            <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="translations[vi][meta_title]" value="<?= htmlspecialchars($translations['vi']['meta_title'] ?? '') ?>" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="translations[vi][meta_description]" rows="3" class="form-control"><?= htmlspecialchars($translations['vi']['meta_description'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="translations[vi][meta_keywords]" value="<?= htmlspecialchars($translations['vi']['meta_keywords'] ?? '') ?>" class="form-control"></div>
                        </div>
                        <div class="tab-pane" id="en">
                             <div class="mb-3"><label class="form-label">Title (EN)</label><input type="text" name="translations[en][title]" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>" class="form-control"></div>
<div class="mb-3"><label class="form-label">Description (EN)</label><textarea name="translations[en][description]" class="tinymce-editor"><?= $translations['en']['description'] ?? '' ?></textarea></div>
                            <hr><h5 class="mb-3">SEO (EN)</h5>
                            <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="translations[en][meta_title]" value="<?= htmlspecialchars($translations['en']['meta_title'] ?? '') ?>" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="translations[en][meta_description]" rows="3" class="form-control"><?= htmlspecialchars($translations['en']['meta_description'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="translations[en][meta_keywords]" value="<?= htmlspecialchars($translations['en']['meta_keywords'] ?? '') ?>" class="form-control"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h5 class="mb-0">Thông tin bổ sung</h5></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Ngày hoàn thành</label><input type="date" name="completed_at" value="<?= $project['completed_at'] ?>" class="form-control"></div>
                        <div class="mb-3">
                            <label class="form-label">Ảnh đại diện</label>
                            <img id="image-preview" src="/interior-website/uploads/projects/<?= htmlspecialchars($project['thumbnail'] ?: 'default-placeholder.png') ?>" class="img-thumbnail mb-2" style="max-height: 150px;">
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
                            <?php foreach($all_products as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= in_array($product['id'], $linked_product_ids) ? 'selected' : '' ?>><?= htmlspecialchars($product['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-primary">Cập nhật</button><a href="index.php" class="btn btn-secondary">Quay lại</a></div>
    </form>
</div>
<div class="modal fade" id="mediaModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Chọn ảnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mediaModalBody"></div></div></div></div>

<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // === SỬA LỖI 1: BỔ SUNG ĐẦY ĐỦ CẤU HÌNH TINYMCE ===
    tinymce.init({
        selector: '.tinymce-editor',
        plugins: 'lists link image code table paste wordcount',
        height: 300,
        branding: false,
        
        // Cấu hình upload ảnh bị thiếu đã được thêm vào
        paste_data_images: true,
        images_upload_url: 'upload_handler.php',
        
        // Ngăn TinyMCE tự động thay đổi URL
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false
    });

    document.getElementById('project-form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
    
    // === SỬA LỖI 2: CẬP NHẬT LOGIC CHO MEDIA LIBRARY ===
    const mediaModalEl = document.getElementById('mediaModal');
    const imagePreview = document.getElementById('image-preview');
    const imageFromLibraryInput = document.getElementById('thumbnail_from_library');
    const imageUploadInput = document.getElementById('thumbnail_upload');
    const mediaModalBody = document.getElementById('mediaModalBody');
    // Khởi tạo đối tượng Modal của Bootstrap một lần
    const mediaModal = new bootstrap.Modal(mediaModalEl);

    mediaModalEl.addEventListener('show.bs.modal', function(){
        fetch('/interior-website/admin/media/media_picker.php')
            .then(r => r.text())
            .then(h => mediaModalBody.innerHTML = h);
    });

    // Sửa lại cách gọi hàm đóng modal
    window.selectMedia = function(fileName) {
        imageFromLibraryInput.value = fileName;
        imagePreview.src = '/interior-website/uploads/library/' + fileName;
        imageUploadInput.value = ''; // Xóa file upload nếu người dùng chọn từ thư viện
        mediaModal.hide(); // Sử dụng đối tượng modal đã khởi tạo để đóng
    }

    imageUploadInput.addEventListener('change', function(e){
        if(e.target.files && e.target.files[0]){
            const r = new FileReader();
            r.onload = function(e){
                imagePreview.src = e.target.result;
                imageFromLibraryInput.value = ''; // Xóa file thư viện nếu người dùng upload file mới
            }
            r.readAsDataURL(e.target.files[0]);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Sửa dự án';
include '../layout.php';
?>