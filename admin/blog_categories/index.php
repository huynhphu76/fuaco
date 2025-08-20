<?php
// Tệp: admin/blog_categories/index.php (HOÀN CHỈNH - BAO GỒM CHỨC NĂNG SỬA)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-blogs')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';

// Bảo mật CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Xử lý form thêm/sửa chuyên mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_category'])) {
    // Kiểm tra CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Lỗi xác thực CSRF!');
    }

    $id = $_POST['id'] ?? null;
    $translations = $_POST['translations'];

    if (!empty($translations['vi']['name'])) {
        try {
            $pdo->beginTransaction();

            if ($id) { // Cập nhật chuyên mục đã có
                $blog_category_id = $id;
            } else { // Thêm chuyên mục mới
                $stmt_main = $pdo->prepare("INSERT INTO blog_categories () VALUES ()");
                $stmt_main->execute();
                $blog_category_id = $pdo->lastInsertId();
            }

            $stmt_trans = $pdo->prepare("INSERT INTO blog_category_translations (blog_category_id, language_code, name, slug, description) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), slug=VALUES(slug), description=VALUES(description)");
            
            foreach (['vi', 'en'] as $lang) {
                $name = $translations[$lang]['name'] ?? '';
                if (!empty($name)) {
                    $slug = $translations[$lang]['slug'] ?? '';
                    if (empty($slug)) {
                        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    }
                    $stmt_trans->execute([
                        $blog_category_id, $lang, $name, $slug,
                        $translations[$lang]['description'] ?? ''
                    ]);
                } else {
                    $pdo->prepare("DELETE FROM blog_category_translations WHERE blog_category_id = ? AND language_code = ?")->execute([$blog_category_id, $lang]);
                }
            }
            
            $pdo->commit();
            
            $log_message = $id ? "Cập nhật chuyên mục bài viết #{$id}" : "Tạo chuyên mục bài viết mới";
            logAction($pdo, $_SESSION['user_id'] ?? null, $log_message);

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi: " . $e->getMessage());
        }
    }
    unset($_SESSION['csrf_token']);
    header("Location: index.php?success=1");
    exit;
}

// Lấy danh sách chuyên mục (hiển thị tiếng Việt)
$stmt = $pdo->prepare("
    SELECT bc.id, bct.name, bct.slug
    FROM blog_categories bc
    LEFT JOIN blog_category_translations bct ON bc.id = bct.blog_category_id AND bct.language_code = 'vi'
    ORDER BY bct.name ASC
");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4"><i class="fas fa-folder-open me-2"></i>Quản lý Chuyên mục Bài viết</h2>
    
    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0" id="form-title">Thêm Chuyên mục mới</h5></div>
                <div class="card-body">
                    <form id="category-form" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="id" id="category_id">

                        <ul class="nav nav-tabs nav-fill mb-3"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="vi">
                                <div class="mb-3"><label class="form-label">Tên chuyên mục (VI)</label><input type="text" name="translations[vi][name]" id="name_vi" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">Đường dẫn (slug)</label><input type="text" name="translations[vi][slug]" id="slug_vi" class="form-control"><small class="form-text text-muted">Để trống để tự tạo.</small></div>
                                <div class="mb-3"><label class="form-label">Mô tả (VI)</label><textarea name="translations[vi][description]" id="description_vi" class="form-control" rows="3"></textarea></div>
                            </div>
                            <div class="tab-pane" id="en">
                                <div class="mb-3"><label class="form-label">Category Name (EN)</label><input type="text" name="translations[en][name]" id="name_en" class="form-control"></div>
                                <div class="mb-3"><label class="form-label">Slug (EN)</label><input type="text" name="translations[en][slug]" id="slug_en" class="form-control"></div>
                                <div class="mb-3"><label class="form-label">Description (EN)</label><textarea name="translations[en][description]" id="description_en" class="form-control" rows="3"></textarea></div>
                            </div>
                        </div>
                        
                        <hr>
                        <button type="submit" name="submit_category" id="form_button" class="btn btn-primary">Thêm mới</button>
                        <button type="button" id="cancel_edit_btn" class="btn btn-secondary d-none">Hủy</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark"><tr><th>Tên Chuyên mục (VI)</th><th>Đường dẫn (slug)</th><th style="width: 100px;">Hành động</th></tr></thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= htmlspecialchars($cat['name'] ?? '[Chưa có tên]') ?></td>
                                <td><?= htmlspecialchars($cat['slug'] ?? '') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $cat['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= $cat['id'] ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-btn');
    const form = document.getElementById('category-form');
    const cancelBtn = document.getElementById('cancel_edit_btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            
            fetch(`get_category_details.php?id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('category_id').value = data.id;
                        
                        ['vi', 'en'].forEach(lang => {
                            document.getElementById(`name_${lang}`).value = '';
                            document.getElementById(`slug_${lang}`).value = '';
                            document.getElementById(`description_${lang}`).value = '';
                        });

                        data.translations.forEach(trans => {
                            document.getElementById(`name_${trans.language_code}`).value = trans.name;
                            document.getElementById(`slug_${trans.language_code}`).value = trans.slug;
                            document.getElementById(`description_${trans.language_code}`).value = trans.description;
                        });
                        
                        document.getElementById('form-title').textContent = 'Chỉnh sửa Chuyên mục';
                        document.getElementById('form_button').textContent = 'Cập nhật';
                        cancelBtn.classList.remove('d-none');
                        window.scrollTo(0, 0);
                    }
                });
        });
    });

    cancelBtn.addEventListener('click', function() {
        form.reset();
        document.getElementById('category_id').value = '';
        document.getElementById('form-title').textContent = 'Thêm Chuyên mục mới';
        document.getElementById('form_button').textContent = 'Thêm mới';
        this.classList.add('d-none');
    });
});
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Chuyên mục Bài viết';
include '../layout.php';
?>