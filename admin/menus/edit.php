<?php
// Tệp: admin/menus/edit.php (HOÀN CHỈNH - NÂNG CẤP TOÀN DIỆN)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-menus')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token();
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$menu_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$menu_id) { header("Location: index.php"); exit; }

// XỬ LÝ FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
verify_csrf_token();    
    try {
        $pdo->beginTransaction();
        // Cập nhật tên Menu đa ngôn ngữ
        $stmt_menu_trans = $pdo->prepare("INSERT INTO menu_translations (menu_id, language_code, name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
        foreach($_POST['translations'] as $lang => $data) {
            if(!empty($data['name'])) {
                $stmt_menu_trans->execute([$menu_id, $lang, $data['name']]);
            }
        }

        // Cập nhật các mục con (menu items)
        if (isset($_POST['items'])) {
            foreach ($_POST['items'] as $item_id => $item_data) {
                // Cập nhật thông tin chung (URL, thứ tự)
                $stmt_item = $pdo->prepare("UPDATE menu_items SET url = ?, display_order = ? WHERE id = ? AND menu_id = ?");
                $stmt_item->execute([$item_data['url'], $item_data['order'], $item_id, $menu_id]);

                // Cập nhật bản dịch cho mục con
                $stmt_item_trans = $pdo->prepare("INSERT INTO menu_item_translations (menu_item_id, language_code, title) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title)");
                foreach($item_data['title'] as $lang => $title) {
                    if (!empty($title)) {
                        $stmt_item_trans->execute([$item_id, $lang, $title]);
                    } else {
                        $pdo->prepare("DELETE FROM menu_item_translations WHERE menu_item_id = ? AND language_code = ?")->execute([$item_id, $lang]);
                    }
                }
            }
        }
        
        // Thêm mục con mới
        if (!empty($_POST['new_item']['vi']['title']) && !empty($_POST['new_item']['url'])) {
            // Thêm vào bảng `menu_items`
            $stmt_new_item = $pdo->prepare("INSERT INTO menu_items (menu_id, url) VALUES (?, ?)");
            $stmt_new_item->execute([$menu_id, $_POST['new_item']['url']]);
            $new_item_id = $pdo->lastInsertId();
            
            // Thêm bản dịch cho mục con mới
            $stmt_new_trans = $pdo->prepare("INSERT INTO menu_item_translations (menu_item_id, language_code, title) VALUES (?, ?, ?)");
            foreach($_POST['new_item'] as $lang => $data) {
                if(is_array($data) && !empty($data['title'])) {
                    $stmt_new_trans->execute([$new_item_id, $lang, $data['title']]);
                }
            }
        }

        // Xóa mục con
        if (isset($_POST['delete_item_id'])) {
            $item_id_to_delete = filter_var($_POST['delete_item_id'], FILTER_VALIDATE_INT);
            $stmt_delete = $pdo->prepare("DELETE FROM menu_items WHERE id = ? AND menu_id = ?");
            $stmt_delete->execute([$item_id_to_delete, $menu_id]);
        }
        
        $pdo->commit();
    } catch(Exception $e) {
        $pdo->rollBack();
        die("Lỗi: " . $e->getMessage());
    }
    
    unset($_SESSION['csrf_token']);
    header("Location: edit.php?id=" . $menu_id . "&success=1");
    exit;
}

// LẤY DỮ LIỆU HIỆN TẠI
$stmt_menu = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
$stmt_menu->execute([$menu_id]);
$menu = $stmt_menu->fetch(PDO::FETCH_ASSOC);
if (!$menu) { die('Không tìm thấy menu'); }

$stmt_menu_trans = $pdo->prepare("SELECT * FROM menu_translations WHERE menu_id = ?");
$stmt_menu_trans->execute([$menu_id]);
$menu_translations = $stmt_menu_trans->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

// Lấy tất cả mục con và các bản dịch của chúng
$stmt_items = $pdo->prepare("
    SELECT mi.id, mi.url, mi.display_order, mit.language_code, mit.title
    FROM menu_items mi
    LEFT JOIN menu_item_translations mit ON mi.id = mit.menu_item_id
    WHERE mi.menu_id = ?
    ORDER BY mi.display_order ASC, mi.id ASC
");
$stmt_items->execute([$menu_id]);
$raw_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Sắp xếp lại dữ liệu cho dễ sử dụng
$menu_items = [];
foreach ($raw_items as $item) {
    $id = $item['id'];
    if (!isset($menu_items[$id])) {
        $menu_items[$id] = [
            'id' => $id,
            'url' => $item['url'],
            'display_order' => $item['display_order'],
            'translations' => []
        ];
    }
    if ($item['language_code']) {
        $menu_items[$id]['translations'][$item['language_code']] = ['title' => $item['title']];
    }
}

ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4">Chỉnh sửa Menu</h2>
    <form method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Tên Menu</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Tên Menu (VI)</label><input type="text" name="translations[vi][name]" value="<?= htmlspecialchars($menu_translations['vi']['name'] ?? '') ?>" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Menu Name (EN)</label><input type="text" name="translations[en][name]" value="<?= htmlspecialchars($menu_translations['en']['name'] ?? '') ?>" class="form-control"></div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0">Các mục trong Menu</h5></div>
            <div class="card-body">
                <?php foreach ($menu_items as $item): ?>
                <div class="p-3 border rounded mb-3">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Tiêu đề (VI)</label><input type="text" name="items[<?= $item['id'] ?>][title][vi]" value="<?= htmlspecialchars($item['translations']['vi']['title'] ?? '') ?>" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Title (EN)</label><input type="text" name="items[<?= $item['id'] ?>][title][en]" value="<?= htmlspecialchars($item['translations']['en']['title'] ?? '') ?>" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">URL</label><input type="text" name="items[<?= $item['id'] ?>][url]" value="<?= htmlspecialchars($item['url']) ?>" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Thứ tự</label><input type="number" name="items[<?= $item['id'] ?>][order]" value="<?= $item['display_order'] ?>" class="form-control"></div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" name="delete_item_id" value="<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa mục này?')">Xóa mục</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer">
                <h6 class="mb-3">Thêm mục mới</h6>
                <div class="row g-3">
                    <div class="col-md-3"><input type="text" name="new_item[vi][title]" class="form-control" placeholder="Tiêu đề (VI)"></div>
                    <div class="col-md-3"><input type="text" name="new_item[en][title]" class="form-control" placeholder="Title (EN)"></div>
                    <div class="col-md-3"><input type="text" name="new_item[url]" class="form-control" placeholder="URL, ví dụ: /lien-he"></div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Lưu tất cả thay đổi</button>
            <a href="index.php" class="btn btn-secondary">Quay lại danh sách</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Chỉnh sửa Menu';
include '../layout.php';
?>