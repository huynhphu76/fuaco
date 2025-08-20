<?php
// Tệp: admin/products/create.php (ĐÃ SỬA LỖI JAVASCRIPT)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('create-products')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token(); // Đảm bảo token được tạo

// Bảo mật CSRF: Tạo token


// Lấy danh mục
$language_code = 'vi';
$stmt_cat = $pdo->prepare("SELECT c.id, ct.name FROM categories c JOIN category_translations ct ON c.id = ct.category_id WHERE ct.language_code = ? ORDER BY ct.name ASC");
$stmt_cat->execute([$language_code]);
$categories = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bảo mật CSRF: Kiểm tra token
       verify_csrf_token(); // Kiểm tra token
       $config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

    
    if (!empty($_POST['name']['vi']) || !empty($_POST['name']['en'])) {
        try {
            $pdo->beginTransaction();

            // 1. Dữ liệu chung
            $category_id = $_POST['category_id'] ?: null;
            $price = $_POST['price'];
            $quantity = (int)$_POST['quantity'];
            $status = $_POST['status'];
            
            // Xử lý ảnh đại diện
            $main_image = null;
            if (!empty($_POST['main_image_from_library'])) {
                $image_filename = basename($_POST['main_image_from_library']);
                $source_path = __DIR__ . '/../../uploads/library/' . $image_filename;
                $destination_path = __DIR__ . '/../../uploads/products/' . $image_filename;
                if (file_exists($source_path)) {
                    copy($source_path, $destination_path);
                    $main_image = $image_filename;
                }
            } 
            elseif (isset($_FILES['main_image_upload']) && $_FILES['main_image_upload']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/products/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $filename = uniqid('product_', true) . '.' . pathinfo($_FILES['main_image_upload']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['main_image_upload']['tmp_name'], $uploadDir . $filename)) {
                    $main_image = $filename;
                }
            }

            // 2. Thêm vào bảng products
            $stmt_product = $pdo->prepare("INSERT INTO products (category_id, price, quantity, status, main_image) VALUES (?, ?, ?, ?, ?)");
            $stmt_product->execute([$category_id, $price, $quantity, $status, $main_image]);
            $product_id = $pdo->lastInsertId();

            // 3. Thêm vào bảng dịch
            $stmt_trans = $pdo->prepare("INSERT INTO product_translations (product_id, language_code, name, slug, description, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach (['vi', 'en'] as $lang) {
                $name = $_POST['name'][$lang] ?? '';
                if (!empty($name)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                  $clean_description = $purifier->purify($_POST['description'][$lang] ?? '');

$stmt_trans->execute([
    $product_id, $lang, $name, $slug, 
    $clean_description, // <-- Sử dụng biến đã làm sạch
    $_POST['meta_title'][$lang] ?? '', 
    $_POST['meta_description'][$lang] ?? '', 
    $_POST['meta_keywords'][$lang] ?? ''
]);
                }
            }

            // 4. Thêm thuộc tính
            if ($product_id && isset($_POST['attributes'])) {
                $stmt_attr = $pdo->prepare("INSERT INTO product_attributes (product_id, attribute_name, attribute_value) VALUES (?, ?, ?)");
                foreach ($_POST['attributes'] as $attr) {
                    if (!empty($attr['name']) && !empty($attr['value'])) {
                        $stmt_attr->execute([$product_id, trim($attr['name']), trim($attr['value'])]);
                    }
                }
            }

            $pdo->commit();
            unset($_SESSION['csrf_token']);
            logAction($pdo, $_SESSION['user_id'], "Tạo sản phẩm mới #" . $product_id);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi khi tạo sản phẩm: " . $e->getMessage());
        }
    }
}

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-plus-circle me-2"></i>Thêm sản phẩm mới</h2>
    <form id="product-form" method="post" enctype="multipart/form-data" class="mt-4">
            <?php csrf_field(); // Thêm token vào form ?>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li>
                        </ul>
                    </div>
                    <div class="card-body tab-content">
                        <div class="tab-pane active" id="vi">
                            <div class="mb-3"><label class="form-label">Tên sản phẩm (VI)</label><input type="text" name="name[vi]" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Mô tả (VI)</label><textarea name="description[vi]" class="form-control" rows="10"></textarea></div>
                            <hr><h5 class="mb-3">SEO (VI)</h5>
                            <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[vi]" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[vi]" class="form-control" rows="3"></textarea></div>
                            <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[vi]" class="form-control"></div>
                        </div>
                        <div class="tab-pane" id="en">
                             <div class="mb-3"><label class="form-label">Product Name (EN)</label><input type="text" name="name[en]" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Description (EN)</label><textarea name="description[en]" class="form-control" rows="10"></textarea></div>
                            <hr><h5 class="mb-3">SEO (EN)</h5>
                            <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[en]" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[en]" class="form-control" rows="3"></textarea></div>
                            <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[en]" class="form-control"></div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Thuộc tính sản phẩm</h5></div>
                    <div class="card-body" id="attributes-container">
                        </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-sm btn-secondary" id="add-attribute-btn"><i class="fas fa-plus"></i> Thêm thuộc tính</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Thông tin chung</h5></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="active">Hiển thị</option><option value="inactive">Ẩn</option></select></div>
                        <div class="mb-3"><label class="form-label">Danh mục</label><select name="category_id" class="form-select"><option value="">-- Chọn --</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></select></div>
                        <hr>
                        <div class="mb-3"><label class="form-label">Giá (VNĐ)</label><input type="number" name="price" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Số lượng tồn kho</label><input type="number" name="quantity" class="form-control" value="0" required></div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Ảnh đại diện</label>
                            <img id="image-preview" src="/interior-website/assets/images/default-placeholder.png" class="img-thumbnail mb-2" style="max-height: 150px;">
                            <input type="hidden" name="main_image_from_library" id="main_image_from_library">
                            <div class="input-group">
                                <input type="file" name="main_image_upload" class="form-control form-control-sm" id="main_image_upload">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#mediaModal">Thư viện</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu sản phẩm</button><a href="index.php" class="btn btn-secondary">Hủy</a></div>
    </form>
</div>

<div class="modal fade" id="mediaModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Chọn ảnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mediaModalBody"></div></div></div></div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // --- 1. SCRIPT CHO THUỘC TÍNH SẢN PHẨM (ĐÃ SỬA) ---
    const addAttributeBtn = document.getElementById('add-attribute-btn');
    const attributesContainer = document.getElementById('attributes-container');

    if (addAttributeBtn) {
        addAttributeBtn.addEventListener('click', function() {
            // Tạo một định danh duy nhất cho mỗi dòng thuộc tính
            const uniqueId = Date.now(); 
            
            // Tạo phần tử div mới cho dòng thuộc tính
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 align-items-center attribute-row';
            newRow.innerHTML = `
                <div class="col-5">
                    <input type="text" name="attributes[${uniqueId}][name]" class="form-control form-control-sm" placeholder="Tên thuộc tính (VD: Màu sắc)">
                </div>
                <div class="col-5">
                    <input type="text" name="attributes[${uniqueId}][value]" class="form-control form-control-sm" placeholder="Giá trị (VD: Xám)">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-sm btn-danger remove-attribute-btn">Xóa</button>
                </div>
            `;
            attributesContainer.appendChild(newRow);
        });
    }

    if (attributesContainer) {
        // Sử dụng event delegation để xử lý sự kiện click trên nút xóa
        attributesContainer.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-attribute-btn')) {
                // Tìm đến dòng cha và xóa nó
                e.target.closest('.attribute-row').remove();
            }
        });
    }


    // --- 2. SCRIPT CHO THƯ VIỆN MEDIA ---
    const mediaModal = document.getElementById('mediaModal');
    if(mediaModal) {
        const mediaModalBody = document.getElementById('mediaModalBody');
        const imagePreview = document.getElementById('image-preview');
        const imageFromLibraryInput = document.getElementById('main_image_from_library');
        const imageUploadInput = document.getElementById('main_image_upload');

        mediaModal.addEventListener('show.bs.modal', function () {
            mediaModalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border"></div></div>';
            fetch('/interior-website/admin/media/media_picker.php')
                .then(response => response.text())
                .then(html => { mediaModalBody.innerHTML = html; });
        });

        window.selectMedia = function(fileName) {
            imageFromLibraryInput.value = fileName;
            imagePreview.src = '/interior-website/uploads/library/' + fileName;
            imageUploadInput.value = '';
            bootstrap.Modal.getInstance(mediaModal).hide();
        }

        imageUploadInput.addEventListener('change', function(event) {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imageFromLibraryInput.value = '';
                }
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Thêm sản phẩm';
include '../layout.php';
?>