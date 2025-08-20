<?php
// Tệp: admin/theme_options/index.php (HOÀN THIỆN)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-theme')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

// Lấy tất cả các tùy chọn không dịch
$options_stmt = $pdo->query("SELECT option_key, option_value FROM theme_options");
$options = $options_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Lấy tất cả các tùy chọn có dịch
$options_trans_stmt = $pdo->query("SELECT option_key, language_code, option_value FROM theme_option_translations");
$translations_raw = $options_trans_stmt->fetchAll(PDO::FETCH_ASSOC);

// Sắp xếp lại mảng translations cho dễ sử dụng
$options_translations = [];
foreach ($translations_raw as $trans) {
    $options_translations[$trans['language_code']][$trans['option_key']] = $trans['option_value'];
}

$languages = ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh'];

// Lấy danh sách menu để chọn cho footer
$menus_stmt = $pdo->prepare("
    SELECT m.id, mt.name 
    FROM menus m 
    JOIN menu_translations mt ON m.id = mt.menu_id 
    WHERE mt.language_code = 'vi'
");
$menus_stmt->execute();
$all_menus = $menus_stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-paint-brush me-2"></i>Tùy biến Giao diện</h2>
        <div>
            <a href="../projects/create.php" class="btn btn-info"><i class="fas fa-plus me-2"></i>Thêm Dự án mới</a>
            <a href="../blogs/create.php" class="btn btn-info"><i class="fas fa-plus me-2"></i>Thêm Bài viết mới</a>
        </div>
    </div>
    <p>Quản lý các yếu tố hiển thị chung trên toàn bộ website.</p>

<form action="save.php" method="POST" enctype="multipart/form-data" class="mt-4">
    <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="lang-tabs" role="tablist">
                    <?php foreach ($languages as $code => $name): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $code == 'vi' ? 'active' : '' ?>" id="tab-<?= $code ?>" data-bs-toggle="tab" data-bs-target="#content-<?= $code ?>" type="button" role="tab"><?= $name ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="lang-tabs-content">
                    <?php foreach ($languages as $code => $name): ?>
                    <div class="tab-pane fade <?= $code == 'vi' ? 'show active' : '' ?>" id="content-<?= $code ?>" role="tabpanel">
                        
                        <h4 class="mb-3">Nội dung Trang chủ</h4>
                        
                        <div class="p-3 border rounded mb-4">
                            <h5 class="fw-bold">Mục "Sản Phẩm Tinh Hoa"</h5>
                            <div class="mb-3"><label class="form-label">Tiêu đề</label><input type="text" class="form-control" name="translations[<?= $code ?>][featured_products_title]" value="<?= htmlspecialchars($options_translations[$code]['featured_products_title'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Phụ đề</label><textarea class="form-control" rows="2" name="translations[<?= $code ?>][featured_products_subtitle]"><?= htmlspecialchars($options_translations[$code]['featured_products_subtitle'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Chữ trên nút "Xem tất cả"</label><input type="text" class="form-control" name="translations[<?= $code ?>][view_all_products_button]" value="<?= htmlspecialchars($options_translations[$code]['view_all_products_button'] ?? 'Xem tất cả sản phẩm') ?>"></div>
                        </div>

                        <div class="p-3 border rounded mb-4">
                            <h5 class="fw-bold">Mục "Dự Án Tiêu Biểu"</h5>
                             <div class="mb-3"><label class="form-label">Tiêu đề</label><input type="text" class="form-control" name="translations[<?= $code ?>][project_section_title]" value="<?= htmlspecialchars($options_translations[$code]['project_section_title'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Phụ đề</label><textarea class="form-control" rows="2" name="translations[<?= $code ?>][project_section_subtitle]"><?= htmlspecialchars($options_translations[$code]['project_section_subtitle'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Chữ trên nút "Xem tất cả"</label><input type="text" class="form-control" name="translations[<?= $code ?>][view_all_projects_button]" value="<?= htmlspecialchars($options_translations[$code]['view_all_projects_button'] ?? 'Xem tất cả dự án') ?>"></div>
                        </div>

                        <div class="p-3 border rounded mb-4">
                             <h5 class="fw-bold">Mục "Góc Cảm Hứng" (Bài viết)</h5>
                            <div class="mb-3"><label class="form-label">Tiêu đề</label><input type="text" class="form-control" name="translations[<?= $code ?>][blog_section_title]" value="<?= htmlspecialchars($options_translations[$code]['blog_section_title'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Phụ đề</label><textarea class="form-control" rows="2" name="translations[<?= $code ?>][blog_section_subtitle]"><?= htmlspecialchars($options_translations[$code]['blog_section_subtitle'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Chữ trên nút "Xem tất cả"</label><input type="text" class="form-control" name="translations[<?= $code ?>][view_all_blog_button]" value="<?= htmlspecialchars($options_translations[$code]['view_all_blog_button'] ?? 'Xem tất cả bài viết') ?>"></div>
                        </div>
                        
                        <div class="p-3 border rounded mb-4">
                            <h5 class="fw-bold">Mục "Đánh giá của khách hàng"</h5>
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" name="translations[<?= $code ?>][testimonial_section_title]" value="<?= htmlspecialchars($options_translations[$code]['testimonial_section_title'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phụ đề</label>
                                <textarea class="form-control" rows="2" name="translations[<?= $code ?>][testimonial_section_subtitle]"><?= htmlspecialchars($options_translations[$code]['testimonial_section_subtitle'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="p-3 border rounded mb-4">
                            <h5 class="fw-bold">Mục Lịch Hẹn</h5>
 
<hr>
                            <div class="mb-3"><label class="form-label">Tiêu đề chính</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_section_title]" value="<?= htmlspecialchars($options_translations[$code]['appointment_section_title'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Phụ đề/Mô tả</label><textarea class="form-control" rows="2" name="translations[<?= $code ?>][appointment_section_subtitle]"><?= htmlspecialchars($options_translations[$code]['appointment_section_subtitle'] ?? '') ?></textarea></div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Nhãn "Họ và Tên"</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_name_label]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_name_label'] ?? '') ?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nhãn "Số Điện Thoại"</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_phone_label]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_phone_label'] ?? '') ?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nhãn "Email"</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_email_label]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_email_label'] ?? '') ?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nhãn "Ngày hẹn"</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_date_label]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_date_label'] ?? '') ?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nhãn "Giờ hẹn"</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_time_label]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_time_label'] ?? '') ?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nhãn "Ghi chú"</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_note_label]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_note_label'] ?? '') ?>"></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Chữ trên nút bấm</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_form_button_text]" value="<?= htmlspecialchars($options_translations[$code]['appointment_form_button_text'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Thông báo gửi thành công</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_success_message]" value="<?= htmlspecialchars($options_translations[$code]['appointment_success_message'] ?? '') ?>"></div>
                            <div class="mb-3"><label class="form-label">Thông báo gửi thất bại</label><input type="text" class="form-control" name="translations[<?= $code ?>][appointment_error_message]" value="<?= htmlspecialchars($options_translations[$code]['appointment_error_message'] ?? '') ?>"></div>
                        </div>

                        <div class="p-3 border rounded mb-4">
                            <h5 class="fw-bold">Nội dung chung</h5>
                            <div class="mb-3"><label class="form-label">Tiêu đề Banner Tuyên Ngôn (Parallax)</label><textarea class="form-control" rows="3" name="translations[<?= $code ?>][brand_statement_title]"><?= htmlspecialchars($options_translations[$code]['brand_statement_title'] ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Mô tả ngắn ở Footer</label><textarea class="form-control" rows="3" name="translations[<?= $code ?>][footer_about]"><?= htmlspecialchars($options_translations[$code]['footer_about'] ?? '') ?></textarea></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header"><h4>Cài đặt chung (Không theo ngôn ngữ)</h4></div>
            <div class="card-body">
                <h5>Logo & Favicon</h5>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Logo Sáng màu</label><input type="file" class="form-control" name="logo_light"><?php if(!empty($options['logo_light'])): ?><img src="<?= BASE_URL . htmlspecialchars($options['logo_light']) ?>" class="img-thumbnail mt-2" width="150"><?php endif; ?></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Logo Tối màu</label><input type="file" class="form-control" name="logo_dark"><?php if(!empty($options['logo_dark'])): ?><img src="<?= BASE_URL . htmlspecialchars($options['logo_dark']) ?>" class="img-thumbnail mt-2" width="150" style="background-color: #f0f0f0;"><?php endif; ?></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Favicon</label><input type="file" class="form-control" name="favicon"><?php if(!empty($options['favicon'])): ?><img src="<?= BASE_URL . htmlspecialchars($options['favicon']) ?>" class="img-thumbnail mt-2" width="50"><?php endif; ?></div>
                </div>
                <hr>
                <h5>Ảnh Nền</h5>.
                <div class="mb-3"><label class="form-label">Ảnh nền Banner Tuyên Ngôn</label><input type="file" class="form-control" name="banner_image"><?php if(!empty($options['banner_image'])): ?><img src="<?= BASE_URL . htmlspecialchars($options['banner_image']) ?>" class="img-thumbnail mt-2" width="300"><?php endif; ?></div>
                <div class="mb-3">
    <label for="appointment_section_image" class="form-label">Ảnh Hiển Thị Mục Lịch Hẹn</label>
    <?php $current_appt_image = $options['appointment_section_image'] ?? ''; ?>
    <?php if ($current_appt_image && file_exists(__DIR__ . '/../../' . $current_appt_image)): ?>
        <img src="/interior-website/<?= htmlspecialchars($current_appt_image) ?>" class="img-thumbnail mt-2" width="300">
    <?php endif; ?>
    <input type="file" class="form-control" id="appointment_section_image" name="appointment_section_image">
    <div class="form-text">Tải lên ảnh mới để thay thế.</div>
</div>
                    <hr>
                <h5>Cài đặt Footer</h5>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Menu cột "Khám Phá"</label><select class="form-select" name="options[footer_explore_menu_id]"><option value="">-- Không chọn --</option><?php foreach($all_menus as $menu): ?><option value="<?= $menu['id'] ?>" <?= ($options['footer_explore_menu_id'] ?? '') == $menu['id'] ? 'selected' : '' ?>><?= htmlspecialchars($menu['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Menu cột "Kết Nối"</label><select class="form-select" name="options[footer_connect_menu_id]"><option value="">-- Không chọn --</option><?php foreach($all_menus as $menu): ?><option value="<?= $menu['id'] ?>" <?= ($options['footer_connect_menu_id'] ?? '') == $menu['id'] ? 'selected' : '' ?>><?= htmlspecialchars($menu['name']) ?></option><?php endforeach; ?></select></div>
                </div>
                <hr>
                <h5>Mạng xã hội</h5>
                <div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-facebook-f fa-fw"></i></span><input type="url" class="form-control" name="options[social_facebook]" value="<?= htmlspecialchars($options['social_facebook'] ?? '') ?>"></div>
                <div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-instagram fa-fw"></i></span><input type="url" class="form-control" name="options[social_instagram]" value="<?= htmlspecialchars($options['social_instagram'] ?? '') ?>"></div>
                <div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-youtube fa-fw"></i></span><input type="url" class="form-control" name="options[social_youtube]" value="<?= htmlspecialchars($options['social_youtube'] ?? '') ?>"></div>
                <div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-tiktok fa-fw"></i></span><input type="url" class="form-control" name="options[social_tiktok]" value="<?= htmlspecialchars($options['social_tiktok'] ?? '') ?>"></div>
            </div>
        </div>
        <div class="mt-4"><button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save me-2"></i>Lưu tất cả thay đổi</button></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Tùy biến Giao diện';
include '../layout.php';
?>