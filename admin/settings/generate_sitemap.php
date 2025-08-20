<?php
// Tệp: admin/settings/generate_sitemap.php (Phiên bản đã sửa lỗi)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/permission_check.php';
if (!hasPermission('manage-settings')) { die('Bạn không có quyền truy cập chức năng này.'); }
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

try {
    $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    // Hàm trợ giúp để thêm một URL vào sitemap
    function add_url(&$content, $loc, $lastmod) {
        $content .= '  <url>' . PHP_EOL;
        $content .= '    <loc>' . htmlspecialchars($loc) . '</loc>' . PHP_EOL;
        $content .= '    <lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>' . PHP_EOL;
        $content .= '  </url>' . PHP_EOL;
    }

    // 1. Thêm các trang cơ bản
    add_url($sitemap_content, BASE_URL, date('Y-m-d'));
    add_url($sitemap_content, BASE_URL . 'san-pham', date('Y-m-d'));
    add_url($sitemap_content, BASE_URL . 'du-an', date('Y-m-d'));
    add_url($sitemap_content, BASE_URL . 'bai-viet', date('Y-m-d'));
    add_url($sitemap_content, BASE_URL . 'lien-he', date('Y-m-d'));

    // 2. Lấy tất cả slug từ các bảng (đã sửa lỗi truy vấn)
    $queries = [
        "SELECT slug, updated_at FROM pages WHERE is_published = 1" => "",
        "SELECT pt.slug, p.updated_at FROM product_translations pt JOIN products p ON pt.product_id = p.id WHERE pt.slug IS NOT NULL" => "san-pham/",
        "SELECT bt.slug, b.created_at AS updated_at FROM blog_translations bt JOIN blogs b ON bt.blog_id = b.id WHERE bt.slug IS NOT NULL" => "bai-viet/",
        "SELECT pt.slug, p.created_at AS updated_at FROM project_translations pt JOIN projects p ON pt.project_id = p.id WHERE pt.slug IS NOT NULL" => "du-an/"
    ];
    
    foreach ($queries as $sql => $prefix) {
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['slug'])) {
                $slug = ltrim($row['slug'], '/');
                $url = BASE_URL . $prefix . $slug;
                add_url($sitemap_content, $url, $row['updated_at'] ?? date('Y-m-d'));
            }
        }
    }
    
    $sitemap_content .= '</urlset>';

    // Ghi file sitemap.xml ra thư mục gốc của website
  $sitemap_path = __DIR__ . '/../../sitemap.xml';
    file_put_contents($sitemap_path, $sitemap_content);

    // Chuyển hướng lại trang cài đặt với thông báo thành công
    header('Location: index.php?sitemap_success=1');
    exit;

} catch (Exception $e) {
    die("Lỗi khi tạo sitemap: " . $e->getMessage());
}
?>