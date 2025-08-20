-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 08:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `interior_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `user_id`, `action`, `timestamp`) VALUES
(969, 5, 'Xóa tài khoản #12: \'admin@fu1aaco.com\'', '2025-08-15 07:10:22'),
(970, 5, 'Xóa tài khoản #11: \'huynhphu@gmail.com\'', '2025-08-15 07:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `name`, `phone`, `email`, `date_time`, `note`, `status`, `created_at`) VALUES
(9, 'sdsdsdđeereewr', '3232334234234234', 'huynhphu06143@gmail.com', '2025-07-10 15:28:00', 'hẹn gặp', 'confirmed', '2025-07-31 08:25:34'),
(10, 'huỳnh phú', '3232334234234234', 'admin@fuaco.com', '2025-08-27 20:43:00', 'hẹn gặp', 'confirmed', '2025-08-01 09:38:34'),
(11, 'bá hiếu', '3232334234234234', 'admin@fuaco.com', '2025-08-02 17:12:00', 'á', 'pending', '2025-08-01 10:11:19'),
(12, 'phú', '12121', 'admin@gmail.com', '2025-08-05 16:27:00', 'âsas', 'pending', '2025-08-05 09:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `xdescription` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'published',
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `xdescription`, `thumbnail`, `status`, `user_id`, `category_id`, `image`, `created_at`) VALUES
(32, NULL, 'blog_6899ae53d1a269.04420102.jpg', 'published', NULL, 13, NULL, '2025-08-08 08:25:04'),
(33, NULL, 'blog_6899ae6e98a687.60873655.jpg', 'published', NULL, NULL, NULL, '2025-08-11 08:48:46'),
(34, NULL, 'blog_6899ae7b9e3339.81604498.jpg', 'published', NULL, 13, NULL, '2025-08-11 08:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`) VALUES
(13);

-- --------------------------------------------------------

--
-- Table structure for table `blog_category_translations`
--

CREATE TABLE `blog_category_translations` (
  `id` int(11) NOT NULL,
  `blog_category_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_category_translations`
--

INSERT INTO `blog_category_translations` (`id`, `blog_category_id`, `language_code`, `name`, `slug`, `description`) VALUES
(9, 13, 'vi', 'Xu Hướng Thiết Kế', 'xu-huong-thiet-ke', ''),
(10, 13, 'en', 'Design Trends', 'design-trends', '');

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `author_name` varchar(150) NOT NULL,
  `author_email` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_products`
--

CREATE TABLE `blog_post_products` (
  `post_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_projects`
--

CREATE TABLE `blog_post_projects` (
  `post_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_tags`
--

CREATE TABLE `blog_post_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_tags`
--

INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES
(1, 'fgfmgng', 'fgfmgng');

-- --------------------------------------------------------

--
-- Table structure for table `blog_translations`
--

CREATE TABLE `blog_translations` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_translations`
--

INSERT INTO `blog_translations` (`id`, `blog_id`, `language_code`, `title`, `slug`, `content`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(26, 32, 'vi', '5 Xu Hướng Thiết Kế Nội Thất Bền Vững Sẽ Lên Ngôi', '5-xu-h-ng-thi-t-k-n-i-th-t-b-n-v-ng-s-l-n-ng-i', '<p>5 Xu Hướng Thiết Kế Nội Thất Bền Vững Sẽ L&ecirc;n Ng&ocirc;i<br><img src=\"http://localhost/interior-website/uploads/blogs/content/blog_content_6899ae64aa67a8.47397427.png\"></p>', '', '', ''),
(27, 32, 'en', '5 Sustainable Interior Design Trends Set to Dominate', '5-sustainable-interior-design-trends-set-to-dominate', '<p>&lt;p&gt;In a world increasingly focused on the environment, sustainable interiors have become a core design philosophy. The coming year will witness an explosion of creative ideas that make our homes not only more beautiful but also greener.&lt;/p&gt;</p>', '', '', ''),
(34, 33, 'vi', '5 Xu Hướng Thiết Kế Nội Thất Bền Vững Sẽ Lên Ngôi', '5-xu-h-ng-thi-t-k-n-i-th-t-b-n-v-ng-s-l-n-ng-i', '', '', '', ''),
(35, 34, 'vi', '5 Xu Hướng Thiết Kế Nội Thất Bền Vững Sẽ Lên Ngôi1212137777', '5-xu-h-ng-thi-t-k-n-i-th-t-b-n-v-ng-s-l-n-ng-i1212137777', '<p>32323123123yghgfhfgh</p>', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `created_at`) VALUES
(21, '2025-08-01 06:15:02'),
(23, '2025-08-08 08:17:27');

-- --------------------------------------------------------

--
-- Table structure for table `category_translations`
--

CREATE TABLE `category_translations` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category_translations`
--

INSERT INTO `category_translations` (`id`, `category_id`, `language_code`, `name`, `slug`, `description`) VALUES
(32, 21, 'vi', 'SOFA', 'sofa', 'Các mẫu sofa và ghế bành sang trọng, mang lại sự thoải mái và đẳng cấp cho phòng khách.'),
(33, 21, 'en', 'Sofa', 'sofa', 'Luxurious sofas and armchairs that bring comfort and class to the living room.'),
(36, 23, 'vi', 'Dining', 'dining', '');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `password`, `reset_token`, `reset_token_expires_at`, `created_at`) VALUES
(1, 'huỳnh phú', 'admin@gmail.com', '$2y$10$Zlr4A8fSYdp/pbd3gG00oeigODS4bVluxf52OczIochK7KQiwP2w.', NULL, NULL, '2025-08-13 02:49:31'),
(2, 'huỳnh phú', 'huynhphu06143@gmail.com', '$2y$10$TWHlDy6S09g9xBdsxzYTKeOg3vHaR0s8izsVJQH7w.rxIhEX.yJiy', NULL, NULL, '2025-08-13 03:12:06');

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'Số sao đánh giá từ 1 đến 5',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `name`, `message`, `rating`, `created_at`) VALUES
(10, '4444', '44444', 5, '2025-07-31 06:35:11'),
(11, '5555', '5555', 5, '2025-07-31 06:35:16'),
(12, '6666', '6666', 5, '2025-07-31 06:35:21'),
(13, '77777', '7777', 5, '2025-07-31 06:35:28'),
(14, 'sâsas', 'ấ', 5, '2025-07-31 07:36:52');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `department` varchar(255) DEFAULT NULL COMMENT 'Phòng ban',
  `location` varchar(255) DEFAULT NULL COMMENT 'Địa điểm làm việc',
  `salary` varchar(255) DEFAULT NULL COMMENT 'Mức lương',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Đang tuyển, 0 = Ngừng tuyển',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `department`, `location`, `salary`, `is_active`, `created_at`, `updated_at`) VALUES
(10, '111', '1111', '111', 1, '2025-08-06 01:33:32', '2025-08-06 01:33:32');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `applicant_email` varchar(255) NOT NULL,
  `applicant_phone` varchar(50) NOT NULL,
  `cv_path` varchar(255) NOT NULL COMMENT 'Đường dẫn tới file CV',
  `cover_letter` text DEFAULT NULL COMMENT 'Thư giới thiệu',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `applicant_name`, `applicant_email`, `applicant_phone`, `cv_path`, `cover_letter`, `submitted_at`) VALUES
(9, 10, 'âs', 'admin@gmail.com', '3232334234234234', 'uploads/cvs/cv_689968fe1853d0.02502078.pdf', NULL, '2025-08-11 03:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `job_translations`
--

CREATE TABLE `job_translations` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Chức danh',
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Mô tả công việc',
  `requirements` text DEFAULT NULL COMMENT 'Yêu cầu ứng viên',
  `benefits` text DEFAULT NULL COMMENT 'Quyền lợi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_translations`
--

INSERT INTO `job_translations` (`id`, `job_id`, `language_code`, `title`, `slug`, `description`, `requirements`, `benefits`) VALUES
(20, 10, 'vi', '111', '111', '<p>11111</p>', '<p>11111</p>', '<p>11111</p>'),
(21, 10, 'en', '222', '222', '<p>22222</p>', '<p>22222</p>', '<p>2222</p>');

-- --------------------------------------------------------

--
-- Table structure for table `media_library`
--

CREATE TABLE `media_library` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL COMMENT 'Kích thước file tính bằng bytes',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_library`
--

INSERT INTO `media_library` (`id`, `file_name`, `file_type`, `file_size`, `uploaded_at`) VALUES
(9, 'media_6889b73a5ef9a0.24458802_ChatGPT Image 14_32_26 23 thg 7, 2025.png', 'image/png', 2251219, '2025-07-30 06:10:02');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `location` varchar(50) NOT NULL COMMENT 'Vị trí hiển thị trên giao diện'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `location`) VALUES
(5, 'footer_links'),
(8, 'footer_menu'),
(6, 'main_nav');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `url`, `parent_id`, `display_order`) VALUES
(10, 5, '/chinh-sach-bao-mat', 0, 0),
(12, 6, '/', 0, 0),
(13, 6, '/ve-chung-toi', 0, 0),
(14, 6, '/san-pham', 0, 0),
(15, 6, '/du-an', 0, 0),
(16, 6, '/bai-viet', 0, 0),
(17, 6, '/tuyen-dung', 0, 0),
(18, 6, '/lien-he ', 0, 0),
(19, 8, '/ve-chung-toi', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_translations`
--

CREATE TABLE `menu_item_translations` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_item_translations`
--

INSERT INTO `menu_item_translations` (`id`, `menu_item_id`, `language_code`, `title`) VALUES
(111, 10, 'vi', 'chính sách bảo mật'),
(112, 10, 'en', 'chính sách bảo mật'),
(175, 12, 'vi', 'Trang Chủ'),
(176, 12, 'en', 'Home'),
(179, 13, 'vi', 'Về Chúng Tôi	'),
(180, 13, 'en', 'About Us'),
(185, 14, 'vi', 'Sản Phẩm	'),
(186, 14, 'en', 'Products	'),
(193, 15, 'vi', 'Dự Án	'),
(194, 15, 'en', 'Projects	'),
(203, 16, 'vi', 'Bài Viết	'),
(204, 16, 'en', 'Blog	'),
(215, 17, 'vi', 'Tuyển Dụng	'),
(216, 17, 'en', 'Careers	'),
(229, 18, 'vi', 'Liên Hệ'),
(230, 18, 'en', 'Contact	'),
(245, 19, 'vi', 'Danh mục');

-- --------------------------------------------------------

--
-- Table structure for table `menu_translations`
--

CREATE TABLE `menu_translations` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_translations`
--

INSERT INTO `menu_translations` (`id`, `menu_id`, `language_code`, `name`) VALUES
(1, 5, 'vi', 'âsas'),
(2, 5, 'en', 'âsass'),
(16, 6, 'vi', 'menu nav'),
(17, 6, 'en', 'sdasdsad'),
(20, 8, 'vi', 'giữa trang');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cod' COMMENT 'Phương thức thanh toán',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `customer_name`, `phone`, `email`, `address`, `status`, `total_price`, `payment_method`, `created_at`, `updated_at`) VALUES
(38, 2, 'huỳnh phú', '3232334234234234', 'huynhphu06143@gmail.com', 'ưeedfdf', 'cancelled', 1000.00, 'cod', '2025-08-13 11:30:08', '2025-08-13 11:34:10'),
(39, 1, 'huỳnh phú', '3232334234234234', 'admin@gmail.com', '23434', 'delivered', 6000.00, 'cod', '2025-08-14 09:23:30', '2025-08-14 11:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(9, 1, 2, 2, 99999999.99),
(10, 16, 57, 3, 10000000.00),
(11, 17, 60, 9, 111.00),
(12, 17, 57, 8, 10000000.00),
(13, 18, 56, 15, 100000.00),
(14, 18, 60, 1, 111.00),
(15, 19, 60, 4, 111.00),
(16, 19, 56, 2, 100000.00),
(17, 20, 60, 2, 111.00),
(18, 20, 65, 2, 2323.00),
(19, 21, 65, 2, 2323.00),
(20, 23, 56, 4, 100000.00),
(21, 24, 65, 2, 2323.00),
(22, 24, 60, 11, 111.00),
(23, 25, 60, 1, 111.00),
(24, 27, 73, 19, 1000.00),
(25, 29, 72, 1, 1000.00),
(26, 31, 72, 1, 1000.00),
(27, 33, 73, 1, 1000.00),
(28, 35, 73, 1, 1000.00),
(29, 37, 72, 1, 1000.00),
(30, 38, 73, 1, 1000.00),
(31, 39, 71, 4, 0.00),
(32, 39, 73, 5, 1000.00),
(33, 39, 72, 1, 1000.00),
(34, 39, 70, 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `is_published`, `created_at`, `updated_at`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(8, '/ve-chung-toi', 1, '2025-07-31 02:07:35', '2025-08-02 04:34:22', NULL, NULL, NULL),
(10, '/chinh-sach-bao-mat', 1, '2025-08-02 10:07:48', '2025-08-05 09:12:03', NULL, NULL, NULL),
(12, '/rkher-rj', 1, '2025-08-14 10:06:05', '2025-08-15 01:16:46', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_translations`
--

CREATE TABLE `page_translations` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_translations`
--

INSERT INTO `page_translations` (`id`, `page_id`, `language_code`, `title`, `content`) VALUES
(15, 8, 'vi', 'sdsdsd', '<p><img src=\"http://localhost/interior-website/uploads/pages/page_img_689d683a292085.58711806.jpg\"></p>'),
(25, 8, 'en', 'addsd', '<p><img src=\"http://localhost/interior-website/uploads/pages/page_img_688db685b22d83.36644652.jpg\"></p>'),
(27, 10, 'vi', 'sdsdasd', '<p>sdasdad</p>'),
(29, 10, 'en', 'âsasas', '<p>&acirc;sasasas</p>'),
(37, 12, 'vi', 'Biệt Thự Ven Biển The Ocean Villa023092383723234234', '<p>12121212ggggggg3232334355555555ghfghfưewewewef1212333333344467889999155656<img src=\"http://localhost/interior-website/uploads/pages/page_img_689db507eb35d2.26370291.jpg\" alt=\"page_img_689db507eb35d2.26370291.jpg\" />ffffff<br /><img src=\"http://localhost/interior-website/uploads/pages/page_img_689e8aa2dc3fa5.91444650.jpg\" alt=\"page_img_689e8aa2dc3fa5.91444650.jpg\" /></p>');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Ví dụ: view-products, edit-users',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(33, 'view-dashboard', 'Xem trang tổng quan'),
(34, 'view-products', 'Xem danh sách sản phẩm'),
(35, 'create-products', 'Thêm sản phẩm mới'),
(36, 'edit-products', 'Sửa sản phẩm'),
(37, 'delete-products', 'Xóa sản phẩm'),
(38, 'view-orders', 'Xem danh sách đơn hàng'),
(39, 'edit-orders', 'Sửa/Cập nhật đơn hàng'),
(40, 'delete-orders', 'Xóa đơn hàng'),
(41, 'manage-categories', 'Quản lý danh mục (Thêm, sửa, xóa)'),
(42, 'manage-projects', 'Quản lý dự án (Thêm, sửa, xóa)'),
(43, 'manage-blogs', 'Quản lý bài viết (Thêm, sửa, xóa)'),
(44, 'manage-appointments', 'Quản lý lịch hẹn'),
(45, 'manage-feedbacks', 'Quản lý phản hồi'),
(46, 'view-users', 'Xem danh sách người dùng'),
(47, 'create-users', 'Thêm người dùng mới'),
(48, 'edit-users', 'Sửa thông tin người dùng'),
(49, 'delete-users', 'Xóa người dùng'),
(50, 'manage-roles', 'Quản lý vai trò và quyền hạn'),
(51, 'manage-settings', 'Quản lý cài đặt website'),
(52, 'view-logs', 'Xem nhật ký hệ thống'),
(53, 'manage-sliders', 'Quản lý Trình chiếu (Sliders)'),
(56, 'manage-pages', 'Quản lý Trang tĩnh (Pages)'),
(57, 'manage-menus', 'Quản lý Menu Giao diện'),
(58, 'manage-seo', 'Quản lý SEO'),
(60, 'manage-theme', 'Quản lý Tùy biến Giao diện (logo, banner, footer...)'),
(61, 'manage-product-reviews', 'Quản lý đánh giá sản phẩm (xem, duyệt, xóa)'),
(62, 'manage-recruitment', 'Quản lý Tuyển dụng (Đăng tin, xem ứng viên)');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `main_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `price`, `quantity`, `main_image`, `status`, `created_at`, `updated_at`) VALUES
(70, 21, 0.00, 1000, 'product_6899ae46f038a7.77255217.jpg', 'active', '2025-08-08 08:19:55', '2025-08-11 08:48:06'),
(71, 21, 0.00, 100, 'product_6899ae3ddfccf8.04977921.jpg', 'active', '2025-08-08 08:20:57', '2025-08-11 08:47:57'),
(72, 21, 1000.00, 1000, 'product_6899ae372f4035.88182060.jpg', 'active', '2025-08-08 08:21:27', '2025-08-11 08:47:51'),
(73, 21, 1000.00, 100, 'product_6899ae29df8837.74752597.jpg', 'active', '2025-08-08 08:22:01', '2025-08-11 08:47:37'),
(79, 23, 1111.00, 11, 'product_689e9aca808912.60729681.jpg', 'active', '2025-08-15 02:26:18', '2025-08-15 02:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `attribute_name` varchar(150) NOT NULL COMMENT 'Tên thuộc tính, ví dụ: Chất liệu',
  `attribute_value` varchar(255) NOT NULL COMMENT 'Giá trị thuộc tính, ví dụ: Gỗ Sồi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_attributes`
--

INSERT INTO `product_attributes` (`id`, `product_id`, `attribute_name`, `attribute_value`) VALUES
(58, 70, 'Chất liệu', 'Vải lanh Bỉ, Khung gỗ sồi'),
(59, 70, 'Kích thước', '240cm x 100cm x 85cm');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `alt_text` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `alt_text`) VALUES
(16, 73, 'uploads/products/prod_img_689db1f26bccb3.88762826.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `author_name` varchar(150) NOT NULL,
  `author_email` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'Đánh giá từ 1 đến 5 sao',
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `author_name`, `author_email`, `content`, `rating`, `status`, `created_at`) VALUES
(5, 70, 'huỳnh phú', 'admin@gmail.com', 'sản phẩm rất tốt', 5, 'approved', '2025-08-14 04:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `product_translations`
--

CREATE TABLE `product_translations` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_translations`
--

INSERT INTO `product_translations` (`id`, `product_id`, `language_code`, `name`, `slug`, `description`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(86, 70, 'vi', 'Sofa Vải Lanh \"Serenity\"', 'sofa-v-i-lanh-serenity-', '<p>Trở thành tâm điểm của sự tinh tế, ghế sofa \"Serenity\" là định nghĩa mới về sự thoải mái và phong cách. Được thiết kế với những đường nét mềm mại, tối giản, đây là nơi thư giãn lý tưởng sau một ngày dài.</p>\r\n<ul>\r\n    <li><strong>Thiết kế Module linh hoạt:</strong> Dễ dàng tùy chỉnh để phù hợp với mọi không gian.</li>\r\n    <li><strong>Đệm Lông Vũ cao cấp:</strong> Mang lại sự êm ái và khả năng giữ phom dáng hoàn hảo.</li>\r\n</ul>', '', '', ''),
(87, 70, 'en', ' The \"Serenity\" Linen Sofa', '-the-serenity-linen-sofa', '<p>The centerpiece of sophistication, the \"Serenity\" sofa redefines comfort and style. Designed with soft, minimalist lines, it is the ideal sanctuary after a long day.</p>\r\n<ul>\r\n    <li><strong>Flexible Modular Design:</strong> Easily customize to fit any living space.</li>\r\n    <li><strong>Premium Feather-Down Cushions:</strong> Offers exceptional softness and perfect shape retention.</li>\r\n</ul>', '', '', ''),
(88, 71, 'vi', 'Sofa Vải Lanh \"Serenity\"', 'sofa-v-i-lanh-serenity-', '<p>Trở thành tâm điểm của sự tinh tế, ghế sofa \"Serenity\" là định nghĩa mới về sự thoải mái và phong cách. Được thiết kế với những đường nét mềm mại, tối giản, đây là nơi thư giãn lý tưởng sau một ngày dài.</p>\r\n<ul>\r\n    <li><strong>Thiết kế Module linh hoạt:</strong> Dễ dàng tùy chỉnh để phù hợp với mọi không gian.</li>\r\n    <li><strong>Đệm Lông Vũ cao cấp:</strong> Mang lại sự êm ái và khả năng giữ phom dáng hoàn hảo.</li>\r\n</ul>', '', '', ''),
(89, 71, 'en', 'The \"Serenity\" Linen Sofa', 'the-serenity-linen-sofa', '<p>The centerpiece of sophistication, the \"Serenity\" sofa redefines comfort and style. Designed with soft, minimalist lines, it is the ideal sanctuary after a long day.</p>\r\n<ul>\r\n    <li><strong>Flexible Modular Design:</strong> Easily customize to fit any living space.</li>\r\n    <li><strong>Premium Feather-Down Cushions:</strong> Offers exceptional softness and perfect shape retention.</li>\r\n</ul>', '', '', ''),
(90, 72, 'vi', 'DAYBED', 'daybed', 'sâsasÁđá', 'ÂSAS', 'Á', 'A'),
(91, 73, 'vi', 'Bàn ăn gia đình121223333', 'b-n-n-gia-nh121223333', 'sdadasdasdasd34543534246677789879', '', '', ''),
(106, 79, 'vi', 'Bàn ăn gia đình', 'b-n-n-gia-nh', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `completed_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `thumbnail`, `completed_at`, `created_at`) VALUES
(14, 'project_thumb_6888640379b2f1.69845312.png', NULL, '2025-07-29 06:02:43'),
(15, NULL, NULL, '2025-07-29 08:03:07'),
(30, 'project_thumb_6899ae8ee8dc40.39447017.jpg', NULL, '2025-08-11 04:22:11'),
(31, 'project_thumb_6899ae99debd97.05141693.jpg', '2025-08-14', '2025-08-11 08:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

CREATE TABLE `project_images` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `image_url`, `created_at`) VALUES
(29, 30, 'uploads/projects/project_30_689970cc70a153.08020284.png', '2025-08-11 11:25:48');

-- --------------------------------------------------------

--
-- Table structure for table `project_products`
--

CREATE TABLE `project_products` (
  `project_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_products`
--

INSERT INTO `project_products` (`project_id`, `product_id`) VALUES
(30, 70),
(30, 71),
(30, 72),
(30, 73);

-- --------------------------------------------------------

--
-- Table structure for table `project_translations`
--

CREATE TABLE `project_translations` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_translations`
--

INSERT INTO `project_translations` (`id`, `project_id`, `language_code`, `title`, `slug`, `description`, `meta_title`, `meta_description`, `meta_keywords`) VALUES
(37, 30, 'vi', 'dfdfds', 'dfdfds', '<p>gfdgfdgdfgdf</p>', '', '', ''),
(38, 30, 'en', 'dsfsdf2323412334312321', 'dsfsdf2323412334312321', '<p>dsdsdgh5667878990<img src=\"http://localhost/interior-website/uploads/projects/content/project_content_68996fe9bd90b7.19049387.png\"></p>', '', '', ''),
(43, 31, 'vi', 'Kiến Tạo Không Gian Sống Đẳng Cấp111145456677', 'ki-n-t-o-kh-ng-gian-s-ng-ng-c-p111145456677', '<p>edasdasdas3sdsd</p>', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'Quản trị viên cấp cao nhất, có toàn bộ quyền.'),
(2, 'Staff', 'Nhân viên, có các quyền hạn bị giới hạn.'),
(3, 'thiết kế', 'dsd');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 56),
(1, 57),
(1, 58),
(1, 60),
(1, 61),
(1, 62),
(2, 33),
(2, 34),
(2, 36),
(2, 38),
(2, 39),
(2, 41),
(2, 42),
(2, 43),
(2, 44),
(2, 45),
(2, 46),
(2, 47),
(2, 48),
(2, 50),
(2, 51),
(2, 52),
(2, 53),
(2, 56),
(2, 57),
(2, 58),
(2, 61),
(2, 62);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','image','html','link') DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `updated_at`) VALUES
(1, 'dsdsdsdsdsd', 'sdsdsdsd', 'text', '2025-07-27 11:53:41'),
(3, 'contact_email', 'fuaco@gmail.com', 'text', '2025-08-01 06:12:51'),
(4, 'hotline', '012344565', 'text', '2025-08-01 06:12:51'),
(23, 'bank_account_name', 'Huỳnh Phú', 'text', '2025-08-06 06:27:33'),
(24, 'bank_account_number', '0123456789', 'text', '2025-08-06 04:17:58'),
(25, 'bank_name', 'Vietcombank', 'text', '2025-08-06 06:27:33'),
(26, 'bank_branch', 'Quảng ngãi', 'text', '2025-08-06 06:27:33');

-- --------------------------------------------------------

--
-- Table structure for table `setting_translations`
--

CREATE TABLE `setting_translations` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `setting_translations`
--

INSERT INTO `setting_translations` (`id`, `setting_key`, `language_code`, `setting_value`) VALUES
(1, 'site_name', 'vi', 'FUACO Interior'),
(2, 'address', 'vi', 'Lô C5 3, KCN, Sơn Tịnh, Quảng Ngãi'),
(3, 'footer_text', 'vi', 'sdjsdhshfd'),
(7, 'site_name', 'en', 'FUACO Interior'),
(8, 'address', 'en', 'Lô C5 3, KCN, Sơn Tịnh, Quảng Ngãi'),
(9, 'footer_text', 'en', 'sđfhdfdfbhdsbfhvdgfvgvfgdvfgsdfvsdfdf');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `image_url`, `button_link`, `display_order`, `is_active`, `created_at`) VALUES
(18, 'slider_6899abd585f671.10788416.jpg', '/san-pham', 1, 1, '2025-08-08 08:15:09'),
(19, 'slider_6899af7b1a5e66.79432720.jpg', '/du-an', 2, 1, '2025-08-08 08:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `slider_translations`
--

CREATE TABLE `slider_translations` (
  `id` int(11) NOT NULL,
  `slider_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slider_translations`
--

INSERT INTO `slider_translations` (`id`, `slider_id`, `language_code`, `title`, `subtitle`, `button_text`) VALUES
(37, 18, 'vi', 'Kiến Tạo Không Gian Sống Đẳng Cấp', 'Nơi mỗi thiết kế là một tác phẩm nghệ thuật, mang đến vẻ đẹp tinh tế và bền vững.', 'Khám Phá Bộ Sưu Tập'),
(38, 19, 'vi', 'Dấu Ấn Cá Nhân', 'Biến ngôi nhà của bạn thành một không gian độc đáo, phản ánh phong cách của riêng bạn.', 'Xem Dự Án'),
(40, 18, 'en', 'Crafting Elegant Living Spaces', 'Where each design is a work of art, bringing sophisticated and sustainable beauty.', 'Discover The Collection'),
(42, 19, 'en', 'Your Personal Signature', 'Turn your house into a unique space that reflects your own style.', 'View Projects');

-- --------------------------------------------------------

--
-- Table structure for table `theme_options`
--

CREATE TABLE `theme_options` (
  `id` int(11) NOT NULL,
  `option_key` varchar(255) NOT NULL,
  `option_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theme_options`
--

INSERT INTO `theme_options` (`id`, `option_key`, `option_value`) VALUES
(1, 'banner_button_link', ''),
(2, 'social_facebook', 'https://www.gartenmoebel.de/'),
(3, 'social_instagram', 'https://www.gartenmoebel.de/'),
(4, 'social_youtube', 'https://www.gartenmoebel.de/'),
(5, 'social_tiktok', 'https://www.gartenmoebel.de/'),
(11, 'banner_image', 'uploads/theme/banner_image_688c5a996830b3.59039129.jpg'),
(17, 'logo_light', 'uploads/theme/logo_light_689c365c96b881.62559970.png'),
(41, 'footer_explore_menu_id', ''),
(42, 'footer_connect_menu_id', '5'),
(88, 'favicon', 'uploads/theme/favicon_688ade6ee6fcf4.95142920.png'),
(352, 'appointment_section_image', 'uploads/theme/appointment_section_image_689d6869c60cb8.67058383.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `theme_option_translations`
--

CREATE TABLE `theme_option_translations` (
  `id` int(11) NOT NULL,
  `option_key` varchar(255) NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `option_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theme_option_translations`
--

INSERT INTO `theme_option_translations` (`id`, `option_key`, `language_code`, `option_value`) VALUES
(1, 'banner_title', 'vi', '2222uuyuty'),
(2, 'banner_subtitle', 'vi', '1111y'),
(3, 'banner_button_text', 'vi', '3333'),
(4, 'footer_about', 'vi', 'FUACO - Thương hiệu hàng đầu trong lĩnh vực thiết kế và thi công nội thất cao cấp, mang đến những giải pháp không gian sống sang trọng và bền vững.\r\n\r\n'),
(5, 'banner_title', 'en', 'sdsad'),
(6, 'banner_subtitle', 'en', 'sadasd'),
(7, 'banner_button_text', 'en', 'ádads'),
(8, 'footer_about', 'en', 'FUACO - Thương hiệu hàng đầu trong lĩnh vực thiết kế và thi công nội thất cao cấp, mang đến những giải pháp không gian sống sang trọng và bền vững.\r\n'),
(120, 'brand_statement_title', 'vi', 'Chúng tôi không chỉ tạo ra nội thất, chúng tôi kiến tạo phong cách sống'),
(125, 'brand_statement_title', 'en', 'We don\'t just create furniture; we craft lifestyles.'),
(181, 'featured_products_title', 'vi', 'Sản Phẩm Tinh Hoa'),
(182, 'featured_products_subtitle', 'vi', 'Mỗi sản phẩm là một tuyên ngôn về phong cách, được chế tác tỉ mỉ để đáp ứng những tiêu chuẩn khắt khe nhất về thẩm mỹ và chất lượng.'),
(185, 'featured_products_title', 'en', 'Exquisite Products'),
(186, 'featured_products_subtitle', 'en', 'Each product is a statement of style, meticulously crafted to meet the highest standards of aesthetics and quality.'),
(199, 'project_section_title', 'vi', 'Dự Án Của Chúng Tôi'),
(200, 'project_section_subtitle', 'vi', 'Cùng chiêm ngưỡng những không gian đã được FUACO thổi hồn, nơi mỗi chi tiết đều kể một câu chuyện riêng về sự đẳng cấp và tinh tế.'),
(201, 'blog_section_title', 'vi', 'Góc Cảm Hứng'),
(202, 'blog_section_subtitle', 'vi', 'Khám phá những xu hướng thiết kế mới nhất, mẹo trang trí hữu ích và những câu chuyện đằng sau các tác phẩm của chúng tôi.'),
(207, 'project_section_title', 'en', 'Our Portfolio'),
(208, 'project_section_subtitle', 'en', 'Our Portfolio'),
(209, 'blog_section_title', 'en', ' Inspiration Corner'),
(210, 'blog_section_subtitle', 'en', 'Khám phá những xu hướng thiết kế mới nhất, mẹo trang trí hữu ích và những câu chuyện đằng sau các tác phẩm của chúng tôi.'),
(219, 'appointment_section_title', 'vi', 'Đặt Lịch Hẹn Tư Vấn ở Đây'),
(220, 'appointment_section_subtitle', 'vi', 'Để lại thông tin của bạn, đội ngũ chuyên gia của FUACO sẽ liên hệ để tư vấn giải pháp nội thất phù hợp nhất cho không gian của bạn.\r\n'),
(221, 'appointment_form_name_label', 'vi', 'Họ Và Tên'),
(222, 'appointment_form_phone_label', 'vi', 'Số Điện Thoại'),
(223, 'appointment_form_email_label', 'vi', 'Email'),
(224, 'appointment_form_date_label', 'vi', 'Ngày Hẹn Gặp'),
(225, 'appointment_form_time_label', 'vi', 'Giờ Hẹn Gặp'),
(226, 'appointment_form_note_label', 'vi', 'Ghi Rõ Nội Dung'),
(227, 'appointment_form_button_text', 'vi', 'Gửi Lịch Hẹn Đi nè'),
(228, 'appointment_success_message', 'vi', ''),
(229, 'appointment_error_message', 'vi', ''),
(238, 'appointment_section_title', 'en', 'Đặt Lịch Hẹn Tư Vấn'),
(239, 'appointment_section_subtitle', 'en', 'sdsad'),
(240, 'appointment_form_name_label', 'en', 'ád'),
(241, 'appointment_form_phone_label', 'en', ''),
(242, 'appointment_form_email_label', 'en', ''),
(243, 'appointment_form_date_label', 'en', ''),
(244, 'appointment_form_time_label', 'en', ''),
(245, 'appointment_form_note_label', 'en', ''),
(246, 'appointment_form_button_text', 'en', ''),
(247, 'appointment_success_message', 'en', ''),
(248, 'appointment_error_message', 'en', ''),
(405, 'view_all_products_button', 'vi', 'Xem tất cả sản phẩm'),
(408, 'view_all_projects_button', 'vi', 'Xem tất cả dự án'),
(411, 'view_all_blog_button', 'vi', 'Xem tất cả bài viết v'),
(427, 'view_all_products_button', 'en', 'Xem tất cả sản phẩm'),
(430, 'view_all_projects_button', 'en', 'Xem tất cả dự án của chúng tôi'),
(433, 'view_all_blog_button', 'en', 'Xem tất cả bài viết'),
(579, 'testimonial_section_title', 'en', 'What Our Clients Say'),
(580, 'testimonial_section_subtitle', 'en', 'Niềm tin và sự hài lòng của khách hàng là thước đo thành công lớn nhất của FUACO.'),
(590, 'testimonial_section_title', 'vi', 'Khách Hàng Nói Về Chúng Tôi'),
(591, 'testimonial_section_subtitle', 'vi', 'Niềm tin và sự hài lòng của khách hàng là thước đo thành công lớn nhất của FUACO.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `avatar`, `created_at`, `updated_at`, `reset_token`, `reset_token_expires_at`) VALUES
(5, 'admin', 'admin@gmail.com', '$2y$10$/qGvXfJYYNmxhDOiwXACWeCt3QrfkHNi.J.1FtTNQMdOPDavMURkC', 1, 'avatar_689599e04c6e89.20592615.png', '2025-07-28 01:06:03', '2025-08-08 06:32:00', '57266f4ef5e00232361a8d03a06cdd43eedb9eaa664a5dd49625cb0886b122b9', '2025-07-28 11:27:55'),
(10, 'Huỳnh Phú admin', 'tuh317978@gmail.com', '$2y$10$3WsKmbns1ltVJhR9eSOqEui9UOMAvWqFK62QO/8tAuL/eIEJh.Aje', 1, 'avatar_688c5897c61422.30608217.png', '2025-07-28 07:39:18', '2025-08-01 06:03:03', NULL, NULL),
(13, 'huỳnh phú', 'huynhphu06143@gmail.com', '$2y$10$pBwwIjouUVBln5WS7OntxeR1xFD3KQxwfGOe7MAgqbRisYEmKeDx6', 1, NULL, '2025-08-13 06:05:17', '2025-08-13 06:10:52', '7cf232db8225afca56ae8f37b17dd0b7dbfb202c78e42a3cee17a7930063843e', '2025-08-13 14:10:52');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`customer_id`, `product_id`, `created_at`) VALUES
(1, 70, '2025-08-15 09:01:28'),
(1, 79, '2025-08-15 09:01:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_category_translations`
--
ALTER TABLE `blog_category_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_category_id_language_code` (`blog_category_id`,`language_code`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `blog_post_products`
--
ALTER TABLE `blog_post_products`
  ADD PRIMARY KEY (`post_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `blog_post_projects`
--
ALTER TABLE `blog_post_projects`
  ADD PRIMARY KEY (`post_id`,`project_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_translations`
--
ALTER TABLE `blog_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_id_language_code` (`blog_id`,`language_code`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_translations`
--
ALTER TABLE `category_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_lang` (`category_id`,`language_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `job_translations`
--
ALTER TABLE `job_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id_language_code` (`job_id`,`language_code`);

--
-- Indexes for table `media_library`
--
ALTER TABLE `media_library`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location` (`location`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_item_id_language_code` (`menu_item_id`,`language_code`);

--
-- Indexes for table `menu_translations`
--
ALTER TABLE `menu_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_id_language_code` (`menu_id`,`language_code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `page_translations`
--
ALTER TABLE `page_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_id_language_code` (`page_id`,`language_code`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_translations`
--
ALTER TABLE `product_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_lang` (`product_id`,`language_code`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_products`
--
ALTER TABLE `project_products`
  ADD PRIMARY KEY (`project_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `project_translations`
--
ALTER TABLE `project_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id_language_code` (`project_id`,`language_code`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `setting_translations`
--
ALTER TABLE `setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_language_unique` (`setting_key`,`language_code`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `slider_translations`
--
ALTER TABLE `slider_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slider_id_language_code` (`slider_id`,`language_code`);

--
-- Indexes for table `theme_options`
--
ALTER TABLE `theme_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_key` (`option_key`);

--
-- Indexes for table `theme_option_translations`
--
ALTER TABLE `theme_option_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_language_unique` (`option_key`,`language_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`customer_id`,`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=971;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `blog_category_translations`
--
ALTER TABLE `blog_category_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_translations`
--
ALTER TABLE `blog_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `category_translations`
--
ALTER TABLE `category_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `job_translations`
--
ALTER TABLE `job_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `media_library`
--
ALTER TABLE `media_library`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `menu_translations`
--
ALTER TABLE `menu_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `page_translations`
--
ALTER TABLE `page_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_translations`
--
ALTER TABLE `product_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `project_translations`
--
ALTER TABLE `project_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `setting_translations`
--
ALTER TABLE `setting_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `slider_translations`
--
ALTER TABLE `slider_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `theme_options`
--
ALTER TABLE `theme_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=367;

--
-- AUTO_INCREMENT for table `theme_option_translations`
--
ALTER TABLE `theme_option_translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1781;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blogs`
--
ALTER TABLE `blogs`
  ADD CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `blog_category_translations`
--
ALTER TABLE `blog_category_translations`
  ADD CONSTRAINT `fk_blog_cat_translations` FOREIGN KEY (`blog_category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `blog_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_post_products`
--
ALTER TABLE `blog_post_products`
  ADD CONSTRAINT `bpp_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bpp_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_post_projects`
--
ALTER TABLE `blog_post_projects`
  ADD CONSTRAINT `bppr_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bppr_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  ADD CONSTRAINT `blog_post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_translations`
--
ALTER TABLE `blog_translations`
  ADD CONSTRAINT `fk_blog_translations_blogs` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_translations`
--
ALTER TABLE `category_translations`
  ADD CONSTRAINT `fk_category_translations` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `fk_job_applications` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_translations`
--
ALTER TABLE `job_translations`
  ADD CONSTRAINT `fk_job_translations` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  ADD CONSTRAINT `fk_menu_item_translations` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_translations`
--
ALTER TABLE `menu_translations`
  ADD CONSTRAINT `fk_menu_translations` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_translations`
--
ALTER TABLE `page_translations`
  ADD CONSTRAINT `fk_page_translations` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_translations`
--
ALTER TABLE `product_translations`
  ADD CONSTRAINT `fk_product_translations` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `project_images_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_products`
--
ALTER TABLE `project_products`
  ADD CONSTRAINT `project_products_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_translations`
--
ALTER TABLE `project_translations`
  ADD CONSTRAINT `fk_project_translations` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `slider_translations`
--
ALTER TABLE `slider_translations`
  ADD CONSTRAINT `fk_slider_translations` FOREIGN KEY (`slider_id`) REFERENCES `sliders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
