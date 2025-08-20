<?php
// Tệp: templates/compare_bar.php (Phiên bản cuối cùng - Đã thêm song ngữ)
if (empty($_SESSION['compare_list'])) {
    return; // Không hiển thị gì nếu danh sách trống
}

global $pdo;
$language_code = $_SESSION['lang'] ?? 'vi';

// --- THÊM MẢNG DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'compare_now' => 'So Sánh Ngay',
        'clear_all' => 'Xóa tất cả'
    ],
    'en' => [
        'compare_now' => 'Compare Now',
        'clear_all' => 'Clear All'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---

$ids = $_SESSION['compare_list'];
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
    SELECT p.id, p.main_image, pt.name 
    FROM products p
    JOIN product_translations pt ON p.id = pt.product_id
    WHERE p.id IN ($placeholders) AND pt.language_code = ?
");
$stmt->execute(array_merge($ids, [$language_code]));
$compare_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* CSS cho thanh so sánh (giữ nguyên) */
.compare-bar-container { position: fixed; bottom: 0; left: 0; width: 100%; height: 80px; background-color: var(--color-primary); color: white; z-index: 1020; box-shadow: 0 -5px 20px rgba(0,0,0,0.1); }
.compare-slot { display: flex; align-items: center; margin-right: 15px; padding: 5px; background: rgba(255,255,255,0.1); border-radius: 5px; position: relative; }
.compare-slot img { width: 50px; height: 50px; object-fit: cover; margin-right: 10px; }
.compare-slot-name { font-size: 0.9rem; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.remove-compare-item { position: absolute; top: -5px; right: -5px; width: 20px; height: 20px; background: white; color: black; border: none; border-radius: 50%; font-size: 12px; line-height: 20px; text-align: center; cursor: pointer; }
</style>

<div class="compare-bar-container">
    <div class="container d-flex justify-content-between align-items-center h-100">
        <div class="compare-slots d-flex align-items-center">
            <?php foreach ($compare_products as $item): ?>
                <div class="compare-slot">
                    <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($item['main_image']) ?>" alt="">
                    <span class="compare-slot-name"><?= htmlspecialchars($item['name']) ?></span>
                    <form action="<?= BASE_URL ?>pages/compare_handler.php" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="remove-compare-item">&times;</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="compare-actions">
            <a href="<?= BASE_URL ?>so-sanh" class="btn btn-primary <?= count($compare_products) < 2 ? 'disabled' : '' ?>"><?= $lang['compare_now'] ?></a>
            <form action="<?= BASE_URL ?>pages/compare_handler.php" method="POST" style="display: inline;">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-link text-white"><?= $lang['clear_all'] ?></button>
            </form>
        </div>
    </div>
</div>