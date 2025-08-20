<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Trang quản trị' ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/interior-website/assets/css/admin.css?v=<?= time() ?>">

</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar" id="sidebar">
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </aside>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <main class="main-content">
            <button class="btn btn-light d-md-none m-3" id="toggleSidebar">
                <i class="fa fa-bars"></i>
            </button>
            <header class="topbar">
                <?php include __DIR__ . '/partials/topbar.php'; ?>
            </header>
            <div class="content-area">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/interior-website/assets/js/admin.js"></script>
</body>
</html>