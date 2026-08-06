<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$flash = getFlash();

$keyword = trim($_GET['lokasi'] ?? '');
$tahun = trim($_GET['tahun'] ?? ''); // MENANGKAP INPUT TAHUN
$results = [];

// Mengecek apakah user melakukan pencarian (lewat keyword ATAU filter tahun)
$hasSearched = ($keyword !== '' || $tahun !== '');

// MENGAMBIL DAFTAR TAHUN UNIK DARI DATABASE UNTUK DROPDOWN
$list_tahun = [];
if (isset($pdo)) {
    try {
        $stmt_tahun = $pdo->query("SELECT DISTINCT tahun_perolehan FROM cctv_inventory WHERE tahun_perolehan IS NOT NULL AND tahun_perolehan != '' ORDER BY tahun_perolehan DESC");
        $list_tahun = $stmt_tahun->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Abaikan jika tabel belum siap
    }
}

// LOGIKA QUERY YANG SUDAH DISESUAIKAN
if ($hasSearched) {
    $sql = "SELECT id, serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan
            FROM cctv_inventory WHERE 1=1";
    $params = [];

    // Jika mencari pakai keyword
    if ($keyword !== '') {
        $sql .= " AND (serial_number LIKE :k1
            OR nama_kapal      LIKE :k2
            OR jenis_barang    LIKE :k3
            OR brand_barang    LIKE :k4
            OR no_model        LIKE :k5
            OR posisi_barang   LIKE :k6
            OR tahun_perolehan LIKE :k7)";
        
        $like = '%' . $keyword . '%';
        $params['k1'] = $like; $params['k2'] = $like; $params['k3'] = $like;
        $params['k4'] = $like; $params['k5'] = $like; $params['k6'] = $like; $params['k7'] = $like;
    }

    // Jika difilter pakai tahun
    if ($tahun !== '') {
        $sql .= " AND tahun_perolehan = :tahun";
        $params['tahun'] = $tahun;
    }

    $sql .= " ORDER BY nama_kapal ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

} elseif (function_exists('isAdmin') && isAdmin()) {
    // Jika tidak mencari apa-apa, tapi login sebagai admin (tampilkan semua)
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
<title>Cek CCTV by Keyword</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <h1>Cek CCTV Berdasarkan Keyword</h1>
    <p class="subtitle">Masukkan serial number, nama kapal, jenis barang, brand, model, lokasi, atau tahun perolehan.</p>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <form method="get" action="" class="search-form">
        <!-- Menyimpan state filter tahun saat mencari pakai keyword -->
        <?php if ($tahun !== ''): ?>
            <input type="hidden" name="tahun" value="<?= htmlspecialchars($tahun) ?>">
        <?php endif; ?>

        <input
            type="text"
            name="lokasi"
            placeholder="Cari serial number, nama kapal, brand, model, lokasi..."
            value="<?= htmlspecialchars($keyword) ?>"
            autofocus
        >
        <button type="submit" class="btn">Cari</button>
    </form>

    <?php if (function_exists('isAdmin') && isAdmin()): ?>
    <div class="actions-row">
        <a href="add_edit.php" class="btn">+ Tambah Data</a>
        <a href="import.php" class="btn btn-secondary">Import dari Excel/CSV</a>
    </div>
    <?php endif; ?>

    <?php if (count($results) > 0 || $hasSearched): ?>
        
        <!-- BARIS INFO DATA & FILTER TAHUN BERDAMPINGAN -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <span class="badge badge-blue" style="margin-bottom: 0;">Total <?= count($results) ?> data ditampilkan</span>
            
            <form method="get" action="" style="display: flex; align-items: center; gap: 8px;">
                <!-- Menyimpan state keyword saat mengganti tahun -->
                <?php if ($keyword !== ''): ?>
                    <input type="hidden" name="lokasi" value="<?= htmlspecialchars($keyword) ?>">
                <?php endif; ?>
                
                <label for="filter_tahun" style="font-size: 0.875rem; font-weight: 600; color: #475569;">Tahun Perolehan:</label>
                <select name="tahun" id="filter_tahun" onchange="this.form.submit()" style="height: 36px; padding: 0 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.875rem; background-color: #fff;">
                    <option value="">-- Semua Tahun --</option>
                    <?php foreach ($list_tahun as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>" <?= $tahun === (string)$t ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (count($results) > 0): ?>
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
                            <td style="white-space: nowrap;">
                                <a class="btn btn-sm" style="margin-right: 4px;" href="add_edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
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
                Tidak ada data CCTV yang cocok dengan kata kunci "<?= htmlspecialchars($keyword) ?>" pada tahun <?= htmlspecialchars($tahun) ?>.
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty">
            <?php if (function_exists('isAdmin') && isAdmin()): ?>
                Belum ada data.
            <?php else: ?>
                Masukkan kata kunci untuk mencari data CCTV.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>