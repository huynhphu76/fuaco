<?php
// Tệp: /templates/layout.php (Phiên bản cuối cùng, đã sửa lỗi JS)
global $pdo, $page_file, $language_code;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language_code) ?>">
<head>
    <?php include __DIR__ . '/head.php'; ?>
</head>
<body>

    <header class="header-container">
        <?php include __DIR__ . '/header.php'; ?>
    </header>

    <main>
        <?php 
            if (isset($page_file) && file_exists($page_file)) {
                include $page_file;
            } else {
                echo "<div class='container my-5'><p>Lỗi: Không tìm thấy nội dung trang.</p></div>";
            }
        ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
    <?php include __DIR__ . '/compare_bar.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    
    <script src="<?= BASE_URL ?>assets/js/script.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // Khởi tạo AOS cho hiệu ứng cuộn
            AOS.init({ duration: 800, once: true });

            // Script cho header cuộn
            const header = document.querySelector('.header-container');
            if (header) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                });
            }

            // Khởi tạo Swiper.js cho Testimonials (nếu có)
            if (document.querySelector('.testimonial-swiper-container')) {
                const testimonialSwiper = new Swiper('.testimonial-swiper-container', {
                    loop: true,
                    slidesPerView: 1,
                    spaceBetween: 30,
                    autoplay: { delay: 5000, disableOnInteraction: false },
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                    breakpoints: { 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
                });
            }
            
            // Script cho lớp phủ tìm kiếm
            const searchToggleBtn = document.getElementById('search-toggle-btn');
            const searchOverlay = document.getElementById('search-overlay');
            const searchCloseBtn = document.getElementById('search-close-btn');
            if (searchToggleBtn && searchOverlay && searchCloseBtn) {
                const searchInput = searchOverlay.querySelector('input[type="search"]');
                searchToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchOverlay.classList.add('active');
                    searchInput.focus();
                });
                searchCloseBtn.addEventListener('click', () => searchOverlay.classList.remove('active'));
                searchOverlay.addEventListener('click', e => { if (e.target === searchOverlay) searchOverlay.classList.remove('active'); });
                document.addEventListener('keydown', e => { if (e.key === 'Escape') searchOverlay.classList.remove('active'); });
            }
        });
    </script>
    </body>
</html>