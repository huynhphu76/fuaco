<?php
// Tệp: /pages/blog_list.php
global $pdo, $language_code;
// --- DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'page_title' => 'Blog',
        'page_subtitle' => 'Khám phá những xu hướng thiết kế và câu chuyện thương hiệu từ FUACO.',
        'no_posts' => 'Chưa có bài viết nào.'
    ],
    'en' => [
        'page_title' => 'Blog',
        'page_subtitle' => 'Discover design trends and brand stories from FUACO.',
        'no_posts' => 'No posts found.'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---
// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts_per_page = 6;
$offset = ($page - 1) * $posts_per_page;

// Đếm tổng số bài viết
$total_posts_stmt = $pdo->prepare("SELECT COUNT(b.id) FROM blogs b JOIN blog_translations bt ON b.id = bt.blog_id WHERE b.status = 'published' AND bt.language_code = ?");
$total_posts_stmt->execute([$language_code]);
$total_posts = $total_posts_stmt->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Lấy bài viết cho trang hiện tại
$stmt = $pdo->prepare("
    SELECT b.thumbnail, b.created_at, bt.title, bt.slug, bt.content, bct.name as category_name
    FROM blogs b
    JOIN blog_translations bt ON b.id = bt.blog_id
    LEFT JOIN blog_categories bc ON b.category_id = bc.id
    LEFT JOIN blog_category_translations bct ON bc.id = bct.blog_category_id AND bct.language_code = :cat_lang
    WHERE b.status = 'published' AND bt.language_code = :post_lang
    ORDER BY b.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute([
    ':cat_lang' => $language_code,
    ':post_lang' => $language_code,
    ':limit' => $posts_per_page,
    ':offset' => $offset
]);
$blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

function truncate_html($text, $length = 150, $ending = '...') {
    $text = strip_tags($text);
    if (mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length);
        $text = mb_substr($text, 0, mb_strrpos($text, ' '));
        $text = $text . $ending;
    }
    return $text;
}
?>

<div class="page-header">
    <div class="container">
        <h1><?= $lang['page_title'] ?></h1>
     <p class="lead"><?= $lang['page_subtitle'] ?></p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <?php if (empty($blog_posts)): ?>
           <p><?= $lang['no_posts'] ?></p>
        <?php else: ?>
            <?php foreach ($blog_posts as $post): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="blog-card-list">
                        <a href="<?= BASE_URL ?>bai-viet/<?= htmlspecialchars($post['slug']) ?>" class="blog-card-image">
                            <img src="<?= BASE_URL ?>uploads/blogs/<?= htmlspecialchars($post['thumbnail']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                        </a>
                        <div class="blog-card-body">
                            <div class="blog-card-meta">
                                <?php if($post['category_name']): ?>
                                    <span><?= htmlspecialchars($post['category_name']) ?></span> /
                                <?php endif; ?>
                                <span><?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                            </div>
                            <h4 class="blog-card-title">
                                <a href="<?= BASE_URL ?>bai-viet/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a>
                            </h4>
                            <p class="blog-card-excerpt"><?= htmlspecialchars(truncate_html($post['content'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>bai-viet?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>