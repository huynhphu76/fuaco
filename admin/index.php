<?php
// Tệp: admin/index.php (HOÀN CHỈNH - PHIÊN BẢN TUYỆT PHẨM)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/permission_check.php';

if (!hasPermission('view-dashboard')) {
    die('Bạn không có quyền truy cập Bảng điều khiển.');
}

try {
    $language_code = 'vi';
    
    // 1. Dữ liệu cho các thẻ KPI
    $total_projects = $pdo->query("SELECT COUNT(id) FROM projects")->fetchColumn();
    $total_blogs = $pdo->query("SELECT COUNT(id) FROM blogs")->fetchColumn();
    $revenue_this_month = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'delivered' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
    $new_orders_today = $pdo->query("SELECT COUNT(id) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    // 2. Dữ liệu cho các danh sách hoạt động
    $recent_orders = $pdo->query("SELECT id, customer_name, total_price, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    $recent_blogs_stmt = $pdo->prepare("SELECT b.id, bt.title, b.created_at FROM blogs b JOIN blog_translations bt ON b.id = bt.blog_id WHERE bt.language_code = ? ORDER BY b.created_at DESC LIMIT 5");
    $recent_blogs_stmt->execute([$language_code]);
    $recent_blogs = $recent_blogs_stmt->fetchAll(PDO::FETCH_ASSOC);

    $recent_projects_stmt = $pdo->prepare("SELECT p.id, pt.title, p.created_at FROM projects p JOIN project_translations pt ON p.id = pt.project_id WHERE pt.language_code = ? ORDER BY p.created_at DESC LIMIT 5");
    $recent_projects_stmt->execute([$language_code]);
    $recent_projects = $recent_projects_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Dữ liệu cho BIỂU ĐỒ
    $revenue_chart_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE status = 'delivered' AND DATE(created_at) = ?");
        $stmt->execute([$date]);
        $revenue_chart_data[date('d/m', strtotime($date))] = $stmt->fetchColumn() ?? 0;
    }
    $chart_labels_revenue = json_encode(array_keys($revenue_chart_data));
    $chart_values_revenue = json_encode(array_values($revenue_chart_data));

    $order_status_stats = $pdo->query("SELECT status, COUNT(id) as count FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    $chart_labels_status = json_encode(array_keys($order_status_stats));
    $chart_values_status = json_encode(array_values($order_status_stats));

} catch (PDOException $e) {
    $error_message = "Không thể tải dữ liệu thống kê. Lỗi: " . $e->getMessage();
}

// Các hàm trợ giúp
function getStatusBadgeClass($status) {
    $map = ['delivered' => 'bg-success-soft text-success', 'shipped' => 'bg-info-soft text-info', 'confirmed' => 'bg-primary-soft text-primary', 'cancelled' => 'bg-danger-soft text-danger', 'pending' => 'bg-warning-soft text-warning'];
    return $map[$status] ?? 'bg-secondary-soft text-secondary';
}
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array('y' => 'năm', 'm' => 'tháng', 'w' => 'tuần', 'd' => 'ngày', 'h' => 'giờ', 'i' => 'phút', 's' => 'giây');
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' trước' : 'vừa xong';
}
ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    :root {
        --primary-color: #4e73df; --success-color: #1cc88a; --info-color: #36b9cc;
        --warning-color: #f6c23e; --text-dark: #343a40; --text-muted: #858796;
        --border-color: #e3e6f0; --body-bg: #f0f2f5;
    }
    .kpi-card {
        background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 1rem;
        transition: all 0.3s ease-in-out; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        text-decoration: none; color: inherit;
    }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
    .icon-circle { width: 3.5rem; height: 3.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,.1); }
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); } .text-primary { color: #4e73df !important; }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1); } .text-success { color: #1cc88a !important; }
    .bg-info-soft { background-color: rgba(54, 185, 204, 0.1); } .text-info { color: #36b9cc !important; }
    .bg-warning-soft { background-color: rgba(246, 194, 62, 0.1); } .text-warning { color: #f6c23e !important; }
    .activity-list .list-group-item { border: none; padding: 1rem 0.25rem; }
    .activity-list .list-group-item:not(:last-child) { border-bottom: 1px solid var(--border-color); }
    .activity-list .activity-title { color: var(--text-dark); font-weight: 500; text-decoration: none; }
    .activity-list .activity-title:hover { color: var(--primary-color); }
    .activity-list .activity-time { font-size: 0.8rem; color: var(--text-muted); }
    .custom-footer { text-align: center; padding: 1.5rem 0; margin-top: 2rem; color: #a0aec0; font-size: 0.85rem; border-top: 1px solid var(--border-color); }
</style>

<div class="dashboard container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Bảng điều khiển Tổng quan</h1>
            <p class="mb-0 text-muted">Chào mừng trở lại, <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Admin') ?>! Tổng quan hệ thống của bạn hôm nay.</p>
        </div>
        <div>
             <a href="blogs/create.php" class="btn btn-success me-2"><i class="fas fa-feather-alt me-2"></i>Viết bài mới</a>
             <a href="projects/create.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm dự án mới</a>
        </div>
    </div>
    <?php if (isset($error_message)): ?> <div class="alert alert-danger"><?= $error_message ?></div> <?php endif; ?>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4"><a href="projects/index.php" class="kpi-card card h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="text-muted small">TỔNG DỰ ÁN</div><div class="h4 mb-0 fw-bold"><?= $total_projects ?? 0 ?></div></div><div class="icon-circle"><i class="fas fa-project-diagram fa-2x text-primary"></i></div></div></a></div>
        <div class="col-xl-3 col-md-6 mb-4"><a href="blogs/index.php" class="kpi-card card h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="text-muted small">TỔNG BÀI VIẾT</div><div class="h4 mb-0 fw-bold"><?= $total_blogs ?? 0 ?></div></div><div class="icon-circle"><i class="fas fa-feather-alt fa-2x text-success"></i></div></div></a></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="kpi-card card h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="text-muted small">DOANH THU THÁNG</div><div class="h4 mb-0 fw-bold"><?= number_format($revenue_this_month ?? 0) ?>₫</div></div><div class="icon-circle"><i class="fas fa-dollar-sign fa-2x text-info"></i></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><a href="orders/index.php" class="kpi-card card h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="text-muted small">ĐƠN HÀNG MỚI (HÔM NAY)</div><div class="h4 mb-0 fw-bold">+<?= $new_orders_today ?? 0 ?></div></div><div class="icon-circle"><i class="fas fa-shopping-cart fa-2x text-warning"></i></div></div></a></div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Biểu đồ doanh thu (7 ngày gần nhất)</h6></div>
                <div class="card-body"><div class="chart-area" style="height: 320px;"><canvas id="revenueChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Tỉ lệ trạng thái đơn hàng</h6></div>
                <div class="card-body"><div class="chart-pie pt-4" style="height: 320px;"><canvas id="orderStatusChart"></canvas></div></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-receipt me-2"></i>Đơn hàng mới nhất</h6><a href="orders/index.php" class="small">Xem tất cả</a></div>
                <div class="card-body"><ul class="list-group list-group-flush activity-list">
                    <?php if(!empty($recent_orders)): foreach ($recent_orders as $order): ?>
                    <li class="list-group-item"><div class="d-flex w-100 justify-content-between"><a href="orders/edit.php?id=<?= $order['id'] ?>" class="activity-title mb-1"><?= htmlspecialchars($order['customer_name']) ?></a><small class="activity-time"><?= time_elapsed_string($order['created_at']) ?></small></div><p class="mb-1 small">Đơn hàng #<?= $order['id'] ?> - <?= number_format($order['total_price'], 0) ?>₫</p></li>
                    <?php endforeach; else: ?><p class="text-muted text-center small p-3">Chưa có đơn hàng nào.</p><?php endif; ?>
                </ul></div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
             <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-feather-alt me-2"></i>Bài viết gần đây</h6><a href="blogs/index.php" class="small">Xem tất cả</a></div>
                <div class="card-body"><ul class="list-group list-group-flush activity-list">
                    <?php if(!empty($recent_blogs)): foreach ($recent_blogs as $blog): ?>
                    <li class="list-group-item"><div class="d-flex w-100 justify-content-between"><a href="blogs/edit.php?id=<?= $blog['id'] ?>" class="activity-title mb-1"><?= htmlspecialchars($blog['title']) ?></a><small class="activity-time"><?= time_elapsed_string($blog['created_at']) ?></small></div></li>
                    <?php endforeach; else: ?><p class="text-muted text-center small p-3">Chưa có bài viết nào.</p><?php endif; ?>
                </ul></div>
            </div>
        </div>
         <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center"><h6 class="m-0 fw-bold text-primary"><i class="fas fa-project-diagram me-2"></i>Dự án gần đây</h6><a href="projects/index.php" class="small">Xem tất cả</a></div>
                <div class="card-body"><ul class="list-group list-group-flush activity-list">
                    <?php if(!empty($recent_projects)): foreach ($recent_projects as $project): ?>
                    <li class="list-group-item"><div class="d-flex w-100 justify-content-between"><a href="projects/edit.php?id=<?= $project['id'] ?>" class="activity-title mb-1"><?= htmlspecialchars($project['title']) ?></a><small class="activity-time"><?= time_elapsed_string($project['created_at']) ?></small></div></li>
                    <?php endforeach; else: ?><p class="text-muted text-center small p-3">Chưa có dự án nào.</p><?php endif; ?>
                </ul></div>
            </div>
        </div>
    </div>

    <footer class="custom-footer">
        Hệ thống được thiết kế và phát triển bởi <strong>Huỳnh Phú</strong>.
        <br>
        Bản quyền thuộc về © <?= date('Y') ?> <strong>Công ty Fuaco</strong>. Mọi quyền được bảo lưu.
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Biểu đồ doanh thu
    if (document.getElementById('revenueChart')) {
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line', data: { labels: <?= $chart_labels_revenue ?? '[]' ?>, datasets: [{ label: 'Doanh thu', data: <?= $chart_values_revenue ?? '[]' ?>, backgroundColor: 'rgba(78, 115, 223, 0.1)', borderColor: '#4e73df', borderWidth: 3, pointBackgroundColor: '#4e73df', tension: 0.4, fill: true }] },
            options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('vi-VN').format(v) + '₫' } } }, plugins: { legend: {display: false}, tooltip: { callbacks: { label: c => new Intl.NumberFormat('vi-VN').format(c.parsed.y) + '₫' }}} }
        });
    }

    // 2. Biểu đồ trạng thái đơn hàng
    if (document.getElementById('orderStatusChart')) {
        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut', data: { labels: <?= $chart_labels_status ?? '[]' ?>, datasets: [{ data: <?= $chart_values_status ?? '[]' ?>, backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b', '#36b9cc', '#4e73df', '#858796'], hoverBorderColor: "rgba(234, 236, 244, 1)", }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } } }, cutout: '75%', }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = "Dashboard";
include 'layout.php';
?>