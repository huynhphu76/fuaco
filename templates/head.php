<?php
// Tệp: /templates/head.php (Đã sửa lỗi Favicon)
global $pdo;

$favicon_file = $pdo->query("SELECT option_value FROM theme_options WHERE option_key = 'favicon'")->fetchColumn();
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FUACO - Kiến Tạo Không Gian Sống Đẳng Cấp</title>

<?php if($favicon_file): ?>
    <link rel="icon" href="<?= BASE_URL . htmlspecialchars($favicon_file) ?>">
    <?php endif; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Manrope:wght@300;400;500;700;800&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet"> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>


<style>
    :root {
        --font-heading: 'Playfair Display', serif; --font-body: 'Manrope', sans-serif;
        --color-primary: #1a1a1a; --color-secondary: #c5a47e; --color-text: #555;
        --color-light-bg: #f9f6f3; --color-white: #ffffff; --border-radius: 8px; --transition-speed: 0.4s;
    }
    html { scroll-behavior: smooth; }
    body { font-family: var(--font-body); color: var(--color-text); background-color: var(--color-white); overflow-x: hidden; line-height: 1.7; }
    h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); color: var(--color-primary); font-weight: 700; }
    .header-container { position: absolute; top: 0; left: 0; width: 100%; z-index: 1030; transition: all var(--transition-speed); }
    .header-container.scrolled { position: fixed; background-color: var(--color-white); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
    .top-bar { background-color: rgba(0,0,0,0.2); color: rgba(255, 255, 255, 0.8); font-size: 0.85rem; padding: 0.5rem 0; transition: all var(--transition-speed); }
    .header-container.scrolled .top-bar { background-color: var(--color-primary); }
    .top-bar a { color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: color var(--transition-speed); }
    .top-bar a:hover { color: var(--color-white); }
    .navbar { background-color: transparent !important; padding: 1rem 0; transition: all var(--transition-speed); }
    .header-container.scrolled .navbar { padding: 0.75rem 0; }
    .navbar-brand img { max-height: 50px; filter: brightness(0) invert(1); transition: all var(--transition-speed); }
    .header-container.scrolled .navbar-brand img { filter: none; max-height: 40px; }
  .navbar-nav .nav-link {
    color: var(--color-white);
    font-weight: 500;
    position: relative; 
    padding-bottom: 8px; 
    transition: color 0.3s ease-in-out;
    z-index: 1; /* THÊM DÒNG NÀY */
}
.header-container.scrolled .navbar-nav .nav-link {
    color: var(--color-primary);
}
.navbar-nav .nav-link:hover {
    color: var(--color-secondary) !important; /* Luôn đổi thành màu vàng khi hover */
}

/* --- HIỆU ỨNG GẠCH CHÂN "ĐỈNH CAO" --- */
.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0; /* Ban đầu gạch chân có chiều rộng bằng 0 */
    height: 2px;
    background-color: var(--color-secondary);
    transition: width 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); /* Hiệu ứng chạy mượt */
}
.navbar-nav .nav-link:hover::after {
    width: 100%; /* Mở rộng gạch chân ra 100% khi hover */
}
    #heroSlider { height: 100vh; }
    .carousel-item { height: 100vh; background-size: cover; background-position: center; }
    .carousel-item::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.1) 100%); }
    .carousel-caption { top: 50%; transform: translateY(-50%); left: 10%; right: auto; width: 50%; text-align: left; z-index: 10; }
    .carousel-caption h1 { font-size: clamp(3rem, 6vw, 5rem); color: var(--color-white); font-style: italic; }
    .brand-statement-section { padding: 10rem 0; background-size: cover; background-attachment: fixed; position: relative; color: var(--color-white); text-align: center; }
    .brand-statement-section .overlay { position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); }
    .brand-statement-section .content { position: relative; z-index: 2; }
    .brand-statement-section h2 { color: var(--color-white); font-size: 3rem; }
    .section-header { text-align: center; margin-bottom: 4rem; }
    .section-header h2 { font-size: clamp(2.2rem, 5vw, 3rem); }
    .section-header p { max-width: 600px; margin: 1rem auto 0; font-size: 1.1rem; font-weight: 300; }
    
    /* CARD STYLES */
    .product-card, .project-card, .blog-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: all var(--transition-speed) cubic-bezier(0.25, 0.8, 0.25, 1);
    text-decoration: none;
    display: block;
    color: var(--color-text);
}
.product-card { border: 1px solid #eee; background: var(--color-white); margin-bottom: 1.5rem; }
.project-card, .blog-card { box-shadow: 0 5px 25px rgba(0,0,0,0.05); }

.product-card:hover, .project-card:hover, .blog-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 18px 35px rgba(0, 0, 0, 0.1);
}
.card-image { position: relative; overflow: hidden; }
.card-image img { 
    transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1); /* Làm hiệu ứng chậm và mượt hơn */
    width: 100%; 
}
.product-card:hover .card-image img, .project-card:hover .card-image img, .blog-card:hover .card-image img {
    transform: scale(1.08);
}
    /* PROJECT CARD */
    .project-card { position: relative; color: var(--color-white); height: 100%; }
    .project-card .card-image { height: 100%; }
    .project-card .card-image img { height: 100%; object-fit: cover; }
    .project-card .card-body {
        position: absolute; bottom: 0; left: 0; right: 0;
        padding: 2rem;
        background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
    }
    .project-card .card-title { font-size: 1.75rem; color: var(--color-white); }
    .project-card .card-text { color: rgba(255,255,255,0.8); }

    /* BLOG CARD */
    .blog-card .card-body { padding: 1.5rem; }
    .blog-card .blog-meta { font-size: 0.85rem; color: #999; margin-bottom: 0.5rem; text-transform: uppercase; }
    .blog-card .card-title { font-family: var(--font-body); font-weight: 700; font-size: 1.15rem; color: var(--color-primary); }
    .blog-card .read-more { color: var(--color-secondary); font-weight: 700; text-decoration: none; }

    /* FOOTER STYLES */
    .footer { background-color: var(--color-primary); color: rgba(255, 255, 255, 0.7); padding: 5rem 0 2rem; }
    .footer .footer-logo { max-height: 40px; margin-bottom: 1rem; filter: brightness(0) invert(1); }
    .footer h5 { color: var(--color-white); font-weight: 700; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; margin-bottom: 1.5rem; }
    .footer a, .footer .contact-info span { color: rgba(255, 255, 255, 0.7); text-decoration: none; transition: color 0.3s ease; }
    .footer a:hover { color: var(--color-secondary); }
    .footer .contact-info i { color: var(--color-secondary); width: 25px; }
    .footer-bottom { border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 2rem; margin-top: 3rem; font-size: 0.9rem; }
    #appointment-section {
        background-color: var(--color-light-bg);
        padding: 5rem 0;
    }
    .appointment-form-container {
        background-color: var(--color-white);
        padding: 3rem;
        border-radius: var(--border-radius);
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    }
    .appointment-form-container h2 {
        font-size: 2.5rem;
    }
    .appointment-form-container .form-control {
        padding: 0.9rem 1rem;
        border-radius: var(--border-radius);
        border: 1px solid #ddd;
    }
    .appointment-form-container .form-control:focus {
        border-color: var(--color-secondary);
        box-shadow: 0 0 0 0.25rem rgba(197, 164, 126, 0.25);
    }
    .appointment-image {
        border-radius: var(--border-radius);
        object-fit: cover;
        width: 100%;
        height: 100%;
        min-height: 400px;
    }

    .appointment-alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .appointment-alert-success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .appointment-alert-error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }
     /* =================== CSS MỚI CHO THANH TRƯỢT NGANG =================== */
.testimonials-section {
    background-color: var(--color-light-bg);
    padding: 6rem 0;
}
/* Vùng chứa thanh trượt */
.testimonial-scroll-wrapper {
    position: relative;
}
/* Lớp nội dung có thể trượt ngang */
.testimonial-scroll-inner {
    display: flex;
    flex-wrap: nowrap; /* Ngăn các card xuống dòng */
    overflow-x: auto; /* Kích hoạt thanh trượt ngang */
    padding-bottom: 20px; /* Tạo khoảng trống cho thanh trượt */
    -webkit-overflow-scrolling: touch; /* Giúp trượt mượt trên mobile */
    scrollbar-width: thin; /* Cho Firefox */
    scrollbar-color: var(--color-secondary) #e0e0e0; /* Cho Firefox */
}
/* CSS cho thanh trượt */
/* =================== CSS HOÀN CHỈNH CHO MỤC ĐÁNH GIÁ =================== */
.testimonials-grid-section {
    background-color: var(--color-light-bg);
    padding: 6rem 0;
}
.testimonial-card-grid {
    background-color: var(--color-white);
    padding: 2.5rem;
    border-radius: var(--border-radius);
    box-shadow: 0 10px 40px rgba(0,0,0,0.07);
    height: 100%; 
    display: flex;
    flex-direction: column;
}
.testimonial-stars {
    color: #FFC107;
    margin-bottom: 1.5rem;
    font-size: 1rem;
}
.testimonial-grid-text {
    flex-grow: 1;
    font-style: italic;
    color: var(--color-text);
    font-size: 1.1rem;
    line-height: 1.8;
}
.testimonial-grid-author {
    font-weight: 700;
    color: var(--color-primary);
    margin-top: 2rem;
    text-align: right;
}
/* CSS cho các nút điều khiển của slider */
.testimonial-slider-controls {
    display: flex;
    justify-content: center;
    margin-top: 2.5rem;
}
.swiper-button-next, .swiper-button-prev {
    position: static !important;
    margin: 0 0.5rem !important;
    width: 45px !important;
    height: 45px !important;
    background-color: var(--color-white);
    border: 1px solid #ddd;
    border-radius: 50%;
    color: var(--color-primary) !important;
    transition: all 0.3s ease;
}
.swiper-button-next:hover, .swiper-button-prev:hover {
    background-color: var(--color-secondary);
    border-color: var(--color-secondary);
    color: var(--color-white) !important;
}
.swiper-button-next::after, .swiper-button-prev::after {
    font-size: 1rem !important;
    font-weight: bold;
}
/* [CODE THÊM VÀO] */
/* --- Search Overlay --- */
#search-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(26, 26, 26, 0.95);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease, visibility 0s 0.4s;
}
#search-overlay.active {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.4s ease;
}
.search-form-container {
    width: 90%;
    max-width: 700px;
}
.search-form-container form {
    position: relative;
}
.search-form-container input[type="search"] {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 2px solid rgba(255,255,255,0.5);
    color: var(--color-white);
    font-size: clamp(1.5rem, 5vw, 2.5rem);
    padding: 1rem 3.5rem 1rem 0;
    font-family: var(--font-heading);
    font-weight: 300;
}
.search-form-container input[type="search"]::placeholder {
    color: rgba(255,255,255,0.4);
}
.search-form-container input[type="search"]:focus {
    outline: none;
    border-bottom-color: var(--color-secondary);
}
.search-form-container button[type="submit"] {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: rgba(255,255,255,0.8);
    font-size: 1.5rem;
    cursor: pointer;
}
#search-close-btn {
    position: absolute;
    top: 40px; right: 40px;
    background: none;
    border: none;
    color: rgba(255,255,255,0.7);
    font-size: 2rem;
    cursor: pointer;
    transition: transform 0.3s ease;
}
#search-close-btn:hover {
    transform: rotate(90deg);
    color: var(--color-white);
}
.btn {
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    letter-spacing: 0.5px;
}
.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.btn-primary {
    background-color: var(--color-secondary);
    color: var(--color-primary);
    border-color: var(--color-secondary);
}
.btn-primary:hover {
    background-color: transparent;
    color: var(--color-secondary);
}
.btn-outline-dark {
    border-color: #ddd;
    color: var(--color-text);
}
.btn-outline-dark:hover {
    background-color: var(--color-primary);
    color: var(--color-white);
    border-color: var(--color-primary);
}
/* --- CSS CHO PHẦN ĐÁNH GIÁ --- */
.product-rating-summary { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.star-rating { color: #ffc107; }
.reviews-link { color: var(--color-text); text-decoration: none; font-size: 0.9em; }
.reviews-link:hover { text-decoration: underline; }
.review-list { list-style: none; padding: 0; }
.review-item { border-bottom: 1px solid #eee; padding: 1.5rem 0; }
.review-item:last-child { border-bottom: none; }
.review-author { font-weight: 600; color: var(--color-primary); }
.review-date { font-size: 0.85rem; color: #999; margin-left: 0.5rem; }
.review-content { margin-top: 0.75rem; }
.review-form { background-color: #f8f9fa; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; }
.star-rating-input { direction: rtl; display: inline-block; }
.star-rating-input input[type=radio] { display: none; }
.star-rating-input label { color: #ccc; cursor: pointer; font-size: 1.8rem; padding: 0 0.1rem; transition: color 0.2s; }
.star-rating-input input[type=radio]:checked ~ label, .star-rating-input label:hover, .star-rating-input label:hover ~ label { color: #ffc107; }
/* --- TOÀN BỘ CSS CHO PHẦN ĐÁNH GIÁ SẢN PHẨM --- */

/* Tóm tắt sao ở đầu trang */
.product-rating-summary { 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
    margin-bottom: 1rem; 
}
.star-rating { 
    color: #ffc107; 
}
.reviews-link { 
    color: var(--color-text); 
    text-decoration: none; 
    font-size: 0.9em; 
}
.reviews-link:hover { 
    text-decoration: underline; 
}

/* Danh sách các bình luận */
.review-list { 
    list-style: none; 
    padding: 0; 
}
.review-item { 
    border-bottom: 1px solid #eee; 
    padding: 1.5rem 0; 
}
.review-item:last-child { 
    border-bottom: none; 
}
.review-author { 
    font-weight: 600; 
    color: var(--color-primary); 
}
.review-date { 
    font-size: 0.85rem; 
    color: #999; 
    margin-left: 0.5rem; 
}
.review-content { 
    margin-top: 0.75rem; 
}

/* Form gửi đánh giá */
.review-form {
    background-color: #f8f9fa;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    margin-top: 2rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
.review-form h4 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-align: center;
}
.review-form p {
    font-size: 0.95rem;
    color: #6c757d;
    margin-bottom: 2rem;
    text-align: center;
}
.review-form .form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: var(--color-primary);
}
.review-form .mb-3:first-of-type {
    text-align: center;
}
.star-rating-input { 
    direction: rtl; 
    display: inline-block; 
}
.star-rating-input input[type=radio] { 
    display: none; 
}
.star-rating-input label { 
    color: #ccc; 
    cursor: pointer; 
    font-size: 1.8rem; 
    padding: 0 0.1rem; 
    transition: color 0.2s; 
}
.star-rating-input input[type=radio]:checked ~ label, 
.star-rating-input label:hover, 
.star-rating-input label:hover ~ label { 
    color: #ffc107; 
}
.review-form input.form-control,
.review-form textarea.form-control {
    width: 100%;
    padding: 0.85rem 1.1rem;
    font-size: 1rem;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
.review-form .form-control:focus {
    border-color: var(--color-secondary);
    box-shadow: 0 0 0 .25rem rgba(197, 164, 126, 0.25);
    outline: none;
}
.review-form .btn-dark {
    width: 100%;
    background-color: var(--color-primary);
    color: var(--color-white);
    border: none;
    padding: 0.85rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.review-form .btn-dark:hover {
    background-color: #000;
}
/* --- CSS HOÀN CHỈNH CHO TRANG LIÊN HỆ --- */

/* Tiêu đề các mục */
.contact-page-section h3 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 2rem;
    position: relative;
    padding-bottom: 0.75rem;
}
.contact-page-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background-color: var(--color-secondary);
}

/* Khung thông tin bên trái */
.contact-info-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.contact-info-item i {
    font-size: 1.5rem;
    color: var(--color-secondary);
    margin-top: 5px;
    width: 25px;
    text-align: center;
}
.contact-info-item strong {
    display: block;
    color: var(--color-primary);
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
}
.contact-info-item p {
    margin-bottom: 0;
    color: var(--color-text);
}
.contact-info-item a {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.3s;
}
.contact-info-item a:hover {
    color: var(--color-secondary);
}

/* Form liên hệ bên phải */
.contact-form-wrapper {
    background-color: #f8f9fa;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    border: 1px solid #dee2e6;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}
.contact-form-wrapper .form-control {
    padding: 0.85rem 1.1rem;
    border-radius: 4px;
    border: 1px solid #ced4da;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
.contact-form-wrapper .form-control:focus {
     border-color: var(--color-secondary);
     box-shadow: 0 0 0 .25rem rgba(197,164,126,.25);
     outline: none;
}
.contact-form-wrapper .btn-dark {
     width: 100%;
     padding: 0.85rem 1.5rem;
     font-size: 1rem;
     font-weight: 600;
     text-transform: uppercase;
     transition: background-color 0.3s ease;
}

/* Bản đồ */
.map-container {
    width: 100%;
    height: 450px;
    margin-top: 4rem;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.map-container iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
/* --- CSS CHO CÁC TRANG NỘI DUNG TĨNH --- */
.content-section {
    line-height: 1.8;
    font-size: 1.1rem;
}
.content-section h2, .content-section h3 {
    font-weight: 600;
    margin-top: 2.5rem;
    margin-bottom: 1.5rem;
}
.content-section img {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius);
    margin: 2rem 0;
}
/* --- CSS CHO PHẦN BLOG --- */
.blog-card-list {
    background-color: #fff;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.07);
    transition: all 0.4s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.blog-card-list:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
.blog-card-image img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}
.blog-card-body {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.blog-card-meta {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
}
.blog-card-title {
    font-family: var(--font-body);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    flex-grow: 1;
}
.blog-card-title a {
    color: var(--color-primary);
    text-decoration: none;
    transition: color 0.3s;
}
.blog-card-title a:hover {
    color: var(--color-secondary);
}
.blog-card-excerpt {
    color: var(--color-text);
    font-size: 0.95rem;
    line-height: 1.6;
}
.blog-post-detail {
    background-color: #fff;
    padding: 3rem;
    border-radius: var(--border-radius);
}
.blog-post-header { text-align: center; margin-bottom: 2rem; }
.blog-post-category { display: inline-block; background: var(--color-secondary); color: #fff; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; text-decoration: none; text-transform: uppercase; margin-bottom: 1rem; }
.blog-post-title { font-size: 2.8rem; }
.blog-post-meta { color: #888; display: flex; justify-content: center; gap: 1.5rem; font-size: 0.9rem; margin-top: 1rem; }
.blog-post-thumbnail { width: 100%; height: auto; border-radius: var(--border-radius); margin-bottom: 2.5rem; }
.blog-post-content { line-height: 1.8; font-size: 1.1rem; }
.blog-post-content img { max-width: 100%; height: auto; border-radius: var(--border-radius); }
/* --- CSS CHO PHẦN BÌNH LUẬN BLOG --- */
.comment-section h3 { font-weight: 600; }
.comment-list { list-style: none; padding: 0; }
.comment-item { margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #eee; }
.comment-item:last-child { border-bottom: none; }
.comment-author { font-weight: 600; font-size: 1.1rem; }
.comment-date { font-size: 0.85rem; color: #999; margin-top: 0.25rem; }
.comment-content { margin-top: 1rem; }
.comment-form-wrapper { background-color: #f8f9fa; padding: 2.5rem; border-radius: var(--border-radius); }
.comment-form-wrapper h4 { font-weight: 600; }
/* --- CSS CHO PHẦN DỰ ÁN --- */
.project-list-card {
    position: relative;
    display: block;
    overflow: hidden;
    border-radius: var(--border-radius);
    box-shadow: 0 5px 25px rgba(0,0,0,0.07);
    height: 350px;
}
.project-card-image {
    width: 100%;
    height: 100%;
}
.project-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.project-list-card:hover .project-card-image img {
    transform: scale(1.1);
}
.project-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 3rem 1.5rem 1.5rem;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
    color: #fff;
}
.project-card-title {
    font-size: 1.5rem;
    color: #fff;
    font-weight: 600;
}
.project-detail-header {
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}
.project-detail-header h1 {
    font-size: 3rem;
}
.project-gallery img {
    transition: opacity 0.3s ease;
}
.project-gallery a:hover img {
    opacity: 0.8;
}
/* --- CSS HOÀN CHỈNH CHO NÚT LIÊN HỆ NỔI --- */
.floating-contact-buttons {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: flex-end;
}
.contact-button {
    display: flex;
    align-items: center;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    text-decoration: none;
    color: #fff;
    transition: all 0.3s ease;
    overflow: hidden;
}
.contact-button:hover {
    width: 170px;
    border-radius: 30px;
    padding: 0 15px 0 8px; /* Thêm padding khi mở rộng */
}
.contact-button i {
    /* Quan trọng: Giữ lại các thuộc tính font của FontAwesome */
    font-size: 24px; /* Tăng kích thước icon */
    line-height: 55px; /* Căn giữa icon theo chiều dọc */
    text-align: center;
    width: 55px; /* Chiếm toàn bộ chiều rộng ban đầu */
    flex-shrink: 0;
}
.contact-button span {
    white-space: nowrap;
    font-weight: 600;
    font-size: 1rem;
    margin-left: 10px;
    opacity: 0;
    transition: opacity 0.2s ease;
    max-width: 0; /* Ẩn chữ ban đầu */
}
.contact-button:hover span {
    opacity: 1; /* Hiện chữ khi hover */
    max-width: 100px; /* Cho phép chữ chiếm không gian */
}

/* Màu nền cho từng nút */
.cb-facebook { background-color: #1877F2; }
.cb-instagram { background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); }
.cb-youtube { background-color: #FF0000; }
.cb-tiktok { background-color: #000; }
/* --- CSS CHO NÚT CHUYỂN NGÔN NGỮ BẰNG CỜ --- */
.lang-switcher {
    display: inline-flex;
    align-items: center;
    gap: 12px; /* Khoảng cách giữa 2 lá cờ */
    vertical-align: middle;
}
.lang-switcher-link {
    display: block;
    transition: transform 0.3s ease, opacity 0.3s ease;
    opacity: 0.6; /* Làm mờ lá cờ không được chọn */
    line-height: 0; /* Xóa khoảng trống thừa quanh SVG */
}
.lang-switcher-link:hover,
.lang-switcher-link.active {
    transform: scale(1.15);
    opacity: 1; /* Hiển thị rõ lá cờ được chọn hoặc khi hover */
}
.lang-switcher-flag {
    width: 28px;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
/* --- CSS CHO TRANG TUYỂN DỤNG --- */
.job-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
    text-decoration: none;
    color: var(--color-text);
    transition: all 0.3s ease;
}
.job-item:hover {
    border-color: var(--color-secondary);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transform: translateY(-3px);
}
.job-item-title {
    color: var(--color-primary);
    margin-bottom: 0.5rem;
}
.job-item-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.9rem;
    color: #777;
}
.application-form-wrapper {
    background-color: var(--color-light-bg);
    padding: 2.5rem;
    border-radius: var(--border-radius);
    position: sticky;
    top: 120px; /* Giữ form cố định khi cuộn */
}
/* --- CSS CHO THÔNG TIN CHI TIẾT TUYỂN DỤNG --- */
.job-meta-details {
    padding: 1.5rem;
    background-color: var(--color-light-bg);
    border-radius: var(--border-radius);
    border: 1px solid #eee;
}
.job-meta-details .meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
}
.job-meta-details .meta-item i {
    color: var(--color-secondary);
}
/* --- CSS CHO HIỆU ỨNG TRÊN ẢNH SẢN PHẨM --- */
.product-image-container {
    position: relative;
    overflow: hidden;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.product-actions-overlay {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transform: translate(-50%, 10px);
    transition: all 0.3s ease-in-out;
    z-index: 2;
}

/* KHI RÊ CHUỘT VÀO VÙNG CHỨA ẢNH */
.product-image-container:hover .product-actions-overlay {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, 0);
}

.product-image-container::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.5), rgba(0,0,0,0));
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    
    /* === BẮT ĐẦU SỬA LỖI TẠI ĐÂY === */
    pointer-events: none; /* Cho phép click xuyên qua lớp nền mờ */
}
.product-image-container:hover::after {
    opacity: 1;
}

.action-btn {
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    
    /* === THÊM DÒNG NÀY ĐỂ NÚT BẤM CÓ THỂ CLICK ĐƯỢC === */
    pointer-events: auto;
}

</style>