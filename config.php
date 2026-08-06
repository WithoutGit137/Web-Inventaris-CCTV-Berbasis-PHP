<?php
/**
 * Konfigurasi Koneksi Database
 * Sesuaikan HOST, DB_NAME, DB_USER, dan DB_PASS dengan server Anda.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host    = 'localhost';
$db_name = 'db_cctv';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db_name};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}
