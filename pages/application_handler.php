<?php
// Tệp: /pages/application_handler.php (Phiên bản cuối cùng, đã sửa lỗi)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

// Dữ liệu song ngữ cho thông báo
$language_code = $_SESSION['lang'] ?? 'vi';
$translations = [
    'vi' => [
        'success' => 'Nộp hồ sơ thành công! Cảm ơn bạn đã quan tâm.',
        'error_upload' => 'Lỗi tải lên tệp CV. Vui lòng thử lại.',
        'error_db' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau.',
        'missing_fields' => 'Vui lòng điền đầy đủ các trường bắt buộc.',
        'job_not_found' => 'Vị trí tuyển dụng không còn tồn tại.'
    ],
    'en' => [
        'success' => 'Application submitted successfully! Thank you for your interest.',
        'error_upload' => 'Error uploading CV file. Please try again.',
        'error_db' => 'An error occurred. Please try again later.',
        'missing_fields' => 'Please fill in all required fields.',
        'job_not_found' => 'The job position no longer exists.'
    ]
];
$lang = $translations[$language_code];

// Lấy và làm sạch dữ liệu
$job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
$slug = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING);
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$cover_letter = trim(filter_input(INPUT_POST, 'cover_letter', FILTER_SANITIZE_STRING));

$redirect_url = BASE_URL . 'tuyen-dung/' . $slug . '#application-form';

// Kiểm tra các trường bắt buộc
if (empty($job_id) || empty($slug) || empty($name) || empty($email) || empty($phone)) {
    $_SESSION['application_message'] = ['type' => 'danger', 'text' => $lang['missing_fields']];
    header('Location: ' . $redirect_url);
    exit;
}

// --- BƯỚC KIỂM TRA QUAN TRỌNG ĐÃ BỊ THIẾU ---
// Kiểm tra xem job_id có thực sự tồn tại trong bảng jobs hay không
$check_job_stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND is_active = 1");
$check_job_stmt->execute([$job_id]);
if ($check_job_stmt->fetch() === false) {
    $_SESSION['application_message'] = ['type' => 'danger', 'text' => $lang['job_not_found']];
    header('Location: ' . BASE_URL . 'tuyen-dung'); // Chuyển về trang danh sách nếu tin không còn
    exit;
}
// --- KẾT THÚC BƯỚC KIỂM TRA ---


// Xử lý upload file CV
$cv_path = '';
if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/cvs/';
    if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); }
    
    $file_extension = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('cv_', true) . '.' . $file_extension;
    
    if (move_uploaded_file($_FILES['cv']['tmp_name'], $upload_dir . $filename)) {
        $cv_path = 'uploads/cvs/' . $filename;
    }
}

if (empty($cv_path)) {
    $_SESSION['application_message'] = ['type' => 'danger', 'text' => $lang['error_upload']];
    header('Location: ' . $redirect_url);
    exit;
}

// Chèn dữ liệu vào CSDL
try {
    $stmt = $pdo->prepare(
        "INSERT INTO job_applications (job_id, applicant_name, applicant_email, applicant_phone, cv_path, cover_letter) 
         VALUES (:job_id, :name, :email, :phone, :cv_path, :cover_letter)"
    );
    
    $stmt->execute([
        ':job_id' => $job_id,
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':cv_path' => $cv_path,
        ':cover_letter' => $cover_letter ?: null
    ]);
    
    $_SESSION['application_message'] = ['type' => 'success', 'text' => $lang['success']];

} catch (PDOException $e) {
    error_log('Application submission error: ' . $e->getMessage());
    $_SESSION['application_message'] = ['type' => 'danger', 'text' => $lang['error_db']];
}

header('Location: ' . $redirect_url);
exit;