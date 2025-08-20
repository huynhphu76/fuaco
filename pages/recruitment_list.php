<?php
// Tệp: /pages/recruitment_list.php
global $pdo, $language_code;

// --- DỮ LIỆU SONG NGỮ ---
$translations = [
    'vi' => ['page_title' => 'Tuyển dụng', 'page_subtitle' => 'Gia nhập đội ngũ FUACO để cùng kiến tạo những không gian sống đẳng cấp.', 'view_detail' => 'Xem chi tiết'],
    'en' => ['page_title' => 'Careers', 'page_subtitle' => 'Join the FUACO team to create classy living spaces together.', 'view_detail' => 'View Details']
];
$lang = $translations[$language_code];

// Lấy các vị trí đang tuyển
$stmt = $pdo->prepare("
    SELECT j.location, j.salary, jt.title, jt.slug
    FROM jobs j
    JOIN job_translations jt ON j.id = jt.job_id
    WHERE j.is_active = 1 AND jt.language_code = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$language_code]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div class="container">
        <h1><?= $lang['page_title'] ?></h1>
        <p class="lead"><?= $lang['page_subtitle'] ?></p>
    </div>
</div>

<div class="container my-5">
    <div class="recruitment-list">
        <?php foreach ($jobs as $job): ?>
            <a href="<?= BASE_URL ?>tuyen-dung/<?= htmlspecialchars($job['slug']) ?>" class="job-item">
                <div>
                    <h4 class="job-item-title"><?= htmlspecialchars($job['title']) ?></h4>
                    <div class="job-item-meta">
                        <span><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($job['location']) ?></span>
                        <span><i class="fas fa-dollar-sign me-2"></i><?= htmlspecialchars($job['salary']) ?></span>
                    </div>
                </div>
                <span class="btn btn-outline-dark btn-sm"><?= $lang['view_detail'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>