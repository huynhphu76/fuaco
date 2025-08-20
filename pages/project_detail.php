<?php
// Tệp: /pages/project_detail.php
global $pdo, $language_code, $params;

$slug = $params['slug'] ?? null;
if (!$slug) { die("Không tìm thấy dự án."); }

try {
    // BƯỚC 1: Dùng slug để tìm ra ID của dự án, bất kể ngôn ngữ là gì.
    $id_stmt = $pdo->prepare("SELECT project_id FROM project_translations WHERE slug = ?");
    $id_stmt->execute([$slug]);
    $project_id = $id_stmt->fetchColumn();

    // BƯỚC 2: Nếu tìm thấy ID, dùng ID đó để lấy đúng bản dịch theo ngôn ngữ hiện tại.
    if ($project_id) {
        $stmt = $pdo->prepare("
            SELECT p.id, p.thumbnail, p.completed_at, pt.title, pt.description
            FROM projects p
            JOIN project_translations pt ON p.id = pt.project_id
            WHERE p.id = :project_id AND pt.language_code = :lang
        ");
        $stmt->execute([':project_id' => $project_id, ':lang' => $language_code]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $project = null;
    }

    if (!$project) {
        http_response_code(404);
        echo "<div class='container my-5 text-center'><h1>404</h1><p>Không tìm thấy dự án.</p></div>";
        return;
    }

    // Lấy thư viện ảnh của dự án
    $images_stmt = $pdo->prepare("SELECT image_url FROM project_images WHERE project_id = ?");
    $images_stmt->execute([$project['id']]);
    $project_images = $images_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Lấy sản phẩm liên quan đến dự án
    $products_stmt = $pdo->prepare("
        SELECT p.price, p.main_image, pt.name, pt.slug
        FROM products p
        JOIN product_translations pt ON p.id = pt.product_id
        JOIN project_products pp ON p.id = pp.product_id
        WHERE pp.project_id = ? AND pt.language_code = ?
    ");
    $products_stmt->execute([$project['id'], $language_code]);
    $related_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Dữ liệu song ngữ
    $lang = [
        'vi' => ['completed_on' => 'Hoàn thành vào', 'gallery' => 'Thư viện hình ảnh', 'products_used' => 'Sản phẩm trong dự án'],
        'en' => ['completed_on' => 'Completed on', 'gallery' => 'Image Gallery', 'products_used' => 'Products in this Project']
    ][$language_code];

} catch (PDOException $e) {
    die("Lỗi cơ sở dữ liệu: " . $e->getMessage());
}
?>

<div class="breadcrumb-container">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>du-an">Dự án</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($project['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container my-5">
    <div class="project-detail-header text-center">
        <h1><?= htmlspecialchars($project['title']) ?></h1>
        <?php if ($project['completed_at']): ?>
            <p class="text-muted"><?= $lang['completed_on'] ?>: <?= date('d/m/Y', strtotime($project['completed_at'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="project-detail-content content-section my-5">
                <?= $project['description'] ?>
            </div>

            <?php if (!empty($project_images)): ?>
            <div class="project-gallery my-5">
                <h3 class="text-center mb-4"><?= $lang['gallery'] ?></h3>
                <div class="row g-3">
                    <?php foreach ($project_images as $image_url): ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="<?= BASE_URL . htmlspecialchars($image_url) ?>" data-fancybox="gallery">
                            <img src="<?= BASE_URL . htmlspecialchars($image_url) ?>" class="img-fluid rounded">
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($related_products)): ?>
            <div class="project-related-products my-5">
                <h3 class="text-center mb-4"><?= $lang['products_used'] ?></h3>
                <div class="row">
                    <?php foreach ($related_products as $product): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-card h-100">
                            <a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($product['slug']) ?>">
                                <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                            </a>
                            <div class="card-body text-center">
                                <h5 class="product-title"><a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($product['slug']) ?>" class="text-decoration-none"><?= htmlspecialchars($product['name']) ?></a></h5>
                                <p class="product-price mt-2 mb-0"><?= number_format($product['price']) ?>₫</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
  Fancybox.bind("[data-fancybox]", {});
</script>