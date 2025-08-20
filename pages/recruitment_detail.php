<?php
// Tệp: /pages/recruitment_detail.php (Phiên bản đã sửa lỗi)
global $pdo, $language_code, $params;

$slug = $params['slug'] ?? null;
if (!$slug) { die("Không tìm thấy trang."); }

try {
    // BƯỚC 1: Dùng slug để tìm ra ID của tin tuyển dụng, bất kể ngôn ngữ
    $id_stmt = $pdo->prepare("SELECT job_id FROM job_translations WHERE slug = ?");
    $id_stmt->execute([$slug]);
    $job_id = $id_stmt->fetchColumn();

    $job = null;
    // BƯỚC 2: Nếu tìm thấy ID, dùng ID đó để lấy đúng bản dịch theo ngôn ngữ đang chọn
    if ($job_id) {
        $stmt = $pdo->prepare("
            SELECT j.id as job_main_id, j.location, j.salary, j.department, jt.* FROM jobs j 
            JOIN job_translations jt ON j.id = jt.job_id 
            WHERE j.id = :job_id AND jt.language_code = :lang AND j.is_active = 1
        ");
        $stmt->execute([':job_id' => $job_id, ':lang' => $language_code]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        // BƯỚC 3 (DỰ PHÒNG): Nếu không có bản dịch cho ngôn ngữ hiện tại, tự động lấy tiếng Việt
        if (!$job) {
            $stmt_vi = $pdo->prepare("
                SELECT j.id as job_main_id, j.location, j.salary, j.department, jt.* FROM jobs j 
                JOIN job_translations jt ON j.id = jt.job_id 
                WHERE j.id = :job_id AND jt.language_code = 'vi' AND j.is_active = 1
            ");
            $stmt_vi->execute([':job_id' => $job_id]);
            $job = $stmt_vi->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$job) { 
        http_response_code(404); 
        echo "<div class='container my-5 text-center'><h1>404</h1><p>Không tìm thấy tin tuyển dụng.</p></div>"; 
        return; 
    }

    $application_message = $_SESSION['application_message'] ?? null;
    unset($_SESSION['application_message']);

    // Dữ liệu song ngữ
    $lang = [
        'vi' => ['desc' => 'Mô tả công việc', 'req' => 'Yêu cầu ứng viên', 'bene' => 'Quyền lợi', 'department' => 'Phòng ban', 'location' => 'Địa điểm', 'salary' => 'Mức lương', 'apply_title' => 'Ứng tuyển vị trí này', 'name' => 'Họ và tên *', 'email' => 'Email *', 'phone' => 'Số điện thoại *', 'cv' => 'Tải lên CV của bạn (PDF, DOC, DOCX) *', 'cover_letter' => 'Thư giới thiệu (Tùy chọn)', 'submit' => 'Nộp hồ sơ'],
        'en' => ['desc' => 'Job Description', 'req' => 'Requirements', 'bene' => 'Benefits', 'department' => 'Department', 'location' => 'Location', 'salary' => 'Salary', 'apply_title' => 'Apply for this position', 'name' => 'Full Name *', 'email' => 'Email *', 'phone' => 'Phone *', 'cv' => 'Upload your CV (PDF, DOC, DOCX) *', 'cover_letter' => 'Cover Letter (Optional)', 'submit' => 'Submit Application']
    ][$language_code];

} catch (PDOException $e) {
    die("Lỗi cơ sở dữ liệu: " . $e->getMessage());
}
?>

<div class="page-header"><div class="container"><h1><?= htmlspecialchars($job['title']) ?></h1></div></div>

<div class="job-meta-details container mt-4 mb-5">
    <div class="row">
        <div class="col-md-4"><div class="meta-item"><i class="fas fa-building"></i><strong><?= $lang['department'] ?>:</strong><span><?= htmlspecialchars($job['department'] ?: 'N/A') ?></span></div></div>
        <div class="col-md-4"><div class="meta-item"><i class="fas fa-map-marker-alt"></i><strong><?= $lang['location'] ?>:</strong><span><?= htmlspecialchars($job['location'] ?: 'N/A') ?></span></div></div>
        <div class="col-md-4"><div class="meta-item"><i class="fas fa-dollar-sign"></i><strong><?= $lang['salary'] ?>:</strong><span><?= htmlspecialchars($job['salary'] ?: 'N/A') ?></span></div></div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 content-section">
            <div class="mb-5"><h3><?= $lang['desc'] ?></h3><?= $job['description'] ?></div>
            <div class="mb-5"><h3><?= $lang['req'] ?></h3><?= $job['requirements'] ?></div>
            <div class="mb-5"><h3><?= $lang['bene'] ?></h3><?= $job['benefits'] ?></div>
        </div>
        <div class="col-lg-4">
            <div class="application-form-wrapper" id="application-form">
                <h4><?= $lang['apply_title'] ?></h4>
                <?php if ($application_message): ?>
                    <div class="alert alert-<?= $application_message['type'] ?> mt-3"><?= $application_message['text'] ?></div>
                <?php endif; ?>
                <form action="<?= BASE_URL ?>pages/application_handler.php" method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                    <input type="hidden" name="slug" value="<?= $slug ?>">
                    <div class="mb-3"><label class="form-label"><?= $lang['name'] ?></label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label"><?= $lang['email'] ?></label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label"><?= $lang['phone'] ?></label><input type="tel" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label"><?= $lang['cv'] ?></label><input type="file" name="cv" class="form-control" required accept=".pdf,.doc,.docx"></div>
                    <div class="mb-3"><label class="form-label"><?= $lang['cover_letter'] ?></label><textarea name="cover_letter" class="form-control" rows="4"></textarea></div>
                    <button type="submit" class="btn btn-primary w-100"><?= $lang['submit'] ?></button>
                </form>
            </div>
        </div>
    </div>
</div>