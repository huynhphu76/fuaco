<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                
                <span class="me-2 d-none d-lg-inline text-gray-600 small">
                    <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?>
                </span>
                
                <?php // Hiển thị avatar hoặc icon mặc định ?>
                <img class="img-profile rounded-circle" 
                     src="/interior-website/uploads/avatars/<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'default.png') ?>"
                     onerror="this.onerror=null;this.src='/interior-website/assets/images/default-avatar.png';"
                     style="width: 32px; height: 32px; object-fit: cover;">
            </a>
            
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="/interior-website/admin/profile.php">
                    <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                    Hồ sơ
                </a>
                
                <?php if (hasPermission('manage-settings')): ?>
                <a class="dropdown-item" href="/interior-website/admin/settings/index.php">
                    <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                    Cài đặt
                </a>
                <?php endif; ?>

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/interior-website/admin/logout.php">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                    Đăng xuất
                </a>
            </div>
        </li>
    </ul>
</nav>
