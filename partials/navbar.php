<?php
// Hitung ucapan waktu (Indonesia)
$hour = (int)date('G');
if ($hour < 11)      $greet = 'Selamat pagi';
elseif ($hour < 15)  $greet = 'Selamat siang';
elseif ($hour < 19)  $greet = 'Selamat sore';
else            $greet = 'Selamat malam';
?>
<nav class="navbar">
    <div class="navbar-row">
        <a href="index.php" class="brand">Sistem Cek Inventory CCTV</a>

        <button type="button" class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="Buka menu">
            <span class="nav-toggle-bar"></span>
            <span class="nav-toggle-bar"></span>
            <span class="nav-toggle-bar"></span>
        </button>
    </div>

    <div class="menu" id="navMenu">
        <a href="index.php">Cek Serial</a>
        <a href="cek_lokasi.php">Cek Keyword</a>

        <span class="user-info">
            <?= $greet ?>, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'guest') ?></strong>
        </span>

        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn">Login</a>
        <?php endif; ?>
    </div>
</nav>

<script>
(function () {
    var btn  = document.getElementById('navToggle');
    var menu = document.getElementById('navMenu');
    if (!btn || !menu) return;

    function closeMenu() {
        menu.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
    }
    function toggleMenu() {
        var open = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    btn.addEventListener('click', function (e) { e.stopPropagation(); toggleMenu(); });
    document.addEventListener('click', function (e) {
        if (menu.classList.contains('open') && !menu.contains(e.target) && e.target !== btn) closeMenu();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
    window.addEventListener('resize', function () { if (window.innerWidth > 768) closeMenu(); });
})();
</script>