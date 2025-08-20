<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-categories')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();

// Lấy danh mục từ bảng dịch để hiển thị
$language_code = 'vi';
$stmt_cat = $pdo->prepare("SELECT c.id, ct.name FROM categories c JOIN category_translations ct ON c.id = ct.category_id WHERE ct.language_code = ? ORDER BY ct.name ASC");
$stmt_cat->execute([$language_code]);
$categories = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    // SỬA LỖI: Kiểm tra xem có ít nhất một tên được điền
    if (!empty($_POST['name']['vi']) || !empty($_POST['name']['en'])) {
        try {
            $pdo->beginTransaction();
            
            // 1. Tạo một mục rỗng trong bảng chính để lấy ID
            $pdo->query("INSERT INTO categories (id) VALUES (NULL)");
            $category_id = $pdo->lastInsertId();

            // 2. Thêm các bản dịch vào bảng translations
            foreach (['vi', 'en'] as $lang) {
                $name = $_POST['name'][$lang] ?? '';
                $description = $_POST['description'][$lang] ?? '';
                
                // Chỉ lưu khi có tên
                if (!empty($name)) {
                    // Tự động tạo slug nếu để trống
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    
                    $stmt = $pdo->prepare("INSERT INTO category_translations (category_id, language_code, name, slug, description) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$category_id, $lang, $name, $slug, $description]);
                }
            }

            $pdo->commit();
            logAction($pdo, $_SESSION['user_id'], "Tạo danh mục mới #" . $category_id);
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi khi tạo danh mục: " . $e->getMessage());
        }
    }
}
ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4">Thêm danh mục đa ngôn ngữ</h2>
    <form method="POST">
        <?php csrf_field(); ?>
        <ul class="nav nav-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vi-tab-pane" type="button">Tiếng Việt</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#en-tab-pane" type="button">English</button></li></ul>
        <div class="tab-content card" id="myTabContent" style="border-top-left-radius: 0;">
            <div class="tab-pane fade show active" id="vi-tab-pane">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Tên danh mục (Tiếng Việt)</label><input type="text" name="name[vi]" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Mô tả (Tiếng Việt)</label><textarea name="description[vi]" class="form-control" rows="3"></textarea></div>
                </div>
            </div>
            <div class="tab-pane fade" id="en-tab-pane">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Category Name (English)</label><input type="text" name="name[en]" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Description (English)</label><textarea name="description[en]" class="form-control" rows="3"></textarea></div>
                </div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-success">Thêm mới</button><a href="index.php" class="btn btn-secondary">Quay lại</a></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Thêm danh mục';
include '../layout.php';
?>