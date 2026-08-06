<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
requireAdmin();

// Kolom yang wajib ada di file, key = nama header setelah dinormalisasi (huruf kecil, tanpa spasi/simbol)
$expectedColumns = [
    'serialnumber'    => 'serial_number',
    'sn'              => 'serial_number',
    'namakapal'       => 'nama_kapal',
    'kapal'           => 'nama_kapal',
    'jenisbarang'     => 'jenis_barang',
    'jenis'           => 'jenis_barang',
    'brandbarang'     => 'brand_barang',
    'brand'           => 'brand_barang',
    'merk'            => 'brand_barang',
    'nomodel'         => 'no_model',
    'model'           => 'no_model',
    'posisibarang'    => 'posisi_barang',
    'posisi'          => 'posisi_barang',
    'lokasi'          => 'posisi_barang',
    'tahunperolehan'  => 'tahun_perolehan',
    'tahun'           => 'tahun_perolehan',
];

function normalizeHeader($h)
{
    return preg_replace('/[^a-z0-9]/', '', strtolower(trim((string)$h)));
}

function mapHeaders(array $header, array $expectedColumns): array
{
    $colMap = [];
    foreach ($header as $idx => $h) {
        $norm = normalizeHeader($h);
        if (isset($expectedColumns[$norm])) {
            $colMap[$idx] = $expectedColumns[$norm];
        }
    }
    return $colMap;
}

function processRow(array $row, array $colMap, PDO $pdo, array &$summary, array &$errors, int $rowNum): void
{
    $data = [
        'serial_number' => null, 'nama_kapal' => null, 'jenis_barang' => null,
        'brand_barang' => null, 'no_model' => null, 'posisi_barang' => null, 'tahun_perolehan' => null,
    ];
    foreach ($colMap as $idx => $dbCol) {
        $data[$dbCol] = isset($row[$idx]) ? trim((string)$row[$idx]) : null;
    }

    // lewati baris yang benar-benar kosong
    $nonEmpty = array_filter($data, function ($v) {
        return $v !== null && $v !== '';
    });
    if (count($nonEmpty) === 0) {
        return;
    }

    if (empty($data['serial_number'])) {
        $errors[] = "Baris $rowNum: kolom serial_number kosong, baris dilewati.";
        $summary['skipped']++;
        return;
    }

    foreach (['nama_kapal', 'jenis_barang', 'brand_barang', 'no_model', 'posisi_barang', 'tahun_perolehan'] as $req) {
        if ($data[$req] === null || $data[$req] === '') {
            $errors[] = "Baris $rowNum (SN: {$data['serial_number']}): kolom $req kosong, baris dilewati.";
            $summary['skipped']++;
            return;
        }
    }

    if (!preg_match('/^\d{4}$/', (string)$data['tahun_perolehan'])) {
        $errors[] = "Baris $rowNum (SN: {$data['serial_number']}): tahun_perolehan '{$data['tahun_perolehan']}' tidak valid (harus 4 digit), baris dilewati.";
        $summary['skipped']++;
        return;
    }

    $check = $pdo->prepare("SELECT id FROM cctv_inventory WHERE serial_number = :sn");
    $check->execute(['sn' => $data['serial_number']]);
    $exists = $check->fetch();

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO cctv_inventory
                (serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan)
             VALUES
                (:serial_number, :nama_kapal, :jenis_barang, :brand_barang, :no_model, :posisi_barang, :tahun_perolehan)
             ON DUPLICATE KEY UPDATE
                nama_kapal = VALUES(nama_kapal),
                jenis_barang = VALUES(jenis_barang),
                brand_barang = VALUES(brand_barang),
                no_model = VALUES(no_model),
                posisi_barang = VALUES(posisi_barang),
                tahun_perolehan = VALUES(tahun_perolehan)"
        );
        $stmt->execute($data);
        $exists ? $summary['updated']++ : $summary['inserted']++;
    } catch (PDOException $e) {
        $errors[] = "Baris $rowNum (SN: {$data['serial_number']}): gagal disimpan (" . $e->getMessage() . ").";
        $summary['skipped']++;
    }
}

$summary = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload file gagal (kode error: ' . $file['error'] . ').';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tmpPath = $file['tmp_name'];
        $summary = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];

        if ($ext === 'csv') {
            $handle = fopen($tmpPath, 'r');
            if ($handle === false) {
                $errors[] = 'Gagal membuka file CSV.';
                $summary = null;
            } else {
                $header = fgetcsv($handle);
                if ($header === false) {
                    $errors[] = 'File CSV kosong atau tidak valid.';
                    $summary = null;
                } else {
                    if (isset($header[0])) {
                        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                    }
                    $colMap = mapHeaders($header, $expectedColumns);
                    if (empty($colMap)) {
                        $errors[] = 'Header kolom tidak dikenali. Pastikan file memiliki kolom: serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan.';
                        $summary = null;
                    } else {
                        $rowNum = 1;
                        while (($row = fgetcsv($handle)) !== false) {
                            $rowNum++;
                            processRow($row, $colMap, $pdo, $summary, $errors, $rowNum);
                        }
                    }
                }
                fclose($handle);
            }
        } elseif (in_array($ext, ['xlsx', 'xls'], true)) {
            $autoload = __DIR__ . '/vendor/autoload.php';
            if (!file_exists($autoload)) {
                $errors[] = 'Library PhpSpreadsheet belum terpasang di server. Jalankan "composer install" di folder aplikasi (lihat README.md), atau simpan file Excel Anda sebagai .csv lalu upload ulang.';
                $summary = null;
            } else {
                require_once $autoload;
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->toArray(null, true, true, false);
                    $header = array_shift($rows);
                    if ($header === null) {
                        $errors[] = 'File Excel kosong.';
                        $summary = null;
                    } else {
                        $colMap = mapHeaders($header, $expectedColumns);
                        if (empty($colMap)) {
                            $errors[] = 'Header kolom tidak dikenali. Pastikan file memiliki kolom: serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan.';
                            $summary = null;
                        } else {
                            $rowNum = 1;
                            foreach ($rows as $row) {
                                $rowNum++;
                                processRow($row, $colMap, $pdo, $summary, $errors, $rowNum);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Gagal membaca file Excel: ' . $e->getMessage();
                }
            }
        } else {
            $errors[] = 'Format file tidak didukung. Gunakan .csv atau .xlsx.';
            $summary = null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Import Data CCTV dari Excel/CSV</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <h1>Import Data dari Excel/CSV</h1>
    <p class="subtitle">
        Upload file <strong>.xlsx</strong> atau <strong>.csv</strong> untuk menambah data baru sekaligus memperbarui
        data yang sudah ada (dicocokkan berdasarkan Serial Number).
    </p>

    <div class="alert alert-info">
        Kolom yang dibutuhkan (urutan bebas, nama header tidak case-sensitive):<br>
        <code>serial_number, nama_kapal, jenis_barang, brand_barang, no_model, posisi_barang, tahun_perolehan</code><br>
        Belum punya template? <a href="template_import.csv" download>Unduh template CSV</a>.
    </div>

    <?php if ($summary !== null): ?>
        <div class="summary-box">
            <div class="summary-item">Data Baru Ditambahkan<strong><?= $summary['inserted'] ?></strong></div>
            <div class="summary-item">Data Diperbarui<strong><?= $summary['updated'] ?></strong></div>
            <div class="summary-item">Baris Dilewati<strong><?= $summary['skipped'] ?></strong></div>
        </div>
        <?php if (($summary['inserted'] + $summary['updated']) > 0): ?>
            <div class="alert alert-success">Import selesai. <a href="index.php">Lihat data terbaru &rarr;</a></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <p><strong>Catatan / baris bermasalah:</strong></p>
        <ul class="error-list">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2 style="margin-top:30px;">Upload File</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="field">
            <label>Pilih file (.xlsx atau .csv)</label>
            <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required>
        </div>
        <div class="field" style="flex:none; align-self:flex-end;">
            <button type="submit">Import Data</button>
        </div>
    </form>
</div>
<div class="footer">
    &copy; 2026 Hafizh Ibrahim. SMK 3 Negeri Jakarta. Sistem Inventory CCTV Kapal PID.
</div>
</body>
</html>
