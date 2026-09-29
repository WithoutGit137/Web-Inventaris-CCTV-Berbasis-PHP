-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 29, 2026 at 03:25 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_cctv`
--

-- --------------------------------------------------------

--
-- Table structure for table `cctv`
--

CREATE TABLE `cctv` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `nama_kapal` varchar(150) DEFAULT NULL,
  `jenis_barang` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `no_model` varchar(100) DEFAULT NULL,
  `posisi_barang` varchar(150) DEFAULT NULL,
  `tahun_perolehan` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cctv_inventory`
--

CREATE TABLE `cctv_inventory` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `kondisi` enum('Baru','Sangat Baik','Baik','Cukup','Rusak') DEFAULT 'Baru',
  `nama_kapal` varchar(150) NOT NULL,
  `jenis_barang` varchar(100) NOT NULL,
  `brand_barang` varchar(100) NOT NULL,
  `no_model` varchar(100) NOT NULL,
  `posisi_barang` varchar(150) NOT NULL,
  `tahun_perolehan` year(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cctv_inventory`
--

INSERT INTO `cctv_inventory` (`id`, `serial_number`, `kondisi`, `nama_kapal`, `jenis_barang`, `brand_barang`, `no_model`, `posisi_barang`, `tahun_perolehan`, `created_at`, `updated_at`) VALUES
(1, 'HKV20231001', 'Baru', 'KM. Nusantara Jaya', 'Kamera CCTV Dome', 'Hikvision', 'DS-2CE56D0T-IRPF', 'Deck Kemudi', 2023, '2026-07-31 03:23:26', '2026-07-31 03:23:26'),
(2, 'HKV20231002', 'Baru', 'KM. Nusantara Jaya', 'Kamera CCTV Bullet', 'Hikvision', 'DS-2CE16D0T-IRPF', 'Ruang Mesin', 2023, '2026-07-31 03:23:26', '2026-07-31 03:23:26'),
(3, 'DHU20220587', 'Sangat Baik', 'KM. Bahari Sentosa', 'Kamera CCTV IP', 'Dahua', 'IPC-HFW1230S', 'Buritan', 2022, '2026-07-31 03:23:26', '2026-09-05 07:21:55'),
(4, 'DHU20220588', 'Rusak', 'KM. Bahari Sentosa', 'DVR 8 Channel', 'Dahua', 'DHI-XVR5108HS', 'Ruang Kontrol', 2022, '2026-07-31 03:23:26', '2026-09-07 02:12:22'),
(5, 'CPP20240012', 'Baru', 'KM. Samudera Indah', 'Kamera CCTV PTZ', 'CP Plus', 'CP-UNC-TP81ZL5', 'Anjungan', 2024, '2026-07-31 03:23:26', '2026-07-31 03:23:26'),
(6, 'SNY20210099', 'Sangat Baik', 'KM. Elang Laut', 'Kamera CCTV Bullet', 'Sony', 'SNC-VB770', 'Haluan', 2021, '2026-07-31 03:23:26', '2026-09-07 12:40:00'),
(7, 'HKV20250001', 'Baru', 'KM. Contoh Sejahtera', 'Kamera CCTV Dome', 'Hikvision', 'DS-2CE56D0T-IRPF', 'Deck Kemudi', 2025, '2026-09-07 12:54:03', '2026-09-07 12:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `cctv_kondisi_log`
--

CREATE TABLE `cctv_kondisi_log` (
  `id` int(11) NOT NULL,
  `cctv_id` int(11) NOT NULL,
  `kondisi` varchar(50) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cctv_kondisi_log`
--

INSERT INTO `cctv_kondisi_log` (`id`, `cctv_id`, `kondisi`, `changed_at`) VALUES
(1, 3, 'Sangat Baik', '2026-09-05 07:21:55'),
(2, 1, 'Baru', '2026-07-31 03:23:26'),
(3, 2, 'Baru', '2026-07-31 03:23:26'),
(4, 4, 'Baru', '2026-07-31 03:23:26'),
(5, 5, 'Baru', '2026-07-31 03:23:26'),
(6, 6, 'Baru', '2026-07-31 03:23:26'),
(9, 4, 'Rusak', '2026-09-07 02:12:22'),
(10, 6, 'Sangat Baik', '2026-09-07 12:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `created_at`) VALUES
(1, 'hafizh', '$2y$10$31elivDJNn3VvpzDxF28eu1NzLGV1wwYTPNRLmTv3Cz9n32GAcuPm', 'hafizh', 'admin', '2026-07-31 03:59:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cctv`
--
ALTER TABLE `cctv`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cctv_inventory`
--
ALTER TABLE `cctv_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `idx_serial_number` (`serial_number`);

--
-- Indexes for table `cctv_kondisi_log`
--
ALTER TABLE `cctv_kondisi_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cctv_id` (`cctv_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cctv`
--
ALTER TABLE `cctv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cctv_inventory`
--
ALTER TABLE `cctv_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cctv_kondisi_log`
--
ALTER TABLE `cctv_kondisi_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cctv_kondisi_log`
--
ALTER TABLE `cctv_kondisi_log`
  ADD CONSTRAINT `cctv_kondisi_log_ibfk_1` FOREIGN KEY (`cctv_id`) REFERENCES `cctv_inventory` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
