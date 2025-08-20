<?php
// Tệp: /pages/contact.php
global $pdo, $language_code;

// --- DỮ LIỆU SONG NGỮ CHO TRANG ---
$translations = [
    'vi' => [
        'page_title' => 'Liên Hệ',
        'page_subtitle' => 'Chúng tôi luôn sẵn sàng lắng nghe bạn. Hãy kết nối với FUACO.',
        'info_title' => 'Thông tin liên hệ',
        'address' => 'Địa chỉ',
        'hotline' => 'Hotline',
        'email' => 'Email',
        'form_title' => 'Gửi tin nhắn cho chúng tôi',
        'name_label' => 'Họ tên *',
        'email_label' => 'Email *',
        'subject_label' => 'Chủ đề *',
        'message_label' => 'Nội dung tin nhắn *',
        'submit_button' => 'Gửi tin nhắn'
    ],
    'en' => [
        'page_title' => 'Contact Us',
        'page_subtitle' => 'We are always ready to hear from you. Get in touch with FUACO.',
        'info_title' => 'Contact Information',
        'address' => 'Address',
        'hotline' => 'Hotline',
        'email' => 'Email',
        'form_title' => 'Send us a message',
        'name_label' => 'Name *',
        'email_label' => 'Email *',
        'subject_label' => 'Subject *',
        'message_label' => 'Message *',
        'submit_button' => 'Send Message'
    ]
];
$lang = $translations[$language_code];
// --- KẾT THÚC ---


// Lấy thông tin liên hệ từ CSDL
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$settings_trans_stmt = $pdo->prepare("SELECT setting_key, setting_value FROM setting_translations WHERE language_code = ?");
$settings_trans_stmt->execute([$language_code]);
$settings_trans = $settings_trans_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Lấy và xóa thông báo từ session
$contact_message = $_SESSION['contact_form_message'] ?? null;
unset($_SESSION['contact_form_message']);
?>

<div class="page-header">
    <div class="container">
        <h1><?= $lang['page_title'] ?></h1>
        <p class="lead"><?= $lang['page_subtitle'] ?></p>
    </div>
</div>

<div class="container my-5">
    <div class="row g-5">
        <div class="col-lg-5 contact-page-section">
            <h3 class="mb-4"><?= $lang['info_title'] ?></h3>
            <div class="contact-info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <strong><?= $lang['address'] ?></strong>
                    <p><?= htmlspecialchars($settings_trans['address'] ?? '') ?></p>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="fas fa-phone-alt"></i>
                <div>
                    <strong><?= $lang['hotline'] ?></strong>
                    <p><a href="tel:<?= htmlspecialchars($settings['hotline'] ?? '') ?>"><?= htmlspecialchars($settings['hotline'] ?? '') ?></a></p>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong><?= $lang['email'] ?></strong>
                    <p><a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? '') ?>"><?= htmlspecialchars($settings['contact_email'] ?? '') ?></a></p>
                </div>
            </div>
        </div>
        <div class="col-lg-7 contact-page-section">
            <h3 class="mb-4"><?= $lang['form_title'] ?></h3>
            <div class="contact-form-wrapper">
                <?php if ($contact_message): ?>
                    <div class="alert alert-<?= htmlspecialchars($contact_message['type']) ?>">
                        <?= htmlspecialchars($contact_message['text']) ?>
                    </div>
                <?php endif; ?>
                <form action="<?= BASE_URL ?>pages/contact_handler.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label"><?= $lang['name_label'] ?></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><?= $lang['email_label'] ?></label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label"><?= $lang['subject_label'] ?></label>
                        <input type="text" name="subject" id="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label"><?= $lang['message_label'] ?></label>
                        <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark"><?= $lang['submit_button'] ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="map-container">
  <iframe src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d15400.719050650272!2d108.78293424915584!3d15.20332572253774!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1zTMO0IEM1IDMsIEtDTiwgU8ahbiBU4buLbmgsIFF14bqjbmcgTmfDo2k!5e0!3m2!1svi!2s!4v1754108464313!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>