<?php
// Tệp: pages/auth_handler.php (Đã thêm song ngữ)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$language_code = $_SESSION['lang'] ?? 'vi';
$translations = [
    'vi' => [
        'error_invalid_input' => 'Vui lòng điền đầy đủ và chính xác thông tin.',
        'error_email_exists' => 'Email này đã được sử dụng.',
        'error_login_failed' => 'Email hoặc mật khẩu không chính xác.',
        'error_generic' => 'Đã có lỗi xảy ra. Vui lòng thử lại.'
    ],
    'en' => [
        'error_invalid_input' => 'Please fill in all fields correctly.',
        'error_email_exists' => 'This email is already in use.',
        'error_login_failed' => 'Incorrect email or password.',
        'error_generic' => 'An error occurred. Please try again.'
    ]
];
$lang = $translations[$language_code];

$action = $_POST['action'] ?? 'login';

try {
    switch ($action) {
        case 'register':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
                $_SESSION['error_message'] = $lang['error_invalid_input'];
                header('Location: ' . BASE_URL . 'dang-ky');
                exit;
            }

            $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = $lang['error_email_exists'];
                header('Location: ' . BASE_URL . 'dang-ky');
                exit;
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $passwordHash]);
            $customer_id = $pdo->lastInsertId();

            $_SESSION['customer'] = ['id' => $customer_id, 'name' => $name, 'email' => $email];
            header('Location: ' . BASE_URL . 'tai-khoan');
            exit;

        case 'login':
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch();

            if ($customer && password_verify($password, $customer['password'])) {
                $_SESSION['customer'] = ['id' => $customer['id'], 'name' => $customer['name'], 'email' => $customer['email']];
                header('Location: ' . BASE_URL . 'tai-khoan');
                exit;
            } else {
                $_SESSION['error_message'] = $lang['error_login_failed'];
                header('Location: ' . BASE_URL . 'dang-nhap');
                exit;
            }
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = $lang['error_generic'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    exit;
}
?>