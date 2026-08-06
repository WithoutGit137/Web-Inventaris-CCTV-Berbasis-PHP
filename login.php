<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Jika sudah login sebagai admin, langsung alihkan ke index.php
if (function_exists('isAdmin') && isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $loginSuccess = false;

        // 1. Pengecekan via Database PDO (jika $pdo ada di config.php)
        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Deteksi nama kolom password secara otomatis (password / password_hash / pass)
                    $dbPassword = $user['password'] ?? $user['password_hash'] ?? $user['pass'] ?? '';

                    // Cek password (bisa hash maupun plain text)
                    if ($dbPassword !== '' && (password_verify($password, $dbPassword) || $password === $dbPassword)) {
                        $loginSuccess = true;
                        $_SESSION['user_id']  = $user['id'] ?? 1;
                        $_SESSION['username'] = $user['username'] ?? $username;
                        $_SESSION['role']     = $user['role'] ?? 'admin';
                    }
                }
            } catch (PDOException $e) {
                // Jika terjadi kendala pada tabel database
                $loginSuccess = false;
            }
        }

        // 2. Fallback jika tidak menggunakan database / tabel users kosong
        if (!$loginSuccess && !isset($user)) {
            if ($username === 'admin' && $password === 'admin') {
                $loginSuccess = true;
                $_SESSION['user_id']  = 1;
                $_SESSION['username'] = 'admin';
                $_SESSION['role']     = 'admin';
            }
        }

        // Eksekusi Login
        if ($loginSuccess) {
            if (function_exists('setFlash')) {
                setFlash('success', 'Berhasil login sebagai Admin.');
            }
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi username dan password.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Inventory CCTV</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Navbar disembunyikan -->

<div class="container login-container">
    <div style="margin-bottom: 16px;">
        <a href="index.php" style="color: #64748b; text-decoration: none; font-size: 0.875rem; font-weight: 500;">
            ← Kembali ke Beranda
        </a>
    </div>

    <h1>Login Admin</h1>
    <p class="subtitle">Masuk untuk mengelola data CCTV kapal.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>

        <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Login</button>
    </form>
</div>

<!-- Footer Tetap Ada -->
<?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>