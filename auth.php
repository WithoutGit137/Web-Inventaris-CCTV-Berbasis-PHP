<?php
/**
 * auth.php
 * Kumpulan fungsi bantu untuk cek login & role.
 * WAJIB di-include SETELAH config.php (butuh session_start()).
 */

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Akses Ditolak</title>';
        echo '<link rel="stylesheet" href="assets/style.css"></head><body>';
        echo '<div class="container"><div class="alert alert-error">';
        echo 'Akses ditolak. Halaman ini khusus untuk admin.</div>';
        echo '<p><a href="index.php">&larr; Kembali ke halaman utama</a></p></div></body></html>';
        exit;
    }
}

function currentUsername(): string
{
    return $_SESSION['username'] ?? '';
}

function currentRole(): string
{
    return $_SESSION['role'] ?? '';
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
