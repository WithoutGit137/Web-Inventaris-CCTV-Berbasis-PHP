# Aplikasi Cek Serial Number CCTV Kapal

Aplikasi PHP + MySQL untuk mencari, mengelola, dan mengimpor data CCTV kapal berdasarkan
serial number. Dilengkapi login dengan 2 role:

- **Admin** — bisa melihat, mencari, menambah, mengedit, menghapus data, import Excel/CSV, dan kelola user.
- **Client** — hanya bisa melihat dan mencari data (read-only).

## Struktur File
```
config.php          -> koneksi database + session
auth.php             -> helper login/role
setup.php            -> buat akun admin pertama (jalankan sekali, lalu hapus)
login.php / logout.php
index.php            -> halaman cek serial number + tabel data
add_edit.php          -> form tambah/edit data (admin)
delete.php            -> handler hapus data (admin)
import.php            -> import massal dari Excel (.xlsx) atau CSV (admin)
manage_users.php      -> kelola akun admin/client (admin)
partials/navbar.php   -> navigasi bersama
assets/style.css       -> style bersama
database.sql          -> skema database + data contoh
template_import.csv   -> contoh format file import
composer.json         -> dependency PhpSpreadsheet (khusus import .xlsx)
```

## 1. Instalasi Database
Import `database.sql` ke MySQL (via phpMyAdmin atau terminal):
```
mysql -u root -p < database.sql
```
Ini akan membuat tabel `users` (kosong) dan `cctv_inventory` (berisi data contoh).

## 2. Atur Koneksi
Buka `config.php`, sesuaikan `$db_user` dan `$db_pass` dengan kredensial MySQL Anda
(default XAMPP: user `root`, password kosong).

## 3. (Opsional tapi disarankan) Pasang Dependency Import Excel
Fitur import **.xlsx** membutuhkan library PhpSpreadsheet. Jalankan di folder aplikasi:
```
composer install
```
Jika Composer tidak tersedia di server Anda, fitur import **.csv tetap berfungsi tanpa
composer** — Anda cukup menyimpan file Excel sebagai "CSV (Comma delimited)" sebelum upload.

## 4. Jalankan Aplikasi & Buat Akun Admin
1. Salin seluruh file ke folder `htdocs`/`www` server Anda, mis. `htdocs/cek-cctv/`.
2. Buka `http://localhost/cek-cctv/` di browser — Anda akan diarahkan ke `setup.php`
   karena belum ada akun.
3. Isi username & password untuk akun admin pertama.
4. **Setelah akun admin dibuat, hapus file `setup.php` dari server** (penting untuk
   keamanan, agar orang lain tidak bisa membuat akun admin baru lewat halaman itu).
5. Login di `login.php` dengan akun admin tersebut.

## 5. Membuat Akun Client (read-only) atau Admin Tambahan
Login sebagai admin → menu **Kelola User** → isi form "Tambah User Baru", pilih role
`client` (read-only) atau `admin` (read/write).

## 6. Menggunakan Import Excel/CSV
1. Login sebagai admin → menu **Import Excel/CSV**.
2. Unduh template (`template_import.csv`) jika perlu, atau siapkan file dengan kolom:
   `serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan`
   (urutan kolom bebas, nama header tidak case-sensitive).
3. Upload file `.xlsx` atau `.csv`.
4. Sistem otomatis:
   - **Menambahkan** baris dengan serial number baru.
   - **Memperbarui** baris dengan serial number yang sudah ada di database.
   - **Melewati** baris yang datanya tidak lengkap/tidak valid (ditampilkan di daftar error).

## Catatan Keamanan
- Semua query menggunakan **prepared statement (PDO)** — aman dari SQL Injection.
- Password disimpan sebagai hash (`password_hash`/`password_verify`), tidak pernah plain text.
- Setiap halaman admin dilindungi `requireAdmin()`; halaman data dilindungi `requireLogin()`.
- **Hapus `setup.php` setelah akun admin pertama dibuat.**
- Untuk produksi, aktifkan HTTPS agar sesi login tidak bisa disadap di jaringan publik.
