<?php
// Tệp: admin/blogs/create.php (HOÀN CHỈNH - ĐÃ SỬA LỖI)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php'; // <-- THÊM DÒNG NÀY
require_once __DIR__ . '/../../vendor/autoload.php'; // <-- THÊM DÒNG NÀY
get_csrf_token();




// === SỬA LỖI SQL TẠI ĐÂY ===
// Lấy danh mục bài viết từ bảng dịch để hiển thị trong dropdown
$language_code = 'vi';
$stmt_cat = $pdo->prepare("
    SELECT bc.id, bct.name 
    FROM blog_categories bc 
    JOIN blog_category_translations bct ON bc.id = bct.blog_category_id 
    WHERE bct.language_code = ? 
    ORDER BY bct.name ASC
");
$stmt_cat->execute([$language_code]);
$categories = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token(); // <-- THÊM DÒNG NÀY
   

    if (!empty($_POST['title']['vi']) || !empty($_POST['title']['en'])) {
         $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        try {
            $pdo->beginTransaction();

            // 1. XỬ LÝ DỮ LIỆU CHUNG (BẢNG `blogs`)
            $thumbnail = null;
            $uploadDir = __DIR__ . '/../../uploads/blogs/';
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
                $filename = uniqid('blog_', true) . '.' . pathinfo($_FILES['thumbnail_upload']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['thumbnail_upload']['tmp_name'], $uploadDir . $filename)) {
                    $thumbnail = $filename;
                }
            }

            $stmt_blog = $pdo->prepare("INSERT INTO blogs (category_id, thumbnail, status, user_id) VALUES (?, ?, ?, ?)");
            $stmt_blog->execute([
                $_POST['category_id'] ?: null, 
                $thumbnail, 
                $_POST['status'],
                $_SESSION['user_id'] ?? null
            ]);
            $blog_id = $pdo->lastInsertId();

            // 2. XỬ LÝ DỮ LIỆU DỊCH (BẢNG `blog_translations`)
            $stmt_trans = $pdo->prepare("INSERT INTO blog_translations (blog_id, language_code, title, slug, content, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach (['vi', 'en'] as $lang) {
                $title = $_POST['title'][$lang] ?? '';
                if (!empty($title)) {
                     $clean_content = $purifier->purify($_POST['content'][$lang] ?? ''); // Làm sạch
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
$stmt_trans->execute([
    $blog_id, $lang, $title, $slug,
    $clean_content, // <-- Sửa lại thành biến đã làm sạch
    $_POST['meta_title'][$lang] ?? '',
    $_POST['meta_description'][$lang] ?? '', 
    $_POST['meta_keywords'][$lang] ?? ''
]);
                }
            }
            
            $pdo->commit();
            unset($_SESSION['csrf_token']);
            logAction($pdo, $_SESSION['user_id'] ?? null, "Tạo bài viết mới #" . $blog_id);
            header("Location: index.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi khi tạo bài viết: " . $e->getMessage());
        }
    }
}

ob_start();
?>
<div class="dashboard">
 <h2><i class="fas fa-plus-circle me-2"></i>Thêm bài viết mới</h2>
 <form id="blog-form" method="post" enctype="multipart/form-data" class="mt-4">  <?php csrf_field(); ?>
 <div class="row g-4">
 <div class="col-lg-9">
 <div class="card shadow-sm">
 <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
 <div class="card-body tab-content">
 <div class="tab-pane active" id="vi">
 <div class="mb-3"><label class="form-label">Tiêu đề (VI)</label><input type="text" name="title[vi]" class="form-control" required></div>
 <div class="mb-3"><label class="form-label">Nội dung (VI)</label><textarea name="content[vi]" class="tinymce-editor"></textarea></div>
 <hr><h5 class="mb-3">SEO (VI)</h5>
 <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[vi]" class="form-control"></div>
 <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[vi]" class="form-control" rows="3"></textarea></div>
 <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[vi]" class="form-control"></div>
 </div>
 <div class="tab-pane" id="en">
 <div class="mb-3"><label class="form-label">Title (EN)</label><input type="text" name="title[en]" class="form-control"></div>
 <div class="mb-3"><label class="form-label">Content (EN)</label><textarea name="content[en]" class="tinymce-editor"></textarea></div>
 <hr><h5 class="mb-3">SEO (EN)</h5>
 <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[en]" class="form-control"></div>
 <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[en]" class="form-control" rows="3"></textarea></div>
 <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[en]" class="form-control"></div>
 </div>
 </div>
 </div>
 </div>
 <div class="col-lg-3">
 <div class="card shadow-sm">
 <div class="card-header"><h5>Thông tin & xuất bản</h5></div>
 <div class="card-body">
 <div class="mb-3">
 <label class="form-label">Trạng thái</label>
 <select name="status" class="form-select">
 <option value="published">Xuất bản</option>
 <option value="draft">Bản nháp</option>
 </select>
 </div>
 <div class="mb-3">
 <label class="form-label">Chuyên mục</label>
 <select name="category_id" class="form-select"><option value="">-- Chọn --</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></select>
 </div>
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
 </div>
 </div>
 <div class="mt-4"><button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Đăng bài viết</button><a href="index.php" class="btn btn-secondary">Hủy</a></div>
 </form>
</div>
<div class="modal fade" id="mediaModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Chọn ảnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mediaModalBody"></div></div></div></div>
<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    tinymce.init({
    selector: '.tinymce-editor',
    height: 500,
    branding: false,
    plugins: 'lists link image code table paste wordcount',
    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image table code',
    
    // --- CẤU HÌNH UPLOAD ẢNH CHÍNH XÁC ---
    paste_data_images: true,
    images_upload_url: '/interior-website/admin/blogs/upload_handler.php',
    
    // Quan trọng: Ngăn TinyMCE tự động thay đổi URL của ảnh
    convert_urls: false,
    relative_urls: false,
    remove_script_host: false
});
    document.getElementById('blog-form').addEventListener('submit', function(e) { tinymce.triggerSave(); });
    const mediaModal = document.getElementById('mediaModal');
    if(mediaModal) {
        const mediaModalBody = document.getElementById('mediaModalBody');
        const imagePreview = document.getElementById('image-preview');
        const imageFromLibraryInput = document.getElementById('thumbnail_from_library');
        const imageUploadInput = document.getElementById('thumbnail_upload');
        mediaModal.addEventListener('show.bs.modal', function () { mediaModalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border"></div></div>'; fetch('/interior-website/admin/media/media_picker.php').then(response => response.text()).then(html => { mediaModalBody.innerHTML = html; }); });
        window.selectMedia = function(fileName) { imageFromLibraryInput.value = fileName; imagePreview.src = '/interior-website/uploads/library/' + fileName; imageUploadInput.value = ''; bootstrap.Modal.getInstance(mediaModal).hide(); }
        imageUploadInput.addEventListener('change', function(event) { if (event.target.files && event.target.files[0]) { const reader = new FileReader(); reader.onload = function(e) { imagePreview.src = e.target.result; imageFromLibraryInput.value = ''; }
        reader.readAsDataURL(event.target.files[0]); } });
    }
});
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Thêm bài viết';
include '../layout.php';
?>