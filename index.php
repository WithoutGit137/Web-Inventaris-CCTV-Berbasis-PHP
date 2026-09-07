<?php
require_once __DIR__ . '/config.php';
function getDeskripsiKondisi($kondisi) {
    switch($kondisi) {
        case 'Baru': return 'Kondisi 100% sempurna, segel pabrik, belum aktivasi';
        case 'Sangat Baik': return 'Sudah dibuka/dipakai singkat, fisik mulus total, berfungsi 100% normal';
        case 'Baik': return 'Ada bekas pakai wajar atau hasil perbaikan standar pabrik, fungsi utama lancar';
        case 'Cukup': return 'Ada cacat fisik jelas atau penurunan fungsi komponen tertentu, tetapi masih bisa menyala';
        case 'Rusak': return 'Mati total, hancur, atau biaya perbaikan sudah tidak ekonomis lagi';
        default: return '';
    }
}
require_once __DIR__ . '/auth.php';

$flash = getFlash();

$keyword = trim($_GET['serial_number'] ?? '');
$results = [];
$hasSearched = $keyword !== '';

if ($hasSearched) {
    $stmt = $pdo->prepare(
        "SELECT id, serial_number, kondisi, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan
         FROM cctv_inventory
         WHERE serial_number LIKE :keyword
         ORDER BY nama_kapal ASC"
    );
    $stmt->execute(['keyword' => '%' . $keyword . '%']);
    $results = $stmt->fetchAll();
} elseif (function_exists('isAdmin') && isAdmin()) {
    $results = $pdo->query(
        "SELECT id, serial_number, kondisi, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan
         FROM cctv_inventory ORDER BY nama_kapal ASC"
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Serial Number CCTV Kapal</title>
<link rel="stylesheet" href="assets/style.css">
<!-- Library Scan Barcode -->
<script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <h1>Cek Serial Number CCTV Kapal</h1>
    <p class="subtitle">Masukkan atau scan serial number untuk melihat detail unit CCTV.</p>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Form Pencarian -->
    <form method="get" action="" class="search-form" id="search-form">
        <input
            type="text"
            name="serial_number"
            id="serial_number_input"
            placeholder="Contoh: HKV20231001"
            value="<?= htmlspecialchars($keyword) ?>"
            autofocus
        >
        <!-- Tambahkan class="btn" di sini agar tidak gepeng -->
        <button type="submit" class="btn">Cek Serial Number</button>
        <button type="button" id="btn-scan" class="btn btn-secondary">📷 Scan Barcode</button>
    </form>

    <!-- Area Tampilan Kamera Scanner (Disembunyikan secara bawaan) -->
    <div id="reader-wrapper" style="display:none; margin: 20px 0; text-align: center;">
        <div id="reader" style="width: 100%; max-width: 450px; margin: 0 auto;"></div>
        <button type="button" id="btn-close-scan" class="btn btn-danger btn-sm" style="margin-top: 10px;">Tutup Kamera</button>
    </div>

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
					<th>Kondisi</th>
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
						<td style="cursor: pointer; color: #2563eb; text-decoration: underline; font-weight: bold;" 
							title="<?= htmlspecialchars(getDeskripsiKondisi($row['kondisi'] ?? 'Baru')) ?>" 
							onclick="showKondisiLog(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['serial_number']) ?>')">
							<?= htmlspecialchars($row['kondisi'] ?? 'Baru') ?>
						</td>
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
                Tidak ada data CCTV dengan serial number "<?= htmlspecialchars($keyword) ?>".
            <?php elseif (function_exists('isAdmin') && isAdmin()): ?>
                Belum ada data.
            <?php else: ?>
                Masukkan atau scan serial number di atas untuk melihat data CCTV.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

<!-- Script Kontrol Scanner Barcode -->
<script>
let html5QrCode = null;

document.getElementById('btn-scan').addEventListener('click', function() {
    const wrapper = document.getElementById('reader-wrapper');
    wrapper.style.display = 'block';

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }

    const config = { fps: 10, qrbox: { width: 250, height: 150 } };

    html5QrCode.start(
        { facingMode: "environment" }, // Mengutamakan kamera belakang HP
        config,
        (decodedText, decodedResult) => {
            // Ketika barcode berhasil terdeteksi:
            document.getElementById('serial_number_input').value = decodedText;
            
            // Matikan kamera dan sembunyikan kotak scanner
            html5QrCode.stop().then(() => {
                wrapper.style.display = 'none';
                // Otomatis jalankan pencarian
                document.getElementById('search-form').submit();
            });
        },
        (errorMessage) => {
            // Mengabaikan error pemindaian biasa per-frame
        }
    ).catch(err => {
        alert("Gagal membuka kamera: " + err);
        wrapper.style.display = 'none';
    });
});

document.getElementById('btn-close-scan').addEventListener('click', function() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('reader-wrapper').style.display = 'none';
        });
    }
});
</script>
<!-- Modal Timeline Kondisi dengan Garis Penghubung -->
<style>
/* CSS Khusus untuk Timeline */
.timeline-list {
    list-style: none;
    padding: 0 0 0 10px; /* Jarak dari kiri */
    margin: 0;
    position: relative;
}
.timeline-item {
    position: relative;
    padding-left: 20px;  /* Ruang untuk teks di sebelah kanan garis */
    padding-bottom: 16px; /* Jarak antar item riwayat */
    color: #334155;
    font-size: 0.95rem;
    line-height: 1.5;
}
/* Membuat Titik Bulatan (Dot) */
.timeline-item::before {
    content: '';
    position: absolute;
    left: -4px;
    top: 6px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #3b82f6; /* Warna biru untuk titik */
    z-index: 2;
}
/* Membuat Garis Penghubung ke Bawah */
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 0px;       /* Posisinya sejajar dengan titik */
    top: 14px;       /* Mulai dari bawah titik saat ini */
    bottom: -6px;    /* Turun memanjang sampai titik item berikutnya */
    width: 2px;
    background-color: #cbd5e1; /* Warna garis abu-abu (Slate-300) */
    z-index: 1;
}
/* Menghilangkan margin bawah pada item terakhir */
.timeline-item:last-child {
    padding-bottom: 0;
}
</style>

<div id="logModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
    <div style="background-color: #fff; padding: 24px; border-radius: 12px; width: 90%; max-width: 450px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
            Riwayat Kondisi Barang<br><span id="log-sn" style="font-size: 0.85em; color: #64748b;"></span>
        </h3>
        
        <!-- Wadah Timeline (class timeline-list dipanggil di sini) -->
        <ul id="log-list" class="timeline-list">
            <li class="timeline-item">Memuat riwayat...</li>
        </ul>
        
        <div style="text-align: right; margin-top: 25px;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('logModal').style.display='none'">Tutup</button>
        </div>
    </div>
</div>

<script>
function showKondisiLog(id, sn) {
    document.getElementById('logModal').style.display = 'flex';
    document.getElementById('log-sn').innerText = "SN: " + sn;
    document.getElementById('log-list').innerHTML = '<li class="timeline-item">Memuat data riwayat...</li>';

    // Mengambil data log dari get_kondisi_log.php
    fetch('get_kondisi_log.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('log-list').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('log-list').innerHTML = '<li class="timeline-item">Gagal memuat data.</li>';
        });
}
</script>
</body>
</html>