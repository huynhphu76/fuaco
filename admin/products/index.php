<?php
// Tệp: admin/products/index.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('view-products')) { die('Bạn không có quyền xem sản phẩm.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/csrf_helper.php';
get_csrf_token(); // Đảm bảo token được tạo

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$products_per_page = 10;
$offset = ($page - 1) * $products_per_page;
$search_term = $_GET['search'] ?? '';
$language_code = 'vi'; // Luôn hiển thị Tiếng Việt trong admin

// Xây dựng điều kiện WHERE và các tham số tương ứng
$where_clauses = ["pt.language_code = :lang_code"];
$params = [':lang_code' => $language_code];
if (!empty($search_term)) {
    $where_clauses[] = "pt.name LIKE :search_term";
    $params[':search_term'] = "%" . $search_term . "%";
}
$where_sql = "WHERE " . implode(' AND ', $where_clauses);

// Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(p.id) FROM products p JOIN product_translations pt ON p.id = pt.product_id $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);

// SỬA LỖI: Lấy tên sản phẩm và danh mục từ bảng dịch và sử dụng tham số khác nhau
$sql = "
    SELECT p.id, p.price, p.status, p.main_image, pt.name, ct.name as category_name
    FROM products p
    JOIN product_translations pt ON p.id = pt.product_id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN category_translations ct ON c.id = ct.category_id AND ct.language_code = :lang_code_join
    $where_sql
    ORDER BY p.id DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

// SỬA LỖI: Gán (Bind) tất cả các tham số một cách tường minh và chính xác
$stmt->bindValue(':lang_code_join', $language_code, PDO::PARAM_STR);
$stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$products = $stmt->fetchAll();

ob_start();
?>
<div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-couch me-2"></i>Quản lý sản phẩm</h2>
        <?php if (hasPermission('create-products')): ?>
            <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm sản phẩm</a>
        <?php endif; ?>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php">
                <div class="input-group">
                    <input type="text" name="search" id="search-input" class="form-control" placeholder="Tìm theo tên sản phẩm (Tiếng Việt)..." value="<?= htmlspecialchars($search_term) ?>">
                    <button class="btn btn-outline-primary d-none" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle table-hover">
            <thead class="table-dark"><tr><th>ID</th><th>Ảnh</th><th>Tên sản phẩm (Tiếng Việt)</th><th>Danh mục</th><th>Giá</th><th>Trạng thái</th><th style="width: 150px;">Hành động</th></tr></thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?php if ($p['main_image']): ?><img src="/interior-website/uploads/products/<?= htmlspecialchars($p['main_image']) ?>" class="img-fluid rounded" style="width:60px; height:60px; object-fit:cover;"><?php else: ?><span class="text-muted">N/A</span><?php endif; ?></td>
                        <td><?= htmlspecialchars($p['name'] ?? '[Chưa có bản dịch]') ?></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                        <td><?= number_format($p['price'], 0, ',', '.') ?>₫</td>
                        <td><span class="badge <?= $p['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $p['status'] === 'active' ? 'Hiển thị' : 'Ẩn' ?></span></td>
                        <td>
                            <?php if (hasPermission('edit-products')): ?>
                                <a href="../product_images/index.php?product_id=<?= $p['id'] ?>" class="btn btn-sm btn-info" title="Thư viện ảnh"><i class="fas fa-images"></i></a>
                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning" title="Sửa"><i class="fas fa-edit"></i></a>
                            <?php endif; ?>
                            <?php if (hasPermission('delete-products')): ?>
<form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <?php csrf_field(); // Thêm token vào form ?>
    <button type="submit" class="btn btn-sm btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
</form>
                                <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages > 1): ?>
        <nav><ul class="pagination justify-content-center"><li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search_term) ?>">Trước</a></li><?php for ($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?= ($page == $i) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search_term) ?>"><?= $i ?></a></li><?php endfor; ?><li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search_term) ?>">Sau</a></li></ul></nav>
    <?php endif; ?>
</div>
<script>document.addEventListener('DOMContentLoaded', function() { let dt;const si=document.getElementById('search-input');if(si){si.addEventListener('input',function(){clearTimeout(dt);dt=setTimeout(function(){const sv=si.value;const cu=new URL(window.location.href);cu.searchParams.set('search',sv);cu.searchParams.set('page','1');window.location.href=cu.toString();},500);});}});</script>
<?php
$content = ob_get_clean();
$pageTitle = "Danh sách sản phẩm";
include '../layout.php';
?>