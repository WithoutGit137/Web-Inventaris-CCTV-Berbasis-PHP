<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$flash = getFlash();

$keyword = trim($_GET['nama_kapal'] ?? '');
$results = [];
$hasSearched = $keyword !== '';

if ($hasSearched) {
    $stmt = $pdo->prepare(
        "SELECT id, serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan
         FROM cctv_inventory
         WHERE nama_kapal LIKE :keyword
         ORDER BY nama_kapal ASC"
    );
    $stmt->execute(['keyword' => '%' . $keyword . '%']);
    $results = $stmt->fetchAll();
} elseif (function_exists('isAdmin') && isAdmin()) {
    $results = $pdo->query(
        "SELECT id, serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan
         FROM cctv_inventory ORDER BY nama_kapal ASC"
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek CCTV by Nama Kapal</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <h1>Cek CCTV Berdasarkan Nama Kapal</h1>
    <p class="subtitle">Masukkan nama kapal untuk melihat daftar seluruh unit CCTV yang terpasang di kapal tersebut.</p>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <form method="get" action="" class="search-form">
        <input
            type="text"
            name="nama_kapal"
            placeholder="Contoh: KM. Bahari Sentosa"
            value="<?= htmlspecialchars($keyword) ?>"
            autofocus
        >
        <button type="submit">Cek Nama Kapal</button>
    </form>

    <?php if (function_exists('isAdmin') && isAdmin()): ?>
    <div class="actions-row">
        <a href="add_edit.php" class="btn">+ Tambah Data</a>
        <a href="import.php" class="btn btn-secondary">Import dari Excel/CSV</a>
    </div>
    <?php endif; ?>

    <?php if (count($results) > 0): ?>
        <div><span class="badge badge-blue">Total <?= count($results) ?> data ditampilkan</span></div>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Nama Kapal</th>
                    <th>Jenis Barang</th>
                    <th>Brand</th>
                    <th>No. Model</th>
                    <th>Posisi Barang</th>
                    <th>Tahun Perolehan</th>
                    <?php if (function_exists('isAdmin') && isAdmin()): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['serial_number']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kapal']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_barang']) ?></td>
                        <td><?= htmlspecialchars($row['brand_barang']) ?></td>
                        <td><?= htmlspecialchars($row['no_model']) ?></td>
                        <td><?= htmlspecialchars($row['posisi_barang']) ?></td>
                        <td><?= htmlspecialchars($row['tahun_perolehan']) ?></td>
                        <?php if (function_exists('isAdmin') && isAdmin()): ?>
                        <td>
                            <a class="btn btn-sm" href="add_edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
                            <form method="post" action="delete.php" style="display:inline"
                                  onsubmit="return confirm('Hapus data serial number <?= htmlspecialchars($row['serial_number']) ?>?');">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="empty">
            <?php if ($hasSearched): ?>
                Tidak ada data CCTV yang ditemukan untuk kapal "<?= htmlspecialchars($keyword) ?>".
            <?php elseif (function_exists('isAdmin') && isAdmin()): ?>
                Belum ada data.
            <?php else: ?>
                Masukkan nama kapal di atas untuk melihat data CCTV.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="footer">
    &copy; 2026 Hafizh Ibrahim. Sistem Inventory CCTV Kapal.
</div>

</body>
</html>
