<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
requireAdmin();

$error = '';

// Tambah user baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role     = ($_POST['role'] ?? 'client') === 'admin' ? 'admin' : 'client';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, password_hash, full_name, role) VALUES (:u, :p, :f, :r)"
            );
            $stmt->execute(['u' => $username, 'p' => $hash, 'f' => $fullName, 'r' => $role]);
            setFlash('success', 'User "' . $username . '" berhasil ditambahkan sebagai ' . $role . '.');
            header('Location: manage_users.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Username "' . $username . '" sudah digunakan.';
            } else {
                $error = 'Gagal menambah user: ' . $e->getMessage();
            }
        }
    }
}

// Hapus user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId === (int)$_SESSION['user_id']) {
        setFlash('error', 'Anda tidak bisa menghapus akun yang sedang login.');
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $delId]);
        setFlash('success', 'User berhasil dihapus.');
    }
    header('Location: manage_users.php');
    exit;
}

$flash = getFlash();
$users = $pdo->query("SELECT id, username, full_name, role, created_at FROM users ORDER BY created_at ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola User</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <h1>Kelola User</h1>
    <p class="subtitle">Buat akun admin (read/write) atau client (read-only) baru.</p>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Tambah User Baru</h2>
    <form method="post" style="margin-bottom:30px;">
        <input type="hidden" name="action" value="add">
        <div class="field">
            <label>Nama Lengkap</label>
            <input type="text" name="full_name">
        </div>
        <div class="field">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <div class="field">
            <label>Role</label>
            <select name="role">
                <option value="client">Client (read-only)</option>
                <option value="admin">Admin (read/write)</option>
            </select>
        </div>
        <div class="field" style="flex:none; align-self:flex-end;">
            <button type="submit">Tambah User</button>
        </div>
    </form>

    <h2>Daftar User</h2>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th>Dibuat Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-client' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><?= htmlspecialchars($u['created_at']) ?></td>
                    <td>
                        <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="post" style="display:inline" onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                        <?php else: ?>
                            <span style="color:#9ca3af; font-size:13px;">(akun Anda)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
