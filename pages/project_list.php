<?php
// Tệp: /pages/project_list.php
global $pdo, $language_code;

// --- DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => [
        'page_title' => 'Dự Án',
        'page_subtitle' => 'Khám phá những không gian đầy cảm hứng được kiến tạo bởi FUACO.',
        'no_projects' => 'Hiện chưa có dự án nào được cập nhật.'
    ],
    'en' => [
        'page_title' => 'Projects',
        'page_subtitle' => 'Discover inspiring spaces crafted by FUACO.',
        'no_projects' => 'No projects have been updated yet.'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$projects_per_page = 9;
$offset = ($page - 1) * $projects_per_page;

// Đếm tổng số dự án
$total_projects_stmt = $pdo->prepare("SELECT COUNT(p.id) FROM projects p JOIN project_translations pt ON p.id = pt.project_id WHERE pt.language_code = ?");
$total_projects_stmt->execute([$language_code]);
$total_projects = $total_projects_stmt->fetchColumn();
$total_pages = ceil($total_projects / $projects_per_page);

// Lấy dự án cho trang hiện tại
$stmt = $pdo->prepare("
    SELECT p.thumbnail, pt.title, pt.slug
    FROM projects p
    JOIN project_translations pt ON p.id = pt.project_id
    WHERE pt.language_code = :lang
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':lang', $language_code, PDO::PARAM_STR);
$stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div class="container">
        <h1><?= $lang['page_title'] ?></h1>
        <p class="lead"><?= $lang['page_subtitle'] ?></p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <?php if (empty($projects)): ?>
            <p><?= $lang['no_projects'] ?></p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="<?= BASE_URL ?>du-an/<?= htmlspecialchars($project['slug']) ?>" class="project-list-card">
                        <div class="project-card-image">
                            <img src="<?= BASE_URL ?>uploads/projects/<?= htmlspecialchars($project['thumbnail']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                        <div class="project-card-overlay">
                            <h4 class="project-card-title"><?= htmlspecialchars($project['title']) ?></h4>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>du-an?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>