-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Bulan Mei 2026 pada 08.28
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
-- Database: `mahayana_wisata`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `trip_type` enum('one_way','round_trip') DEFAULT 'one_way',
  `parent_booking_id` int(11) DEFAULT NULL,
  `group_code` varchar(50) DEFAULT NULL,
  `booking_code` varchar(20) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(25) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `travel_status` enum('pending','on_progress','completed','cancelled') DEFAULT 'pending',
  `travel_date` date DEFAULT NULL,
  `qr_used` tinyint(1) DEFAULT 0,
  `qr_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `schedule_id`, `trip_type`, `parent_booking_id`, `group_code`, `booking_code`, `total_price`, `status`, `created_at`, `travel_status`, `travel_date`, `qr_used`, `qr_used_at`) VALUES
(132, 3, 18, 'one_way', NULL, 'GRP-1777660633', 'BK-1777660633', 78000.00, 'paid', '2026-05-01 18:37:13', 'completed', '2026-05-02', 0, NULL),
(133, 3, 15, 'one_way', NULL, 'GRP-1777666619', 'BK-1777666619', 78000.00, 'paid', '2026-05-01 20:16:59', 'completed', '2026-05-02', 0, NULL),
(136, 3, 20, 'one_way', NULL, 'GRP-1777673149', 'BK-1777673149', 75000.00, 'paid', '2026-05-01 22:05:49', 'completed', '2026-05-02', 1, '2026-05-02 05:45:43'),
(137, 3, 20, 'one_way', NULL, 'GRP-1777679869', 'BK-1777679869', 150000.00, 'paid', '2026-05-01 23:57:49', 'completed', '2026-05-02', 1, '2026-05-02 07:02:11'),
(138, 3, 19, 'one_way', NULL, 'GRP-1777685573', 'BK-1777685573', 75000.00, 'paid', '2026-05-02 01:32:53', 'completed', '2026-05-02', 0, NULL),
(139, 3, 16, 'one_way', NULL, 'GRP-1777719582', 'BK-1777719582', 78000.00, 'pending', '2026-05-02 10:59:42', 'on_progress', '2026-05-02', 1, '2026-05-02 19:33:48'),
(140, 3, 22, 'one_way', NULL, 'GRP-1777727409', 'BK-1777727409', 78000.00, 'paid', '2026-05-02 13:10:09', 'completed', '2026-05-02', 1, '2026-05-02 20:12:38'),
(141, 3, 16, 'one_way', NULL, 'GRP-1777801339', 'BK-1777801339', 78000.00, 'paid', '2026-05-03 09:42:19', 'completed', '2026-05-03', 0, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_details`
--

CREATE TABLE `booking_details` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking_details`
--

INSERT INTO `booking_details` (`id`, `booking_id`, `seat_number`, `passenger_name`) VALUES
(156, 132, 'A3', 'Abang Fierza'),
(157, 133, 'EX1', 'Abang Fierza'),
(160, 136, 'A4', 'Abang Fierza'),
(161, 137, 'A2', 'Abang Fierza'),
(162, 137, 'B1', 'prety'),
(163, 138, 'A1', 'Abang Fierza'),
(164, 139, 'C1', 'Abang Fierza'),
(165, 140, 'A1', 'Abang Fierza'),
(166, 141, 'D3', 'Abang Fierza');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_seats`
--

CREATE TABLE `booking_seats` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `bus_name` varchar(50) DEFAULT NULL,
  `plate_number` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buses`
--

INSERT INTO `buses` (`id`, `bus_name`, `plate_number`, `capacity`, `image`) VALUES
(1, 'Mahayana Executive 01', 'F 1234 ABC', 14, 'bus_1777399348.jpg'),
(2, 'Mahayana Royal 02', 'F 5678 DEF', 14, 'bus_1777399335.jpg'),
(3, 'hayes -luxury class', 'F 9999 AB', 9, 'bus_1777399312.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `group_code` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','paid','expired','failed','verified','rejected') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_id` varchar(100) DEFAULT NULL,
  `snap_token` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `group_code`, `amount`, `payment_method`, `payment_proof`, `status`, `paid_at`, `created_at`, `order_id`, `snap_token`) VALUES
(93, 'GRP-1777660633', 78000.00, 'qris', NULL, 'paid', '2026-05-02 01:38:02', '2026-05-01 18:37:14', 'MHYN-132-1777660634', NULL),
(94, 'GRP-1777666619', 78000.00, 'qris', NULL, 'paid', '2026-05-02 03:17:39', '2026-05-01 20:17:00', 'MHYN-133-1777666619', NULL),
(95, 'GRP-1777672680', 75000.00, 'qris', NULL, 'paid', '2026-05-02 04:58:19', '2026-05-01 21:58:01', 'MHYN-134-1777672680', NULL),
(97, 'GRP-1777673149', 75000.00, 'qris', NULL, 'paid', '2026-05-02 05:07:11', '2026-05-01 22:05:50', 'MHYN-136-1777673149', NULL),
(98, 'GRP-1777679869', 150000.00, 'qris', NULL, 'paid', '2026-05-02 06:58:56', '2026-05-01 23:57:50', 'MHYN-137-1777679869', NULL),
(99, 'GRP-1777685573', 75000.00, 'qris', NULL, 'paid', '2026-05-02 08:33:20', '2026-05-02 01:32:54', 'MHYN-138-1777685573', NULL),
(100, 'GRP-1777719582', 78000.00, 'qris', NULL, 'paid', '2026-05-02 18:00:13', '2026-05-02 10:59:44', 'MHYN-139-1777719583', NULL),
(101, 'GRP-1777727409', 78000.00, 'qris', NULL, 'paid', '2026-05-02 20:11:00', '2026-05-02 13:10:10', 'MHYN-140-1777727409', NULL),
(102, 'GRP-1777801339', 78000.00, 'qris', NULL, 'paid', '2026-05-03 16:44:06', '2026-05-03 09:43:34', 'MHYN-141-1777801413', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `tipe_promo` varchar(100) DEFAULT NULL,
  `points` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promos`
--

INSERT INTO `promos` (`id`, `title`, `type`, `tipe_promo`, `points`, `image`, `is_active`, `created_at`) VALUES
(1, 'promo', 'diskon', 'Sewa Armada', '10', 'promo_1777414873.jpg', 1, '2026-04-28 22:21:13'),
(2, 'promo akhir bulan ', 'member', 'Shuttle', '50', 'promo_1777416481.jpg', 1, '2026-04-28 22:48:01'),
(3, 'promo tahun baru', 'diskon', 'Wisata', '39', 'promo_1777416509.jpg', 1, '2026-04-28 22:48:29'),
(4, 'diskon besar', 'Diskon', 'Shuttle', '9', 'promo_1777678864_655.jpg', 1, '2026-05-01 23:41:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `bus_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `rental_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_per_day` int(11) DEFAULT NULL,
  `total_price` decimal(12,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rentals`
--

INSERT INTO `rentals` (`id`, `customer_name`, `customer_phone`, `bus_name`, `capacity`, `rental_date`, `description`, `image`, `price_per_day`, `total_price`, `status`, `created_at`) VALUES
(3, 'mahayana', '087731375531', 'hayes -luxury class', 14, '2026-04-29', 'perhari lengkap AC dan P3K', '1777401829_5.jpg', 1000000, 1000000.00, 'pending', '2026-04-28 18:37:47'),
(4, 'mahayana', '087731375531', 'Mahayana Executive', 14, '2026-04-29', 'lengkap', '1777401883_6434.jpg', 1000000, 1000000.00, 'pending', '2026-04-28 18:44:43'),
(5, 'mahayana', '6285759455910', 'haice', 9, '2026-04-29', 'mewah', '1777401938_7457.jpg', 1000000, 1000000.00, 'pending', '2026-04-28 18:45:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rental_images`
--

CREATE TABLE `rental_images` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rental_images`
--

INSERT INTO `rental_images` (`id`, `rental_id`, `image`, `created_at`) VALUES
(1, 5, '1777404504_8761.jpg', '2026-04-28 19:28:24'),
(2, 5, '1777404504_4367.jpg', '2026-04-28 19:28:24'),
(5, 5, '1777405267_7162.jpg', '2026-04-28 19:41:07'),
(6, 5, '1777405267_5413.jpg', '2026-04-28 19:41:07'),
(7, 5, '1777405267_6586.jpg', '2026-04-28 19:41:07'),
(8, 5, '1777405267_7323.jpg', '2026-04-28 19:41:07'),
(9, 5, '1777405267_9024.jpg', '2026-04-28 19:41:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `departure_city` varchar(100) DEFAULT NULL,
  `arrival_city` varchar(100) DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `routes`
--

INSERT INTO `routes` (`id`, `departure_city`, `arrival_city`, `base_price`) VALUES
(2, 'Bandung', 'Cianjur', 78000.00),
(3, 'Cianjur', 'Jakarta', 200000.00),
(4, 'Jakarta', 'Cianjur', 200000.00),
(5, 'Bandung', 'Jakarta', 200000.00),
(6, 'Jakarta', 'Bandung', 100000.00),
(7, 'Cianjur', 'Bandung', 75000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `date` date DEFAULT NULL,
  `is_daily` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ini adalah Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`id`, `bus_id`, `route_id`, `departure_time`, `arrival_time`, `date`, `is_daily`) VALUES
(15, 1, 2, '03:26:00', '05:30:00', '2026-05-02', 0),
(16, 1, 2, '18:25:00', '20:25:00', '2026-04-29', 1),
(17, 1, 7, '14:30:00', '16:25:00', '2026-04-29', 1),
(18, 2, 2, '03:37:00', '03:38:00', '2026-04-29', 1),
(19, 3, 7, '10:30:00', '12:13:00', '2026-04-29', 1),
(20, 3, 7, '05:51:00', '07:52:00', '2026-04-29', 1),
(21, 3, 7, '16:16:00', '18:08:00', '2026-04-29', 1),
(22, 3, 2, '20:40:00', '22:19:00', '2026-04-29', 1),
(23, 2, 7, '22:20:00', '00:20:00', '2026-04-29', 1),
(24, 1, 2, '01:46:00', '03:45:00', '2026-05-01', 1),
(25, 3, 4, '19:21:00', '22:24:00', '2026-05-02', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `seats`
--

CREATE TABLE `seats` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `seat_number` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `row_label` varchar(5) DEFAULT NULL,
  `col_number` int(11) DEFAULT NULL,
  `position` enum('front','middle','back') DEFAULT NULL,
  `is_driver` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'available',
  `travel_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `seats`
--

INSERT INTO `seats` (`id`, `bus_id`, `schedule_id`, `seat_number`, `created_at`, `updated_at`, `row_label`, `col_number`, `position`, `is_driver`, `status`, `travel_date`) VALUES
(456, 3, 19, 'A1', '2026-04-29 12:46:39', NULL, 'A', 1, 'front', 0, 'available', NULL),
(457, 3, 19, 'EX2', '2026-04-29 12:46:39', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(458, 3, 19, 'EX3', '2026-04-29 12:46:39', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(459, 3, 19, 'EX4', '2026-04-29 12:46:39', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(460, 3, 19, 'EX5', '2026-04-29 12:46:39', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(461, 3, 19, 'EX8', '2026-04-29 12:46:39', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(462, 3, 19, 'EX9', '2026-04-29 12:46:39', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(463, 3, 19, 'EX10', '2026-04-29 12:46:39', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(464, 3, 19, 'EX11', '2026-04-29 12:46:39', NULL, 'D', 4, 'middle', 0, 'available', NULL),
(474, 3, 21, 'A1', '2026-04-29 12:47:03', NULL, 'A', 1, 'front', 0, 'available', NULL),
(475, 3, 21, 'EX2', '2026-04-29 12:47:03', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(476, 3, 21, 'EX3', '2026-04-29 12:47:03', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(477, 3, 21, 'EX4', '2026-04-29 12:47:03', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(478, 3, 21, 'EX5', '2026-04-29 12:47:03', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(479, 3, 21, 'EX8', '2026-04-29 12:47:03', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(480, 3, 21, 'EX9', '2026-04-29 12:47:03', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(481, 3, 21, 'EX10', '2026-04-29 12:47:03', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(482, 3, 21, 'EX11', '2026-04-29 12:47:03', NULL, 'D', 4, 'middle', 0, 'available', NULL),
(497, 2, 18, 'A1', '2026-04-29 12:53:34', NULL, 'A', 1, 'front', 0, 'booked', NULL),
(498, 2, 18, 'A2', '2026-04-29 12:53:34', NULL, 'B', 1, 'middle', 0, 'booked', NULL),
(499, 2, 18, 'A3', '2026-04-29 12:53:34', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(500, 2, 18, 'A4', '2026-04-29 12:53:34', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(501, 2, 18, 'L4', '2026-04-29 12:53:34', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(502, 2, 18, 'L5', '2026-04-29 12:53:34', NULL, 'C', 2, 'middle', 0, 'available', NULL),
(503, 2, 18, 'L6', '2026-04-29 12:53:34', NULL, 'C', 3, 'middle', 0, 'available', NULL),
(504, 2, 18, 'L7', '2026-04-29 12:53:34', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(505, 2, 18, 'L8', '2026-04-29 12:53:34', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(506, 2, 18, 'L9', '2026-04-29 12:53:34', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(507, 2, 18, 'D1', '2026-04-29 12:53:34', NULL, 'E', 1, 'back', 0, 'available', NULL),
(508, 2, 18, 'D2', '2026-04-29 12:53:34', NULL, 'E', 2, 'back', 0, 'available', NULL),
(509, 2, 18, 'D3', '2026-04-29 12:53:34', NULL, 'E', 3, 'back', 0, 'available', NULL),
(510, 2, 18, 'D4', '2026-04-29 12:53:34', NULL, 'E', 4, 'back', 0, 'available', NULL),
(511, 2, 23, 'A1', '2026-04-29 12:54:02', NULL, 'A', 1, 'front', 0, 'available', NULL),
(512, 2, 23, 'A2', '2026-04-29 12:54:02', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(513, 2, 23, 'A3', '2026-04-29 12:54:02', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(514, 2, 23, 'A4', '2026-04-29 12:54:02', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(515, 2, 23, 'L4', '2026-04-29 12:54:02', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(516, 2, 23, 'L5', '2026-04-29 12:54:02', NULL, 'C', 2, 'middle', 0, 'available', NULL),
(517, 2, 23, 'L6', '2026-04-29 12:54:02', NULL, 'C', 3, 'middle', 0, 'available', NULL),
(518, 2, 23, 'L7', '2026-04-29 12:54:02', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(519, 2, 23, 'L8', '2026-04-29 12:54:02', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(520, 2, 23, 'L9', '2026-04-29 12:54:02', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(521, 2, 23, 'D1', '2026-04-29 12:54:02', NULL, 'E', 1, 'back', 0, 'available', NULL),
(522, 2, 23, 'D2', '2026-04-29 12:54:02', NULL, 'E', 2, 'back', 0, 'available', NULL),
(523, 2, 23, 'D3', '2026-04-29 12:54:02', NULL, 'E', 3, 'back', 0, 'available', NULL),
(524, 2, 23, 'D4', '2026-04-29 12:54:02', NULL, 'E', 4, 'back', 0, 'available', NULL),
(525, 1, 15, 'EX1', '2026-04-29 12:54:37', NULL, 'A', 1, 'front', 0, 'available', NULL),
(526, 1, 15, 'EX2', '2026-04-29 12:54:37', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(527, 1, 15, 'EX3', '2026-04-29 12:54:37', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(528, 1, 15, 'EX4', '2026-04-29 12:54:37', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(529, 1, 15, 'EX5', '2026-04-29 12:54:37', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(530, 1, 15, 'EX6', '2026-04-29 12:54:37', NULL, 'C', 2, 'middle', 0, 'available', NULL),
(531, 1, 15, 'EX7', '2026-04-29 12:54:37', NULL, 'C', 3, 'middle', 0, 'available', NULL),
(532, 1, 15, 'EX8', '2026-04-29 12:54:37', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(533, 1, 15, 'EX9', '2026-04-29 12:54:37', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(534, 1, 15, 'EX10', '2026-04-29 12:54:37', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(535, 1, 15, 'EX11', '2026-04-29 12:54:37', NULL, 'E', 1, 'back', 0, 'available', NULL),
(536, 1, 15, 'EX12', '2026-04-29 12:54:37', NULL, 'E', 2, 'back', 0, 'available', NULL),
(537, 1, 15, 'EX13', '2026-04-29 12:54:37', NULL, 'E', 3, 'back', 0, 'available', NULL),
(538, 1, 15, 'EX14', '2026-04-29 12:54:37', NULL, 'E', 4, 'back', 0, 'available', NULL),
(539, 1, 16, 'EX2', '2026-04-29 12:54:59', NULL, 'A', 1, 'front', 0, 'available', NULL),
(540, 1, 16, 'EX5', '2026-04-29 12:54:59', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(541, 1, 16, 'EX6', '2026-04-29 12:54:59', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(542, 1, 16, 'EX7', '2026-04-29 12:54:59', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(543, 1, 16, 'EX8', '2026-04-29 12:54:59', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(544, 1, 16, 'EX9', '2026-04-29 12:54:59', NULL, 'C', 2, 'middle', 0, 'available', NULL),
(545, 1, 16, 'EX10', '2026-04-29 12:54:59', NULL, 'C', 3, 'middle', 0, 'available', NULL),
(546, 1, 16, 'C1', '2026-04-29 12:54:59', NULL, 'D', 1, 'middle', 0, 'booked', NULL),
(547, 1, 16, 'G4', '2026-04-29 12:54:59', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(548, 1, 16, 'J5', '2026-04-29 12:54:59', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(549, 1, 16, 'D3', '2026-04-29 12:54:59', NULL, 'E', 1, 'back', 0, 'available', NULL),
(550, 1, 16, 'D4', '2026-04-29 12:54:59', NULL, 'E', 2, 'back', 0, 'available', NULL),
(551, 1, 16, 'D5', '2026-04-29 12:54:59', NULL, 'E', 3, 'back', 0, 'available', NULL),
(552, 1, 16, 'D6', '2026-04-29 12:54:59', NULL, 'E', 4, 'back', 0, 'available', NULL),
(567, 1, 17, 'L1', '2026-04-29 13:25:33', NULL, 'A', 1, 'front', 0, 'available', NULL),
(568, 1, 17, 'L2', '2026-04-29 13:25:33', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(569, 1, 17, 'L3', '2026-04-29 13:25:33', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(570, 1, 17, 'EX7', '2026-04-29 13:25:33', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(571, 1, 17, 'L4', '2026-04-29 13:25:33', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(572, 1, 17, 'L5', '2026-04-29 13:25:33', NULL, 'C', 2, 'middle', 0, 'available', NULL),
(573, 1, 17, 'L6', '2026-04-29 13:25:33', NULL, 'C', 3, 'middle', 0, 'available', NULL),
(574, 1, 17, 'L7', '2026-04-29 13:25:33', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(575, 1, 17, 'L8', '2026-04-29 13:25:33', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(576, 1, 17, 'L9', '2026-04-29 13:25:33', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(577, 1, 17, 'D3', '2026-04-29 13:25:33', NULL, 'E', 1, 'back', 0, 'available', NULL),
(578, 1, 17, 'D4', '2026-04-29 13:25:33', NULL, 'E', 2, 'back', 0, 'available', NULL),
(579, 1, 17, 'D5', '2026-04-29 13:25:33', NULL, 'E', 3, 'back', 0, 'available', NULL),
(580, 1, 17, 'D6', '2026-04-29 13:25:33', NULL, 'E', 4, 'back', 0, 'available', NULL),
(590, 3, 22, 'A1', '2026-04-29 14:14:00', NULL, 'A', 1, 'front', 0, 'available', NULL),
(591, 3, 22, 'A2', '2026-04-29 14:14:00', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(592, 3, 22, 'A3', '2026-04-29 14:14:00', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(593, 3, 22, 'A4', '2026-04-29 14:14:00', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(594, 3, 22, 'C1', '2026-04-29 14:14:00', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(595, 3, 22, 'B1', '2026-04-29 14:14:00', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(596, 3, 22, 'B2', '2026-04-29 14:14:00', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(597, 3, 22, 'B3', '2026-04-29 14:14:00', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(598, 3, 22, 'B4', '2026-04-29 14:14:00', NULL, 'D', 4, 'middle', 0, 'available', NULL),
(613, 1, 24, 'L1', '2026-04-29 15:44:08', NULL, 'A', 1, 'front', 0, 'available', NULL),
(614, 1, 24, 'L2', '2026-04-29 15:44:08', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(615, 1, 24, 'L3', '2026-04-29 15:44:08', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(616, 1, 24, 'EX7', '2026-04-29 15:44:08', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(617, 1, 24, 'L4', '2026-04-29 15:44:08', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(618, 1, 24, 'L5', '2026-04-29 15:44:08', NULL, 'C', 2, 'middle', 0, 'available', NULL),
(619, 1, 24, 'L6', '2026-04-29 15:44:08', NULL, 'C', 3, 'middle', 0, 'available', NULL),
(620, 1, 24, 'L7', '2026-04-29 15:44:08', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(621, 1, 24, 'L8', '2026-04-29 15:44:08', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(622, 1, 24, 'L9', '2026-04-29 15:44:08', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(623, 1, 24, 'D3', '2026-04-29 15:44:08', NULL, 'E', 1, 'back', 0, 'available', NULL),
(624, 1, 24, 'D4', '2026-04-29 15:44:08', NULL, 'E', 2, 'back', 0, 'available', NULL),
(625, 1, 24, 'D5', '2026-04-29 15:44:08', NULL, 'E', 3, 'back', 0, 'available', NULL),
(626, 1, 24, 'D6', '2026-04-29 15:44:08', NULL, 'E', 4, 'back', 0, 'available', NULL),
(627, 3, 20, 'A1', '2026-05-01 14:45:52', NULL, 'A', 1, 'front', 0, 'booked', NULL),
(628, 3, 20, 'A2', '2026-05-01 14:45:52', NULL, 'B', 1, 'middle', 0, 'available', NULL),
(629, 3, 20, 'A3', '2026-05-01 14:45:52', NULL, 'B', 2, 'middle', 0, 'available', NULL),
(630, 3, 20, 'A4', '2026-05-01 14:45:52', NULL, 'B', 3, 'middle', 0, 'available', NULL),
(631, 3, 20, 'C1', '2026-05-01 14:45:52', NULL, 'C', 1, 'middle', 0, 'available', NULL),
(632, 3, 20, 'B1', '2026-05-01 14:45:52', NULL, 'D', 1, 'middle', 0, 'available', NULL),
(633, 3, 20, 'B2', '2026-05-01 14:45:52', NULL, 'D', 2, 'middle', 0, 'available', NULL),
(634, 3, 20, 'B3', '2026-05-01 14:45:52', NULL, 'D', 3, 'middle', 0, 'available', NULL),
(635, 3, 20, 'B4', '2026-05-01 14:45:52', NULL, 'D', 4, 'middle', 0, 'available', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `user_picture` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `role`, `user_picture`, `created_at`) VALUES
(1, 'Super Admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', NULL, '2026-04-17 18:24:42'),
(3, 'Abang Fierza', 'abangfierza01@gmail.com', '$2y$10$Mz1KRKyRxv0aD7ZzUwRu9O5167cArXtQAlSDtgt0pTVaM3Nkm7riS', NULL, 'user', 'https://lh3.googleusercontent.com/a/ACg8ocIeukohXY9PF-e7ROjE7so645g7wnpkuKO9yuY-4LVJ9Elawoey=s96-c', '2026-04-22 09:41:33'),
(4, 'ikhlas bahrudin', 'robetnt390@gmail.com', '$2y$10$/57sqzHCKqNoij96Ev.zPu0OuPujDWgGmE5HPENF8vm62fm.BCQ4q', NULL, 'user', 'https://lh3.googleusercontent.com/a/ACg8ocJ5TlhR2yBbm3nT1YK_6rJz84lr3xmAR7JpKA1gsbC3NvpEssb_=s96-c', '2026-04-24 07:12:17');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `travel_status_idx` (`travel_status`),
  ADD KEY `status_idx` (`status`);

--
-- Indeks untuk tabel `booking_details`
--
ALTER TABLE `booking_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `idx_seat` (`seat_id`);

--
-- Indeks untuk tabel `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rental_images`
--
ALTER TABLE `rental_images`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indeks untuk tabel `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seat` (`schedule_id`,`seat_number`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT untuk tabel `booking_details`
--
ALTER TABLE `booking_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT untuk tabel `booking_seats`
--
ALTER TABLE `booking_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT untuk tabel `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `rental_images`
--
ALTER TABLE `rental_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `seats`
--
ALTER TABLE `seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=636;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`);

--
-- Ketidakleluasaan untuk tabel `booking_details`
--
ALTER TABLE `booking_details`
  ADD CONSTRAINT `booking_details_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD CONSTRAINT `booking_seats_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_seats_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
