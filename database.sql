-- =====================================================
-- Database: db_cctv
-- Aplikasi Cek Serial Number CCTV Kapal
-- (dengan login admin/client, CRUD, dan import Excel/CSV)
-- =====================================================

CREATE DATABASE IF NOT EXISTS db_cctv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_cctv;

-- =====================================================
-- Tabel users (login)
-- role: admin  -> bisa tambah/edit/hapus/import data
--       client -> hanya bisa melihat/mencari data (read-only)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(100) DEFAULT NULL,
    role          ENUM('admin','client') NOT NULL DEFAULT 'client',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catatan: akun admin PERTAMA dibuat lewat setup.php (bukan lewat file SQL ini),
-- supaya hash password dibuat langsung oleh PHP di server Anda dan pasti valid.
-- Akun client/admin berikutnya bisa dibuat admin lewat menu "Kelola User".

-- =====================================================
-- Tabel data CCTV
-- =====================================================
CREATE TABLE IF NOT EXISTS cctv_inventory (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    serial_number    VARCHAR(100) NOT NULL UNIQUE,
    nama_kapal       VARCHAR(150) NOT NULL,
    jenis_barang     VARCHAR(100) NOT NULL,
    brand_barang     VARCHAR(100) NOT NULL,
    no_model         VARCHAR(100) NOT NULL,
    posisi_barang    VARCHAR(150) NOT NULL,
    tahun_perolehan  YEAR NOT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_serial_number ON cctv_inventory (serial_number);

-- =====================================================
-- Data contoh (silakan hapus/ganti dengan data asli Anda)
-- =====================================================
INSERT INTO cctv_inventory
(serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan)
VALUES
('HKV20231001', 'KM. Nusantara Jaya', 'Kamera CCTV Dome',   'Hikvision', 'DS-2CE56D0T-IRPF', 'Deck Kemudi',      2023),
('HKV20231002', 'KM. Nusantara Jaya', 'Kamera CCTV Bullet', 'Hikvision', 'DS-2CE16D0T-IRPF',  'Ruang Mesin',      2023),
('DHU20220587', 'KM. Bahari Sentosa', 'Kamera CCTV IP',     'Dahua',     'IPC-HFW1230S',      'Buritan',          2022),
('DHU20220588', 'KM. Bahari Sentosa', 'DVR 8 Channel',      'Dahua',     'DHI-XVR5108HS',     'Ruang Kontrol',    2022),
('CPP20240012', 'KM. Samudera Indah', 'Kamera CCTV PTZ',    'CP Plus',  'CP-UNC-TP81ZL5',    'Anjungan',         2024),
('SNY20210099', 'KM. Elang Laut',     'Kamera CCTV Bullet', 'Sony',      'SNC-VB770',         'Haluan',           2021);
