<?php
// Hitung ucapan waktu (Indonesia)
$hour = (int)date('G');
if ($hour < 11)      $greet = 'Selamat pagi';
elseif ($hour < 15)  $greet = 'Selamat siang';
elseif ($hour < 19)  $greet = 'Selamat sore';
else            $greet = 'Selamat malam';
?>
<nav class="navbar">
    <a href="index.php" class="brand">Sistem Cek Inventory CCTV</a>

    <div class="menu">
        <a href="index.php">Cek Serial</a>
        <a href="cek_lokasi.php">Cek Keyword</a>

        <span class="user-info">
            <?= $greet ?>, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'guest') ?></strong>
        </span>

        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn">Login Admin</a>
        <?php endif; ?>
    </div>
</nav>