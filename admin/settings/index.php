<?php
// Tệp: admin/settings/index.php (Phiên bản đã sửa lỗi layout)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-settings')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

// Lấy dữ liệu
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_raw = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
$stmt_trans = $pdo->query("SELECT * FROM setting_translations");
$translations_raw = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);
$translations = [];
foreach ($translations_raw as $trans) {
    $translations[$trans['language_code']][$trans['setting_key']] = $trans['setting_value'];
}
ob_start();
?>
<div class="dashboard">
    <h2 class="mb-4"><i class="fas fa-cogs me-2"></i>Cài đặt trang web</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Lưu thay đổi thành công!</div>
    <?php endif; ?>

    <?php if (isset($_GET['sitemap_success'])): ?>
        <div class="alert alert-success">
            Tệp `sitemap.xml` đã được tạo và cập nhật thành công! Bạn có thể xem nó tại <a href="/interior-website/sitemap.xml" target="_blank" class="alert-link">đây</a>.
        </div>
    <?php endif; ?>

    <form method="post" action="save.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Thông tin Doanh nghiệp (Hỗ trợ song ngữ)</h5></div>
            <div class="card-body">
                <ul class="nav nav-tabs card-header-tabs mb-3">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vi">Tiếng Việt</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#en">English</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="vi">
                        <div class="mb-3">
                            <label class="form-label">Tên website (VI)</label>
                            <input type="text" name="translations[vi][site_name]" class="form-control" value="<?= htmlspecialchars($translations['vi']['site_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ (VI)</label>
                            <input type="text" name="translations[vi][address]" class="form-control" value="<?= htmlspecialchars($translations['vi']['address'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="tab-pane" id="en">
                        <div class="mb-3">
                            <label class="form-label">Site Name (EN)</label>
                            <input type="text" name="translations[en][site_name]" class="form-control" value="<?= htmlspecialchars($translations['en']['site_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address (EN)</label>
                            <input type="text" name="translations[en][address]" class="form-control" value="<?= htmlspecialchars($translations['en']['address'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Thông tin Chung (Không dịch)</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email liên hệ</label>
                        <input type="email" name="settings[contact_email]" class="form-control" value="<?= htmlspecialchars($settings_raw['contact_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hotline</label>
                        <input type="text" name="settings[hotline]" class="form-control" value="<?= htmlspecialchars($settings_raw['hotline'] ?? '') ?>">
                    </div>
                    <hr class="my-3">
                    <h6 class="col-12 mb-3">Thông tin Chuyển khoản</h6>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên chủ tài khoản</label>
                        <input type="text" name="settings[bank_account_name]" class="form-control" value="<?= htmlspecialchars($settings_raw['bank_account_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số tài khoản</label>
                        <input type="text" name="settings[bank_account_number]" class="form-control" value="<?= htmlspecialchars($settings_raw['bank_account_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên ngân hàng</label>
                        <input type="text" name="settings[bank_name]" class="form-control" value="<?= htmlspecialchars($settings_raw['bank_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Chi nhánh (Tùy chọn)</label>
                        <input type="text" name="settings[bank_branch]" class="form-control" value="<?= htmlspecialchars($settings_raw['bank_branch'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0">SEO - Sơ đồ trang web</h5></div>
            <div class="card-body">
                <p class="text-muted">Nhấn nút bên dưới để tạo hoặc cập nhật tệp `sitemap.xml` cho trang web của bạn. Việc này giúp các công cụ tìm kiếm như Google lập chỉ mục nội dung của bạn tốt hơn.</p>
                <a href="generate_sitemap.php" class="btn btn-info">
                    <i class="fas fa-cogs me-2"></i>Tạo / Cập nhật Sitemap
                </a>
            </div>
        </div>
        
        <div class="col-12 text-end mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu tất cả thay đổi</button>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Cài đặt Website';
include '../layout.php';
?>