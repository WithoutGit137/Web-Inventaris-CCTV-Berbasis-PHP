<nav class="navbar">
    <a href="index.php" class="brand">Cek Inventory CCTV</a>
    
    <div class="menu">
        <a href="index.php">Cek by Serial Number</a>
        <!-- Diubah menjadi Cek by Lokasi -->
        <a href="cek_lokasi.php">Cek by Keyword</a>
        
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <span class="user-info">Login sebagai: <strong>Admin</strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn" style="padding: 6px 14px; font-size: 13px;">Login Admin</a>
        <?php endif; ?>
    </div>
</nav>