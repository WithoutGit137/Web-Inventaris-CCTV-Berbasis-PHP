<?php
// Partial ini mengasumsikan config.php + auth.php sudah di-include
// dan user sudah pasti login (requireLogin() sudah dipanggil sebelumnya).
?>
<div class="navbar">
    <a href="index.php" class="brand">Cek CCTV Kapal</a>
    <div class="menu">
        <a href="index.php">Cek Serial Number</a>
        <?php if (isAdmin()): ?>
            <a href="add_edit.php">Tambah Data</a>
            <a href="import.php">Import Excel/CSV</a>
            <a href="manage_users.php">Kelola User</a>
        <?php endif; ?>
        <span class="user-info">
            <?= htmlspecialchars(currentUsername()) ?>
            <span class="badge <?= isAdmin() ? 'badge-admin' : 'badge-client' ?>">
                <?= isAdmin() ? 'Admin' : 'Client (read-only)' ?>
            </span>
        </span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>
