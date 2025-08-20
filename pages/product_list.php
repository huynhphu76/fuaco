<?php
// Tệp: pages/product_list.php (Phiên bản cuối cùng - Tích hợp Logic và Thiết kế mới)

global $pdo, $language_code;
$wishlist_ids = [];
if (isset($_SESSION['customer']['id'])) {
    $stmt_wishlist = $pdo->prepare("SELECT product_id FROM wishlist WHERE customer_id = ?");
    $stmt_wishlist->execute([$_SESSION['customer']['id']]);
    $wishlist_ids = $stmt_wishlist->fetchAll(PDO::FETCH_COLUMN);
}
try {
    // === 1. LẤY DỮ LIỆU ĐỘNG CHO TRANG ===
    $theme_options_trans_stmt = $pdo->prepare("SELECT option_key, option_value FROM theme_option_translations WHERE language_code = ?");
    $theme_options_trans_stmt->execute([$language_code]);
    $trans_options = $theme_options_trans_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
// === BẮT ĐẦU MẢNG DỊCH TRUNG TÂM HOÀN CHỈNH ===
$translations = [
    'vi' => [
        'page_title' => 'Bộ Sưu Tập',
        'page_subtitle' => 'Mang phong cách nghỉ dưỡng đến ngôi nhà của bạn.',
        'filter_category' => 'Danh mục',
        'filter_all_products' => 'Tất cả sản phẩm',
        'filter_price_range' => 'Khoảng giá',
        'price_from' => 'Từ',
        'price_to' => 'Đến',
        'apply_filter_btn' => 'Áp dụng bộ lọc',
        'sort_label' => 'Sắp xếp:',
        'sort_newest' => 'Mới nhất',
        'sort_price_asc' => 'Giá: Tăng dần',
        'sort_price_desc' => 'Giá: Giảm dần',
        'found_results_for' => 'Tìm thấy %d kết quả cho "%s"',
        'showing_results' => 'Hiển thị %d trên %d sản phẩm',
        'no_products_found' => 'Không tìm thấy sản phẩm nào phù hợp.',
        'wishlist_add' => 'Yêu thích',
        'wishlist_remove' => 'Xóa khỏi yêu thích',
        'compare' => 'So sánh',
        'add_to_cart' => 'Thêm vào giỏ hàng',
        'alert_added_to_cart' => 'Đã thêm sản phẩm vào giỏ hàng!',
        'alert_error' => 'Lỗi: '
    ],
    'en' => [
        'page_title' => 'The Collection',
        'page_subtitle' => 'Bring resort-style living to your home.',
        'filter_category' => 'Category',
        'filter_all_products' => 'All Products',
        'filter_price_range' => 'Price Range',
        'price_from' => 'From',
        'price_to' => 'To',
        'apply_filter_btn' => 'Apply Filter',
        'sort_label' => 'Sort by:',
        'sort_newest' => 'Newest',
        'sort_price_asc' => 'Price: Low to High',
        'sort_price_desc' => 'Price: High to Low',
        'found_results_for' => 'Found %d results for "%s"',
        'showing_results' => 'Showing %d of %d products',
        'no_products_found' => 'No products found matching your selection.',
        'wishlist_add' => 'Add to Wishlist',
        'wishlist_remove' => 'Remove from Wishlist',
        'compare' => 'Compare',
        'add_to_cart' => 'Add to Cart',
        'alert_added_to_cart' => 'Product added to cart!',
        'alert_error' => 'Error: '
    ]
];
$lang = $translations[$language_code];
// === KẾT THÚC MẢNG DỊCH ===

    // Lấy danh mục cho sidebar
    $stmt_categories = $pdo->prepare("SELECT c.id, ct.name FROM categories c JOIN category_translations ct ON c.id = ct.category_id WHERE ct.language_code = ? ORDER BY ct.name ASC");
    $stmt_categories->execute([$language_code]);
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    // Lấy và nhóm các thuộc tính từ bảng product_attributes
    $attributes_query = $pdo->query("SELECT DISTINCT attribute_name, attribute_value FROM product_attributes ORDER BY attribute_name, attribute_value");
    $all_attributes_flat = $attributes_query->fetchAll(PDO::FETCH_ASSOC);
    $attributes = [];
    foreach ($all_attributes_flat as $attr) {
        $attributes[$attr['attribute_name']][] = $attr['attribute_value'];
    }

    // === 2. XỬ LÝ LỌC, TÌM KIẾM, SẮP XẾP VÀ PHÂN TRANG ===
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $products_per_page = 9;
    $offset = ($page - 1) * $products_per_page;
    
    $search_term = trim($_GET['search'] ?? '');
    $selected_category = $_GET['category'] ?? null;
    $selected_attributes = isset($_GET['attrs']) && is_array($_GET['attrs']) ? $_GET['attrs'] : [];
    $sort_order = $_GET['sort'] ?? 'newest';
    $min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? $_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? $_GET['max_price'] : null;

    // Xây dựng câu lệnh SQL động
    $base_select = "SELECT DISTINCT p.id, p.price, p.main_image, pt.name, pt.slug";
    $count_select = "SELECT COUNT(DISTINCT p.id)";
    
    $sql_from = " FROM products p JOIN product_translations pt ON p.id = pt.product_id";
    $sql_where = " WHERE pt.language_code = :lang AND p.status = 'active'";
    $params = [':lang' => $language_code];

    if (!empty($search_term)) { $sql_where .= " AND (pt.name LIKE :search)"; $params[':search'] = "%{$search_term}%"; }
    if ($selected_category) { $sql_where .= " AND p.category_id = :cat_id"; $params[':cat_id'] = $selected_category; }
    if ($min_price !== null && $min_price !== '') { $sql_where .= " AND p.price >= :min_price"; $params[':min_price'] = $min_price; }
    if ($max_price !== null && $max_price !== '') { $sql_where .= " AND p.price <= :max_price"; $params[':max_price'] = $max_price; }
    
    if (!empty($selected_attributes)) {
        $sql_from .= " JOIN product_attributes pa ON p.id = pa.product_id";
        $attr_placeholders = []; $i = 0;
        foreach ($selected_attributes as $attr_value) {
            $key = ":attr_val_" . $i++;
            $attr_placeholders[] = $key;
            $params[$key] = $attr_value;
        }
        $sql_where .= " AND pa.attribute_value IN (" . implode(', ', $attr_placeholders) . ")";
    }
    
    $sql_order_by = " ORDER BY ";
    switch ($sort_order) {
        case 'price_asc': $sql_order_by .= "p.price ASC"; break;
        case 'price_desc': $sql_order_by .= "p.price DESC"; break;
        default: $sql_order_by .= "p.created_at DESC";
    }

    $count_sql = $count_select . $sql_from . $sql_where;
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $products_per_page);

    $products_sql = $base_select . $sql_from . $sql_where . $sql_order_by . " LIMIT :limit OFFSET :offset";
    $products_stmt = $pdo->prepare($products_sql);
    
    $params[':limit'] = $products_per_page;
    $params[':offset'] = $offset;

    foreach ($params as $key => &$val) {
        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $products_stmt->bindParam($key, $val, $type);
    }

    $products_stmt->execute();
    $products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

    function get_current_url_with_params($new_params) {
        $current_params = $_GET;
        unset($current_params['url']);
        $all_params = array_merge($current_params, $new_params);
        return BASE_URL . 'san-pham?' . http_build_query($all_params);
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage());
}
?>

<style>
    .page-header {
        padding: 5rem 0;
        background-color: var(--color-light-bg);
        text-align: center;
        margin-bottom: 4rem;
        border-bottom: 1px solid #eee;
    }
    .page-header h1 {
        font-size: 3.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .page-header .lead {
        font-size: 1.15rem;
        color: var(--color-text);
        max-width: 600px;
        margin: 0 auto;
    }
    .filter-sidebar {
        padding: 2rem;
        background-color: #fdfdfd;
        border: 1px solid #f0f0f0;
        border-radius: var(--border-radius);
    }
    .filter-group {
        border-bottom: 1px solid #eee;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .filter-sidebar .filter-group:last-of-type {
        border-bottom: none; margin-bottom: 0; padding-bottom: 0;
    }
    .filter-title {
        font-family: var(--font-body);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
        margin-bottom: 1rem;
        color: var(--color-primary);
    }
    .category-link {
        color: var(--color-text);
        text-decoration: none;
        display: block;
        padding: 0.4rem 0;
        transition: all 0.3s ease;
        border-radius: 4px;
    }
    .category-link:hover {
        color: var(--color-secondary);
        background-color: #f8f9fa;
        padding-left: 0.5rem;
    }
    .category-link.active {
        color: var(--color-secondary);
        font-weight: 700;
    }
    .form-check-label { font-size: 0.95rem; cursor: pointer; }
    .form-check-input { cursor: pointer; }
    .form-check-input:checked {
        background-color: var(--color-secondary);
        border-color: var(--color-secondary);
    }
    .filter-sidebar .btn-dark {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        padding: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    .filter-sidebar .btn-dark:hover { background-color: #000; }
    .product-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    .product-list-header .form-select { width: auto; min-width: 180px; }
    .product-card {
        border: 1px solid #f0f0f0;
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        background-color: #fff;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    }
    .product-card .card-img-top {
        border-radius: 0;
        aspect-ratio: 1 / 1.2;
        object-fit: cover;
    }
    .product-card .card-body { padding: 1.5rem; }
    .product-card .product-title {
        font-family: var(--font-body);
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--color-primary);
        margin-bottom: 0.5rem;
    }
    .product-card .product-title a { color: inherit; text-decoration: none; transition: color 0.3s ease; }
    .product-card .product-title a:hover { color: var(--color-secondary); }
    .product-card .product-price {
        font-family: var(--font-heading);
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-secondary);
    }
    .pagination .page-link {
        color: var(--color-primary);
        border-radius: 50% !important;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 5px;
        border: 1px solid #eee;
    }
    .pagination .page-link:hover {
        background-color: var(--color-light-bg);
        color: var(--color-secondary);
    }
    .pagination .page-item.active .page-link {
        background-color: var(--color-secondary);
        border-color: var(--color-secondary);
        color: var(--color-white);
        box-shadow: 0 4px 10px rgba(197, 164, 126, 0.4);
    }
</style>
<div class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($lang['page_title']) ?></h1>
<p class="lead"><?= htmlspecialchars($lang['page_subtitle']) ?></p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3">
            <aside class="filter-sidebar">
                <form action="<?= BASE_URL ?>san-pham" method="GET">
                    <?php if (!empty($search_term)): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                    <?php endif; ?>

                    <div class="filter-group">
                      <h6 class="filter-title mb-3"><?= htmlspecialchars($lang['filter_category']) ?></h6>
                        <a href="<?= BASE_URL ?>san-pham?search=<?= htmlspecialchars($search_term) ?>" class="category-link <?= !$selected_category ? 'active' : '' ?>"><?= htmlspecialchars($lang['filter_all_products']) ?></a>
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= get_current_url_with_params(['category' => $category['id'], 'page' => 1]) ?>" class="category-link <?= ($selected_category == $category['id']) ? 'active' : '' ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
<h6 class="filter-title mb-3"><?= htmlspecialchars($lang['filter_price_range']) ?></h6>
                        <div class="d-flex align-items-center">
                            <input type="number" name="min_price" class="form-control me-2" placeholder="<?= $lang['price_from'] ?>" min="0">
                            <span class="mx-1">-</span>
                            <input type="number" name="max_price" class="form-control ms-2" placeholder="<?= $lang['price_to'] ?>" min="0">
                        </div>
                    </div>

                    <?php foreach ($attributes as $name => $values): ?>
                    <div class="filter-group">
                        <h6 class="filter-title mb-3"><?= htmlspecialchars($name) ?></h6>
                        <?php foreach ($values as $value): 
                            $checkbox_id = 'attr_' . htmlspecialchars(preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($name . '_' . $value)));
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="attrs[]" value="<?= htmlspecialchars($value) ?>" id="<?= $checkbox_id ?>" <?= in_array($value, $selected_attributes) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $checkbox_id ?>">
                                <?= htmlspecialchars($value) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-grid">
<button type="submit" class="btn btn-dark"><?= htmlspecialchars($lang['apply_filter_btn']) ?></button>
                    </div>
                </form>
            </aside>
        </div>

        <div class="col-lg-9">
            <div class="product-list-header">
                <span class="text-muted">
                    <?php if (!empty($search_term)): ?>
    <?= sprintf($lang['found_results_for'], $total_products, htmlspecialchars($search_term)) ?>
<?php else: ?>
    <?= sprintf($lang['showing_results'], count($products), $total_products) ?>
<?php endif; ?>
                </span>
                <form action="<?= BASE_URL ?>san-pham" method="GET" class="d-flex align-items-center">
                    <?php if($selected_category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>"><?php endif; ?>
                    <?php foreach($selected_attributes as $attr): ?><input type="hidden" name="attrs[]" value="<?= htmlspecialchars($attr) ?>"><?php endforeach; ?>
                    <?php if (!empty($search_term)): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>"><?php endif; ?>
                    <?php if ($min_price !== null && $min_price !== ''): ?><input type="hidden" name="min_price" value="<?= htmlspecialchars($min_price) ?>"><?php endif; ?>
                    <?php if ($max_price !== null && $max_price !== ''): ?><input type="hidden" name="max_price" value="<?= htmlspecialchars($max_price) ?>"><?php endif; ?>

                    <label for="sort" class="form-label me-2 mb-0 flex-shrink-0"><?= htmlspecialchars($lang['sort_label']) ?></label>
                    <select name="sort" id="sort" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="newest" <?= $sort_order == 'newest' ? 'selected' : '' ?>><?= htmlspecialchars($lang['sort_newest']) ?></option>
                        <option value="price_asc" <?= $sort_order == 'price_asc' ? 'selected' : '' ?>><?= htmlspecialchars($lang['sort_price_asc']) ?></option>
                        <option value="price_desc" <?= $sort_order == 'price_desc' ? 'selected' : '' ?>><?= htmlspecialchars($lang['sort_price_desc']) ?></option>
                    </select>
                </form>
            </div>

            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12"><p><?= htmlspecialchars($no_products_found_text) ?></p></div>
                <?php else: ?>
<?php foreach ($products as $product): ?>
<div class="col-lg-4 col-md-6 mb-4">
    <div class="product-card h-100">
        
        <div class="product-image-container">
            <a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($product['slug'] ?? '') ?>">
                <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['main_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
            </a>
           <div class="product-actions-overlay">
    <?php if(isset($_SESSION['customer'])): ?>
        <?php $is_in_wishlist = in_array($product['id'], $wishlist_ids); ?>
        <form action="<?= BASE_URL ?>pages/wishlist_handler.php" method="POST" class="d-inline">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="action" value="<?= $is_in_wishlist ? 'remove' : 'add' ?>">
           <button type="submit" class="btn btn-light btn-sm rounded-circle action-btn" title="<?= $is_in_wishlist ? $lang['wishlist_remove'] : $lang['wishlist_add'] ?>">
    <i class="fa-heart <?= $is_in_wishlist ? 'fas' : 'far' ?>"></i>
</button>
        </form>
    <?php else: ?>
        <a href="<?= BASE_URL ?>dang-nhap" class="btn btn-light btn-sm rounded-circle action-btn" title="<?= $lang['wishlist_add'] ?>">
            <i class="far fa-heart"></i>
        </a>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>pages/compare_handler.php" method="POST" class="d-inline">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <button type="submit" class="btn btn-light btn-sm rounded-circle action-btn" title="<?= $lang['compare'] ?>">
            <i class="fas fa-balance-scale"></i>
        </button>
    </form>

    <button class="btn btn-light btn-sm rounded-circle action-btn ajax-add-to-cart" 
            data-product-id="<?= $product['id'] ?>" 
           title="<?= $lang['add_to_cart'] ?>">
        <i class="fas fa-shopping-cart"></i>
    </button>
</div>
        </div>
        
        <div class="card-body text-center">
            <h5 class="product-title flex-grow-1"><a href="<?= BASE_URL ?>san-pham/<?= htmlspecialchars($product['slug'] ?? '') ?>" class="text-decoration-none"><?= htmlspecialchars($product['name']) ?></a></h5>
            <p class="product-price mt-2 mb-0"><?= number_format($product['price']) ?>₫</p>
        </div>

    </div>
</div>
<?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav class="mt-4" aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= get_current_url_with_params(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ajax-add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', 1); // Mặc định thêm 1 sản phẩm

            fetch('<?= BASE_URL ?>pages/cart_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật số lượng trên icon giỏ hàng
                    const cartBadge = document.getElementById('cart-item-count-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_item_count;
                    }
                    // Bạn có thể thêm một thông báo popup nhỏ ở đây nếu muốn
alert('<?= $lang['alert_added_to_cart'] ?>');
                } else {
                    alert('<?= $lang['alert_error'] ?>' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>