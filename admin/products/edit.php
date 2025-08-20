<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('edit-products')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
require_once __DIR__ . '/../../vendor/autoload.php';
get_csrf_token();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$product_stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product_stmt->execute([$id]);
$product = $product_stmt->fetch();
if (!$product) { die("Sản phẩm không tồn tại."); }

$translations_stmt = $pdo->prepare("SELECT language_code, name, slug, description, meta_title, meta_description, meta_keywords FROM product_translations WHERE product_id = ?");
$translations_stmt->execute([$id]);
$translations = $translations_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

$language_code = 'vi';
$stmt_cat = $pdo->prepare("SELECT c.id, ct.name FROM categories c JOIN category_translations ct ON c.id = ct.category_id WHERE ct.language_code = ? ORDER BY ct.name ASC");
$stmt_cat->execute([$language_code]);
$categories = $stmt_cat->fetchAll();

$attributes_stmt = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id = ?");
$attributes_stmt->execute([$id]);
$attributes = $attributes_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Xác thực CSRF và khởi tạo Purifier
    verify_csrf_token();
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    try {
        $pdo->beginTransaction();

        // 2. Xử lý ảnh đại diện (giữ nguyên logic cũ)
        $main_image = $product['main_image'];
        $uploadDir = __DIR__ . '/../../uploads/products/';
        if (!empty($_POST['main_image_from_library']) && basename($_POST['main_image_from_library']) !== $main_image) {
            if ($main_image && file_exists($uploadDir . $main_image)) { @unlink($uploadDir . $main_image); }
            $image_filename = basename($_POST['main_image_from_library']);
            $source_path = __DIR__ . '/../../uploads/library/' . $image_filename;
            if (file_exists($source_path)) { copy($source_path, $uploadDir . $image_filename); $main_image = $image_filename; }
        } 
        elseif (isset($_FILES['main_image_upload']) && $_FILES['main_image_upload']['error'] === UPLOAD_ERR_OK) {
            if ($main_image && file_exists($uploadDir . $main_image)) { @unlink($uploadDir . $main_image); }
            $filename = uniqid('product_', true) . '.' . pathinfo($_FILES['main_image_upload']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['main_image_upload']['tmp_name'], $uploadDir . $filename)) { $main_image = $filename; }
        }

        // 3. Cập nhật bảng `products`
        $stmt_product = $pdo->prepare("UPDATE products SET category_id=?, price=?, quantity=?, status=?, main_image=? WHERE id=?");
        $stmt_product->execute([$_POST['category_id'] ?: null, $_POST['price'], (int)$_POST['quantity'], $_POST['status'], $main_image, $id]);

        // 4. Cập nhật hoặc chèn bản dịch
        foreach (['vi', 'en'] as $lang) {
            $name = $_POST['name'][$lang] ?? '';
            if (!empty($name)) {
                $description = $purifier->purify($_POST['description'][$lang] ?? ''); // Làm sạch HTML
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

                $check_stmt = $pdo->prepare("SELECT id FROM product_translations WHERE product_id = ? AND language_code = ?");
                $check_stmt->execute([$id, $lang]);

                if ($check_stmt->fetch()) {
                    $update_trans_stmt = $pdo->prepare("UPDATE product_translations SET name=?, slug=?, description=?, meta_title=?, meta_description=?, meta_keywords=? WHERE product_id=? AND language_code=?");
                    $update_trans_stmt->execute([$name, $slug, $description, $_POST['meta_title'][$lang] ?? '', $_POST['meta_description'][$lang] ?? '', $_POST['meta_keywords'][$lang] ?? '', $id, $lang]);
                } else {
                    $insert_trans_stmt = $pdo->prepare("INSERT INTO product_translations (product_id, language_code, name, slug, description, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $insert_trans_stmt->execute([$id, $lang, $name, $slug, $description, $_POST['meta_title'][$lang] ?? '', $_POST['meta_description'][$lang] ?? '', $_POST['meta_keywords'][$lang] ?? '']);
                }
            } else {
                $pdo->prepare("DELETE FROM product_translations WHERE product_id = ? AND language_code = ?")->execute([$id, $lang]);
            }
        }

        // 5. Cập nhật thuộc tính
        $pdo->prepare("DELETE FROM product_attributes WHERE product_id = ?")->execute([$id]);
        if (isset($_POST['attributes'])) {
            $stmt_attr = $pdo->prepare("INSERT INTO product_attributes (product_id, attribute_name, attribute_value) VALUES (?, ?, ?)");
            foreach ($_POST['attributes'] as $attr) {
                if (!empty($attr['name']) && !empty($attr['value'])) {
                    $stmt_attr->execute([$id, trim($attr['name']), trim($attr['value'])]);
                }
            }
        }

        $pdo->commit();
        logAction($pdo, $_SESSION['user']['id'], "Cập nhật sản phẩm #{$id}");
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi cập nhật sản phẩm: " . $e->getMessage());
    }
}
ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-edit me-2"></i>Chỉnh sửa sản phẩm</h2>
    <form id="product-form" method="post" enctype="multipart/form-data" class="mt-4">
        <?php csrf_field(); ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <ul class="nav nav-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vi-tab-pane" type="button">Tiếng Việt</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#en-tab-pane" type="button">English</button></li></ul>
                <div class="tab-content card mb-4" style="border-top-left-radius: 0;">
                    <div class="tab-pane fade show active" id="vi-tab-pane"><div class="card-body"><div class="mb-3"><label class="form-label">Tên sản phẩm (VI)</label><input type="text" name="name[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['name'] ?? '') ?>" required></div><div class="mb-3"><label class="form-label">Mô tả (VI)</label><textarea name="description[vi]" class="form-control" rows="10"><?= htmlspecialchars($translations['vi']['description'] ?? '') ?></textarea></div><hr><h5 class="mb-3">SEO (VI)</h5><div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['meta_title'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[vi]" class="form-control" rows="3"><?= htmlspecialchars($translations['vi']['meta_description'] ?? '') ?></textarea></div><div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[vi]" class="form-control" value="<?= htmlspecialchars($translations['vi']['meta_keywords'] ?? '') ?>"></div></div></div>
                    <div class="tab-pane fade" id="en-tab-pane"><div class="card-body"><div class="mb-3"><label class="form-label">Product Name (EN)</label><input type="text" name="name[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['name'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Description (EN)</label><textarea name="description[en]" class="form-control" rows="10"><?= htmlspecialchars($translations['en']['description'] ?? '') ?></textarea></div><hr><h5 class="mb-3">SEO (EN)</h5><div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['meta_title'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description[en]" class="form-control" rows="3"><?= htmlspecialchars($translations['en']['meta_description'] ?? '') ?></textarea></div><div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords[en]" class="form-control" value="<?= htmlspecialchars($translations['en']['meta_keywords'] ?? '') ?>"></div></div></div>
                </div>

                <div class="card shadow-sm"><div class="card-header"><h5 class="mb-0">Thuộc tính sản phẩm</h5></div><div class="card-body" id="attributes-container"><?php foreach($attributes as $index => $attr): ?><div class="row mb-2 align-items-center"><div class="col-5"><input type="text" name="attributes[<?= $index ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($attr['attribute_name']) ?>"></div><div class="col-5"><input type="text" name="attributes[<?= $index ?>][value]" class="form-control form-control-sm" value="<?= htmlspecialchars($attr['attribute_value']) ?>"></div><div class="col-2"><button type="button" class="btn btn-sm btn-danger remove-attribute-btn">Xóa</button></div></div><?php endforeach; ?></div><div class="card-footer"><button type="button" class="btn btn-sm btn-secondary" id="add-attribute-btn"><i class="fas fa-plus"></i> Thêm thuộc tính</button></div></div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm"><div class="card-header"><h5 class="mb-0">Thông tin chung</h5></div><div class="card-body"><div class="mb-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="active" <?= ($product['status'] == 'active') ? 'selected' : '' ?>>Hiển thị</option><option value="inactive" <?= ($product['status'] == 'inactive') ? 'selected' : '' ?>>Ẩn</option></select></div><div class="mb-3"><label class="form-label">Danh mục</label><select name="category_id" class="form-select"><option value="">-- Chọn --</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?></select></div><hr><div class="mb-3"><label class="form-label">Giá (VNĐ)</label><input type="number" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required></div><div class="mb-3"><label class="form-label">Số lượng tồn kho</label><input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($product['quantity']) ?>" required></div><hr><div class="mb-3"><label class="form-label">Ảnh đại diện</label><img id="image-preview" src="/interior-website/uploads/products/<?= htmlspecialchars($product['main_image'] ?: 'default-placeholder.png') ?>" class="img-thumbnail mb-2" style="max-height: 150px;"><input type="hidden" name="main_image_from_library" id="main_image_from_library"><div class="input-group"><input type="file" name="main_image_upload" class="form-control form-control-sm" id="main_image_upload"><button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#mediaModal">Thư viện</button></div></div></div></div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button><a href="index.php" class="btn btn-secondary">Quay lại</a></div>
    </form>
</div>
<div class="modal fade" id="mediaModal" ...></div>
<script> /* JavaScript cho media, thuộc tính và SEO preview */ </script>
<?php
$content = ob_get_clean();
$pageTitle = 'Chỉnh sửa sản phẩm';
include '../layout.php';
?>