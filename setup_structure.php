<?php
$folders = [
    "config",
    "assets/css", "assets/js", "assets/images", "assets/vendor",
    "templates",
    "pages",
    "admin/products", "admin/blogs", "admin/projects", "admin/settings", "admin/appointments",
    "controllers",
    "models",
    "functions",
    "uploads/products", "uploads/projects", "uploads/banners",
    "routes"
];

$files = [
    "index.php",
    ".htaccess",
    "config/database.php", "config/constants.php",
    "templates/head.php", "templates/header.php", "templates/footer.php", "templates/layout.php",
    "pages/home.php", "pages/product_list.php", "pages/product_detail.php", "pages/project_gallery.php",
    "pages/blog.php", "pages/contact.php", "pages/appointment_form.php",
    "admin/index.php", "admin/login.php", "admin/logout.php",
    "controllers/ProductController.php", "controllers/BlogController.php",
    "controllers/AppointmentController.php", "controllers/AdminController.php",
    "models/Product.php", "models/Category.php", "models/Appointment.php", "models/SiteSetting.php",
    "functions/helpers.php", "functions/auth.php", "functions/validators.php",
    "routes/web.php"
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) mkdir($folder, 0777, true);
}

foreach ($files as $file) {
    if (!file_exists($file)) file_put_contents($file, "");
}

echo "✅ Cấu trúc dự án đã tạo thành công!";
