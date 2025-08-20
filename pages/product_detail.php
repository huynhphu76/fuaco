<?php
// Tệp: pages/product_detail.php (Phiên bản cuối cùng - Tích hợp thư viện Drift Zoom)

global $pdo, $language_code, $params;
// --- DỮ LIỆU SONG NGỮ CHO TRANG ---
$wishlist_ids = [];
if (isset($_SESSION['customer']['id'])) {
    $stmt_wishlist = $pdo->prepare("SELECT product_id FROM wishlist WHERE customer_id = ?");
    $stmt_wishlist->execute([$_SESSION['customer']['id']]);
    $wishlist_ids = $stmt_wishlist->fetchAll(PDO::FETCH_COLUMN);
}
$action_button_translations = [
    'vi' => [
        'wishlist_add' => 'Thêm vào danh sách yêu thích',
        'wishlist_remove' => 'Xóa khỏi danh sách yêu thích',
        'compare' => 'Thêm vào so sánh',
    ],
    'en' => [
        'wishlist_add' => 'Add to Wishlist',
        'wishlist_remove' => 'Remove from Wishlist',
        'compare' => 'Add to Compare',
    ]
];
$btn_lang = $action_button_translations[$language_code];
$translations = [
    'vi' => [
        'see_review_single' => 'Xem 1 đánh giá',
        'see_reviews' => 'Xem %d đánh giá',
        'tab_description' => 'Mô tả chi tiết',
        'tab_specs' => 'Thông số kỹ thuật',
        'tab_reviews' => 'Đánh giá (%d)',
        'reviews_for_product' => '%d đánh giá cho "%s"',
        'no_reviews' => 'Chưa có đánh giá nào cho sản phẩm này.',
        'form_title' => 'Gửi đánh giá của bạn',
        'form_note' => 'Email của bạn sẽ không được hiển thị công khai.',
        'form_rating' => 'Đánh giá của bạn *',
        'form_comment' => 'Nhận xét của bạn *',
        'form_name' => 'Tên *',
        'form_email' => 'Email *',
        'form_submit' => 'Gửi đi',
        'add_to_cart' => 'Thêm vào giỏ hàng',
'out_of_stock' => 'Hết hàng',
'status' => 'Tình trạng:',
'in_stock' => 'Còn hàng',
'date_posted' => 'Ngày đăng:',
'adding_js' => 'Đang thêm...',
'error_js' => 'Đã có lỗi xảy ra.',
'related_products_title' => 'Sản phẩm liên quan',
    ],
    'en' => [
        'see_review_single' => 'See 1 review',
        'see_reviews' => 'See %d reviews',
        'tab_description' => 'Description',
        'tab_specs' => 'Specifications',
        'tab_reviews' => 'Reviews (%d)',
        'reviews_for_product' => '%d reviews for "%s"',
        'no_reviews' => 'There are no reviews for this product yet.',
        'form_title' => 'Submit Your Review',
        'form_note' => 'Your email address will not be published.',
        'form_rating' => 'Your rating *',
        'form_comment' => 'Your review *',
        'form_name' => 'Name *',
        'form_email' => 'Email *',
        'form_submit' => 'Submit',
        'add_to_cart' => 'Add to Cart',
'out_of_stock' => 'Out of Stock',
'status' => 'Status:',
'in_stock' => 'In Stock',
'date_posted' => 'Date Posted:',
'adding_js' => 'Adding...',
'error_js' => 'An error occurred.',
'related_products_title' => 'Related Products',
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---
$compare_translations = [
    'vi' => [
        'add_to_compare' => 'Thêm vào so sánh',
    ],
    'en' => [
        'add_to_compare' => 'Add to Compare',
    ]
];
$compare_lang = $compare_translations[$language_code];
try {
    // Logic PHP để lấy dữ liệu sản phẩm (giữ nguyên, đã ổn định)
    $product_slug = $params['slug'] ?? $_GET['slug'] ?? null;
    if (!$product_slug) {
        $product = null;
    } else {
        $id_stmt = $pdo->prepare("SELECT product_id FROM product_translations WHERE slug = ?");
        $id_stmt->execute([$product_slug]);
        $product_id = $id_stmt->fetchColumn();

        if ($product_id) {
            $stmt = $pdo->prepare("
                SELECT 
                    p.id as product_id, p.category_id, p.price, p.quantity, p.main_image, p.created_at,
                    pt.name, pt.slug, pt.description
                FROM products p
                JOIN product_translations pt ON p.id = pt.product_id
                WHERE p.id = :product_id AND pt.language_code = :lang AND p.status = 'active'
            ");
            $stmt->execute([':product_id' => $product_id, ':lang' => $language_code]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            // Xử lý chuyển hướng ngôn ngữ
            if ($product && $product['slug'] !== $product_slug) {
                echo '<script>window.location.href = "' . BASE_URL . 'san-pham/' . $product['slug'] . '";</script>';
                echo '<div class="container my-5 text-center"><p>Đang chuyển hướng...</p></div>';
                exit();
            }
        } else {
            $product = null;
        }
    }

    if (!$product) {
        http_response_code(404);
        echo "<div class='container my-5'><div class='alert alert-warning text-center'><h1>Sản phẩm không tồn tại</h1><p>Sản phẩm bạn đang tìm kiếm không tồn tại hoặc chưa có bản dịch cho ngôn ngữ này.</p><a href='" . BASE_URL . "san-pham' class='btn btn-dark'>Quay lại trang sản phẩm</a></div></div>";
        return;
    }
    
    // Lấy các thông tin phụ (giữ nguyên)
    $category_name = null;
    if ($product['category_id']) {
        $cat_stmt = $pdo->prepare("SELECT name FROM category_translations WHERE category_id = ? AND language_code = ?");
        $cat_stmt->execute([$product['category_id'], $language_code]);
        $category_name = $cat_stmt->fetchColumn();
    }
    $gallery_stmt = $pdo->prepare("SELECT image_url, alt_text FROM product_images WHERE product_id = ?");
    $gallery_stmt->execute([$product['product_id']]);
    $gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);
    $attributes_stmt = $pdo->prepare("SELECT attribute_name, attribute_value FROM product_attributes WHERE product_id = ? ORDER BY attribute_name");
    $attributes_stmt->execute([$product['product_id']]);
    $attributes = $attributes_stmt->fetchAll(PDO::FETCH_ASSOC);
    $related_stmt = $pdo->prepare("SELECT p.price, p.main_image, pt.name, pt.slug FROM products p JOIN product_translations pt ON p.id = pt.product_id WHERE p.category_id = :cat_id AND p.id != :prod_id AND pt.language_code = :lang AND p.status = 'active' ORDER BY RAND() LIMIT 4");
    $related_stmt->execute([':cat_id' => $product['category_id'], ':prod_id' => $product['product_id'], ':lang' => $language_code]);
    $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
// --- LOGIC MỚI: LẤY DỮ LIỆU ĐÁNH GIÁ ---
$reviews_stmt = $pdo->prepare("SELECT * FROM product_reviews WHERE product_id = ? AND status = 'approved' ORDER BY created_at DESC");
$reviews_stmt->execute([$product['product_id']]);
$reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

$review_summary = ['total' => 0, 'avg' => 0];
if (count($reviews) > 0) {
    $total_rating = 0;
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
    }
    $review_summary['total'] = count($reviews);
    $review_summary['avg'] = round($total_rating / $review_summary['total'], 1);
}
// Lấy và xóa thông báo từ session
$review_message = $_SESSION['review_message'] ?? null;
unset($_SESSION['review_message']);
} catch (PDOException $e) {
    die("Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage());
}

?>

<link rel="stylesheet" href="https://unpkg.com/drift-zoom/dist/drift-basic.min.css">
<style>
/* CSS GIAO DIỆN CHUNG (ĐÃ LOẠI BỎ CSS ZOOM TỰ VIẾT) */
.breadcrumb-container{background-color:#f8f9fa;padding:1rem 0;border-bottom:1px solid #eee}.breadcrumb{margin-bottom:0;font-size:.9rem}.breadcrumb-item a{text-decoration:none;color:var(--color-secondary)}.breadcrumb-item.active{color:var(--color-text)}.product-detail-layout{display:grid;grid-template-columns:55% 45%;gap:4rem;align-items:start}.product-gallery-column .main-image-wrapper{border-radius:var(--border-radius);overflow:hidden;aspect-ratio:1 / 1;box-shadow:0 10px 30px rgba(0,0,0,.07);position:relative}.product-gallery-column .main-image{width:100%;height:100%;object-fit:cover;cursor:crosshair}.product-gallery-column .thumbnail-wrapper{display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-top:1rem}.product-gallery-column .thumbnail-item{cursor:pointer;border-radius:6px;overflow:hidden;border:2px solid transparent;transition:all .3s ease;aspect-ratio:1 / 1}.product-gallery-column .thumbnail-item:hover{border-color:#ddd;transform:translateY(-3px)}.product-gallery-column .thumbnail-item.active{border-color:var(--color-secondary);transform:translateY(-3px);box-shadow:0 5px 15px rgba(0,0,0,.1)}.product-gallery-column .thumbnail-image{width:100%;height:100%;object-fit:cover}.product-info-column .product-category{font-size:.9rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-text);text-decoration:none;margin-bottom:.75rem;display:inline-block}.product-info-column .product-title{font-size:3rem;font-weight:700;line-height:1.2;margin-bottom:1rem}.product-info-column .product-price{font-size:2.25rem;font-weight:600;color:var(--color-secondary);font-family:var(--font-heading);margin-bottom:1.5rem}.product-info-column .product-short-description{color:#666;line-height:1.8}.product-actions{display:grid;grid-template-columns:auto 1fr;gap:1rem}.quantity-selector{display:flex;border:1px solid #ddd;border-radius:4px}.quantity-selector .quantity-input{width:60px;text-align:center;font-weight:600;border:none;background:transparent;color:inherit;-moz-appearance:textfield}.quantity-selector .quantity-input::-webkit-inner-spin-button,.quantity-selector .quantity-input::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}.quantity-selector .quantity-btn{background:#f5f5f5;border:none;width:40px;font-size:1.2rem;color:#888;cursor:pointer;transition:background-color .2s}.quantity-selector .quantity-btn:hover{background:#e9e9e9;color:#333}.btn-add-to-cart{background:var(--color-primary);color:#fff;border:none;padding:.85rem 1.5rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;border-radius:4px;cursor:pointer;transition:all .3s ease}.btn-add-to-cart:hover{background:#000;box-shadow:0 5px 15px rgba(0,0,0,.2)}.btn-add-to-cart:disabled{background:#ccc;cursor:not-allowed}.product-meta{font-size:.9rem;color:#777;border-top:1px solid #eee;padding-top:1.5rem}.product-meta .meta-item{margin-bottom:.5rem}.stock-status.in-stock{color:#198754}.stock-status.out-of-stock{color:#dc3545}.product-details-full{margin-top:4rem}.product-tabs .nav-tabs{border-bottom:2px solid #dee2e6}.product-tabs .nav-link{color:var(--color-text);font-weight:600;padding:1rem 0;margin-right:2.5rem;border:none;border-bottom:3px solid transparent;transition:all .3s ease}.product-tabs .nav-link:hover{border-color:#ddd}.product-tabs .nav-link.active{color:var(--color-secondary);border-color:var(--color-secondary);background:transparent}.product-tabs .tab-content{border:none;padding:0}.product-tabs .tab-pane-content{padding-top:2rem}.product-tabs .tab-pane-content img{max-width:100%;height:auto;border-radius:var(--border-radius);margin:1rem 0}.specs-table{width:100%;border-collapse:collapse}.specs-table td,.specs-table th{padding:1rem;border-bottom:1px solid #f0f0f0}.specs-table th{font-weight:600;text-align:left;width:30%}.related-products-section{padding:4rem 0;margin-top:3rem;background-color:var(--color-light-bg)}.related-products-section .section-title{font-size:2.5rem;font-weight:700;margin-bottom:3rem;text-align:center}.product-card{border:1px solid #f0f0f0;border-radius:var(--border-radius);overflow:hidden;transition:all .4s cubic-bezier(.25,.8,.25,1);background-color:#fff;display:flex;flex-direction:column;height:100%}.product-card:hover{transform:translateY(-8px);box-shadow:0 15px 30px rgba(0,0,0,.08)}.product-card .card-img-top{border-radius:0;aspect-ratio:1 / 1.2;object-fit:cover}.product-card .card-body{padding:1.5rem}.product-card .product-title{font-family:var(--font-body);font-weight:600;font-size:1.05rem;color:var(--color-primary);margin-bottom:.5rem}.product-card .product-title a{color:inherit;text-decoration:none;transition:color .3s ease}.product-card .product-title a:hover{color:var(--color-secondary)}.product-card .product-price{font-family:var(--font-heading);font-size:1.25rem;font-weight:700;color:var(--color-secondary)}@media (max-width:991px){.product-detail-layout{grid-template-columns:1fr;gap:2rem}.drift-zoom-pane{display:none!important; /* Ẩn khung zoom trên mobile */}.product-info-column .product-title{font-size:2.5rem}.product-info-column .product-price{font-size:2rem}}
</style>

<div class="breadcrumb-container">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>san-pham">Cửa Hàng</a></li>
                <?php if ($category_name): ?>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>san-pham?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($category_name) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container my-5 product-detail-layout">
    <div class="product-gallery-column">
        <div class="product-gallery">
            <div class="main-image-wrapper">
                <img id="main-product-image" 
                     src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>" 
                     data-zoom="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     class="main-image">
            </div>
            
            <?php if(!empty($gallery_images)): ?>
            <div class="thumbnail-wrapper">
                <div class="thumbnail-item active">
                    <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                         data-image="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                         data-zoom="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                         alt="Thumbnail chính" class="thumbnail-image">
                </div>
                <?php foreach ($gallery_images as $image): ?>
                    <div class="thumbnail-item">
                        <img src="<?= BASE_URL . htmlspecialchars($image['image_url']) ?>"
                             data-image="<?= BASE_URL . htmlspecialchars($image['image_url']) ?>"
                             data-zoom="<?= BASE_URL . htmlspecialchars($image['image_url']) ?>"
                             alt="<?= htmlspecialchars($image['alt_text'] ?? 'Thumbnail sản phẩm') ?>" class="thumbnail-image">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-info-column">
        <div class="product-info">
            <?php if ($category_name): ?>
            <a href="<?= BASE_URL ?>san-pham?category=<?= $product['category_id'] ?>" class="product-category"><?= htmlspecialchars($category_name) ?></a>
            <?php endif; ?>
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <?php if ($review_summary['total'] > 0): ?>
    <div class="product-rating-summary">
        <span class="star-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="<?= $i <= $review_summary['avg'] ? 'fas' : 'far' ?> fa-star"></i>
            <?php endfor; ?>
        </span>
     <a href="#reviews-tab-pane" class="reviews-link">(<?= $review_summary['total'] > 1 ? sprintf($lang['see_reviews'], $review_summary['total']) : $lang['see_review_single'] ?>)</a>
    </div>
<?php endif; ?>
            <p class="product-price"><?= number_format($product['price']) ?>₫</p>
            <div class="product-short-description my-4">
                <p><?= nl2br(htmlspecialchars(strip_tags($product['description']))) ?></p>
            </div>
         <form id="add-to-cart-form" class="product-actions my-4">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    
    <div class="quantity-selector">
        <button type="button" class="quantity-btn" data-action="decrease">-</button>
        <input type="number" class="quantity-input" value="1" min="1" max="<?= htmlspecialchars($product['quantity']) ?>" name="quantity" <?= $product['quantity'] < 1 ? 'disabled' : '' ?>>
        <button type="button" class="quantity-btn" data-action="increase">+</button>
    </div>
  <button type="submit" class="btn-add-to-cart" ...><span class="btn-text"><?= $product['quantity'] > 0 ? $lang['add_to_cart'] : $lang['out_of_stock'] ?></span></button>
</form>

<div id="add-to-cart-message" class="mt-2"></div>

<div class="product-wishlist-action mt-3">
    <?php if(isset($_SESSION['customer'])): ?>
        <?php $is_in_wishlist = in_array($product['product_id'], $wishlist_ids); ?>
        <form action="<?= BASE_URL ?>pages/wishlist_handler.php" method="POST">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <input type="hidden" name="action" value="<?= $is_in_wishlist ? 'remove' : 'add' ?>">
            <input type="hidden" name="action" value="<?= $is_in_wishlist ? 'remove' : 'add' ?>">
                <button type="submit" class="btn btn-link text-decoration-none p-0">
                    <?= $is_in_wishlist ? $btn_lang['wishlist_remove'] : $btn_lang['wishlist_add'] ?>
            </button>
        </form>
    <?php else: ?>
            <a href="<?= BASE_URL ?>dang-nhap" class="btn btn-link text-decoration-none p-0">
                <i class="far fa-heart"></i> <?= $btn_lang['wishlist_add'] ?>
            </a>
        <?php endif; ?>
</div>
<form action="<?= BASE_URL ?>pages/compare_handler.php" method="POST" class="mt-3">
    </form>
<form action="<?= BASE_URL ?>pages/compare_handler.php" method="POST" class="mt-3">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    <button type="submit" class="btn btn-light">
        <i class="fas fa-balance-scale"></i> <?= $compare_lang['add_to_compare'] ?>
    </button>
</form>

            <div class="product-meta">
             <div class="meta-item"><strong><?= $lang['status'] ?></strong>
                    <?php if ($product['quantity'] > 0): ?>
                   <span class="stock-status in-stock"><?= $lang['in_stock'] ?> (<?= $product['quantity'] ?>)</span>
                    <?php else: ?>
                  <span class="stock-status out-of-stock"><?= $lang['out_of_stock'] ?></span>
                    <?php endif; ?>
                </div>
               <div class="meta-item">
        <strong><?= $lang['date_posted'] ?></strong>
        <span><?= date('d/m/Y', strtotime($product['created_at'])) ?></span>
    </div>
        </div>
    </div>
</div>

<div class="container my-5 product-details-full">
    <div class="row">
        <div class="col-12">
           <div class="product-tabs">
    <ul class="nav nav-tabs" id="productTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description-tab-pane" type="button"><?= $lang['tab_description'] ?></button>
        </li>
        <?php if(!empty($attributes)): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-tab-pane" type="button"><?= $lang['tab_specs'] ?></button>
        </li>
        <?php endif; ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" type="button"><?= sprintf($lang['tab_reviews'], $review_summary['total']) ?></button>
        </li>
    </ul>
    <div class="tab-content" id="productTabContent">
        <div class="tab-pane fade show active" id="description-tab-pane" role="tabpanel">
            <div class="tab-pane-content"><?= $product['description'] ?></div>
        </div>
        <?php if(!empty($attributes)): ?>
        <div class="tab-pane fade" id="specs-tab-pane" role="tabpanel">
            <div class="tab-pane-content">
                <table class="specs-table">
                    <tbody>
                        <?php foreach ($attributes as $attribute): ?>
                        <tr>
                            <th><?= htmlspecialchars($attribute['attribute_name']) ?></th>
                            <td><?= htmlspecialchars($attribute['attribute_value']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <div class="tab-pane fade" id="reviews-tab-pane" role="tabpanel">
            <div class="tab-pane-content">
              <div>
    <h4><?= sprintf($lang['reviews_for_product'], $review_summary['total'], htmlspecialchars($product['name'])) ?></h4>
    <?php if(empty($reviews)): ?>
        <p><?= $lang['no_reviews'] ?></p>
    <?php else: ?>
        <ul class="review-list">
            <?php foreach($reviews as $review): ?>
            <li class="review-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="review-author"><?= htmlspecialchars($review['author_name']) ?></span>
                        <span class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <div class="star-rating">
                        <?php for ($i = 0; $i < 5; $i++): ?><i class="<?= $i < $review['rating'] ? 'fas' : 'far' ?> fa-star"></i><?php endfor; ?>
                    </div>
                </div>
                <p class="review-content"><?= nl2br(htmlspecialchars($review['content'])) ?></p>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr class="my-5"> <div id="review-form" class="review-form">
        <h4><?= $lang['form_title'] ?></h4>
        <p><?= $lang['form_note'] ?></p>
        
        <?php if ($review_message): ?>
            <div class="alert alert-<?= $review_message['type'] == 'success' ? 'success' : 'danger' ?>">
                <?= htmlspecialchars($review_message['text']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>pages/product_review_handler.php" method="POST">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <input type="hidden" name="product_slug" value="<?= $product['slug'] ?>">
            <div class="mb-3">
                <label class="form-label"><?= $lang['form_rating'] ?></label>
                <div class="star-rating-input">
                    <input type="radio" name="rating" id="rate-5" value="5" required><label for="rate-5"><i class="fas fa-star"></i></label>
                    <input type="radio" name="rating" id="rate-4" value="4"><label for="rate-4"><i class="fas fa-star"></i></label>
                    <input type="radio" name="rating" id="rate-3" value="3"><label for="rate-3"><i class="fas fa-star"></i></label>
                    <input type="radio" name="rating" id="rate-2" value="2"><label for="rate-2"><i class="fas fa-star"></i></label>
                    <input type="radio" name="rating" id="rate-1" value="1"><label for="rate-1"><i class="fas fa-star"></i></label>
                </div>
            </div>
            <div class="mb-3">
                <label for="reviewContent" class="form-label"><?= $lang['form_comment'] ?></label>
                <textarea name="content" id="reviewContent" rows="4" class="form-control" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="authorName" class="form-label"><?= $lang['form_name'] ?></label>
                    <input type="text" name="name" id="authorName" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="authorEmail" class="form-label"><?= $lang['form_email'] ?></label>
                    <input type="email" name="email" id="authorEmail" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><?= $lang['form_submit'] ?></button>
        </form>
    </div>
</div>
            </div>
        </div>
    </div>
</div> </div> </div> </div> </div>
<?php if (!empty($related_products)): ?>
<div class="related-products-section">
    <div class="container">
        <h2 class="section-title"><?= $lang['related_products_title'] ?></h2>
        <div class="row">
            <?php foreach ($related_products as $related_product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card h-100">
                        <a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($related_product['slug'] ?? '') ?>">
                            <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($related_product['main_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($related_product['name']) ?>">
                        </a>
                        <div class="card-body text-center d-flex flex-column">
                            <h5 class="product-title flex-grow-1"><a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($related_product['slug'] ?? '') ?>" class="text-decoration-none"><?= htmlspecialchars($related_product['name']) ?></a></h5>
                            <p class="product-price mt-2 mb-0"><?= number_format($related_product['price']) ?>₫</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://unpkg.com/drift-zoom/dist/Drift.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // KHỐI JAVASCRIPT MỚI, SỬ DỤNG THƯ VIỆN DRIFT
    let drift; // Biến để lưu trữ instance của Drift
    const mainImage = document.getElementById('main-product-image');

    // Hàm khởi tạo hoặc cập nhật zoom
    function initOrUpdateZoom() {
        // Hủy instance cũ nếu nó đã tồn tại
        if (drift) {
            drift.destroy(); 
        }
        // Tạo instance mới
        drift = new Drift(mainImage, {
            paneContainer: document.querySelector('.main-image-wrapper'),
            inlinePane: false, // Để khung zoom hiển thị bên cạnh
            hoverBoundingBox: true // Tăng độ nhạy khi rê chuột
        });
    }

    // Xử lý khi click vào thumbnail
    if (mainImage) {
        initOrUpdateZoom(); // Khởi tạo zoom cho ảnh chính khi tải trang

        const thumbnails = document.querySelectorAll('.thumbnail-item');
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function () {
                // Bỏ active ở tất cả thumbnail và thêm vào thumbnail được click
                thumbnails.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
                
                const clickedImg = this.querySelector('img');
                // Cập nhật ảnh chính và thuộc tính data-zoom của nó
                mainImage.src = clickedImg.dataset.image;
                mainImage.dataset.zoom = clickedImg.dataset.zoom;

                // Khởi tạo lại zoom cho ảnh mới
                // Drift sẽ tự động nhận biết sự thay đổi thuộc tính `src`, nhưng gọi lại hàm để chắc chắn
                initOrUpdateZoom(); 
            });
        });
    }

    // JS cho nút tăng giảm số lượng (giữ nguyên)
    const quantitySelector = document.querySelector('.quantity-selector');
    if (quantitySelector) {
        const input = quantitySelector.querySelector('.quantity-input');
        const btnDecrease = quantitySelector.querySelector('[data-action="decrease"]');
        const btnIncrease = quantitySelector.querySelector('[data-action="increase"]');
        const maxQuantity = parseInt(input.max, 10) || 1;

        btnDecrease.addEventListener('click', function() {
            let currentValue = parseInt(input.value, 10);
            if (currentValue > 1) input.value = currentValue - 1;
        });

        btnIncrease.addEventListener('click', function() {
            let currentValue = parseInt(input.value, 10);
            if (currentValue < maxQuantity) input.value = currentValue + 1;
        });
    }
    // [THÊM VÀO] Xử lý thêm vào giỏ hàng bằng AJAX
const addToCartForm = document.getElementById('add-to-cart-form');
if (addToCartForm) {
    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(addToCartForm);
        const messageDiv = document.getElementById('add-to-cart-message');
        const cartBadge = document.getElementById('cart-item-count-badge');
        
       messageDiv.innerHTML = '<?= $lang['adding_js'] ?>';
        
        fetch('<?= BASE_URL ?>pages/cart_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                if (cartBadge) {
                    cartBadge.textContent = data.cart_item_count;
                }
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
          messageDiv.innerHTML = '<div class="alert alert-danger"><?= $lang['error_js'] ?></div>';
            console.error('Error:', error);
        });
    });
}
});
</script>