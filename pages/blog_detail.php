<?php
// Tệp: /pages/blog_detail.php
global $pdo, $language_code, $params;

$slug = $params['slug'] ?? null;
if (!$slug) { die("Không tìm thấy bài viết."); }

try {
    // Sửa lỗi: Bổ sung category_id vào câu lệnh SELECT
    $id_stmt = $pdo->prepare("SELECT blog_id FROM blog_translations WHERE slug = ?");
    $id_stmt->execute([$slug]);
    $blog_id = $id_stmt->fetchColumn();

    if ($blog_id) {
        $stmt = $pdo->prepare("
            SELECT b.id, b.category_id, b.thumbnail, b.created_at, bt.title, bt.content, bct.name as category_name, u.name as author_name
            FROM blogs b
            JOIN blog_translations bt ON b.id = bt.blog_id
            LEFT JOIN blog_categories bc ON b.category_id = bc.id
            LEFT JOIN blog_category_translations bct ON bc.id = bct.blog_category_id AND bct.language_code = :cat_lang
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.id = :blog_id AND bt.language_code = :post_lang AND b.status = 'published'
        ");
        $stmt->execute([':blog_id' => $blog_id, ':cat_lang' => $language_code, ':post_lang' => $language_code]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $post = null;
    }

    if (!$post) {
        http_response_code(404);
        echo "<div class='container my-5 text-center'><h1>404</h1><p>Không tìm thấy bài viết.</p></div>";
        return;
    }

    // Lấy các bình luận
    $comments_stmt = $pdo->prepare("SELECT * FROM blog_comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $comments_stmt->execute([$post['id']]);
    $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

    $comment_message = $_SESSION['comment_message'] ?? null;
    unset($_SESSION['comment_message']);

    // Lấy các bài viết liên quan
    $related_posts = [];
    if (!empty($post['category_id'])) {
        $related_stmt = $pdo->prepare("
            SELECT b.thumbnail, bt.title, bt.slug
            FROM blogs b
            JOIN blog_translations bt ON b.id = bt.blog_id
            WHERE b.category_id = :category_id AND b.id != :current_post_id AND b.status = 'published' AND bt.language_code = :lang
            ORDER BY b.created_at DESC
            LIMIT 3
        ");
        $related_stmt->execute([':category_id' => $post['category_id'], ':current_post_id' => $post['id'], ':lang' => $language_code]);
        $related_posts = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Dữ liệu song ngữ
    $translations = [
        'vi' => ['related_posts_title' => 'Bài viết liên quan', 'comments_title' => 'Bình luận', 'no_comments' => 'Chưa có bình luận nào. Hãy là người đầu tiên!', 'form_title' => 'Để lại một bình luận', 'form_note' => 'Email của bạn sẽ không được hiển thị công khai.', 'name_label' => 'Tên *', 'email_label' => 'Email *', 'comment_label' => 'Bình luận *', 'submit_button' => 'Gửi bình luận'],
        'en' => ['related_posts_title' => 'Related Articles', 'comments_title' => 'Comments', 'no_comments' => 'No comments yet. Be the first!', 'form_title' => 'Leave a Comment', 'form_note' => 'Your email address will not be published.', 'name_label' => 'Name *', 'email_label' => 'Email *', 'comment_label' => 'Comment *', 'submit_button' => 'Post Comment']
    ];
    $lang = $translations[$language_code];

} catch (PDOException $e) {
    die("Lỗi cơ sở dữ liệu: " . $e->getMessage());
}
?>

<div class="breadcrumb-container">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>bai-viet">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($post['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <article class="blog-post-detail">
                <header class="blog-post-header">
                    <?php if($post['category_name']): ?><a href="#" class="blog-post-category"><?= htmlspecialchars($post['category_name']) ?></a><?php endif; ?>
                    <h1 class="blog-post-title"><?= htmlspecialchars($post['title']) ?></h1>
                    <div class="blog-post-meta"><span><i class="fas fa-user-edit me-2"></i><?= htmlspecialchars($post['author_name'] ?? 'FUACO') ?></span><span><i class="fas fa-calendar-alt me-2"></i><?= date('d/m/Y', strtotime($post['created_at'])) ?></span></div>
                </header>
                <?php if($post['thumbnail']): ?><img src="<?= BASE_URL ?>uploads/blogs/<?= htmlspecialchars($post['thumbnail']) ?>" class="blog-post-thumbnail" alt="<?= htmlspecialchars($post['title']) ?>"><?php endif; ?>
                <div class="blog-post-content"><?= $post['content'] ?></div>
            </article>
 <?php if (!empty($related_posts)): ?>

            <div class="related-content-section mt-5 pt-5 border-top">

                <h3 class="text-center mb-4"><?= $lang['related_posts_title'] ?></h3>

                <div class="row">

                    <?php foreach ($related_posts as $related_post): ?>

                        <div class="col-lg-4 col-md-6 mb-4">

                            <div class="blog-card-list">

                                <a href="<?= BASE_URL ?>bai-viet/<?= htmlspecialchars($related_post['slug']) ?>" class="blog-card-image"><img src="<?= BASE_URL ?>uploads/blogs/<?= htmlspecialchars($related_post['thumbnail']) ?>" alt="<?= htmlspecialchars($related_post['title']) ?>"></a>

                                <div class="blog-card-body"><h4 class="blog-card-title"><a href="<?= BASE_URL ?>bai-viet/<?= htmlspecialchars($related_post['slug']) ?>"><?= htmlspecialchars($related_post['title']) ?></a></h4></div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <?php endif; ?>
            <div class="comment-section mt-5 pt-5 border-top" id="comment-section">
                <h3 class="mb-4"><?= count($comments) ?> <?= $lang['comments_title'] ?></h3>
                <?php if ($comment_message): ?><div class="alert alert-<?= htmlspecialchars($comment_message['type']) ?>"><?= htmlspecialchars($comment_message['text']) ?></div><?php endif; ?>
                <?php if (empty($comments)): ?><p><?= $lang['no_comments'] ?></p><?php else: ?><ul class="comment-list"><?php foreach($comments as $comment): ?><li class="comment-item"><div class="comment-author"><?= htmlspecialchars($comment['author_name']) ?></div><div class="comment-date"><?= date('d/m/Y \l\ú\c H:i', strtotime($comment['created_at'])) ?></div><div class="comment-content"><p><?= nl2br(htmlspecialchars($comment['content'])) ?></p></div></li><?php endforeach; ?></ul><?php endif; ?>
                <hr class="my-5">
                <div class="comment-form-wrapper" id="comment-form">
                    <h4><?= $lang['form_title'] ?></h4><p><?= $lang['form_note'] ?></p>
                    <form action="<?= BASE_URL ?>pages/blog_comment_handler.php" method="POST"><input type="hidden" name="post_id" value="<?= $post['id'] ?>"><input type="hidden" name="slug" value="<?= $slug ?>"><div class="mb-3"><label for="commentContent" class="form-label"><?= $lang['comment_label'] ?></label><textarea name="content" id="commentContent" class="form-control" rows="5" required></textarea></div><div class="row"><div class="col-md-6 mb-3"><label for="commentName" class="form-label"><?= $lang['name_label'] ?></label><input type="text" name="name" id="commentName" class="form-control" required></div><div class="col-md-6 mb-3"><label for="commentEmail" class="form-label"><?= $lang['email_label'] ?></label><input type="email" name="email" id="commentEmail" class="form-control" required></div></div><button type="submit" class="btn btn-dark"><?= $lang['submit_button'] ?></button></form>
                </div>
            </div>

           
        </div>
    </div>
</div>