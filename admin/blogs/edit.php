<?php
// Tệp: admin/blogs/edit.php (HOÀN CHỈNH - ĐÃ SỬA LỖI)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// Bảo mật CSRF
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Xác thực CSRF và nạp thư viện
    require_once __DIR__ . '/../../vendor/autoload.php';
    verify_csrf_token(); 

    // 2. Khởi tạo HTML Purifier
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    try {
        $pdo->beginTransaction();
        
        // 3. Xử lý ảnh đại diện (logic cũ giữ nguyên)
        $blog_stmt = $pdo->prepare("SELECT thumbnail FROM blogs WHERE id = ?");
        $blog_stmt->execute([$id]);
        $thumbnail = $blog_stmt->fetchColumn();
        $uploadDir = __DIR__ . '/../../uploads/blogs/';
        if (!empty($_POST['thumbnail_from_library']) && basename($_POST['thumbnail_from_library']) !== $thumbnail) {
            if ($thumbnail && file_exists($uploadDir . $thumbnail)) { @unlink($uploadDir . $thumbnail); }
            $image_filename = basename($_POST['thumbnail_from_library']);
            $source_path = __DIR__ . '/../../uploads/library/' . $image_filename;
            if (file_exists($source_path)) { copy($source_path, $uploadDir . $image_filename); $thumbnail = $image_filename; }
        } elseif (isset($_FILES['thumbnail_upload']) && $_FILES['thumbnail_upload']['error'] === UPLOAD_ERR_OK) {
            if ($thumbnail && file_exists($uploadDir . $thumbnail)) { @unlink($uploadDir . $thumbnail); }
            $filename = uniqid('blog_', true) . '.' . pathinfo($_FILES['thumbnail_upload']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['thumbnail_upload']['tmp_name'], $uploadDir . $filename)) { $thumbnail = $filename; }
        }

        // 4. Cập nhật bảng `blogs`
        $stmt_blog = $pdo->prepare("UPDATE blogs SET category_id=?, thumbnail=?, status=? WHERE id=?");
        $stmt_blog->execute([$_POST['category_id'] ?: null, $thumbnail, $_POST['status'], $id]);

        // 5. LOGIC MỚI: Cập nhật hoặc chèn bản dịch một cách rõ ràng
        foreach (['vi', 'en'] as $lang) {
            $title = $_POST['title'][$lang] ?? '';
            
            if (!empty($title)) {
                $content = $purifier->purify($_POST['content'][$lang] ?? ''); // Làm sạch HTML
                $meta_title = $_POST['meta_title'][$lang] ?? '';
                $meta_description = $_POST['meta_description'][$lang] ?? '';
                $meta_keywords = $_POST['meta_keywords'][$lang] ?? '';
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

                // Kiểm tra xem bản dịch đã tồn tại chưa
                $check_stmt = $pdo->prepare("SELECT id FROM blog_translations WHERE blog_id = ? AND language_code = ?");
                $check_stmt->execute([$id, $lang]);
                
                if ($check_stmt->fetch()) {
                    // Nếu có rồi -> Cập nhật (UPDATE)
                    $update_trans_stmt = $pdo->prepare(
                        "UPDATE blog_translations SET title=?, slug=?, content=?, meta_title=?, meta_description=?, meta_keywords=? 
                         WHERE blog_id = ? AND language_code = ?"
                    );
                    $update_trans_stmt->execute([$title, $slug, $content, $meta_title, $meta_description, $meta_keywords, $id, $lang]);
                } else {
                    // Nếu chưa có -> Chèn mới (INSERT)
                    $insert_trans_stmt = $pdo->prepare(
                        "INSERT INTO blog_translations (blog_id, language_code, title, slug, content, meta_title, meta_description, meta_keywords) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $insert_trans_stmt->execute([$id, $lang, $title, $slug, $content, $meta_title, $meta_description, $meta_keywords]);
                }
            } 
            else {
                // Nếu không có tiêu đề, xóa bản dịch (nếu có)
                $pdo->prepare("DELETE FROM blog_translations WHERE blog_id = ? AND language_code = ?")->execute([$id, $lang]);
            }
        }
        
        $pdo->commit();
        logAction($pdo, $_SESSION['user']['id'] ?? null, "Cập nhật bài viết #" . $id);
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi cập nhật bài viết: " . $e->getMessage());
    }
}

// LẤY DỮ LIỆU HIỆN TẠI
$blog_stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$blog_stmt->execute([$id]);
$blog = $blog_stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) { die("Bài viết không tồn tại."); }

$translations_stmt = $pdo->prepare("SELECT language_code, title, slug, content, meta_title, meta_description, meta_keywords FROM blog_translations WHERE blog_id = ?");
$translations_stmt->execute([$id]);
$translations = $translations_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

// === SỬA LỖI SQL TẠI ĐÂY ===
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

ob_start();
?>
<div class="dashboard">
 <h2><i class="fas fa-edit me-2"></i>Chỉnh sửa bài viết</h2>
 <form id="blog-form" method="post" enctype="multipart/form-data" class="mt-4">
    <?php csrf_field(); ?>
 <div class="row g-4">
 <div class="col-lg-9">
 <div class="card shadow-sm">
 <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
 <div class="card-body tab-content">
 <div class="tab-pane active" id="vi">
 <div class="mb-3"><label class="form-label">Tiêu đề (VI)</label><input type="text" name="title[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['title'] ?? '') ?>" required></div>
<div class="mb-3"><label class="form-label">Nội dung (VI)</label><textarea name="content[vi]" class="tinymce-editor"><?= $translations['vi']['content'] ?? '' ?></textarea></div>
 <hr><h5 class="mb-3">SEO (VI)</h5>
 <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['meta_title'] ?? '') ?>"></div>
 <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[vi]" class="form-control" rows="3"><?= htmlspecialchars($translations['vi']['meta_description'] ?? '') ?></textarea></div>
 <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['meta_keywords'] ?? '') ?>"></div>
 </div>
 <div class="tab-pane" id="en">
 <div class="mb-3"><label class="form-label">Title (EN)</label><input type="text" name="title[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>"></div>
<div class="mb-3"><label class="form-label">Content (EN)</label><textarea name="content[en]" class="tinymce-editor"><?= $translations['en']['content'] ?? '' ?></textarea></div>
 <hr><h5 class="mb-3">SEO (EN)</h5>
 <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['meta_title'] ?? '') ?>"></div>
 <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[en]" class="form-control" rows="3"><?= htmlspecialchars($translations['en']['meta_description'] ?? '') ?></textarea></div>
 <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['meta_keywords'] ?? '') ?>"></div>
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
 <option value="published" <?= ($blog['status'] == 'published') ? 'selected' : '' ?>>Xuất bản</option>
 <option value="draft" <?= ($blog['status'] == 'draft') ? 'selected' : '' ?>>Bản nháp</option>
 </select>
 </div>
 <div class="mb-3">
 <label class="form-label">Chuyên mục</label>
 <select name="category_id" class="form-select"><option value="">-- Chọn --</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($blog['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></select>
 </div>
 <div class="mb-3">
 <label class="form-label">Ảnh đại diện</label>
 <img id="image-preview" src="/interior-website/uploads/blogs/<?= htmlspecialchars($blog['thumbnail'] ?: '../assets/images/default-placeholder.png') ?>" class="img-thumbnail mb-2" style="max-height: 150px;">
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
 <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button><a href="index.php" class="btn btn-secondary">Hủy</a></div>
 </form>
</div>
<div class="modal fade" id="mediaModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Chọn ảnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mediaModalBody"></div></div></div></div>
<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
// Script giữ nguyên như file create.php
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
        mediaModal.addEventListener('show.bs.modal', function () { mediaModalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border"></div></div>'; fetch('/interior-website/admin/media/media_picker.php').then(r => r.text()).then(h => { mediaModalBody.innerHTML = h; }); });
        window.selectMedia = function(f){imageFromLibraryInput.value=f;imagePreview.src='/interior-website/uploads/library/'+f;imageUploadInput.value='';bootstrap.Modal.getInstance(mediaModal).hide();}
        imageUploadInput.addEventListener('change', function(e){if(e.target.files&&e.target.files[0]){const r=new FileReader();r.onload=function(e){imagePreview.src=e.target.result;imageFromLibraryInput.value='';}
        r.readAsDataURL(e.target.files[0]);}});
    }
});
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Sửa bài viết';
include '../layout.php';
?>