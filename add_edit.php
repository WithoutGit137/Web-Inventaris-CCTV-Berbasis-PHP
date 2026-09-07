<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$data = [
    'serial_number' => '', 'kondisi' => 'Baru', 'nama_kapal' => '', 'jenis_barang' => '',
    'brand_barang' => '', 'no_model' => '', 'posisi_barang' => '', 'tahun_perolehan' => '',
];
$error = '';

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM cctv_inventory WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        setFlash('error', 'Data tidak ditemukan.');
        header('Location: index.php');
        exit;
    }
    $data = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['serial_number']   = trim($_POST['serial_number'] ?? '');
    $data['kondisi']         = trim($_POST['kondisi'] ?? 'Baru');
    $data['nama_kapal']      = trim($_POST['nama_kapal'] ?? '');
    $data['jenis_barang']    = trim($_POST['jenis_barang'] ?? '');
    $data['brand_barang']    = trim($_POST['brand_barang'] ?? '');
    $data['no_model']        = trim($_POST['no_model'] ?? '');
    $data['posisi_barang']   = trim($_POST['posisi_barang'] ?? '');
    $data['tahun_perolehan'] = trim($_POST['tahun_perolehan'] ?? '');

    if (in_array('', [$data['serial_number'], $data['nama_kapal'], $data['jenis_barang'], $data['brand_barang'], $data['no_model'], $data['posisi_barang'], $data['tahun_perolehan']], true)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!preg_match('/^\d{4}$/', $data['tahun_perolehan'])) {
        $error = 'Tahun perolehan harus berupa 4 digit angka, contoh: 2024.';
    } else {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare(
                    "UPDATE cctv_inventory SET
                        serial_number = :serial_number,
                        kondisi = :kondisi,
                        nama_kapal = :nama_kapal,
                        jenis_barang = :jenis_barang,
                        brand_barang = :brand_barang,
                        no_model = :no_model,
                        posisi_barang = :posisi_barang,
                        tahun_perolehan = :tahun_perolehan
                     WHERE id = :id"
                );
                $stmt->execute([
                    'serial_number' => $data['serial_number'],
                    'kondisi' => $data['kondisi'],
                    'nama_kapal' => $data['nama_kapal'],
                    'jenis_barang' => $data['jenis_barang'],
                    'brand_barang' => $data['brand_barang'],
                    'no_model' => $data['no_model'],
                    'posisi_barang' => $data['posisi_barang'],
                    'tahun_perolehan' => $data['tahun_perolehan'],
                    'id' => $id,
                ]);

                // Cek apakah kondisi diubah, jika ya masukan ke log
                if ($data['kondisi'] !== $existing['kondisi']) {
                    $log = $pdo->prepare("INSERT INTO cctv_kondisi_log (cctv_id, kondisi) VALUES (:cctv_id, :kondisi)");
                    $log->execute(['cctv_id' => $id, 'kondisi' => $data['kondisi']]);
                }

                setFlash('success', 'Data berhasil diperbarui.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO cctv_inventory
                        (serial_number, kondisi, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan)
                     VALUES
                        (:serial_number, :kondisi, :nama_kapal, :jenis_barang, :brand_barang, :no_model, :posisi_barang, :tahun_perolehan)"
                );
                $stmt->execute([
                    'serial_number' => $data['serial_number'],
                    'kondisi' => $data['kondisi'],
                    'nama_kapal' => $data['nama_kapal'],
                    'jenis_barang' => $data['jenis_barang'],
                    'brand_barang' => $data['brand_barang'],
                    'no_model' => $data['no_model'],
                    'posisi_barang' => $data['posisi_barang'],
                    'tahun_perolehan' => $data['tahun_perolehan'],
                ]);
                
                // Ambil ID yang baru ditambahkan untuk dimasukkan ke log pertama kalinya
                $newId = $pdo->lastInsertId();
                $log = $pdo->prepare("INSERT INTO cctv_kondisi_log (cctv_id, kondisi) VALUES (:cctv_id, :kondisi)");
                $log->execute(['cctv_id' => $newId, 'kondisi' => $data['kondisi']]);

                setFlash('success', 'Data baru berhasil ditambahkan.');
            }
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Serial number "' . $data['serial_number'] . '" sudah terdaftar.';
            } else {
                $error = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $isEdit ? 'Edit Data' : 'Tambah Data' ?> CCTV</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container narrow">
    <h1><?= $isEdit ? 'Edit Data CCTV' : 'Tambah Data CCTV' ?></h1>
    <p class="subtitle"><?= $isEdit ? 'Perbarui detail unit CCTV di bawah ini.' : 'Isi detail unit CCTV baru.' ?></p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="stacked">
        <div class="field">
            <label for="serial_number">Serial Number</label>
            <div style="display: flex; gap: 8px; align-items: center;">
                <input type="text" id="serial_number" name="serial_number" required value="<?= htmlspecialchars($data['serial_number']) ?>" style="flex: 1;">
                <button type="button" id="btn-scan" class="btn btn-secondary" style="white-space: nowrap;">📷 Scan</button>
            </div>
        </div>
        
        <div class="field">
            <label>Kondisi Barang</label>
            <select name="kondisi" required>
                <option value="Baru" <?= $data['kondisi'] === 'Baru' ? 'selected' : '' ?>>1. Baru</option>
                <option value="Sangat Baik" <?= $data['kondisi'] === 'Sangat Baik' ? 'selected' : '' ?>>2. Sangat Baik</option>
                <option value="Baik" <?= $data['kondisi'] === 'Baik' ? 'selected' : '' ?>>3. Baik</option>
                <option value="Cukup" <?= $data['kondisi'] === 'Cukup' ? 'selected' : '' ?>>4. Cukup</option>
                <option value="Rusak" <?= $data['kondisi'] === 'Rusak' ? 'selected' : '' ?>>5. Rusak</option>
            </select>
        </div>

        <div class="field">
            <label>Nama Kapal</label>
            <input type="text" name="nama_kapal" required value="<?= htmlspecialchars($data['nama_kapal']) ?>">
        </div>
        <div class="field"><label>Jenis Barang</label><input type="text" name="jenis_barang" required value="<?= htmlspecialchars($data['jenis_barang']) ?>"></div>
        <div class="field"><label>Brand Barang</label><input type="text" name="brand_barang" required value="<?= htmlspecialchars($data['brand_barang']) ?>"></div>
        <div class="field"><label>No. Model Barang</label><input type="text" name="no_model" required value="<?= htmlspecialchars($data['no_model']) ?>"></div>
        <div class="field"><label>Posisi Barang</label><input type="text" name="posisi_barang" required value="<?= htmlspecialchars($data['posisi_barang']) ?>"></div>
        <div class="field"><label>Tahun Perolehan</label><input type="text" name="tahun_perolehan" required value="<?= htmlspecialchars($data['tahun_perolehan']) ?>" maxlength="4"></div>
        
        <div class="actions-row" style="margin-top: 20px;">
            <button type="submit" class="btn"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Data' ?></button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<!-- Modal Scanner (Disembunyikan secara default) -->
<div id="scannerModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
    <div style="background-color: #fff; padding: 24px; border-radius: 12px; width: 90%; max-width: 500px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #0f172a;">Scan Serial Number</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">Arahkan kamera ke Barcode (Code-128) atau QR Code.</p>
        
        <!-- Wadah Kamera -->
        <div id="reader" style="width: 100%; margin-bottom: 20px;"></div>
        
        <button type="button" id="btn-close-scan" class="btn btn-danger">Tutup Kamera</button>
    </div>
</div>

<!-- Termasuk Footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>

<!-- Script Library HTML5-QRCode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnScan = document.getElementById('btn-scan');
    const btnCloseScan = document.getElementById('btn-close-scan');
    const scannerModal = document.getElementById('scannerModal');
    const serialInput = document.getElementById('serial_number');
    
    let html5QrCode = null;

    // Saat tombol scan diklik
    btnScan.addEventListener('click', function() {
        scannerModal.style.display = 'flex'; // Tampilkan modal
        
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = { fps: 10, qrbox: { width: 250, height: 150 } };

        // Nyalakan kamera (mengutamakan kamera belakang)
        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
            (decodedText, decodedResult) => {
                // Ketika barcode berhasil terbaca
                serialInput.value = decodedText;
                
                // Berikan efek highlight sementara
                serialInput.style.backgroundColor = '#dcfce7';
                setTimeout(() => { serialInput.style.backgroundColor = ''; }, 1000);
                
                closeScanner(); // Matikan kamera dan tutup modal
            },
            (errorMessage) => {
                // Abaikan error per-frame saat sedang mencari barcode
            }
        ).catch(err => {
            alert("Gagal membuka kamera: " + err);
            closeScanner();
        });
    });

    // Saat tombol tutup diklik manual
    btnCloseScan.addEventListener('click', closeScanner);

    function closeScanner() {
        scannerModal.style.display = 'none'; // Sembunyikan modal
        if (html5QrCode) {
            // Matikan proses kamera
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
            }).catch(error => {
                console.error("Gagal mematikan kamera. ", error);
            });
        }
    }
});
</script>
</body>
</html>