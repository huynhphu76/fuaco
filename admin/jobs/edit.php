<?php
// Tệp: admin/jobs/edit.php (Đã cập nhật)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-recruitment')) { die('Bạn không có quyền truy cập.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/log_action.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token(); // Đảm bảo token được tạo

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     verify_csrf_token(); // Kiểm tra CSRF

    // Khởi tạo HTML Purifier
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    try {
        $pdo->beginTransaction();
        $stmt_job = $pdo->prepare("UPDATE jobs SET department = ?, location = ?, salary = ?, is_active = ? WHERE id = ?");
        $stmt_job->execute([$_POST['department'], $_POST['location'], $_POST['salary'], $_POST['is_active'], $id]);

        $stmt_trans = $pdo->prepare("INSERT INTO job_translations (job_id, language_code, title, slug, description, requirements, benefits) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), slug=VALUES(slug), description=VALUES(description), requirements=VALUES(requirements), benefits=VALUES(benefits)");
        foreach(['vi', 'en'] as $lang) {
            if (!empty($_POST['translations'][$lang]['title'])) {
                $title = $_POST['translations'][$lang]['title'];
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                 $clean_description = $purifier->purify($_POST['translations'][$lang]['description'] ?? '');
                $clean_requirements = $purifier->purify($_POST['translations'][$lang]['requirements'] ?? '');
                $clean_benefits = $purifier->purify($_POST['translations'][$lang]['benefits'] ?? '');
                $stmt_trans->execute([$id, $lang, $title, $slug, $_POST['translations'][$lang]['description'], $_POST['translations'][$lang]['requirements'], $_POST['translations'][$lang]['benefits']]);
            } else {
                $pdo->prepare("DELETE FROM job_translations WHERE job_id = ? AND language_code = ?")->execute([$id, $lang]);
            }
        }
        $pdo->commit();
        logAction($pdo, $_SESSION['user']['id'], "Cập nhật tin tuyển dụng #{$id}: '" . htmlspecialchars($_POST['translations']['vi']['title']) . "'");
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Lỗi khi cập nhật: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$job) { die("Không tìm thấy vị trí tuyển dụng."); }

$trans_stmt = $pdo->prepare("SELECT language_code, title, slug, description, requirements, benefits FROM job_translations WHERE job_id = ?");
$trans_stmt->execute([$id]);
$translations = $trans_stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

ob_start();
?>
<div class="dashboard">
    <h2><i class="fas fa-edit me-2"></i>Chỉnh sửa Vị trí Tuyển dụng</h2>
    <form id="job-form" method="post" class="mt-4">
        <?php csrf_field(); ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li></ul></div>
                    <div class="card-body tab-content">
                        <div class="tab-pane active" id="vi">
                            <div class="mb-3"><label class="form-label">Chức danh (VI)*</label><input type="text" name="translations[vi][title]" class="form-control" value="<?= htmlspecialchars($translations['vi']['title'] ?? '') ?>" required></div>
                            <div class="mb-3"><label class="form-label">Mô tả công việc (VI)</label><textarea name="translations[vi][description]" class="tinymce-editor"><?= htmlspecialchars($translations['vi']['description'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Yêu cầu (VI)</label><textarea name="translations[vi][requirements]" class="tinymce-editor"><?= htmlspecialchars($translations['vi']['requirements'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Quyền lợi (VI)</label><textarea name="translations[vi][benefits]" class="tinymce-editor"><?= htmlspecialchars($translations['vi']['benefits'] ?? '') ?></textarea></div>
                        </div>
                        <div class="tab-pane" id="en">
                            <div class="mb-3"><label class="form-label">Job Title (EN)</label><input type="text" name="translations[en][title]" class="form-control" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Description (EN)</label><textarea name="translations[en][description]" class="tinymce-editor"><?= htmlspecialchars($translations['en']['description'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Requirements (EN)</label><textarea name="translations[en][requirements]" class="tinymce-editor"><?= htmlspecialchars($translations['en']['requirements'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Benefits (EN)</label><textarea name="translations[en][benefits]" class="tinymce-editor"><?= htmlspecialchars($translations['en']['benefits'] ?? '') ?></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header"><h5>Thông tin chung</h5></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Phòng ban</label><input type="text" name="department" class="form-control" value="<?= htmlspecialchars($job['department']) ?>"></div>
                        <div class="mb-3"><label class="form-label">Địa điểm</label><input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location']) ?>"></div>
                        <div class="mb-3"><label class="form-label">Mức lương</label><input type="text" name="salary" class="form-control" value="<?= htmlspecialchars($job['salary']) ?>"></div>
                        <div class="mb-3"><label class="form-label">Trạng thái</label><select name="is_active" class="form-select">
                            <option value="1" <?= $job['is_active'] ? 'selected' : '' ?>>Đang tuyển</option>
                            <option value="0" <?= !$job['is_active'] ? 'selected' : '' ?>>Ngừng tuyển</option>
                        </select></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="index.php" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/7tkog485ortkrygzgrr5o26ooowk24leppdf50yeog98r3wj/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '.tinymce-editor',
            height: 300,
            plugins: 'lists link image code table paste wordcount',
            toolbar: 'undo redo | styles | bold italic | bullist numlist | link image',
            paste_data_images: true,
            images_upload_url: '/interior-website/admin/jobs/upload_handler.php',
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false
        });
        document.getElementById('job-form').addEventListener('submit', () => tinymce.triggerSave());
    });
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Chỉnh sửa tin tuyển dụng';
include '../layout.php';
?>