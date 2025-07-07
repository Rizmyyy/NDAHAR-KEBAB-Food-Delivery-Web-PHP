-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Jun 2025 pada 19.09
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_delivery`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `full_address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `label`, `full_address`, `city`, `province`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 7, 'Rumah', 'Purwokerto Ngetan', 'Banyumas', 'Jawa Tengah', '53211', 1, '2025-06-30 13:03:27', '2025-06-30 13:03:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` varchar(50) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `timestamp`, `is_read`) VALUES
(1, 7, 1, 'halo', '2025-06-29 18:52:22', 1),
(2, 7, 1, 'p', '2025-06-29 19:05:56', 1),
(3, 7, 1, 'p', '2025-06-29 19:26:33', 1),
(4, 7, 1, 'p', '2025-06-29 19:32:33', 1),
(5, 7, 1, 'p', '2025-06-29 19:37:06', 1),
(6, 7, 1, 'p', '2025-06-30 09:22:41', 1),
(7, 5, 1, 'p', '2025-06-30 09:47:32', 0),
(8, 1, 7, 'p', '2025-06-30 10:16:15', 0),
(9, 1, 7, 'p', '2025-06-30 10:26:27', 0),
(10, 5, 1, 'p', '2025-06-30 10:30:23', 0),
(11, 5, 1, 'p', '2025-06-30 10:36:42', 0),
(12, 5, 1, 'p', '2025-06-30 11:53:27', 0),
(13, 8, 1, 'halo', '2025-06-30 11:54:51', 1),
(14, 1, 8, 'p', '2025-06-30 11:56:58', 0),
(15, 1, 8, 'p', '2025-06-30 12:05:40', 0),
(16, 8, 1, 'halo', '2025-06-30 12:36:50', 1),
(17, 1, 8, 'iya kenapa', '2025-06-30 12:36:58', 0),
(18, 7, 1, 'assalamualaikaum', '2025-06-30 12:38:34', 1),
(19, 1, 7, 'waalaikumsalam', '2025-06-30 12:38:49', 0),
(20, 9, 1, 'halo min', '2025-06-30 21:30:35', 1),
(21, 1, 9, 'halo', '2025-06-30 21:30:58', 0),
(22, 5, 1, 'halo', '2025-06-30 21:31:25', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `promo_code_used` varchar(100) DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bukti_bayar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `total_amount`, `payment_method`, `promo_code_used`, `status`, `created_at`, `updated_at`, `bukti_bayar`) VALUES
(1, 0, 'John Doe', 'john@email.com', '081234567890', 150000.00, NULL, NULL, 'completed', '2025-06-27 03:14:16', '2025-06-30 07:22:58', NULL),
(2, 0, 'Jane Smith', 'jane@email.com', '081234567891', 225000.00, NULL, NULL, 'pending', '2025-06-27 03:14:16', '2025-06-30 07:22:58', NULL),
(3, 0, 'Bob Wilson', 'bob@email.com', '081234567892', 180000.00, NULL, NULL, 'processing', '2025-06-27 03:14:16', '2025-06-30 07:22:58', NULL),
(4, 0, 'Alice Brown', 'alice@email.com', '081234567893', 320000.00, NULL, NULL, 'completed', '2025-06-27 03:14:16', '2025-06-30 07:22:58', NULL),
(8, 7, 'zaki', 'guest@example.com', NULL, 75000.00, NULL, NULL, 'pending', '2025-06-28 02:21:32', '2025-06-30 07:22:58', NULL),
(9, 5, 'rizmy', 'guest@example.com', NULL, 22222.00, NULL, NULL, 'completed', '2025-06-28 02:26:17', '2025-06-30 07:22:58', NULL),
(10, 5, 'rizmy', 'guest@example.com', NULL, 75000.00, NULL, NULL, 'completed', '2025-06-28 02:37:49', '2025-06-30 07:22:58', NULL),
(11, 5, 'rizmy', 'guest@example.com', NULL, 152800.00, NULL, NULL, 'completed', '2025-06-28 12:14:18', '2025-06-30 07:22:58', NULL),
(12, 5, 'rizmy', 'rizmy@gmail.com', NULL, 23200.00, NULL, NULL, 'completed', '2025-06-29 13:25:54', '2025-06-30 10:49:27', NULL),
(18, 7, 'zaki', 'zaki@gmail.com', NULL, 58000.00, NULL, NULL, 'processing', '2025-06-30 09:06:58', '2025-06-30 13:00:21', NULL),
(19, 5, 'rizmy', 'rizmy@gmail.com', NULL, 87000.00, NULL, NULL, 'pending', '2025-06-30 14:22:38', '2025-06-30 14:22:38', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(4, 8, 2, 0, 0.00),
(5, 9, 5, 0, 0.00),
(6, 10, 2, 3, 25000.00),
(7, 11, 2, 3, 25000.00),
(8, 11, 1, 4, 29000.00),
(9, 12, 1, 1, 29000.00),
(10, 18, 1, 2, 29000.00),
(11, 19, 1, 3, 29000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `icon_path` varchar(255) NOT NULL,
  `delivery_time` varchar(20) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `reviews` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `slug`, `name`, `description`, `price`, `original_price`, `image_path`, `icon_path`, `delivery_time`, `rating`, `reviews`) VALUES
(1, 'kebab-sapi', 'Kebab Sapi', 'Kebab daging sapi premium dengan sayuran segar, saus spesial, dan roti pita yang lembut. Cita rasa otentik Timur Tengah.', 29000.00, 35000.00, 'img/kebab_sapi.jpg', 'img/iconsapi.png', '25 min', 4.8, '(245+)'),
(2, 'ayam', 'Kebab Ayam', 'Kebab ayam tender dengan bumbu rempah pilihan, dilengkapi sayuran crispy dan saus mayo pedas yang menggugah selera.', 25000.00, 28000.00, 'img/kebabayam.jpg', 'img/iconayam.png', '20 min', 4.6, '(189+)'),
(3, 'vegetarian', 'Kebab Vegetarian', 'Kebab sehat dengan protein nabati, sayuran organik segar, dan saus tahini yang creamy. Pilihan sempurna untuk vegetarian.', 22000.00, 26000.00, 'img/kebabsayur.jpg', 'img/iconsayur.png', '15 min', 4.7, '(156+)'),
(5, 'kebab-premium', 'Kebab Premium', 'Nikmati cita rasa kebab terbaik dalam satu paket istimewa!\\r\\nPaket ini berisi kebab pilihan dengan daging berkualitas, sayuran segar, dan saus rahasia khas kami yang melimpah.\\r\\nCocok untuk kamu yang ingin porsi lebih besar dan rasa yang lebih mantap.', 49000.00, 222222.00, 'img/68626a9784e3a-Kebab Premim.jpg', 'img/icon_default.png', '20 min', 4.5, '(0+)'),
(6, 'kebab-mozarella', 'Kebab Mozarella', 'Lelehan keju mozzarella yang melimpah berpadu sempurna dengan daging gurih dan sayuran segar menciptakan sensasi rasa yang lumer di mulut!', 25000.00, 123233.00, 'img/68626ba15f265-kebab mozarella.jpg', 'img/icon_default.png', '20 min', 4.5, '(0+)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percentage` int(11) NOT NULL DEFAULT 0,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promos`
--

INSERT INTO `promos` (`id`, `name`, `description`, `discount_percentage`, `min_purchase`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(2, 'Promo Ramadan', 'Diskon spesial bulan ramadan', 15, 50000.00, '2024-03-01', '2024-04-30', 1, '2025-06-27 03:14:16'),
(4, 'KEBABHEMAT50', 'diskon 50 % untuk semua menu kebab', 50, 0.00, '0000-00-00', '2025-07-05', 1, '2025-06-30 10:58:19'),
(5, '100RIBUDAY', 'Minimal Pembelian 100ribu', 30, 100000.00, '0000-00-00', '2025-07-02', 1, '2025-06-30 11:00:16'),
(6, 'FORYOU', 'Hanya untukmu', 25, 25000.00, '0000-00-00', '2025-07-08', 1, '2025-06-30 11:02:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'Admin', '', NULL, 'admin'),
(5, 'rizmy', 'rizmy@gmail.com', '$2y$10$eYBTk2M1IMyVCi1i6c0MrOq8bT2/Ee9.9rPgyBAcYNXpayiEBHCCm', 'admin'),
(6, 'uwaw', 'uwaw@gmail.com', '$2y$10$02322RxpO01IY8VmdCM3cexLk3MEpX85/K5BFqtitRQpu5L16JDq.', 'customer'),
(7, 'zakini', 'zaki@gmail.com', '$2y$10$1CSMcMEW1E2farMW6MtSNuTuowjTEBgpj8ujvGTVIhP.YrOkirzCa', 'customer'),
(8, 'satria', 'satria@satria.com', '$2y$10$CDrXO08Hdwjz.BYLhVomfeU6xWlcQfQo51Dt6Y6Pmx3w3AZAjZdAy', 'customer'),
(9, 'mohammad', 'mohammad@gmail.com', '$2y$10$HGGHRNx.Hd1oFbAcCNiBPulbFZre2ZRFsAFWMDC3HkL.ApcFt.Jci', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
