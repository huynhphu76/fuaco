<?php
// Tệp: /pages/home.php (Phiên bản Hoàn Hảo - Cập nhật lần cuối)
global $pdo, $language_code;

// 1. Lấy dữ liệu cho Slider
$sliders_stmt = $pdo->prepare("SELECT s.image_url, s.button_link, st.title, st.subtitle, st.button_text FROM sliders s JOIN slider_translations st ON s.id = st.slider_id WHERE s.is_active = 1 AND st.language_code = ? ORDER BY s.display_order ASC");
$sliders_stmt->execute([$language_code]);
$sliders = $sliders_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Lấy 4 sản phẩm mới nhất
$products_stmt = $pdo->prepare("SELECT p.price, p.main_image, pt.name, pt.slug FROM products p JOIN product_translations pt ON p.id = pt.product_id WHERE pt.language_code = ? AND p.status = 'active' ORDER BY p.created_at DESC LIMIT 4");
$products_stmt->execute([$language_code]);
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Lấy 3 dự án mới nhất
$projects_stmt = $pdo->prepare("SELECT p.thumbnail, pt.title, pt.slug FROM projects p JOIN project_translations pt ON p.id = pt.project_id WHERE pt.language_code = ? ORDER BY p.created_at DESC LIMIT 3");
$projects_stmt->execute([$language_code]);
$projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Lấy 3 bài viết mới nhất
$blogs_stmt = $pdo->prepare("
    SELECT b.thumbnail, b.created_at, bt.title, bt.slug, bct.name as category_name
    FROM blogs b
    JOIN blog_translations bt ON b.id = bt.blog_id
    LEFT JOIN blog_categories bc ON b.category_id = bc.id
    LEFT JOIN blog_category_translations bct ON bc.id = bct.blog_category_id AND bct.language_code = ?
    WHERE bt.language_code = ? AND b.status = 'published'
    ORDER BY b.created_at DESC LIMIT 3
");
$blogs_stmt->execute([$language_code, $language_code]);
$blog_posts = $blogs_stmt->fetchAll(PDO::FETCH_ASSOC);

$testimonials_stmt = $pdo->query("SELECT name, message, rating FROM feedbacks ORDER BY created_at DESC");
$testimonials = $testimonials_stmt->fetchAll(PDO::FETCH_ASSOC);
// 5. Lấy dữ liệu động cho các tiêu đề mục từ theme_options
$theme_options_trans_stmt = $pdo->prepare("SELECT option_key, option_value FROM theme_option_translations WHERE language_code = ?");
$theme_options_trans_stmt->execute([$language_code]);
$trans_options = $theme_options_trans_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$testimonial_section_title = $trans_options['testimonial_section_title'] ?? 'Khách Hàng Nói Về Chúng Tôi';
$testimonial_section_subtitle = $trans_options['testimonial_section_subtitle'] ?? 'Niềm tin và sự hài lòng của khách hàng là thước đo thành công lớn nhất của FUACO.';

// Lấy dữ liệu cho banner và các tiêu đề mục, có giá trị mặc định
$banner_image_file = $pdo->query("SELECT option_value FROM theme_options WHERE option_key = 'banner_image'")->fetchColumn();
$brand_statement_title = $trans_options['brand_statement_title'] ?? '"Chúng tôi không chỉ tạo ra nội thất,<br>chúng tôi kiến tạo phong cách sống."';
$fp_title = $trans_options['featured_products_title'] ?? 'Sản Phẩm Tinh Hoa';
$fp_subtitle = $trans_options['featured_products_subtitle'] ?? 'Mỗi sản phẩm là một tuyên ngôn về phong cách, được chế tác tỉ mỉ để đáp ứng những tiêu chuẩn khắt khe nhất.';
$project_section_title = $trans_options['project_section_title'] ?? 'Dự Án Tiêu Biểu';
$project_section_subtitle = $trans_options['project_section_subtitle'] ?? 'Cùng chiêm ngưỡng những không gian đã được chúng tôi thổi hồn, nơi mỗi chi tiết đều kể một câu chuyện riêng.';
$blog_section_title = $trans_options['blog_section_title'] ?? 'Góc Cảm Hứng';
$blog_section_subtitle = $trans_options['blog_section_subtitle'] ?? 'Khám phá những xu hướng mới nhất, mẹo trang trí hữu ích và câu chuyện đằng sau các thiết kế của chúng tôi.';

$appt_title = $trans_options['appointment_section_title'] ?? 'Đặt Lịch Hẹn Tư Vấn';
$appt_subtitle = $trans_options['appointment_section_subtitle'] ?? 'Để lại thông tin của bạn, đội ngũ chuyên gia của FUACO sẽ liên hệ để tư vấn giải pháp nội thất phù hợp nhất.';
$appt_button_text = $trans_options['appointment_form_button_text'] ?? 'Gửi Lịch Hẹn';
$appt_success_msg = $trans_options['appointment_success_message'] ?? '<strong>Cảm ơn bạn!</strong> Lịch hẹn của bạn đã được gửi thành công.';
$appt_error_msg = $trans_options['appointment_error_message'] ?? '<strong>Đã có lỗi xảy ra.</strong> Vui lòng thử lại.';
$label_name = $trans_options['appointment_form_name_label'] ?? 'Họ và Tên*';
$label_phone = $trans_options['appointment_form_phone_label'] ?? 'Số Điện Thoại*';
$label_email = $trans_options['appointment_form_email_label'] ?? 'Email';
$label_date = $trans_options['appointment_form_date_label'] ?? 'Ngày hẹn*';
$label_time = $trans_options['appointment_form_time_label'] ?? 'Giờ hẹn*';
$label_note = $trans_options['appointment_form_note_label'] ?? 'Ghi chú';
$view_all_projects_button = $trans_options['view_all_projects_button'] ?? 'Xem tất cả dự án';
$view_all_blog_button = $trans_options['view_all_blog_button'] ?? 'Xem tất cả bài viết';
$appointment_image_file = $pdo->query("SELECT option_value FROM theme_options WHERE option_key = 'appointment_section_image'")->fetchColumn();
$view_all_products_button = $trans_options['view_all_products_button'] ?? 'Xem tất cả sản phẩm';
?>

<div id="heroSlider" class="carousel slide" <?php if (count($sliders) > 1): ?> data-bs-ride="carousel" data-bs-interval="2000" data-bs-pause="false" <?php endif; ?>>
 
    <div class="carousel-inner">
        <?php if(empty($sliders)): ?>
            <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1616046229478-9901c5536a45?q=80&w=1920&auto-format&fit=crop')"><div class="carousel-caption"><h1>Chào mừng đến với FUACO</h1></div></div>
        <?php else: ?>
            <?php foreach($sliders as $index => $slider): ?>
            <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>" style="background-image: url('<?= BASE_URL ?>uploads/sliders/<?= htmlspecialchars($slider['image_url']) ?>')">
                <div class="carousel-caption">
                    <h1><?= htmlspecialchars($slider['title']) ?></h1>
                    <p><?= htmlspecialchars($slider['subtitle']) ?></p>
                    <?php if(!empty($slider['button_text'])): ?><a href="<?= htmlspecialchars($slider['button_link']) ?>" class="btn btn-primary"><?= htmlspecialchars($slider['button_text']) ?></a><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<section id="featured-products" class="py-5 my-5">
    <div class="container">
        <div class="section-header">
            <h2><?= htmlspecialchars($fp_title) ?></h2>
            <p><?= htmlspecialchars($fp_subtitle) ?></p>
        </div>
        <div class="row">
            <?php foreach($products as $product): ?>
            <div class="col-lg-3 col-md-6">
                <div class="product-card">
                    <div class="card-image"><a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($product['slug'] ?? '') ?>"><img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>" class="img-fluid" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 350px; object-fit: cover;"></a></div>
                    <div class="p-4 text-center">
                        <a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($product['slug'] ?? '') ?>" class="text-decoration-none d-block mb-2" style="color: var(--color-primary); font-weight: 500;"><?= htmlspecialchars($product['name']) ?></a>
                        <p style="font-size: 1.25rem; font-weight: 700; color: var(--color-secondary);"><?= number_format($product['price']) ?>₫</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>san-pham" class="btn btn-outline-dark"><?= htmlspecialchars($view_all_products_button) ?></a>
        </div>
    </div>
</section>

<section id="featured-projects" class="py-5 my-5 bg-light-bg">
    <div class="container">
        <div class="section-header">
            <h2><?= htmlspecialchars($project_section_title) ?></h2>
            <p><?= htmlspecialchars($project_section_subtitle) ?></p>
        </div>
        <div class="row g-4" style="min-height: 600px;">
            <?php if(count($projects) > 0): ?>
            <div class="col-lg-7">
                <a href="<?= BASE_URL ?>du-an/<?= htmlspecialchars($projects[0]['slug'] ?? '') ?>" class="project-card">
                    <div class="card-image"><img src="<?= BASE_URL ?>uploads/projects/<?= htmlspecialchars($projects[0]['thumbnail']) ?>" alt="<?= htmlspecialchars($projects[0]['title']) ?>"></div>
                    <div class="card-body"><h4 class="card-title"><?= htmlspecialchars($projects[0]['title']) ?></h4></div>
                </a>
            </div>
            <?php endif; ?>
            <?php if(count($projects) > 1): ?>
            <div class="col-lg-5 d-flex flex-column gap-4">
                <?php for($i = 1; $i < min(3, count($projects)); $i++): ?>
                <a href="<?= BASE_URL ?>du-an/<?= htmlspecialchars($projects[$i]['slug'] ?? '') ?>" class="project-card flex-grow-1">
                    <div class="card-image"><img src="<?= BASE_URL ?>uploads/projects/<?= htmlspecialchars($projects[$i]['thumbnail']) ?>" alt="<?= htmlspecialchars($projects[$i]['title']) ?>"></div>
                    <div class="card-body"><h4 class="card-title"><?= htmlspecialchars($projects[$i]['title']) ?></h4></div>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
         <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>du-an" class="btn btn-outline-dark"><?= htmlspecialchars($view_all_projects_button) ?></a>
        </div>
    </div>
</section>
<?php
// Tệp: /pages/home.php (Cập nhật sao động)
global $pdo, $language_code;

// ... (các câu lệnh PHP để lấy dữ liệu slider, product, project, blog giữ nguyên) ...

// CẬP NHẬT TRUY VẤN ĐỂ LẤY THÊM RATING


// ... (phần lấy dữ liệu từ theme_options giữ nguyên) ...
?>

<?php if (!empty($testimonials)): ?>
<section id="testimonials" class="testimonials-grid-section">
    <div class="container">
        <div class="section-header">
            <h2><?= htmlspecialchars($testimonial_section_title) ?></h2>
            <p><?= htmlspecialchars($testimonial_section_subtitle) ?></p>
        </div>

        <?php if (count($testimonials) > 3): // Nếu có nhiều hơn 3, dùng SLIDER ?>
            
            <div class="swiper testimonial-swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach($testimonials as $testimonial): ?>
                        <div class="swiper-slide">
                            <div class="testimonial-card-grid">
                                <div class="testimonial-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><i class="<?= $i <= $testimonial['rating'] ? 'fas' : 'far' ?> fa-star"></i><?php endfor; ?>
                                </div>
                                <p class="testimonial-grid-text">"<?= htmlspecialchars($testimonial['message']) ?>"</p>
                                <p class="testimonial-grid-author mb-0">— <?= htmlspecialchars($testimonial['name']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="testimonial-slider-controls">
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
                </div>

        <?php else: // Nếu có 3 hoặc ít hơn, dùng LƯỚI TĨNH ?>

            <div class="row">
                <?php 
                foreach($testimonials as $testimonial): 
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card-grid">
                        <div class="testimonial-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?><i class="<?= $i <= $testimonial['rating'] ? 'fas' : 'far' ?> fa-star"></i><?php endfor; ?>
                        </div>
                        <p class="testimonial-grid-text">"<?= htmlspecialchars($testimonial['message']) ?>"</p>
                        <p class="testimonial-grid-author mb-0">— <?= htmlspecialchars($testimonial['name']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if($banner_image_file): ?>
<section class="brand-statement-section" style="background-image: url('<?= BASE_URL . htmlspecialchars($banner_image_file) ?>');">
    <div class="overlay"></div>
    <div class="container content">
        <h2><?= $brand_statement_title ?></h2>
    </div>
</section>
<?php endif; ?>

<section id="from-the-blog" class="py-5 my-5">
    <div class="container">
         <div class="section-header">
            <h2><?= htmlspecialchars($blog_section_title) ?></h2>
            <p><?= htmlspecialchars($blog_section_subtitle) ?></p>
        </div>
        <div class="row">
            <?php foreach($blog_posts as $post): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="<?= BASE_URL ?>bai-viet/<?= htmlspecialchars($post['slug'] ?? '') ?>" class="blog-card">
                    <div class="card-image"><img src="<?= BASE_URL ?>uploads/blogs/<?= htmlspecialchars($post['thumbnail']) ?>" class="img-fluid" style="height: 250px; object-fit: cover;"></div>
                    <div class="card-body">
                        <p class="blog-meta"><?= htmlspecialchars($post['category_name'] ?? 'Tin tức') ?> / <?= date('d F, Y', strtotime($post['created_at'])) ?></p>
                        <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
         <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>bai-viet" class="btn btn-outline-dark"><?= htmlspecialchars($view_all_blog_button) ?></a>
        </div>
    </div>
    
 <section id="appointment-section">
    <div class="container">
        <?php if (isset($_GET['appointment'])): ?>
            <div class="appointment-alert <?= $_GET['appointment'] == 'success' ? 'appointment-alert-success' : 'appointment-alert-error' ?>">
                <?= $_GET['appointment'] == 'success' ? $appt_success_msg : $appt_error_msg ?>
            </div>
        <?php endif; ?>
        
        <div class="row align-items-center g-5">
            <div class="col-lg-6 d-none d-lg-block">
                <?php if ($appointment_image_file && file_exists(__DIR__ . '/../' . $appointment_image_file)): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($appointment_image_file) ?>" 
                         class="appointment-image" 
                         alt="Tư vấn nội thất">
                <?php else: // Ảnh mặc định nếu chưa có ảnh nào được tải lên ?>
                    <img src="https://images.unsplash.com/photo-1572021335469-31706a17aaef?q=80&w=1470&auto=format&fit=crop" 
                         class="appointment-image" 
                         alt="Tư vấn nội thất">
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <div class="appointment-form-container">
                    <h2 class="mb-3"><?= htmlspecialchars($appt_title) ?></h2>
                    <p class="text-muted mb-4"><?= htmlspecialchars($appt_subtitle) ?></p>

                    <form action="<?= BASE_URL ?>pages/appointment_handler.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= htmlspecialchars($label_name) ?></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label"><?= htmlspecialchars($label_phone) ?></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label"><?= htmlspecialchars($label_email) ?></label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label"><?= htmlspecialchars($label_date) ?></label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time" class="form-label"><?= htmlspecialchars($label_time) ?></label>
                                <input type="time" class="form-control" id="time" name="time" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="note" class="form-label"><?= htmlspecialchars($label_note) ?></label>
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"><?= htmlspecialchars($appt_button_text) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</section>