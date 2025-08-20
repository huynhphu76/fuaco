<?php
// Lấy URI hiện tại để xác định trang đang hoạt động
$current_uri = $_SERVER['REQUEST_URI'];

// Hàm trợ giúp để kiểm tra xem một nhóm menu có đang active không
function is_nav_group_active($paths) {
    global $current_uri;
    foreach ($paths as $path) {
        if (str_contains($current_uri, $path)) {
            return true;
        }
    }
    return false;
}
?>
<div class="sidebar-inner">
    <div class="sidebar-logo">
        <a href="/interior-website/admin/index.php">
            <img src="https://www.fuaco.com.vn/upload/hinhanh/logo-viet-2364-3116.png" alt="Logo">
        </a>
    </div>
    
    <ul class="sidebar-nav">
        <li class="nav-title">TỔNG QUAN</li>
        <?php if (hasPermission('view-dashboard')): ?>
            <li class="nav-item"><a href="/interior-website/admin/index.php" class="nav-link <?= ($current_uri === '/interior-website/admin/' || $current_uri === '/interior-website/admin/index.php') ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie fa-fw"></i><span>Dashboard</span></a></li>
        <?php endif; ?>
        <?php if (hasPermission('view-orders')): ?>
            <li class="nav-item"><a href="/interior-website/admin/orders/index.php" class="nav-link <?= (str_contains($current_uri, '/orders/')) ? 'active' : '' ?>"><i class="fa-solid fa-receipt fa-fw"></i><span>Đơn hàng</span></a></li>
        <?php endif; ?>

        <li class="nav-title">NỘI DUNG</li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#productsCollapse"><i class="fa-solid fa-box-open fa-fw"></i><span>Sản phẩm</span><i class="fa-solid fa-chevron-down ms-auto"></i></a>
            <div class="collapse <?= is_nav_group_active(['/products/', '/categories/']) ? 'show' : '' ?>" id="productsCollapse">
                <ul class="nav-sub">
                    <li class="nav-sub-item"><a href="/interior-website/admin/products/index.php" class="nav-link <?= (str_contains($current_uri, '/products/')) ? 'active' : '' ?>">Tất cả sản phẩm</a></li>
                    <li class="nav-sub-item"><a href="/interior-website/admin/categories/index.php" class="nav-link <?= (str_contains($current_uri, '/categories/')) ? 'active' : '' ?>">Danh mục</a></li>
                    <li class="nav-sub-item"><a href="/interior-website/admin/product_reviews/index.php" class="nav-link <?= (str_contains($current_uri, '/product_reviews/')) ? 'active' : '' ?>">Đánh giá sản phẩm</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#blogsCollapse"><i class="fa-solid fa-newspaper fa-fw"></i><span>Bài viết</span><i class="fa-solid fa-chevron-down ms-auto"></i></a>
            <div class="collapse <?= is_nav_group_active(['/blogs/', '/blog_categories/', '/blog_comments/']) ? 'show' : '' ?>" id="blogsCollapse">
                <ul class="nav-sub">
                    <li class="nav-sub-item"><a href="/interior-website/admin/blogs/index.php" class="nav-link <?= (str_contains($current_uri, '/blogs/index')) ? 'active' : '' ?>">Tất cả bài viết</a></li>
                    <li class="nav-sub-item"><a href="/interior-website/admin/blog_categories/index.php" class="nav-link <?= (str_contains($current_uri, '/blog_categories/')) ? 'active' : '' ?>">Chuyên mục</a></li>
                    <li class="nav-sub-item"><a href="/interior-website/admin/blog_comments/index.php" class="nav-link <?= (str_contains($current_uri, '/blog_comments/')) ? 'active' : '' ?>">Bình luận</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item"><a href="/interior-website/admin/projects/index.php" class="nav-link <?= (str_contains($current_uri, '/projects/')) ? 'active' : '' ?>"><i class="fa-solid fa-building fa-fw"></i><span>Dự án</span></a></li>
        <li class="nav-item"><a href="/interior-website/admin/media/index.php" class="nav-link <?= (str_contains($current_uri, '/media/')) ? 'active' : '' ?>"><i class="fa-solid fa-photo-film fa-fw"></i><span>Thư viện Media</span></a></li>
         <?php if (hasPermission('manage-recruitment')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#recruitmentCollapse">
                <i class="fa-solid fa-briefcase fa-fw"></i><span>Tuyển dụng</span><i class="fa-solid fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= is_nav_group_active(['/jobs/', '/job_applications/']) ? 'show' : '' ?>" id="recruitmentCollapse">
                <ul class="nav-sub">
                    <li class="nav-sub-item"><a href="/interior-website/admin/jobs/index.php" class="nav-link <?= (str_contains($current_uri, '/jobs/')) ? 'active' : '' ?>">Vị trí tuyển dụng</a></li>
                    <li class="nav-sub-item"><a href="/interior-website/admin/job_applications/index.php" class="nav-link <?= (str_contains($current_uri, '/job_applications/')) ? 'active' : '' ?>">Hồ sơ ứng tuyển</a></li>
                </ul>
            </div>
        </li>
        <?php endif; ?>
        <li class="nav-title">GIAO DIỆN & TƯƠNG TÁC</li>
        <li class="nav-item"><a href="/interior-website/admin/menus/index.php" class="nav-link <?= (str_contains($current_uri, '/menus/')) ? 'active' : '' ?>"><i class="fa-solid fa-bars fa-fw"></i><span>Menus</span></a></li>
        <li class="nav-item"><a href="/interior-website/admin/sliders/index.php" class="nav-link <?= (str_contains($current_uri, '/sliders/')) ? 'active' : '' ?>"><i class="fa-solid fa-images fa-fw"></i><span>Sliders</span></a></li>
        <li class="nav-item"><a href="/interior-website/admin/pages/index.php" class="nav-link <?= (str_contains($current_uri, '/pages/')) ? 'active' : '' ?>"><i class="fa-solid fa-file-alt fa-fw"></i><span>Trang tĩnh</span></a></li>
        <li class="nav-item"><a href="/interior-website/admin/appointments/index.php" class="nav-link <?= (str_contains($current_uri, '/appointments/')) ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check fa-fw"></i><span>Lịch hẹn</span></a></li>
        <li class="nav-item"><a href="/interior-website/admin/feedbacks/index.php" class="nav-link <?= (str_contains($current_uri, '/feedbacks/')) ? 'active' : '' ?>"><i class="fa-solid fa-comment-dots fa-fw"></i><span>Phản hồi</span></a></li>
<?php if (hasPermission('manage-theme')): ?>
    <li class="nav-item">
        <a class="nav-link" href="/interior-website/admin/theme_options/index.php">
            <i class="fas fa-fw fa-paint-brush"></i>
            <span>Tùy biến Giao diện</span>
        </a>
    </li>
<?php endif; ?> 
        <?php if (hasPermission('view-users') || hasPermission('manage-roles') || hasPermission('manage-settings') || hasPermission('view-logs')): ?>
            <li class="nav-title">HỆ THỐNG</li>
            <li class="nav-item"><a href="/interior-website/admin/users/index.php" class="nav-link <?= (str_contains($current_uri, '/users/')) ? 'active' : '' ?>"><i class="fa-solid fa-users-cog fa-fw"></i><span>Người dùng</span></a></li>
            <li class="nav-item"><a href="/interior-website/admin/roles/index.php" class="nav-link <?= (str_contains($current_uri, '/roles/')) ? 'active' : '' ?>"><i class="fa-solid fa-user-tag fa-fw"></i><span>Vai trò & Quyền</span></a></li>
            <li class="nav-item"><a href="/interior-website/admin/settings/index.php" class="nav-link <?= (str_contains($current_uri, '/settings/')) ? 'active' : '' ?>"><i class="fa-solid fa-cog fa-fw"></i><span>Cài đặt</span></a></li>
            <li class="nav-item"><a href="/interior-website/admin/admin_logs/index.php" class="nav-link <?= (str_contains($current_uri, '/admin_logs/')) ? 'active' : '' ?>"><i class="fa-solid fa-history fa-fw"></i><span>Nhật ký</span></a></li>
        <?php endif; ?>

        <li class="nav-item-divider"></li>
        <li class="nav-item"><a href="/interior-website/admin/logout.php" class="nav-link logout"><i class="fa-solid fa-right-from-bracket fa-fw"></i><span>Đăng xuất</span></a></li>
    </ul>
</div>